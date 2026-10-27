<?php
/**
 * Account details: name and mobile, password and deleting the account.
 *
 * @version WooCommerce 9.x template: myaccount/form-edit-account.php (overrides the plugin's file)
 */

defined( 'ABSPATH' ) || exit;

$user  = wp_get_current_user();
$url   = wc_get_account_endpoint_url( 'edit-account' );
$since = layerone_date( strtotime( $user->user_registered ) );
$nonce = function ( $action ) {
	return '<input type="hidden" name="lo_action" value="' . esc_attr( $action ) . '"><input type="hidden" name="_lo_nonce" value="' . esc_attr( wp_create_nonce( 'lo_account_' . $action ) ) . '">';
};
?>
<div class="acc-head"><h1>Account details</h1><p>Member since <span id="pSince"><?php echo esc_html( $since ); ?></span>.</p></div>

<section class="acc-card">
  <h2>Your details</h2>
  <form method="post" action="<?php echo esc_url( $url ); ?>" id="pForm" style="display:grid;gap:16px" novalidate>
    <?php echo $nonce( 'profile' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <div class="row2">
      <div class="fld"><label for="pName">Full name</label><input id="pName" name="name" autocomplete="name" value="<?php echo esc_attr( $user->display_name ); ?>" required></div>
      <div class="fld"><label for="pPhone">Mobile <i>(optional)</i></label><input id="pPhone" name="phone" type="tel" inputmode="numeric" autocomplete="tel-national" pattern="[6-9][0-9]{9}" title="Enter a 10-digit Indian mobile number" value="<?php echo esc_attr( get_user_meta( $user->ID, 'billing_phone', true ) ); ?>"></div>
    </div>
    <div class="fld"><label for="pEmail">Email</label><input id="pEmail" type="email" value="<?php echo esc_attr( $user->user_email ); ?>" disabled></div>
    <label class="ckl"><input type="checkbox" name="marketing" value="1" <?php checked( (int) get_user_meta( $user->ID, 'layerone_marketing', true ), 1 ); ?>><i></i><span>Email me about new series and festive offers</span></label>
    <button class="btn btn-dark btn-sm" type="submit" style="width:max-content">Save changes</button>
  </form>
</section>

<section class="acc-card">
  <h2>Change password</h2>
  <form method="post" action="<?php echo esc_url( $url ); ?>" id="pwForm" style="display:grid;gap:16px" novalidate>
    <?php echo $nonce( 'password' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <div class="fld"><label for="pwOld">Current password</label><input id="pwOld" name="old" type="password" autocomplete="current-password" required></div>
    <div class="row2">
      <div class="fld"><label for="pwNew">New password</label><input id="pwNew" name="new" type="password" autocomplete="new-password" minlength="8" required></div>
      <div class="fld"><label for="pwNew2">Confirm new password</label><input id="pwNew2" name="new2" type="password" autocomplete="new-password" minlength="8" required></div>
    </div>
    <button class="btn btn-dark btn-sm" type="submit" style="width:max-content">Update password</button>
  </form>
</section>

<section class="acc-card danger">
  <h2>Delete account</h2>
  <p class="muted">This removes your account, saved addresses and wishlist. Your past orders stay with us for our records. It cannot be undone.</p>
  <form method="post" action="<?php echo esc_url( $url ); ?>" class="acc-links" data-confirm="Delete your account, saved addresses and wishlist? This cannot be undone."><?php echo $nonce( 'delete_account' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><button class="btn btn-line btn-sm" type="submit">Delete my account</button></form>
</section>
