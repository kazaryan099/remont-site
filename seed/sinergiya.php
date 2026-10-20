<?php
/**
 * Реальный проект «Синергия»: фото, страница проекта, фото главной и услуг.
 * Также убирает лишние демо-проекты и неиспользуемые демо-фото.
 *
 * Отдельный запуск: docker compose run --rm wpcli wp eval-file /seed/sinergiya.php
 * Вызывается и из seed.php. Повторный запуск дублей не создаёт.
 */

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

// Порядок = порядок в галерее проекта. Сгруппировано по помещениям.
$rmt_sin_photos = array(
	'sinergiya-kuhnya-stolovaya'   => 'Кухня-столовая с островом и подвесным светильником',
	'sinergiya-gostinaya'          => 'Гостиная с угловым диваном и стеллажом из ореха',
	'sinergiya-gostinaya-stellazh' => 'Встроенный стеллаж из ореха и ТВ-зона',
	'sinergiya-kamin'              => 'Камин в облицовке под мрамор',
	'sinergiya-kuhnya'             => 'Белая кухня со встроенной техникой',
	'sinergiya-stolovaya'          => 'Обеденная зона у окна',
	'sinergiya-stoleshnica'        => 'Обеденный стол из ореха',
	'sinergiya-kuhnya-moyka'       => 'Мойка с подсветкой рабочей зоны',
	'sinergiya-prihozhaya'         => 'Прихожая: круглое зеркало на реечной стене',
	'sinergiya-koridor'            => 'Коридор с туалетным столиком',
	'sinergiya-garderob'           => 'Гардеробная с открытым хранением',
	'sinergiya-sanuzel-gostevoy'   => 'Гостевой санузел с накладной раковиной',
	'sinergiya-spalnya-krovat'     => 'Мастер-спальня: кровать с мягким изголовьем',
	'sinergiya-spalnya-komod'      => 'Комод под окном в мастер-спальне',
	'sinergiya-vannaya'            => 'Ванная с отдельностоящей ванной и световыми панелями',
	'sinergiya-vannaya-okno'       => 'Ванная с раковиной-колонной у окна',
	'sinergiya-vannaya-tumba'      => 'Ванная с деревянной тумбой и полотенцесушителем',
	'sinergiya-vannaya-zerkalo'    => 'Зеркало с контурной подсветкой',
	'sinergiya-gostevaya-spalnya'  => 'Гостевая спальня с деревянными панелями',
	'sinergiya-detskaya'           => 'Детская с кроваткой и диваном',
	'sinergiya-dush'               => 'Душевая с чёрной фурнитурой',
	'sinergiya-sanuzel'            => 'Санузел с подвесной тумбой',
	'sinergiya-sanuzel-panel'      => 'Санузел с деревянными панелями и нишей',
);

$rmt_sin_import = function ( $name, $alt ) {
	$found = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'meta_key'    => '_rmt_seed_file',
			'meta_value'  => $name,
			'fields'      => 'ids',
			'numberposts' => 1,
		)
	);
	if ( $found ) {
		return $found[0];
	}
	$src = '/seed/images/sinergiya/' . $name . '.jpg';
	if ( ! file_exists( $src ) ) {
		WP_CLI::warning( "Нет файла {$src}" );
		return 0;
	}
	$tmp = wp_tempnam( $name . '.jpg' );
	copy( $src, $tmp );
	$id = media_handle_sideload( array( 'name' => $name . '.jpg', 'tmp_name' => $tmp ), 0, $alt );
	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( $name . ': ' . $id->get_error_message() );
		return 0;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $id, '_rmt_seed_file', $name );
	WP_CLI::log( "Фото: {$name}" );
	return $id;
};

$rmt_sin = array();
foreach ( $rmt_sin_photos as $name => $alt ) {
	$rmt_sin[ $name ] = $rmt_sin_import( $name, $alt );
}
$rmt_sin = array_filter( $rmt_sin );

/* ---------- Проект ---------- */

$rmt_sin_project = get_page_by_path( 'sinergiya', OBJECT, 'project' );
if ( ! $rmt_sin_project ) {
	$rmt_sin_id = wp_insert_post(
		wp_slash(
			array(
				'post_type'    => 'project',
				'post_name'    => 'sinergiya',
				'post_status'  => 'publish',
				'post_title'   => 'Проект «Синергия»',
				'post_excerpt' => 'Двухуровневая квартира в светлой палитре: кухня-гостиная с островом и камином, мастер-спальня с ванной, гостевая спальня, детская и гардеробная.',
				'post_content' => "<p>Интерьер построен на сочетании белых и тёплых бежевых поверхностей с тёмным орехом. Светлые стены и лёгкие шторы оставляют много воздуха, а дерево и камень добавляют глубины.</p>\n<h2>Кухня-гостиная</h2>\n<p>Остров объединяет кухню и обеденную зону, над столом — графичный подвесной светильник. В гостиной — камин в облицовке под мрамор и встроенный стеллаж из ореха с ТВ-зоной.</p>\n<h2>Приватная зона</h2>\n<p>В приватной зоне — мастер-спальня и ванная комната с отдельностоящей ванной, световыми панелями и зеркалом с контурной подсветкой. Гостевая спальня отделана деревянными панелями, в детской — спокойная светлая палитра.</p>\n<h2>Хранение и детали</h2>\n<p>Гардеробная с открытым хранением, реечные стеновые панели в прихожей, подсветка ступеней лестницы.</p>\n<p><em>Площадь, сроки и состав работ будут добавлены после уточнения данных по объекту.</em></p>",
				'menu_order'   => -1,
			)
		),
		true
	);
	if ( is_wp_error( $rmt_sin_id ) ) {
		WP_CLI::error( $rmt_sin_id->get_error_message() );
	}
	update_post_meta( $rmt_sin_id, '_rmt_rooms', 'Двухуровневая квартира' );
	update_post_meta( $rmt_sin_id, '_rmt_tasks', "Кухня-гостиная с островом\nКамин в облицовке под мрамор\nВстроенные стеллажи из ореха\nРеечные панели в прихожей\nДве ванные комнаты и гостевой санузел\nГардеробная" );
	WP_CLI::log( 'Создан проект «Синергия»' );
} else {
	$rmt_sin_id = $rmt_sin_project->ID;
}

update_post_meta( $rmt_sin_id, '_rmt_gallery', implode( ',', array_values( $rmt_sin ) ) );
if ( ! empty( $rmt_sin['sinergiya-kuhnya-stolovaya'] ) ) {
	set_post_thumbnail( $rmt_sin_id, $rmt_sin['sinergiya-kuhnya-stolovaya'] );
}
$rmt_sin_term = get_term_by( 'slug', 'dizajnerskij-remont', 'repair_type' );
if ( $rmt_sin_term ) {
	wp_set_object_terms( $rmt_sin_id, array( $rmt_sin_term->term_id ), 'repair_type' );
}

/* ---------- Фото главной, услуг и страниц ---------- */

$rmt_pick = fn( $name ) => $rmt_sin[ $name ] ?? 0;

set_theme_mod( 'hero_image', $rmt_pick( 'sinergiya-gostinaya' ) );
set_theme_mod( 'quality_image', $rmt_pick( 'sinergiya-vannaya' ) );
set_theme_mod( 'cta_image', $rmt_pick( 'sinergiya-detskaya' ) );
set_theme_mod( 'featured_project', $rmt_sin_id );

$rmt_thumbs = array(
	array( 'service', 'kosmeticheskij-remont', 'sinergiya-gostevaya-spalnya' ),
	array( 'service', 'dizajnerskij-remont', 'sinergiya-spalnya-krovat' ),
	array( 'service', 'remont-v-novostrojke', 'sinergiya-dush' ),
	array( 'service', 'remont-pod-klyuch', 'sinergiya-kuhnya' ),
	array( 'page', 'o-kompanii', 'sinergiya-kuhnya-stolovaya' ),
	array( 'page', 'kontakty', 'sinergiya-koridor' ),
);
foreach ( $rmt_thumbs as list( $type, $slug, $photo ) ) {
	$post = get_page_by_path( $slug, OBJECT, $type );
	if ( $post && $rmt_pick( $photo ) ) {
		set_post_thumbnail( $post, $rmt_pick( $photo ) );
	}
}

/* ---------- Уборка лишних демо-материалов ---------- */

foreach ( array( 'kvartira-s-kuhnej-gostinoj', 'obnovlenie-gostinoj', 'trehkomnatnaya-kvartira' ) as $slug ) {
	$old = get_page_by_path( $slug, OBJECT, 'project' );
	if ( $old && get_post_meta( $old->ID, '_rmt_is_demo', true ) ) {
		wp_delete_post( $old->ID, true );
		WP_CLI::log( "Удалён демо-проект: {$slug}" );
	}
}

$rmt_unused = array( 'hero-loft', 'living-minimal', 'living-bright', 'living-white', 'bedroom-wood', 'bedroom-light', 'bath-window', 'bath-white', 'kitchen-island', 'kitchen-wood', 'kitchen-open', 'room-empty', 'works-frame', 'works-site', 'office-white' );
foreach ( $rmt_unused as $name ) {
	foreach ( get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_rmt_seed_file', 'meta_value' => $name, 'fields' => 'ids', 'numberposts' => -1 ) ) as $att ) {
		wp_delete_attachment( $att, true );
		WP_CLI::log( "Удалено демо-фото: {$name}" );
	}
}

WP_CLI::success( 'Проект «Синергия» подключён.' );
