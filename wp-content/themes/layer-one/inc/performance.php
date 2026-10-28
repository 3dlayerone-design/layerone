<?php
/**
 * Page speed: load only the scripts a page needs, keep fonts from blocking the first paint,
 * and give every page a description. Nothing here changes how the site looks.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* The WordPress emoji script and styles are not used by the theme. */
add_action( 'init', function () {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
} );
add_filter( 'tiny_mce_plugins', function ( $plugins ) {
	return array_diff( (array) $plugins, array( 'wpemoji' ) );
} );

/*
 * WooCommerce scripts the theme does not use: the shop, cart sheet and product page run on the theme's own scripts.
 * Checkout keeps what it needs (WooCommerce loads those through its own dependencies).
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( is_admin() ) {
		return;
	}
	$checkout = function_exists( 'is_checkout' ) && is_checkout();

	foreach ( array( 'wc-add-to-cart', 'wc-single-product', 'zoom', 'flexslider', 'photoswipe', 'photoswipe-ui-default', 'wc-zoom', 'wc-flexslider', 'wc-photoswipe', 'wc-photoswipe-ui-default', 'wc-password-strength-meter', 'password-strength-meter', 'zxcvbn-async', 'wc-account-i18n' ) as $h ) {
		wp_dequeue_script( $h );
	}
	foreach ( array( 'photoswipe', 'photoswipe-default-skin' ) as $h ) {
		wp_dequeue_style( $h );
	}
	/* the product page has its own gallery and zoom: remove these completely so nothing adds them back later */
	foreach ( array( 'wc-single-product', 'zoom', 'flexslider', 'photoswipe', 'photoswipe-ui-default', 'wc-zoom', 'wc-flexslider', 'wc-photoswipe', 'wc-photoswipe-ui-default' ) as $h ) {
		wp_deregister_script( $h );
	}
	if ( ! $checkout ) {
		foreach ( array( 'selectWoo', 'select2', 'wc-checkout', 'wc-address-i18n', 'wc-country-select', 'sourcebuster-js', 'wc-order-attribution', 'woocommerce', 'jquery-blockui', 'js-cookie' ) as $h ) {
			wp_dequeue_script( $h );
		}
		wp_dequeue_style( 'select2' );
	}
	/* jQuery Migrate only keeps very old plugins alive. */
	if ( ! is_admin() && isset( wp_scripts()->registered['jquery'] ) ) {
		wp_scripts()->registered['jquery']->deps = array_diff( wp_scripts()->registered['jquery']->deps, array( 'jquery-migrate' ) );
	}
}, 100 );

/* The two fonts are in the theme; load the Latin files at once so text never waits for them. */
add_action( 'wp_head', function () {
	foreach ( array( 'manrope-latin-wght-normal', 'inter-latin-wght-normal' ) as $f ) {
		echo '<link rel="preload" href="' . esc_url( get_template_directory_uri() . '/assets/fonts/' . $f . '.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "
";
	}
}, 1 );

/* A description for search results and link previews (also on pages that have none of their own). */
add_action( 'wp_head', function () {
	if ( is_admin() || is_feed() || function_exists( 'rank_math' ) || defined( 'WPSEO_VERSION' ) ) {
		return;
	}
	$text = '';
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post && 'product' === $post->post_type && function_exists( 'wc_get_product' ) ) {
			$p    = wc_get_product( $post->ID );
			$text = $p ? $p->get_short_description() : '';
		}
		if ( ! $text && $post ) {
			$text = has_excerpt( $post ) ? get_the_excerpt( $post ) : '';
		}
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$text = term_description();
	}
	$text = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $text ) ) );
	if ( '' === $text ) {
		$text = trim( (string) get_bloginfo( 'description' ) );
	}
	if ( '' === $text ) {
		$text = 'Layer One – 3D printed home and desk pieces and Flexi Crew collectible figures, printed to order in NCR and delivered across India.';
	}
	if ( strlen( $text ) > 158 ) {
		$text = rtrim( mb_substr( $text, 0, 155 ) ) . '…';
	}
	echo '<meta name="description" content="' . esc_attr( $text ) . '">' . "\n";
}, 3 );

/* ' srcset="…" sizes="…"' for an image shown small, so phones fetch a small file instead of the 1024 px one. */
function layerone_srcset_attrs( $attachment_id, $sizes ) {
	$set = $attachment_id ? wp_get_attachment_image_srcset( (int) $attachment_id, 'large' ) : '';
	return $set ? ' srcset="' . esc_attr( $set ) . '" sizes="' . esc_attr( $sizes ) . '"' : '';
}

/* No tab icon uploaded: use the Layer One mark so the browser does not ask for /favicon.ico (which does not exist). */
add_action( 'wp_head', function () {
	if ( has_site_icon() ) {
		return;
	}
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40"><path d="M20 4 35 12.5 20 21 5 12.5z" fill="#6B7A4F"/><path d="M5 12.5 20 21v15L5 27.5z" fill="#4A5E3A"/><path d="M35 12.5 20 21v15l15-8.5z" fill="#2A3520"/></svg>';
	echo '<link rel="icon" href="data:image/svg+xml,' . rawurlencode( $svg ) . '">' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
}, 2 );
