<?php
/**
 * Обычная страница.
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
			'title' => get_the_title(),
			'text'  => has_excerpt() ? get_the_excerpt() : '',
			'image' => get_post_thumbnail_id(),
		)
	);
	?>
	<section class="section">
		<div class="container container--narrow prose">
			<?php the_content(); ?>
		</div>
	</section>
	<?php
endwhile;

get_template_part( 'template-parts/lead-form' );
get_footer();
