<?php
/**
 * Первый экран главной.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="hero" aria-labelledby="hero-title">
	<div class="hero__media">
		<?php
		echo rmt_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			(int) rmt_opt( 'hero_image' ),
			'rmt-hero',
			array(
				'loading'       => 'eager',
				'fetchpriority' => 'high',
				'sizes'         => '100vw',
				'alt'           => '',
			)
		);
		?>
	</div>
	<div class="hero__content container">
		<?php if ( rmt_opt( 'hero_kicker' ) ) : ?>
			<p class="kicker kicker--light"><?php echo esc_html( rmt_opt( 'hero_kicker' ) ); ?></p>
		<?php endif; ?>
		<h1 class="hero__title display" id="hero-title"><?php echo esc_html( rmt_opt( 'hero_title' ) ); ?></h1>
		<p class="hero__text"><?php echo esc_html( rmt_opt( 'hero_text' ) ); ?></p>
		<div class="hero__actions">
			<a class="btn btn--lg" href="#calculator"><?php echo esc_html( rmt_opt( 'hero_btn' ) ); ?></a>
			<?php echo rmt_link_arrow( '#lead-form', rmt_opt( 'hero_link' ), 'link-arrow--light link-arrow--lg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
</section>
<?php
get_template_part( 'template-parts/facts', null, array( 'items' => rmt_opt_pairs( 'facts' ) ) );
