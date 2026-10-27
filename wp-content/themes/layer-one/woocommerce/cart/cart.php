<?php
/**
 * Cart page: the design's cart.
 *
 * @version WooCommerce 9.x template: cart/cart.php (overrides the plugin's file)
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );

$cart     = WC()->cart;
$count    = $cart->get_cart_contents_count();
$free     = (int) layerone_opt( 'lo_free_ship' );
$sub      = (float) $cart->get_subtotal();
$left     = $free - $sub;
$shipping = (float) $cart->get_shipping_total();
$needs    = $cart->needs_shipping();
$total    = (float) $cart->get_total( 'edit' );
$in_cart  = array();
foreach ( $cart->get_cart() as $item ) {
	$in_cart[] = (int) $item['product_id'];
}

/* "You may also like": shop products that are not in the cart yet */
$more = array();
foreach ( wc_get_products( array( 'status' => 'publish', 'limit' => 12, 'visibility' => 'catalog', 'orderby' => 'menu_order', 'order' => 'ASC', 'return' => 'ids' ) ) as $pid ) {
	if ( layerone_is_shop_product( $pid ) && ! in_array( $pid, $in_cart, true ) && 'bundle' !== layerone_flexi_kind( $pid ) ) {
		$more[] = $pid;
	}
}
$more = array_slice( $more, 0, 4 );
?>
<main class="wrap pg">
  <nav class="crumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><b>Cart</b></nav>
  <div class="pg-head"><h1>Your cart</h1><p id="cpCount"><?php echo esc_html( sprintf( '%d item%s in your cart', $count, 1 === $count ? '' : 's' ) ); ?></p></div>

  <?php wc_print_notices(); ?>

  <div class="cp" id="cp">
    <section class="cp-main" aria-label="Cart items">
      <div class="fs"><p id="fsText"><?php echo $left > 0 ? 'Add <b>' . esc_html( layerone_plain_price( $left ) ) . '</b> more to get <b>free shipping</b>' : 'You have unlocked <b>free shipping</b>'; // phpcs:ignore WordPress.Security.EscapeOutput ?></p><div class="fs-bar"><i id="fsBar" style="width:<?php echo esc_attr( min( 100, $free ? $sub / $free * 100 : 100 ) ); ?>%"></i></div></div>

      <form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
        <div class="cp-list" id="cpList">
          <?php
          echo layerone_flexi_tier_html(); // phpcs:ignore WordPress.Security.EscapeOutput
          foreach ( $cart->get_cart() as $key => $item ) :
          	$product = $item['data'];
          	if ( ! $product || ! $product->exists() || $item['quantity'] <= 0 ) {
          		continue;
          	}
          	$fx    = layerone_flexi_item_data( $item );
          	$name  = wp_specialchars_decode( $product->get_name(), ENT_QUOTES );
          	$href  = get_permalink( $product->get_id() );
          	$thumb = $product->get_image_id() ? wp_get_attachment_image( $product->get_image_id(), 'woocommerce_thumbnail', false, array( 'alt' => $name ) ) : '<div class="ph"></div>';
          	$sub_t = layerone_product_cat_name( wc_get_product( $item['product_id'] ) );
          	if ( $fx ) {
          		if ( 'box' === $fx['type'] ) {
          			$name  = layerone_flexi_line_title( $fx );
          			$href  = layerone_flexi_page_url();
          			$first = wc_get_product( (int) array_key_first( $fx['figures'] ) );
          			$thumb = $first && $first->get_image_id() ? wp_get_attachment_image( $first->get_image_id(), 'woocommerce_thumbnail', false, array( 'alt' => $name ) ) : $thumb;
          		}
          		$sub_t = layerone_flexi_short_names( $fx['figures'] );
          	}
          	$sub_t = apply_filters( 'layerone_cart_line_note', $sub_t, $item );
          	$qty   = (int) $item['quantity'];
          	$max   = $product->is_sold_individually() ? 1 : ( $product->managing_stock() && ! $product->backorders_allowed() ? max( 1, (int) $product->get_stock_quantity() ) : 99 );
          	?>
          <article class="cp-item">
            <a class="im" href="<?php echo esc_url( $href ); ?>"><?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
            <div><h3><a href="<?php echo esc_url( $href ); ?>"><?php echo esc_html( $name ); ?></a></h3>
              <p class="sm"><?php echo esc_html( $sub_t ); ?></p>
              <?php echo $fx ? layerone_flexi_details_html( $fx ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
              <div class="cp-qty">
                <button type="button" data-d="-1" aria-label="Decrease quantity">−</button>
                <span class="cp-q"><?php echo (int) $qty; ?></span>
                <input type="hidden" class="qty" name="cart[<?php echo esc_attr( $key ); ?>][qty]" value="<?php echo (int) $qty; ?>" data-max="<?php echo (int) $max; ?>">
                <button type="button" data-d="1" aria-label="Increase quantity">+</button>
              </div>
              <div class="each"><?php echo esc_html( layerone_plain_price( (float) $product->get_price() ) ); ?> each</div></div>
            <div class="cp-side"><div class="tot"><?php echo esc_html( layerone_plain_price( (float) $product->get_price() * $qty ) ); ?></div><a class="cp-rm" href="<?php echo esc_url( wc_get_cart_remove_url( $key ) ); ?>">Remove</a></div>
          </article>
          <?php endforeach; ?>
        </div>
        <button type="submit" class="screen-reader-text" name="update_cart" value="Update cart" id="cpUpdate" hidden>Update cart</button>
        <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
      </form>

      <div class="cp-foot"><a class="btn btn-line btn-sm" href="<?php echo esc_url( layerone_shop_url() ); ?>">← Continue shopping</a><a class="lk" id="cpClear" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'lo_clear_cart', '1', wc_get_cart_url() ), 'lo_clear_cart' ) ); ?>">Clear cart</a></div>
    </section>

    <aside class="sum" aria-label="Order summary">
      <h2>Order summary</h2>
      <div class="sum-row"><span>Subtotal</span><b id="sSub"><?php echo esc_html( layerone_plain_price( $sub ) ); ?></b></div>
      <div class="sum-row<?php echo ( $needs && $shipping <= 0 ) ? ' free' : ''; ?>" id="sShipRow"><span>Shipping</span><b id="sShip"><?php echo $needs ? ( $shipping > 0 ? esc_html( layerone_plain_price( $shipping ) ) : 'Free' ) : '—'; ?></b></div>
      <?php foreach ( $cart->get_fees() as $fee ) : ?>
      <div class="sum-row"><span><?php echo esc_html( $fee->name ); ?></span><b><?php echo esc_html( layerone_plain_price( (float) $fee->total ) ); ?></b></div>
      <?php endforeach; ?>
      <?php foreach ( $cart->get_coupons() as $code => $coupon ) : ?>
      <div class="sum-row free"><span>Coupon <?php echo esc_html( $code ); ?></span><b>−<?php echo esc_html( layerone_plain_price( (float) $cart->get_coupon_discount_amount( $code ) ) ); ?></b></div>
      <?php endforeach; ?>
      <div class="sum-total"><span>Total</span><span id="sTot"><?php echo esc_html( layerone_plain_price( $total ) ); ?></span></div>
      <a class="btn btn-dark" href="<?php echo esc_url( wc_get_checkout_url() ); ?>">Proceed to checkout</a>
      <p class="sum-note">Free shipping over ₹<?php echo esc_html( number_format_i18n( $free ) ); ?>. Items are printed to order and dispatched in 2–5 days.</p>
      <div class="sum-pay" aria-label="Accepted payments"><?php foreach ( WC()->payment_gateways()->get_available_payment_gateways() as $gw ) : ?><i><?php echo esc_html( 'cod' === $gw->id ? 'COD' : $gw->get_title() ); ?></i><?php endforeach; ?></div>
    </aside>
  </div>

  <?php if ( $more ) : ?>
  <section class="pg-rel">
    <div class="sh"><div><h2>You may <span class="serif">also like</span></h2></div><a href="<?php echo esc_url( layerone_shop_url() ); ?>" class="more">All products →</a></div>
    <div class="grid" id="cpRel"><?php foreach ( $more as $i => $pid ) { echo layerone_product_card( $pid, $i ); } // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
  </section>
  <?php endif; ?>
</main>
<?php do_action( 'woocommerce_after_cart' ); ?>
