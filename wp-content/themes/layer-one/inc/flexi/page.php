<?php
/**
 * Collectibles page plumbing: page URL, product page redirects, and the add-to-cart endpoint for bundles and single figures.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function layerone_flexi_page_url() {
	$page = get_page_by_path( 'collectibles' );
	return $page ? get_permalink( $page ) : home_url( '/collectibles/' );
}

/* Figures, bundles and the box have no product page of their own: send visitors to the Collectibles page. */
add_action( 'template_redirect', function () {
	if ( ! is_singular( 'product' ) ) {
		return;
	}
	$id   = get_queried_object_id();
	$kind = layerone_flexi_kind( $id );
	$url  = layerone_flexi_page_url();

	if ( 'box' === $kind ) {
		$to = $url . '#box';
	} elseif ( 'bundle' === $kind ) {
		$t  = get_term( (int) get_post_meta( $id, '_lo_bundle_term', true ), LAYERONE_FLEXI_TAX );
		$to = $url . ( $t && ! is_wp_error( $t ) ? '#' . $t->slug : '' );
	} elseif ( 'figure' === $kind ) {
		$to = $url . '#fig-' . get_post_meta( $id, '_lo_code', true );
	} else {
		return;
	}
	wp_safe_redirect( $to, 302 );
	exit;
} );

/*
 * POST admin-ajax.php  action=layerone_flexi_add_product  nonce  product_id
 * Adds a pre-built bundle or a single figure and answers with a readable message when it cannot be added.
 */
function layerone_flexi_ajax_add_product() {
	check_ajax_referer( 'layerone_cart', 'nonce' );
	if ( null === WC()->cart ) {
		wc_load_cart();
	}
	$id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$kind = layerone_flexi_kind( $id );
	if ( ! in_array( $kind, array( 'bundle', 'figure' ), true ) || 'publish' !== get_post_status( $id ) ) {
		wp_send_json_error( array( 'message' => 'This product cannot be added here.' ) );
	}

	$key = WC()->cart->add_to_cart( $id, 1 );
	if ( ! $key ) {
		$notices = wc_get_notices( 'error' );
		wc_clear_notices();
		wp_send_json_error( array( 'message' => $notices ? wp_strip_all_tags( $notices[0]['notice'] ) : 'It could not be added to your cart.' ) );
	}
	wc_clear_notices();
	wp_send_json_success( array_merge( layerone_cart_state(), array( 'message' => wp_specialchars_decode( get_the_title( $id ), ENT_QUOTES ) . ' added to your cart' ) ) );
}
add_action( 'wp_ajax_layerone_flexi_add_product', 'layerone_flexi_ajax_add_product' );
add_action( 'wp_ajax_nopriv_layerone_flexi_add_product', 'layerone_flexi_ajax_add_product' );
