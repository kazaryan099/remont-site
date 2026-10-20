<?php
/**
 * Страница не найдена.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part(
	'template-parts/page-hero',
	null,
	array(
		'kicker'  => __( 'Ошибка 404', 'remont' ),
		'title'   => __( 'Такой страницы нет', 'remont' ),
		'text'    => __( 'Возможно, адрес изменился. Начните с главной или посмотрите наши услуги.', 'remont' ),
		'actions' => '<a class="btn" href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'На главную', 'remont' ) . '</a>'
			. rmt_link_arrow( get_post_type_archive_link( 'service' ), __( 'Виды ремонта', 'remont' ) ),
	)
);

get_template_part( 'template-parts/lead-form' );
get_footer();
