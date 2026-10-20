<?php
/**
 * Проекты из портфолио студий: Повадино (2M Architects), ЖК «Эвер» и ЖК «Вереск» (ТИХО).
 * Все изображения — визуализации дизайн-проектов. Заменяют демо-проекты и демо-фото CC0.
 *
 * Отдельный запуск: docker compose run --rm wpcli wp eval-file /seed/portfolio.php
 * Вызывается и из seed.php. Повторный запуск дублей не создаёт.
 */

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$rmt_projects = array(
	array(
		'slug'    => 'povadino',
		'dir'     => 'povadino',
		'order'   => 0,
		'title'   => 'Частный дом в Повадино',
		'type'    => 'dizajnerskij-remont',
		'cover'   => 0,
		'excerpt' => 'Интерьер загородного дома для семьи из четырёх человек: общая зона со вторым светом, приватный второй этаж, спа-зона с фитобочкой.',
		'meta'    => array(
			'area'     => '382',
			'rooms'    => 'Два этажа',
			'building' => 'Частный дом',
			'location' => 'Московская область',
			'stage'    => 'В реализации',
			'tasks'    => "Интерьер и ландшафт в одном проекте\nОбщая зона со вторым светом\nГостевая спальня на первом этаже\nСпа-зона с фитобочкой\nЗона отдыха с выходом на террасу\nДеревянные потолки и натуральные материалы",
		),
		'content' => "<p>Дом для семьи из четырёх человек, которая любит принимать гостей. Интерьер и ландшафт разрабатывались вместе, как единый проект.</p>\n<h2>Планировка</h2>\n<p>Первый этаж — общая зона: гостиная, кухня-столовая и гостевая спальня. Второй этаж приватный, только для семьи. Второй свет связывает этажи в одно пространство.</p>\n<h2>Особенности</h2>\n<p>Спа-зона с фитобочкой и зона отдыха с выходом на террасу второго этажа. Дом стоит рядом с лесом, поэтому в отделке — натуральные материалы: деревянные потолки, природные цвета, контрастные детали.</p>\n<p>Дизайн-проект: студия 2M Architects. Изображения — визуализации проекта.</p>",
		'alts'    => array(
			'Гостиная со вторым светом', 'Гостиная: лестница и ТВ-зона', 'Зона отдыха у окна', 'Лестница на второй этаж и камин',
			'Кухня с островом и обеденной зоной', 'Кухня с барной стойкой', 'Кухня-столовая', 'Обеденная зона у окна',
			'Книжный стеллаж в холле', 'Прихожая', 'Санузел первого этажа с деревянной тумбой', 'Санузел: душевая',
			'Гостевой санузел', 'Галерея второго этажа над вторым светом', 'Зона отдыха под деревянным потолком', 'Спальня с фотообоями «лес»',
			'Спальня: кровать у акцентной стены', 'Спальня: вход и гардероб', 'Кабинет под скатной крышей', 'Зона отдыха с ТВ',
			'Диван в кабинете', 'Спа-зона с фитобочкой', 'Отдельностоящая ванна у окна', 'Спа-зона: ванна и душ',
			'Фитобочка и хранение', 'Детская спальня', 'Детская спальня с терракотовым акцентом', 'Рабочее место в детской',
			'Санузел второго этажа', 'Душевая со стеклянной перегородкой', 'Душевая с подсвеченной нишей',
		),
	),
	array(
		'slug'    => 'zhk-ever',
		'dir'     => 'glava',
		'order'   => 1,
		'title'   => 'ЖК «Эвер»',
		'type'    => 'dizajnerskij-remont',
		'cover'   => 4,
		'excerpt' => 'Квартира в Москве для мамы с двумя дочками: нейтральная воздушная основа и мягкие акценты.',
		'meta'    => array(
			'rooms'    => 'Квартира',
			'location' => 'Москва',
			'tasks'    => "Небольшая перепланировка у мастер-спальни\nУдобная гардеробная\nОтдельный доступ к мастер-ванной\nНейтральная практичная отделка\nДетская с росписью на стене",
		),
		'content' => "<p>Квартира для мамы с двумя дочками.</p>\n<h2>Планировка</h2>\n<p>Планировку изменили незначительно — в зоне мастер-спальни, чтобы гардеробная стала удобнее для хранения, а в мастер-ванную был прямой доступ.</p>\n<h2>Отделка</h2>\n<p>Нейтральная, практичная и воздушная основа, а мягкие акценты подчёркивают характер семьи.</p>\n<p>Дизайн-проект: студия «ТИХО». Изображения — визуализации проекта.</p>",
		'alts'    => array(
			'Детская с росписью на стене', 'Прихожая со встроенным шкафом', 'Кухня-столовая', 'Кухня: обеденная зона',
			'Кухня-гостиная', 'Диван и кофейный столик', 'Санузел с душевой', 'Детская: рабочее место у окна',
			'Детская с росписью «ветви»', 'Детская: диван у окна', 'Мастер-спальня', 'Мастер-спальня: изголовье кровати',
			'Ванная комната',
		),
	),
	array(
		'slug'    => 'zhk-veresk',
		'dir'     => 'veresk',
		'order'   => 2,
		'title'   => 'ЖК «Вереск»',
		'type'    => 'remont-v-novostrojke',
		'cover'   => 0,
		'excerpt' => 'Квартира в современном жилом комплексе для пары с подростком: спокойная гамма и классические детали.',
		'meta'    => array(
			'rooms'    => 'Раздельные комнаты',
			'building' => 'Новостройка',
			'location' => 'Москва',
			'tasks'    => "Планировка застройщика без изменений\nДополнительные зоны хранения\nСкрытые недостатки несущих конструкций\nКлассические молдинги и панели\nКухня с зелёными фасадами",
		),
		'content' => "<p>Квартира в современном жилом комплексе для взрослой пары с подростком.</p>\n<h2>Планировка</h2>\n<p>Исходную планировку застройщика с раздельными комнатами сохранили. Добавили зоны хранения и спрятали недостатки несущих конструкций.</p>\n<h2>Интерьер</h2>\n<p>Спокойная цветовая гамма и классические элементы, которые нравятся семье: молдинги, стеновые панели, люстры.</p>\n<p>Дизайн-проект: студия «ТИХО». Изображения — визуализации проекта.</p>",
		'alts'    => array(
			'Гостиная с молдингами', 'Гостиная: диван и столик', 'ТВ-зона с деревянными панелями', 'Кухня: фартук под мрамор',
			'Кухня-столовая с зелёными фасадами', 'Санузел с подсвеченным зеркалом', 'Санузел: раковина на консоли', 'Комната подростка',
			'Спальня', 'Место для отдыха у окна', 'Спальня: кровать и бра', 'Ванная с душевой',
		),
	),
);

$rmt_import = function ( $file, $name, $alt ) {
	$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_rmt_seed_file', 'meta_value' => $name, 'fields' => 'ids', 'numberposts' => 1 ) );
	if ( $found ) {
		return $found[0];
	}
	if ( ! file_exists( $file ) ) {
		WP_CLI::warning( "Нет файла {$file}" );
		return 0;
	}
	$tmp = wp_tempnam( $name . '.jpg' );
	copy( $file, $tmp );
	$id = media_handle_sideload( array( 'name' => $name . '.jpg', 'tmp_name' => $tmp ), 0, $alt );
	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( $name . ': ' . $id->get_error_message() );
		return 0;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $id, '_rmt_seed_file', $name );
	return $id;
};

$rmt_first_images = array();
foreach ( $rmt_projects as $p ) {
	$ids = array();
	foreach ( $p['alts'] as $i => $alt ) {
		$name  = sprintf( '%s-%02d', $p['dir'], $i );
		$ids[] = $rmt_import( '/seed/images/' . $p['dir'] . '/' . $name . '.jpg', $name, $alt );
	}
	$ids = array_values( array_filter( $ids ) );
	WP_CLI::log( "Фото {$p['title']}: " . count( $ids ) );
	$rmt_first_images[ $p['dir'] ] = $ids;

	$existing = get_page_by_path( $p['slug'], OBJECT, 'project' );
	if ( $existing ) {
		$post_id = $existing->ID;
	} else {
		$post_id = wp_insert_post(
			wp_slash(
				array(
					'post_type'    => 'project',
					'post_name'    => $p['slug'],
					'post_status'  => 'publish',
					'post_title'   => $p['title'],
					'post_excerpt' => $p['excerpt'],
					'post_content' => $p['content'],
					'menu_order'   => $p['order'],
				)
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			WP_CLI::warning( $post_id->get_error_message() );
			continue;
		}
		foreach ( $p['meta'] as $k => $v ) {
			update_post_meta( $post_id, '_rmt_' . $k, $v );
		}
		update_post_meta( $post_id, '_rmt_is_render', '1' );
		$term = get_term_by( 'slug', $p['type'], 'repair_type' );
		if ( $term ) {
			wp_set_object_terms( $post_id, array( $term->term_id ), 'repair_type' );
		}
		WP_CLI::log( "Создан проект: {$p['title']}" );
	}
	update_post_meta( $post_id, '_rmt_gallery', implode( ',', $ids ) );
	if ( isset( $ids[ $p['cover'] ] ) ) {
		set_post_thumbnail( $post_id, $ids[ $p['cover'] ] );
	}
}

// Капитальный ремонт: вместо демо-фото черновых работ — кадр из Повадино.
$rmt_capital = get_page_by_path( 'kapitalnyj-remont', OBJECT, 'service' );
if ( $rmt_capital && ! empty( $rmt_first_images['povadino'][14] ) ) {
	set_post_thumbnail( $rmt_capital, $rmt_first_images['povadino'][14] );
}

/* ---------- Уборка: демо-проекты и все демо-фото CC0 ---------- */

foreach ( get_posts( array( 'post_type' => 'project', 'numberposts' => -1, 'meta_key' => '_rmt_is_demo', 'meta_value' => '1', 'fields' => 'ids' ) ) as $demo ) {
	WP_CLI::log( 'Удалён демо-проект: ' . get_the_title( $demo ) );
	wp_delete_post( $demo, true );
}

$rmt_cc0 = array( 'living-concrete', 'kitchen-window', 'bath-shower', 'bedroom-grey', 'bath-tile', 'bath-dark', 'bedroom-city', 'living-black-window', 'kitchen-bar', 'bedroom-warm', 'works-floor' );
foreach ( $rmt_cc0 as $name ) {
	foreach ( get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_rmt_seed_file', 'meta_value' => $name, 'fields' => 'ids', 'numberposts' => -1 ) ) as $att ) {
		wp_delete_attachment( $att, true );
		WP_CLI::log( "Удалено демо-фото: {$name}" );
	}
}

WP_CLI::success( 'Портфолио обновлено.' );
