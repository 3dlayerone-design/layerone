<?php
/**
 * Collectibles "how it works" row.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) || empty( $attributes['steps'] ) ) {
	return;
}
?>
<div class="lo-flexi"><div class="wrap">
  <section class="v2-how" aria-label="How it works">
    <?php foreach ( $attributes['steps'] as $i => $s ) : ?>
    <div><b><?php echo (int) $i + 1; ?></b><p><strong><?php echo esc_html( $s['title'] ); ?></strong> <?php echo esc_html( isset( $s['text'] ) ? $s['text'] : '' ); ?></p></div>
    <?php endforeach; ?>
  </section>
</div></div>
