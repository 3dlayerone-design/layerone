<?php
/**
 * Empty cart.
 *
 * @version WooCommerce 9.x template: cart/cart-empty.php (overrides the plugin's file)
 */

defined( 'ABSPATH' ) || exit;

/* products to suggest */
$more = array();
foreach ( wc_get_products( array( 'status' => 'publish', 'limit' => 12, 'visibility' => 'catalog', 'orderby' => 'menu_order', 'order' => 'ASC', 'return' => 'ids' ) ) as $pid ) {
	if ( layerone_is_shop_product( $pid ) && 'bundle' !== layerone_flexi_kind( $pid ) ) {
		$more[] = $pid;
	}
}
$more = array_slice( $more, 0, 4 );
?>
<main class="wrap pg">
  <nav class="crumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><b>Cart</b></nav>
  <div class="pg-head"><h1>Your cart</h1></div>
  <?php wc_print_notices(); ?>
  <div class="cp-empty" id="cpEmpty">
    <svg class="i" viewBox="0 0 24 24"><path d="M6 8h12l-1 12H7z"/><path d="M9 8a3 3 0 0 1 6 0"/></svg>
    <h2>Your cart is empty</h2>
    <p>Add a few products and they will show up here.</p>
    <a class="btn btn-dark" href="<?php echo esc_url( layerone_shop_url() ); ?>">Browse all products</a>
  </div>
  <?php if ( $more ) : ?>
  <section class="pg-rel">
    <div class="sh"><div><h2>You may <span class="serif">also like</span></h2></div><a href="<?php echo esc_url( layerone_shop_url() ); ?>" class="more">All products →</a></div>
    <div class="grid" id="cpRel"><?php foreach ( $more as $i => $pid ) { echo layerone_product_card( $pid, $i ); } // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
  </section>
  <?php endif; ?>
</main>
