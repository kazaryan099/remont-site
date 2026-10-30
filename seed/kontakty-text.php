<?php
/**
 * Подзаголовок страницы «Контакты» (новая формулировка).
 * Отдельный запуск: docker compose run --rm wpcli wp eval-file /seed/kontakty-text.php
 */

defined( 'ABSPATH' ) || exit;

$rmt_page = get_page_by_path( 'kontakty' );
if ( $rmt_page ) {
	wp_update_post(
		wp_slash(
			array(
				'ID'           => $rmt_page->ID,
				'post_excerpt' => 'Позвоните или напишите в мессенджер — ответим на вопросы, рассчитаем стоимость и договоримся о выезде на объект.',
			)
		)
	);
	WP_CLI::success( 'Текст страницы «Контакты» обновлён.' );
}
