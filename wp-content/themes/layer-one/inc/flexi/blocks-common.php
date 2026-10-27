<?php
/**
 * Shared bits for the Collectibles page section blocks (flexi-hero, flexi-tabs, flexi-loved, flexi-bundles,
 * flexi-all, flexi-box, flexi-how). The catalogue JSON and the pop-ups are printed once per page, by whichever
 * section block comes first.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* {figures}, {series}, {bundles}, {price} (cheapest per figure) and {n} (biggest box) in editable texts. */
function layerone_flexi_ph( $text ) {
	static $map = null;
	if ( null === $map ) {
		$c     = layerone_flexi_catalog();
		$sizes = $c['box']['sizes'];
		$last  = end( $sizes );
		$map   = array(
			'{figures}' => count( $c['figures'] ),
			'{series}'  => count( $c['series'] ),
			'{bundles}' => count( $c['groups'] ),
			'{price}'   => $last ? layerone_plain_price( (float) $last['price'] / max( 1, (int) $last['n'] ) ) : '',
			'{n}'       => $last ? (int) $last['n'] : '',
		);
	}
	return strtr( (string) $text, $map );
}

/* "Title <span class="serif">accent</span>", with placeholders filled in and everything escaped. */
function layerone_flexi_heading( $title, $accent ) {
	return esc_html( layerone_flexi_ph( $title ) ) . ( $accent ? ' <span class="serif">' . esc_html( layerone_flexi_ph( $accent ) ) . '</span>' : '' );
}

function layerone_flexi_print_data() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	echo '<script type="application/json" id="lo-flexi-data">' . wp_json_encode( layerone_flexi_catalog(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE ) . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput
}

/* Bundle panel and figure quick view (the pop-ups). */
function layerone_flexi_print_overlays() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	?>
<div class="lo-flexi">
<div class="v2-sheet" id="sheet" hidden role="dialog" aria-modal="true" aria-labelledby="sheetName">
  <div class="v2-sheet-top">
    <div class="wrap v2-sheet-bar">
      <div class="v2-sheet-tabs" id="sheetTabs"></div>
      <button type="button" class="v2-x" id="sheetClose" aria-label="Close bundle"><svg class="i" viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
    </div>
  </div>
  <div class="v2-sheet-body" id="sheetBody"></div>
</div>

<div class="v2-qv" id="qv" hidden role="dialog" aria-modal="true" aria-labelledby="qvName">
  <div class="v2-qv-card">
    <button type="button" class="v2-x" id="qvClose" aria-label="Close"><svg class="i" viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
    <div class="v2-qv-media" id="qvMedia"></div>
    <div class="v2-qv-info">
      <small id="qvSeries"></small>
      <h3 id="qvName"></h3>
      <div class="v2-qv-story" id="qvStoryBox" hidden><span class="v2-qv-k">Their story</span><p id="qvStory"></p><p class="v2-qv-lesson" id="qvLesson" hidden></p></div>
      <p id="qvLine" class="v2-qv-spec"></p>
      <div class="v2-qv-act" id="qvAct"></div>
      <button type="button" class="v2-qv-bundle" id="qvBundle"></button>
      <div class="v2-qv-nav"><button type="button" class="fx-ar" id="qvPrev" aria-label="Previous figure">←</button><button type="button" class="fx-ar" id="qvNext" aria-label="Next figure">→</button></div>
    </div>
  </div>
</div>
</div>
	<?php
}

/* Opening of every section block: the data script and, for sections with figure cards, the pop-ups. */
function layerone_flexi_section_start( $with_overlays = true ) {
	layerone_flexi_print_data();
	if ( $with_overlays ) {
		layerone_flexi_print_overlays();
	}
}
