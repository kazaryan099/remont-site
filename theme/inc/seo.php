<?php
/**
 * SEO: описание страниц, канонические адреса архивов, Open Graph, микроразметка организации.
 * Если установлен Yoast SEO или Rank Math — модуль уступает им и ничего не выводит.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

function rmt_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' );
}

/** Текущий адрес страницы без параметров запроса. */
function rmt_current_url() {
	global $wp;
	return home_url( trailingslashit( $wp->request ?? '' ) );
}

/** Описание текущей страницы для meta description и og:description. */
function rmt_meta_description() {
	$desc = '';
	if ( is_front_page() ) {
		$desc = rmt_opt( 'home_desc' ) ?: rmt_opt( 'hero_text' );
	} elseif ( is_singular() ) {
		$post = get_queried_object();
		$desc = get_post_meta( $post->ID, '_rmt_seo_desc', true );
		if ( ! $desc ) {
			$desc = has_excerpt( $post ) ? $post->post_excerpt : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 30, '…' );
		}
	} elseif ( is_post_type_archive( 'service' ) ) {
		$desc = __( 'Виды ремонта квартир: косметический, капитальный, дизайнерский, в новостройке и под ключ. Состав работ и порядок расчёта стоимости.', 'remont' );
	} elseif ( is_post_type_archive( 'project' ) ) {
		$desc = __( 'Портфолио: реализованные ремонты и дизайн-проекты квартир и домов — фотографии, визуализации, площадь и состав работ.', 'remont' );
	} elseif ( is_tax() ) {
		$desc = term_description();
		if ( ! trim( wp_strip_all_tags( (string) $desc ) ) ) {
			/* translators: %s: вид ремонта */
			$desc = sprintf( __( 'Примеры работ: %s. Фотографии, площадь и состав работ по каждому объекту.', 'remont' ), single_term_title( '', false ) );
		}
	}
	return trim( wp_html_excerpt( wp_strip_all_tags( (string) $desc ), 300, '…' ) );
}

/** Картинка для соцсетей. */
function rmt_og_image() {
	$id = 0;
	if ( is_singular() && has_post_thumbnail() ) {
		$id = get_post_thumbnail_id();
	}
	if ( ! $id ) {
		$id = (int) rmt_opt( 'hero_image' );
	}
	$src = $id ? wp_get_attachment_image_src( $id, 'rmt-wide' ) : false;
	return $src ? $src : null;
}

add_action( 'wp_head', 'rmt_seo_head', 2 );
function rmt_seo_head() {
	if ( rmt_seo_plugin_active() ) {
		return;
	}
	$desc  = rmt_meta_description();
	$url   = is_singular() ? get_permalink() : rmt_current_url();
	$image = rmt_og_image();

	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
	// У записей канонический адрес выводит ядро WordPress; для архивов и главной — сами.
	if ( ! is_singular() && ! is_404() && ! is_search() ) {
		$paged = max( 1, (int) get_query_var( 'paged' ) );
		echo '<link rel="canonical" href="' . esc_url( $paged > 1 ? get_pagenum_link( $paged ) : $url ) . '">' . "\n";
	}

	$og = array(
		'og:locale'      => 'ru_RU',
		'og:type'        => is_singular( array( 'post', 'project' ) ) ? 'article' : 'website',
		'og:site_name'   => get_bloginfo( 'name' ),
		'og:title'       => wp_get_document_title(),
		'og:description' => $desc,
		'og:url'         => $url,
	);
	if ( $image ) {
		$og['og:image']        = $image[0];
		$og['og:image:width']  = $image[1];
		$og['og:image:height'] = $image[2];
	}
	foreach ( $og as $property => $content ) {
		if ( '' !== (string) $content ) {
			echo '<meta property="' . esc_attr( $property ) . '" content="' . esc_attr( $content ) . '">' . "\n";
		}
	}
	echo '<meta name="twitter:card" content="' . ( $image ? 'summary_large_image' : 'summary' ) . '">' . "\n";

	if ( is_front_page() ) {
		rmt_schema_organization();
	}
}

/**
 * Микроразметка организации. Телефон, почта и регион попадают в неё,
 * только если администратор отметил, что контакты проверены. Рейтинги и отзывы не выводятся.
 */
function rmt_schema_organization() {
	$data = array(
		'@context' => 'https://schema.org',
		'@type'    => 'HomeAndConstructionBusiness',
		'name'     => get_bloginfo( 'name' ),
		'url'      => home_url( '/' ),
	);
	$logo_id = get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$data['logo'] = wp_get_attachment_image_url( $logo_id, 'full' );
	}
	if ( rmt_opt( 'schema_confirmed' ) ) {
		$data['telephone'] = '+' . preg_replace( '/\D+/', '', (string) rmt_opt( 'phone' ) );
		if ( is_email( rmt_opt( 'email' ) ) ) {
			$data['email'] = rmt_opt( 'email' );
		}
		if ( rmt_opt( 'region' ) ) {
			$data['areaServed'] = rmt_opt( 'region' );
		}
		if ( rmt_opt( 'address' ) ) {
			$data['address'] = rmt_opt( 'address' );
		}
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}

/** Служебные страницы не индексируются. */
add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( is_search() || is_404() ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
		}
		return $robots;
	}
);

/** Заголовок вкладки: «Название страницы — Сайт». */
add_filter( 'document_title_separator', fn() => '—' );

/* ---------- Поле «Описание для поисковиков» ---------- */

add_action( 'add_meta_boxes', 'rmt_seo_meta_box' );
function rmt_seo_meta_box() {
	if ( rmt_seo_plugin_active() ) {
		return;
	}
	add_meta_box( 'rmt_seo', __( 'Описание для поисковиков', 'remont' ), 'rmt_seo_render_box', array( 'page', 'service', 'project', 'post' ), 'side', 'low' );
}

function rmt_seo_render_box( $post ) {
	wp_nonce_field( 'rmt_seo', 'rmt_seo_nonce' );
	echo '<label class="screen-reader-text" for="rmt-seo-desc">' . esc_html__( 'Описание', 'remont' ) . '</label>';
	echo '<textarea id="rmt-seo-desc" name="rmt_seo_desc" rows="4" style="width:100%" maxlength="300">' . esc_textarea( get_post_meta( $post->ID, '_rmt_seo_desc', true ) ) . '</textarea>';
	echo '<p class="description">' . esc_html__( '140–160 символов. Если пусто — берётся отрывок или начало текста.', 'remont' ) . '</p>';
}

add_action( 'save_post', 'rmt_seo_save' );
function rmt_seo_save( $post_id ) {
	if ( ! isset( $_POST['rmt_seo_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['rmt_seo_nonce'] ) ), 'rmt_seo' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$desc = sanitize_textarea_field( wp_unslash( $_POST['rmt_seo_desc'] ?? '' ) );
	if ( $desc ) {
		update_post_meta( $post_id, '_rmt_seo_desc', mb_substr( $desc, 0, 300 ) );
	} else {
		delete_post_meta( $post_id, '_rmt_seo_desc' );
	}
}
