<?php
/**
 * Shop pieces shared by the listing, product, wishlist and home pages: the product card, colour variants,
 * the add-to-cart endpoint that remembers the chosen colour, and a few helpers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Colour swatches. The "Colour" product attribute lists the colours a product comes in, for example "yellow, white, blue". */
function layerone_colours() {
	return array(
		'black'  => '#141414',
		'white'  => '#f1f1f1',
		'grey'   => '#8b8b8b',
		'beige'  => '#d8c3a0',
		'red'    => '#e0392d',
		'orange' => '#f28a2e',
		'yellow' => '#f6c62a',
		'green'  => '#3fae6a',
		'blue'   => '#2f8be6',
		'purple' => '#8b5cf6',
		'pink'   => '#f27aa1',
	);
}

function layerone_product_colours( $product ) {
	$known = layerone_colours();
	$out   = array();
	foreach ( preg_split( '/\s*[|,]\s*/', strtolower( (string) $product->get_attribute( 'colour' ) ) ) as $c ) {
		if ( isset( $known[ $c ] ) && ! in_array( $c, $out, true ) ) {
			$out[] = $c;
		}
	}
	return $out;
}

function layerone_product_features( $product ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\s*[|,]\s*/', (string) $product->get_attribute( 'features' ) ) ) ) );
}

/* Most specific category name, for "Desk organiser" style labels. */
function layerone_product_cat_name( $product ) {
	$terms = wc_get_product_terms( $product->get_id(), 'product_cat', array( 'orderby' => 'parent', 'order' => 'DESC' ) );
	if ( ! $terms ) {
		return '';
	}
	$best = $terms[0];
	foreach ( $terms as $t ) {
		if ( $t->parent && ( ! $best->parent || term_is_ancestor_of( $best, $t, 'product_cat' ) ) ) {
			$best = $t;
		}
	}
	return wp_specialchars_decode( $best->name, ENT_QUOTES );
}

/* Products that belong in the shop: everything except figures and the box. Bundles are shop products. */
function layerone_is_shop_product( $product_id ) {
	return ! in_array( layerone_flexi_kind( $product_id ), array( 'figure', 'box' ), true );
}

function layerone_product_is_new( $product ) {
	return has_term( 'new', 'product_tag', $product->get_id() );
}

/* "-20%" for a sale price. */
function layerone_sale_percent( $product ) {
	if ( ! $product->is_on_sale() ) {
		return 0;
	}
	$reg  = (float) $product->get_regular_price();
	$sale = (float) $product->get_price();
	return $reg > 0 ? (int) round( ( $reg - $sale ) / $reg * 100 ) : 0;
}

/* The heart icon (the saved state is set by wishlist.js). */
function layerone_heart_button( $product_id ) {
	return '<button type="button" class="heart" data-wish="' . (int) $product_id . '" aria-label="Save to wishlist" aria-pressed="false"><svg class="i" viewBox="0 0 24 24"><path d="M12 20s-7.5-4.6-7.5-10.3A4.2 4.2 0 0 1 12 7.4a4.2 4.2 0 0 1 7.5 2.3C19.5 15.4 12 20 12 20z"/></svg></button>';
}

/* Product card, the same markup the design draws in JS. */
function layerone_product_card( $product, $i = 0 ) {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( $product );
	}
	if ( ! $product ) {
		return '';
	}
	$id      = $product->get_id();
	$name    = wp_specialchars_decode( $product->get_name(), ENT_QUOTES );
	$url     = esc_url( get_permalink( $id ) );
	$cols    = layerone_product_colours( $product );
	$known   = layerone_colours();
	$pct     = layerone_sale_percent( $product );
	$img_id  = $product->get_image_id();
	$simple  = $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock();
	$price   = $product->get_price();

	$tag = '';
	if ( $pct ) {
		$tag = '<span class="tag sale">-' . $pct . '%</span>';
	} elseif ( layerone_product_is_new( $product ) ) {
		$tag = '<span class="tag new">New</span>';
	}

	$swatches = '';
	if ( $cols ) {
		$shown    = array_slice( $cols, 0, 5 );
		$more     = count( $cols ) - count( $shown );
		$swatches = '<div class="cdots" role="group" aria-label="Colour">';
		foreach ( $shown as $k => $c ) {
			$swatches .= '<button type="button" class="' . ( $k ? '' : 'on' ) . '" data-sw="' . esc_attr( $c ) . '" style="--c:' . esc_attr( $known[ $c ] ) . '" title="' . esc_attr( ucfirst( $c ) ) . '" aria-label="' . esc_attr( ucfirst( $c ) ) . '" aria-pressed="' . ( $k ? 'false' : 'true' ) . '"></button>';
		}
		$swatches .= ( $more > 0 ? '<span class="cd-more">+' . (int) $more . '</span>' : '' ) . '<span class="cd-name">' . esc_html( ucfirst( $cols[0] ) ) . '</span></div>';
	}

	$feat = '';
	foreach ( layerone_product_features( $product ) as $f ) {
		$feat .= '<span>' . esc_html( $f ) . '</span>';
	}

	if ( $img_id ) {
		$img = wp_get_attachment_image( $img_id, 'large', false, array( 'alt' => $name, 'sizes' => '(max-width:860px) 50vw, 25vw', 'loading' => 'lazy', 'decoding' => 'async' ) );
	} else {
		$img = '<div class="ph"><span>Photo coming</span></div>';
	}

	$quick = $simple
		? '<button type="button" class="quick" data-add="' . (int) $id . '"' . ( $cols ? ' data-col="' . esc_attr( $cols[0] ) . '"' : '' ) . '>Add to cart</button>'
		: '<a class="quick" href="' . $url . '">' . ( $product->is_in_stock() ? 'Choose options' : 'Sold out' ) . '</a>';

	$price_html = '' === $price ? '' : esc_html( layerone_plain_price( (float) $price ) ) . ( $product->is_on_sale() && $product->get_regular_price() ? '<s>' . esc_html( layerone_plain_price( (float) $product->get_regular_price() ) ) . '</s>' : '' );

	return '<article class="prod" style="animation-delay:' . esc_attr( round( $i * 0.05, 2 ) ) . 's">'
		. '<div class="pimg ' . ( $img_id ? 'photo' : '' ) . '"><a class="plink" href="' . $url . '" aria-label="View ' . esc_attr( $name ) . '"></a>'
		. $tag . layerone_heart_button( $id ) . $img . $quick . '</div>'
		. '<div class="pinfo"><div class="pcat">' . esc_html( layerone_product_cat_name( $product ) ) . '</div>'
		. '<h3><a href="' . $url . '">' . esc_html( $name ) . '</a></h3>' . $swatches
		. '<div class="meta">Printed to order · ships in 3–5 days</div>'
		. '<div class="row"><div class="price">' . $price_html . '</div><div class="fm">' . $feat . '</div></div></div></article>';
}

/* ---------- add to cart from cards and the product page (remembers the colour) ---------- */

/*
 * POST admin-ajax.php  action=layerone_add_to_cart  nonce  product_id  qty  colour
 */
function layerone_ajax_add_to_cart() {
	check_ajax_referer( 'layerone_cart', 'nonce' );
	if ( null === WC()->cart ) {
		wc_load_cart();
	}
	$id  = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$qty = isset( $_POST['qty'] ) ? max( 1, min( 99, absint( $_POST['qty'] ) ) ) : 1;
	$p   = $id ? wc_get_product( $id ) : null;

	if ( ! $p || 'publish' !== get_post_status( $id ) || ! layerone_is_shop_product( $id ) || ! $p->is_type( 'simple' ) ) {
		wp_send_json_error( array( 'message' => 'This product cannot be added here.' ) );
	}

	$data   = array();
	$colour = isset( $_POST['colour'] ) ? sanitize_key( wp_unslash( $_POST['colour'] ) ) : '';
	if ( $colour && in_array( $colour, layerone_product_colours( $p ), true ) ) {
		$data['lo_colour'] = $colour;
	}

	$key = WC()->cart->add_to_cart( $id, $qty, 0, array(), $data );
	if ( ! $key ) {
		$notices = wc_get_notices( 'error' );
		wc_clear_notices();
		wp_send_json_error( array( 'message' => $notices ? wp_strip_all_tags( $notices[0]['notice'] ) : 'It could not be added to your cart.' ) );
	}
	wc_clear_notices();
	wp_send_json_success( array_merge( layerone_cart_state(), array( 'message' => wp_specialchars_decode( $p->get_name(), ENT_QUOTES ) . ' added to cart' ) ) );
}
add_action( 'wp_ajax_layerone_add_to_cart', 'layerone_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_layerone_add_to_cart', 'layerone_ajax_add_to_cart' );

/* A colour makes a separate cart line, and shows in the cart, checkout and the order. */
add_filter( 'woocommerce_get_item_data', function ( $data, $item ) {
	if ( ! empty( $item['lo_colour'] ) ) {
		$data[] = array( 'key' => 'Colour', 'value' => ucfirst( $item['lo_colour'] ) );
	}
	return $data;
}, 10, 2 );

add_action( 'woocommerce_checkout_create_order_line_item', function ( $item, $key, $values ) {
	if ( ! empty( $values['lo_colour'] ) ) {
		$item->add_meta_data( 'Colour', ucfirst( $values['lo_colour'] ), true );
	}
}, 10, 3 );

/* The cart sheet shows the colour next to the category. */
add_filter( 'layerone_cart_line_note', function ( $note, $item ) {
	return ! empty( $item['lo_colour'] ) ? trim( $note . ' · ' . ucfirst( $item['lo_colour'] ), ' ·' ) : $note;
}, 10, 2 );

/* "Clear cart" link on the cart page. */
add_action( 'wp_loaded', function () {
	if ( empty( $_GET['lo_clear_cart'] ) || ! function_exists( 'WC' ) || ! WC()->cart ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	if ( wp_verify_nonce( isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '', 'lo_clear_cart' ) ) {
		WC()->cart->empty_cart();
		wc_add_notice( 'Your cart was cleared.' );
	}
	wp_safe_redirect( wc_get_cart_url() );
	exit;
} );
