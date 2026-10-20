<?php
/**
 * Преимущества: три колонки с верхней линией.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

$rmt_items = rmt_opt_pairs( 'advantages' );
if ( ! $rmt_items ) {
	return;
}
?>
<section class="section advantages" aria-labelledby="adv-title">
	<div class="container">
		<?php
		rmt_section_head(
			array(
				'title' => rmt_opt( 'adv_title' ),
				'text'  => rmt_opt( 'adv_text' ),
				'id'    => 'adv-title',
				'class' => 'section-head--narrow',
			)
		);
		?>
		<ul class="columns" role="list">
			<?php foreach ( $rmt_items as $rmt_item ) : ?>
				<li class="columns__item">
					<h3 class="columns__title display"><?php echo esc_html( $rmt_item['title'] ); ?></h3>
					<p><?php echo esc_html( $rmt_item['text'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
