<?php
/**
 * Сплит-блок «Контроль качества»: фото слева, графитовая панель справа.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

$rmt_points = rmt_opt_pairs( 'quality' );
?>
<section class="split" aria-labelledby="quality-title">
	<div class="split__media">
		<?php echo rmt_image( (int) rmt_opt( 'quality_image' ), 'rmt-wide', array( 'alt' => '', 'sizes' => '(min-width: 1024px) 50vw, 100vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<div class="split__panel">
		<p class="kicker"><?php echo esc_html( rmt_opt( 'quality_kicker' ) ); ?></p>
		<h2 class="split__title display" id="quality-title"><?php echo esc_html( rmt_opt( 'quality_title' ) ); ?></h2>
		<p class="split__text"><?php echo esc_html( rmt_opt( 'quality_text' ) ); ?></p>
		<?php if ( $rmt_points ) : ?>
			<ul class="split__stats" role="list">
				<?php foreach ( $rmt_points as $rmt_p ) : ?>
					<li><strong><?php echo esc_html( $rmt_p['title'] ); ?></strong><span><?php echo esc_html( $rmt_p['text'] ); ?></span></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<a class="btn btn--dark" href="#lead-form"><?php esc_html_e( 'Обсудить ремонт', 'remont' ); ?> →</a>
	</div>
</section>
