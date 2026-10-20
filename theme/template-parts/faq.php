<?php
/**
 * Вопросы и ответы (аккордеон на <details>, работает без JavaScript).
 *
 * $args: title, limit
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

$rmt_faq = get_posts(
	array(
		'post_type'      => 'faq',
		'posts_per_page' => (int) ( $args['limit'] ?? 12 ),
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
	)
);
if ( ! $rmt_faq ) {
	return;
}
?>
<section class="section faq" id="faq" aria-labelledby="faq-title">
	<div class="container container--narrow">
		<?php
		rmt_section_head(
			array(
				'title' => $args['title'] ?? rmt_opt( 'faq_title' ),
				'id'    => 'faq-title',
			)
		);
		?>
		<div class="faq__list">
			<?php foreach ( $rmt_faq as $rmt_i => $rmt_q ) : ?>
				<details class="faq__item" <?php echo 0 === $rmt_i ? 'open' : ''; ?>>
					<summary class="faq__question"><?php echo esc_html( get_the_title( $rmt_q ) ); ?></summary>
					<div class="faq__answer prose">
						<?php echo wp_kses_post( wpautop( $rmt_q->post_content ) ); ?>
					</div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
