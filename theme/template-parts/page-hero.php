<?php
/**
 * Первый экран внутренних страниц.
 *
 * $args:
 *   variant  photo (фото на весь блок, светлый текст) | paper (текст слева, фото справа)
 *   kicker, title, text, image (ID), aside (HTML правой карточки для photo), actions (HTML кнопок)
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

$rmt_variant = ( $args['variant'] ?? 'paper' ) === 'photo' ? 'photo' : 'paper';
$rmt_image   = (int) ( $args['image'] ?? 0 );
?>
<section class="page-hero page-hero--<?php echo esc_attr( $rmt_variant ); ?>">
	<?php if ( 'photo' === $rmt_variant ) : ?>
		<div class="page-hero__media">
			<?php echo rmt_image( $rmt_image, 'rmt-hero', array( 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php endif; ?>

	<div class="container page-hero__grid">
		<div class="page-hero__content">
			<?php rmt_breadcrumbs(); ?>
			<?php if ( ! empty( $args['kicker'] ) ) : ?>
				<p class="kicker<?php echo 'photo' === $rmt_variant ? ' kicker--light' : ''; ?>"><?php echo esc_html( $args['kicker'] ); ?></p>
			<?php endif; ?>
			<h1 class="page-hero__title display"><?php echo esc_html( $args['title'] ?? '' ); ?></h1>
			<?php if ( ! empty( $args['text'] ) ) : ?>
				<p class="page-hero__text"><?php echo esc_html( $args['text'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $args['actions'] ) ) : ?>
				<div class="page-hero__actions"><?php echo $args['actions']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML собирается в шаблонах из экранированных значений. ?></div>
			<?php endif; ?>
		</div>

		<?php if ( 'paper' === $rmt_variant && $rmt_image ) : ?>
			<div class="page-hero__figure">
				<?php echo rmt_image( $rmt_image, 'rmt-wide', array( 'alt' => '', 'loading' => 'eager', 'sizes' => '(min-width: 1024px) 40vw, 100vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php elseif ( ! empty( $args['aside'] ) ) : ?>
			<div class="page-hero__aside"><?php echo $args['aside']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML собирается в шаблонах из экранированных значений. ?></div>
		<?php endif; ?>
	</div>
</section>
