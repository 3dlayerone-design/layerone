<?php
/**
 * One order in My account.
 *
 * @version WooCommerce 9.x template: myaccount/view-order.php (overrides the plugin's file)
 */

defined( 'ABSPATH' ) || exit;

$order = wc_get_order( $order_id );
if ( ! $order ) {
	echo layerone_acc_empty( 'orders', 'Order not found', 'Check the order number, or sign in to the account you ordered with.' ); // phpcs:ignore WordPress.Security.EscapeOutput
	return;
}
layerone_render_order_view( $order, true );
do_action( 'woocommerce_view_order', $order_id );
