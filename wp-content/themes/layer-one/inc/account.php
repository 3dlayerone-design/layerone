<?php
/**
 * My Account: the design's account area on top of WooCommerce.
 *
 * - Order stages (Order placed > Printing > Dispatched > Delivered) as order statuses
 * - Extra account pages: Wishlist and Returns; Addresses with several saved addresses
 * - Registration with name, mobile and password confirmation
 * - Return requests (a private post type) with a status the shop team can change
 *
 * Forms are posted to the account pages themselves and handled on wp_loaded ("lo_action").
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ====================================================================================================
 * Store settings the account pages rely on
 * ================================================================================================== */

add_action( 'init', function () {
	if ( get_option( 'layerone_account_setup' ) ) {
		return;
	}
	update_option( 'woocommerce_enable_myaccount_registration', 'yes' );
	update_option( 'woocommerce_registration_generate_password', 'no' );
	update_option( 'woocommerce_registration_generate_username', 'yes' );
	update_option( 'woocommerce_enable_checkout_login_reminder', 'yes' );
	update_option( 'woocommerce_enable_signup_and_login_from_checkout', 'no' );
	update_option( 'layerone_account_setup', 1 );
} );

/* ====================================================================================================
 * Order stages
 * ================================================================================================== */

add_action( 'init', function () {
	register_post_status( 'wc-printing', array(
		'label'                     => 'Printing',
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		/* translators: %s: number of orders */
		'label_count'               => _n_noop( 'Printing <span class="count">(%s)</span>', 'Printing <span class="count">(%s)</span>' ),
	) );
	register_post_status( 'wc-dispatched', array(
		'label'                     => 'Dispatched',
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		/* translators: %s: number of orders */
		'label_count'               => _n_noop( 'Dispatched <span class="count">(%s)</span>', 'Dispatched <span class="count">(%s)</span>' ),
	) );
} );

add_filter( 'wc_order_statuses', function ( $statuses ) {
	$out = array();
	foreach ( $statuses as $key => $label ) {
		$out[ $key ] = $label;
		if ( 'wc-processing' === $key ) {
			$out['wc-printing']   = 'Printing';
			$out['wc-dispatched'] = 'Dispatched';
		}
	}
	return $out;
} );

/* Orders in these statuses are still "in progress" for the customer. */
add_filter( 'woocommerce_valid_order_statuses_for_payment_complete', function ( $s ) {
	return array_merge( $s, array( 'printing' ) );
} );

function layerone_stages() {
	return array(
		'placed'     => array( 'Order placed', 'We have your order and are getting it ready to print.' ),
		'printing'   => array( 'Printing', 'Your item is being printed and finished by hand.' ),
		'dispatched' => array( 'Dispatched', 'Packed and handed to the courier.' ),
		'delivered'  => array( 'Delivered', 'Delivered to your address.' ),
	);
}

/* placed | printing | dispatched | delivered, or cancelled | failed | refunded */
function layerone_order_stage( $order ) {
	$s = $order->get_status();
	if ( 'printing' === $s || 'dispatched' === $s ) {
		return $s;
	}
	if ( 'completed' === $s ) {
		return 'delivered';
	}
	if ( in_array( $s, array( 'cancelled', 'failed', 'refunded' ), true ) ) {
		return $s;
	}
	return 'placed';
}

function layerone_stage_chip( $order ) {
	$stage  = layerone_order_stage( $order );
	$stages = layerone_stages();
	$label  = isset( $stages[ $stage ] ) ? $stages[ $stage ][0] : ucfirst( $stage );
	return '<span class="stt ' . esc_attr( $stage ) . '">' . esc_html( $label ) . '</span>';
}

function layerone_order_open( $order ) {
	return ! in_array( layerone_order_stage( $order ), array( 'delivered', 'cancelled', 'failed', 'refunded' ), true );
}

function layerone_date( $timestamp ) {
	return wp_date( 'j M Y', $timestamp );
}

/* ====================================================================================================
 * Account pages and menu
 * ================================================================================================== */

add_filter( 'woocommerce_get_query_vars', function ( $vars ) {
	$vars['wishlist'] = 'wishlist';
	$vars['returns']  = 'returns';
	return $vars;
} );

add_filter( 'woocommerce_account_menu_items', function () {
	return array(
		'dashboard'    => 'Dashboard',
		'orders'       => 'Orders',
		'wishlist'     => 'Wishlist',
		'edit-address' => 'Addresses',
		'returns'      => 'Returns',
		'edit-account' => 'Account details',
	);
} );

add_filter( 'woocommerce_endpoint_wishlist_title', function () {
	return 'Wishlist';
} );
add_filter( 'woocommerce_endpoint_returns_title', function () {
	return 'Returns & replacements';
} );

add_action( 'woocommerce_account_wishlist_endpoint', function () {
	wc_get_template( 'myaccount/wishlist.php' );
} );
add_action( 'woocommerce_account_returns_endpoint', function () {
	wc_get_template( 'myaccount/returns.php' );
} );

/* Icons in the sidebar, as in the design. */
function layerone_account_icon( $endpoint ) {
	$paths = array(
		'dashboard'    => '<path d="M4 11 12 4l8 7v9a1 1 0 0 1-1 1h-4v-6H9v6H5a1 1 0 0 1-1-1z"/>',
		'orders'       => '<path d="M12 3 4 7v10l8 4 8-4V7z"/><path d="m4 7 8 4 8-4M12 11v10"/>',
		'wishlist'     => '<path d="M12 20s-7.5-4.6-7.5-10.3A4.2 4.2 0 0 1 12 7.4a4.2 4.2 0 0 1 7.5 2.3C19.5 15.4 12 20 12 20z"/>',
		'edit-address' => '<path d="M12 21s7-6.2 7-11.5A7 7 0 0 0 5 9.5C5 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'returns'      => '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/>',
		'edit-account' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>',
		'box'          => '<path d="M12 3 4 7v10l8 4 8-4V7z"/><path d="m4 7 8 4 8-4M12 11v10"/>',
	);
	$p = isset( $paths[ $endpoint ] ) ? $paths[ $endpoint ] : '';
	return '<svg class="i" viewBox="0 0 24 24">' . $p . '</svg>';
}

function layerone_acc_empty( $icon, $title, $text, $link = '' ) {
	return '<div class="acc-empty">' . layerone_account_icon( $icon ) . '<h3>' . esc_html( $title ) . '</h3><p>' . esc_html( $text ) . '</p>' . $link . '</div>';
}

/* ====================================================================================================
 * Order cards (lists) and helpers
 * ================================================================================================== */

function layerone_order_thumb( $item ) {
	$product = $item->get_product();
	$fx      = $item->get_meta( '_lo_figures' );
	if ( $fx ) {
		$map = json_decode( $fx, true );
		if ( is_array( $map ) && $map ) {
			$first = wc_get_product( (int) array_key_first( $map ) );
			if ( $first && $first->get_image_id() ) {
				return wp_get_attachment_image( $first->get_image_id(), 'thumbnail', false, array( 'alt' => '' ) );
			}
		}
	}
	if ( $product && $product->get_image_id() ) {
		return wp_get_attachment_image( $product->get_image_id(), 'thumbnail', false, array( 'alt' => '' ) );
	}
	return '<div class="ph"></div>';
}

function layerone_order_card( $order ) {
	$items = array_values( $order->get_items() );
	$out   = '<a class="oc" href="' . esc_url( $order->get_view_order_url() ) . '"><div class="oc-t"><div><b>#' . esc_html( $order->get_order_number() ) . '</b><small>' . esc_html( layerone_date( $order->get_date_created()->getTimestamp() ) ) . '</small></div>' . layerone_stage_chip( $order ) . '</div><div class="oc-i">';
	foreach ( array_slice( $items, 0, 4 ) as $it ) {
		$out .= '<span class="oc-th">' . layerone_order_thumb( $it ) . '</span>';
	}
	if ( count( $items ) > 4 ) {
		$out .= '<span class="oc-more">+' . ( count( $items ) - 4 ) . '</span>';
	}
	$n    = $order->get_item_count();
	$out .= '<div class="oc-n">' . (int) $n . ' item' . ( 1 === $n ? '' : 's' ) . '</div><b class="oc-p">' . esc_html( layerone_plain_price( (float) $order->get_total() ) ) . '</b></div></a>';
	return $out;
}

/* One order, read-only: timeline, items, address and payment. Used by View order and by Track order. */
function layerone_render_order_view( $order, $signed = true ) {
	$stage   = layerone_order_stage( $order );
	$stages  = layerone_stages();
	$keys    = array_keys( $stages );
	$idx     = array_search( $stage, $keys, true );
	$created = $order->get_date_created();
	$eta     = $created ? $created->getTimestamp() + 7 * DAY_IN_SECONDS : 0;
	?>
	<div class="acc-head row"><div><h1>Order #<?php echo esc_html( $order->get_order_number() ); ?></h1><p>Placed on <?php echo esc_html( $created ? layerone_date( $created->getTimestamp() ) : '' ); ?></p></div><?php echo layerone_stage_chip( $order ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>

	<section class="acc-card">
	<?php if ( false !== $idx ) : ?>
		<div class="tl">
			<?php foreach ( $stages as $k => $s ) : $i = array_search( $k, $keys, true ); ?>
			<div class="tl-s <?php echo $i < $idx ? 'done' : ( $i === $idx ? 'cur' : '' ); ?>"><i><?php echo $i < $idx ? '✓' : (int) $i + 1; ?></i><b><?php echo esc_html( $s[0] ); ?></b><small><?php echo $i <= $idx ? esc_html( $s[1] ) : ''; ?></small></div>
			<?php endforeach; ?>
		</div>
		<?php if ( 'delivered' !== $stage && $eta ) : ?><p class="muted tl-eta">Estimated delivery by <b><?php echo esc_html( layerone_date( $eta ) ); ?></b>, depending on your location.</p><?php endif; ?>
	<?php else : ?>
		<p class="muted">This order is <b><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></b>.</p>
	<?php endif; ?>
	</section>

	<div class="acc-two">
		<section class="acc-card"><h2>Items</h2>
			<?php foreach ( $order->get_items() as $item ) : $qty = (int) $item->get_quantity(); ?>
			<div class="ri"><span class="oc-th big"><?php echo layerone_order_thumb( $item ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><div><b><?php echo esc_html( wp_specialchars_decode( $item->get_name(), ENT_QUOTES ) ); ?></b><small>Qty <?php echo (int) $qty; ?> · <?php echo esc_html( layerone_plain_price( (float) $order->get_item_total( $item, false, true ) ) ); ?> each</small>
				<?php foreach ( $item->get_formatted_meta_data() as $meta ) : ?><small class="ri-d"><?php echo esc_html( wp_strip_all_tags( $meta->display_key ) . ': ' . wp_strip_all_tags( $meta->display_value ) ); ?></small><?php endforeach; ?></div><b><?php echo esc_html( layerone_plain_price( (float) $order->get_line_total( $item, true ) ) ); ?></b></div>
			<?php endforeach; ?>
			<div class="sum-row"><span>Subtotal</span><b><?php echo esc_html( layerone_plain_price( (float) $order->get_subtotal() ) ); ?></b></div>
			<div class="sum-row<?php echo (float) $order->get_shipping_total() > 0 ? '' : ' free'; ?>"><span>Shipping</span><b><?php echo (float) $order->get_shipping_total() > 0 ? esc_html( layerone_plain_price( (float) $order->get_shipping_total() ) ) : 'Free'; ?></b></div>
			<div class="sum-total"><span>Total</span><span><?php echo esc_html( layerone_plain_price( (float) $order->get_total() ) ); ?></span></div>
		</section>
		<div class="acc-col">
			<section class="acc-card"><h2>Delivery address</h2><p class="addr"><b><?php echo esc_html( trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) ); ?></b><br><?php echo esc_html( $order->get_billing_address_1() ); ?><?php echo $order->get_billing_address_2() ? ', ' . esc_html( $order->get_billing_address_2() ) : ''; ?><br><?php echo esc_html( $order->get_billing_city() ); ?>, <?php echo esc_html( WC()->countries->get_states( 'IN' )[ $order->get_billing_state() ] ?? $order->get_billing_state() ); ?> <?php echo esc_html( $order->get_billing_postcode() ); ?><br><?php echo esc_html( $order->get_billing_phone() ); ?></p></section>
			<section class="acc-card"><h2>Payment</h2><p class="muted"><?php echo esc_html( $order->get_payment_method_title() ); ?></p><?php if ( $order->get_customer_note() ) : ?><h2 style="margin-top:16px">Your note</h2><p class="muted"><?php echo esc_html( $order->get_customer_note() ); ?></p><?php endif; ?></section>
			<?php if ( $signed ) : ?>
			<div class="acc-links"><button type="button" class="btn btn-line btn-sm" data-reorder="<?php echo (int) $order->get_id(); ?>">Order again</button><button type="button" class="btn btn-line btn-sm" onclick="window.print()">Print invoice</button><a class="btn btn-line btn-sm" href="<?php echo esc_url( add_query_arg( 'order', $order->get_id(), wc_get_endpoint_url( 'returns', '', wc_get_page_permalink( 'myaccount' ) ) ) ); ?>">Report a problem</a></div>
			<?php else : ?>
			<div class="acc-links"><button type="button" class="btn btn-line btn-sm" onclick="window.print()">Print invoice</button></div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/* ====================================================================================================
 * Order again
 * ================================================================================================== */

function layerone_ajax_reorder() {
	check_ajax_referer( 'layerone_cart', 'nonce' );
	$oid   = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
	$order = $oid ? wc_get_order( $oid ) : null;
	if ( ! $order || ! is_user_logged_in() || (int) $order->get_customer_id() !== get_current_user_id() ) {
		wp_send_json_error( array( 'message' => 'We could not find that order.' ) );
	}
	if ( null === WC()->cart ) {
		wc_load_cart();
	}
	$added = 0;
	foreach ( $order->get_items() as $item ) {
		$pid  = (int) $item->get_product_id();
		$qty  = max( 1, (int) $item->get_quantity() );
		$data = array();
		$kind = layerone_flexi_kind( $pid );

		if ( 'box' === $kind ) {
			$map = json_decode( (string) $item->get_meta( '_lo_figures' ), true );
			if ( ! is_array( $map ) || ! preg_match( '/box of (\d+)(?: \(\+(\d+) bonus\))?/', $item->get_name(), $m ) ) {
				continue;
			}
			$data['lo_flexi'] = array( 'type' => 'box', 'size' => (int) $m[1], 'bonus' => isset( $m[2] ) ? (int) $m[2] : 0, 'figures' => array_map( 'intval', $map ) );
		} elseif ( $item->get_meta( 'Colour' ) ) {
			$data['lo_colour'] = strtolower( (string) $item->get_meta( 'Colour' ) );
		}
		if ( WC()->cart->add_to_cart( $pid, $qty, 0, array(), $data ) ) {
			$added++;
		}
	}
	wc_clear_notices();
	if ( ! $added ) {
		wp_send_json_error( array( 'message' => 'These items are not available right now.' ) );
	}
	wp_send_json_success( array_merge( layerone_cart_state(), array( 'message' => 'Items added to your cart' ) ) );
}
add_action( 'wp_ajax_layerone_reorder', 'layerone_ajax_reorder' );

/* ====================================================================================================
 * Saved addresses (several per customer; the default one also fills the checkout)
 * ================================================================================================== */

function layerone_addresses( $uid = 0 ) {
	$uid  = $uid ? $uid : get_current_user_id();
	$list = get_user_meta( $uid, 'layerone_addresses', true );
	return is_array( $list ) ? $list : array();
}

function layerone_state_name( $code ) {
	$states = WC()->countries->get_states( 'IN' );
	return isset( $states[ $code ] ) ? $states[ $code ] : $code;
}

function layerone_address_html( $a ) {
	return '<b>' . esc_html( $a['name'] ) . '</b><br>' . esc_html( $a['a1'] ) . ( $a['a2'] ? ', ' . esc_html( $a['a2'] ) : '' ) . '<br>' . esc_html( $a['city'] ) . ', ' . esc_html( layerone_state_name( $a['state'] ) ) . ' ' . esc_html( $a['pin'] ) . '<br>' . esc_html( $a['phone'] );
}

/* The default address becomes the customer's billing address, which the checkout pre-fills. */
function layerone_sync_default_address( $uid ) {
	$def = null;
	foreach ( layerone_addresses( $uid ) as $a ) {
		if ( ! empty( $a['default'] ) ) {
			$def = $a;
		}
	}
	if ( ! $def ) {
		return;
	}
	$name = trim( $def['name'] );
	$pos  = strrpos( $name, ' ' );
	update_user_meta( $uid, 'billing_first_name', false === $pos ? $name : trim( substr( $name, 0, $pos ) ) );
	update_user_meta( $uid, 'billing_last_name', false === $pos ? '' : trim( substr( $name, $pos + 1 ) ) );
	update_user_meta( $uid, 'billing_phone', $def['phone'] );
	update_user_meta( $uid, 'billing_address_1', $def['a1'] );
	update_user_meta( $uid, 'billing_address_2', $def['a2'] );
	update_user_meta( $uid, 'billing_city', $def['city'] );
	update_user_meta( $uid, 'billing_state', $def['state'] );
	update_user_meta( $uid, 'billing_postcode', $def['pin'] );
	update_user_meta( $uid, 'billing_country', 'IN' );

	/* the checkout reads the customer held in the current session, so refresh that copy too */
	if ( function_exists( 'WC' ) && WC()->customer && (int) WC()->customer->get_id() === (int) $uid ) {
		$c = WC()->customer;
		$c->set_billing_first_name( false === $pos ? $name : trim( substr( $name, 0, $pos ) ) );
		$c->set_billing_last_name( false === $pos ? '' : trim( substr( $name, $pos + 1 ) ) );
		$c->set_billing_phone( $def['phone'] );
		$c->set_billing_address_1( $def['a1'] );
		$c->set_billing_address_2( $def['a2'] );
		$c->set_billing_city( $def['city'] );
		$c->set_billing_state( $def['state'] );
		$c->set_billing_postcode( $def['pin'] );
		$c->set_billing_country( 'IN' );
		$c->save();
	}
}

function layerone_save_address_list( $uid, $list ) {
	/* exactly one default when there is any address */
	$has_def = false;
	foreach ( $list as $k => $a ) {
		if ( ! empty( $a['default'] ) && ! $has_def ) {
			$has_def = true;
		} else {
			$list[ $k ]['default'] = false;
		}
	}
	if ( $list && ! $has_def ) {
		$list[ array_key_first( $list ) ]['default'] = true;
	}
	update_user_meta( $uid, 'layerone_addresses', array_values( $list ) );
	layerone_sync_default_address( $uid );
}

/* An address typed at checkout joins the saved list (once). */
add_action( 'woocommerce_checkout_update_user_meta', function ( $customer_id, $posted ) {
	if ( empty( $posted['billing_address_1'] ) ) {
		return;
	}
	$list = layerone_addresses( $customer_id );
	foreach ( $list as $a ) {
		if ( strtolower( $a['a1'] ) === strtolower( $posted['billing_address_1'] ) && $a['pin'] === $posted['billing_postcode'] ) {
			return;
		}
	}
	$list[] = array(
		'id'      => wp_generate_uuid4(),
		'label'   => 'Home',
		'name'    => trim( $posted['billing_first_name'] . ' ' . $posted['billing_last_name'] ),
		'phone'   => $posted['billing_phone'],
		'a1'      => $posted['billing_address_1'],
		'a2'      => $posted['billing_address_2'],
		'city'    => $posted['billing_city'],
		'state'   => $posted['billing_state'],
		'pin'     => $posted['billing_postcode'],
		'default' => ! $list,
	);
	update_user_meta( $customer_id, 'layerone_addresses', $list );
}, 10, 2 );

/* ====================================================================================================
 * Return requests
 * ================================================================================================== */

add_action( 'init', function () {
	register_post_type( 'lo_return', array(
		'labels'          => array( 'name' => 'Return requests', 'singular_name' => 'Return request', 'menu_name' => 'Return requests', 'all_items' => 'Return requests', 'edit_item' => 'Return request' ),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'woocommerce',
		'supports'        => array( 'title' ),
		'capability_type' => 'shop_order',
		'map_meta_cap'    => true,
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
	) );
} );

function layerone_return_statuses() {
	return array( 'Received' => 'Received', 'In review' => 'In review', 'Approved' => 'Approved', 'Reprinting' => 'Reprinting', 'Closed' => 'Closed' );
}

add_action( 'add_meta_boxes_lo_return', function () {
	add_meta_box( 'lo-return', 'Request', function ( $post ) {
		wp_nonce_field( 'lo_return_status', 'lo_return_nonce' );
		$m = function ( $k ) use ( $post ) {
			return get_post_meta( $post->ID, $k, true );
		};
		$order = wc_get_order( (int) $m( '_lo_order' ) );
		echo '<p><b>Order:</b> ' . ( $order ? '<a href="' . esc_url( $order->get_edit_order_url() ) . '">#' . esc_html( $order->get_order_number() ) . '</a>' : '—' ) . '</p>';
		echo '<p><b>Item:</b> ' . esc_html( $m( '_lo_item' ) ) . '</p><p><b>Reason:</b> ' . esc_html( $m( '_lo_reason' ) ) . '</p><p><b>Details:</b><br>' . nl2br( esc_html( $m( '_lo_details' ) ) ) . '</p>';
		$user = get_userdata( (int) $post->post_author );
		echo '<p><b>Customer:</b> ' . esc_html( $user ? $user->display_name . ' · ' . $user->user_email : '' ) . '</p>';
		echo '<p><label><b>Status</b> <select name="lo_return_status">';
		foreach ( layerone_return_statuses() as $s ) {
			echo '<option' . selected( $m( '_lo_status' ), $s, false ) . '>' . esc_html( $s ) . '</option>';
		}
		echo '</select></label></p>';
	}, 'lo_return', 'normal', 'high' );
} );

add_action( 'save_post_lo_return', function ( $post_id ) {
	if ( ! isset( $_POST['lo_return_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lo_return_nonce'] ) ), 'lo_return_status' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$s = isset( $_POST['lo_return_status'] ) ? sanitize_text_field( wp_unslash( $_POST['lo_return_status'] ) ) : '';
	if ( in_array( $s, layerone_return_statuses(), true ) ) {
		update_post_meta( $post_id, '_lo_status', $s );
	}
} );

add_filter( 'manage_lo_return_posts_columns', function () {
	return array( 'cb' => '<input type="checkbox">', 'title' => 'Request', 'lo_order' => 'Order', 'lo_item' => 'Item', 'lo_reason' => 'Reason', 'lo_status' => 'Status', 'date' => 'Date' );
} );
add_action( 'manage_lo_return_posts_custom_column', function ( $col, $id ) {
	$map = array( 'lo_order' => '_lo_order', 'lo_item' => '_lo_item', 'lo_reason' => '_lo_reason', 'lo_status' => '_lo_status' );
	if ( isset( $map[ $col ] ) ) {
		$v = get_post_meta( $id, $map[ $col ], true );
		echo esc_html( 'lo_order' === $col ? '#' . $v : $v );
	}
}, 10, 2 );

function layerone_user_returns( $uid = 0 ) {
	return get_posts( array( 'post_type' => 'lo_return', 'post_status' => 'publish', 'author' => $uid ? $uid : get_current_user_id(), 'numberposts' => 50, 'orderby' => 'date', 'order' => 'DESC' ) );
}

/* ====================================================================================================
 * Form handling (frontend posts with a hidden "lo_action")
 * ================================================================================================== */

add_action( 'wp_loaded', function () {
	if ( empty( $_POST['lo_action'] ) || ! is_user_logged_in() || ! function_exists( 'wc_add_notice' ) ) {
		return;
	}
	$action = sanitize_key( wp_unslash( $_POST['lo_action'] ) );
	$nonce  = isset( $_POST['_lo_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_lo_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'lo_account_' . $action ) ) {
		return;
	}
	$uid  = get_current_user_id();
	$post = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$back = wc_get_page_permalink( 'myaccount' );

	switch ( $action ) {
		case 'address_save':
			$back = wc_get_endpoint_url( 'edit-address', '', $back );
			$a    = array(
				'id'      => ! empty( $post['id'] ) ? sanitize_text_field( $post['id'] ) : wp_generate_uuid4(),
				'label'   => ! empty( $post['label'] ) ? sanitize_text_field( $post['label'] ) : 'Home',
				'name'    => sanitize_text_field( isset( $post['name'] ) ? $post['name'] : '' ),
				'phone'   => preg_replace( '/\D/', '', isset( $post['phone'] ) ? $post['phone'] : '' ),
				'a1'      => sanitize_text_field( isset( $post['a1'] ) ? $post['a1'] : '' ),
				'a2'      => sanitize_text_field( isset( $post['a2'] ) ? $post['a2'] : '' ),
				'city'    => sanitize_text_field( isset( $post['city'] ) ? $post['city'] : '' ),
				'state'   => sanitize_text_field( isset( $post['state'] ) ? $post['state'] : '' ),
				'pin'     => preg_replace( '/\D/', '', isset( $post['pin'] ) ? $post['pin'] : '' ),
				'default' => ! empty( $post['default'] ),
			);
			if ( ! $a['name'] || ! $a['a1'] || ! $a['city'] || ! WC()->countries->get_states( 'IN' ) || ! isset( WC()->countries->get_states( 'IN' )[ $a['state'] ] ) ) {
				wc_add_notice( 'Please fill in the name, address, city and state.', 'error' );
			} elseif ( ! preg_match( '/^[6-9][0-9]{9}$/', $a['phone'] ) ) {
				wc_add_notice( 'Please enter a 10-digit Indian mobile number.', 'error' );
			} elseif ( ! preg_match( '/^[1-9][0-9]{5}$/', $a['pin'] ) ) {
				wc_add_notice( 'Please enter a 6-digit PIN code.', 'error' );
			} else {
				$list  = layerone_addresses( $uid );
				$found = false;
				foreach ( $list as $k => $old ) {
					if ( $old['id'] === $a['id'] ) {
						$list[ $k ] = $a;
						$found      = true;
					}
				}
				if ( ! $found ) {
					$list[] = $a;
				}
				if ( $a['default'] ) {
					foreach ( $list as $k => $old ) {
						$list[ $k ]['default'] = $old['id'] === $a['id'];
					}
				}
				layerone_save_address_list( $uid, $list );
				wc_add_notice( 'Address saved.' );
			}
			break;

		case 'address_default':
		case 'address_delete':
			$back = wc_get_endpoint_url( 'edit-address', '', $back );
			$id   = isset( $post['id'] ) ? sanitize_text_field( $post['id'] ) : '';
			$list = layerone_addresses( $uid );
			foreach ( $list as $k => $a ) {
				if ( $a['id'] === $id ) {
					if ( 'address_delete' === $action ) {
						unset( $list[ $k ] );
					}
				}
				if ( 'address_default' === $action ) {
					$list[ $k ]['default'] = $a['id'] === $id;
				}
			}
			layerone_save_address_list( $uid, $list );
			wc_add_notice( 'address_delete' === $action ? 'Address deleted.' : 'Default address updated.' );
			break;

		case 'profile':
			$back  = wc_get_endpoint_url( 'edit-account', '', $back );
			$name  = sanitize_text_field( isset( $post['name'] ) ? $post['name'] : '' );
			$phone = preg_replace( '/\D/', '', isset( $post['phone'] ) ? $post['phone'] : '' );
			if ( ! $name ) {
				wc_add_notice( 'Please enter your name.', 'error' );
			} elseif ( $phone && ! preg_match( '/^[6-9][0-9]{9}$/', $phone ) ) {
				wc_add_notice( 'Please enter a 10-digit Indian mobile number.', 'error' );
			} else {
				$pos = strrpos( $name, ' ' );
				wp_update_user( array( 'ID' => $uid, 'display_name' => $name, 'first_name' => false === $pos ? $name : trim( substr( $name, 0, $pos ) ), 'last_name' => false === $pos ? '' : trim( substr( $name, $pos + 1 ) ) ) );
				update_user_meta( $uid, 'billing_phone', $phone );
				update_user_meta( $uid, 'layerone_marketing', ! empty( $post['marketing'] ) ? 1 : 0 );
				wc_add_notice( 'Your details are saved.' );
			}
			break;

		case 'password':
			$back = wc_get_endpoint_url( 'edit-account', '', $back );
			$user = get_userdata( $uid );
			$old  = isset( $post['old'] ) ? $post['old'] : '';
			$new  = isset( $post['new'] ) ? $post['new'] : '';
			if ( ! wp_check_password( $old, $user->user_pass, $uid ) ) {
				wc_add_notice( 'Your current password is not right.', 'error' );
			} elseif ( strlen( $new ) < 8 ) {
				wc_add_notice( 'The new password needs at least 8 characters.', 'error' );
			} elseif ( $new !== ( isset( $post['new2'] ) ? $post['new2'] : '' ) ) {
				wc_add_notice( 'The new passwords do not match.', 'error' );
			} else {
				wp_set_password( $new, $uid );
				wp_set_current_user( $uid );
				wp_set_auth_cookie( $uid );
				wc_add_notice( 'Your password is updated.' );
			}
			break;

		case 'delete_account':
			$user = get_userdata( $uid );
			if ( $user && in_array( 'customer', (array) $user->roles, true ) ) {
				require_once ABSPATH . 'wp-admin/includes/user.php';
				wp_logout();
				wp_delete_user( $uid );
				wp_safe_redirect( home_url( '/' ) );
				exit;
			}
			$back = wc_get_endpoint_url( 'edit-account', '', $back );
			wc_add_notice( 'This account cannot be deleted here. Please contact us.', 'error' );
			break;

		case 'return':
			$back  = wc_get_endpoint_url( 'returns', '', $back );
			$order = wc_get_order( isset( $post['order'] ) ? absint( $post['order'] ) : 0 );
			$idx   = isset( $post['item'] ) ? absint( $post['item'] ) : 0;
			$why   = sanitize_text_field( isset( $post['reason'] ) ? $post['reason'] : '' );
			$text  = sanitize_textarea_field( isset( $post['details'] ) ? $post['details'] : '' );
			$items = $order ? array_values( $order->get_items() ) : array();
			if ( ! $order || (int) $order->get_customer_id() !== $uid || ! isset( $items[ $idx ] ) ) {
				wc_add_notice( 'We could not find that order.', 'error' );
			} elseif ( strlen( $text ) < 10 ) {
				wc_add_notice( 'Please tell us a little more about what happened.', 'error' );
			} else {
				$id = wp_insert_post( array( 'post_type' => 'lo_return', 'post_status' => 'publish', 'post_title' => 'Return', 'post_author' => $uid ) );
				wp_update_post( array( 'ID' => $id, 'post_title' => 'R-' . str_pad( (string) $id, 4, '0', STR_PAD_LEFT ) ) );
				update_post_meta( $id, '_lo_order', $order->get_id() );
				update_post_meta( $id, '_lo_item', wp_specialchars_decode( $items[ $idx ]->get_name(), ENT_QUOTES ) );
				update_post_meta( $id, '_lo_reason', $why );
				update_post_meta( $id, '_lo_details', $text );
				update_post_meta( $id, '_lo_status', 'Received' );
				wp_mail( get_option( 'admin_email' ), 'New return request R-' . $id, "Order #{$order->get_order_number()}\nItem: " . $items[ $idx ]->get_name() . "\nReason: $why\n\n$text\n\n" . admin_url( 'post.php?post=' . $id . '&action=edit' ) );
				wc_add_notice( 'Request R-' . str_pad( (string) $id, 4, '0', STR_PAD_LEFT ) . ' received. Please send photos of the parcel and the item to our team and quote this number.' );
			}
			break;

		default:
			return;
	}
	wp_safe_redirect( $back );
	exit;
} );

/* ====================================================================================================
 * Registration: name, mobile, password confirmation and the terms
 * ================================================================================================== */

add_filter( 'woocommerce_process_registration_errors', function ( $error, $username, $password, $email ) {
	// phpcs:disable WordPress.Security.NonceVerification -- WooCommerce verified the registration nonce.
	$name  = isset( $_POST['lo_name'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['lo_name'] ) ) ) : '';
	$phone = isset( $_POST['lo_phone'] ) ? preg_replace( '/\D/', '', wp_unslash( $_POST['lo_phone'] ) ) : '';
	$pass2 = isset( $_POST['lo_password2'] ) ? wp_unslash( $_POST['lo_password2'] ) : '';
	// phpcs:enable
	if ( '' === $name ) {
		return new WP_Error( 'lo_name', 'Please enter your full name.' );
	}
	if ( strlen( $password ) < 8 ) {
		return new WP_Error( 'lo_pw', 'Your password needs at least 8 characters.' );
	}
	if ( $password !== $pass2 ) {
		return new WP_Error( 'lo_pw2', 'The two passwords do not match.' );
	}
	if ( $phone && ! preg_match( '/^[6-9][0-9]{9}$/', $phone ) ) {
		return new WP_Error( 'lo_phone', 'Please enter a 10-digit Indian mobile number.' );
	}
	if ( empty( $_POST['lo_terms'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return new WP_Error( 'lo_terms', 'Please agree to the Terms and the Privacy Policy.' );
	}
	return $error;
}, 10, 4 );

add_action( 'woocommerce_created_customer', function ( $customer_id ) {
	// phpcs:disable WordPress.Security.NonceVerification
	$name  = isset( $_POST['lo_name'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['lo_name'] ) ) ) : '';
	$phone = isset( $_POST['lo_phone'] ) ? preg_replace( '/\D/', '', wp_unslash( $_POST['lo_phone'] ) ) : '';
	// phpcs:enable
	if ( $name ) {
		$pos = strrpos( $name, ' ' );
		wp_update_user( array( 'ID' => $customer_id, 'display_name' => $name, 'first_name' => false === $pos ? $name : trim( substr( $name, 0, $pos ) ), 'last_name' => false === $pos ? '' : trim( substr( $name, $pos + 1 ) ) ) );
	}
	if ( $phone ) {
		update_user_meta( $customer_id, 'billing_phone', $phone );
	}
} );

/* After signing in or registering, go where the customer was headed (?redirect_to=…) */
add_filter( 'woocommerce_login_redirect', function ( $redirect ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	$to = isset( $_REQUEST['redirect'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect'] ) ) : '';
	return $to ? wp_validate_redirect( $to, $redirect ) : $redirect;
} );
add_filter( 'woocommerce_registration_redirect', function ( $redirect ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	$to = isset( $_REQUEST['redirect'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect'] ) ) : '';
	return $to ? wp_validate_redirect( $to, $redirect ) : $redirect;
} );

/* ====================================================================================================
 * Public pages: Wishlist and Track order (shortcodes on the pages of the same name)
 * ================================================================================================== */

/* Signed-in customers see their wishlist inside My account. */
add_action( 'template_redirect', function () {
	if ( is_page( 'wishlist' ) && is_user_logged_in() ) {
		wp_safe_redirect( wc_get_endpoint_url( 'wishlist', '', wc_get_page_permalink( 'myaccount' ) ) );
		exit;
	}
} );

add_shortcode( 'layerone_wishlist', function () {
	ob_start();
	get_template_part( 'template-parts/wishlist-page' );
	return ob_get_clean();
} );

add_shortcode( 'layerone_track_order', function () {
	ob_start();
	get_template_part( 'template-parts/track-order' );
	return ob_get_clean();
} );

/* Find an order by its number and the mobile number it was placed with. */
function layerone_find_order( $number, $phone ) {
	$id    = absint( preg_replace( '/\D/', '', (string) $number ) );
	$order = $id ? wc_get_order( $id ) : null;
	$mine  = substr( preg_replace( '/\D/', '', (string) $phone ), -10 );
	if ( ! $order || strlen( $mine ) < 10 ) {
		return null;
	}
	$theirs = substr( preg_replace( '/\D/', '', (string) $order->get_billing_phone() ), -10 );
	return hash_equals( $theirs, $mine ) ? $order : null;
}
