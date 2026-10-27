<?php
/**
 * Collectibles box builder, and the phone bar that follows the box. Drawn by the shared app script.
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
  <section class="v2-sec" id="box">
    <div class="v2-builder">
      <div class="v2-bcopy">
        <h2><?php echo layerone_flexi_heading( $a['title'], $a['accent'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
        <?php if ( $a['text'] ) : ?><p><?php echo esc_html( layerone_flexi_ph( $a['text'] ) ); ?></p><?php endif; ?>
        <div class="v2-sizes" id="sizes" role="radiogroup" aria-label="Box size"></div>
        <div class="v2-total"><span id="boxLine"></span><b id="boxPrice"></b></div>
        <div class="v2-bbtns">
          <button type="button" class="btn btn-dark" id="addBoxBtn" disabled>Add box to cart</button>
          <button type="button" class="btn btn-line" id="mysteryBtn">Mystery fill</button>
          <button type="button" class="v2-clear" id="clearBtn">Clear box</button>
        </div>
      </div>
      <div class="v2-crate" id="crate" aria-live="polite"></div>
    </div>
  </section>
</div>

<div class="v2-boxbar" id="boxBar" role="region" aria-label="Your box">
  <a href="#box" class="bb-count" aria-label="Go to your box"><span class="bb-thumbs" id="bbThumbs"></span><span class="bb-txt"><b id="bbN">0 / 12</b><small id="bbS">figures picked</small></span></a>
  <button type="button" class="btn btn-dark bb-add" id="bbAdd">Pick 12 more</button>
</div></div>
