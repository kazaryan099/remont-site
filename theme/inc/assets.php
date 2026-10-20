<?php
/**
 * Подключение стилей и скриптов.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

/** Версия файла по дате изменения — чтобы браузер не держал старый кэш после правок. */
function rmt_asset_ver( $rel ) {
	$path = RMT_DIR . '/' . $rel;
	return file_exists( $path ) ? RMT_VERSION . '.' . filemtime( $path ) : RMT_VERSION;
}

add_action( 'wp_enqueue_scripts', 'rmt_enqueue_assets' );
function rmt_enqueue_assets() {
	wp_enqueue_style( 'rmt-main', RMT_URI . '/assets/css/main.css', array(), rmt_asset_ver( 'assets/css/main.css' ) );

	wp_enqueue_script( 'rmt-main', RMT_URI . '/assets/js/main.js', array(), rmt_asset_ver( 'assets/js/main.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_localize_script(
		'rmt-main',
		'rmtData',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'i18n'    => array(
				'sending' => __( 'Отправляем…', 'remont' ),
				'error'   => __( 'Не удалось отправить заявку. Проверьте соединение или позвоните нам.', 'remont' ),
			),
		)
	);

	wp_register_script( 'rmt-calculator', RMT_URI . '/assets/js/calculator.js', array(), rmt_asset_ver( 'assets/js/calculator.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );

	// Блочная библиотека нужна только если контент собран в редакторе блоков — оставляем, но без глобальных стилей.
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}

/** Предзагрузка основных шрифтов (кириллица). */
add_action( 'wp_head', 'rmt_preload_fonts', 1 );
function rmt_preload_fonts() {
	foreach ( array( 'russo-one-cyrillic', 'montserrat-cyrillic', 'tenor-sans-cyrillic' ) as $font ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( RMT_URI . '/assets/fonts/' . $font . '.woff2' ) );
	}
}
