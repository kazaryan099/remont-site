<?php
/**
 * Страница услуги: /uslugi/<slug>/
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$rmt_calc_key = rmt_meta( 'calc_type' );
	$rmt_includes = rmt_meta_lines( 'includes' );
	$rmt_term     = get_term_by( 'slug', get_post_field( 'post_name' ), 'repair_type' );

	// Ориентир из калькулятора для правой карточки.
	$rmt_rate = null;
	foreach ( rmt_calc_settings()['types'] as $rmt_t ) {
		if ( $rmt_t['key'] === $rmt_calc_key ) {
			$rmt_rate = $rmt_t;
		}
	}

	ob_start();
	?>
	<div class="price-card">
		<p class="price-card__kicker"><?php esc_html_e( 'Ориентир по калькулятору', 'remont' ); ?></p>
		<?php if ( rmt_meta( 'price' ) ) : ?>
			<p class="price-card__value"><?php echo esc_html( rmt_meta( 'price' ) ); ?></p>
		<?php elseif ( $rmt_rate ) : ?>
			<p class="price-card__value"><?php echo esc_html( sprintf( __( '%s за м²', 'remont' ), rmt_money( $rmt_rate['rate'] ) ) ); ?></p>
		<?php endif; ?>
		<p class="price-card__meta">
			<?php echo rmt_meta( 'duration' ) ? esc_html( sprintf( __( 'Срок: %s', 'remont' ), rmt_meta( 'duration' ) ) ) : esc_html__( 'Срок — по графику после замера', 'remont' ); ?>
			<?php if ( rmt_calc_settings()['demo'] && ! rmt_meta( 'price' ) ) : ?>
				<br><strong><?php esc_html_e( 'Демонстрационная ставка', 'remont' ); ?></strong>
			<?php endif; ?>
		</p>
		<a class="btn btn--block" href="#calculator"><?php esc_html_e( 'Посчитать свой ремонт', 'remont' ); ?> →</a>
	</div>
	<?php
	$rmt_aside = ob_get_clean();

	get_template_part(
		'template-parts/page-hero',
		null,
		array(
			'variant' => 'photo',
			'kicker'  => rmt_meta( 'kicker' ),
			'title'   => get_the_title(),
			'text'    => has_excerpt() ? get_the_excerpt() : '',
			'image'   => get_post_thumbnail_id(),
			'aside'   => $rmt_aside,
			'actions' => '<a class="btn" href="#lead-form">' . esc_html__( 'Вызвать замерщика', 'remont' ) . '</a>',
		)
	);
	?>

	<section class="section">
		<div class="container content-grid">
			<div class="prose">
				<?php the_content(); ?>
			</div>
			<?php if ( $rmt_includes ) : ?>
				<aside class="includes card" aria-labelledby="includes-title">
					<h2 class="includes__title" id="includes-title"><?php esc_html_e( 'Что входит', 'remont' ); ?></h2>
					<ul class="check-list" role="list">
						<?php foreach ( $rmt_includes as $rmt_item ) : ?>
							<li><?php echo rmt_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $rmt_item ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				</aside>
			<?php endif; ?>
		</div>
	</section>

	<?php
	if ( $rmt_term ) {
		get_template_part(
			'template-parts/projects-slider',
			null,
			array(
				'kicker' => __( 'Портфолио', 'remont' ),
				'title'  => sprintf( __( 'Примеры: %s', 'remont' ), mb_strtolower( get_the_title() ) ),
				'term'   => $rmt_term->term_id,
			)
		);
	}
	get_template_part( 'template-parts/calculator', null, array( 'type' => $rmt_calc_key ) );
	get_template_part( 'template-parts/steps' );
	get_template_part( 'template-parts/faq' );
	get_template_part(
		'template-parts/services-grid',
		null,
		array(
			'title'   => __( 'Другие виды ремонта', 'remont' ),
			'exclude' => get_the_ID(),
		)
	);
	get_template_part( 'template-parts/lead-form', null, array( 'type' => $rmt_calc_key ) );
endwhile;

get_footer();
