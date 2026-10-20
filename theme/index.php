<?php
/**
 * Запасной шаблон: блог, поиск и всё, для чего нет отдельного шаблона.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( is_search() ) {
	/* translators: %s: поисковый запрос */
	$rmt_title = sprintf( __( 'Поиск: %s', 'remont' ), get_search_query() );
} elseif ( is_home() ) {
	$rmt_title = get_the_title( (int) get_option( 'page_for_posts' ) ) ?: __( 'Статьи', 'remont' );
} else {
	$rmt_title = wp_strip_all_tags( get_the_archive_title() );
}

get_template_part( 'template-parts/page-hero', null, array( 'title' => $rmt_title ) );
?>
<section class="section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<ul class="post-list" role="list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<li <?php post_class( 'post-list__item' ); ?>>
						<h2 class="post-list__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p class="post-list__date"><?php echo esc_html( get_the_date() ); ?></p>
						<p><?php echo esc_html( get_the_excerpt() ); ?></p>
					</li>
				<?php endwhile; ?>
			</ul>
			<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
		<?php else : ?>
			<p class="lead"><?php esc_html_e( 'Ничего не найдено.', 'remont' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php
get_template_part( 'template-parts/lead-form' );
get_footer();
