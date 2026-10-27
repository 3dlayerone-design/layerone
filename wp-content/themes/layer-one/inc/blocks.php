<?php
/**
 * Native Gutenberg blocks (no plugin). Each block lives in blocks/<name>/ with block.json.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function layerone_register_blocks() {
	/* The landing page CSS from the design; loaded only on pages that use one of the blocks. */
	wp_register_style( 'layerone-landing2', get_template_directory_uri() . '/assets/css/landing2.css', array( 'layerone-type' ), LAYERONE_VERSION );

	/* Collectibles page styles from the design. */
	wp_register_style( 'layerone-flexi', get_template_directory_uri() . '/assets/css/flexi.css', array( 'layerone-type' ), LAYERONE_VERSION );
	wp_register_style( 'layerone-flexi2', get_template_directory_uri() . '/assets/css/flexi2.css', array( 'layerone-flexi' ), LAYERONE_VERSION );

	/* One script runs every section of the Collectibles page (hero, most loved, bundles, all figures, box). */
	wp_register_script( 'layerone-flexi-app', get_template_directory_uri() . '/assets/js/flexi-app.js', array(), LAYERONE_VERSION, true );

	/* Small helper library shared by the block editors. */
	wp_register_script(
		'layerone-editor-utils',
		get_template_directory_uri() . '/assets/js/editor-utils.js',
		array( 'wp-element', 'wp-components', 'wp-block-editor', 'wp-data', 'wp-core-data', 'wp-api-fetch' ),
		LAYERONE_VERSION,
		true
	);

	foreach ( array( 'hero', 'doors', 'categories', 'flexi', 'custom', 'steps', 'newsletter', 'flexi-hero', 'flexi-tabs', 'flexi-loved', 'flexi-bundles', 'flexi-all', 'flexi-box', 'flexi-how' ) as $block ) {
		register_block_type( get_template_directory() . '/blocks/' . $block );
	}
}
add_action( 'init', 'layerone_register_blocks' );

/* Own group in the block inserter ("Layer One"), shown first. */
function layerone_block_category( $categories ) {
	array_unshift( $categories, array(
		'slug'  => 'layer-one',
		'title' => __( 'Layer One', 'layer-one' ),
		'icon'  => null,
	) );
	return $categories;
}
add_filter( 'block_categories_all', 'layerone_block_category' );

/* Editor-only styles for the accordion panels in the block editors (the canvas is an iframe, so use block assets). */
add_action( 'enqueue_block_assets', function () {
	if ( is_admin() ) {
		wp_enqueue_style( 'layerone-editor-blocks', get_template_directory_uri() . '/assets/css/editor-blocks.css', array(), LAYERONE_VERSION );
	}
} );
