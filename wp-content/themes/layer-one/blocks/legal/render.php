<?php
/**
 * Policy page block, front end. The text is the block's inner blocks; each Heading 2 becomes a link in "On this page".
 *
 * @var array  $attributes Block attributes.
 * @var string $content    The rendered inner blocks.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$toc  = array();
$body = preg_replace_callback( '#<h2([^>]*)>(.*?)</h2>#is', function ( $m ) use ( &$toc ) {
	$label = trim( wp_strip_all_tags( $m[2] ) );
	if ( '' === $label ) {
		return $m[0];
	}
	if ( preg_match( '#\sid="([^"]+)"#', $m[1], $idm ) ) {
		$id = $idm[1];
		$attrs = $m[1];
	} else {
		$id = sanitize_title( $label );
		$attrs = $m[1] . ' id="' . esc_attr( $id ) . '"';
	}
	$toc[ $id ] = $label;
	return '<h2' . $attrs . '>' . $m[2] . '</h2>';
}, (string) $content );

if ( '' === trim( wp_strip_all_tags( $body ) ) ) {
	if ( current_user_can( 'edit_posts' ) ) {
		echo '<p style="padding:24px;text-align:center">Policy page: write the text inside the block in the editor.</p>';
	}
	return;
}
$title   = $attributes['heading'] ? $attributes['heading'] : get_the_title();
$updated = $attributes['updated'] ? $attributes['updated'] : get_the_modified_date( 'j F Y' );
?>
<main class="wrap pg">
  <nav class="crumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><b><?php echo esc_html( $title ); ?></b></nav>
  <div class="pg-head"><h1><?php echo esc_html( $title ); ?></h1><?php if ( $attributes['intro'] ) : ?><p><?php echo esc_html( $attributes['intro'] ); ?></p><?php endif; ?></div>
  <div class="lg">
    <?php if ( $toc ) : ?>
    <nav class="lg-toc" aria-label="On this page"><h2>On this page</h2><?php foreach ( $toc as $id => $label ) : ?><a href="#<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></a><?php endforeach; ?></nav>
    <?php endif; ?>
    <article class="lg-body">
      <?php if ( current_user_can( 'edit_pages' ) && preg_match( '/\[[A-Z][^\]]{2,40}\]/', wp_strip_all_tags( $body ) ) ) : ?>
      <div class="lg-note"><svg class="i" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg><div><b>Only you see this.</b> Some text still has [bracketed] details to fill in. Edit the page, and have a lawyer check it before the store goes live.</div></div>
      <?php endif; ?>
      <?php echo $body; // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <p class="lg-upd">Last updated: <?php echo esc_html( $updated ); ?></p>
    </article>
  </div>
</main>
