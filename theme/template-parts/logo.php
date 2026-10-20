<?php
/**
 * Логотип: загруженный в настройках или текстовый.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

$rmt_logo_id = get_theme_mod( 'custom_logo' );
?>
<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" <?php echo is_front_page() ? 'aria-current="page"' : ''; ?>>
	<?php if ( $rmt_logo_id ) : ?>
		<?php echo wp_get_attachment_image( $rmt_logo_id, 'full', false, array( 'class' => 'logo__img', 'alt' => get_bloginfo( 'name' ), 'loading' => 'eager' ) ); ?>
	<?php else : ?>
		<svg class="logo__mark" viewBox="0 0 32 32" aria-hidden="true" focusable="false"><path d="M3 29V13L16 3l13 10v16H19V19h-6v10H3Z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M13 19h6" stroke="currentColor" stroke-width="2"/></svg>
		<span class="logo__text"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
	<?php endif; ?>
</a>
