<?php
/**
 * Fonts: the public site uses the theme fonts (see functions.php). The admin uses WordPress's own fonts.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function layerone_fonts_url() {
	return get_template_directory_uri() . '/assets/css/fonts.css?ver=' . LAYERONE_VERSION;
}

/* wp-admin, the editor, the Customizer and the login screen keep the fonts WordPress ships with. */

/* Emails and other front-end forms inherit the site font. */
add_action( 'wp_head', function () {
	echo '<style id="layerone-form-font">input,select,textarea,button{font-family:var(--f,inherit)}</style>' . "\n";
}, 99 );
