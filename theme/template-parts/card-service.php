<?php
/**
 * Карточка услуги: фото на всю карточку, текст поверх снизу.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

$rmt_post   = get_post( $args['post'] ?? null );
$rmt_kicker = rmt_meta( 'kicker', $rmt_post->ID );
$rmt_price  = rmt_meta( 'price', $rmt_post->ID );
?>
<article class="tile">
	<div class="tile__media">
		<?php echo rmt_post_image( $rmt_post, ! empty( $args['wide'] ) ? 'rmt-wide' : 'rmt-card', array( 'alt' => '', 'sizes' => '(min-width: 1024px) 50vw, 100vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<div class="tile__body">
		<?php if ( $rmt_price || $rmt_kicker ) : ?>
			<p class="tile__kicker"><?php echo esc_html( $rmt_price ?: $rmt_kicker ); ?></p>
		<?php endif; ?>
		<h3 class="tile__title display">
			<a class="tile__link" href="<?php echo esc_url( get_permalink( $rmt_post ) ); ?>"><?php echo esc_html( get_the_title( $rmt_post ) ); ?></a>
		</h3>
		<?php if ( has_excerpt( $rmt_post ) ) : ?>
			<p class="tile__text"><?php echo esc_html( get_the_excerpt( $rmt_post ) ); ?></p>
		<?php endif; ?>
		<span class="link-arrow link-arrow--light" aria-hidden="true"><span><?php esc_html_e( 'Подробнее', 'remont' ); ?></span><?php echo rmt_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	</div>
</article>
