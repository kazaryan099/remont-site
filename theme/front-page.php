<?php
/**
 * Главная страница.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part( 'template-parts/hero-home' );
get_template_part( 'template-parts/services-grid' );
get_template_part( 'template-parts/featured-project' );
get_template_part(
	'template-parts/projects-slider',
	null,
	array(
		'kicker' => rmt_opt( 'projects_kicker' ),
		'title'  => rmt_opt( 'projects_title' ),
		'text'   => rmt_opt( 'projects_text' ),
		'promo'  => true,
	)
);
get_template_part( 'template-parts/calculator' );
get_template_part( 'template-parts/advantages' );
get_template_part( 'template-parts/quality' );
get_template_part( 'template-parts/steps' );
get_template_part( 'template-parts/faq' );
get_template_part( 'template-parts/lead-form' );
get_template_part( 'template-parts/cta' );

get_footer();
