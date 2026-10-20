<?php
/**
 * Реализованный проект на главной: текст и фото-мозаика из галереи.
 * Проект выбирается в настройщике; по умолчанию — первый проект без пометки «Демо».
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

$rmt_project = get_post( (int) rmt_opt( 'featured_project' ) );
if ( ! $rmt_project || 'project' !== $rmt_project->post_type || 'publish' !== $rmt_project->post_status ) {
	$rmt_found   = get_posts(
		array(
			'post_type'      => 'project',
			'posts_per_page' => 1,
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'key'     => '_rmt_is_demo',
					'compare' => 'NOT EXISTS',
				),
			),
		)
	);
	$rmt_project = $rmt_found ? $rmt_found[0] : null;
}
if ( ! $rmt_project ) {
	return;
}

// Раскладываем фото: горизонтальные — на широкие ячейки, вертикальные — на узкие.
$rmt_ids = array_unique( array_filter( array_merge( array( get_post_thumbnail_id( $rmt_project ) ), rmt_gallery_ids( $rmt_project->ID ) ) ) );
$rmt_land = array();
$rmt_port = array();
foreach ( $rmt_ids as $rmt_id ) {
	$rmt_meta = wp_get_attachment_metadata( $rmt_id );
	if ( empty( $rmt_meta['width'] ) ) {
		continue;
	}
	if ( $rmt_meta['width'] > $rmt_meta['height'] ) {
		$rmt_land[] = $rmt_id;
	} else {
		$rmt_port[] = $rmt_id;
	}
}
$rmt_tiles = array();
if ( count( $rmt_land ) >= 2 && count( $rmt_port ) >= 4 ) {
	$rmt_tiles = array(
		array( $rmt_land[0], 'wide' ), array( $rmt_port[0], '' ), array( $rmt_port[1], '' ),
		array( $rmt_port[2], '' ), array( $rmt_port[3], '' ), array( $rmt_land[1], 'wide' ),
	);
} else {
	foreach ( array_slice( $rmt_ids, 0, 6 ) as $rmt_id ) {
		$rmt_tiles[] = array( $rmt_id, '' );
	}
}
$rmt_specs = rmt_project_specs( $rmt_project->ID );
$rmt_tasks = array_slice( rmt_meta_lines( 'tasks', $rmt_project->ID ), 0, 6 );
?>
<section class="section section--paper featured" aria-labelledby="featured-title">
	<div class="container">
		<div class="featured__head">
			<div>
				<p class="kicker"><?php echo rmt_meta( 'is_render', $rmt_project->ID ) ? esc_html__( 'Дизайн-проект', 'remont' ) : esc_html__( 'Реализованный проект', 'remont' ); ?></p>
				<h2 class="display" id="featured-title"><?php echo esc_html( get_the_title( $rmt_project ) ); ?></h2>
			</div>
			<div class="featured__intro">
				<?php if ( has_excerpt( $rmt_project ) ) : ?>
					<p class="lead"><?php echo esc_html( get_the_excerpt( $rmt_project ) ); ?></p>
				<?php endif; ?>
				<?php if ( $rmt_specs ) : ?>
					<ul class="specs-inline" role="list">
						<?php foreach ( $rmt_specs as $rmt_spec ) : ?>
							<li><?php echo esc_html( $rmt_spec['value'] ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php echo rmt_link_arrow( get_permalink( $rmt_project ), __( 'Смотреть проект', 'remont' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>

		<ul class="featured__grid" role="list">
			<?php foreach ( $rmt_tiles as list( $rmt_id, $rmt_mod ) ) : ?>
				<li class="featured__tile<?php echo $rmt_mod ? ' featured__tile--' . esc_attr( $rmt_mod ) : ''; ?>">
					<a href="<?php echo esc_url( get_permalink( $rmt_project ) ); ?>" tabindex="-1" aria-hidden="true">
						<?php echo wp_get_attachment_image( $rmt_id, $rmt_mod ? 'rmt-wide' : 'rmt-card', false, array( 'loading' => 'lazy', 'sizes' => $rmt_mod ? '(min-width: 1024px) 50vw, 100vw' : '(min-width: 1024px) 25vw, 50vw' ) ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php if ( $rmt_tasks ) : ?>
			<ul class="featured__tasks" role="list">
				<?php foreach ( $rmt_tasks as $rmt_task ) : ?>
					<li><?php echo rmt_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $rmt_task ); ?></span></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
