<?php
/**
 * Prints menu items as bare <a> tags, the way the Layer One header and drawer expect.
 * Add the CSS class "hot" to a menu item to highlight it.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Footer columns: the same bare links, each wrapped in an <li>.
 */
class Layerone_List_Walker extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {}

	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$target = $item->target ? ' target="' . esc_attr( $item->target ) . '" rel="noopener"' : '';
		$output .= '<li><a href="' . esc_url( $item->url ) . '"' . $target . '>' . esc_html( $item->title ) . '</a></li>';
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}

class Layerone_Flat_Walker extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {}

	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = array_filter( (array) $item->classes, function ( $c ) {
			return in_array( $c, array( 'hot', 'all' ), true );
		} );
		if ( $item->current || $item->current_item_ancestor ) {
			$classes[] = 'current';
		}

		$attrs  = ' href="' . esc_url( $item->url ) . '"';
		$attrs .= $classes ? ' class="' . esc_attr( implode( ' ', $classes ) ) . '"' : '';
		$attrs .= $item->target ? ' target="' . esc_attr( $item->target ) . '" rel="noopener"' : '';

		$output .= '<a' . $attrs . '>' . esc_html( $item->title ) . '</a>';
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}
