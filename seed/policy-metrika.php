<?php
/**
 * Политика конфиденциальности: уточнение про сторонние сервисы и раздел о Яндекс Метрике.
 * Запуск: docker compose run --rm wpcli wp eval-file /seed/policy-metrika.php
 * Вызывается и из seed.php. Повторный запуск ничего не дублирует.
 */

defined( 'ABSPATH' ) || exit;

$rmt_policy = get_page_by_path( 'politika-konfidencialnosti' );
if ( ! $rmt_policy ) {
	WP_CLI::warning( 'Страница политики не найдена' );
	return;
}

$rmt_old = 'Заявки хранятся в системе управления сайтом и доступны только администраторам. Данные не передаются сторонним сервисам.';
$rmt_new = 'Заявки хранятся в системе управления сайтом и доступны только администраторам. Данные из заявок не передаются сторонним сервисам.';
$rmt_metrika = "\n<h2>Файлы cookie и статистика посещений</h2>\n<p>На сайте работает сервис веб-аналитики Яндекс Метрика (ООО «Яндекс»). С помощью файлов cookie он собирает обезличенные данные о посещениях: просмотренные страницы, действия на сайте, тип устройства и браузера, примерное местоположение. Эти данные нужны, чтобы понимать, как пользуются сайтом, и делать его удобнее. Содержимое полей формы заявки сервис не записывает.</p>\n<p>Отключить cookie можно в настройках браузера, а сбор данных Метрикой — с помощью блокировщика или официального расширения Яндекса для отказа от отслеживания.</p>";

$rmt_content = $rmt_policy->post_content;
if ( false !== strpos( $rmt_content, 'Яндекс Метрика' ) ) {
	WP_CLI::log( 'Раздел о Метрике уже есть' );
	return;
}
$rmt_content = str_replace( $rmt_old, $rmt_new, $rmt_content );
$rmt_anchor  = '<h2>Как отозвать согласие</h2>';
$rmt_content = false !== strpos( $rmt_content, $rmt_anchor )
	? str_replace( $rmt_anchor, ltrim( $rmt_metrika ) . "\n" . $rmt_anchor, $rmt_content )
	: $rmt_content . $rmt_metrika;

wp_update_post( wp_slash( array( 'ID' => $rmt_policy->ID, 'post_content' => $rmt_content ) ) );
WP_CLI::success( 'Политика конфиденциальности обновлена (Яндекс Метрика)' );
