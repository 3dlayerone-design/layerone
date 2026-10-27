<?php
/**
 * Checkout: the design's short form (full name, one address, India only) on top of WooCommerce's checkout.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Our own "have an account" hint and no coupon / login boxes above the form. */
add_action( 'init', function () {
	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
	/* the payment block sits in step 3 of the form, not under the summary */
	remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
} );

add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
	$b = &$fields['billing'];

	unset( $b['billing_company'] );
	$b['billing_email']['label']            = 'Email';
	$b['billing_phone']['label']            = 'Mobile number';
	$b['billing_city']['label']             = 'City';
	$b['billing_state']['label']            = 'State';
	$b['billing_postcode']['label']         = 'PIN code';
	$b['billing_first_name']['label']       = 'Full name';
	$b['billing_first_name']['placeholder'] = 'As on the parcel';
	$b['billing_last_name']['required']     = false;
	$b['billing_phone']['required']         = true;
	$b['billing_address_1']['label']        = 'Address';
	$b['billing_address_1']['placeholder']  = 'House no., building, street';
	$b['billing_address_2']['label']        = 'Area / landmark';
	$b['billing_address_2']['placeholder']  = 'Area, colony, landmark';
	$b['billing_postcode']['placeholder']   = '110001';

	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['label']       = 'Order note';
		$fields['order']['order_comments']['placeholder'] = 'Colour preference, gift message, delivery instructions…';
	}
	return $fields;
} );

/* "Full name" goes in one box; split it for WooCommerce, which keeps first and last name apart. */
add_filter( 'woocommerce_checkout_posted_data', function ( $data ) {
	$name = isset( $data['billing_first_name'] ) ? trim( $data['billing_first_name'] ) : '';
	if ( '' !== $name && empty( $data['billing_last_name'] ) && false !== strpos( $name, ' ' ) ) {
		$pos                        = strrpos( $name, ' ' );
		$data['billing_first_name'] = trim( substr( $name, 0, $pos ) );
		$data['billing_last_name']  = trim( substr( $name, $pos + 1 ) );
	}
	$data['billing_country'] = 'IN';
	return $data;
} );

/* Indian mobile number and PIN code. */
add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
	if ( ! empty( $data['billing_phone'] ) ) {
		$digits = preg_replace( '/\D/', '', $data['billing_phone'] );
		if ( 12 === strlen( $digits ) && 0 === strpos( $digits, '91' ) ) {
			$digits = substr( $digits, 2 );
		}
		if ( ! preg_match( '/^[6-9][0-9]{9}$/', $digits ) ) {
			$errors->add( 'lo_phone', 'Please enter a 10-digit Indian mobile number.' );
		}
	}
	if ( ! empty( $data['billing_postcode'] ) && ! preg_match( '/^[1-9][0-9]{5}$/', trim( $data['billing_postcode'] ) ) ) {
		$errors->add( 'lo_pin', 'Please enter a 6-digit PIN code.' );
	}
}, 10, 2 );

/* One field in the design's markup. */
function layerone_checkout_field( $checkout, $key, $extra = array() ) {
	$fields = $checkout->get_checkout_fields();
	$group  = 0 === strpos( $key, 'billing_' ) ? 'billing' : 'order';
	if ( empty( $fields[ $group ][ $key ] ) ) {
		return;
	}
	$f     = $fields[ $group ][ $key ];
	$value = $checkout->get_value( $key );
	$label = isset( $f['label'] ) ? $f['label'] : '';
	$attrs = array(
		'id'           => $key,
		'name'         => $key,
		'placeholder'  => isset( $f['placeholder'] ) ? $f['placeholder'] : '',
		'autocomplete' => isset( $f['autocomplete'] ) ? $f['autocomplete'] : '',
	);
	$attrs = array_merge( $attrs, $extra );
	$req   = ! empty( $f['required'] );
	$html  = '';
	foreach ( $attrs as $k => $v ) {
		if ( '' !== $v && null !== $v ) {
			$html .= ' ' . $k . '="' . esc_attr( $v ) . '"';
		}
	}
	if ( $req ) {
		$html .= ' required';
	}

	echo '<div class="fld"><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . ( $req ? '' : ' <i>(optional)</i>' ) . '</label>';
	if ( 'billing_state' === $key ) {
		echo '<select' . $html . '><option value="">Select state</option>'; // phpcs:ignore WordPress.Security.EscapeOutput
		foreach ( WC()->countries->get_states( 'IN' ) as $code => $name ) {
			echo '<option value="' . esc_attr( $code ) . '"' . selected( $value, $code, false ) . '>' . esc_html( $name ) . '</option>';
		}
		echo '</select>';
	} elseif ( 'order_comments' === $key ) {
		echo '<textarea rows="3"' . $html . '>' . esc_textarea( $value ) . '</textarea>'; // phpcs:ignore WordPress.Security.EscapeOutput
	} else {
		$type = isset( $f['type'] ) && in_array( $f['type'], array( 'email', 'tel' ), true ) ? $f['type'] : 'text';
		echo '<input type="' . esc_attr( $type ) . '"' . $html . ' value="' . esc_attr( $value ) . '">'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</div>';
}

/* Signed-in customers: fill the form from the saved account details (the default address), not from a stale session copy. */
add_filter( 'woocommerce_checkout_get_value', function ( $value, $input ) {
	if ( ! is_user_logged_in() || 0 !== strpos( $input, 'billing_' ) || isset( $_POST[ $input ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return $value;
	}
	$saved = get_user_meta( get_current_user_id(), $input, true );
	if ( 'billing_first_name' === $input ) {
		/* one "Full name" box */
		$saved = trim( $saved . ' ' . get_user_meta( get_current_user_id(), 'billing_last_name', true ) );
	}
	return '' !== $saved ? $saved : $value;
}, 10, 2 );
