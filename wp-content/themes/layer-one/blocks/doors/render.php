<?php
/**
 * Three doors block, front end.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$doors = array_filter( (array) $attributes['doors'], function ( $d ) {
	return ! empty( $d['title'] );
} );
if ( ! $doors ) {
	if ( current_user_can( 'edit_posts' ) ) {
		echo '<p style="padding:24px;text-align:center">Three doors: add at least one card in the editor.</p>';
	}
	return;
}
$classes = array( 'd-fx', 'd-hd', 'd-cu' );
?>
<section class="l2-hero wrap">
  <div class="l2-intro">
    <h1><?php echo esc_html( $attributes['heading'] ); ?> <?php if ( $attributes['headingAccent'] ) : ?><span class="serif"><?php echo esc_html( $attributes['headingAccent'] ); ?></span><?php endif; ?></h1>
    <?php if ( $attributes['intro'] ) : ?><p><?php echo esc_html( $attributes['intro'] ); ?></p><?php endif; ?>
  </div>
  <div class="l2-doors">
    <?php
    $n = 0;
    foreach ( $doors as $d ) :
    	$url = ! empty( $d['url'] ) ? $d['url'] : layerone_shop_url();
    	?>
    <a class="door <?php echo esc_attr( $classes[ $n % 3 ] ); ?>" href="<?php echo esc_url( $url ); ?>">
      <div class="door-media" data-imgs="1">
        <?php
        $media = array_values( array_filter( (array) ( isset( $d['images'] ) ? $d['images'] : array() ) ) );
        foreach ( $media as $i => $media_id ) {
        	$media_id = (int) $media_id;
        	if ( wp_attachment_is( 'video', $media_id ) ) {
        		$video_url = wp_get_attachment_url( $media_id );
        		if ( ! $video_url ) {
        			continue;
        		}
        		$poster = get_the_post_thumbnail_url( $media_id, 'large' );
        		printf(
        			'<video class="%s" src="%s" muted playsinline preload="%s"%s%s></video>',
        			0 === $i ? 'on' : '',
        			esc_url( $video_url ),
        			0 === $i ? 'auto' : 'metadata',
        			$poster ? ' poster="' . esc_url( $poster ) . '"' : '',
        			1 === count( $media ) ? ' autoplay loop' : ''
        		);
        		continue;
        	}
        	$src = wp_get_attachment_image_url( $media_id, 'large' );
        	if ( ! $src ) {
        		continue;
        	}
        	printf(
        		'<img src="%s" alt="" class="%s" %s decoding="async">',
        		esc_url( $src ),
        		0 === $i ? 'on' : '',
        		0 === $i ? 'fetchpriority="high"' : 'loading="lazy"'
        	);
        }
        ?>
      </div>
      <div class="door-txt">
        <?php if ( ! empty( $d['kicker'] ) ) : ?><small><?php echo esc_html( $d['kicker'] ); ?></small><?php endif; ?>
        <h2><?php echo esc_html( $d['title'] ); ?></h2>
        <?php if ( ! empty( $d['text'] ) ) : ?><p><?php echo esc_html( $d['text'] ); ?></p><?php endif; ?>
        <span class="door-go"><span class="dg-t"><?php echo esc_html( ! empty( $d['label'] ) ? $d['label'] : 'Shop now' ); ?></span><i>→</i></span>
      </div>
    </a>
    	<?php
    	$n++;
    endforeach;
    ?>
  </div>
  <?php if ( ! empty( $attributes['trust'] ) ) : ?>
  <ul class="l2-trust">
    <?php foreach ( $attributes['trust'] as $t ) : ?>
    <li><b><?php echo esc_html( isset( $t['bold'] ) ? $t['bold'] : '' ); ?></b> <?php echo esc_html( isset( $t['text'] ) ? $t['text'] : '' ); ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</section>
