<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wishlist page for visitors who are not signed in (the list is kept in the browser; signed-in customers see it in My account).
 */
?>
<main class="wrap pg">
  <nav class="crumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><b>Wishlist</b></nav>
  <div class="acc solo" style="display:block;max-width:none">
    <div class="acc-main" id="accBody" data-guest-wishlist="1">
      <div class="acc-head"><h1>Wishlist</h1><p>Pieces you have saved for later. <a class="lk" href="<?php echo esc_url( add_query_arg( 'redirect_to', rawurlencode( layerone_wishlist_page_url() ), wc_get_page_permalink( 'myaccount' ) ) ); ?>">Sign in</a> to keep your wishlist on every device.</p></div>
      <div class="wl-bar"><span class="muted" id="wlCount"></span><button type="button" class="btn btn-dark btn-sm" id="wlAll" hidden>Add all to cart</button></div>
      <div id="wlGrid"></div>
    </div>
  </div>
</main>
