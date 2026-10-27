<?php
/**
 * Orders list with All / In progress / Delivered tabs.
 *
 * @version WooCommerce 9.x template: myaccount/orders.php (overrides the plugin's file)
 */

defined( 'ABSPATH' ) || exit;

$orders = wc_get_orders( array( 'customer_id' => get_current_user_id(), 'limit' => -1, 'orderby' => 'date', 'order' => 'DESC' ) );
?>
<div class="acc-head"><h1>Orders</h1><p>Track and review everything you have ordered.</p></div>
<div class="ord-tabs" id="ordTabs"><button type="button" class="on" data-f="all">All</button><button type="button" data-f="open">In progress</button><button type="button" data-f="done">Delivered</button></div>
<div id="ordList">
  <?php
  if ( $orders ) {
  	foreach ( $orders as $o ) {
  		$open = layerone_order_open( $o );
  		echo '<div class="ord-wrap" data-state="' . ( $open ? 'open' : ( 'delivered' === layerone_order_stage( $o ) ? 'done' : 'other' ) ) . '">' . layerone_order_card( $o ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
  	}
  	echo '<div class="acc-empty" id="ordEmpty" hidden>' . layerone_account_icon( 'orders' ) . '<h3>No orders here</h3><p>Try another filter.</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
  } else {
  	echo layerone_acc_empty( 'orders', 'No orders yet', 'When you place an order it will show up here.', '<a class="btn btn-dark btn-sm" href="' . esc_url( layerone_shop_url() ) . '">Start shopping</a>' ); // phpcs:ignore WordPress.Security.EscapeOutput
  }
  ?>
</div>
