<?php
/**
 * Тёмная полоса фактов под первым экраном.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

$rmt_items = $args['items'] ?? array();
if ( ! $rmt_items ) {
	return;
}
?>
<div class="facts">
	<ul class="facts__list container" role="list">
		<?php foreach ( $rmt_items as $rmt_item ) : ?>
			<li class="facts__item">
				<strong><?php echo esc_html( $rmt_item['title'] ); ?></strong>
				<?php if ( $rmt_item['text'] ) : ?>
					<span><?php echo esc_html( $rmt_item['text'] ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
