<?php
/**
 * Collectibles hero block. The wall of figures is drawn by the shared app script.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'layerone_flexi_section_start' ) ) {
	return;
}
$a = $attributes;
layerone_flexi_section_start();
?>
<div class="lo-flexi">
<section class="fx-hero has-show v2-hero" id="top">
  <div class="wrap fx-hero-in">
    <div class="fx-hero-copy">
      <span class="eyebrow"><?php echo $a['badge'] ? '<i>' . esc_html( $a['badge'] ) . '</i> ' : ''; ?><?php echo esc_html( layerone_flexi_ph( $a['eyebrow'] ) ); ?></span>
      <h1><?php echo layerone_flexi_heading( $a['heading'], $a['headingAccent'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
      <p class="sub"><?php echo esc_html( $a['intro'] ); ?></p>
      <?php if ( $a['fromText'] ) : ?><p class="v2-from"><?php echo wp_kses( layerone_flexi_ph( $a['fromText'] ), array( 'b' => array(), 'strong' => array() ) ); ?></p><?php endif; ?>
      <div class="cta">
        <?php if ( $a['btn1'] ) : ?><a href="#all" class="btn btn-dark"><?php echo esc_html( layerone_flexi_ph( $a['btn1'] ) ); ?></a><?php endif; ?>
        <?php if ( $a['btn2'] ) : ?><a href="#bundles" class="btn btn-line"><?php echo esc_html( layerone_flexi_ph( $a['btn2'] ) ); ?></a><?php endif; ?>
      </div>
    </div>
    <div class="fx-show" id="heroShow" data-tag="<?php echo esc_attr( sanitize_title( $a['wallTag'] ) ); ?>" data-ids="<?php echo esc_attr( wp_json_encode( array_values( array_filter( array_map( 'intval', (array) $a['wallProducts'] ) ) ) ) ); ?>"></div>
  </div>
</section>
</div>
