<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Site footer. Columns and bottom links come from the footer menu locations; text from Customizer > Footer.
 */
$wa        = preg_replace( '/\D/', '', layerone_opt( 'lo_whatsapp' ) );
$payments  = array_filter( array_map( 'trim', explode( ',', layerone_opt( 'lo_ft_payments' ) ) ) );
$columns   = array(
	'footer-shop'    => 'Shop',
	'footer-support' => 'Support',
	'footer-company' => 'Company',
);
?>
<footer class="ft" id="contact">
  <div class="wrap">
    <a href="#" class="totop" aria-label="Back to top"><svg class="i" viewBox="0 0 24 24"><path d="M12 19V5M5 12l7-7 7 7"/></svg></a>
    <div class="ft-grid">
      <div class="ft-brand">
        <?php get_template_part( 'template-parts/logo' ); ?>
        <?php if ( layerone_opt( 'lo_ft_about' ) ) : ?><p><?php echo esc_html( layerone_opt( 'lo_ft_about' ) ); ?></p><?php endif; ?>
        <ul class="ft-contact">
          <?php if ( layerone_opt( 'lo_ft_email' ) ) : ?>
          <li><svg class="i" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m4 7 8 6 8-6"/></svg><a href="mailto:<?php echo esc_attr( antispambot( layerone_opt( 'lo_ft_email' ) ) ); ?>"><?php echo esc_html( layerone_opt( 'lo_ft_email' ) ); ?></a></li>
          <?php endif; ?>
          <?php if ( $wa && layerone_opt( 'lo_ft_phone' ) ) : ?>
          <li><svg class="i" viewBox="0 0 24 24"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/></svg><a href="<?php echo esc_url( 'https://wa.me/' . $wa ); ?>" target="_blank" rel="noopener"><?php echo esc_html( layerone_opt( 'lo_ft_phone' ) ); ?></a></li>
          <?php endif; ?>
          <?php if ( layerone_opt( 'lo_ft_hours' ) ) : ?>
          <li><svg class="i" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg><?php echo esc_html( layerone_opt( 'lo_ft_hours' ) ); ?></li>
          <?php endif; ?>
        </ul>
        <?php if ( layerone_opt( 'lo_ft_instagram' ) || layerone_opt( 'lo_ft_amazon' ) ) : ?>
        <div class="soc">
          <?php if ( layerone_opt( 'lo_ft_instagram' ) ) : ?>
          <a href="<?php echo esc_url( layerone_opt( 'lo_ft_instagram' ) ); ?>" target="_blank" rel="noopener" aria-label="Instagram"><svg class="i" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg></a>
          <?php endif; ?>
          <?php if ( layerone_opt( 'lo_ft_amazon' ) ) : ?>
          <a href="<?php echo esc_url( layerone_opt( 'lo_ft_amazon' ) ); ?>" target="_blank" rel="noopener" aria-label="Amazon store"><svg class="i" viewBox="0 0 24 24"><path d="M4 15c4 3 12 3 16 0"/><path d="m17 14 3 1-1 3"/></svg></a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
      <?php
      foreach ( $columns as $location => $label ) {
      	$links = layerone_menu_html( $location, 'Layerone_List_Walker' );
      	if ( ! $links ) {
      		continue;
      	}
      	echo '<nav class="ft-col" aria-label="' . esc_attr( $label ) . '"><h4>' . esc_html( $label ) . '</h4><ul>' . $links . '</ul></nav>'; // phpcs:ignore WordPress.Security.EscapeOutput
      }
      ?>
    </div>

    <div class="ft-bar">
      <span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( layerone_opt( 'lo_ft_copyright' ) ); ?></span>
      <?php if ( $payments || layerone_opt( 'lo_ft_secure' ) ) : ?>
      <div class="pay" aria-label="Accepted payments">
        <?php if ( layerone_opt( 'lo_ft_secure' ) ) : ?>
        <span class="lock"><svg class="i" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg><?php echo esc_html( layerone_opt( 'lo_ft_secure' ) ); ?></span>
        <?php endif; ?>
        <?php foreach ( $payments as $p ) : ?><i><?php echo esc_html( $p ); ?></i><?php endforeach; ?>
      </div>
      <?php endif; ?>
      <div class="legal"><button type="button" id="themeBtn" class="theme-btn">Dark theme</button><?php
      echo layerone_menu_html( 'footer-legal', 'Layerone_Flat_Walker' ); // phpcs:ignore WordPress.Security.EscapeOutput
      ?></div>
    </div>
  </div>
  <?php if ( layerone_opt( 'lo_ft_mark' ) ) : ?><div class="ft-mark" aria-hidden="true"><?php echo esc_html( layerone_opt( 'lo_ft_mark' ) ); ?></div><?php endif; ?>
</footer>
