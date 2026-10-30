<?php
/**
 * Название проекта: «Частный дом в КП «Повадино»».
 * Отдельный запуск: docker compose run --rm wpcli wp eval-file /seed/povadino-kp.php
 */

defined( 'ABSPATH' ) || exit;

$rmt_project = get_page_by_path( 'povadino', OBJECT, 'project' );
if ( $rmt_project ) {
	wp_update_post( wp_slash( array( 'ID' => $rmt_project->ID, 'post_title' => 'Частный дом в КП «Повадино»' ) ) );
	WP_CLI::success( 'Проект переименован: Частный дом в КП «Повадино»' );
}
