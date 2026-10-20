<?php
/**
 * Карточка проекта: фото с названием поверх, описание, характеристики.
 *
 * $args: post, heading (h2|h3)
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

$rmt_post    = get_post( $args['post'] ?? null );
$rmt_heading = in_array( $args['heading'] ?? 'h3', array( 'h2', 'h3' ), true ) ? $args['heading'] : 'h3';
$rmt_specs   = array_intersect_key( rmt_project_specs( $rmt_post->ID ), array_flip( array( 'area', 'rooms', 'duration' ) ) );
$rmt_type    = rmt_project_type( $rmt_post->ID );
?>
<article class="project-card">
	<div class="project-card__media">
		<?php echo rmt_post_image( $rmt_post, 'rmt-card', array( 'alt' => '', 'sizes' => '(min-width: 1024px) 30vw, 85vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php if ( rmt_meta( 'is_demo', $rmt_post->ID ) ) : ?>
			<span class="badge"><?php esc_html_e( 'Демо', 'remont' ); ?></span>
		<?php elseif ( rmt_meta( 'is_render', $rmt_post->ID ) ) : ?>
			<span class="badge"><?php esc_html_e( 'Визуализация', 'remont' ); ?></span>
		<?php endif; ?>
		<<?php echo esc_html( $rmt_heading ); ?> class="project-card__name display">
			<a class="stretched-link" href="<?php echo esc_url( get_permalink( $rmt_post ) ); ?>"><?php echo esc_html( get_the_title( $rmt_post ) ); ?></a>
		</<?php echo esc_html( $rmt_heading ); ?>>
	</div>
	<?php if ( $rmt_type ) : ?>
		<p class="project-card__type"><?php echo esc_html( $rmt_type->name ); ?></p>
	<?php endif; ?>
	<?php if ( has_excerpt( $rmt_post ) ) : ?>
		<p class="project-card__excerpt"><?php echo esc_html( get_the_excerpt( $rmt_post ) ); ?></p>
	<?php endif; ?>
	<?php if ( $rmt_specs ) : ?>
		<ul class="specs-inline" role="list">
			<?php foreach ( $rmt_specs as $rmt_spec ) : ?>
				<li><?php echo esc_html( $rmt_spec['value'] ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</article>
