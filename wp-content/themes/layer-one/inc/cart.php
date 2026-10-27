<?php
/**
 * Cart sheet: server-rendered from WooCommerce, updated over admin-ajax.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function layerone_cart_count() {
	return function_exists( 'WC' ) && WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0;
}

/* Price as plain text with the currency symbol decoded. */
function layerone_plain_price( $amount ) {
	return html_entity_decode( wp_strip_all_tags( wc_price( $amount ) ), ENT_QUOTES, 'UTF-8' );
}

function layerone_ship_note() {
	$free = (int) layerone_opt( 'lo_free_ship' );
	if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
		return sprintf( 'Free shipping over %s · Printed and dispatched in 2–5 days.', layerone_plain_price( $free ) );
	}
	$sub = (float) WC()->cart->get_subtotal();
	if ( $sub >= $free ) {
		return 'You get free shipping. Printed and dispatched in 2–5 days.';
	}
	return sprintf( 'Add %s more for free shipping.', layerone_plain_price( $free - $sub ) );
}

/* Inner HTML of the .cl list. */
function layerone_cart_items_html() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
		return '<div class="empty">Your cart is empty.<br>Add a product to get started.</div>';
	}
	$html = layerone_flexi_tier_html();
	foreach ( WC()->cart->get_cart() as $key => $item ) {
		$product = $item['data'];
		if ( ! $product || ! $product->exists() ) {
			continue;
		}
		$name = $product->get_name();
		$cats = wp_strip_all_tags( wc_get_product_category_list( $product->get_parent_id() ?: $product->get_id(), ', ' ) );
		$img  = $product->get_image_id()
			? wp_get_attachment_image( $product->get_image_id(), 'thumbnail', false, array( 'alt' => '' ) )
			: '<div class="ph ci-ph"></div>';
		$link = esc_url( $product->get_permalink() );
		$k    = esc_attr( $key );

		/* Flexi Crew boxes and bundles: figure list, first figure as the photo */
		$details = '';
		$fx      = layerone_flexi_item_data( $item );
		if ( $fx ) {
			if ( 'box' === $fx['type'] ) {
				$name = layerone_flexi_line_title( $fx );
				$link = esc_url( home_url( '/collectibles/' ) );
				$first = wc_get_product( (int) array_key_first( $fx['figures'] ) );
				if ( $first && $first->get_image_id() ) {
					$img = wp_get_attachment_image( $first->get_image_id(), 'thumbnail', false, array( 'alt' => '' ) );
				}
			}
			$cats    = layerone_flexi_short_names( $fx['figures'] );
			$details = layerone_flexi_details_html( $fx );
		}

		$html .= '<div class="ci" data-key="' . $k . '">' . $img
			. '<div class="d"><b><a href="' . $link . '">' . esc_html( $name ) . '</a></b>'
			. ( $cats ? '<small>' . esc_html( apply_filters( 'layerone_cart_line_note', $cats, $item ) ) . '</small>' : '' ) . $details
			. '<div class="q"><button type="button" data-q="-1" data-key="' . $k . '" aria-label="Decrease">−</button><span>' . (int) $item['quantity'] . '</span><button type="button" data-q="1" data-key="' . $k . '" aria-label="Increase">+</button></div></div>'
			. '<div class="pr">' . wc_price( (float) $product->get_price() * $item['quantity'] ) . '</div></div>';
	}
	return $html;
}

function layerone_cart_state() {
	return array(
		'count'    => layerone_cart_count(),
		'items'    => layerone_cart_items_html(),
		'subtotal' => function_exists( 'WC' ) && WC()->cart ? layerone_plain_price( WC()->cart->get_subtotal() ) : '',
		'note'     => layerone_ship_note(),
	);
}

function layerone_ajax_cart() {
	check_ajax_referer( 'layerone_cart', 'nonce' );

	if ( ! function_exists( 'WC' ) ) {
		wp_send_json_error();
	}
	if ( null === WC()->cart ) {
		wc_load_cart();
	}

	$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
	$qty = isset( $_POST['qty'] ) ? absint( $_POST['qty'] ) : null;

	if ( $key && null !== $qty && WC()->cart->get_cart_item( $key ) ) {
		if ( 0 === $qty ) {
			WC()->cart->remove_cart_item( $key );
		} else {
			WC()->cart->set_quantity( $key, min( 99, $qty ) );
		}
	}

	wp_send_json_success( layerone_cart_state() );
}
add_action( 'wp_ajax_layerone_cart', 'layerone_ajax_cart' );
add_action( 'wp_ajax_nopriv_layerone_cart', 'layerone_ajax_cart' );
