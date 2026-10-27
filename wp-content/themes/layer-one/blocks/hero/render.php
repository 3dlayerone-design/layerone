<?php
/**
 * Hero spotlight block, front end.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'wc_get_product' ) ) {
	return;
}

/* Tints and glow per slide, taken from the design (cycled). */
$tints = array(
	array( array( '#e4f1ff', '#fff3e0', '#eef6f3' ), 'rgba(61,123,255,.30)' ),
	array( array( '#ece6ff', '#f3eefe', '#eef0f7' ), 'rgba(106,76,255,.32)' ),
	array( array( '#eef0f2', '#f6f3ee', '#eef3f2' ), 'rgba(10,77,67,.28)' ),
	array( array( '#fff0e2', '#ffe8e0', '#f6efe9' ), 'rgba(255,120,60,.36)' ),
	array( array( '#ffe9f0', '#f1ecff', '#eef4f7' ), 'rgba(255,92,147,.32)' ),
);

$wa     = preg_replace( '/\D/', '', layerone_opt( 'lo_whatsapp' ) );
$slides = array();

foreach ( (array) $attributes['slides'] as $i => $s ) {
	$product = ! empty( $s['productId'] ) ? wc_get_product( (int) $s['productId'] ) : false;
	if ( ! $product || 'publish' !== $product->get_status() ) {
		continue;
	}

	$k     = count( $slides );
	$img_id = $product->get_image_id();
	$cats  = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'all' ) );
	$cat   = $cats ? $cats[0]->name : '';
	/* Chips come from the "Features" attribute (falls back to "Material"); attribute values are joined with " | ". */
	$raw   = $product->get_attribute( 'features' ) ?: $product->get_attribute( 'material' );
	$chips = array_values( array_unique( array_filter( array_map( 'trim', preg_split( '/\s*\|\s*|\s*,\s*/', $raw ) ) ) ) );

	$on_sale = $product->is_on_sale();
	$more    = ! empty( $s['moreUrl'] ) ? $s['moreUrl'] : ( $cats ? get_term_link( $cats[0] ) : layerone_shop_url() );

	$slides[] = array(
		'id'     => $product->get_id(),
		'name'   => $product->get_name(),
		'cat'    => $cat,
		'kicker' => ! empty( $s['kicker'] ) ? $s['kicker'] : $cat,
		'desc'   => ! empty( $s['description'] ) ? $s['description'] : wp_strip_all_tags( $product->get_short_description() ),
		'price'  => layerone_plain_price( (float) $product->get_price() ),
		'old'    => $on_sale ? layerone_plain_price( (float) $product->get_regular_price() ) : '',
		'unit'   => ! empty( $s['unit'] ) ? trim( $s['unit'] ) : '',
		'chips'  => array_values( $chips ),
		'img'    => $img_id ? wp_get_attachment_image_url( $img_id, 'large' ) : '',
		'imgS'   => $img_id ? wp_get_attachment_image_url( $img_id, 'medium_large' ) : '',
		'thumb'  => $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '',
		'moreL'  => ! empty( $s['moreLabel'] ) ? $s['moreLabel'] : 'Shop now',
		'moreU'  => is_wp_error( $more ) ? layerone_shop_url() : $more,
		'quote'  => ! empty( $s['quote'] ) && $wa ? 'https://wa.me/' . $wa . '?text=' . rawurlencode( $s['quote'] ) : '',
		'bg'     => ! empty( $s['bgColor'] ) ? (string) sanitize_hex_color( $s['bgColor'] ) : '',
		'lt'     => $tints[ $k % count( $tints ) ][0],
		'glow'   => $tints[ $k % count( $tints ) ][1],
	);
}

if ( ! $slides ) {
	if ( current_user_can( 'edit_posts' ) ) {
		echo '<p class="wp-block-layerone-hero-empty" style="padding:24px;text-align:center">Hero spotlight: add at least one product slide in the editor.</p>';
	}
	return;
}

$f   = $slides[0];
$cfg = array(
	'slides' => $slides,
	'auto'   => ! empty( $attributes['autoplay'] ),
	'ms'     => max( 2, (int) $attributes['duration'] ) * 1000,
);
$quote = '' !== $f['quote'];
?>
<section class="hero l2-spot" aria-label="Spotlight" data-hero="<?php echo esc_attr( wp_json_encode( $cfg ) ); ?>">
  <div class="main<?php echo $f['bg'] ? ' has-bg' : ''; ?>" style="<?php echo $f['bg'] ? esc_attr( '--hero-bg:' . $f['bg'] . ';' ) : ''; ?>--t1:<?php echo esc_attr( $f['lt'][0] ); ?>;--t2:<?php echo esc_attr( $f['lt'][1] ); ?>;--t3:<?php echo esc_attr( $f['lt'][2] ); ?>">
    <div class="copy l2-spot-copy" id="spCopy">
      <span class="l2-k" id="spK"><?php echo esc_html( $f['kicker'] ); ?></span>
      <h2 id="spH"><?php echo esc_html( $f['name'] ); ?></h2>
      <p class="sub" id="spP"><?php echo esc_html( $f['desc'] ); ?></p>
      <div class="sp-price" id="spPr"><b><?php echo esc_html( $f['price'] ); ?></b><?php echo $f['unit'] ? '<span>' . esc_html( $f['unit'] ) . '</span>' : ''; ?><?php echo $f['old'] ? '<s>' . esc_html( $f['old'] ) . '</s>' : ''; ?></div>
      <div class="cta">
        <button type="button" class="btn btn-dark" id="spBuy" data-id="<?php echo (int) $f['id']; ?>"<?php echo $quote ? ' hidden' : ''; ?>>Add to cart</button>
        <a class="btn btn-dark" id="spQuote" href="<?php echo esc_url( $f['quote'] ); ?>" target="_blank" rel="noopener"<?php echo $quote ? '' : ' hidden'; ?>>Get a quote on WhatsApp</a>
        <a class="btn btn-line" id="spMore" href="<?php echo esc_url( $f['moreU'] ); ?>"><?php echo esc_html( $f['moreL'] ); ?></a>
      </div>
      <div class="sp-bar" aria-hidden="true"><i id="spBarI"></i></div>
    </div>
    <div class="viewer" id="viewer" style="--glow:<?php echo esc_attr( $f['glow'] ); ?>">
      <div class="orbit"></div><div class="orbit o2"></div><div class="disc"></div>
      <div id="sparks"></div>
      <div class="gs"></div>
      <div class="fig"><img id="heroImg" class="himg in" src="<?php echo esc_url( $f['img'] ); ?>" alt="<?php echo esc_attr( $f['name'] ); ?>" fetchpriority="high"></div>
      <div class="chip a"><svg class="i" viewBox="0 0 24 24"><path d="M12 3 4 7v10l8 4 8-4V7z"/><path d="m4 7 8 4 8-4M12 11v10"/></svg><b>Printed to order</b></div>
      <div class="chip c" id="fc"><?php echo implode( ' · ', array_map( function ( $c ) {
      	return '<b>' . esc_html( $c ) . '</b>';
      }, $f['chips'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
      <div class="chip b"><div class="th" id="heroTh" style="background-image:url(<?php echo esc_url( $f['thumb'] ); ?>)"></div><div><b id="hn"><?php echo esc_html( $f['name'] ); ?></b><br><span id="hc"><?php echo esc_html( $f['cat'] ); ?></span></div><span class="p" id="hp"><?php echo esc_html( $f['price'] ); ?></span><button type="button" id="hb" data-id="<?php echo (int) $f['id']; ?>"<?php echo $quote ? ' hidden' : ''; ?>>Add</button><a id="hq" class="hq" href="<?php echo esc_url( $f['quote'] ); ?>" target="_blank" rel="noopener"<?php echo $quote ? '' : ' hidden'; ?>>Quote</a></div>
      <div class="pick" id="pick">
        <?php foreach ( $slides as $k => $s ) : ?>
        <button type="button" data-k="<?php echo (int) $k; ?>" class="<?php echo 0 === $k ? 'on' : ''; ?>" aria-label="<?php echo esc_attr( $s['name'] ); ?>"><img src="<?php echo esc_url( $s['thumb'] ); ?>" alt=""></button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
