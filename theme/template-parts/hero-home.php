<?php
/**
 * Первый экран главной.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

// Фото — декоративный фон, поэтому alt пустой. Без фото — одна заглушка.
$rmt_slides = rmt_hero_images() ?: array( 0 );
?>
<section class="hero" aria-labelledby="hero-title"<?php echo count( $rmt_slides ) > 1 ? ' data-hero-slider' : ''; ?>>
	<div class="hero__media">
		<?php foreach ( $rmt_slides as $i => $rmt_slide ) : ?>
			<div class="hero__slide<?php echo 0 === $i ? ' is-active' : ''; ?>" data-hero-slide>
				<?php
				echo rmt_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					$rmt_slide,
					'rmt-hero',
					0 === $i
						? array( 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw', 'alt' => '' )
						: array( 'sizes' => '100vw', 'alt' => '' )
				);
				?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php if ( count( $rmt_slides ) > 1 ) : ?>
		<div class="hero__controls container">
			<div class="hero__dots" role="group" aria-label="<?php esc_attr_e( 'Фото на первом экране', 'remont' ); ?>">
				<?php foreach ( $rmt_slides as $i => $rmt_slide ) : ?>
					<button class="hero__dot" type="button" data-hero-dot="<?php echo (int) $i; ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: номер фото, 2: всего фото */ __( 'Фото %1$d из %2$d', 'remont' ), $i + 1, count( $rmt_slides ) ) ); ?>"<?php echo 0 === $i ? ' aria-current="true"' : ''; ?>></button>
				<?php endforeach; ?>
			</div>
			<?php // Подпись: из какого проекта фото, ссылка на его страницу. Видна подпись только текущего фото. ?>
			<div class="hero__captions">
				<?php foreach ( $rmt_slides as $i => $rmt_slide ) : ?>
					<?php $rmt_project = rmt_image_project( $rmt_slide ); ?>
					<?php if ( $rmt_project ) : ?>
						<a class="hero__caption link-arrow link-arrow--light<?php echo 0 === $i ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_permalink( $rmt_project ) ); ?>" data-hero-caption="<?php echo (int) $i; ?>">
							<span><?php echo esc_html( get_the_title( $rmt_project ) ); ?></span>
						</a>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<button class="hero__pause" type="button" data-hero-pause aria-pressed="false" aria-label="<?php esc_attr_e( 'Остановить смену фото', 'remont' ); ?>">
				<svg class="hero__pause-icon" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M4 2h3v12H4zM9 2h3v12H9z"/></svg>
				<svg class="hero__play-icon" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M4 2l10 6-10 6z"/></svg>
			</button>
		</div>
	<?php endif; ?>
	<div class="hero__content container">
		<?php if ( rmt_opt( 'hero_kicker' ) ) : ?>
			<p class="kicker kicker--light">
				<a class="hero__kicker-link" href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>"><?php echo esc_html( rmt_opt( 'hero_kicker' ) ); ?><?php echo rmt_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			</p>
		<?php endif; ?>
		<h1 class="hero__title display" id="hero-title"><?php echo esc_html( rmt_opt( 'hero_title' ) ); ?></h1>
		<?php if ( rmt_opt( 'hero_text' ) ) : ?>
			<p class="hero__text"><?php echo esc_html( rmt_opt( 'hero_text' ) ); ?></p>
		<?php endif; ?>
		<div class="hero__actions">
			<a class="btn btn--lg" href="#calculator"><?php echo esc_html( rmt_opt( 'hero_btn' ) ); ?></a>
			<?php echo rmt_link_arrow( '#lead-form', rmt_opt( 'hero_link' ), 'link-arrow--light link-arrow--lg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
</section>
<?php
get_template_part( 'template-parts/facts', null, array( 'items' => rmt_opt_pairs( 'facts' ) ) );
