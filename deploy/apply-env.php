<?php
/**
 * Применяет настройки из hosting.env, которых нет в репозитории: пароль админки, адреса для заявок, индексацию.
 * Читает их из окружения, поэтому пароль не виден в списке процессов.
 *
 * Запуск: wp eval-file deploy/apply-env.php (его вызывает install.sh).
 */

defined( 'ABSPATH' ) || exit;

$rmt_admin = get_user_by( 'login', getenv( 'ADMIN_USER' ) );
if ( ! $rmt_admin ) {
	WP_CLI::error( 'Не найден пользователь ' . getenv( 'ADMIN_USER' ) );
}
wp_set_password( getenv( 'ADMIN_PASSWORD' ), $rmt_admin->ID );
update_option( 'blog_public', '0' === getenv( 'INDEXING' ) ? 0 : 1 );
if ( getenv( 'NOTIFY_EMAILS' ) ) {
	update_option( 'rmt_lead_notify_email', getenv( 'NOTIFY_EMAILS' ) );
}

WP_CLI::success( 'Настройки хостинга применены.' );
