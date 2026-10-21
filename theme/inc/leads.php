<?php
/**
 * Приём заявок: проверка, защита от спама, сохранение в WordPress и просмотр в админке.
 *
 * Форма работает и без JavaScript (admin-post.php с редиректом), и через fetch (admin-ajax.php).
 * Сообщение об успехе показывается только после того, как заявка реально сохранена.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

const RMT_LEAD_MIN_SECONDS = 3;   // Быстрее человек форму не заполнит.
const RMT_LEAD_RATE_LIMIT  = 5;   // Не больше 5 заявок…
const RMT_LEAD_RATE_WINDOW = 900; // …за 15 минут с одного адреса.

add_action( 'wp_ajax_rmt_lead', 'rmt_lead_ajax' );
add_action( 'wp_ajax_nopriv_rmt_lead', 'rmt_lead_ajax' );
add_action( 'admin_post_rmt_lead', 'rmt_lead_post' );
add_action( 'admin_post_nopriv_rmt_lead', 'rmt_lead_post' );

function rmt_lead_ajax() {
	$result = rmt_lead_handle( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce проверяется в rmt_lead_handle().
	if ( $result['ok'] ) {
		wp_send_json_success( array( 'message' => $result['message'] ) );
	}
	wp_send_json_error(
		array(
			'message' => $result['message'],
			'errors'  => $result['errors'],
		),
		$result['status']
	);
}

function rmt_lead_post() {
	$result = rmt_lead_handle( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce проверяется в rmt_lead_handle().
	$back   = wp_get_referer() ?: home_url( '/' );
	$back   = remove_query_arg( array( 'lead', 'lead_fields' ), $back );
	// В адрес передаются только коды — тексты берутся из rmt_lead_messages(), а не из URL.
	$back = add_query_arg(
		array_filter(
			array(
				'lead'        => $result['code'],
				'lead_fields' => implode( ',', array_keys( $result['errors'] ) ),
			)
		),
		$back
	);
	wp_safe_redirect( $back . '#lead-form' );
	exit;
}

/** Тексты результатов отправки по кодам. */
function rmt_lead_messages() {
	return array(
		'ok'      => __( 'Спасибо! Заявка принята — мы перезвоним в рабочее время.', 'remont' ),
		'expired' => __( 'Страница устарела. Обновите её и отправьте заявку ещё раз.', 'remont' ),
		'spam'    => __( 'Заявка не прошла проверку. Позвоните нам, пожалуйста.', 'remont' ),
		'fast'    => __( 'Форма отправлена слишком быстро. Подождите пару секунд и попробуйте снова.', 'remont' ),
		'rate'    => __( 'Слишком много заявок подряд. Попробуйте позже или позвоните нам.', 'remont' ),
		'invalid' => __( 'Проверьте поля формы.', 'remont' ),
		'save'    => __( 'Не удалось сохранить заявку. Пожалуйста, позвоните нам.', 'remont' ),
	);
}

/** Тексты ошибок полей по имени поля. */
function rmt_lead_field_messages() {
	return array(
		'name'        => __( 'Укажите имя: от 2 до 60 букв.', 'remont' ),
		'phone'       => __( 'Проверьте номер телефона: нужно 10–15 цифр.', 'remont' ),
		'repair_type' => __( 'Выберите тип ремонта.', 'remont' ),
		/* translators: %d: максимальная площадь из настроек калькулятора */
		'area'        => sprintf( __( 'Площадь — число от 1 до %d м².', 'remont' ), rmt_calc_settings()['max_area'] ),
		'comment'     => __( 'Комментарий слишком длинный (до 2000 символов).', 'remont' ),
	);
}

/** Подписанная метка времени показа формы. */
function rmt_lead_time_token() {
	$t = time();
	return $t . '.' . wp_hash( 'rmt_lead_ts|' . $t );
}

/**
 * Проверка и сохранение заявки.
 *
 * @return array{ok:bool,code:string,status:int,message:string,errors:array<string,string>}
 */
function rmt_lead_handle( array $raw ) {
	$raw      = wp_unslash( $raw );
	$messages = rmt_lead_messages();
	$fail     = function ( $code, $status = 400, $errors = array() ) use ( $messages ) {
		return array(
			'ok'      => false,
			'code'    => $code,
			'status'  => $status,
			'message' => $messages[ $code ],
			'errors'  => $errors,
		);
	};

	// 1. CSRF: одноразовый ключ WordPress.
	if ( empty( $raw['_rmt_nonce'] ) || ! wp_verify_nonce( sanitize_key( $raw['_rmt_nonce'] ), 'rmt_lead' ) ) {
		return $fail( 'expired', 403 );
	}

	// 2. Ловушка для ботов: скрытое поле должно остаться пустым.
	if ( ! empty( $raw['company_site'] ) ) {
		return $fail( 'spam', 400 );
	}

	// 3. Слишком быстрая отправка или подделанная метка времени.
	$ts_parts = explode( '.', (string) ( $raw['rmt_ts'] ?? '' ), 2 );
	$ts       = (int) $ts_parts[0];
	if ( 2 !== count( $ts_parts ) || ! hash_equals( wp_hash( 'rmt_lead_ts|' . $ts ), $ts_parts[1] ) ) {
		return $fail( 'expired', 403 );
	}
	$age = time() - $ts;
	if ( $age < RMT_LEAD_MIN_SECONDS ) {
		return $fail( 'fast', 429 );
	}
	if ( $age > DAY_IN_SECONDS ) {
		return $fail( 'expired', 403 );
	}

	// 4. Ограничение частоты по хешу IP (сам адрес не сохраняется).
	$ip      = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$rl_key  = 'rmt_lead_rl_' . md5( $ip . wp_salt( 'nonce' ) );
	$rl_hits = (int) get_transient( $rl_key );
	if ( $rl_hits >= RMT_LEAD_RATE_LIMIT ) {
		return $fail( 'rate', 429 );
	}

	// 5. Проверка полей.
	$errors = array();
	$texts  = rmt_lead_field_messages();
	$types  = rmt_repair_type_options();

	$name = trim( sanitize_text_field( $raw['name'] ?? '' ) );
	if ( ! preg_match( '/^[\p{L}][\p{L}\s\'’.\-]{1,59}$/u', $name ) ) {
		$errors['name'] = $texts['name'];
	}

	$phone_digits = preg_replace( '/\D+/', '', (string) ( $raw['phone'] ?? '' ) );
	if ( 11 === strlen( $phone_digits ) && '8' === $phone_digits[0] ) {
		$phone_digits = '7' . substr( $phone_digits, 1 );
	} elseif ( 10 === strlen( $phone_digits ) ) {
		$phone_digits = '7' . $phone_digits;
	}
	if ( strlen( $phone_digits ) < 11 || strlen( $phone_digits ) > 15 ) {
		$errors['phone'] = $texts['phone'];
	}

	$type = sanitize_key( $raw['repair_type'] ?? '' );
	if ( ! isset( $types[ $type ] ) ) {
		$errors['repair_type'] = $texts['repair_type'];
	}

	$area_raw = trim( str_replace( ',', '.', (string) ( $raw['area'] ?? '' ) ) );
	$area     = '';
	if ( '' !== $area_raw ) {
		if ( ! is_numeric( $area_raw ) || (float) $area_raw < 1 || (float) $area_raw > rmt_calc_settings()['max_area'] ) {
			$errors['area'] = $texts['area'];
		} else {
			$area = (string) round( (float) $area_raw, 1 );
		}
	}

	$comment = sanitize_textarea_field( $raw['comment'] ?? '' );
	if ( mb_strlen( $comment ) > 2000 ) {
		$errors['comment'] = $texts['comment'];
	}


	if ( $errors ) {
		return $fail( 'invalid', 422, $errors );
	}

	$calc   = mb_substr( sanitize_text_field( $raw['calc_summary'] ?? '' ), 0, 600 );
	$source = esc_url_raw( wp_validate_redirect( (string) ( $raw['source'] ?? '' ), '' ) );

	// 6. Сохранение. Ошибка сохранения = ошибка для пользователя.
	$phone   = '+' . $phone_digits;
	$post_id = wp_insert_post(
		array(
			'post_type'   => 'rmt_lead',
			'post_status' => 'publish',
			'post_title'  => sprintf( '%s · %s', $name, $phone ),
			'meta_input'  => array(
				'_rmt_lead_name'     => $name,
				'_rmt_lead_phone'    => $phone,
				'_rmt_lead_type'     => $type,
				'_rmt_lead_area'     => $area,
				'_rmt_lead_comment'  => $comment,
				'_rmt_lead_calc'     => $calc,
				'_rmt_lead_source'   => $source,
				'_rmt_lead_status'   => 'new',
			),
		),
		true
	);

	if ( is_wp_error( $post_id ) || ! $post_id || get_post_meta( $post_id, '_rmt_lead_phone', true ) !== $phone ) {
		return $fail( 'save', 500 );
	}

	set_transient( $rl_key, $rl_hits + 1, RMT_LEAD_RATE_WINDOW );
	rmt_lead_notify( $post_id );

	return array(
		'ok'      => true,
		'code'    => 'ok',
		'status'  => 200,
		'message' => $messages['ok'],
		'errors'  => array(),
	);
}

/* ---------- Копии заявок на почту: настройка и отправка ---------- */

// Поле адресов — на общей странице «Настройки → Калькулятор и заявки» (группа rmt_calc_group).
add_action( 'admin_init', 'rmt_lead_register_settings' );
function rmt_lead_register_settings() {
	register_setting(
		'rmt_calc_group',
		'rmt_lead_notify_email',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'rmt_sanitize_notify_emails',
			'default'           => '',
		)
	);
}

add_action( 'rmt_settings_page_sections', 'rmt_lead_settings_section' );
function rmt_lead_settings_section() {
	?>
	<h2><?php esc_html_e( 'Уведомления о заявках', 'remont' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="rmt-notify"><?php esc_html_e( 'Email для копий заявок', 'remont' ); ?></label></th>
			<td>
				<input id="rmt-notify" type="text" class="large-text" name="rmt_lead_notify_email" value="<?php echo esc_attr( get_option( 'rmt_lead_notify_email', '' ) ); ?>">
				<p class="description"><?php esc_html_e( 'Один или несколько адресов через запятую. Заявки в любом случае сохраняются в разделе «Заявки», письмо — копия. Отправка идёт через SMTP, настроенный в wp-config.php (константы RMT_SMTP_*).', 'remont' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

/** Адреса для уведомлений из настроек: список через запятую, только корректные email. */
function rmt_lead_notify_emails() {
	$raw = preg_split( '/[\s,;]+/', (string) get_option( 'rmt_lead_notify_email', '' ), -1, PREG_SPLIT_NO_EMPTY );
	return array_values( array_unique( array_filter( array_map( 'sanitize_email', $raw ), 'is_email' ) ) );
}

/** Очистка поля настроек: оставляем корректные адреса через запятую. */
function rmt_sanitize_notify_emails( $value ) {
	$list = preg_split( '/[\s,;]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY );
	return implode( ', ', array_unique( array_filter( array_map( 'sanitize_email', $list ), 'is_email' ) ) );
}

/**
 * Копия заявки на почту — если адреса заданы в настройках. Заявка к этому моменту уже сохранена,
 * поэтому сбой почты на неё не влияет; результат отправки записывается в заявку.
 */
function rmt_lead_notify( $post_id ) {
	$to = rmt_lead_notify_emails();
	if ( ! $to ) {
		return;
	}
	$types = rmt_repair_type_options();
	$type  = get_post_meta( $post_id, '_rmt_lead_type', true );
	$name  = get_post_meta( $post_id, '_rmt_lead_name', true );
	$phone = get_post_meta( $post_id, '_rmt_lead_phone', true );
	$area  = get_post_meta( $post_id, '_rmt_lead_area', true );
	$val   = fn( $v ) => '' !== (string) $v ? $v : '—';
	$body  = implode(
		"\n",
		array(
			'Новая заявка с сайта ' . get_bloginfo( 'name' ) . ' — ' . wp_date( 'j F Y, H:i' ),
			'',
			'Имя: ' . $name,
			'Телефон: ' . $phone,
			'Тип ремонта: ' . $val( $types[ $type ] ?? $type ),
			'Площадь: ' . $val( $area ? $area . ' м²' : '' ),
			'Комментарий: ' . $val( get_post_meta( $post_id, '_rmt_lead_comment', true ) ),
			'Расчёт из калькулятора: ' . $val( get_post_meta( $post_id, '_rmt_lead_calc', true ) ),
			'Страница: ' . $val( get_post_meta( $post_id, '_rmt_lead_source', true ) ),
			'',
			'Заявка в админке: ' . admin_url( 'post.php?post=' . $post_id . '&action=edit' ),
		)
	);
	$sent = wp_mail( $to, sprintf( 'Заявка с сайта: %s, %s', $name, $phone ), $body );
	update_post_meta( $post_id, '_rmt_lead_mailed', $sent ? 'sent' : 'failed' );
}

/* ---------- Админка ---------- */

function rmt_lead_statuses() {
	return array(
		'new'  => __( 'Новая', 'remont' ),
		'work' => __( 'В работе', 'remont' ),
		'done' => __( 'Закрыта', 'remont' ),
	);
}

add_filter( 'manage_rmt_lead_posts_columns', 'rmt_lead_columns' );
function rmt_lead_columns( $cols ) {
	return array(
		'cb'         => $cols['cb'],
		'title'      => __( 'Заявка', 'remont' ),
		'rmt_type'   => __( 'Тип ремонта', 'remont' ),
		'rmt_area'   => __( 'Площадь', 'remont' ),
		'rmt_status' => __( 'Статус', 'remont' ),
		'date'       => __( 'Дата', 'remont' ),
	);
}

add_action( 'manage_rmt_lead_posts_custom_column', 'rmt_lead_column_value', 10, 2 );
function rmt_lead_column_value( $col, $post_id ) {
	$types    = rmt_repair_type_options();
	$statuses = rmt_lead_statuses();
	switch ( $col ) {
		case 'rmt_type':
			$t = get_post_meta( $post_id, '_rmt_lead_type', true );
			echo esc_html( $types[ $t ] ?? $t );
			break;
		case 'rmt_area':
			$a = get_post_meta( $post_id, '_rmt_lead_area', true );
			echo $a ? esc_html( $a . ' м²' ) : '—';
			break;
		case 'rmt_status':
			$s = get_post_meta( $post_id, '_rmt_lead_status', true ) ?: 'new';
			echo esc_html( $statuses[ $s ] ?? $s );
			break;
	}
}

add_action( 'add_meta_boxes_rmt_lead', 'rmt_lead_meta_box' );
function rmt_lead_meta_box() {
	add_meta_box( 'rmt_lead_details', __( 'Данные заявки', 'remont' ), 'rmt_lead_render_details', 'rmt_lead', 'normal', 'high' );
}

function rmt_lead_render_details( $post ) {
	$types  = rmt_repair_type_options();
	$type   = get_post_meta( $post->ID, '_rmt_lead_type', true );
	$phone  = get_post_meta( $post->ID, '_rmt_lead_phone', true );
	$rows   = array(
		__( 'Имя', 'remont' )                => get_post_meta( $post->ID, '_rmt_lead_name', true ),
		__( 'Тип ремонта', 'remont' )        => $types[ $type ] ?? $type,
		__( 'Площадь, м²', 'remont' )        => get_post_meta( $post->ID, '_rmt_lead_area', true ),
		__( 'Комментарий', 'remont' )        => get_post_meta( $post->ID, '_rmt_lead_comment', true ),
		__( 'Из калькулятора', 'remont' )    => get_post_meta( $post->ID, '_rmt_lead_calc', true ),
		__( 'Страница', 'remont' )           => get_post_meta( $post->ID, '_rmt_lead_source', true ),
		__( 'Копия на почту', 'remont' )     => array( 'sent' => __( 'отправлена', 'remont' ), 'failed' => __( 'не отправлена — проверьте настройки почты', 'remont' ) )[ get_post_meta( $post->ID, '_rmt_lead_mailed', true ) ] ?? '',
	);
	$status = get_post_meta( $post->ID, '_rmt_lead_status', true ) ?: 'new';

	wp_nonce_field( 'rmt_lead_status', 'rmt_lead_status_nonce' );
	echo '<table class="form-table" role="presentation"><tbody>';
	echo '<tr><th>' . esc_html__( 'Телефон', 'remont' ) . '</th><td><a href="' . esc_url( 'tel:' . $phone ) . '">' . esc_html( $phone ) . '</a></td></tr>';
	foreach ( $rows as $label => $value ) {
		echo '<tr><th>' . esc_html( $label ) . '</th><td>' . nl2br( esc_html( $value ?: '—' ) ) . '</td></tr>';
	}
	echo '<tr><th><label for="rmt-lead-status">' . esc_html__( 'Статус', 'remont' ) . '</label></th><td><select id="rmt-lead-status" name="rmt_lead_status">';
	foreach ( rmt_lead_statuses() as $key => $label ) {
		echo '<option value="' . esc_attr( $key ) . '" ' . selected( $status, $key, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select></td></tr></tbody></table>';
}

add_action( 'save_post_rmt_lead', 'rmt_lead_save_status' );
function rmt_lead_save_status( $post_id ) {
	if ( ! isset( $_POST['rmt_lead_status_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['rmt_lead_status_nonce'] ) ), 'rmt_lead_status' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$status = sanitize_key( wp_unslash( $_POST['rmt_lead_status'] ?? 'new' ) );
	if ( isset( rmt_lead_statuses()[ $status ] ) ) {
		update_post_meta( $post_id, '_rmt_lead_status', $status );
	}
}

/** Счётчик новых заявок в меню админки. */
add_action( 'admin_menu', 'rmt_lead_menu_badge', 99 );
function rmt_lead_menu_badge() {
	global $menu;
	$q     = new WP_Query(
		array(
			'post_type'      => 'rmt_lead',
			'post_status'    => 'publish',
			'meta_key'       => '_rmt_lead_status', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => 'new',              // phpcs:ignore WordPress.DB.SlowDBQuery
			'fields'         => 'ids',
			'posts_per_page' => 1,
			'no_found_rows'  => false,
		)
	);
	$count = (int) $q->found_posts;
	if ( ! $count || ! is_array( $menu ) ) {
		return;
	}
	foreach ( $menu as $i => $item ) {
		if ( 'edit.php?post_type=rmt_lead' === ( $item[2] ?? '' ) ) {
			$menu[ $i ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . $count . '</span></span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}
	}
}
