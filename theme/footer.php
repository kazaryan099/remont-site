<?php
/**
 * Подвал сайта.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

$rmt_services = get_posts(
	array(
		'post_type'      => 'service',
		'posts_per_page' => 8,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	)
);
$rmt_types    = get_terms(
	array(
		'taxonomy'   => 'repair_type',
		'hide_empty' => true,
	)
);
$rmt_privacy  = get_privacy_policy_url();
?>
</main>

<footer class="site-footer">
	<div class="container">
		<div class="site-footer__grid">
			<section class="site-footer__col" aria-labelledby="footer-services">
				<h2 class="site-footer__title" id="footer-services"><?php esc_html_e( 'Услуги', 'remont' ); ?></h2>
				<ul>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'service' ) ); ?>"><?php esc_html_e( 'Все виды ремонта', 'remont' ); ?></a></li>
					<?php foreach ( $rmt_services as $rmt_s ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $rmt_s ) ); ?>"><?php echo esc_html( get_the_title( $rmt_s ) ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</section>

			<section class="site-footer__col" aria-labelledby="footer-portfolio">
				<h2 class="site-footer__title" id="footer-portfolio"><?php esc_html_e( 'Портфолио', 'remont' ); ?></h2>
				<ul>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>"><?php esc_html_e( 'Все проекты', 'remont' ); ?></a></li>
					<?php if ( ! is_wp_error( $rmt_types ) ) : ?>
						<?php foreach ( $rmt_types as $rmt_t ) : ?>
							<li><a href="<?php echo esc_url( get_term_link( $rmt_t ) ); ?>"><?php echo esc_html( $rmt_t->name ); ?></a></li>
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>
			</section>

			<section class="site-footer__col" aria-labelledby="footer-company">
				<h2 class="site-footer__title" id="footer-company"><?php esc_html_e( 'Компания', 'remont' ); ?></h2>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => '',
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
				?>
			</section>

			<section class="site-footer__col site-footer__contacts" aria-labelledby="footer-contacts">
				<h2 class="site-footer__title" id="footer-contacts"><?php esc_html_e( 'Связаться', 'remont' ); ?></h2>
				<a class="site-footer__phone" href="<?php echo esc_url( rmt_phone_href() ); ?>"><?php echo esc_html( rmt_opt( 'phone' ) ); ?></a>
				<?php if ( rmt_opt( 'email' ) ) : ?>
					<a class="site-footer__email" href="<?php echo esc_url( 'mailto:' . rmt_opt( 'email' ) ); ?>"><?php echo esc_html( rmt_opt( 'email' ) ); ?></a>
				<?php endif; ?>
				<p class="site-footer__meta">
					<?php echo esc_html( rmt_opt( 'region' ) ); ?><br>
					<?php echo esc_html( rmt_opt( 'schedule' ) ); ?>
				</p>
				<?php if ( rmt_opt( 'telegram' ) || rmt_opt( 'whatsapp' ) ) : ?>
					<p class="site-footer__social">
						<?php if ( rmt_opt( 'telegram' ) ) : ?>
							<a href="<?php echo esc_url( rmt_opt( 'telegram' ) ); ?>" rel="noopener" target="_blank">Telegram</a>
						<?php endif; ?>
						<?php if ( rmt_opt( 'whatsapp' ) ) : ?>
							<a href="<?php echo esc_url( rmt_opt( 'whatsapp' ) ); ?>" rel="noopener" target="_blank">WhatsApp</a>
						<?php endif; ?>
					</p>
				<?php endif; ?>
				<a class="btn" href="#lead-form"><?php esc_html_e( 'Оставить заявку', 'remont' ); ?></a>
			</section>
		</div>

		<div class="site-footer__bottom">
			<?php // Юрлицо и ИНН показываются, только когда заданы в настройщике; иначе — название сайта. ?>
			<p>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( rmt_opt( 'legal' ) ?: get_bloginfo( 'name' ) ); ?></p>
			<?php if ( $rmt_privacy ) : ?>
				<a href="<?php echo esc_url( $rmt_privacy ); ?>"><?php esc_html_e( 'Политика конфиденциальности', 'remont' ); ?></a>
			<?php endif; ?>
			<p class="site-footer__note"><?php esc_html_e( 'Информация на сайте не является публичной офертой.', 'remont' ); ?></p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
