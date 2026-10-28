<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * "Frequently asked questions" under every product. The questions live in this list; change the texts here.
 */
$free = (int) layerone_opt( 'lo_free_ship' );
$wa   = preg_replace( '/\D/', '', layerone_opt( 'lo_whatsapp' ) );
$faq  = array(
	array( 'How long does my order take?', 'Every item is printed after you order. It is dispatched in 3–5 days (custom orders 5–7 days after you approve the free preview) and then delivered across India.' ),
	array( 'Is shipping free?', sprintf( 'Yes, shipping is free on orders over ₹%s anywhere in India. Below that, you will see the shipping cost in your cart before you pay.', number_format_i18n( $free ) ) ),
	array( 'Do you offer cash on delivery?', 'Yes, partial COD. Pay ₹100 now to confirm your order and the rest when it arrives.' ),
	array( 'Will the colour match the photos?', 'Very close. Each piece is printed to order in PLA+, so shades can vary slightly from batch to batch and from screen to screen. Pick your colour above and we print that one.' ),
	array( 'What is it made of and how do I care for it?', 'PLA+, a tough plant-based plastic. Wipe with a dry or slightly damp cloth, and keep it away from direct sun and hot places such as a parked car, as PLA+ softens in high heat.' ),
	array( 'Can I get it with my name or logo?', 'Yes. Our Custom & Corporate line adds your name, logo or idea. We send a free preview for you to approve before anything is printed. Corporate orders start at 5 pieces.' ),
	array( 'What if it arrives damaged?', 'Send us a photo within 7 days and we reprint it for you, free. See Shipping & returns above for details.' ),
);
?>
<section class="wrap pd-faq" id="faq" aria-labelledby="faqH">
  <div class="pd-faq-head">
    <h2 id="faqH">Frequently asked <span class="serif">questions</span></h2>
    <p>Can’t find your answer? <a href="<?php echo esc_url( $wa ? 'https://wa.me/' . $wa : home_url( '/contact/' ) ); ?>"<?php echo $wa ? ' target="_blank" rel="noopener"' : ''; ?>>Message us on WhatsApp</a> and we’ll help.</p>
  </div>
  <div class="pd-faq-list">
    <?php foreach ( $faq as $i => $row ) : ?>
    <details name="faq"<?php echo 0 === $i ? ' open' : ''; ?>><summary><?php echo esc_html( $row[0] ); ?></summary><div class="body"><?php echo esc_html( $row[1] ); ?></div></details>
    <?php endforeach; ?>
  </div>
</section>
