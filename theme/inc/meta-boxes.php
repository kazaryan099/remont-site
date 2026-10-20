<?php
/**
 * Дополнительные поля услуг и проектов (без сторонних плагинов).
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

/**
 * Описание полей по типам записей.
 * type: text | textarea | lines (по одному пункту в строке) | checkbox | select | gallery
 */
function rmt_meta_fields() {
	$calc_types = array( '' => __( '— не связано —', 'remont' ) );
	foreach ( rmt_calc_settings()['types'] as $t ) {
		$calc_types[ $t['key'] ] = $t['label'];
	}

	return array(
		'service' => array(
			'title'  => __( 'Параметры услуги', 'remont' ),
			'fields' => array(
				'kicker'    => array( 'label' => __( 'Надзаголовок на карточке', 'remont' ), 'type' => 'text', 'hint' => __( 'Короткая строка над названием, например «Без перепланировки».', 'remont' ) ),
				'price'     => array( 'label' => __( 'Цена (текст)', 'remont' ), 'type' => 'text', 'hint' => __( 'Например «от 0 000 ₽ за м²». Оставьте пустым, если цена не указывается.', 'remont' ) ),
				'duration'  => array( 'label' => __( 'Срок (текст)', 'remont' ), 'type' => 'text', 'hint' => __( 'Например «от 1 месяца». Оставьте пустым, если не знаете.', 'remont' ) ),
				'includes'  => array( 'label' => __( 'Что входит', 'remont' ), 'type' => 'lines', 'hint' => __( 'По одному пункту в строке.', 'remont' ) ),
				'calc_type' => array( 'label' => __( 'Тип в калькуляторе', 'remont' ), 'type' => 'select', 'options' => $calc_types, 'hint' => __( 'Калькулятор на странице услуги откроется с этим типом ремонта.', 'remont' ) ),
			),
		),
		'project' => array(
			'title'  => __( 'Характеристики объекта', 'remont' ),
			'fields' => array(
				'is_demo'  => array( 'label' => __( 'Демонстрационный пример', 'remont' ), 'type' => 'checkbox', 'hint' => __( 'На сайте будет пометка «Демо». Снимите, когда замените данные реальными.', 'remont' ) ),
				'is_render' => array( 'label' => __( 'Изображения — визуализации', 'remont' ), 'type' => 'checkbox', 'hint' => __( 'Отметьте, если в галерее 3D-визуализации дизайн-проекта, а не фото готового ремонта. На сайте появится пометка «Визуализация».', 'remont' ) ),
				'stage'    => array( 'label' => __( 'Стадия', 'remont' ), 'type' => 'text', 'hint' => __( 'Например «В реализации» или «Завершён».', 'remont' ) ),
				'area'     => array( 'label' => __( 'Площадь, м²', 'remont' ), 'type' => 'text' ),
				'duration' => array( 'label' => __( 'Срок работ', 'remont' ), 'type' => 'text' ),
				'rooms'    => array( 'label' => __( 'Комнат / планировка', 'remont' ), 'type' => 'text' ),
				'building' => array( 'label' => __( 'Тип дома', 'remont' ), 'type' => 'text', 'hint' => __( 'Например «Новостройка» или «Вторичное жильё».', 'remont' ) ),
				'location' => array( 'label' => __( 'Район / город', 'remont' ), 'type' => 'text' ),
				'year'     => array( 'label' => __( 'Год', 'remont' ), 'type' => 'text' ),
				'tasks'    => array( 'label' => __( 'Что сделано', 'remont' ), 'type' => 'lines', 'hint' => __( 'По одному пункту в строке.', 'remont' ) ),
				'gallery'  => array( 'label' => __( 'Галерея', 'remont' ), 'type' => 'gallery' ),
			),
		),
	);
}

/**
 * Чтение поля: rmt_meta( 'area' ) или rmt_meta( 'area', $post_id ).
 */
function rmt_meta( $key, $post_id = null ) {
	return get_post_meta( $post_id ?: get_the_ID(), '_rmt_' . $key, true );
}

/** Поле-список: массив непустых строк. */
function rmt_meta_lines( $key, $post_id = null ) {
	$raw = (string) rmt_meta( $key, $post_id );
	return array_values( array_filter( array_map( 'trim', explode( "\n", $raw ) ) ) );
}

/** ID изображений галереи проекта. */
function rmt_gallery_ids( $post_id = null ) {
	$raw = (string) rmt_meta( 'gallery', $post_id );
	return array_values( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );
}

add_action( 'add_meta_boxes', 'rmt_add_meta_boxes' );
function rmt_add_meta_boxes() {
	foreach ( rmt_meta_fields() as $post_type => $box ) {
		add_meta_box( 'rmt_meta_' . $post_type, $box['title'], 'rmt_render_meta_box', $post_type, 'normal', 'high', array( 'post_type' => $post_type ) );
	}
}

function rmt_render_meta_box( $post, $box ) {
	$fields = rmt_meta_fields()[ $box['args']['post_type'] ]['fields'];
	wp_nonce_field( 'rmt_save_meta', 'rmt_meta_nonce' );

	echo '<table class="form-table rmt-meta" role="presentation"><tbody>';
	foreach ( $fields as $key => $f ) {
		$id    = 'rmt_' . $key;
		$value = get_post_meta( $post->ID, '_rmt_' . $key, true );
		echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $f['label'] ) . '</label></th><td>';

		switch ( $f['type'] ) {
			case 'textarea':
			case 'lines':
				echo '<textarea class="large-text" rows="5" id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '">' . esc_textarea( $value ) . '</textarea>';
				break;
			case 'checkbox':
				echo '<input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" value="1" ' . checked( $value, '1', false ) . '>';
				break;
			case 'select':
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '">';
				foreach ( $f['options'] as $opt => $label ) {
					echo '<option value="' . esc_attr( $opt ) . '" ' . selected( $value, $opt, false ) . '>' . esc_html( $label ) . '</option>';
				}
				echo '</select>';
				break;
			case 'gallery':
				$ids = array_filter( array_map( 'absint', explode( ',', (string) $value ) ) );
				echo '<input type="hidden" id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" value="' . esc_attr( implode( ',', $ids ) ) . '">';
				echo '<div class="rmt-gallery-preview" data-target="' . esc_attr( $id ) . '">';
				foreach ( $ids as $img_id ) {
					echo wp_get_attachment_image( $img_id, 'thumbnail' );
				}
				echo '</div><p><button type="button" class="button rmt-gallery-pick" data-target="' . esc_attr( $id ) . '">' . esc_html__( 'Выбрать фотографии', 'remont' ) . '</button> ';
				echo '<button type="button" class="button-link rmt-gallery-clear" data-target="' . esc_attr( $id ) . '">' . esc_html__( 'Очистить', 'remont' ) . '</button></p>';
				break;
			default:
				echo '<input type="text" class="regular-text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" value="' . esc_attr( $value ) . '">';
		}

		if ( ! empty( $f['hint'] ) ) {
			echo '<p class="description">' . esc_html( $f['hint'] ) . '</p>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

add_action( 'save_post', 'rmt_save_meta', 10, 2 );
function rmt_save_meta( $post_id, $post ) {
	$all = rmt_meta_fields();
	if ( ! isset( $all[ $post->post_type ], $_POST['rmt_meta_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['rmt_meta_nonce'] ) ), 'rmt_save_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( $all[ $post->post_type ]['fields'] as $key => $f ) {
		$name = 'rmt_' . $key;
		$raw  = isset( $_POST[ $name ] ) ? wp_unslash( $_POST[ $name ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- санитизация ниже по типу поля.

		switch ( $f['type'] ) {
			case 'textarea':
			case 'lines':
				$value = sanitize_textarea_field( $raw );
				break;
			case 'checkbox':
				$value = '1' === $raw ? '1' : '';
				break;
			case 'select':
				$value = array_key_exists( $raw, $f['options'] ) ? $raw : '';
				break;
			case 'gallery':
				$value = implode( ',', array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );
				break;
			default:
				$value = sanitize_text_field( $raw );
		}

		if ( '' === $value ) {
			delete_post_meta( $post_id, '_rmt_' . $key );
		} else {
			update_post_meta( $post_id, '_rmt_' . $key, $value );
		}
	}
}

/**
 * Выбор фото галереи через стандартную медиатеку WordPress.
 */
add_action( 'admin_enqueue_scripts', 'rmt_admin_gallery_script' );
function rmt_admin_gallery_script( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'project' !== get_current_screen()->post_type ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'rmt-admin-gallery', RMT_URI . '/assets/js/admin-gallery.js', array( 'jquery' ), RMT_VERSION, true );
	wp_add_inline_style( 'wp-admin', '.rmt-gallery-preview{display:flex;flex-wrap:wrap;gap:6px}.rmt-gallery-preview img{width:80px;height:80px;object-fit:cover}' );
}
