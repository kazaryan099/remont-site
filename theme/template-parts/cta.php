<?php
/**
 * Финальный тёмный призыв к действию.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="cta" aria-labelledby="cta-title">
	<div class="container cta__grid">
		<div class="cta__text">
			<h2 class="display" id="cta-title"><?php echo esc_html( rmt_opt( 'cta_title' ) ); ?></h2>
			<p class="lead"><?php echo esc_html( rmt_opt( 'cta_text' ) ); ?></p>
			<div class="cta__actions">
				<a class="btn" href="#lead-form"><?php esc_html_e( 'Вызвать замерщика', 'remont' ); ?> →</a>
				<?php echo rmt_link_arrow( get_post_type_archive_link( 'project' ), __( 'Смотреть портфолио', 'remont' ), 'link-arrow--light' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
		<figure class="cta__media">
			<?php echo rmt_image( (int) rmt_opt( 'cta_image' ), 'rmt-wide', array( 'alt' => '', 'sizes' => '(min-width: 1024px) 45vw, 100vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<figcaption><?php echo rmt_link_arrow( rmt_phone_href(), rmt_opt( 'phone' ), 'link-arrow--light' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figcaption>
		</figure>
	</div>
</section>
