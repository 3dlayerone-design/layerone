<?php
/**
 * FAQ block, front end.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$groups = array();
foreach ( (array) $attributes['groups'] as $g ) {
	$title = isset( $g['title'] ) ? trim( (string) $g['title'] ) : '';
	$items = array();
	foreach ( (array) ( isset( $g['items'] ) ? $g['items'] : array() ) as $it ) {
		if ( ! empty( $it['q'] ) && ! empty( $it['a'] ) ) {
			$items[] = $it;
		}
	}
	if ( '' !== $title && $items ) {
		$groups[ sanitize_title( $title ) ] = array( 'title' => $title, 'items' => $items );
	}
}
if ( ! $groups ) {
	if ( current_user_can( 'edit_posts' ) ) {
		echo '<p style="padding:24px;text-align:center">FAQs: add at least one group with a question in the editor.</p>';
	}
	return;
}

/* Structured data for search engines: the same questions, as plain text. */
$ld = array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array() );
foreach ( $groups as $g ) {
	foreach ( $g['items'] as $it ) {
		$ld['mainEntity'][] = array(
			'@type'          => 'Question',
			'name'           => wp_strip_all_tags( $it['q'] ),
			'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags( $it['a'] ) ),
		);
	}
}
$help_url = $attributes['helpUrl'] ? $attributes['helpUrl'] : home_url( '/contact/' );
$first    = true;
?>
<script type="application/ld+json"><?php echo wp_json_encode( $ld, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP ); // phpcs:ignore WordPress.Security.EscapeOutput ?></script>
<main class="wrap pg">
  <nav class="crumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><b><?php echo esc_html( get_the_title() ); ?></b></nav>
  <div class="pg-head"><h1><?php echo esc_html( $attributes['heading'] ); ?><?php if ( $attributes['headingAccent'] ) : ?> <span class="serif"><?php echo esc_html( $attributes['headingAccent'] ); ?></span><?php endif; ?></h1><?php if ( $attributes['intro'] ) : ?><p><?php echo esc_html( $attributes['intro'] ); ?></p><?php endif; ?></div>

  <div class="fq-tools">
    <div class="fq-search"><svg class="i" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg><input id="fqSearch" type="search" placeholder="Search questions…" aria-label="Search questions"></div>
    <div class="fq-chips" id="fqChips"><button type="button" class="on" data-cat="all">All</button><?php foreach ( $groups as $id => $g ) : ?><button type="button" data-cat="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $g['title'] ); ?></button><?php endforeach; ?></div>
  </div>

  <?php foreach ( $groups as $id => $g ) : ?>
  <section class="fq-group" id="<?php echo esc_attr( $id ); ?>"><h2><?php echo esc_html( $g['title'] ); ?></h2><div class="faq-list">
    <?php foreach ( $g['items'] as $it ) : ?>
    <details name="fq" class="fq-item" data-cat="<?php echo esc_attr( $id ); ?>"<?php echo $first ? ' open' : ''; ?>><summary><?php echo esc_html( $it['q'] ); ?></summary><div class="body"><?php echo wp_kses_post( $it['a'] ); ?></div></details>
    <?php $first = false; endforeach; ?>
  </div></section>
  <?php endforeach; ?>

  <div class="fq-none" id="fqNone" hidden><b>No answers found</b>Nothing matches “<span id="fqNoneQ"></span>”. Try a different word, or ask us directly.</div>

  <?php if ( $attributes['helpTitle'] ) : ?>
  <div class="fq-cta">
    <div><h2><?php echo esc_html( $attributes['helpTitle'] ); ?></h2><?php if ( $attributes['helpText'] ) : ?><p><?php echo esc_html( $attributes['helpText'] ); ?></p><?php endif; ?></div>
    <?php if ( $attributes['helpLabel'] ) : ?><a class="btn btn-dark" href="<?php echo esc_url( $help_url ); ?>"><?php echo esc_html( $attributes['helpLabel'] ); ?></a><?php endif; ?>
  </div>
  <?php endif; ?>
</main>
