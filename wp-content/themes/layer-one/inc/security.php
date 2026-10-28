<?php
/**
 * Abuse protection: per-visitor rate limits, no account guessing, no username leaks.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* The visitor's address (the server's own, not a header the visitor can write). */
function layerone_visitor_key() {
	return md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
}

/* True while this visitor is under the limit for $bucket; $hit = count this call as an attempt. */
function layerone_rate_ok( $bucket, $max, $window, $hit = true ) {
	$key   = 'lo_rl_' . $bucket . '_' . layerone_visitor_key();
	$count = (int) get_transient( $key );
	if ( $count >= $max ) {
		return false;
	}
	if ( $hit ) {
		set_transient( $key, $count + 1, $window );
	}
	return true;
}

/* Wrong order number + phone guesses on the Track order form: 10 misses per 15 minutes. */
function layerone_track_locked() {
	return ! layerone_rate_ok( 'track', 10, 15 * MINUTE_IN_SECONDS, false );
}
function layerone_track_miss() {
	layerone_rate_ok( 'track', 10, 15 * MINUTE_IN_SECONDS, true );
}

/* New accounts: 5 per hour from one visitor. */
add_filter( 'woocommerce_registration_errors', function ( $errors ) {
	if ( ! layerone_rate_ok( 'register', 5, HOUR_IN_SECONDS ) ) {
		$errors->add( 'lo_rate', 'Too many accounts were created from this connection. Please try again later.' );
	}
	return $errors;
} );

/* One message for every sign-in failure, so nobody can find out which emails have an account. */
add_filter( 'authenticate', function ( $user ) {
	if ( is_wp_error( $user ) && in_array( $user->get_error_code(), array( 'invalid_username', 'invalid_email', 'incorrect_password' ), true ) ) {
		return new WP_Error( 'lo_invalid_login', 'The email or password you entered is incorrect.' );
	}
	return $user;
}, 99 );

/* Failed sign-ins: 10 per 15 minutes per visitor. */
add_action( 'wp_login_failed', function () {
	layerone_rate_ok( 'login', 10, 15 * MINUTE_IN_SECONDS );
} );
add_filter( 'authenticate', function ( $user ) {
	if ( ! layerone_rate_ok( 'login', 10, 15 * MINUTE_IN_SECONDS, false ) && ! is_wp_error( $user ) ) {
		return $user; /* correct details still get in */
	}
	if ( ! layerone_rate_ok( 'login', 10, 15 * MINUTE_IN_SECONDS, false ) && is_wp_error( $user ) ) {
		return new WP_Error( 'lo_locked', 'Too many tries. Please wait 15 minutes and try again.' );
	}
	return $user;
}, 100 );

/* "Forgot password" answers the same for a known and an unknown email, and sends at most 5 per hour from one visitor. */
add_action( 'wp_loaded', function () {
	if ( empty( $_POST['wc_reset_password'] ) || empty( $_POST['user_login'] ) || ! function_exists( 'wc_get_account_endpoint_url' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	$nonce = isset( $_POST['woocommerce-lost-password-nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['woocommerce-lost-password-nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'lost_password' ) ) {
		return;
	}
	$login = trim( sanitize_text_field( wp_unslash( $_POST['user_login'] ) ) );
	$user  = is_email( $login ) ? get_user_by( 'email', $login ) : get_user_by( 'login', $login );
	if ( $user && layerone_rate_ok( 'reset', 5, HOUR_IN_SECONDS ) ) {
		return; /* WooCommerce sends the real email */
	}
	wp_safe_redirect( add_query_arg( 'reset-link-sent', 'true', wc_get_account_endpoint_url( 'lost-password' ) ) );
	exit;
}, 5 );

/* Do not let visitors list usernames. */
add_action( 'template_redirect', function () {
	if ( is_author() || ( isset( $_GET['author'] ) && '' !== $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}, 1 );
add_action( 'parse_request', function ( $wp ) {
	if ( ! is_admin() && isset( $_GET['author'] ) && '' !== $_GET['author'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
} );
add_filter( 'rest_endpoints', function ( $endpoints ) {
	if ( ! is_user_logged_in() ) {
		foreach ( array_keys( $endpoints ) as $route ) {
			if ( 0 === strpos( $route, '/wp/v2/users' ) ) {
				unset( $endpoints[ $route ] );
			}
		}
	}
	return $endpoints;
} );
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}, 10, 2 );

/* No XML-RPC (it allows password guessing in bulk). */
add_filter( 'xmlrpc_enabled', '__return_false' );

/* Browser safety headers, and nothing that tells strangers which versions run here. */
add_action( 'send_headers', function () {
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );
	header_remove( 'X-Powered-By' );
} );
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );
add_action( 'init', function () {
	remove_action( 'wp_head', 'wc_generator_tag' );
	remove_action( 'wp_head', 'woocommerce_version' );
} );
