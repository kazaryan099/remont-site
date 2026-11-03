<?php
/**
 * Создаёт wp-config.php из переменных окружения (их читает install.sh из hosting.env).
 * Пароли не попадают в командную строку, поэтому не видны в списке процессов на общем хостинге.
 *
 * Запуск: php make-config.php /путь/к/wp-config.php
 */

$rmt_q = fn( $value ) => var_export( (string) $value, true );
$rmt_e = fn( $key, $default = '' ) => false !== getenv( $key ) ? getenv( $key ) : $default;

$rmt_lines = array( '<?php' );
foreach ( array( 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST' ) as $rmt_const ) {
	$rmt_lines[] = "define('{$rmt_const}', " . $rmt_q( $rmt_e( $rmt_const ) ) . ');';
}
$rmt_lines[] = "define('DB_CHARSET', 'utf8mb4');";
$rmt_lines[] = "define('DB_COLLATE', '');";
foreach ( array( 'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT' ) as $rmt_const ) {
	$rmt_lines[] = "define('{$rmt_const}', " . $rmt_q( bin2hex( random_bytes( 32 ) ) ) . ');';
}
$rmt_lines[] = "\$table_prefix = 'wp_';";
$rmt_lines[] = "define('WP_ENVIRONMENT_TYPE', " . $rmt_q( $rmt_e( 'ENVIRONMENT', 'production' ) ) . ');';
$rmt_lines[] = "define('DISALLOW_FILE_EDIT', true);";
$rmt_lines[] = "define('WP_DEBUG', false);";
if ( $rmt_e( 'SMTP_HOST' ) ) {
	$rmt_lines[] = "define('RMT_SMTP_HOST', " . $rmt_q( $rmt_e( 'SMTP_HOST' ) ) . ');';
	$rmt_lines[] = "define('RMT_SMTP_PORT', " . (int) $rmt_e( 'SMTP_PORT', 465 ) . ');';
	$rmt_lines[] = "define('RMT_SMTP_SECURE', " . $rmt_q( $rmt_e( 'SMTP_SECURE', 'ssl' ) ) . ');';
	$rmt_lines[] = "define('RMT_SMTP_USER', " . $rmt_q( $rmt_e( 'SMTP_USER' ) ) . ');';
	$rmt_lines[] = "define('RMT_SMTP_PASS', " . $rmt_q( $rmt_e( 'SMTP_PASSWORD' ) ) . ');';
	$rmt_lines[] = "define('RMT_SMTP_FROM', " . $rmt_q( $rmt_e( 'SMTP_USER' ) ) . ');';
}
$rmt_lines[] = "if (!defined('ABSPATH')) { define('ABSPATH', __DIR__ . '/'); }";
$rmt_lines[] = "require_once ABSPATH . 'wp-settings.php';";

$rmt_target = $argv[1] ?? '';
if ( '' === $rmt_target || false === file_put_contents( $rmt_target, implode( "\n", $rmt_lines ) . "\n" ) ) {
	fwrite( STDERR, "Не удалось записать wp-config.php\n" );
	exit( 1 );
}
chmod( $rmt_target, 0640 );
