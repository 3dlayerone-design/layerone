<?php
/**
 * Drop list (newsletter) signups: stored in an option, listed under Tools > Drop list.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function layerone_droplist_submit() {
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$nonce = isset( $_POST['_lo_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_lo_nonce'] ) ) : '';
	$ok    = is_email( $email ) && wp_verify_nonce( $nonce, 'layerone_droplist' );

	if ( $ok ) {
		$list = (array) get_option( 'layerone_droplist', array() );
		if ( ! in_array( $email, $list, true ) ) {
			$list[] = $email;
			update_option( 'layerone_droplist', $list, false );
		}
	}

	if ( isset( $_POST['ajax'] ) ) {
		$ok ? wp_send_json_success() : wp_send_json_error();
	}

	wp_safe_redirect( add_query_arg( 'droplist', $ok ? 'ok' : 'error', wp_get_referer() ?: home_url( '/' ) ) );
	exit;
}
add_action( 'admin_post_nopriv_layerone_droplist', 'layerone_droplist_submit' );
add_action( 'admin_post_layerone_droplist', 'layerone_droplist_submit' );

add_action( 'admin_menu', function () {
	add_management_page( 'Drop list', 'Drop list', 'manage_options', 'layerone-droplist', function () {
		$list = (array) get_option( 'layerone_droplist', array() );
		echo '<div class="wrap"><h1>Drop list (' . count( $list ) . ')</h1>';
		echo $list ? '<ol><li>' . implode( '</li><li>', array_map( 'esc_html', $list ) ) . '</li></ol>' : '<p>No signups yet.</p>';
		echo '</div>';
	} );
} );
