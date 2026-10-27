</main>

<?php get_template_part( 'template-parts/footer-site' ); ?>

<?php
$custom = layerone_opt( 'lo_custom_url' ) ?: home_url( '/custom/' );
$on     = array(
	'home'   => is_front_page(),
	'shop'   => function_exists( 'is_woocommerce' ) && is_woocommerce(),
	'custom' => is_page( 'custom' ),
);
$cnt    = layerone_cart_count();
?>
<div class="drawer" id="drawer">
  <div class="drawer-top"><span class="logo"><span><?php echo esc_html( layerone_opt( 'lo_logo_a' ) ); ?><em><?php echo esc_html( layerone_opt( 'lo_logo_b' ) ); ?></em></span></span><button class="x" id="closeDr" aria-label="Close"><svg class="i" viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg></button></div>
  <?php echo layerone_nav_links(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>

<nav class="tabbar" id="tabbar" aria-label="App navigation">
  <a href="<?php echo esc_url( home_url( '/' ) ); ?>"<?php echo $on['home'] ? ' class="on"' : ''; ?> data-t="home"><span class="ic"><svg class="i" viewBox="0 0 24 24"><path d="M4 11 12 4l8 7v9a1 1 0 0 1-1 1h-4v-6H9v6H5a1 1 0 0 1-1-1z"/></svg></span>Home</a>
  <a href="<?php echo esc_url( layerone_shop_url() ); ?>"<?php echo $on['shop'] ? ' class="on"' : ''; ?> data-t="shop"><span class="ic"><svg class="i" viewBox="0 0 24 24"><rect x="4" y="4" width="7" height="7" rx="2"/><rect x="13" y="4" width="7" height="7" rx="2"/><rect x="4" y="13" width="7" height="7" rx="2"/><rect x="13" y="13" width="7" height="7" rx="2"/></svg></span>Shop</a>
  <button type="button" id="tabCats"><span class="ic"><svg class="i" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h10"/></svg></span>Categories</button>
  <a href="<?php echo esc_url( $custom ); ?>"<?php echo $on['custom'] ? ' class="on"' : ''; ?> data-t="custom"><span class="ic"><svg class="i" viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg></span>Custom</a>
  <button type="button" id="tabCart"><span class="ic"><svg class="i" viewBox="0 0 24 24"><path d="M6 8h12l-1 12H7z"/><path d="M9 8a3 3 0 0 1 6 0"/></svg></span>Cart<span class="n" id="cn2"<?php echo $cnt ? '' : ' hidden'; ?>><?php echo (int) $cnt; ?></span></button>
</nav>

<div class="ov" id="ov"></div>
<aside class="cart" id="cart" aria-label="Cart">
  <div class="ch"><h3>Your cart</h3><button class="x" id="closeCart" aria-label="Close"><svg class="i" viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg></button></div>
  <div class="cl" id="cl"><?php echo layerone_cart_items_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
  <div class="cf">
    <div class="sub-t"><span>Subtotal</span><span id="st"><?php echo function_exists( 'WC' ) && WC()->cart ? wp_kses_post( wc_price( WC()->cart->get_subtotal() ) ) : ''; ?></span></div>
    <p id="shipNote"><?php echo esc_html( layerone_ship_note() ); ?></p>
    <a href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '#' ); ?>" class="cf-view">View cart</a>
    <a href="<?php echo esc_url( function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : '#' ); ?>" class="btn btn-dark" id="checkout">Checkout</a>
  </div>
</aside>
<div class="toast" id="toast"></div>

<?php wp_footer(); ?>
</body>
</html>
