<?php
/**
 * Карусель первого экрана: 5 фото из проектов (Синергия, Повадино, ЖК «Вереск»).
 * Фото уже должны быть загружены (sinergiya.php, portfolio.php) — ищем их по метке _rmt_seed_file.
 *
 * Отдельный запуск: docker compose run --rm wpcli wp eval-file /seed/hero-slides.php
 * Вызывается и из seed.php. Повторный запуск ничего не дублирует.
 */

defined( 'ABSPATH' ) || exit;

$rmt_hero_slides = array(
	'hero_image'   => 'sinergiya-gostinaya',
	'hero_image_2' => 'povadino-00',
	'hero_image_3' => 'sinergiya-kuhnya-stolovaya',
	'hero_image_4' => 'veresk-04',
	'hero_image_5' => 'povadino-05',
);

foreach ( $rmt_hero_slides as $rmt_key => $rmt_name ) {
	$rmt_found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_rmt_seed_file', 'meta_value' => $rmt_name, 'fields' => 'ids', 'numberposts' => 1 ) );
	if ( ! $rmt_found ) {
		WP_CLI::warning( "Нет фото {$rmt_name} — слайд {$rmt_key} пропущен" );
		continue;
	}
	set_theme_mod( $rmt_key, $rmt_found[0] );
	WP_CLI::log( "{$rmt_key}: {$rmt_name} (#{$rmt_found[0]})" );
}

WP_CLI::success( 'Фото карусели назначены.' );
