<?php
/**
 * Типы записей: услуги, проекты портфолио, вопросы-ответы, заявки.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'rmt_register_content_types' );
function rmt_register_content_types() {
	// Таксономия регистрируется раньше проектов, чтобы адреса /portfolio/tip/…/ не перехватывались записями.
	register_taxonomy(
		'repair_type',
		array( 'project' ),
		array(
			'labels'            => array(
				'name'          => __( 'Виды ремонта', 'remont' ),
				'singular_name' => __( 'Вид ремонта', 'remont' ),
				'all_items'     => __( 'Все виды', 'remont' ),
				'edit_item'     => __( 'Изменить вид ремонта', 'remont' ),
				'add_new_item'  => __( 'Добавить вид ремонта', 'remont' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'       => 'portfolio/tip',
				'with_front' => false,
			),
		)
	);

	register_post_type(
		'service',
		array(
			'labels'        => array(
				'name'               => __( 'Услуги', 'remont' ),
				'singular_name'      => __( 'Услуга', 'remont' ),
				'add_new'            => __( 'Добавить услугу', 'remont' ),
				'add_new_item'       => __( 'Новая услуга', 'remont' ),
				'edit_item'          => __( 'Изменить услугу', 'remont' ),
				'all_items'          => __( 'Все услуги', 'remont' ),
				'not_found'          => __( 'Услуг пока нет', 'remont' ),
				'not_found_in_trash' => __( 'В корзине пусто', 'remont' ),
			),
			'public'        => true,
			'has_archive'   => 'uslugi',
			'rewrite'       => array(
				'slug'       => 'uslugi',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-hammer',
			'menu_position' => 20,
			'show_in_rest'  => true,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
		)
	);

	register_post_type(
		'project',
		array(
			'labels'        => array(
				'name'               => __( 'Портфолио', 'remont' ),
				'singular_name'      => __( 'Проект', 'remont' ),
				'add_new'            => __( 'Добавить проект', 'remont' ),
				'add_new_item'       => __( 'Новый проект', 'remont' ),
				'edit_item'          => __( 'Изменить проект', 'remont' ),
				'all_items'          => __( 'Все проекты', 'remont' ),
				'not_found'          => __( 'Проектов пока нет', 'remont' ),
				'not_found_in_trash' => __( 'В корзине пусто', 'remont' ),
			),
			'public'        => true,
			'has_archive'   => 'portfolio',
			'rewrite'       => array(
				'slug'       => 'portfolio',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-format-gallery',
			'menu_position' => 21,
			'show_in_rest'  => true,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
		)
	);

	register_post_type(
		'faq',
		array(
			'labels'              => array(
				'name'          => __( 'Вопросы и ответы', 'remont' ),
				'singular_name' => __( 'Вопрос', 'remont' ),
				'add_new'       => __( 'Добавить вопрос', 'remont' ),
				'add_new_item'  => __( 'Новый вопрос', 'remont' ),
				'edit_item'     => __( 'Изменить вопрос', 'remont' ),
				'all_items'     => __( 'Все вопросы', 'remont' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'exclude_from_search' => true,
			'menu_icon'           => 'dashicons-editor-help',
			'menu_position'       => 22,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'editor', 'page-attributes' ),
		)
	);

	// Заявки: только для администраторов, создать вручную нельзя — их создаёт форма.
	register_post_type(
		'rmt_lead',
		array(
			'labels'          => array(
				'name'          => __( 'Заявки', 'remont' ),
				'singular_name' => __( 'Заявка', 'remont' ),
				'edit_item'     => __( 'Заявка', 'remont' ),
				'all_items'     => __( 'Все заявки', 'remont' ),
				'not_found'     => __( 'Заявок пока нет', 'remont' ),
				'search_items'  => __( 'Найти заявку', 'remont' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_rest'    => false,
			'menu_icon'       => 'dashicons-email-alt',
			'menu_position'   => 3,
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'capabilities'    => array(
				'create_posts'       => 'do_not_allow',
				'edit_posts'         => 'manage_options',
				'edit_others_posts'  => 'manage_options',
				'delete_posts'       => 'manage_options',
				'delete_others_posts' => 'manage_options',
				'read_private_posts' => 'manage_options',
				'publish_posts'      => 'manage_options',
			),
			'map_meta_cap'    => true,
		)
	);
}

add_action( 'after_switch_theme', 'rmt_flush_rewrites' );
function rmt_flush_rewrites() {
	rmt_register_content_types();
	flush_rewrite_rules();
}

/**
 * Архивы: порядок «как в админке» (поле «Порядок»), затем по дате.
 */
add_action( 'pre_get_posts', 'rmt_archive_order' );
function rmt_archive_order( WP_Query $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->is_post_type_archive( array( 'service', 'project' ) ) || $q->is_tax( 'repair_type' ) ) {
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		$q->set( 'posts_per_page', $q->is_post_type_archive( 'service' ) ? 20 : 9 );
	}
}
