<?php
/**
 * Портфолио: /portfolio/ и /portfolio/tip/<вид>/
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

get_header();

$rmt_current = is_tax( 'repair_type' ) ? get_queried_object() : null;
$rmt_terms   = get_terms(
	array(
		'taxonomy'   => 'repair_type',
		'hide_empty' => true,
	)
);

get_template_part(
	'template-parts/page-hero',
	null,
	array(
		'kicker' => __( 'Портфолио', 'remont' ),
		'title'  => $rmt_current ? $rmt_current->name : __( 'Наши проекты', 'remont' ),
		'text'   => $rmt_current && $rmt_current->description ? $rmt_current->description : __( 'Реализованные ремонты и дизайн-проекты квартир и домов: фото, визуализации, площадь и состав работ.', 'remont' ),
	)
);
?>
<section class="section section--tight">
	<div class="container">
		<?php if ( $rmt_terms && ! is_wp_error( $rmt_terms ) ) : ?>
			<nav class="filter" aria-label="<?php esc_attr_e( 'Фильтр по виду ремонта', 'remont' ); ?>">
				<a class="chip-link<?php echo $rmt_current ? '' : ' is-active'; ?>" href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>" <?php echo $rmt_current ? '' : 'aria-current="page"'; ?>><?php esc_html_e( 'Все', 'remont' ); ?></a>
				<?php foreach ( $rmt_terms as $rmt_term ) : ?>
					<?php $rmt_active = $rmt_current && $rmt_current->term_id === $rmt_term->term_id; ?>
					<a class="chip-link<?php echo $rmt_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $rmt_term ) ); ?>" <?php echo $rmt_active ? 'aria-current="page"' : ''; ?>>
						<?php echo esc_html( $rmt_term->name ); ?> <span class="chip-link__count"><?php echo esc_html( $rmt_term->count ); ?></span>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<h2 class="visually-hidden"><?php esc_html_e( 'Список проектов', 'remont' ); ?></h2>
			<ul class="card-grid" role="list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<li><?php get_template_part( 'template-parts/card-project', null, array( 'post' => get_post(), 'heading' => 'h3' ) ); ?></li>
				<?php endwhile; ?>
			</ul>
			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 1,
					'prev_text' => __( 'Назад', 'remont' ),
					'next_text' => __( 'Дальше', 'remont' ),
				)
			);
			?>
		<?php else : ?>
			<p class="lead"><?php esc_html_e( 'Проекты скоро появятся.', 'remont' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php
get_template_part( 'template-parts/cta' );
get_template_part( 'template-parts/lead-form' );
get_footer();
