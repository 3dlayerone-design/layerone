<?php
/**
 * Checkout page: the design's checkout. The form and the order summary are one WooCommerce checkout form.
 *
 * @version WooCommerce 9.x template: checkout/form-checkout.php (overrides the plugin's file)
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo '<main class="wrap pg"><div class="cp-empty"><h2>Please sign in to check out</h2><p>You must be signed in to check out.</p><a class="btn btn-dark" href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '">Sign in</a></div></main>';
	return;
}
?>
<main class="wrap pg">
  <nav class="crumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><a href="<?php echo esc_url( wc_get_cart_url() ); ?>">Cart</a><span>/</span><b>Checkout</b></nav>
  <div class="pg-head"><h1>Checkout</h1><p>Delivery across India. Printed to order in NCR.<?php if ( ! is_user_logged_in() ) : ?> <span id="signinHint">Have an account? <a class="lk" href="<?php echo esc_url( add_query_arg( 'redirect_to', rawurlencode( wc_get_checkout_url() ), wc_get_page_permalink( 'myaccount' ) ) ); ?>">Sign in</a> to fill this in faster.</span><?php endif; ?></p></div>

  <?php wc_print_notices(); ?>

  <form name="checkout" method="post" class="co checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" novalidate>
    <div class="co-form">
      <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

      <section class="cos">
        <h2><b>1</b>Contact</h2>
        <div class="row2">
          <?php
          layerone_checkout_field( $checkout, 'billing_email', array( 'placeholder' => 'you@example.com' ) );
          layerone_checkout_field( $checkout, 'billing_phone', array( 'placeholder' => '10-digit number', 'inputmode' => 'numeric', 'autocomplete' => 'tel-national', 'title' => 'Enter a 10-digit Indian mobile number' ) );
          ?>
        </div>
      </section>

      <section class="cos">
        <h2><b>2</b>Delivery address</h2>
        <div class="stack">
          <input type="hidden" name="billing_country" value="IN">
          <?php
          layerone_checkout_field( $checkout, 'billing_first_name', array( 'autocomplete' => 'name' ) );
          layerone_checkout_field( $checkout, 'billing_address_1', array( 'autocomplete' => 'address-line1' ) );
          layerone_checkout_field( $checkout, 'billing_address_2', array( 'autocomplete' => 'address-line2' ) );
          ?>
          <div class="row3">
            <?php
            layerone_checkout_field( $checkout, 'billing_city', array( 'autocomplete' => 'address-level2' ) );
            layerone_checkout_field( $checkout, 'billing_state', array( 'autocomplete' => 'address-level1' ) );
            layerone_checkout_field( $checkout, 'billing_postcode', array( 'inputmode' => 'numeric', 'autocomplete' => 'postal-code', 'pattern' => '[1-9][0-9]{5}', 'title' => 'Enter a 6-digit PIN code' ) );
            ?>
          </div>
          <?php layerone_checkout_field( $checkout, 'order_comments' ); ?>
        </div>
      </section>

      <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

      <section class="cos">
        <h2><b>3</b>Payment</h2>
        <?php woocommerce_checkout_payment(); ?>
      </section>
    </div>

    <aside class="sum" aria-label="Order summary">
      <h2>Order summary</h2>
      <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
      <div id="order_review" class="woocommerce-checkout-review-order">
        <?php woocommerce_order_review(); ?>
      </div>
      <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
    </aside>
    <?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
  </form>
</main>
<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
