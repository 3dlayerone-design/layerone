<?php
/**
 * Flexi Crew catalogue for the front end: one JSON document with bundles, series, figures, bundle products and box rules.
 * GET /wp-json/layerone/v1/flexi
 * Built from products and terms, cached, and cleared whenever any of them change.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const LAYERONE_FLEXI_CACHE = 'layerone_flexi_catalog';

function layerone_flexi_price_fields( $product ) {
	$price   = $product->get_price();
	$regular = $product->get_regular_price();
	$sale    = $product->get_sale_price();
	return array(
		'price'   => '' === $price ? null : (float) $price,
		'regular' => '' === $regular ? null : (float) $regular,
		'sale'    => '' === $sale ? null : (float) $sale,
		'onSale'  => $product->is_on_sale(),
	);
}

function layerone_flexi_build_catalog() {
	/* bundles (parent terms) and their series (children) */
	$bundle_terms = get_terms( array(
		'taxonomy'   => LAYERONE_FLEXI_TAX,
		'parent'     => 0,
		'hide_empty' => false,
		'meta_key'   => 'lo_order', // phpcs:ignore WordPress.DB.SlowDBQuery
		'orderby'    => 'meta_value_num',
		'order'      => 'ASC',
	) );
	$bundle_terms = is_wp_error( $bundle_terms ) ? array() : $bundle_terms;

	$groups = array();
	$series = array();
	$series_bundle = array();
	foreach ( $bundle_terms as $b ) {
		$ids = array();
		foreach ( layerone_flexi_series_of_bundle( $b->term_id ) as $s ) {
			$ids[]                          = $s->term_id;
			$series_bundle[ $s->term_id ]   = $b->term_id;
			$series[]                       = array(
				'id'     => $s->term_id,
				'slug'   => $s->slug,
				'name'   => wp_specialchars_decode( $s->name, ENT_QUOTES ),
				'tint'   => get_term_meta( $s->term_id, 'lo_tint', true ) ?: '#dfe4e4',
				'bundle' => $b->term_id,
			);
		}
		$groups[] = array( 'id' => $b->term_id, 'slug' => $b->slug, 'name' => wp_specialchars_decode( $b->name, ENT_QUOTES ), 'series' => $ids );
	}
	$tints = wp_list_pluck( $series, 'tint', 'id' );

	/* figures */
	$figures = array();
	$all_ids = layerone_flexi_figure_ids( array_keys( $series_bundle ) );
	foreach ( $all_ids as $id ) {
		$p = wc_get_product( $id );
		if ( ! $p ) {
			continue;
		}
		$terms = wp_get_post_terms( $id, LAYERONE_FLEXI_TAX, array( 'fields' => 'ids' ) );
		$sid   = 0;
		foreach ( (array) $terms as $t ) {
			if ( isset( $series_bundle[ $t ] ) ) {
				$sid = (int) $t;
				break;
			}
		}
		$img   = (int) $p->get_image_id();
		$mp4   = (int) get_post_meta( $id, '_lo_video_mp4', true );
		$webm  = (int) get_post_meta( $id, '_lo_video_webm', true );
		$figures[] = array_merge( array(
			'id'       => $id,
			'code'     => (string) ( get_post_meta( $id, '_lo_code', true ) ?: $p->get_sku() ),
			'name'     => wp_specialchars_decode( $p->get_name(), ENT_QUOTES ),
			'animal'   => (string) get_post_meta( $id, '_lo_animal', true ),
			'no'       => (int) get_post_meta( $id, '_lo_no', true ),
			'series'   => $sid,
			'bundle'   => $sid ? $series_bundle[ $sid ] : 0,
			'tint'     => $sid && isset( $tints[ $sid ] ) ? $tints[ $sid ] : '#dfe4e4',
			'thumb'    => $img ? (string) wp_get_attachment_image_url( $img, 'medium' ) : '',
			'image'    => $img ? (string) wp_get_attachment_image_url( $img, 'large' ) : '',
			'mp4'      => $mp4 ? (string) wp_get_attachment_url( $mp4 ) : '',
			'webm'     => $webm ? (string) wp_get_attachment_url( $webm ) : '',
			'story'    => (string) get_post_meta( $id, '_lo_story', true ),
			'lesson'   => (string) get_post_meta( $id, '_lo_lesson', true ),
			'inStock'  => $p->is_in_stock(),
			'canBuy'   => $p->is_purchasable() && $p->is_in_stock(),
			'max'      => $p->managing_stock() ? max( 0, (int) $p->get_stock_quantity() ) : 99,
			'tags'     => wp_get_post_terms( $id, 'product_tag', array( 'fields' => 'slugs' ) ),
		), layerone_flexi_price_fields( $p ) );
	}

	/* pre-built bundle products */
	$bundles = array();
	$posts   = get_posts( array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
		'meta_query'     => array( array( 'key' => '_lo_kind', 'value' => 'bundle' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
		'no_found_rows'  => true,
	) );
	foreach ( $posts as $id ) {
		$p = wc_get_product( $id );
		if ( ! $p ) {
			continue;
		}
		$img       = (int) $p->get_image_id();
		$bundles[] = array_merge( array(
			'id'      => $id,
			'term'    => (int) get_post_meta( $id, '_lo_bundle_term', true ),
			'name'    => wp_specialchars_decode( $p->get_name(), ENT_QUOTES ),
			'line'    => wp_strip_all_tags( $p->get_short_description() ),
			'badge'   => (string) get_post_meta( $id, '_lo_badge', true ),
			'banner'  => $img ? (string) wp_get_attachment_image_url( $img, 'large' ) : '',
			'bannerSet' => $img ? layerone_srcset_attrs( $img, '(max-width:700px) 300px, 330px' ) : '',
			'url'     => layerone_flexi_page_url() . '#' . ( ( $t = get_term( (int) get_post_meta( $id, '_lo_bundle_term', true ), LAYERONE_FLEXI_TAX ) ) && ! is_wp_error( $t ) ? $t->slug : '' ),
			'figures' => layerone_flexi_bundle_figure_ids( $id ),
			'inStock' => $p->is_in_stock(),
			'canBuy'  => $p->is_purchasable() && $p->is_in_stock(),
		), layerone_flexi_price_fields( $p ) );
	}

	/* same order as the bundle terms (the order set under Bundles & series) */
	$term_order = array_flip( wp_list_pluck( $groups, 'id' ) );
	usort( $bundles, function ( $a, $b ) use ( $term_order ) {
		$ia = isset( $term_order[ $a['term'] ] ) ? $term_order[ $a['term'] ] : 999;
		$ib = isset( $term_order[ $b['term'] ] ) ? $term_order[ $b['term'] ] : 999;
		return $ia <=> $ib;
	} );

	/* box rules */
	$s = layerone_flexi_settings();
	return array(
		'groups'  => $groups,
		'series'  => $series,
		'figures' => $figures,
		'bundles' => $bundles,
		'box'     => array(
			'productId'   => (int) get_option( 'layerone_flexi_box_product' ),
			'sizes'       => array_values( $s['sizes'] ),
			'bonusSize'   => (int) $s['bonus_size'],
			'bonusCount'  => (int) $s['bonus_count'],
			'tiers'       => array_map( 'intval', explode( ',', $s['tiers'] ) ),
		),
		'pageUrl'      => layerone_flexi_page_url(),
		'freeShipping' => (int) layerone_opt( 'lo_free_ship' ),
	);
}

function layerone_flexi_catalog() {
	$cached = get_transient( LAYERONE_FLEXI_CACHE );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	$data = layerone_flexi_build_catalog();
	set_transient( LAYERONE_FLEXI_CACHE, $data, HOUR_IN_SECONDS );
	return $data;
}

function layerone_flexi_clear_cache() {
	delete_transient( LAYERONE_FLEXI_CACHE );
}
add_action( 'save_post_product', 'layerone_flexi_clear_cache' );
add_action( 'woocommerce_update_product', 'layerone_flexi_clear_cache' );
add_action( 'woocommerce_product_set_stock', 'layerone_flexi_clear_cache' );
add_action( 'deleted_post', 'layerone_flexi_clear_cache' );
add_action( 'created_' . LAYERONE_FLEXI_TAX, 'layerone_flexi_clear_cache' );
add_action( 'edited_' . LAYERONE_FLEXI_TAX, 'layerone_flexi_clear_cache' );
add_action( 'delete_' . LAYERONE_FLEXI_TAX, 'layerone_flexi_clear_cache' );
add_action( 'set_object_terms', 'layerone_flexi_clear_cache' );
add_action( 'update_option_layerone_flexi_settings', 'layerone_flexi_clear_cache' );
add_action( 'update_option_woocommerce_currency', 'layerone_flexi_clear_cache' );

add_action( 'rest_api_init', function () {
	register_rest_route( 'layerone/v1', '/flexi', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => function () {
			return rest_ensure_response( layerone_flexi_catalog() );
		},
	) );
} );

/* Lists for the block editors: figures, bundles and product tags to choose from. Editors only. */
add_action( 'rest_api_init', function () {
	register_rest_route( 'layerone/v1', '/flexi/options', array(
		'methods'             => 'GET',
		'permission_callback' => function () {
			return current_user_can( 'edit_posts' );
		},
		'callback'            => function () {
			$out = array( 'figures' => array(), 'bundles' => array(), 'tags' => array() );
			foreach ( array( 'figure' => 'figures', 'bundle' => 'bundles' ) as $kind => $key ) {
				$ids = get_posts( array(
					'post_type'      => 'product',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'orderby'        => 'title',
					'order'          => 'ASC',
					'meta_query'     => array( array( 'key' => '_lo_kind', 'value' => $kind ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
					'no_found_rows'  => true,
				) );
				foreach ( $ids as $id ) {
					$out[ $key ][] = array( 'id' => $id, 'name' => wp_specialchars_decode( get_the_title( $id ), ENT_QUOTES ), 'code' => (string) get_post_meta( $id, '_lo_code', true ) );
				}
			}
			/* WooCommerce does not count hidden products in a tag's count, and figures are hidden: count the figures ourselves. */
			$tally = array();
			foreach ( layerone_flexi_catalog()['figures'] as $f ) {
				foreach ( (array) $f['tags'] as $slug ) {
					$tally[ $slug ] = ( $tally[ $slug ] ?? 0 ) + 1;
				}
			}
			$tags = get_terms( array( 'taxonomy' => 'product_tag', 'hide_empty' => false ) );
			foreach ( is_wp_error( $tags ) ? array() : $tags as $t ) {
				$out['tags'][] = array( 'slug' => $t->slug, 'name' => wp_specialchars_decode( $t->name, ENT_QUOTES ), 'count' => max( (int) $t->count, $tally[ $t->slug ] ?? 0 ) );
			}
			return rest_ensure_response( $out );
		},
	) );
} );
