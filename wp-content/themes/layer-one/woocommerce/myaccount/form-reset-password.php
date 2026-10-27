<?php
/**
 * Choose a new password (from the link in the reset email).
 *
 * @version WooCommerce 9.x template: myaccount/form-reset-password.php (overrides the plugin's file)
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_reset_password_form' );
?>
<main class="wrap pg simple">
  <?php wc_print_notices(); ?>
  <h1 class="simple-h">Choose a new password.</h1>
  <section class="simple-card">
    <form method="post" class="woocommerce-ResetPassword lost_reset_password" novalidate>
      <label class="sr" for="password_1">New password</label><div class="fld"><input id="password_1" name="password_1" type="password" autocomplete="new-password" minlength="8" placeholder="New password (8+ characters)" required></div>
      <label class="sr" for="password_2">Confirm new password</label><div class="fld"><input id="password_2" name="password_2" type="password" autocomplete="new-password" minlength="8" placeholder="Confirm new password" required></div>
      <input type="hidden" name="reset_key" value="<?php echo esc_attr( $args['key'] ); ?>">
      <input type="hidden" name="reset_login" value="<?php echo esc_attr( $args['login'] ); ?>">
      <input type="hidden" name="wc_reset_password" value="true">
      <?php do_action( 'woocommerce_resetpassword_form' ); ?>
      <?php wp_nonce_field( 'reset_password', 'woocommerce-reset-password-nonce' ); ?>
      <button class="btn btn-dark" type="submit">Save new password</button>
    </form>
  </section>
</main>
<?php do_action( 'woocommerce_after_reset_password_form' ); ?>
