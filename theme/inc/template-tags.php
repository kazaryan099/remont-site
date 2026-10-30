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
		// Знаки WhatsApp и Telegram — Simple Icons (CC0), MAX — maxicons.ru; одноцветные, красятся через currentColor.
		'whatsapp' => '<path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>',
		'telegram' => '<path fill="currentColor" d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>',
		'max'      => '<path fill="currentColor" d="M350.4,9.6C141.8,20.5,4.1,184.1,12.8,390.4c3.8,90.3,40.1,168,48.7,253.7,2.2,22.2-4.2,49.6,21.4,59.3,31.5,11.9,79.8-8.1,106.2-26.4,9-6.1,17.6-13.2,24.2-22,27.3,18.1,53.2,35.6,85.7,43.4,143.1,34.3,299.9-44.2,369.6-170.3C799.6,291.2,622.5-4.6,350.4,9.6h0ZM269.4,504c-11.3,8.8-22.2,20.8-34.7,27.7-18.1,9.7-23.7-.4-30.5-16.4-21.4-50.9-24-137.6-11.5-190.9,16.8-72.5,72.9-136.3,150-143.1,78-6.9,150.4,32.7,183.1,104.2,72.4,159.1-112.9,316.2-256.4,218.6h0Z"/>',
	);
	$viewbox = 'max' === $key ? '0 0 720 720' : '0 0 24 24';
	return isset( $paths[ $key ] ) ? '<svg class="icon icon--' . esc_attr( $key ) . '" viewBox="' . $viewbox . '" aria-hidden="true" focusable="false">' . $paths[ $key ] . '</svg>' : '';
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
