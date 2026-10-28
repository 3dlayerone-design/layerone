<?php
/**
 * Shop, product category and product search pages: the design's listing page.
 * The products are embedded as JSON (cards already drawn on the server) and filtered in the browser by assets/js/listing.js.
 */

defined( 'ABSPATH' ) || exit;

get_header();

/* ---- what this page lists ---- */
$term  = is_product_category() ? get_queried_object() : null;
$query = is_search() ? sanitize_text_field( get_search_query( false ) ) : '';

$args = array(
	'status'     => 'publish',
	'limit'      => -1,
	'visibility' => $query ? 'search' : 'catalog',
	'orderby'    => 'menu_order',
	'order'      => 'ASC',
);
if ( $term ) {
	$args['category'] = array( $term->slug );
}
if ( $query ) {
	$args['s'] = $query;
}

$items = array();
foreach ( wc_get_products( $args ) as $i => $p ) {
	if ( ! layerone_is_shop_product( $p->get_id() ) ) {
		continue;
	}
	$price   = $p->get_price();
	$items[] = array(
		'id'     => $p->get_id(),
		'idx'    => (int) $p->get_menu_order() * 100000 + (int) $p->get_id(),
		'cat'    => layerone_product_cat_name( $p ),
		'name'   => wp_specialchars_decode( $p->get_name(), ENT_QUOTES ),
		'price'  => '' === $price ? 0 : (float) $price,
		'sale'   => (bool) $p->is_on_sale(),
		'isNew'  => layerone_product_is_new( $p ),
		'best'   => (bool) $p->is_featured(),
		'colors' => layerone_product_colours( $p ),
		'html'   => layerone_product_card( $p, 0 ),
	);
}

/* ---- headings ---- */
$default_desc = 'Home & desk decor, collectibles and custom gifts. Everything is printed to order in NCR.';
if ( $term ) {
	$title = wp_specialchars_decode( $term->name, ENT_QUOTES );
	$desc  = $term->description ? wp_strip_all_tags( $term->description ) : 'Everything in ' . $title . ' is printed to order in NCR.';
} elseif ( $query ) {
	$title = 'Search results for “' . $query . '”';
	$desc  = 'Products that match your search.';
} else {
	$title = 'All products';
	$desc  = $default_desc;
}
$free = (int) layerone_opt( 'lo_free_ship' );

$config = array(
	'items'   => $items,
	'colours' => layerone_colours(),
	'title'   => $title,
);
?>
<section class="lp-hero">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span>
      <?php if ( $term && $term->parent ) : $parent = get_term( $term->parent, 'product_cat' ); ?>
        <a href="<?php echo esc_url( get_term_link( $parent ) ); ?>"><?php echo esc_html( $parent->name ); ?></a><span>/</span>
      <?php endif; ?>
      <b id="crumb"><?php echo esc_html( $title ); ?></b></nav>
    <div class="lp-head">
      <div>
        <h1 id="lpTitle"><?php echo esc_html( $title ); ?></h1>
        <p id="lpDesc"><?php echo esc_html( $desc ); ?></p>
      </div>
      <ul class="lp-facts" aria-label="Good to know">
        <li><b id="factN"><?php echo (int) count( $items ); ?></b><span>products</span></li>
        <li><b>3–5 days</b><span>printed to order</span></li>
        <li><b>Free</b><span>shipping over ₹<?php echo esc_html( number_format_i18n( $free ) ); ?></span></li>
      </ul>
    </div>
    <div class="lp-pills" id="catPills" role="group" aria-label="Categories"></div>
  </div>
</section>

<main class="wrap lp">
  <div class="lp-layout">
    <aside class="lf" id="filters" aria-label="Filters">
      <div class="lf-top"><h2>Filters</h2><button type="button" class="lf-clear" id="clearAll">Clear all</button><button type="button" class="x lf-close" id="closeFilters" aria-label="Close filters"><svg class="i" viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg></button></div>

      <fieldset class="lf-box">
        <legend>Colour</legend>
        <div class="lf-colors" id="fColors"></div>
      </fieldset>

      <fieldset class="lf-box">
        <legend>Offers</legend>
        <label class="ck"><input type="checkbox" id="fSale"><i></i><span>On sale</span><small id="nSale"></small></label>
        <label class="ck"><input type="checkbox" id="fNew"><i></i><span>New arrivals</span><small id="nNew"></small></label>
      </fieldset>

      <fieldset class="lf-box">
        <legend>Price</legend>
        <div id="fPrice"></div>
        <div class="lf-range">
          <label><span>Min ₹</span><input type="number" id="pMin" min="0" step="50" inputmode="numeric" placeholder="0"></label>
          <i>–</i>
          <label><span>Max ₹</span><input type="number" id="pMax" min="0" step="50" inputmode="numeric" placeholder="3000"></label>
        </div>
      </fieldset>

      <button type="button" class="btn btn-dark lf-apply" id="applyFilters">Show <span id="applyCount">0</span> products</button>
    </aside>

    <section class="lp-main" aria-live="polite">
      <div class="lp-bar">
        <button type="button" class="btn btn-line btn-sm lf-open" id="openFilters"><svg class="i" viewBox="0 0 24 24"><path d="M4 6h16M7 12h10M10 18h4"/></svg>Filters <span class="lf-n" id="fCount" hidden>0</span></button>
        <span class="lp-count" id="count">0 products</span>
        <label class="lp-sort"><span>Sort by</span>
          <select id="sort" aria-label="Sort products">
            <option value="featured">Featured</option>
            <option value="new">Newest</option>
            <option value="low">Price: low to high</option>
            <option value="high">Price: high to low</option>
            <option value="az">Name: A to Z</option>
          </select>
        </label>
      </div>

      <div class="lp-chips" id="chips"></div>

      <div class="grid" id="lpGrid"></div>

      <div class="lp-empty" id="empty" hidden>
        <svg class="i" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5M8.5 11h5"/></svg>
        <h3>No products match these filters</h3>
        <p>Try removing a filter or widening the price range.</p>
        <button type="button" class="btn btn-dark btn-sm" id="emptyClear">Clear all filters</button>
      </div>

      <div class="lp-more" id="moreWrap">
        <span id="shown">Showing 0 of 0</span>
        <div class="lp-prog"><i id="progBar"></i></div>
        <button type="button" class="btn btn-line" id="loadMore">Load more</button>
      </div>
    </section>
  </div>
</main>

<script type="application/json" id="lo-shop-data"><?php echo wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE ); // phpcs:ignore WordPress.Security.EscapeOutput ?></script>
<?php
get_footer();
