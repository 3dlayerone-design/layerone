<?php
/**
 * Layer One theme functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Asset version = newest file time in assets/ and blocks/, so edited scripts and styles are never served from the browser cache. */
function layerone_assets_stamp() {
	$newest = 0;
	foreach ( array( 'assets', 'blocks' ) as $dir ) {
		$path = get_template_directory() . '/' . $dir;
		if ( ! is_dir( $path ) ) {
			continue;
		}
		$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS ) );
		foreach ( $it as $file ) {
			if ( $file->isFile() ) {
				$newest = max( $newest, $file->getMTime() );
			}
		}
	}
	return $newest;
}
define( 'LAYERONE_VERSION', '1.0.' . layerone_assets_stamp() );

require_once get_template_directory() . '/inc/class-flat-walker.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/cart.php';
require_once get_template_directory() . '/inc/blocks.php';
require_once get_template_directory() . '/inc/newsletter.php';
require_once get_template_directory() . '/inc/fonts.php';
require_once get_template_directory() . '/inc/flexi/model.php';
require_once get_template_directory() . '/inc/flexi/settings.php';
require_once get_template_directory() . '/inc/flexi/catalog.php';
require_once get_template_directory() . '/inc/flexi/cart.php';
require_once get_template_directory() . '/inc/flexi/page.php';
require_once get_template_directory() . '/inc/flexi/blocks-common.php';
require_once get_template_directory() . '/inc/shop.php';
require_once get_template_directory() . '/inc/wishlist.php';
require_once get_template_directory() . '/inc/checkout.php';
require_once get_template_directory() . '/inc/account.php';
if ( is_admin() ) {
	require_once get_template_directory() . '/inc/flexi/admin.php';
}

function layerone_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array( 'height' => 40, 'width' => 160, 'flex-width' => true, 'flex-height' => true ) );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus( array(
		'primary'        => __( 'Primary menu (header, mobile drawer)', 'layer-one' ),
		'footer-shop'    => __( 'Footer: Shop column', 'layer-one' ),
		'footer-support' => __( 'Footer: Support column', 'layer-one' ),
		'footer-company' => __( 'Footer: Company column', 'layer-one' ),
		'footer-legal'   => __( 'Footer: bottom links (Privacy, Terms…)', 'layer-one' ),
	) );
}
add_action( 'after_setup_theme', 'layerone_setup' );

function layerone_assets() {
	$uri = get_template_directory_uri() . '/assets';

	wp_enqueue_style( 'layerone-fonts', layerone_fonts_url(), array(), null );
	wp_enqueue_style( 'layerone-style', $uri . '/css/style.css', array( 'layerone-fonts' ), LAYERONE_VERSION );
	wp_enqueue_style( 'layerone-motion', $uri . '/css/motion.css', array( 'layerone-style' ), LAYERONE_VERSION );
	wp_enqueue_style( 'layerone-green', $uri . '/css/green.css', array( 'layerone-motion' ), LAYERONE_VERSION );
	wp_enqueue_style( 'layerone-type', $uri . '/css/type.css', array( 'layerone-green' ), LAYERONE_VERSION );
	wp_enqueue_style( 'layerone-wp', $uri . '/css/wp.css', array( 'layerone-type' ), LAYERONE_VERSION );

	/* Shop, category and search pages */
	if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() || is_search() ) ) {
		wp_enqueue_style( 'layerone-listing', $uri . '/css/listing.css', array( 'layerone-type' ), LAYERONE_VERSION );
		wp_enqueue_script( 'layerone-listing', $uri . '/js/listing.js', array( 'layerone-header' ), LAYERONE_VERSION, true );
	}

	/* Cart, checkout and account pages */
	$own_page = function_exists( 'is_cart' ) && ( is_account_page() || is_page( array( 'wishlist', 'track-order' ) ) );
	if ( $own_page ) {
		wp_enqueue_script( 'layerone-account', $uri . '/js/account.js', array( 'layerone-header', 'layerone-wishlist', 'layerone-shop' ), LAYERONE_VERSION, true );
	}
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || $own_page ) ) {
		wp_enqueue_style( 'layerone-pages', $uri . '/css/pages.css', array( 'layerone-type' ), LAYERONE_VERSION );
		wp_enqueue_style( 'layerone-account', $uri . '/css/account.css', array( 'layerone-pages' ), LAYERONE_VERSION );
	}
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		wp_enqueue_script( 'layerone-cart-page', $uri . '/js/cart-page.js', array( 'layerone-header' ), LAYERONE_VERSION, true );
	}

	/* Single product page */
	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_style( 'layerone-product', $uri . '/css/product.css', array( 'layerone-type' ), LAYERONE_VERSION );
		wp_enqueue_script( 'layerone-product', $uri . '/js/product.js', array( 'layerone-header', 'layerone-shop' ), LAYERONE_VERSION, true );
	}

	/* Theme switch runs in <head> so the colour theme is applied before first paint. */
	wp_enqueue_script( 'layerone-theme', $uri . '/js/theme.js', array(), LAYERONE_VERSION, false );
	wp_enqueue_script( 'layerone-header', $uri . '/js/header.js', array(), LAYERONE_VERSION, true );
	wp_enqueue_script( 'layerone-wishlist', $uri . '/js/wishlist.js', array( 'layerone-header' ), LAYERONE_VERSION, true );
	wp_enqueue_script( 'layerone-shop', $uri . '/js/shop.js', array( 'layerone-header' ), LAYERONE_VERSION, true );
	wp_localize_script( 'layerone-header', 'LAYERONE', array(
		'ajax'  => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'layerone_cart' ),
		'wish'  => layerone_wishlist_js_data(),
		'shopUrl' => layerone_shop_url(),
		'wcAdd' => class_exists( 'WC_AJAX' ) ? WC_AJAX::get_endpoint( 'add_to_cart' ) : '',
	) );
}
add_action( 'wp_enqueue_scripts', 'layerone_assets' );

/* The design brings its own layout; skip WooCommerce's layout stylesheets. */
add_filter( 'woocommerce_enqueue_styles', function ( $styles ) {
	unset( $styles['woocommerce-layout'], $styles['woocommerce-smallscreen'], $styles['woocommerce-general'] );
	return $styles;
} );

/* Helpers used by the header templates. */
function layerone_opt( $key ) {
	$defaults = layerone_customizer_defaults();
	return get_theme_mod( $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

function layerone_shop_url() {
	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
}

function layerone_account_url() {
	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
}

function layerone_cat_url( $slug ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		$link = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}
	return layerone_shop_url();
}

/* Announcement bar messages parsed from the Customizer textarea. */
function layerone_ann_slides() {
	$slides = array();
	if ( ! layerone_opt( 'lo_ann_on' ) ) {
		return $slides;
	}
	$json = json_decode( (string) layerone_opt( 'lo_ann_slides' ), true );
	if ( is_array( $json ) ) {
		foreach ( $json as $r ) {
			$slides[] = array(
				'bold' => isset( $r['bold'] ) ? trim( $r['bold'] ) : '',
				'text' => isset( $r['text'] ) ? trim( $r['text'] ) : '',
				'link' => isset( $r['link'] ) ? trim( $r['link'] ) : '',
				'url'  => ! empty( $r['url'] ) ? esc_url( $r['url'] ) : layerone_shop_url(),
			);
		}
		return $slides;
	}
	foreach ( preg_split( '/\r\n|\r|\n/', (string) layerone_opt( 'lo_ann_slides' ) ) as $line ) {
		if ( '' === trim( $line ) ) {
			continue;
		}
		$p        = array_map( 'trim', explode( '|', $line ) );
		$slides[] = array(
			'bold' => $p[0],
			'text' => isset( $p[1] ) ? $p[1] : '',
			'link' => isset( $p[2] ) ? $p[2] : '',
			'url'  => ! empty( $p[3] ) ? esc_url( $p[3] ) : layerone_shop_url(),
		);
	}
	return $slides;
}

/* Menu in a location as bare links (flat walker) or <li> links (list walker). Empty when no menu is assigned. */
function layerone_menu_html( $location, $walker_class = 'Layerone_Flat_Walker' ) {
	if ( ! has_nav_menu( $location ) ) {
		return '';
	}
	return wp_nav_menu( array(
		'theme_location' => $location,
		'container'      => false,
		'items_wrap'     => '%3$s',
		'walker'         => new $walker_class(),
		'depth'          => 1,
		'echo'           => false,
		'fallback_cb'    => false,
	) );
}

/* Header nav links. Falls back to the three product lines until a menu is assigned. */
function layerone_nav_links() {
	if ( has_nav_menu( 'primary' ) ) {
		return wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false,
			'items_wrap'     => '%3$s',
			'walker'         => new Layerone_Flat_Walker(),
			'depth'          => 1,
			'echo'           => false,
			'fallback_cb'    => false,
		) );
	}
	$items = array(
		'The Flexi Crew'       => layerone_cat_url( 'flexi-crew' ),
		'Home &amp; Desk'      => layerone_cat_url( 'home-desk' ),
		'Custom &amp; Corporate' => layerone_cat_url( 'custom-corporate' ),
	);
	$html = '';
	foreach ( $items as $label => $url ) {
		$html .= '<a href="' . esc_url( $url ) . '">' . $label . '</a>';
	}
	return $html;
}

/* The cart page posts its own form and reloads, so WooCommerce's ajax cart script (which only knows the default markup) is not needed. */
add_action( 'wp_enqueue_scripts', function () {
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		wp_dequeue_script( 'wc-cart' );
	}
}, 99 );
