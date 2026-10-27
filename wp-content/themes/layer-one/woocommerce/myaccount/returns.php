<?php
/**
 * Returns & replacements: report a problem and see the requests so far.
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignore WordPress.Security.NonceVerification
$pre    = isset( $_GET['order'] ) ? absint( $_GET['order'] ) : 0;
$orders = wc_get_orders( array( 'customer_id' => get_current_user_id(), 'limit' => -1, 'orderby' => 'date', 'order' => 'DESC' ) );
$data   = array();
foreach ( $orders as $o ) {
	$names = array();
	foreach ( $o->get_items() as $it ) {
		$names[] = wp_specialchars_decode( $it->get_name(), ENT_QUOTES );
	}
	$data[ $o->get_id() ] = $names;
}
$list = layerone_user_returns();
?>
<div class="acc-head"><h1>Returns &amp; replacements</h1><p>Damaged in transit or not as expected? Tell us within 7 days of delivery and we will reprint it. See the <a class="lk" href="<?php echo esc_url( home_url( '/returns/' ) ); ?>">returns policy</a>.</p></div>

<section class="acc-card">
  <h2>Report a problem</h2>
  <?php if ( ! $orders ) : ?>
  <p class="muted" id="rtNone">You need an order first. <a class="lk" href="<?php echo esc_url( layerone_shop_url() ); ?>">Browse products</a></p>
  <?php else : ?>
  <form id="rtForm" method="post" action="<?php echo esc_url( wc_get_account_endpoint_url( 'returns' ) ); ?>" class="stack" style="display:grid;gap:16px" data-items="<?php echo esc_attr( wp_json_encode( $data ) ); ?>" novalidate>
    <input type="hidden" name="lo_action" value="return"><input type="hidden" name="_lo_nonce" value="<?php echo esc_attr( wp_create_nonce( 'lo_account_return' ) ); ?>">
    <div class="row2">
      <div class="fld"><label for="rtOrder">Order</label><select id="rtOrder" name="order" required><?php foreach ( $orders as $o ) : ?><option value="<?php echo (int) $o->get_id(); ?>" <?php selected( $pre, $o->get_id() ); ?>>#<?php echo esc_html( $o->get_order_number() . ' · ' . layerone_date( $o->get_date_created()->getTimestamp() ) ); ?></option><?php endforeach; ?></select></div>
      <div class="fld"><label for="rtItem">Item</label><select id="rtItem" name="item" required></select></div>
    </div>
    <div class="fld"><label for="rtReason">What went wrong?</label>
      <select id="rtReason" name="reason" required><option>Damaged in transit</option><option>Print defect</option><option>Wrong item or colour</option><option>Missing part</option><option>Something else</option></select></div>
    <div class="fld"><label for="rtDetails">Details</label><textarea id="rtDetails" name="details" rows="4" required minlength="10" placeholder="Tell us what happened. We will ask for photos on WhatsApp or email."></textarea></div>
    <button class="btn btn-dark" type="submit">Submit request</button>
  </form>
  <?php endif; ?>
</section>

<section class="acc-card"><h2>Your requests</h2>
  <div id="rtList">
    <?php if ( $list ) : foreach ( $list as $r ) : ?>
    <div class="rt-row"><div><b><?php echo esc_html( $r->post_title ); ?></b><small><?php echo esc_html( layerone_date( strtotime( $r->post_date ) ) ); ?> · Order #<?php echo esc_html( get_post_meta( $r->ID, '_lo_order', true ) ); ?></small><p><?php echo esc_html( get_post_meta( $r->ID, '_lo_item', true ) . ' · ' . get_post_meta( $r->ID, '_lo_reason', true ) ); ?></p></div><span class="stt placed"><?php echo esc_html( get_post_meta( $r->ID, '_lo_status', true ) ); ?></span></div>
    <?php endforeach; else : ?><p class="muted">No requests yet.</p><?php endif; ?>
  </div>
</section>
