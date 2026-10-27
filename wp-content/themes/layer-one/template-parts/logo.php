<?php
/**
 * Logo: the uploaded custom logo, else the Layer One mark with the two-part wordmark.
 */
if ( has_custom_logo() ) {
	echo '<span class="logo">' . get_custom_logo() . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
	return;
}
?>
<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
  <svg viewBox="0 0 40 40" fill="none" aria-hidden="true"><path d="M20 4 35 12.5 20 21 5 12.5z" fill="#6B7A4F"/><path d="M5 12.5 20 21v15L5 27.5z" fill="#4A5E3A"/><path d="M35 12.5 20 21v15l15-8.5z" fill="#2A3520"/></svg>
  <span><?php echo esc_html( layerone_opt( 'lo_logo_a' ) ); ?><em><?php echo esc_html( layerone_opt( 'lo_logo_b' ) ); ?></em></span>
</a>
