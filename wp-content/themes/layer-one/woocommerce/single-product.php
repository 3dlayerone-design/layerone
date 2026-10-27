<?php
/**
 * Single product page: the design's product page.
 * Photos, price, colours, quantity and "Add to cart" are drawn here; assets/js/product.js adds the gallery viewer,
 * the sticky bar and the quantity and colour behaviour.
 *
 * @version WooCommerce 9.x template: single-product.php (overrides the plugin's file)
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$product = wc_get_product( get_the_ID() );
	if ( ! $product ) {
		continue;
	}

	$id      = $product->get_id();
	$name    = wp_specialchars_decode( $product->get_name(), ENT_QUOTES );
	$cols    = layerone_product_colours( $product );
	$known   = layerone_colours();
	$feat    = layerone_product_features( $product );
	$cat     = layerone_product_cat_name( $product );
	$cat_obj = null;
	foreach ( wc_get_product_terms( $id, 'product_cat', array( 'orderby' => 'parent', 'order' => 'DESC' ) ) as $t ) {
		if ( wp_specialchars_decode( $t->name, ENT_QUOTES ) === $cat ) {
			$cat_obj = $t;
			break;
		}
	}
	$simple  = $product->is_type( 'simple' );
	$buyable = $product->is_purchasable() && $product->is_in_stock();
	$free    = (int) layerone_opt( 'lo_free_ship' );

	/* photos: main image first, then the gallery */
	$photos = array();
	foreach ( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) as $pid ) {
		if ( $pid ) {
			$photos[] = (int) $pid;
		}
	}

	/* lead text and description */
	$lead = wp_strip_all_tags( $product->get_short_description() );
	$lead = $lead ? $lead : sprintf( '%s is printed to order in PLA+ on our own print farm in NCR, then finished and checked by hand before it ships.', $name );
	$desc = trim( $product->get_description() );
	$mat  = trim( $product->get_attribute( 'material' ) );

	/* previous / next among the shop's products */
	$list = array();
	foreach ( wc_get_products( array( 'status' => 'publish', 'limit' => -1, 'visibility' => 'catalog', 'orderby' => 'menu_order', 'order' => 'ASC', 'return' => 'ids' ) ) as $pid ) {
		if ( layerone_is_shop_product( $pid ) ) {
			$list[] = $pid;
		}
	}
	/* prev / next skip bundles: those open on the Collectibles page */
	$walk = array_values( array_filter( $list, function ( $pid ) use ( $id ) {
		return $pid === $id || 'bundle' !== layerone_flexi_kind( $pid );
	} ) );
	$at   = array_search( $id, $walk, true );
	$prev = ( false !== $at && count( $walk ) > 1 ) ? $walk[ ( $at - 1 + count( $walk ) ) % count( $walk ) ] : 0;
	$next = ( false !== $at && count( $walk ) > 1 ) ? $walk[ ( $at + 1 ) % count( $walk ) ] : 0;

	/* you may also like */
	$related = array();
	foreach ( $list as $pid ) {
		if ( $pid !== $id ) {
			$related[] = $pid;
		}
	}
	if ( $cat_obj ) {
		usort( $related, function ( $a, $b ) use ( $cat_obj ) {
			return (int) has_term( $cat_obj->term_id, 'product_cat', $b ) - (int) has_term( $cat_obj->term_id, 'product_cat', $a );
		} );
	}
	$related = array_slice( $related, 0, 4 );

	$sku       = $product->get_sku() ? $product->get_sku() : 'LO-' . str_pad( (string) $id, 3, '0', STR_PAD_LEFT );
	$price_now = layerone_plain_price( (float) $product->get_price() );
	$on_sale   = $product->is_on_sale() && $product->get_regular_price();
	$save      = $on_sale ? layerone_plain_price( (float) $product->get_regular_price() - (float) $product->get_price() ) : '';
	$cart_attr = $simple && $buyable ? ' data-add="' . (int) $id . '"' . ( $cols ? ' data-col="' . esc_attr( $cols[0] ) . '"' : '' ) : '';
	?>
<main class="pd">
  <div class="pd-gallery" id="pdGallery" aria-label="Product photos">
    <div class="pd-fab-wrap"><div class="pd-fab">
      <button type="button" id="pdShare" aria-label="Share this product"><svg class="i" viewBox="0 0 24 24"><circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="6" r="2.5"/><circle cx="18" cy="18" r="2.5"/><path d="m8.2 10.8 7.6-3.6M8.2 13.2l7.6 3.6"/></svg></button>
      <button type="button" id="pdWish" data-wish="<?php echo (int) $id; ?>" aria-label="Save to wishlist"><svg class="i" viewBox="0 0 24 24"><path d="M12 20s-7.5-4.6-7.5-10.3A4.2 4.2 0 0 1 12 7.4a4.2 4.2 0 0 1 7.5 2.3C19.5 15.4 12 20 12 20z"/></svg></button>
    </div></div>
    <?php if ( count( $photos ) > 1 ) : ?><span class="pd-count"><?php echo (int) count( $photos ); ?> photos</span><?php endif; ?>
    <?php if ( $photos ) : ?>
      <?php foreach ( $photos as $i => $pid ) : ?>
      <figure><button type="button" class="pd-zoom" data-i="<?php echo (int) $i; ?>" data-full="<?php echo esc_url( wp_get_attachment_image_url( $pid, 'full' ) ); ?>" aria-label="Zoom photo <?php echo (int) $i + 1; ?>"><svg class="i" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5M11 8v6M8 11h6"/></svg></button>
        <?php echo wp_get_attachment_image( $pid, 'large', false, array( 'alt' => $name . ' – photo ' . ( $i + 1 ), 'sizes' => '(max-width:900px) 100vw, 50vw', 'loading' => $i ? 'lazy' : 'eager', 'fetchpriority' => $i ? 'auto' : 'high', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></figure>
      <?php endforeach; ?>
    <?php else : ?>
      <figure><div class="ph"><span>Photo coming</span></div></figure>
    <?php endif; ?>
  </div>

  <section class="pd-info" aria-label="Product details">
    <div class="pd-top">
      <nav class="crumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><a href="<?php echo esc_url( layerone_shop_url() ); ?>">All products</a><span>/</span><b id="crumbName"><?php echo esc_html( $name ); ?></b></nav>
      <div class="pd-nav">
        <?php if ( $prev ) : ?><a id="pdPrev" href="<?php echo esc_url( get_permalink( $prev ) ); ?>">Prev</a><?php endif; ?>
        <?php if ( $next ) : ?><a id="pdNext" href="<?php echo esc_url( get_permalink( $next ) ); ?>">Next</a><?php endif; ?>
      </div>
    </div>

    <h1 id="pdTitle"><?php echo esc_html( $name ); ?></h1>
    <p class="pd-lead" id="pdLead"><?php echo esc_html( $lead ); ?></p>
    <div class="pd-pricerow"><div class="pd-price" id="pdPrice"><?php echo esc_html( $price_now ); ?><?php echo $on_sale ? '<s>' . esc_html( layerone_plain_price( (float) $product->get_regular_price() ) ) . '</s><em>Save ' . esc_html( $save ) . '</em>' : ''; ?></div><button type="button" class="pd-share" id="pdShare2" aria-label="Share this product"><svg class="i" viewBox="0 0 24 24"><path d="M14 4l7 6-7 6v-4c-6 0-9 2-11 6 0-6 3-11 11-12z"/></svg></button></div>
    <p class="pd-note">Free shipping over ₹<?php echo esc_html( number_format_i18n( $free ) ); ?> · Partial COD available</p>

    <?php if ( $simple && $cols ) : ?>
    <div class="pd-opt" id="pdColorBox">
      <h4>Select colour <b id="pdColorName"><?php echo esc_html( ucfirst( $cols[0] ) ); ?></b></h4>
      <div class="pd-pills" id="pdColors">
        <?php foreach ( $cols as $k => $c ) : ?>
        <button type="button" data-c="<?php echo esc_attr( $c ); ?>" class="<?php echo $k ? '' : 'on'; ?>" aria-pressed="<?php echo $k ? 'false' : 'true'; ?>"><i style="--c:<?php echo esc_attr( $known[ $c ] ); ?>"></i><?php echo esc_html( ucfirst( $c ) ); ?></button>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if ( $simple ) : ?>
    <div class="pd-buy">
      <span class="pd-ql">Quantity:</span>
      <div class="pd-qty"><button type="button" id="pdMinus" aria-label="Decrease quantity">−</button><output id="pdQty">1</output><button type="button" id="pdPlus" aria-label="Increase quantity">+</button></div>
      <?php if ( $buyable ) : ?>
        <button type="button" class="btn btn-dark pd-add" id="pdAdd"<?php echo $cart_attr; // phpcs:ignore WordPress.Security.EscapeOutput ?> data-qty-from="#pdQty">Add to cart</button>
      <?php else : ?>
        <button type="button" class="btn btn-dark pd-add" id="pdAdd" disabled><?php echo $product->is_in_stock() ? 'Unavailable' : 'Sold out'; ?></button>
      <?php endif; ?>
    </div>
    <?php else : ?>
    <div class="pd-buy" style="display:block"><?php woocommerce_template_single_add_to_cart(); ?></div>
    <?php endif; ?>

    <dl class="pd-meta">
      <div><dt>SKU</dt><dd id="pdSku"><?php echo esc_html( $sku ); ?></dd></div>
      <?php if ( $cat_obj ) : ?><div><dt>Category</dt><dd id="pdCat"><a href="<?php echo esc_url( get_term_link( $cat_obj ) ); ?>"><?php echo esc_html( $cat ); ?></a></dd></div><?php endif; ?>
      <?php if ( $feat ) : ?><div><dt>Features</dt><dd id="pdTags"><?php echo esc_html( implode( ', ', $feat ) ); ?></dd></div><?php endif; ?>
      <div><dt>Ships in</dt><dd id="pdShip">3–5 days, printed to order</dd></div>
    </dl>

    <div class="pd-acc">
      <details name="pdacc" open><summary>Description</summary><div class="body" id="accDesc"><?php echo $desc ? wp_kses_post( wpautop( $desc ) ) : esc_html( $lead ); ?></div></details>
      <details name="pdacc"><summary>Details</summary><div class="body" id="accDetails"><ul>
        <li>Material: <?php echo esc_html( $mat ? $mat : 'PLA+' ); ?></li>
        <?php foreach ( $feat as $f ) : if ( $mat && 0 === strcasecmp( $f, $mat ) ) { continue; } ?><li><?php echo esc_html( $f ); ?></li><?php endforeach; ?>
        <li>Printed to order in NCR, India</li><li>Colours can vary slightly from photos</li></ul></div></details>
      <details name="pdacc"><summary>Shipping &amp; returns</summary><div class="body">Free shipping over ₹<?php echo esc_html( number_format_i18n( $free ) ); ?> across India. Pay ₹100 now to confirm a COD order and the rest on delivery. Damaged in transit? We reprint it within 7 days.</div></details>
      <details name="pdacc"><summary>Reviews<?php echo $product->get_review_count() ? ' (' . (int) $product->get_review_count() . ')' : ''; ?></summary><div class="body">
        <?php
        $reviews = get_comments( array( 'post_id' => $id, 'status' => 'approve', 'type' => 'review', 'number' => 10 ) );
        if ( $reviews ) {
        	foreach ( $reviews as $r ) {
        		$rating = (int) get_comment_meta( $r->comment_ID, 'rating', true );
        		echo '<p><strong>' . esc_html( $r->comment_author ) . '</strong>' . ( $rating ? ' · ' . esc_html( str_repeat( '★', $rating ) ) : '' ) . '<br>' . esc_html( $r->comment_content ) . '</p>';
        	}
        } else {
        	echo 'No reviews yet. Reviews from verified buyers will appear here.';
        }
        ?>
      </div></details>
    </div>
  </section>
</main>

<?php get_template_part( 'template-parts/product-faq' ); ?>

<?php if ( $related ) : ?>
<section class="wrap pd-rel">
  <div class="sh"><div><h2>You may <span class="serif">also like</span></h2><p>More from the print farm.</p></div><a href="<?php echo esc_url( layerone_shop_url() ); ?>" class="more">All products →</a></div>
  <div class="grid" id="pdRel"><?php foreach ( $related as $i => $rid ) { echo layerone_product_card( $rid, $i ); } // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
</section>
<?php endif; ?>

<div class="lb" id="lb" role="dialog" aria-modal="true" aria-label="Photo viewer" hidden>
  <button type="button" class="lb-x" id="lbX" aria-label="Close"><svg class="i" viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
  <button type="button" class="lb-nav lb-prev" id="lbPrev" aria-label="Previous photo"><svg class="i" viewBox="0 0 24 24"><path d="m15 5-7 7 7 7"/></svg></button>
  <button type="button" class="lb-nav lb-next" id="lbNext" aria-label="Next photo"><svg class="i" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg></button>
  <div class="lb-stage" id="lbStage"><img id="lbImg" alt=""></div>
  <div class="lb-bar"><span id="lbN"></span><button type="button" id="lbZoom" class="lb-zoom"><svg class="i" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5M11 8v6M8 11h6"/></svg><b>Zoom</b></button></div>
</div>

<?php if ( $simple && $buyable ) : ?>
<div class="sbar" id="sbar" role="region" aria-label="Quick add to cart">
  <div class="wrap sbar-in">
    <?php echo $product->get_image_id() ? wp_get_attachment_image( $product->get_image_id(), 'thumbnail', false, array( 'class' => 'sb-img', 'id' => 'sbImg', 'alt' => '' ) ) : '<img class="sb-img" id="sbImg" alt="">'; // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <div class="sb-info"><b id="sbName"><?php echo esc_html( $name ); ?></b><span id="sbPrice"><?php echo esc_html( $price_now ); ?><?php echo $on_sale ? '<s>' . esc_html( layerone_plain_price( (float) $product->get_regular_price() ) ) . '</s>' : ''; ?></span></div>
    <?php if ( $cols ) : ?>
    <div class="sb-colors" id="sbColors" role="group" aria-label="Colour">
      <?php foreach ( $cols as $k => $c ) : ?>
      <button type="button" data-c="<?php echo esc_attr( $c ); ?>" class="<?php echo $k ? '' : 'on'; ?>" aria-pressed="<?php echo $k ? 'false' : 'true'; ?>" title="<?php echo esc_attr( ucfirst( $c ) ); ?>"><i style="--c:<?php echo esc_attr( $known[ $c ] ); ?>"></i><span><?php echo esc_html( ucfirst( $c ) ); ?></span></button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="pd-qty sb-qty"><button type="button" id="sbMinus" aria-label="Decrease quantity">−</button><output id="sbQty">1</output><button type="button" id="sbPlus" aria-label="Increase quantity">+</button></div>
    <button type="button" class="btn btn-dark sb-add" id="sbAdd"<?php echo $cart_attr; // phpcs:ignore WordPress.Security.EscapeOutput ?> data-qty-from="#sbQty">Add to cart</button>
  </div>
</div>
<?php endif; ?>
	<?php
endwhile;

get_footer();
