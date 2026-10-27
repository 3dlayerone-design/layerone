<?php
/**
 * Collectibles section tabs.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'layerone_flexi_section_start' ) ) {
	return;
}
layerone_flexi_section_start( false );
?>
<div class="lo-flexi">
<nav class="v2-tabs" id="v2tabs" aria-label="On this page">
  <div class="wrap v2-tabs-in">
    <div class="v2-tablinks">
      <?php foreach ( (array) $attributes['links'] as $l ) : ?>
      <a href="#<?php echo esc_attr( sanitize_title( isset( $l['anchor'] ) ? $l['anchor'] : '' ) ); ?>"><?php echo esc_html( layerone_flexi_ph( isset( $l['label'] ) ? $l['label'] : '' ) ); ?></a>
      <?php endforeach; ?>
    </div>
    <a href="#box" class="v2-tabbox" id="tabBox" hidden><span class="v2-tabthumbs" id="tabThumbs"></span><b id="tabCount">0 / 12</b></a>
  </div>
</nav>
</div>
