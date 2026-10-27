<?php
/**
 * My account: sidebar and the page of the chosen endpoint (signed-in customers).
 *
 * @version WooCommerce 9.x template: myaccount/my-account.php (overrides the plugin's file)
 */

defined( 'ABSPATH' ) || exit;

$labels  = wc_get_account_menu_items();
$current = 'dashboard';
foreach ( array_keys( $labels ) as $endpoint ) {
	if ( 'dashboard' !== $endpoint && is_wc_endpoint_url( $endpoint ) ) {
		$current = $endpoint;
	}
}
if ( is_wc_endpoint_url( 'view-order' ) ) {
	$current = 'orders';
}
$crumb = isset( $labels[ $current ] ) ? $labels[ $current ] : 'My account';
if ( is_wc_endpoint_url( 'view-order' ) ) {
	$crumb = 'Order details';
}
?>
<main class="wrap pg">
  <nav class="crumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><?php if ( 'dashboard' !== $current ) : ?><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">My account</a><span>/</span><?php endif; ?><b><?php echo esc_html( 'dashboard' === $current ? 'Dashboard' : $crumb ); ?></b></nav>
  <div class="acc">
    <aside class="acc-nav" id="accNav" aria-label="My account menu">
      <?php do_action( 'woocommerce_account_navigation' ); ?>
    </aside>
    <div class="acc-main" id="accBody">
      <?php do_action( 'woocommerce_account_content' ); ?>
    </div>
  </div>
</main>
