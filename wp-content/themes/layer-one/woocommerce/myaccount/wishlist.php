<?php
/**
 * Wishlist inside My account (server-drawn cards from the customer's saved list).
 */

defined( 'ABSPATH' ) || exit;

$ids   = layerone_wishlist_ids();
$cards = array();
foreach ( $ids as $i => $id ) {
	$p = wc_get_product( $id );
	if ( $p && 'publish' === $p->get_status() && layerone_is_shop_product( $id ) ) {
		$cards[] = layerone_product_card( $p, $i );
	}
}
?>
<div class="acc-head"><h1>Wishlist</h1><p>Pieces you have saved for later.</p></div>
<div class="wl-bar"><span class="muted" id="wlCount"><?php echo $cards ? esc_html( count( $cards ) . ' saved item' . ( 1 === count( $cards ) ? '' : 's' ) ) : ''; ?></span><button type="button" class="btn btn-dark btn-sm" id="wlAll"<?php echo $cards ? '' : ' hidden'; ?>>Add all to cart</button></div>
<div id="wlGrid" class="<?php echo $cards ? 'grid' : ''; ?>"><?php echo $cards ? implode( '', $cards ) : layerone_acc_empty( 'wishlist', 'Your wishlist is empty', 'Tap the heart on any product to save it here.', '<a class="btn btn-dark btn-sm" href="' . esc_url( layerone_shop_url() ) . '">Browse products</a>' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
