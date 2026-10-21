<?php
/**
 * Отправка почты через SMTP. Параметры — константы в wp-config.php (в Git не хранятся):
 *   RMT_SMTP_HOST, RMT_SMTP_PORT, RMT_SMTP_SECURE ('ssl' | 'tls' | ''),
 *   RMT_SMTP_USER, RMT_SMTP_PASS (если сервер требует авторизацию), RMT_SMTP_FROM.
 * Локально в Docker письма уходят в Mailpit (http://localhost:8025), на Beget — через smtp.beget.com.
 * Без RMT_SMTP_HOST WordPress отправляет письма стандартной функцией PHP mail().
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

add_action( 'phpmailer_init', 'rmt_smtp_setup' );
function rmt_smtp_setup( $mailer ) {
	if ( ! defined( 'RMT_SMTP_HOST' ) || ! RMT_SMTP_HOST ) {
		return;
	}
	$mailer->isSMTP();
	$mailer->Host       = RMT_SMTP_HOST;
	$mailer->Port       = defined( 'RMT_SMTP_PORT' ) ? (int) RMT_SMTP_PORT : 465;
	$mailer->SMTPSecure = defined( 'RMT_SMTP_SECURE' ) ? RMT_SMTP_SECURE : 'ssl';
	$mailer->SMTPAutoTLS = '' !== $mailer->SMTPSecure;
	if ( defined( 'RMT_SMTP_USER' ) && RMT_SMTP_USER ) {
		$mailer->SMTPAuth = true;
		$mailer->Username = RMT_SMTP_USER;
		$mailer->Password = defined( 'RMT_SMTP_PASS' ) ? RMT_SMTP_PASS : '';
	}
	$mailer->CharSet = 'UTF-8';
}

/** Отправитель: ящик домена, имя — название сайта. */
add_filter(
	'wp_mail_from',
	function ( $from ) {
		return defined( 'RMT_SMTP_FROM' ) && is_email( RMT_SMTP_FROM ) ? RMT_SMTP_FROM : $from;
	}
);
add_filter( 'wp_mail_from_name', fn() => get_bloginfo( 'name' ) );

/** Ошибки отправки — в журнал PHP (без текста письма, чтобы не писать персональные данные). */
add_action(
	'wp_mail_failed',
	function ( $error ) {
		error_log( 'DOMO: письмо не отправлено — ' . $error->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}
);
