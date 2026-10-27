<?php
/**
 * Flexi Crew in the cart and in orders.
 *
 * A cart line can carry "lo_flexi" data:
 *   type    box | bundle
 *   figures [ figure product id => quantity ]  (a snapshot taken when the line was added)
 *   size    the box size that was bought (boxes)
 *   bonus   free extra figures included in the list (boxes)
 *
 * Boxes are priced here from the sizes table (WooCommerce > Flexi Crew), never from the browser.
 * Bundles use their own product price. Single figures are ordinary products and need nothing here.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- helpers ---------- */

function layerone_flexi_size_row( $size ) {
	foreach ( layerone_flexi_settings()['sizes'] as $row ) {
		if ( (int) $row['n'] === (int) $size ) {
			return $row;
		}
	}
	return null;
}

function layerone_flexi_box_price( $size ) {
	$row = layerone_flexi_size_row( $size );
	return $row ? (float) $row['price'] : 0.0;
}

/* Figures that the box size includes as a free extra (only the bonus size has them). */
function layerone_flexi_bonus_for( $size ) {
	$s = layerone_flexi_settings();
	return ( (int) $s['bonus_size'] > 0 && (int) $s['bonus_size'] === (int) $size ) ? (int) $s['bonus_count'] : 0;
}

function layerone_flexi_item_data( $cart_item ) {
	return isset( $cart_item['lo_flexi'] ) && is_array( $cart_item['lo_flexi'] ) ? $cart_item['lo_flexi'] : null;
}

function layerone_flexi_figure_count( $data ) {
	return $data ? (int) array_sum( array_map( 'intval', (array) $data['figures'] ) ) : 0;
}

function layerone_flexi_line_title( $data ) {
	if ( 'box' === $data['type'] ) {
		$bonus = ! empty( $data['bonus'] ) ? sprintf( ' (+%d bonus)', (int) $data['bonus'] ) : '';
		return sprintf( 'Flexi Crew box of %d%s', (int) $data['size'], $bonus );
	}
	return '';
}

/* "Burbu, Swik ×2, Finn" */
function layerone_flexi_figure_names( $figures ) {
	$out = array();
	foreach ( (array) $figures as $id => $qty ) {
		$name  = wp_specialchars_decode( get_the_title( (int) $id ), ENT_QUOTES );
		$out[] = (int) $qty > 1 ? $name . ' ×' . (int) $qty : $name;
	}
	return implode( ', ', $out );
}

/* ---------- adding to the cart ---------- */

/* Bundles: remember which figures were in the bundle at the moment it was added. */
add_filter( 'woocommerce_add_cart_item_data', function ( $data, $product_id ) {
	if ( isset( $data['lo_flexi'] ) || 'bundle' !== layerone_flexi_kind( $product_id ) ) {
		return $data;
	}
	$figures = array();
	foreach ( layerone_flexi_bundle_figure_ids( $product_id ) as $fid ) {
		$figures[ $fid ] = 1;
	}
	if ( $figures ) {
		$data['lo_flexi'] = array( 'type' => 'bundle', 'figures' => $figures );
	}
	return $data;
}, 10, 2 );

/* The box product can only be bought through the builder. */
add_filter( 'woocommerce_add_to_cart_validation', function ( $ok, $product_id, $quantity = 1 ) {
	if ( ! $ok ) {
		return $ok;
	}
	$kind = layerone_flexi_kind( $product_id );
	if ( 'box' === $kind ) {
		if ( ! doing_action( 'wp_ajax_layerone_flexi_add_box' ) && ! doing_action( 'wp_ajax_nopriv_layerone_flexi_add_box' ) ) {
			wc_add_notice( 'Please pick your figures in the box builder.', 'error' );
			return false;
		}
		return $ok;
	}
	/* bundles and single figures: the figures they use must still fit in stock together with what is in the cart */
	if ( 'bundle' === $kind ) {
		$extra = array();
		foreach ( layerone_flexi_bundle_figure_ids( $product_id ) as $fid ) {
			$extra[ $fid ] = (int) $quantity;
		}
	} elseif ( 'figure' === $kind ) {
		$extra = array( (int) $product_id => (int) $quantity );
	} else {
		return $ok;
	}
	$problem = layerone_flexi_stock_problem( layerone_flexi_merge_needs( layerone_flexi_cart_needs(), $extra ) );
	if ( $problem ) {
		wc_add_notice( $problem, 'error' );
		return false;
	}
	return $ok;
}, 10, 3 );

/* Box price comes from the sizes table. */
add_action( 'woocommerce_before_calculate_totals', function ( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}
	foreach ( $cart->get_cart() as $item ) {
		$d = layerone_flexi_item_data( $item );
		if ( $d && 'box' === $d['type'] ) {
			$item['data']->set_price( layerone_flexi_box_price( $d['size'] ) );
		}
	}
}, 20 );

/*
 * POST admin-ajax.php  action=layerone_flexi_add_box  nonce  size  figures={"<id>":qty,...}
 * Checks everything on the server, then puts the box in the cart.
 */
function layerone_flexi_ajax_add_box() {
	check_ajax_referer( 'layerone_cart', 'nonce' );
	if ( null === WC()->cart ) {
		wc_load_cart();
	}

	$fail = function ( $msg ) {
		wp_send_json_error( array( 'message' => $msg ) );
	};

	$size = isset( $_POST['size'] ) ? absint( $_POST['size'] ) : 0;
	if ( ! layerone_flexi_size_row( $size ) ) {
		$fail( 'Please choose a box size.' );
	}
	$box_id = (int) get_option( 'layerone_flexi_box_product' );
	if ( ! $box_id || 'box' !== layerone_flexi_kind( $box_id ) ) {
		$fail( 'The box is not set up yet.' );
	}

	$raw = isset( $_POST['figures'] ) ? json_decode( wp_unslash( $_POST['figures'] ), true ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( ! is_array( $raw ) ) {
		$fail( 'Your box could not be read.' );
	}

	$figures = array();
	foreach ( $raw as $id => $qty ) {
		$id  = (int) $id;
		$qty = (int) $qty;
		if ( $id < 1 || $qty < 1 ) {
			continue;
		}
		if ( 'figure' !== layerone_flexi_kind( $id ) || 'publish' !== get_post_status( $id ) ) {
			$fail( 'One of the figures is not available.' );
		}
		$figures[ $id ] = $qty;
	}

	$total = (int) array_sum( $figures );
	$bonus = layerone_flexi_bonus_for( $size );
	if ( $total !== $size && ! ( $bonus && $total === $size + $bonus ) ) {
		$left = $size - $total;
		$fail( $left > 0 ? sprintf( 'Pick %d more figure%s to fill the box.', $left, 1 === $left ? '' : 's' ) : sprintf( 'This box holds %d figures.', $size + $bonus ) );
	}

	$problem = layerone_flexi_stock_problem( layerone_flexi_merge_needs( layerone_flexi_cart_needs(), $figures ) );
	if ( $problem ) {
		$fail( $problem );
	}

	ksort( $figures ); /* same figures = same cart line, so identical boxes merge */
	$data = array(
		'type'    => 'box',
		'size'    => $size,
		'bonus'   => $total - $size,
		'figures' => $figures,
	);

	$key = WC()->cart->add_to_cart( $box_id, 1, 0, array(), array( 'lo_flexi' => $data ) );
	if ( ! $key ) {
		$notices = wc_get_notices( 'error' );
		wc_clear_notices();
		$fail( $notices ? wp_strip_all_tags( $notices[0]['notice'] ) : 'The box could not be added.' );
	}

	wp_send_json_success( array_merge( layerone_cart_state(), array( 'message' => layerone_flexi_line_title( $data ) . ' added to your cart' ) ) );
}
add_action( 'wp_ajax_layerone_flexi_add_box', 'layerone_flexi_ajax_add_box' );
add_action( 'wp_ajax_nopriv_layerone_flexi_add_box', 'layerone_flexi_ajax_add_box' );

/* ---------- showing it in the cart, checkout and emails ---------- */

add_filter( 'woocommerce_cart_item_name', function ( $name, $item ) {
	$d = layerone_flexi_item_data( $item );
	return ( $d && 'box' === $d['type'] ) ? esc_html( layerone_flexi_line_title( $d ) ) : $name;
}, 10, 2 );

add_filter( 'woocommerce_cart_item_thumbnail', function ( $thumb, $item ) {
	$d = layerone_flexi_item_data( $item );
	if ( $d && 'box' === $d['type'] && ! empty( $d['figures'] ) ) {
		$first = wc_get_product( (int) array_key_first( $d['figures'] ) );
		if ( $first && $first->get_image_id() ) {
			return $first->get_image( 'woocommerce_thumbnail' );
		}
	}
	return $thumb;
}, 10, 2 );

add_filter( 'woocommerce_get_item_data', function ( $data, $item ) {
	$d = layerone_flexi_item_data( $item );
	if ( $d ) {
		$data[] = array(
			'key'   => sprintf( '%d figures', layerone_flexi_figure_count( $d ) ),
			'value' => layerone_flexi_figure_names( $d['figures'] ),
		);
	}
	return $data;
}, 10, 2 );

/* ---------- orders ---------- */

add_action( 'woocommerce_checkout_create_order_line_item', function ( $item, $cart_item_key, $values ) {
	$d = layerone_flexi_item_data( $values );
	if ( ! $d ) {
		return;
	}
	if ( 'box' === $d['type'] ) {
		$item->set_name( layerone_flexi_line_title( $d ) );
	}
	$item->add_meta_data( sprintf( '%d figures', layerone_flexi_figure_count( $d ) ), layerone_flexi_figure_names( $d['figures'] ), true );
	$item->add_meta_data( '_lo_figures', wp_json_encode( $d['figures'] ), true ); /* hidden: figure id => quantity per unit */
	$item->add_meta_data( '_lo_flexi_type', $d['type'], true );
}, 10, 3 );

/* ---------- stock: figures inside boxes and bundles ---------- */

function layerone_flexi_order_needs( $order ) {
	$needs = array();
	foreach ( $order->get_items() as $item ) {
		$json = $item->get_meta( '_lo_figures' );
		$map  = $json ? json_decode( $json, true ) : null;
		if ( ! is_array( $map ) ) {
			continue;
		}
		foreach ( $map as $fid => $qty ) {
			$needs[ (int) $fid ] = ( $needs[ (int) $fid ] ?? 0 ) + (int) $qty * (int) $item->get_quantity();
		}
	}
	return $needs;
}

add_action( 'woocommerce_reduce_order_stock', function ( $order ) {
	if ( $order->get_meta( '_lo_flexi_stock_reduced' ) ) {
		return;
	}
	$needs = layerone_flexi_order_needs( $order );
	foreach ( $needs as $fid => $qty ) {
		$p = wc_get_product( $fid );
		if ( $p && $p->managing_stock() ) {
			wc_update_product_stock( $p, $qty, 'decrease' );
		}
	}
	if ( $needs ) {
		$order->update_meta_data( '_lo_flexi_stock_reduced', 1 );
		$order->save();
		$order->add_order_note( 'Flexi Crew figure stock reduced for the boxes and bundles in this order.' );
	}
} );

add_action( 'woocommerce_restore_order_stock', function ( $order ) {
	if ( ! $order->get_meta( '_lo_flexi_stock_reduced' ) ) {
		return;
	}
	foreach ( layerone_flexi_order_needs( $order ) as $fid => $qty ) {
		$p = wc_get_product( $fid );
		if ( $p && $p->managing_stock() ) {
			wc_update_product_stock( $p, $qty, 'increase' );
		}
	}
	$order->delete_meta_data( '_lo_flexi_stock_reduced' );
	$order->save();
	$order->add_order_note( 'Flexi Crew figure stock restored.' );
} );

/* How many of each figure the cart needs: boxes and bundles by their lists, single figures by quantity. [ figure id => count ] */
function layerone_flexi_cart_needs() {
	$needs = array();
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return $needs;
	}
	foreach ( WC()->cart->get_cart() as $item ) {
		$d = layerone_flexi_item_data( $item );
		if ( ! $d ) {
			if ( 'figure' === layerone_flexi_kind( $item['product_id'] ) ) {
				$needs[ (int) $item['product_id'] ] = ( $needs[ (int) $item['product_id'] ] ?? 0 ) + (int) $item['quantity'];
			}
			continue;
		}
		foreach ( $d['figures'] as $fid => $qty ) {
			$needs[ (int) $fid ] = ( $needs[ (int) $fid ] ?? 0 ) + (int) $qty * (int) $item['quantity'];
		}
	}
	return $needs;
}

function layerone_flexi_merge_needs( $a, $b ) {
	foreach ( $b as $fid => $qty ) {
		$a[ (int) $fid ] = ( $a[ (int) $fid ] ?? 0 ) + (int) $qty;
	}
	return $a;
}

/* First stock problem in a needs list, as a message; empty string when everything fits. */
function layerone_flexi_stock_problem( $needs ) {
	foreach ( $needs as $fid => $qty ) {
		$p = wc_get_product( $fid );
		if ( ! $p ) {
			continue;
		}
		$name = get_the_title( $fid );
		if ( ! $p->is_in_stock() ) {
			return sprintf( '%s is sold out.', $name );
		}
		if ( $p->managing_stock() && ! $p->backorders_allowed() && $p->get_stock_quantity() < $qty ) {
			return sprintf( 'Only %d of %s left, but your cart needs %d.', max( 0, (int) $p->get_stock_quantity() ), $name, $qty );
		}
	}
	return '';
}

/* Whole-cart check at the cart and checkout pages. */
add_action( 'woocommerce_check_cart_items', function () {
	$problem = layerone_flexi_stock_problem( layerone_flexi_cart_needs() );
	if ( $problem ) {
		wc_add_notice( $problem . ' Please adjust your cart.', 'error' );
	}
} );

/* Figures that sit in boxes and bundles in the cart, for the progress bar. */
function layerone_flexi_cart_figures() {
	$n = 0;
	if ( function_exists( 'WC' ) && WC()->cart ) {
		foreach ( WC()->cart->get_cart() as $item ) {
			$d = layerone_flexi_item_data( $item );
			if ( $d ) {
				$n += layerone_flexi_figure_count( $d ) * (int) $item['quantity'];
			}
		}
	}
	return $n;
}

/* ---------- the theme's cart sheet ---------- */

/* "Burbu, Swik, Finn +9 more" */
function layerone_flexi_short_names( $figures ) {
	$names = array();
	foreach ( array_keys( (array) $figures ) as $id ) {
		$names[] = wp_specialchars_decode( get_the_title( (int) $id ), ENT_QUOTES );
	}
	return implode( ', ', array_slice( $names, 0, 3 ) ) . ( count( $names ) > 3 ? sprintf( ' +%d more', count( $names ) - 3 ) : '' );
}

/* Fold-out list of the figures in a box or bundle. */
function layerone_flexi_details_html( $d ) {
	$total = layerone_flexi_figure_count( $d );
	$what  = 'box' === $d['type'] ? 'box' : 'bundle';
	$html  = '<details class="bx"><summary>' . esc_html( sprintf( 'See the %d figures in this %s', $total, $what ) ) . '</summary><ul class="bx-list">';
	foreach ( (array) $d['figures'] as $id => $qty ) {
		$p     = wc_get_product( (int) $id );
		$thumb = $p && $p->get_image_id() ? wp_get_attachment_image( $p->get_image_id(), 'thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) ) : '<span class="bx-ph"></span>';
		$html .= '<li>' . $thumb . '<span class="bx-n">' . esc_html( wp_specialchars_decode( get_the_title( (int) $id ), ENT_QUOTES ) ) . '</span>' . ( (int) $qty > 1 ? '<b>×' . (int) $qty . '</b>' : '' ) . '</li>';
	}
	return $html . '</ul></details>';
}

/* Progress bar: how many figures sit in boxes and bundles in the cart. */
function layerone_flexi_tier_html() {
	$f = layerone_flexi_cart_figures();
	if ( ! $f ) {
		return '';
	}
	$tiers = array_merge( array( 0 ), array_map( 'intval', explode( ',', layerone_flexi_settings()['tiers'] ) ) );
	$last  = count( $tiers ) - 1;
	$i     = 0;
	foreach ( $tiers as $x => $t ) {
		if ( $f >= $t ) {
			$i = $x;
		}
	}
	$next = null;
	foreach ( $tiers as $t ) {
		if ( $t > $f ) {
			$next = $t;
			break;
		}
	}
	$pct   = $f >= $tiers[ $last ] ? 100 : ( $i + ( $f - $tiers[ $i ] ) / ( $tiers[ $i + 1 ] - $tiers[ $i ] ) ) / $last * 100;
	$label = function ( $t, $x ) use ( $last ) {
		return $x === $last ? $t . '+' : (string) $t;
	};

	$spans = '';
	foreach ( $tiers as $x => $t ) {
		$spans .= sprintf( '<span class="%s" style="left:%s%%">%s</span>', $f >= $t ? 'on' : '', round( $x * 100 / $last, 2 ), esc_html( $label( $t, $x ) ) );
	}
	$text  = sprintf( '<b>%d figure%s</b> in your boxes', $f, 1 === $f ? '' : 's' );
	$text .= $next ? sprintf( ' · %d more to reach %s', $next - $f, $label( $next, array_search( $next, $tiers, true ) ) ) : ' · a true collector';

	return '<div class="ftier" aria-label="Figures in your boxes"><div class="ftier-bar"><i style="width:' . round( $pct, 1 ) . '%"></i>' . $spans . '</div><p>' . $text . '</p></div>';
}
