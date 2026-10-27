<?php
/**
 * My account dashboard.
 *
 * @version WooCommerce 9.x template: myaccount/dashboard.php (overrides the plugin's file)
 */

defined( 'ABSPATH' ) || exit;

$user    = wp_get_current_user();
$first   = $user->first_name ? $user->first_name : ( $user->display_name ? strtok( $user->display_name, ' ' ) : $user->user_login );
$orders  = wc_get_orders( array( 'customer_id' => $user->ID, 'limit' => -1, 'orderby' => 'date', 'order' => 'DESC' ) );
$open    = count( array_filter( $orders, 'layerone_order_open' ) );
$wish    = count( layerone_wishlist_ids( $user->ID ) );
$returns = count( layerone_user_returns( $user->ID ) );
$def     = null;
foreach ( layerone_addresses( $user->ID ) as $a ) {
	if ( ! empty( $a['default'] ) ) {
		$def = $a;
	}
}
$def = $def ? $def : ( layerone_addresses( $user->ID )[0] ?? null );
?>
<div class="acc-head"><h1>Hello, <?php echo esc_html( $first ); ?></h1><p>Here is what is happening with your account.</p></div>
<div class="stats">
  <a class="stat" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><b><?php echo (int) count( $orders ); ?></b><span>Orders</span></a>
  <a class="stat" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><b><?php echo (int) $open; ?></b><span>In progress</span></a>
  <a class="stat" href="<?php echo esc_url( wc_get_account_endpoint_url( 'wishlist' ) ); ?>"><b><?php echo (int) $wish; ?></b><span>Wishlist</span></a>
  <a class="stat" href="<?php echo esc_url( wc_get_account_endpoint_url( 'returns' ) ); ?>"><b><?php echo (int) $returns; ?></b><span>Return requests</span></a>
</div>
<section class="acc-card"><div class="acc-card-h"><h2>Recent orders</h2><a class="lk" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">View all</a></div>
  <?php
  if ( $orders ) {
  	foreach ( array_slice( $orders, 0, 3 ) as $o ) {
  		echo layerone_order_card( $o ); // phpcs:ignore WordPress.Security.EscapeOutput
  	}
  } else {
  	echo layerone_acc_empty( 'orders', 'No orders yet', 'When you place an order it will show up here.', '<a class="btn btn-dark btn-sm" href="' . esc_url( layerone_shop_url() ) . '">Start shopping</a>' ); // phpcs:ignore WordPress.Security.EscapeOutput
  }
  ?>
</section>
<div class="acc-two">
  <section class="acc-card"><div class="acc-card-h"><h2>Default address</h2><a class="lk" href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>"><?php echo $def ? 'Manage' : 'Add'; ?></a></div>
    <?php echo $def ? '<p class="addr">' . layerone_address_html( $def ) . '</p>' : '<p class="muted">You have not saved an address yet. Add one to check out faster.</p>'; // phpcs:ignore WordPress.Security.EscapeOutput ?></section>
  <section class="acc-card"><div class="acc-card-h"><h2>Need help?</h2></div>
    <p class="muted">Questions about an order, a custom piece or a replacement? We are happy to help.</p>
    <div class="acc-links"><a class="btn btn-line btn-sm" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact us</a><a class="btn btn-line btn-sm" href="<?php echo esc_url( home_url( '/faq/' ) ); ?>">Read FAQs</a></div></section>
</div>
