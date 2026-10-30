<?php
/**
 * Template Name: Контакты
 * Template Post Type: page
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	get_template_part(
		'template-parts/page-hero',
		null,
		array(
			'kicker'  => __( 'Контакты', 'remont' ),
			'title'   => get_the_title(),
			'text'    => has_excerpt() ? get_the_excerpt() : '',
			'image'   => get_post_thumbnail_id(),
			'actions' => '<a class="btn" href="' . esc_url( rmt_phone_href() ) . '">' . esc_html( rmt_opt( 'phone' ) ) . '</a>'
				. rmt_link_arrow( '#lead-form', __( 'Написать нам', 'remont' ) ),
		)
	);

	$rmt_cards = array(
		array( __( 'Телефон', 'remont' ), '<a href="' . esc_url( rmt_phone_href() ) . '">' . esc_html( rmt_opt( 'phone' ) ) . '</a>' ),
		array( __( 'Электронная почта', 'remont' ), rmt_opt( 'email' ) ? '<a href="' . esc_url( 'mailto:' . rmt_opt( 'email' ) ) . '">' . esc_html( rmt_opt( 'email' ) ) . '</a>' : '' ),
		array( __( 'Регион работы', 'remont' ), esc_html( rmt_opt( 'region' ) ) ),
		array( __( 'График', 'remont' ), esc_html( rmt_opt( 'schedule' ) ) ),
		array( __( 'Офис', 'remont' ), esc_html( rmt_opt( 'address' ) ) ),
	);
	?>
	<section class="section" aria-labelledby="contacts-title">
		<div class="container">
			<h2 class="visually-hidden" id="contacts-title"><?php esc_html_e( 'Как с нами связаться', 'remont' ); ?></h2>
			<dl class="contact-cards">
				<?php foreach ( $rmt_cards as list( $rmt_label, $rmt_value ) ) : ?>
					<?php if ( '' === $rmt_value ) { continue; } ?>
					<div class="contact-card">
						<dt><?php echo esc_html( $rmt_label ); ?></dt>
						<dd><?php echo $rmt_value; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- экранировано при сборке массива. ?></dd>
					</div>
				<?php endforeach; ?>
				<?php if ( rmt_messengers() ) : ?>
					<div class="contact-card">
						<dt><?php esc_html_e( 'Мессенджеры', 'remont' ); ?></dt>
						<dd><?php echo rmt_messenger_links(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
					</div>
				<?php endif; ?>
			</dl>
			<?php if ( rmt_opt_pairs( 'requisites' ) ) : ?>
				<section class="requisites card" aria-labelledby="requisites-title">
					<h2 class="requisites__title" id="requisites-title"><?php esc_html_e( 'Реквизиты', 'remont' ); ?></h2>
					<dl class="requisites__list">
						<?php foreach ( rmt_opt_pairs( 'requisites' ) as $rmt_row ) : ?>
							<div><dt><?php echo esc_html( $rmt_row['title'] ); ?></dt><dd><?php echo esc_html( $rmt_row['text'] ); ?></dd></div>
						<?php endforeach; ?>
					</dl>
				</section>
			<?php endif; ?>
			<?php if ( get_the_content() ) : ?>
				<div class="prose contact-prose"><?php the_content(); ?></div>
			<?php endif; ?>
		</div>
	</section>
	<?php
endwhile;

get_template_part(
	'template-parts/lead-form',
	null,
	array(
		'title' => __( 'Напишите нам', 'remont' ),
		'text'  => __( 'Оставьте телефон и пару слов о задаче — перезвоним в рабочее время.', 'remont' ),
	)
);
get_footer();
