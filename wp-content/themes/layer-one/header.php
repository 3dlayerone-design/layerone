<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Site header: announcement bar, top bar, menu bar. Mobile drawer, tab bar and cart sheet are in footer.php.
 */
$wa      = preg_replace( '/\D/', '', layerone_opt( 'lo_whatsapp' ) );
$count   = layerone_cart_count();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#ffffff">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php $slides = layerone_ann_slides(); if ( $slides ) : ?>
<div class="ann ann-slider" data-speed="<?php echo (int) max( 2, (int) layerone_opt( 'lo_ann_speed' ) ); ?>" aria-live="off">
<?php foreach ( $slides as $i => $s ) : ?>
  <p class="ann-s<?php echo 0 === $i ? ' on' : ''; ?>"><b><?php echo esc_html( $s['bold'] ); ?></b><?php echo $s['text'] ? ' ' . esc_html( $s['text'] ) : ''; ?><?php if ( $s['link'] ) : ?> <a href="<?php echo esc_url( $s['url'] ); ?>"><?php echo esc_html( $s['link'] ); ?></a><?php endif; ?></p>
<?php endforeach; ?>
</div>
<?php endif; ?>

<header class="header">
  <div class="wrap top">
    <button class="tool burger" id="burger" aria-label="Menu"><svg class="i" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
    <?php get_template_part( 'template-parts/logo' ); ?>
    <form class="search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
      <input type="hidden" name="post_type" value="product">
      <input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( layerone_opt( 'lo_search_hint' ) ); ?>" aria-label="Search">
      <button type="submit" aria-label="Search"><svg class="i" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></button>
    </form>
    <div class="tools">
      <a href="<?php echo esc_url( layerone_account_url() ); ?>" class="tool hide"><svg class="i" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg>Account</a>
      <a href="<?php echo esc_url( layerone_wishlist_page_url() ); ?>" class="tool hide" aria-label="Wishlist"><svg class="i" viewBox="0 0 24 24"><path d="M12 20s-7.5-4.6-7.5-10.3A4.2 4.2 0 0 1 12 7.4a4.2 4.2 0 0 1 7.5 2.3C19.5 15.4 12 20 12 20z"/></svg><span class="n" id="wn" hidden>0</span></a>
      <button class="tool cartb" id="cartBtn" aria-label="Open cart"><svg class="i" viewBox="0 0 24 24"><path d="M6 8h12l-1 12H7z"/><path d="M9 8a3 3 0 0 1 6 0"/></svg><span class="lbl">Cart</span><span class="n" id="cn"><?php echo (int) $count; ?></span></button>
    </div>
  </div>
  <div class="wrap nav">
    <?php echo layerone_nav_links(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <div class="r">
      <?php if ( layerone_opt( 'lo_nav_note' ) ) : ?><span><?php echo esc_html( layerone_opt( 'lo_nav_note' ) ); ?></span><?php endif; ?>
      <?php if ( $wa && layerone_opt( 'lo_whatsapp_text' ) ) : ?><a href="<?php echo esc_url( 'https://wa.me/' . $wa ); ?>" target="_blank" rel="noopener"><?php echo esc_html( layerone_opt( 'lo_whatsapp_text' ) ); ?></a><?php endif; ?>
    </div>
  </div>
</header>

<main id="main"<?php echo is_front_page() ? ' class="l2"' : ''; ?>>
