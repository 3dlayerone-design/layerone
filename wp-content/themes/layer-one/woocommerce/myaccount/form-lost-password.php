<?php
/**
 * Forgot your password.
 *
 * @version WooCommerce 9.x template: myaccount/form-lost-password.php (overrides the plugin's file)
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_lost_password_form' );
?>
<main class="wrap pg simple">
  <?php wc_print_notices(); ?>
  <h1 class="simple-h">Forgot your password?</h1>
  <section class="simple-card">
    <form method="post" class="woocommerce-ResetPassword lost_reset_password" novalidate>
      <label class="sr" for="user_login">Account email</label><div class="fld"><input id="user_login" name="user_login" type="text" autocomplete="email" placeholder="Email address" required></div>
      <?php do_action( 'woocommerce_lostpassword_form' ); ?>
      <input type="hidden" name="wc_reset_password" value="true">
      <?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>
      <button class="btn btn-dark" type="submit">Email me a reset link</button>
    </form>
  </section>
  <p class="simple-links"><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">Back to sign in</a></p>
</main>
<?php do_action( 'woocommerce_after_lost_password_form' ); ?>
