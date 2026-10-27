<?php
/**
 * "Thank you" page after an order is placed.
 *
 * @version WooCommerce 9.x template: checkout/thankyou.php (overrides the plugin's file)
 */

defined( 'ABSPATH' ) || exit;
?>
<main class="wrap pg">
  <nav class="crumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><b>Order received</b></nav>

  <?php if ( $order ) : ?>
    <?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

    <?php if ( $order->has_status( 'failed' ) ) : ?>
    <div class="co-done">
      <h2>Your payment did not go through</h2>
      <p>Please try again, or choose another payment method.</p>
      <div class="acc-links" style="justify-content:center"><a class="btn btn-dark" href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>">Pay now</a><a class="btn btn-line" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">My account</a></div>
    </div>
    <?php else : ?>
    <div class="co-done">
      <div class="badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.500 4.500 4.500L19 7.500"/></svg></div>
      <h2>Thanks, your order is in</h2>
      <p>Order <b>#<?php echo esc_html( $order->get_order_number() ); ?></b> for <b><?php echo esc_html( layerone_plain_price( (float) $order->get_total() ) ); ?></b>. We sent a confirmation to <b><?php echo esc_html( $order->get_billing_email() ); ?></b>.</p>
      <p><?php echo esc_html( $order->get_payment_method_title() ); ?>. Printed to order and dispatched in 2–5 days.</p>

      <div class="sum-items" style="max-width:520px;margin:18px auto 0;text-align:left">
        <?php foreach ( $order->get_items() as $item ) :
        	$product = $item->get_product();
        	?>
        <div class="si"><div class="t"><?php echo $product && $product->get_image_id() ? wp_get_attachment_image( $product->get_image_id(), 'thumbnail', false, array( 'alt' => '' ) ) : '<div class="ph"></div>'; // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php echo (int) $item->get_quantity(); ?></b></div>
          <div><?php echo esc_html( $item->get_name() ); ?><?php
          foreach ( $item->get_formatted_meta_data() as $meta ) {
          	echo '<small>' . esc_html( wp_strip_all_tags( $meta->display_key ) . ': ' . wp_strip_all_tags( $meta->display_value ) ) . '</small>';
          }
          ?></div><div><?php echo esc_html( layerone_plain_price( (float) $order->get_line_total( $item, true ) ) ); ?></div></div>
        <?php endforeach; ?>
      </div>

      <?php if ( ! is_user_logged_in() ) : ?><p>Create an account with this email to see it under My orders.</p><?php endif; ?>
      <div class="acc-links" style="justify-content:center">
        <?php if ( is_user_logged_in() ) : ?><a class="btn btn-dark" href="<?php echo esc_url( $order->get_view_order_url() ); ?>">View order</a><?php else : ?><a class="btn btn-dark" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">Create account</a><?php endif; ?>
        <a class="btn btn-line" href="<?php echo esc_url( layerone_shop_url() ); ?>">Continue shopping</a>
      </div>
    </div>
    <?php endif; ?>

    <?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
    <?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>
  <?php else : ?>
    <div class="co-done"><h2>Thanks, your order is in</h2><p>We have received it. A confirmation is on its way to your email.</p><div class="acc-links" style="justify-content:center"><a class="btn btn-line" href="<?php echo esc_url( layerone_shop_url() ); ?>">Continue shopping</a></div></div>
  <?php endif; ?>
</main>
