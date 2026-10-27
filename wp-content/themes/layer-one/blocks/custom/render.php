<?php
/**
 * Custom & corporate block, front end.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$img = function ( $id, $size = 'large' ) {
	return $id ? wp_get_attachment_image_url( (int) $id, $size ) : '';
};
$wa       = preg_replace( '/\D/', '', layerone_opt( 'lo_whatsapp' ) );
$default  = layerone_cat_url( 'custom-corporate' );
$link_url = $attributes['linkUrl'] ? $attributes['linkUrl'] : $default;
$deal     = (array) $attributes['deal'];
$figc     = (array) $attributes['figc'];
$forme    = (array) $attributes['forme'];
$deal_wa  = $wa ? 'https://wa.me/' . $wa . '?text=' . rawurlencode( isset( $deal['waMessage'] ) ? $deal['waMessage'] : '' ) : '';
?>
<section class="l2-sec wrap" id="custom">
  <div class="l2-sh">
    <div>
      <?php if ( $attributes['kicker'] ) : ?><span class="l2-k"><?php echo esc_html( $attributes['kicker'] ); ?></span><?php endif; ?>
      <h2><?php echo esc_html( $attributes['heading'] ); ?><?php if ( $attributes['headingAccent'] ) : ?> <span class="serif"><?php echo esc_html( $attributes['headingAccent'] ); ?></span><?php endif; ?></h2>
    </div>
    <?php if ( $attributes['linkLabel'] ) : ?><a href="<?php echo esc_url( $link_url ); ?>" class="more"><?php echo esc_html( $attributes['linkLabel'] ); ?></a><?php endif; ?>
  </div>

  <?php if ( ! empty( $attributes['showcase'] ) ) : ?>
  <div class="cu-show" aria-label="Examples of personal figurines and corporate gifts">
    <?php
    foreach ( $attributes['showcase'] as $s ) :
    	$src = $img( isset( $s['imageId'] ) ? $s['imageId'] : 0, 'medium_large' );
    	if ( ! $src ) {
    		continue;
    	}
    	$corp = isset( $s['kind'] ) && 'Corporate' === $s['kind'];
    	?>
    <a class="cu-sc<?php echo $corp ? ' co' : ''; ?>" href="<?php echo esc_url( ! empty( $s['url'] ) ? $s['url'] : $default ); ?>"><img src="<?php echo esc_url( $src ); ?>" alt="<?php echo esc_attr( $s['name'] . ', an example made by LayerOne' ); ?>" loading="lazy" decoding="async"><span><i><?php echo esc_html( $corp ? 'Corporate' : 'Personal' ); ?></i><?php echo esc_html( $s['name'] ); ?></span></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if ( ! empty( $deal['title'] ) ) : ?>
  <div class="deal">
    <div class="t">
      <?php if ( ! empty( $deal['kicker'] ) ) : ?><div class="k"><?php echo esc_html( $deal['kicker'] ); ?></div><?php endif; ?>
      <h2><?php echo wp_kses( nl2br( esc_html( $deal['title'] ) ), array( 'br' => array() ) ); ?></h2>
      <?php if ( ! empty( $deal['text'] ) ) : ?><p><?php echo esc_html( $deal['text'] ); ?></p><?php endif; ?>
      <?php if ( ! empty( $deal['stats'] ) ) : ?>
      <div class="cd stats"><?php foreach ( $deal['stats'] as $s ) : ?><div><b><?php echo esc_html( $s['value'] ); ?></b><span><?php echo esc_html( $s['label'] ); ?></span></div><?php endforeach; ?></div>
      <?php endif; ?>
      <?php if ( ! empty( $deal['steps'] ) ) : ?>
      <div class="steps"><?php foreach ( $deal['steps'] as $s ) : ?><span><?php echo esc_html( $s['text'] ); ?></span><?php endforeach; ?></div>
      <?php endif; ?>
      <?php if ( $deal_wa && ! empty( $deal['ctaLabel'] ) ) : ?><a href="<?php echo esc_url( $deal_wa ); ?>" target="_blank" rel="noopener" class="btn"><?php echo esc_html( $deal['ctaLabel'] ); ?></a><?php endif; ?>
      <?php if ( ! empty( $deal['moreLabel'] ) ) : ?><a href="<?php echo esc_url( ! empty( $deal['moreUrl'] ) ? $deal['moreUrl'] : $default ); ?>" class="deal-more"><?php echo esc_html( $deal['moreLabel'] ); ?></a><?php endif; ?>
    </div>
    <div class="im">
      <?php
      foreach ( array_values( (array) ( isset( $deal['images'] ) ? $deal['images'] : array() ) ) as $k => $id ) {
      	$src = $img( $id );
      	if ( $src ) {
      		printf( '<img src="%s" %salt="" loading="lazy" decoding="async">', esc_url( $src ), 1 === $k ? 'class="mid" ' : '' );
      	}
      }
      ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if ( ! empty( $figc['title'] ) ) : ?>
  <div class="figc">
    <?php $src = $img( isset( $figc['imageId'] ) ? $figc['imageId'] : 0 ); ?>
    <?php if ( $src ) : ?><div class="figc-art"><img src="<?php echo esc_url( $src ); ?>" sizes="(max-width:600px) 100vw, 240px" alt="" loading="lazy" decoding="async"></div><?php endif; ?>
    <div class="figc-txt">
      <span class="figc-k"><?php echo ! empty( $figc['badge'] ) ? '<i>' . esc_html( $figc['badge'] ) . '</i> ' : ''; ?><?php echo esc_html( isset( $figc['kicker'] ) ? $figc['kicker'] : '' ); ?></span>
      <h3><?php echo esc_html( $figc['title'] ); ?></h3>
      <?php if ( ! empty( $figc['text'] ) ) : ?><p><?php echo esc_html( $figc['text'] ); ?></p><?php endif; ?>
      <?php if ( ! empty( $figc['tags'] ) ) : ?><div class="figc-tags"><?php foreach ( $figc['tags'] as $t ) : ?><span><?php echo esc_html( $t['text'] ); ?></span><?php endforeach; ?></div><?php endif; ?>
    </div>
    <div class="figc-go">
      <?php if ( ! empty( $figc['steps'] ) ) : ?><ol><?php foreach ( $figc['steps'] as $t ) : ?><li><?php echo esc_html( $t['text'] ); ?></li><?php endforeach; ?></ol><?php endif; ?>
      <?php if ( ! empty( $figc['btnLabel'] ) ) : ?><a href="<?php echo esc_url( ! empty( $figc['btnUrl'] ) ? $figc['btnUrl'] : $default ); ?>" class="btn btn-dark btn-sm"><?php echo esc_html( $figc['btnLabel'] ); ?></a><?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if ( ! empty( $forme['title'] ) ) : ?>
  <div class="forme">
    <div><h3><?php echo esc_html( $forme['title'] ); ?></h3><?php if ( ! empty( $forme['text'] ) ) : ?><p><?php echo esc_html( $forme['text'] ); ?></p><?php endif; ?></div>
    <?php if ( ! empty( $forme['items'] ) ) : ?>
    <ul><?php foreach ( $forme['items'] as $t ) : ?><li><span><?php echo esc_html( $t['label'] ); ?></span><b><?php echo esc_html( $t['price'] ); ?></b></li><?php endforeach; ?></ul>
    <?php endif; ?>
    <?php if ( ! empty( $forme['btnLabel'] ) ) : ?><a href="<?php echo esc_url( ! empty( $forme['btnUrl'] ) ? $forme['btnUrl'] : $default ); ?>" class="btn btn-dark btn-sm"><?php echo esc_html( $forme['btnLabel'] ); ?></a><?php endif; ?>
  </div>
  <?php endif; ?>
</section>
