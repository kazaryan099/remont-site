<?php
/**
 * Точка входа темы. Логика разнесена по модулям в inc/.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

define( 'RMT_VERSION', '1.0.0' );
define( 'RMT_DIR', get_template_directory() );
define( 'RMT_URI', get_template_directory_uri() );

$rmt_modules = array(
	'setup',          // поддержка возможностей темы, меню, размеры изображений
	'template-tags',  // вспомогательные функции вывода
	'post-types',     // услуги, проекты, FAQ, заявки
	'meta-boxes',     // поля услуг и проектов
	'customizer',     // контакты и тексты главной
	'calculator',     // настройки калькулятора
	'leads',          // приём и хранение заявок
	'seo',            // метаданные, Open Graph, микроразметка
	'analytics',      // Яндекс Метрика (только на боевом сайте)
	'assets',         // стили и скрипты
);

foreach ( $rmt_modules as $rmt_module ) {
	require_once RMT_DIR . '/inc/' . $rmt_module . '.php';
}
