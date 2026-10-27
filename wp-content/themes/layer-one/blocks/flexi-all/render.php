<?php
/**
 * Collectibles all figures. Chips and grid are drawn by the shared app script.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'layerone_flexi_section_start' ) ) {
	return;
}
$a    = $attributes;
$text = layerone_flexi_ph( $a['text'] );
layerone_flexi_section_start();
?>
<div class="lo-flexi"><div class="wrap">
  <section class="v2-sec" id="all">
    <div class="sh"><div><h2><?php echo layerone_flexi_heading( $a['title'], $a['accent'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2><p id="allNote" data-default="<?php echo esc_attr( $text ); ?>"><?php echo esc_html( $text ); ?></p></div></div>
    <div class="v2-chips" id="chips" role="tablist" aria-label="Filter by series"></div>
    <div class="v2-grid" id="allGrid"></div>
  </section>
</div></div>
