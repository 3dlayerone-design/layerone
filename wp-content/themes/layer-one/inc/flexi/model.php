<?php
/**
 * Flexi Crew data model.
 *
 * - Figure  = WooCommerce product (kind "figure"), tagged with a series.
 * - Series and bundle = one hierarchical taxonomy "flexi_group": parent = bundle, child = series.
 * - Bundle  = WooCommerce product (kind "bundle") linked to a bundle term; its figures are every figure
 *             in the bundle's series. It is in stock only while all of its figures are.
 * - Box     = one WooCommerce product (kind "box"); the picked figures live in the cart item (Phase 2).
 *
 * Product meta keys: _lo_kind, _lo_code, _lo_no, _lo_animal, _lo_story, _lo_lesson,
 * _lo_video_mp4, _lo_video_webm (attachment ids), _lo_bundle_term, _lo_badge.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const LAYERONE_FLEXI_TAX = 'flexi_group';

function layerone_flexi_register_taxonomy() {
	register_taxonomy( LAYERONE_FLEXI_TAX, 'product', array(
		'labels'            => array(
			'name'          => 'Bundles & series',
			'singular_name' => 'Bundle or series',
			'menu_name'     => 'Flexi bundles & series',
			'all_items'     => 'All bundles & series',
			'add_new_item'  => 'Add bundle or series',
			'edit_item'     => 'Edit bundle or series',
			'parent_item'   => 'Bundle (parent of a series)',
			'search_items'  => 'Search bundles & series',
		),
		'hierarchical'      => true,
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => false,
		'query_var'         => false,
		'capabilities'      => array(
			'manage_terms' => 'manage_product_terms',
			'edit_terms'   => 'edit_product_terms',
			'delete_terms' => 'delete_product_terms',
			'assign_terms' => 'assign_product_terms',
		),
	) );

	register_term_meta( LAYERONE_FLEXI_TAX, 'lo_tint', array( 'type' => 'string', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => 'sanitize_hex_color' ) );
	register_term_meta( LAYERONE_FLEXI_TAX, 'lo_order', array( 'type' => 'integer', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => 'absint' ) );
}
add_action( 'init', 'layerone_flexi_register_taxonomy' );

/* ---------- term fields: tint (series) and sort order ---------- */

function layerone_flexi_term_fields_add() {
	?>
	<div class="form-field">
		<label for="lo_tint">Backdrop tint (series)</label>
		<input type="color" name="lo_tint" id="lo_tint" value="#dfe4e4">
		<p>The soft colour behind this series' figure photos. Used for series; ignored for bundles.</p>
	</div>
	<div class="form-field">
		<label for="lo_order">Sort order</label>
		<input type="number" name="lo_order" id="lo_order" value="0" min="0" step="1">
		<p>Lower numbers come first.</p>
	</div>
	<?php
}
add_action( LAYERONE_FLEXI_TAX . '_add_form_fields', 'layerone_flexi_term_fields_add' );

function layerone_flexi_term_fields_edit( $term ) {
	$tint  = get_term_meta( $term->term_id, 'lo_tint', true ) ?: '#dfe4e4';
	$order = (int) get_term_meta( $term->term_id, 'lo_order', true );
	?>
	<tr class="form-field">
		<th scope="row"><label for="lo_tint">Backdrop tint (series)</label></th>
		<td><input type="color" name="lo_tint" id="lo_tint" value="<?php echo esc_attr( $tint ); ?>"><p class="description">Used for series; ignored for bundles.</p></td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="lo_order">Sort order</label></th>
		<td><input type="number" name="lo_order" id="lo_order" value="<?php echo esc_attr( $order ); ?>" min="0" step="1"><p class="description">Lower numbers come first.</p></td>
	</tr>
	<?php
}
add_action( LAYERONE_FLEXI_TAX . '_edit_form_fields', 'layerone_flexi_term_fields_edit' );

function layerone_flexi_term_fields_save( $term_id ) {
	if ( ! current_user_can( 'edit_term', $term_id ) ) {
		return;
	}
	// phpcs:disable WordPress.Security.NonceVerification -- core verifies the term form nonce before these hooks fire.
	if ( isset( $_POST['lo_tint'] ) ) {
		update_term_meta( $term_id, 'lo_tint', sanitize_hex_color( wp_unslash( $_POST['lo_tint'] ) ) );
	}
	if ( isset( $_POST['lo_order'] ) ) {
		update_term_meta( $term_id, 'lo_order', absint( $_POST['lo_order'] ) );
	}
	// phpcs:enable
}
add_action( 'created_' . LAYERONE_FLEXI_TAX, 'layerone_flexi_term_fields_save' );
add_action( 'edited_' . LAYERONE_FLEXI_TAX, 'layerone_flexi_term_fields_save' );

/* ---------- helpers ---------- */

function layerone_flexi_kind( $product_id ) {
	return (string) get_post_meta( (int) $product_id, '_lo_kind', true );
}

/* Series terms (children) of a bundle term, in sort order. */
function layerone_flexi_series_of_bundle( $bundle_term_id ) {
	$terms = get_terms( array(
		'taxonomy'   => LAYERONE_FLEXI_TAX,
		'parent'     => (int) $bundle_term_id,
		'hide_empty' => false,
		'meta_key'   => 'lo_order', // phpcs:ignore WordPress.DB.SlowDBQuery
		'orderby'    => 'meta_value_num',
		'order'      => 'ASC',
	) );
	return is_wp_error( $terms ) ? array() : $terms;
}

/* Published figure product ids of the given series term ids, ordered by series then figure number. */
function layerone_flexi_figure_ids( $series_term_ids ) {
	$ids = array();
	foreach ( (array) $series_term_ids as $term_id ) {
		$found = get_posts( array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_lo_no', // phpcs:ignore WordPress.DB.SlowDBQuery
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'meta_query'     => array( array( 'key' => '_lo_kind', 'value' => 'figure' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
			'tax_query'      => array( array( 'taxonomy' => LAYERONE_FLEXI_TAX, 'field' => 'term_id', 'terms' => (int) $term_id, 'include_children' => false ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
			'no_found_rows'  => true,
		) );
		$ids = array_merge( $ids, $found );
	}
	return array_map( 'intval', $ids );
}

/* The figures that belong to a bundle product. */
function layerone_flexi_bundle_figure_ids( $bundle_product_id ) {
	$term_id = (int) get_post_meta( (int) $bundle_product_id, '_lo_bundle_term', true );
	if ( ! $term_id ) {
		return array();
	}
	$series = wp_list_pluck( layerone_flexi_series_of_bundle( $term_id ), 'term_id' );
	return layerone_flexi_figure_ids( $series );
}

/* A bundle is in stock only while every one of its figures is. */
function layerone_flexi_bundle_in_stock( $bundle_product_id ) {
	$ids = layerone_flexi_bundle_figure_ids( $bundle_product_id );
	if ( ! $ids ) {
		return false;
	}
	foreach ( $ids as $id ) {
		$p = wc_get_product( $id );
		if ( ! $p || ! $p->is_in_stock() ) {
			return false;
		}
	}
	return true;
}

function layerone_flexi_filter_in_stock( $in_stock, $product ) {
	if ( $product && 'bundle' === layerone_flexi_kind( $product->get_id() ) ) {
		return $in_stock && layerone_flexi_bundle_in_stock( $product->get_id() );
	}
	return $in_stock;
}
add_filter( 'woocommerce_product_is_in_stock', 'layerone_flexi_filter_in_stock', 10, 2 );

/* Figures and the box product only appear on the Collectibles page, never in the shop or search. */
function layerone_flexi_hide_figure( $product_id ) {
	if ( in_array( layerone_flexi_kind( $product_id ), array( 'figure', 'box' ), true ) ) {
		wp_set_object_terms( (int) $product_id, array( 'exclude-from-catalog', 'exclude-from-search' ), 'product_visibility' );
	}
}
