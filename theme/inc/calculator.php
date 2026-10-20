<?php
/**
 * Калькулятор стоимости: тарифы ремонта (ставка за м² и состав работ) и площадь.
 * Страница настроек: «Настройки → Калькулятор и заявки».
 *
 * Формула: площадь × ставка тарифа = фиксированная стоимость.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

/**
 * Тарифы по умолчанию — из прайса компании (папка calc, 20.10.2026).
 */
function rmt_calc_defaults() {
	return array(
		'demo'         => false,
		'min_area'     => 10,
		'max_area'     => 1000,
		'default_area' => 60,
		'types'        => array(
			array(
				'key'      => 'econom',
				'label'    => 'Эконом',
				'subtitle' => 'Бюджетный ремонт',
				'rate'     => 40000,
				'includes' => "Полы: стяжка, ламинат, пластиковый плинтус.\nСтены: штукатурка, шпатлёвка, обои.\nСанузел: стандартная плитка.\nПотолки: натяжные.\nСтандартный монтаж: электрики, водопровода, канализации.",
			),
			array(
				'key'      => 'standard',
				'label'    => 'Стандарт',
				'subtitle' => 'Классический ремонт',
				'rate'     => 50000,
				'includes' => "Полы: стяжка, ламинат, инженерная доска, пластиковый плинтус.\nСтены: штукатурка, шпатлёвка, обои.\nСанузел: плитка размером до 1 метра.\nПотолки: натяжные, гипсокартон.\nУлучшенный монтаж: электрики, водопровода, канализации.",
			),
			array(
				'key'      => 'premium',
				'label'    => 'Премиум',
				'subtitle' => 'Дизайнерский ремонт',
				'rate'     => 60000,
				'includes' => "Полы: стяжка, наливной пол, фанера, паркетная доска, МДФ-плинтус, скрытый плинтус.\nСтены: штукатурка, шпатлёвка, покраска под проявочный свет.\nОкна: откосы под покраску, подготовка основания под деревянный или каменный подоконник.\nСанузел: нестандартная плитка.\nПотолки: гипсокартон под покраску.\nПремиум-монтаж: электрики, водопровода, канализации и отопления.",
			),
		),
	);
}

/** Текущие настройки калькулятора (с подстановкой значений по умолчанию). */
function rmt_calc_settings() {
	$saved = get_option( 'rmt_calc', array() );
	$s     = wp_parse_args( is_array( $saved ) ? $saved : array(), rmt_calc_defaults() );
	if ( empty( $s['types'] ) ) {
		$s['types'] = rmt_calc_defaults()['types'];
	}
	foreach ( $s['types'] as &$t ) {
		$t = wp_parse_args( $t, array( 'subtitle' => '', 'includes' => '' ) );
	}
	return $s;
}

/** Состав работ тарифа — массив строк. */
function rmt_calc_includes( $type ) {
	return array_values( array_filter( array_map( 'trim', explode( "\n", (string) ( $type['includes'] ?? '' ) ) ) ) );
}

/** Подписи тарифов для формы заявки: key => label. */
function rmt_repair_type_options() {
	$out = array();
	foreach ( rmt_calc_settings()['types'] as $t ) {
		$out[ $t['key'] ] = $t['label'];
	}
	$out['unknown'] = __( 'Пока не знаю', 'remont' );
	return $out;
}

/** Формат суммы: 1 250 000 ₽. */
function rmt_money( $value ) {
	return number_format_i18n( (float) $value, 0 ) . ' ₽';
}

add_action( 'admin_menu', 'rmt_calc_admin_menu' );
function rmt_calc_admin_menu() {
	add_options_page( __( 'Калькулятор и заявки', 'remont' ), __( 'Калькулятор и заявки', 'remont' ), 'manage_options', 'rmt-calc', 'rmt_calc_render_page' );
}

add_action( 'admin_init', 'rmt_calc_register_settings' );
function rmt_calc_register_settings() {
	register_setting(
		'rmt_calc_group',
		'rmt_calc',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'rmt_calc_sanitize',
			'default'           => rmt_calc_defaults(),
		)
	);
	register_setting(
		'rmt_calc_group',
		'rmt_lead_notify_email',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_email',
			'default'           => '',
		)
	);
}

function rmt_calc_sanitize( $input ) {
	$input = is_array( $input ) ? $input : array();
	$out   = array(
		'demo'         => ! empty( $input['demo'] ),
		'min_area'     => max( 1, absint( $input['min_area'] ?? 10 ) ),
		'max_area'     => max( 10, absint( $input['max_area'] ?? 1000 ) ),
		'default_area' => max( 1, absint( $input['default_area'] ?? 60 ) ),
		'types'        => array(),
	);
	$out['default_area'] = min( max( $out['default_area'], $out['min_area'] ), $out['max_area'] );

	foreach ( (array) ( $input['types'] ?? array() ) as $i => $row ) {
		$label = sanitize_text_field( $row['label'] ?? '' );
		if ( '' === $label ) {
			continue; // Пустое название = удалить тариф.
		}
		$key            = sanitize_key( $row['key'] ?? '' );
		$out['types'][] = array(
			'key'      => '' !== $key ? $key : 'tariff-' . ( (int) $i + 1 ),
			'label'    => $label,
			'subtitle' => sanitize_text_field( $row['subtitle'] ?? '' ),
			'rate'     => max( 0, (float) str_replace( array( ' ', ',' ), array( '', '.' ), (string) ( $row['rate'] ?? 0 ) ) ),
			'includes' => sanitize_textarea_field( $row['includes'] ?? '' ),
		);
	}
	return $out;
}

function rmt_calc_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s    = rmt_calc_settings();
	$rows = array_merge( $s['types'], array( array() ) );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Калькулятор и заявки', 'remont' ); ?></h1>
		<p><?php esc_html_e( 'Формула: площадь × ставка выбранного тарифа. На сайте показывается одна фиксированная сумма.', 'remont' ); ?></p>
		<p><?php esc_html_e( 'Чтобы удалить тариф, очистите его название. Пустая строка внизу таблицы — для нового тарифа.', 'remont' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'rmt_calc_group' ); ?>

			<h2><?php esc_html_e( 'Тарифы ремонта', 'remont' ); ?></h2>
			<table class="widefat striped" style="max-width:1100px">
				<thead><tr>
					<th style="width:16%"><?php esc_html_e( 'Название', 'remont' ); ?></th>
					<th style="width:18%"><?php esc_html_e( 'Подзаголовок', 'remont' ); ?></th>
					<th style="width:12%"><?php esc_html_e( 'Ставка, ₽ за м²', 'remont' ); ?></th>
					<th><?php esc_html_e( 'Состав работ (по пункту в строке)', 'remont' ); ?></th>
					<th style="width:10%"><?php esc_html_e( 'Служебный ключ', 'remont' ); ?></th>
				</tr></thead>
				<tbody>
				<?php
				foreach ( $rows as $i => $row ) :
					$base = 'rmt_calc[types][' . $i . ']';
					?>
					<tr>
						<td><input type="text" style="width:100%" name="<?php echo esc_attr( $base . '[label]' ); ?>" value="<?php echo esc_attr( $row['label'] ?? '' ); ?>" aria-label="<?php esc_attr_e( 'Название', 'remont' ); ?>"></td>
						<td><input type="text" style="width:100%" name="<?php echo esc_attr( $base . '[subtitle]' ); ?>" value="<?php echo esc_attr( $row['subtitle'] ?? '' ); ?>" aria-label="<?php esc_attr_e( 'Подзаголовок', 'remont' ); ?>"></td>
						<td><input type="number" step="100" min="0" style="width:100%" name="<?php echo esc_attr( $base . '[rate]' ); ?>" value="<?php echo esc_attr( $row['rate'] ?? '' ); ?>" aria-label="<?php esc_attr_e( 'Ставка за м²', 'remont' ); ?>"></td>
						<td><textarea rows="5" style="width:100%" name="<?php echo esc_attr( $base . '[includes]' ); ?>" aria-label="<?php esc_attr_e( 'Состав работ', 'remont' ); ?>"><?php echo esc_textarea( $row['includes'] ?? '' ); ?></textarea></td>
						<td><input type="text" style="width:100%" name="<?php echo esc_attr( $base . '[key]' ); ?>" value="<?php echo esc_attr( $row['key'] ?? '' ); ?>" aria-label="<?php esc_attr_e( 'Служебный ключ', 'remont' ); ?>"></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Площадь, м²', 'remont' ); ?></th>
					<td>
						<?php esc_html_e( 'от', 'remont' ); ?> <input type="number" min="1" name="rmt_calc[min_area]" value="<?php echo esc_attr( $s['min_area'] ); ?>" class="small-text">
						<?php esc_html_e( 'до', 'remont' ); ?> <input type="number" min="10" name="rmt_calc[max_area]" value="<?php echo esc_attr( $s['max_area'] ); ?>" class="small-text">
						<?php esc_html_e( 'по умолчанию', 'remont' ); ?> <input type="number" min="1" name="rmt_calc[default_area]" value="<?php echo esc_attr( $s['default_area'] ); ?>" class="small-text">
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Демо-режим', 'remont' ); ?></th>
					<td><label><input type="checkbox" name="rmt_calc[demo]" value="1" <?php checked( $s['demo'] ); ?>> <?php esc_html_e( 'Показывать на сайте, что ставки демонстрационные', 'remont' ); ?></label></td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Уведомления о заявках', 'remont' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="rmt-notify"><?php esc_html_e( 'Email для уведомлений', 'remont' ); ?></label></th>
					<td>
						<input id="rmt-notify" type="email" class="regular-text" name="rmt_lead_notify_email" value="<?php echo esc_attr( get_option( 'rmt_lead_notify_email', '' ) ); ?>">
						<p class="description"><?php esc_html_e( 'Необязательно. Заявки в любом случае сохраняются в разделе «Заявки». Для отправки писем на сервере должна быть настроена почта.', 'remont' ); ?></p>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
