<?php
/**
 * Collectibles bundle cards. Cards are drawn by the shared app script.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'layerone_flexi_section_start' ) ) {
	return;
}
$a = $attributes;
layerone_flexi_section_start();
?>
<div class="lo-flexi"><div class="wrap">
  <section class="v2-sec" id="bundles" data-ids="<?php echo esc_attr( wp_json_encode( array_values( array_filter( array_map( 'intval', (array) $a['bundleProducts'] ) ) ) ) ); ?>">
    <div class="sh"><div><h2><?php echo layerone_flexi_heading( $a['title'], $a['accent'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2><?php if ( $a['text'] ) : ?><p><?php echo esc_html( layerone_flexi_ph( $a['text'] ) ); ?></p><?php endif; ?></div></div>
    <div class="v2-bento" id="bento"></div>
  </section>
</div></div>
