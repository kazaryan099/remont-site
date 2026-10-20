<?php
/**
 * Template Name: О компании
 * Template Post Type: page
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	get_template_part(
		'template-parts/page-hero',
		null,
		array(
			'variant' => 'photo',
			'kicker'  => __( 'О компании', 'remont' ),
			'title'   => get_the_title(),
			'text'    => has_excerpt() ? get_the_excerpt() : '',
			'image'   => get_post_thumbnail_id(),
			'actions' => '<a class="btn" href="#lead-form">' . esc_html__( 'Обсудить ремонт', 'remont' ) . '</a>'
				. rmt_link_arrow( get_post_type_archive_link( 'project' ), __( 'Наши проекты', 'remont' ), 'link-arrow--light' ),
		)
	);
	get_template_part( 'template-parts/facts', null, array( 'items' => rmt_opt_pairs( 'facts' ) ) );
	?>
	<section class="section" aria-labelledby="approach-title">
		<div class="container about-grid">
			<div>
				<p class="kicker"><?php esc_html_e( 'Подход', 'remont' ); ?></p>
				<h2 class="display" id="approach-title"><?php esc_html_e( 'Как мы организуем ремонт', 'remont' ); ?></h2>
				<div class="prose">
					<?php the_content(); ?>
				</div>
			</div>
			<ul class="principles" role="list">
				<?php foreach ( rmt_opt_pairs( 'advantages' ) as $rmt_item ) : ?>
					<li>
						<h3 class="principles__title"><?php echo esc_html( $rmt_item['title'] ); ?></h3>
						<p><?php echo esc_html( $rmt_item['text'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
endwhile;

get_template_part( 'template-parts/steps' );
get_template_part( 'template-parts/quality' );
get_template_part( 'template-parts/faq' );
get_template_part( 'template-parts/lead-form' );
get_footer();
