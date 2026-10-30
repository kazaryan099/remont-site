<?php
/**
 * «Синергия» — частный дом в коттеджном посёлке, а не квартира:
 * новое название, описание без слова «квартира», вид ремонта «Ремонт домов».
 *
 * Отдельный запуск: docker compose run --rm wpcli wp eval-file /seed/sinergiya-dom.php
 * Вызывается и из seed.php. Повторный запуск ничего не меняет.
 */

defined( 'ABSPATH' ) || exit;

$rmt_project = get_page_by_path( 'sinergiya', OBJECT, 'project' );
if ( ! $rmt_project ) {
	WP_CLI::warning( 'Проект sinergiya не найден' );
	return;
}

wp_update_post(
	wp_slash(
		array(
			'ID'           => $rmt_project->ID,
			'post_title'   => 'Частный дом в КП «Синергия»',
			'post_excerpt' => 'Двухэтажный дом в светлой палитре: кухня-гостиная с островом и камином, мастер-спальня с ванной, гостевая спальня, детская и гардеробная.',
		)
	)
);
update_post_meta( $rmt_project->ID, '_rmt_rooms', 'Двухэтажный частный дом' );
wp_set_object_terms( $rmt_project->ID, array( 'remont-domov' ), 'repair_type' );

WP_CLI::success( 'Проект «Синергия» → «Частный дом в КП «Синергия»», вид ремонта: «Ремонт домов».' );
