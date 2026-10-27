<?php
/**
 * How it's made block, front end.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) || empty( $attributes['steps'] ) ) {
	return;
}
?>
<section class="l2-sec wrap" id="how">
  <div class="l2-sh"><div>
    <?php if ( $attributes['kicker'] ) : ?><span class="l2-k"><?php echo esc_html( $attributes['kicker'] ); ?></span><?php endif; ?>
    <h2><?php echo esc_html( $attributes['heading'] ); ?><?php if ( $attributes['headingAccent'] ) : ?> <span class="serif"><?php echo esc_html( $attributes['headingAccent'] ); ?></span><?php endif; ?></h2>
  </div></div>
  <ol class="l2-how">
    <?php
    foreach ( $attributes['steps'] as $s ) :
    	$src = ! empty( $s['imageId'] ) ? wp_get_attachment_image_url( (int) $s['imageId'], 'large' ) : '';
    	?>
    <li><?php if ( $src ) : ?><img src="<?php echo esc_url( $src ); ?>" alt="<?php echo esc_attr( isset( $s['alt'] ) ? $s['alt'] : '' ); ?>" loading="lazy"><?php endif; ?><b><?php echo esc_html( $s['title'] ); ?></b><p><?php echo esc_html( isset( $s['text'] ) ? $s['text'] : '' ); ?></p></li>
    <?php endforeach; ?>
  </ol>
</section>
