<?php
/**
 * Архив услуг: /uslugi/
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part(
	'template-parts/page-hero',
	null,
	array(
		'kicker'  => __( 'Услуги', 'remont' ),
		'title'   => __( 'Ремонт квартир и домов', 'remont' ),
		'text'    => __( 'Квартиры в новостройках и на вторичном рынке, частные дома и коттеджи. Выберите, что нужно отремонтировать, — расскажем, что входит в работы и как считается стоимость.', 'remont' ),
		'image'   => (int) rmt_opt( 'quality_image' ),
		'actions' => '<a class="btn" href="#calculator">' . esc_html__( 'Рассчитать стоимость', 'remont' ) . '</a>'
			. rmt_link_arrow( '#lead-form', __( 'Вызвать замерщика', 'remont' ) ),
	)
);

get_template_part(
	'template-parts/services-grid',
	null,
	array( 'title' => __( 'Выберите услугу', 'remont' ) )
);
get_template_part( 'template-parts/calculator' );
get_template_part( 'template-parts/steps' );
get_template_part( 'template-parts/faq' );
get_template_part( 'template-parts/lead-form' );

get_footer();
