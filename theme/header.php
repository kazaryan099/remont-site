<?php
/**
 * Шапка сайта.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#171815">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Перейти к содержимому', 'remont' ); ?></a>

<header class="site-header" data-header>
	<div class="site-header__inner container">
		<button class="burger" type="button" aria-controls="mobile-menu" aria-expanded="false" data-menu-open>
			<?php echo rmt_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span class="visually-hidden"><?php esc_html_e( 'Открыть меню', 'remont' ); ?></span>
		</button>

		<?php get_template_part( 'template-parts/logo' ); ?>

		<nav class="main-nav" aria-label="<?php esc_attr_e( 'Основное меню', 'remont' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 2,
					'fallback_cb'    => 'rmt_fallback_menu',
				)
			);
			?>
		</nav>

		<div class="site-header__actions">
			<a class="header-phone" href="<?php echo esc_url( rmt_phone_href() ); ?>">
				<?php echo rmt_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( rmt_opt( 'phone' ) ); ?></span>
			</a>
			<a class="btn btn--sm header-cta" href="#lead-form"><?php esc_html_e( 'Оставить заявку', 'remont' ); ?></a>
		</div>
	</div>
</header>

<div class="mobile-menu" id="mobile-menu" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Меню', 'remont' ); ?>" hidden data-menu>
	<div class="mobile-menu__top">
		<?php get_template_part( 'template-parts/logo' ); ?>
		<button class="mobile-menu__close" type="button" data-menu-close>
			<?php echo rmt_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span class="visually-hidden"><?php esc_html_e( 'Закрыть меню', 'remont' ); ?></span>
		</button>
	</div>
	<nav class="mobile-menu__nav" aria-label="<?php esc_attr_e( 'Мобильное меню', 'remont' ); ?>">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'menu',
				'depth'          => 2,
				'fallback_cb'    => 'rmt_fallback_menu',
			)
		);
		?>
	</nav>
	<div class="mobile-menu__contacts">
		<p class="kicker"><?php esc_html_e( 'Связаться', 'remont' ); ?></p>
		<p><?php echo esc_html( rmt_opt( 'schedule' ) ); ?></p>
	</div>
	<div class="mobile-menu__bar">
		<a class="btn" href="<?php echo esc_url( rmt_phone_href() ); ?>"><?php esc_html_e( 'Позвонить', 'remont' ); ?></a>
		<a class="btn btn--dark" href="#lead-form" data-menu-close><?php esc_html_e( 'Заявка', 'remont' ); ?></a>
	</div>
</div>

<main id="main" class="site-main" tabindex="-1">
