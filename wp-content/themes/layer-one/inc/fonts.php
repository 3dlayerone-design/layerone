<?php
/**
 * Layer One fonts everywhere: the public site (see functions.php), wp-admin, the block editor,
 * the Customizer and the login screen all use the same two Google fonts.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function layerone_fonts_url() {
	return 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap';
}

function layerone_enqueue_admin_fonts() {
	wp_enqueue_style( 'layerone-fonts', layerone_fonts_url(), array(), null );
	wp_enqueue_style( 'layerone-admin-fonts', get_template_directory_uri() . '/assets/css/admin-fonts.css', array( 'layerone-fonts' ), LAYERONE_VERSION );
}

/* wp-admin screens, including the block editor page and its sidebar. */
add_action( 'admin_enqueue_scripts', 'layerone_enqueue_admin_fonts' );
/* Login, register and password reset screens. */
add_action( 'login_enqueue_scripts', 'layerone_enqueue_admin_fonts' );
/* Customizer controls panel. */
add_action( 'customize_controls_enqueue_scripts', 'layerone_enqueue_admin_fonts' );
/* Customizer preview and the editor canvas (an iframe) get the same files through block assets. */
add_action( 'enqueue_block_assets', function () {
	if ( ! is_admin() ) {
		return;
	}
	wp_enqueue_style( 'layerone-fonts', layerone_fonts_url(), array(), null );
	wp_enqueue_style( 'layerone-admin-fonts', get_template_directory_uri() . '/assets/css/admin-fonts.css', array( 'layerone-fonts' ), LAYERONE_VERSION );
} );

/* Emails and other front-end forms inherit the site font. */
add_action( 'wp_head', function () {
	echo '<style id="layerone-form-font">input,select,textarea,button{font-family:var(--f,inherit)}</style>' . "\n";
}, 99 );
