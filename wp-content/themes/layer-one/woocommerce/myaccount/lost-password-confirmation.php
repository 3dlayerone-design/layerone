<?php
/**
 * Password reset email sent.
 *
 * @version WooCommerce 9.x template: myaccount/lost-password-confirmation.php (overrides the plugin's file)
 */

defined( 'ABSPATH' ) || exit;
?>
<main class="wrap pg simple">
  <h1 class="simple-h">Check your email.</h1>
  <section class="simple-card">
    <div class="acc-ok">If that email belongs to an account, a link to choose a new password is on its way. It can take a few minutes to arrive, so look in your spam folder too.</div>
  </section>
  <p class="simple-links"><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">Back to sign in</a></p>
</main>
