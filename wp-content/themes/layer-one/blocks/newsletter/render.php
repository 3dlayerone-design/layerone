<?php
/**
 * Drop list signup block, front end.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}
$status = isset( $_GET['droplist'] ) ? sanitize_key( wp_unslash( $_GET['droplist'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
?>
<div class="wrap"><div class="news">
  <h2><?php echo esc_html( $attributes['heading'] ); ?><?php if ( $attributes['headingAccent'] ) : ?> <span class="serif"><?php echo esc_html( $attributes['headingAccent'] ); ?></span><?php endif; ?><?php echo ! empty( $attributes['tail'] ) ? ' ' . esc_html( $attributes['tail'] ) : ''; ?></h2>
  <form id="nf" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-thanks="<?php echo esc_attr( $attributes['thanks'] ); ?>">
    <input type="hidden" name="action" value="layerone_droplist">
    <input type="hidden" name="_lo_nonce" value="<?php echo esc_attr( wp_create_nonce( 'layerone_droplist' ) ); ?>">
    <input type="email" name="email" placeholder="<?php echo esc_attr( $attributes['placeholder'] ); ?>" required aria-label="Email">
    <button class="btn btn-dark btn-sm"><?php echo esc_html( $attributes['button'] ); ?></button>
  </form>
  <?php if ( 'ok' === $status ) : ?><p role="status" style="width:100%"><?php echo esc_html( $attributes['thanks'] ); ?></p><?php endif; ?>
</div></div>
