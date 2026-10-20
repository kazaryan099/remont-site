<?php
/**
 * Базовая настройка темы.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'rmt_setup' );
function rmt_setup() {
	load_theme_textdomain( 'remont', RMT_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 64,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Главное меню', 'remont' ),
			'footer'  => __( 'Меню в подвале (Компания)', 'remont' ),
		)
	);

	// Карточки услуг и проектов, герой, полноэкранная галерея.
	add_image_size( 'rmt-card', 900, 900, true );
	add_image_size( 'rmt-wide', 1600, 900, true );
	add_image_size( 'rmt-hero', 1920, 1280, false );
}

/**
 * Новые загрузки JPEG/PNG сохраняются в WebP (если сервер это поддерживает) — заметно легче.
 */
add_filter(
	'image_editor_output_format',
	function ( $formats ) {
		if ( function_exists( 'imagewebp' ) || class_exists( 'Imagick' ) ) {
			$formats['image/jpeg'] = 'image/webp';
		}
		return $formats;
	}
);

add_filter( 'wp_editor_set_quality', fn() => 80 );

add_filter( 'excerpt_length', fn() => 28 );
add_filter( 'excerpt_more', fn() => '…' );

// Убираем то, что сайту не нужно: эмодзи-скрипты и версию WordPress в коде страницы.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );

/**
 * Заголовок архива без служебных приставок «Архивы:» / «Рубрика:».
 */
add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );

/**
 * Виджеты в теме не используются — убираем их панель из настройщика.
 */
add_filter(
	'customize_loaded_components',
	fn( $components ) => array_diff( $components, array( 'widgets' ) )
);
