<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Track an order with the order number and the mobile number used for it. No sign-in needed.
 */

// phpcs:disable WordPress.Security.NonceVerification
$error = '';
$order = null;
if ( isset( $_POST['lo_track'] ) ) {
	$nonce = isset( $_POST['_lo_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_lo_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'lo_track' ) ) {
		$error = 'Please try again.';
	} else {
		$order = layerone_find_order( isset( $_POST['number'] ) ? wp_unslash( $_POST['number'] ) : '', isset( $_POST['phone'] ) ? wp_unslash( $_POST['phone'] ) : '' );
		if ( ! $order ) {
			$error = layerone_track_locked() ? 'Too many tries. Please wait 15 minutes and try again.' : 'We could not find an order with that number and mobile number.';
		}
	}
}
// phpcs:enable

if ( $order ) :
	?>
<main class="wrap pg">
  <nav class="crumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><a href="<?php echo esc_url( get_permalink() ); ?>">Track your order</a><span>/</span><b>Order details</b></nav>
  <div class="acc solo"><div class="acc-main"><?php layerone_render_order_view( $order, false ); ?></div></div>
</main>
<?php else : ?>
<main class="wrap pg simple">
  <h1 class="simple-h">Track your order.</h1>
  <section class="simple-card">
    <form method="post" action="<?php echo esc_url( get_permalink() ); ?>" novalidate>
      <label class="sr" for="tNo">Order number</label><div class="fld"><input id="tNo" name="number" placeholder="Order number (for example 1042)" autocomplete="off" required></div>
      <label class="sr" for="tPh">Mobile number</label><div class="fld"><input id="tPh" name="phone" type="tel" inputmode="numeric" autocomplete="tel-national" pattern="[6-9][0-9]{9}" title="Enter a 10-digit Indian mobile number" placeholder="Mobile number" required></div>
      <?php if ( $error ) : ?><div class="err" role="alert"><?php echo esc_html( $error ); ?></div><?php endif; ?>
      <input type="hidden" name="lo_track" value="1"><input type="hidden" name="_lo_nonce" value="<?php echo esc_attr( wp_create_nonce( 'lo_track' ) ); ?>">
      <button class="btn btn-dark" type="submit">Track order</button>
    </form>
  </section>
  <p class="simple-links"><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">Sign in to see all orders</a></p>
</main>
<?php endif; ?>
