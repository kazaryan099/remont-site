<?php
/**
 * Переход с пяти услуг на две: «Ремонт квартир» и «Ремонт домов».
 * - создаёт две услуги и одноимённые виды ремонта (фильтр портфолио);
 * - раскладывает проекты: квартиры — в «Ремонт квартир», дома — в «Ремонт домов»;
 * - удаляет прежние пять услуг и их виды ремонта;
 * - пересобирает подменю «Услуги» в главном меню.
 *
 * Отдельный запуск: docker compose run --rm wpcli wp eval-file /seed/services-v2.php
 * Вызывается и из seed.php. Повторный запуск ничего не дублирует.
 */

defined( 'ABSPATH' ) || exit;

$rmt_services = require __DIR__ . '/services.php';
$rmt_old      = array( 'kosmeticheskij-remont', 'kapitalnyj-remont', 'dizajnerskij-remont', 'remont-v-novostrojke', 'remont-pod-klyuch' );
$rmt_projects = array(
	'sinergiya'  => 'remont-domov',
	'zhk-ever'   => 'remont-kvartir',
	'zhk-veresk' => 'remont-kvartir',
	'povadino'   => 'remont-domov',
);

$rmt_photo = function ( $name ) {
	$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_rmt_seed_file', 'meta_value' => $name, 'fields' => 'ids', 'numberposts' => 1 ) );
	return $found ? $found[0] : 0;
};

/* ---------- Новые услуги и виды ремонта ---------- */

$rmt_ids = array();
foreach ( $rmt_services as $rmt_order => $rmt_s ) {
	$rmt_term = term_exists( $rmt_s['slug'], 'repair_type' );
	if ( ! $rmt_term ) {
		$rmt_term = wp_insert_term( $rmt_s['title'], 'repair_type', array( 'slug' => $rmt_s['slug'] ) );
	}

	$rmt_post = get_page_by_path( $rmt_s['slug'], OBJECT, 'service' );
	if ( $rmt_post ) {
		$rmt_ids[ $rmt_s['slug'] ] = $rmt_post->ID; // Не перезаписываем правки администратора.
		continue;
	}
	$rmt_id = wp_insert_post(
		wp_slash(
			array(
				'post_type'    => 'service',
				'post_name'    => $rmt_s['slug'],
				'post_status'  => 'publish',
				'post_title'   => $rmt_s['title'],
				'post_excerpt' => $rmt_s['excerpt'],
				'post_content' => $rmt_s['content'],
				'menu_order'   => $rmt_order,
			)
		),
		true
	);
	if ( is_wp_error( $rmt_id ) ) {
		WP_CLI::error( $rmt_id->get_error_message() );
	}
	update_post_meta( $rmt_id, '_rmt_kicker', $rmt_s['kicker'] );
	update_post_meta( $rmt_id, '_rmt_includes', $rmt_s['includes'] );
	update_post_meta( $rmt_id, '_rmt_calc_type', $rmt_s['calc'] );
	if ( $rmt_photo( $rmt_s['photo'] ) ) {
		set_post_thumbnail( $rmt_id, $rmt_photo( $rmt_s['photo'] ) );
	}
	$rmt_ids[ $rmt_s['slug'] ] = $rmt_id;
	WP_CLI::log( "Создана услуга: {$rmt_s['title']}" );
}

/* ---------- Проекты: по новым видам ремонта ---------- */

foreach ( $rmt_projects as $rmt_slug => $rmt_type ) {
	$rmt_project = get_page_by_path( $rmt_slug, OBJECT, 'project' );
	if ( $rmt_project ) {
		wp_set_object_terms( $rmt_project->ID, array( $rmt_type ), 'repair_type' );
		WP_CLI::log( "Проект {$rmt_slug} → {$rmt_type}" );
	}
}

/* ---------- Удаление прежних услуг и видов ремонта ---------- */

foreach ( $rmt_old as $rmt_slug ) {
	$rmt_post = get_page_by_path( $rmt_slug, OBJECT, 'service' );
	if ( $rmt_post ) {
		wp_delete_post( $rmt_post->ID, true ); // Пункт меню на неё WordPress удаляет сам.
		WP_CLI::log( "Удалена услуга: {$rmt_slug}" );
	}
	$rmt_term = get_term_by( 'slug', $rmt_slug, 'repair_type' );
	if ( $rmt_term ) {
		wp_delete_term( $rmt_term->term_id, 'repair_type' );
		WP_CLI::log( "Удалён вид ремонта: {$rmt_slug}" );
	}
}

/* ---------- Подменю «Услуги» ---------- */

$rmt_locations = get_theme_mod( 'nav_menu_locations', array() );
$rmt_menu_id   = (int) ( $rmt_locations['primary'] ?? 0 );
if ( $rmt_menu_id ) {
	$rmt_items  = wp_get_nav_menu_items( $rmt_menu_id ) ?: array();
	$rmt_parent = 0;
	$rmt_have   = array();
	foreach ( $rmt_items as $rmt_item ) {
		if ( 'post_type_archive' === $rmt_item->type && 'service' === $rmt_item->object ) {
			$rmt_parent = $rmt_item->ID;
		}
		if ( 'service' === $rmt_item->object && 'post_type' === $rmt_item->type ) {
			$rmt_have[] = (int) $rmt_item->object_id;
		}
	}
	if ( $rmt_parent ) {
		foreach ( $rmt_services as $rmt_s ) {
			$rmt_id = $rmt_ids[ $rmt_s['slug'] ] ?? 0;
			if ( $rmt_id && ! in_array( $rmt_id, $rmt_have, true ) ) {
				wp_update_nav_menu_item(
					$rmt_menu_id,
					0,
					array(
						'menu-item-object-id' => $rmt_id,
						'menu-item-object'    => 'service',
						'menu-item-type'      => 'post_type',
						'menu-item-parent-id' => $rmt_parent,
						'menu-item-status'    => 'publish',
					)
				);
				WP_CLI::log( "Меню: «Услуги» → {$rmt_s['title']}" );
			}
		}
	}
}

WP_CLI::success( 'Услуги: «Ремонт квартир» и «Ремонт домов».' );
