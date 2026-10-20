<?php
/**
 * Этапы ремонта на терракотовом фоне.
 *
 * $args: heading_tag
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

$rmt_steps = rmt_opt_pairs( 'steps' );
if ( ! $rmt_steps ) {
	return;
}
?>
<section class="section section--brand steps" id="steps" aria-labelledby="steps-title">
	<div class="container steps__grid">
		<div class="steps__intro">
			<p class="kicker"><?php echo esc_html( rmt_opt( 'steps_kicker' ) ); ?></p>
			<h2 class="display" id="steps-title"><?php echo esc_html( rmt_opt( 'steps_title' ) ); ?></h2>
			<p class="lead"><?php echo esc_html( rmt_opt( 'steps_text' ) ); ?></p>
			<?php echo rmt_link_arrow( '#lead-form', __( 'Записаться на замер', 'remont' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<ol class="steps__list">
			<?php foreach ( $rmt_steps as $rmt_i => $rmt_step ) : ?>
				<li class="steps__item">
					<span class="steps__num display" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $rmt_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<h3 class="steps__title"><?php echo esc_html( $rmt_step['title'] ); ?></h3>
					<p><?php echo esc_html( $rmt_step['text'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
