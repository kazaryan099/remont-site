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
		'title'   => __( 'Виды ремонта квартир', 'remont' ),
		'text'    => __( 'От косметического обновления до ремонта под ключ с дизайн-проектом. Выберите подходящий вариант — расскажем, что в него входит и как считается стоимость.', 'remont' ),
		'image'   => (int) rmt_opt( 'quality_image' ),
		'actions' => '<a class="btn" href="#calculator">' . esc_html__( 'Рассчитать стоимость', 'remont' ) . '</a>'
			. rmt_link_arrow( '#lead-form', __( 'Вызвать замерщика', 'remont' ) ),
	)
);

get_template_part(
	'template-parts/services-grid',
	null,
	array( 'title' => __( 'Выберите вид ремонта', 'remont' ) )
);
get_template_part( 'template-parts/calculator' );
get_template_part( 'template-parts/steps' );
get_template_part( 'template-parts/faq' );
get_template_part( 'template-parts/lead-form' );

get_footer();
