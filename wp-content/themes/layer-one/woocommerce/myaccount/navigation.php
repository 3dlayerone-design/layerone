<?php
/**
 * My account sidebar.
 *
 * @version WooCommerce 9.x template: myaccount/navigation.php (overrides the plugin's file)
 */

defined( 'ABSPATH' ) || exit;

$user    = wp_get_current_user();
$current = 'dashboard';
foreach ( array_keys( wc_get_account_menu_items() ) as $endpoint ) {
	if ( 'dashboard' !== $endpoint && is_wc_endpoint_url( $endpoint ) ) {
		$current = $endpoint;
	}
}
if ( is_wc_endpoint_url( 'view-order' ) ) {
	$current = 'orders';
}
$name = $user->display_name ? $user->display_name : $user->user_login;
?>
<div class="acc-user"><span class="av-l"><?php echo esc_html( strtoupper( mb_substr( $name, 0, 1 ) ) ); ?></span><div><b><?php echo esc_html( $name ); ?></b><small><?php echo esc_html( $user->user_email ); ?></small></div></div>
<nav aria-label="My account">
  <?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
  <a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"<?php echo $endpoint === $current ? ' aria-current="page"' : ''; ?>><?php echo layerone_account_icon( $endpoint ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $label ); ?></a>
  <?php endforeach; ?>
</nav>
<a class="acc-out" href="<?php echo esc_url( wp_nonce_url( wc_get_endpoint_url( 'customer-logout', '', wc_get_page_permalink( 'myaccount' ) ), 'customer-logout' ) ); ?>" style="display:block;text-align:center">Sign out</a>
