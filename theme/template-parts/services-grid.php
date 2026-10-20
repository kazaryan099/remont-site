<?php
/**
 * Мозаика услуг: фото-карточки с текстом поверх.
 *
 * $args: title, kicker, heading_tag, exclude (ID услуги, которую не показывать)
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

$rmt_services = get_posts(
	array(
		'post_type'      => 'service',
		'posts_per_page' => 10,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
		'post__not_in'   => array_filter( array( (int) ( $args['exclude'] ?? 0 ) ) ),
	)
);
if ( ! $rmt_services ) {
	return;
}
?>
<section class="section services" aria-labelledby="services-title">
	<div class="container">
		<?php
		rmt_section_head(
			array(
				'kicker' => $args['kicker'] ?? '',
				'title'  => $args['title'] ?? rmt_opt( 'services_title' ),
				'id'     => 'services-title',
				'tag'    => $args['heading_tag'] ?? 'h2',
			)
		);
		?>
		<ul class="services__grid services__grid--<?php echo count( $rmt_services ); ?>" role="list">
			<?php foreach ( $rmt_services as $rmt_i => $rmt_service ) : ?>
				<li class="services__cell">
					<?php get_template_part( 'template-parts/card-service', null, array( 'post' => $rmt_service, 'wide' => $rmt_i < 2 ) ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
