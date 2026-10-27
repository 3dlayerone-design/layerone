<?php
/**
 * Wishlist, built into the theme (no plugin).
 *
 * - Signed-in customers: saved in the user meta "layerone_wishlist" (a list of product ids), so it follows them to any device.
 * - Guests: kept in the browser (localStorage) and merged into the account at sign-in.
 *
 * assets/js/wishlist.js drives the hearts and the header counter; the wishlist page asks for its cards here.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const LAYERONE_WISH_META = 'layerone_wishlist';

function layerone_wishlist_ids( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return array();
	}
	$ids = get_user_meta( $user_id, LAYERONE_WISH_META, true );
	return is_array( $ids ) ? array_values( array_unique( array_map( 'intval', $ids ) ) ) : array();
}

function layerone_wishlist_save( $user_id, $ids ) {
	/* only real, published shop products */
	$ids = array_values( array_filter( array_unique( array_map( 'intval', (array) $ids ) ), function ( $id ) {
		return 'product' === get_post_type( $id ) && 'publish' === get_post_status( $id ) && layerone_is_shop_product( $id );
	} ) );
	update_user_meta( $user_id, LAYERONE_WISH_META, $ids );
	return $ids;
}

function layerone_wishlist_page_url() {
	$page = get_page_by_path( 'wishlist' );
	return $page ? get_permalink( $page ) : home_url( '/wishlist/' );
}

/* Hand the state to the front end. Printed with the other theme settings by functions.php (see layerone_assets). */
function layerone_wishlist_js_data() {
	return array(
		'loggedIn' => is_user_logged_in(),
		'ids'      => layerone_wishlist_ids(),
		'url'      => layerone_wishlist_page_url(),
		'loginUrl' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url(),
	);
}

/*
 * POST admin-ajax.php  action=layerone_wish  nonce  do=toggle|merge|remove  product_id | ids[]
 * Signed-in customers only; guests never call this.
 */
function layerone_ajax_wish() {
	check_ajax_referer( 'layerone_cart', 'nonce' );
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => 'Please sign in.' ), 401 );
	}
	$uid  = get_current_user_id();
	$ids  = layerone_wishlist_ids( $uid );
	$do   = isset( $_POST['do'] ) ? sanitize_key( wp_unslash( $_POST['do'] ) ) : 'toggle';
	$pid  = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$on   = null;

	if ( 'merge' === $do ) {
		$incoming = isset( $_POST['ids'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['ids'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$ids      = array_merge( $ids, $incoming );
	} elseif ( $pid ) {
		$at = array_search( $pid, $ids, true );
		if ( 'remove' === $do || ( 'toggle' === $do && false !== $at ) ) {
			if ( false !== $at ) {
				unset( $ids[ $at ] );
			}
			$on = false;
		} else {
			$ids[] = $pid;
			$on    = true;
		}
	}
	$ids = layerone_wishlist_save( $uid, $ids );
	wp_send_json_success( array( 'ids' => $ids, 'on' => $on ) );
}
add_action( 'wp_ajax_layerone_wish', 'layerone_ajax_wish' );

/*
 * GET admin-ajax.php?action=layerone_wish_cards&ids=1,2,3
 * Product cards (the same markup as the shop) for the wishlist page; works for guests too.
 */
function layerone_ajax_wish_cards() {
	$ids = isset( $_GET['ids'] ) ? array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_GET['ids'] ) ) ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
	$ids = array_slice( array_unique( $ids ), 0, 100 );
	$out = array();
	foreach ( $ids as $i => $id ) {
		$p = wc_get_product( $id );
		if ( $p && 'publish' === $p->get_status() && layerone_is_shop_product( $id ) ) {
			$out[] = array( 'id' => $id, 'html' => layerone_product_card( $p, $i ) );
		}
	}
	wp_send_json_success( array( 'cards' => $out ) );
}
add_action( 'wp_ajax_layerone_wish_cards', 'layerone_ajax_wish_cards' );
add_action( 'wp_ajax_nopriv_layerone_wish_cards', 'layerone_ajax_wish_cards' );

/* Wishlists of deleted accounts go with them (user meta is removed by WordPress), and unpublished products drop out on the next save. */
