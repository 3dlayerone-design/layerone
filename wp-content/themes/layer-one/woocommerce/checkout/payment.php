<?php
/**
 * Payment options, agreement and the Place order button. WooCommerce replaces this block by its class name when totals change.
 *
 * @version WooCommerce 9.x template: checkout/payment.php (overrides the plugin's file)
 */

defined( 'ABSPATH' ) || exit;

if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_before_payment' );
}
?>
<div id="payment" class="woocommerce-checkout-payment">
  <?php if ( WC()->cart && WC()->cart->needs_payment() ) : ?>
  <div class="pay-opts">
    <?php
    if ( ! empty( $available_gateways ) ) {
    	$first = true;
    	foreach ( $available_gateways as $gateway ) {
    		$checked = $gateway->chosen || ( $first && ! WC()->session->get( 'chosen_payment_method' ) ) || ( WC()->session->get( 'chosen_payment_method' ) === $gateway->id );
    		$first   = false;
    		?>
    <label class="po"><input type="radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>" <?php checked( $checked ); ?>><span class="dot"></span><div><b><?php echo esc_html( $gateway->get_title() ); ?></b><small><?php echo esc_html( wp_strip_all_tags( $gateway->get_description() ) ); ?></small></div></label>
    		<?php
    	}
    } else {
    	echo '<p class="sum-note">' . esc_html( apply_filters( 'woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? 'Sorry, it seems that there are no available payment methods. Please contact us if you need assistance.' : 'Please fill in your details above to see the available payment methods.' ) ) . '</p>';
    }
    ?>
  </div>
  <p class="sum-note" style="margin-top:16px"><svg class="i" style="display:inline;width:14px;height:14px;vertical-align:-2px;margin-right:6px" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>Your details are used only to deliver this order.</p>
  <?php endif; ?>

  <div class="co-place">
    <?php if ( wc_terms_and_conditions_checkbox_enabled() ) : ?>
    <label class="ckl"><input type="checkbox" name="terms" id="terms" required><i></i><span><?php wc_terms_and_conditions_checkbox_text(); ?></span></label>
    <input type="hidden" name="terms-field" value="1">
    <?php endif; ?>
    <?php do_action( 'woocommerce_review_order_before_submit' ); ?>
    <?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="btn btn-dark" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <?php do_action( 'woocommerce_review_order_after_submit' ); ?>
  </div>
</div>
<?php
if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
