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

/**
 * Виды ремонта с проектами — в том же порядке, что и одноимённые услуги («Ремонт квартир», затем «Ремонт домов»).
 *
 * @return WP_Term[]
 */
function rmt_repair_types() {
	$terms = get_terms( array( 'taxonomy' => 'repair_type', 'hide_empty' => true ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	$order = array_flip( wp_list_pluck( get_posts( array( 'post_type' => 'service', 'posts_per_page' => 20, 'orderby' => 'menu_order', 'order' => 'ASC' ) ), 'post_name' ) );
	usort(
		$terms,
		function ( $a, $b ) use ( $order ) {
			return ( $order[ $a->slug ] ?? PHP_INT_MAX ) <=> ( $order[ $b->slug ] ?? PHP_INT_MAX ) ?: strcmp( $a->name, $b->name );
		}
	);
	return $terms;
}

/**
 * Мессенджеры для кнопок «Написать»: WhatsApp и Telegram — по ссылке из настроек или по номеру телефона,
 * MAX — только по ссылке из настроек (открыть чат MAX по номеру телефона ссылкой нельзя).
 *
 * @return array<string, array{label:string,url:string}>
 */
function rmt_messengers() {
	$digits = preg_replace( '/\D+/', '', (string) rmt_opt( 'phone' ) );
	if ( 11 === strlen( $digits ) && '8' === $digits[0] ) {
		$digits = '7' . substr( $digits, 1 ); // 8 906 … → 7 906 …
	}
	$by_phone = strlen( $digits ) >= 10 && ! preg_match( '/^7?0{10}$/', $digits ); // Заглушка +7 (000) — не в счёт.
	$list     = array(
		'whatsapp' => array( 'WhatsApp', rmt_opt( 'whatsapp' ) ?: ( $by_phone ? 'https://wa.me/' . $digits : '' ) ),
		'telegram' => array( 'Telegram', rmt_opt( 'telegram' ) ?: ( $by_phone ? 'https://t.me/+' . $digits : '' ) ),
		'max'      => array( 'MAX', rmt_opt( 'max' ) ),
	);
	$out = array();
	foreach ( $list as $key => list( $label, $url ) ) {
		if ( $url ) {
			$out[ $key ] = array( 'label' => $label, 'url' => $url );
		}
	}
	return $out;
}

/** Значок мессенджера (упрощённые знаки, currentColor). */
function rmt_messenger_icon( $key ) {
	$paths = array(
		'whatsapp' => '<path d="M12 3a9 9 0 0 0-7.8 13.5L3 21l4.6-1.2A9 9 0 1 0 12 3Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 8.2c.2-.4.5-.4.7-.4h.5c.2 0 .4.1.5.4l.7 1.6c.1.2 0 .5-.1.6l-.5.6c-.1.2-.1.4 0 .5.6 1 1.4 1.8 2.5 2.4.2.1.4.1.5 0l.6-.7c.2-.2.4-.2.6-.1l1.6.8c.2.1.3.3.3.5v.4c0 .4-.2.8-.6 1-.6.3-1.4.4-2.2.1-2.4-.9-4.3-2.8-5.2-5.1-.3-.8-.2-1.6.1-2.2Z" fill="currentColor"/>',
		'telegram' => '<path d="M21 4.5 2.9 11.4c-.9.4-.9 1.5 0 1.8l4.5 1.5 1.7 5.2c.2.6 1 .8 1.5.4l2.5-2.1 4.6 3.4c.6.4 1.4.1 1.6-.6L22.5 6c.2-1-.6-1.8-1.5-1.5Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="m7.6 14.6 9.6-6.2-7 7.5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>',
		'max'      => '<path d="M12 3a9 9 0 0 0-8.2 12.7L3 21l5.4-.9A9 9 0 1 0 12 3Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M7.5 15V9.2l2.4 3.4 2.4-3.4V15M14.2 15l2.3-6 2.2 6M15 13.2h3" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" transform="translate(-1.5 0) scale(.92) translate(1 1)"/>',
	);
	return isset( $paths[ $key ] ) ? '<svg class="icon icon--' . esc_attr( $key ) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $paths[ $key ] . '</svg>' : '';
}

/** Кнопки мессенджеров: значок + название. $class — модификатор группы. */
function rmt_messenger_links( $class = '' ) {
	$list = rmt_messengers();
	if ( ! $list ) {
		return '';
	}
	$html = '<span class="messengers ' . esc_attr( $class ) . '">';
	foreach ( $list as $key => $m ) {
		$html .= sprintf(
			'<a class="messenger messenger--%1$s" href="%2$s" target="_blank" rel="noopener">%3$s<span>%4$s</span></a>',
			esc_attr( $key ),
			esc_url( $m['url'] ),
			rmt_messenger_icon( $key ),
			esc_html( $m['label'] )
		);
	}
	return $html . '</span>';
}
