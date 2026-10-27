<?php
/**
 * Sign in and create account (guests on the My account page).
 *
 * @version WooCommerce 9.x template: myaccount/form-login.php (overrides the plugin's file)
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification
$register = isset( $_GET['action'] ) && 'register' === $_GET['action'] && 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
if ( isset( $_POST['register'] ) ) {
	$register = true;
}
$redirect  = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : ( isset( $_REQUEST['redirect'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect'] ) ) : '' );
$keep      = $redirect ? array( 'redirect_to' => rawurlencode( $redirect ) ) : array();
$login_url = add_query_arg( $keep, wc_get_page_permalink( 'myaccount' ) );
$reg_url   = add_query_arg( array_merge( $keep, array( 'action' => 'register' ) ), wc_get_page_permalink( 'myaccount' ) );
$val       = function ( $k ) {
	return isset( $_POST[ $k ] ) ? esc_attr( wp_unslash( $_POST[ $k ] ) ) : '';
};
// phpcs:enable

do_action( 'woocommerce_before_customer_login_form' );
?>
<main class="wrap pg simple">
  <?php if ( $redirect ) : ?><p class="simple-note" id="authMsg">Please sign in to continue.</p><?php endif; ?>
  <?php wc_print_notices(); ?>

  <?php if ( ! $register ) : ?>
  <h1 class="simple-h">Welcome back.</h1>
  <section class="simple-card">
    <form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>
      <?php do_action( 'woocommerce_login_form_start' ); ?>
      <label class="sr" for="username">Email address</label><div class="fld"><input id="username" name="username" type="text" autocomplete="username" placeholder="Email address" value="<?php echo $val( 'username' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" required></div>
      <label class="sr" for="password">Password</label><div class="fld"><input id="password" name="password" type="password" autocomplete="current-password" placeholder="Password" required></div>
      <?php do_action( 'woocommerce_login_form' ); ?>
      <label class="ckl"><input type="checkbox" name="rememberme" id="rememberme" value="forever"><i></i><span>Keep me signed in</span></label>
      <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
      <input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ); ?>">
      <button class="btn btn-dark" type="submit" name="login" value="Log in">Sign in</button>
      <?php do_action( 'woocommerce_login_form_end' ); ?>
    </form>
  </section>
  <p class="simple-links"><a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">Forgot your password?</a><?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?><br><a href="<?php echo esc_url( $reg_url ); ?>">Create an account</a><?php endif; ?><br><a href="<?php echo esc_url( home_url( '/track-order/' ) ); ?>">Track an order without signing in</a></p>

  <?php else : ?>
  <h1 class="simple-h">Create your account.</h1>
  <section class="simple-card">
    <form method="post" class="woocommerce-form woocommerce-form-register register" novalidate>
      <?php do_action( 'woocommerce_register_form_start' ); ?>
      <label class="sr" for="lo_name">Full name</label><div class="fld"><input id="lo_name" name="lo_name" autocomplete="name" placeholder="Full name" value="<?php echo $val( 'lo_name' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" required></div>
      <label class="sr" for="reg_email">Email address</label><div class="fld"><input id="reg_email" name="email" type="email" autocomplete="email" placeholder="Email address" value="<?php echo $val( 'email' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" required></div>
      <label class="sr" for="lo_phone">Mobile number</label><div class="fld"><input id="lo_phone" name="lo_phone" type="tel" inputmode="numeric" autocomplete="tel-national" pattern="[6-9][0-9]{9}" title="Enter a 10-digit Indian mobile number" placeholder="Mobile number (optional)" value="<?php echo $val( 'lo_phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>"></div>
      <label class="sr" for="reg_password">Password</label><div class="fld"><input id="reg_password" name="password" type="password" autocomplete="new-password" minlength="8" placeholder="Password (8+ characters)" required></div>
      <label class="sr" for="lo_password2">Confirm password</label><div class="fld"><input id="lo_password2" name="lo_password2" type="password" autocomplete="new-password" minlength="8" placeholder="Confirm password" required></div>
      <label class="ckl"><input type="checkbox" name="lo_terms" value="1" required><i></i><span>I agree to the <a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">Terms</a> and <a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">Privacy Policy</a>.</span></label>
      <?php do_action( 'woocommerce_register_form' ); ?>
      <?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
      <input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ); ?>">
      <button class="btn btn-dark" type="submit" name="register" value="Register">Create account</button>
      <?php do_action( 'woocommerce_register_form_end' ); ?>
    </form>
  </section>
  <p class="simple-links"><a href="<?php echo esc_url( $login_url ); ?>">Already have an account? Sign in</a></p>
  <?php endif; ?>
</main>
<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
