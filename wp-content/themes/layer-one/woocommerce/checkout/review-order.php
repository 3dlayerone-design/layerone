<?php
/**
 * Order summary on the checkout page. WooCommerce replaces this block by its class name whenever the totals change.
 *
 * @version WooCommerce 9.x template: checkout/review-order.php (overrides the plugin's file)
 */

defined( 'ABSPATH' ) || exit;

$cart = WC()->cart;
?>
<div class="woocommerce-checkout-review-order-table">
  <div class="sum-items" id="siList">
    <?php
    do_action( 'woocommerce_review_order_before_cart_contents' );
    foreach ( $cart->get_cart() as $key => $item ) :
    	$product = $item['data'];
    	if ( ! $product || ! $product->exists() || $item['quantity'] <= 0 ) {
    		continue;
    	}
    	$fx    = layerone_flexi_item_data( $item );
    	$name  = wp_specialchars_decode( $product->get_name(), ENT_QUOTES );
    	$thumb = $product->get_image_id() ? wp_get_attachment_image( $product->get_image_id(), 'thumbnail', false, array( 'alt' => '' ) ) : '<div class="ph"></div>';
    	$small = layerone_product_cat_name( wc_get_product( $item['product_id'] ) );
    	if ( $fx ) {
    		if ( 'box' === $fx['type'] ) {
    			$name  = layerone_flexi_line_title( $fx );
    			$first = wc_get_product( (int) array_key_first( $fx['figures'] ) );
    			$thumb = $first && $first->get_image_id() ? wp_get_attachment_image( $first->get_image_id(), 'thumbnail', false, array( 'alt' => '' ) ) : $thumb;
    		}
    		$small = sprintf( '%d figures', layerone_flexi_figure_count( $fx ) );
    	}
    	$small = apply_filters( 'layerone_cart_line_note', $small, $item );
    	?>
    <div class="si"><div class="t"><?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php echo (int) $item['quantity']; ?></b></div>
      <div><?php echo esc_html( $name ); ?><small><?php echo esc_html( $small ); ?></small></div><div><?php echo esc_html( layerone_plain_price( (float) $product->get_price() * (int) $item['quantity'] ) ); ?></div></div>
    <?php endforeach; do_action( 'woocommerce_review_order_after_cart_contents' ); ?>
  </div>

  <div class="sum-row"><span>Subtotal</span><b id="sSub"><?php echo esc_html( layerone_plain_price( (float) $cart->get_subtotal() ) ); ?></b></div>

  <?php
  /* shipping: the chosen rate travels with the form as a hidden field */
  if ( $cart->needs_shipping() && $cart->show_shipping() ) :
  	$packages = WC()->shipping()->get_packages();
  	$chosen   = WC()->session->get( 'chosen_shipping_methods' );
  	$ship     = (float) $cart->get_shipping_total();
  	foreach ( $packages as $i => $package ) {
  		$method = isset( $chosen[ $i ] ) ? $chosen[ $i ] : '';
  		echo '<input type="hidden" name="shipping_method[' . (int) $i . ']" data-index="' . (int) $i . '" id="shipping_method_' . (int) $i . '" value="' . esc_attr( $method ) . '" class="shipping_method">';
  	}
  	?>
  <div class="sum-row<?php echo $ship <= 0 ? ' free' : ''; ?>" id="sShipRow"><span>Shipping</span><b id="sShip"><?php echo $ship > 0 ? esc_html( layerone_plain_price( $ship ) ) : 'Free'; ?></b></div>
  <?php endif; ?>

  <?php foreach ( $cart->get_fees() as $fee ) : ?>
  <div class="sum-row"><span><?php echo esc_html( $fee->name ); ?></span><b><?php echo esc_html( layerone_plain_price( (float) $fee->total ) ); ?></b></div>
  <?php endforeach; ?>
  <?php foreach ( $cart->get_coupons() as $code => $coupon ) : ?>
  <div class="sum-row free"><span>Coupon <?php echo esc_html( $code ); ?></span><b>−<?php echo esc_html( layerone_plain_price( (float) $cart->get_coupon_discount_amount( $code ) ) ); ?></b></div>
  <?php endforeach; ?>

  <div class="sum-total"><span>Total</span><span id="sTot"><?php echo esc_html( layerone_plain_price( (float) $cart->get_total( 'edit' ) ) ); ?></span></div>
  <p class="sum-note">Printed to order and dispatched in 2–5 days. <a class="lk" href="<?php echo esc_url( wc_get_cart_url() ); ?>">Edit cart</a></p>
</div>
