<?php
/**
 * Category tiles block, front end.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'wc_get_products' ) ) {
	return;
}

$tiles = array();
foreach ( (array) $attributes['tiles'] as $t ) {
	$term = ! empty( $t['categoryId'] ) ? get_term( (int) $t['categoryId'], 'product_cat' ) : null;
	$term = $term && ! is_wp_error( $term ) ? $term : null;

	$title = ! empty( $t['title'] ) ? $t['title'] : ( $term ? $term->name : '' );
	if ( '' === $title ) {
		continue;
	}

	$url = ! empty( $t['url'] ) ? $t['url'] : ( $term ? get_term_link( $term ) : layerone_shop_url() );

	/* "from ₹X": lowest price among the category's products, unless typed by hand. */
	$from = '';
	if ( ! empty( $t['priceText'] ) ) {
		$from = $t['priceText'];
	} elseif ( $term ) {
		$min = null;
		foreach ( wc_get_products( array( 'status' => 'publish', 'limit' => -1, 'category' => array( $term->slug ) ) ) as $p ) {
			$price = (float) $p->get_price();
			if ( '' !== $p->get_price() && ( null === $min || $price < $min ) ) {
				$min = $price;
			}
		}
		$from = null !== $min ? 'from ' . layerone_plain_price( $min ) : '';
	}

	$tiles[] = array(
		'title' => $title,
		'url'   => is_wp_error( $url ) ? layerone_shop_url() : $url,
		'from'  => $from,
		'img'   => ! empty( $t['imageId'] ) ? wp_get_attachment_image_url( (int) $t['imageId'], 'large' ) : '',
		'set'   => ! empty( $t['imageId'] ) ? layerone_srcset_attrs( (int) $t['imageId'], ! empty( $t['wide'] ) ? '(max-width:700px) 92vw, 640px' : '(max-width:700px) 92vw, 330px' ) : '',
		'wide'  => ! empty( $t['wide'] ),
	);
}

if ( ! $tiles ) {
	if ( current_user_can( 'edit_posts' ) ) {
		echo '<p style="padding:24px;text-align:center">Category tiles: add at least one tile in the editor.</p>';
	}
	return;
}

$link_url = $attributes['linkUrl'] ? $attributes['linkUrl'] : layerone_cat_url( 'home-desk' );
?>
<section class="l2-sec wrap" id="home-desk">
  <div class="l2-sh">
    <div>
      <?php if ( $attributes['kicker'] ) : ?><span class="l2-k"><?php echo esc_html( $attributes['kicker'] ); ?></span><?php endif; ?>
      <h2><?php echo esc_html( $attributes['heading'] ); ?><?php if ( $attributes['headingAccent'] ) : ?> <span class="serif"><?php echo esc_html( $attributes['headingAccent'] ); ?></span><?php endif; ?></h2>
    </div>
    <?php if ( $attributes['linkLabel'] ) : ?><a href="<?php echo esc_url( $link_url ); ?>" class="more"><?php echo esc_html( $attributes['linkLabel'] ); ?></a><?php endif; ?>
  </div>
  <div class="decor hd7">
    <?php foreach ( $tiles as $k => $t ) : ?>
    <a href="<?php echo esc_url( $t['url'] ); ?>" class="dt photo<?php echo $t['wide'] ? ' wide' : ''; ?>" style="--c:#141414;animation-delay:<?php echo esc_attr( $k * 0.07 ); ?>s">
      <div class="pic"><?php if ( $t['img'] ) : ?><img src="<?php echo esc_url( $t['img'] ); ?>"<?php echo $t['set']; // phpcs:ignore WordPress.Security.EscapeOutput ?> alt="" loading="lazy" decoding="async"><?php endif; ?></div>
      <div class="top"><div><small>Category</small><h3><?php echo esc_html( $t['title'] ); ?></h3></div></div>
      <div class="bot"><div class="dp"><?php echo esc_html( $t['from'] ); ?></div><span class="vw">View <i>→</i></span></div>
    </a>
    <?php endforeach; ?>
  </div>
</section>
