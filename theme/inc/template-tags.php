<?php
/**
 * Вспомогательные функции вывода.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

/**
 * Встроенная SVG-иконка (декоративная, скрыта от скринридеров).
 */
function rmt_icon( $name, $class = '' ) {
	$paths = array(
		'arrow'   => '<path d="M0 6h42M36 1l6 5-6 5" fill="none" stroke="currentColor" stroke-width="1.2"/>',
		'phone'   => '<path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1l-2.3 2.2Z" fill="currentColor"/>',
		'menu'    => '<path d="M3 7h18M3 12h12M3 17h18" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round"/>',
		'close'   => '<path d="M5 5l14 14M19 5L5 19" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round"/>',
		'chevron' => '<path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>',
		'check'   => '<path d="M5 12.5l4.5 4.5L19 7.5" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>',
		'prev'    => '<path d="M76 6H1M7 1L1 6l6 5" fill="none" stroke="currentColor" stroke-width="1.2"/>',
		'next'    => '<path d="M0 6h75M69 1l6 5-6 5" fill="none" stroke="currentColor" stroke-width="1.2"/>',
	);
	$viewbox = array(
		'arrow' => '0 0 44 12',
		'prev'  => '0 0 77 12',
		'next'  => '0 0 77 12',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="icon icon--%1$s %2$s" viewBox="%3$s" aria-hidden="true" focusable="false">%4$s</svg>',
		esc_attr( $name ),
		esc_attr( $class ),
		esc_attr( $viewbox[ $name ] ?? '0 0 24 24' ),
		$paths[ $name ] // Статичная разметка из массива выше.
	);
}

/** Ссылка tel: из телефона в настройках. */
function rmt_phone_href() {
	return 'tel:+' . preg_replace( '/\D+/', '', (string) rmt_opt( 'phone' ) );
}

/**
 * Изображение по ID вложения с запасным вариантом-заглушкой.
 */
function rmt_image( $attachment_id, $size = 'large', $attr = array() ) {
	$attr = wp_parse_args( $attr, array( 'loading' => 'lazy', 'decoding' => 'async' ) );
	if ( $attachment_id && wp_attachment_is_image( $attachment_id ) ) {
		return wp_get_attachment_image( $attachment_id, $size, false, $attr );
	}
	return sprintf(
		'<img src="%s" alt="%s" class="%s" loading="lazy" decoding="async" width="1600" height="1000">',
		esc_url( RMT_URI . '/assets/img/placeholder.svg' ),
		esc_attr( $attr['alt'] ?? '' ),
		esc_attr( trim( ( $attr['class'] ?? '' ) . ' is-placeholder' ) )
	);
}

/** Миниатюра записи (или заглушка). */
function rmt_post_image( $post = null, $size = 'rmt-card', $attr = array() ) {
	$post = get_post( $post );
	return rmt_image( get_post_thumbnail_id( $post ), $size, $attr );
}

/** Заголовок секции: надзаголовок, заголовок, вводный текст. */
function rmt_section_head( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'kicker' => '',
			'title'  => '',
			'text'   => '',
			'tag'    => 'h2',
			'id'     => '',
			'class'  => '',
		)
	);
	$tag = in_array( $args['tag'], array( 'h1', 'h2', 'h3' ), true ) ? $args['tag'] : 'h2';
	echo '<header class="section-head ' . esc_attr( $args['class'] ) . '">';
	if ( $args['kicker'] ) {
		echo '<p class="kicker">' . esc_html( $args['kicker'] ) . '</p>';
	}
	printf(
		'<%1$s class="display"%2$s>%3$s</%1$s>',
		$tag, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- значение из белого списка.
		$args['id'] ? ' id="' . esc_attr( $args['id'] ) . '"' : '',
		esc_html( $args['title'] )
	);
	if ( $args['text'] ) {
		echo '<p class="lead">' . esc_html( $args['text'] ) . '</p>';
	}
	echo '</header>';
}

/** Ссылка-действие в стиле «ТЕКСТ ⟶». */
function rmt_link_arrow( $url, $text, $class = '' ) {
	return sprintf(
		'<a class="link-arrow %s" href="%s"><span>%s</span>%s</a>',
		esc_attr( $class ),
		esc_url( $url ),
		esc_html( $text ),
		rmt_icon( 'arrow' )
	);
}

/**
 * Хлебные крошки + микроразметка BreadcrumbList.
 */
function rmt_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	$items = array( array( __( 'Главная', 'remont' ), home_url( '/' ) ) );

	if ( is_singular( array( 'service', 'project' ) ) ) {
		$pt      = get_post_type_object( get_post_type() );
		$items[] = array( $pt->labels->name, get_post_type_archive_link( $pt->name ) );
		$items[] = array( get_the_title(), '' );
	} elseif ( is_post_type_archive() ) {
		$items[] = array( post_type_archive_title( '', false ), '' );
	} elseif ( is_tax( 'repair_type' ) ) {
		$items[] = array( get_post_type_object( 'project' )->labels->name, get_post_type_archive_link( 'project' ) );
		$items[] = array( single_term_title( '', false ), '' );
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $ancestor ) {
			$items[] = array( get_the_title( $ancestor ), get_permalink( $ancestor ) );
		}
		$items[] = array( get_the_title(), '' );
	} elseif ( is_search() ) {
		$items[] = array( __( 'Поиск', 'remont' ), '' );
	} elseif ( is_404() ) {
		$items[] = array( __( 'Страница не найдена', 'remont' ), '' );
	} elseif ( is_singular() ) {
		$items[] = array( get_the_title(), '' );
	}

	$schema = array();
	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Хлебные крошки', 'remont' ) . '"><ol>';
	foreach ( $items as $i => list( $label, $url ) ) {
		$schema[] = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => wp_strip_all_tags( $label ),
			'item'     => $url ?: rmt_current_url(),
		);
		echo '<li>';
		if ( $url ) {
			echo '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
		} else {
			echo '<span aria-current="page">' . esc_html( $label ) . '</span>';
		}
		echo '</li>';
	}
	echo '</ol></nav>';

	echo '<script type="application/ld+json">' . wp_json_encode(
		array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $schema,
		),
		JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
	) . '</script>';
}

/**
 * Меню по умолчанию, пока администратор не назначил своё.
 */
function rmt_fallback_menu( $args = array() ) {
	$services = get_posts(
		array(
			'post_type'      => 'service',
			'posts_per_page' => 10,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);
	$about    = get_page_by_path( 'o-kompanii' );
	$contacts = get_page_by_path( 'kontakty' );

	echo '<ul class="' . esc_attr( $args['menu_class'] ?? 'menu' ) . '">';
	echo '<li class="menu-item' . ( $services ? ' menu-item-has-children' : '' ) . '"><a href="' . esc_url( get_post_type_archive_link( 'service' ) ) . '">' . esc_html__( 'Услуги', 'remont' ) . '</a>';
	if ( $services ) {
		echo '<ul class="sub-menu">';
		foreach ( $services as $s ) {
			echo '<li class="menu-item"><a href="' . esc_url( get_permalink( $s ) ) . '">' . esc_html( get_the_title( $s ) ) . '</a></li>';
		}
		echo '</ul>';
	}
	echo '</li>';
	echo '<li class="menu-item"><a href="' . esc_url( get_post_type_archive_link( 'project' ) ) . '">' . esc_html__( 'Портфолио', 'remont' ) . '</a></li>';
	echo '<li class="menu-item"><a href="' . esc_url( home_url( '/#calculator' ) ) . '">' . esc_html__( 'Стоимость', 'remont' ) . '</a></li>';
	if ( $about ) {
		echo '<li class="menu-item"><a href="' . esc_url( get_permalink( $about ) ) . '">' . esc_html( get_the_title( $about ) ) . '</a></li>';
	}
	if ( $contacts ) {
		echo '<li class="menu-item"><a href="' . esc_url( get_permalink( $contacts ) ) . '">' . esc_html( get_the_title( $contacts ) ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * Пункт-якорь («Стоимость» → /#calculator) ведёт на блок внутри главной, а не на отдельный раздел.
 * WordPress считает его текущим на главной и подсвечивает — снимаем эту отметку.
 */
add_filter( 'wp_nav_menu_objects', 'rmt_menu_unmark_anchors' );
function rmt_menu_unmark_anchors( $items ) {
	foreach ( $items as $item ) {
		if ( false !== strpos( (string) $item->url, '#' ) ) {
			$item->current = false;
			$item->classes = array_diff( (array) $item->classes, array( 'current-menu-item', 'current_page_item', 'current-menu-ancestor', 'current-menu-parent' ) );
		}
	}
	return $items;
}

/**
 * Кнопки раскрытия подменю для клавиатуры и сенсорных экранов.
 */
add_filter( 'walker_nav_menu_start_el', 'rmt_submenu_toggle', 10, 4 );
function rmt_submenu_toggle( $item_output, $item, $depth, $args ) {
	if ( in_array( 'menu-item-has-children', (array) $item->classes, true ) && 0 === $depth && in_array( $args->theme_location ?? '', array( 'primary' ), true ) ) {
		$item_output .= '<button class="submenu-toggle" type="button" aria-expanded="false" aria-label="' . esc_attr( sprintf( __( 'Открыть подменю «%s»', 'remont' ), $item->title ) ) . '">' . rmt_icon( 'chevron' ) . '</button>';
	}
	return $item_output;
}

/** Характеристики проекта для карточек и страницы проекта. */
function rmt_project_specs( $post_id = null ) {
	$specs = array();
	$map   = array(
		'area'     => array( __( 'Площадь', 'remont' ), ' м²' ),
		'duration' => array( __( 'Срок', 'remont' ), '' ),
		'rooms'    => array( __( 'Планировка', 'remont' ), '' ),
		'building' => array( __( 'Дом', 'remont' ), '' ),
		'location' => array( __( 'Район', 'remont' ), '' ),
		'year'     => array( __( 'Год', 'remont' ), '' ),
		'stage'    => array( __( 'Стадия', 'remont' ), '' ),
	);
	foreach ( $map as $key => list( $label, $suffix ) ) {
		$value = rmt_meta( $key, $post_id );
		if ( '' !== (string) $value ) {
			$specs[ $key ] = array(
				'label' => $label,
				'value' => $value . $suffix,
			);
		}
	}
	return $specs;
}

/** Первый вид ремонта проекта. */
function rmt_project_type( $post_id = null ) {
	$terms = get_the_terms( $post_id ?: get_the_ID(), 'repair_type' );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
}
