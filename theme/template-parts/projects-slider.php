<?php
/**
 * Горизонтальная лента проектов с кнопками «Назад / Дальше».
 *
 * $args: kicker, title, text, term (ID вида ремонта), exclude (ID проекта), promo (bool), class
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

$rmt_query_args = array(
	'post_type'      => 'project',
	'posts_per_page' => 8,
	'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	'post__not_in'   => array_filter( array( (int) ( $args['exclude'] ?? 0 ) ) ),
	'no_found_rows'  => true,
);
if ( ! empty( $args['term'] ) ) {
	$rmt_query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
		array(
			'taxonomy' => 'repair_type',
			'terms'    => (int) $args['term'],
		),
	);
}
$rmt_projects = get_posts( $rmt_query_args );
if ( ! $rmt_projects ) {
	return;
}
$rmt_id = 'projects-' . wp_unique_id();
?>
<section class="section projects <?php echo esc_attr( $args['class'] ?? '' ); ?>" aria-labelledby="<?php echo esc_attr( $rmt_id ); ?>-title">
	<div class="container">
		<div class="section-head-row">
			<?php
			rmt_section_head(
				array(
					'kicker' => $args['kicker'] ?? '',
					'title'  => $args['title'] ?? '',
					'text'   => $args['text'] ?? '',
					'id'     => $rmt_id . '-title',
				)
			);
			?>
			<a class="btn btn--shadow" href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>"><?php esc_html_e( 'Смотреть все проекты', 'remont' ); ?> <?php echo rmt_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		</div>

		<div class="slider" data-slider>
			<ul class="slider__track" id="<?php echo esc_attr( $rmt_id ); ?>-track" role="list" tabindex="0" aria-label="<?php esc_attr_e( 'Список проектов, прокручивается горизонтально', 'remont' ); ?>">
				<?php if ( ! empty( $args['promo'] ) ) : ?>
					<li class="slider__item slider__item--promo">
						<div class="promo-card">
							<p class="kicker"><?php esc_html_e( 'Расчёт', 'remont' ); ?></p>
							<h3 class="promo-card__title display"><?php esc_html_e( 'Посчитайте свой ремонт', 'remont' ); ?></h3>
							<p><?php esc_html_e( 'Выберите тип ремонта и площадь — стоимость и состав работ пересчитываются сразу.', 'remont' ); ?></p>
							<?php echo rmt_link_arrow( home_url( '/#calculator' ), __( 'Открыть калькулятор', 'remont' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</li>
				<?php endif; ?>
				<?php foreach ( $rmt_projects as $rmt_project ) : ?>
					<li class="slider__item">
						<?php get_template_part( 'template-parts/card-project', null, array( 'post' => $rmt_project, 'heading' => 'h3' ) ); ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<div class="slider__nav">
				<button class="slider__btn" type="button" data-slider-prev aria-controls="<?php echo esc_attr( $rmt_id ); ?>-track">
					<?php echo rmt_icon( 'prev' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Назад', 'remont' ); ?></span>
				</button>
				<button class="slider__btn" type="button" data-slider-next aria-controls="<?php echo esc_attr( $rmt_id ); ?>-track">
					<span><?php esc_html_e( 'Дальше', 'remont' ); ?></span><?php echo rmt_icon( 'next' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
			</div>
		</div>
	</div>
</section>
