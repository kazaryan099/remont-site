<?php
/**
 * Страница проекта: /portfolio/<slug>/
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$rmt_specs   = rmt_project_specs();
	$rmt_tasks   = rmt_meta_lines( 'tasks' );
	$rmt_gallery = rmt_gallery_ids();
	$rmt_type    = rmt_project_type();

	get_template_part(
		'template-parts/page-hero',
		null,
		array(
			'variant' => 'photo',
			'kicker'  => $rmt_type ? $rmt_type->name : __( 'Проект', 'remont' ),
			'title'   => get_the_title(),
			'text'    => has_excerpt() ? get_the_excerpt() : '',
			'image'   => get_post_thumbnail_id(),
			'actions' => '<a class="btn" href="#lead-form">' . esc_html__( 'Хочу такой же ремонт', 'remont' ) . '</a>'
				. ( $rmt_gallery ? rmt_link_arrow( '#gallery', rmt_meta( 'is_render' ) ? __( 'Смотреть визуализации', 'remont' ) : __( 'Смотреть фото', 'remont' ), 'link-arrow--light' ) : '' ),
		)
	);

	if ( $rmt_specs ) :
		?>
		<div class="facts facts--specs">
			<dl class="facts__list container">
				<?php foreach ( $rmt_specs as $rmt_spec ) : ?>
					<div class="facts__item">
						<dt><?php echo esc_html( $rmt_spec['label'] ); ?></dt>
						<dd><?php echo esc_html( $rmt_spec['value'] ); ?></dd>
					</div>
				<?php endforeach; ?>
			</dl>
		</div>
	<?php endif; ?>

	<section class="section">
		<div class="container">
			<?php if ( rmt_meta( 'is_demo' ) ) : ?>
				<p class="notice"><?php esc_html_e( 'Демонстрационный пример: фотографии и характеристики приведены для показа структуры страницы и будут заменены реальным объектом.', 'remont' ); ?></p>
			<?php elseif ( rmt_meta( 'is_render' ) ) : ?>
				<p class="notice"><?php esc_html_e( 'Изображения на странице — визуализации дизайн-проекта.', 'remont' ); ?></p>
			<?php endif; ?>
			<div class="content-grid">
				<div class="prose">
					<?php the_content(); ?>
				</div>
				<?php if ( $rmt_tasks ) : ?>
					<aside class="includes card" aria-labelledby="tasks-title">
						<h2 class="includes__title" id="tasks-title"><?php echo rmt_meta( 'is_render' ) ? esc_html__( 'Особенности проекта', 'remont' ) : esc_html__( 'Что сделано', 'remont' ); ?></h2>
						<ul class="check-list" role="list">
							<?php foreach ( $rmt_tasks as $rmt_task ) : ?>
								<li><?php echo rmt_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $rmt_task ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					</aside>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php if ( $rmt_gallery ) : ?>
		<section class="section section--paper" id="gallery" aria-labelledby="gallery-title">
			<div class="container">
				<?php
				rmt_section_head(
					array(
						'title' => rmt_meta( 'is_render' ) ? __( 'Визуализации', 'remont' ) : __( 'Фотографии', 'remont' ),
						'id'    => 'gallery-title',
					)
				);
				?>
				<ul class="gallery" role="list" data-gallery>
					<?php foreach ( $rmt_gallery as $rmt_i => $rmt_img ) : ?>
						<?php
						$rmt_full = wp_get_attachment_image_src( $rmt_img, 'rmt-hero' );
						$rmt_alt  = get_post_meta( $rmt_img, '_wp_attachment_image_alt', true ) ?: sprintf( __( '%1$s — фото %2$d', 'remont' ), get_the_title(), $rmt_i + 1 );
						if ( ! $rmt_full ) {
							continue;
						}
						?>
						<li class="gallery__item<?php echo 0 === $rmt_i ? ' gallery__item--wide' : ''; ?>">
							<a href="<?php echo esc_url( $rmt_full[0] ); ?>" data-gallery-item data-caption="<?php echo esc_attr( $rmt_alt ); ?>">
								<?php echo wp_get_attachment_image( $rmt_img, 0 === $rmt_i ? 'rmt-wide' : 'rmt-card', false, array( 'alt' => $rmt_alt, 'loading' => 'lazy', 'sizes' => '(min-width: 1024px) 33vw, 50vw' ) ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>

		<dialog class="lightbox" data-lightbox aria-label="<?php esc_attr_e( 'Просмотр фотографий', 'remont' ); ?>">
			<button class="lightbox__close" type="button" data-lightbox-close>
				<?php echo rmt_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="visually-hidden"><?php esc_html_e( 'Закрыть', 'remont' ); ?></span>
			</button>
			<figure class="lightbox__figure">
				<img src="data:image/gif;base64,R0lGODlhAQABAAAAACw=" alt="" data-lightbox-img>
				<figcaption data-lightbox-caption></figcaption>
			</figure>
			<button class="lightbox__nav lightbox__nav--prev" type="button" data-lightbox-prev>
				<?php echo rmt_icon( 'prev' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="visually-hidden"><?php esc_html_e( 'Предыдущее фото', 'remont' ); ?></span>
			</button>
			<button class="lightbox__nav lightbox__nav--next" type="button" data-lightbox-next>
				<?php echo rmt_icon( 'next' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="visually-hidden"><?php esc_html_e( 'Следующее фото', 'remont' ); ?></span>
			</button>
		</dialog>
	<?php endif; ?>

	<?php
	get_template_part(
		'template-parts/projects-slider',
		null,
		array(
			'kicker'  => __( 'Портфолио', 'remont' ),
			'title'   => __( 'Другие проекты', 'remont' ),
			'exclude' => get_the_ID(),
		)
	);
	get_template_part( 'template-parts/lead-form', null, array( 'type' => $rmt_type ? (string) get_term_meta( $rmt_type->term_id, 'calc_type', true ) : '' ) );
endwhile;

get_footer();
