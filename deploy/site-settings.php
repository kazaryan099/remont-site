<?php
/**
 * Данные компании, которые и так показаны на сайте: телефон, мессенджеры, реквизиты.
 * Меняются здесь или в настройщике WordPress.
 * Пароли и адреса для копий заявок сюда не пишем: они в hosting.env.
 *
 * Запуск: wp eval-file deploy/site-settings.php (его вызывает install.sh).
 */

defined( 'ABSPATH' ) || exit;

$rmt_site_settings = array(
	'phone'      => '+7 (906) 047-24-67',
	'telegram'   => 'https://t.me/ayk099_ru',
	'max'        => 'https://max.ru/u/f9LHodD0cOKdLgXFuk4mUMoSD_JoQaFL7X2mcIRyD1PwfLotQr9vCHs90Q4',
	'legal'      => 'ИП Казарян Айк Самвелович, ИНН 502010516497, ОГРНИП 318502900043457',
	'requisites' => "Индивидуальный предприниматель | Казарян Айк Самвелович\nИНН | 502010516497\nОГРНИП | 318502900043457\nРасчётный счёт | 40802810738000190163\nБанк | ПАО Сбербанк\nБИК | 044525225\nКорр. счёт | 30101810400000000225",
);

foreach ( $rmt_site_settings as $rmt_key => $rmt_value ) {
	set_theme_mod( $rmt_key, $rmt_value );
}

WP_CLI::success( 'Данные компании записаны.' );
