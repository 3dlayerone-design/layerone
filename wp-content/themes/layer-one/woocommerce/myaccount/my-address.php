<?php
/**
 * Addresses: several saved addresses, one of them the default (used to fill the checkout).
 *
 * @version WooCommerce 9.x template: myaccount/my-address.php (overrides the plugin's file)
 */

defined( 'ABSPATH' ) || exit;

$list   = layerone_addresses();
$states = WC()->countries->get_states( 'IN' );
$url    = wc_get_account_endpoint_url( 'edit-address' );
$nonce  = function ( $action ) {
	return '<input type="hidden" name="lo_action" value="' . esc_attr( $action ) . '"><input type="hidden" name="_lo_nonce" value="' . esc_attr( wp_create_nonce( 'lo_account_' . $action ) ) . '">';
};
?>
<div class="acc-head row"><div><h1>Addresses</h1><p>Saved delivery addresses for faster checkout.</p></div><button type="button" class="btn btn-dark btn-sm" id="adNew">+ Add address</button></div>

<div class="ad-grid" id="adList">
  <?php if ( $list ) : foreach ( $list as $a ) : ?>
  <article class="ad-card<?php echo ! empty( $a['default'] ) ? ' def' : ''; ?>">
    <?php echo ! empty( $a['default'] ) ? '<span class="ad-tag">Default</span>' : ''; ?><h3><?php echo esc_html( $a['label'] ? $a['label'] : 'Address' ); ?></h3><p class="addr"><?php echo layerone_address_html( $a ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
    <div class="ad-act">
      <button type="button" class="lk" data-edit="<?php echo esc_attr( wp_json_encode( $a ) ); ?>">Edit</button>
      <?php if ( empty( $a['default'] ) ) : ?>
      <form method="post" action="<?php echo esc_url( $url ); ?>" style="display:inline"><?php echo $nonce( 'address_default' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><input type="hidden" name="id" value="<?php echo esc_attr( $a['id'] ); ?>"><button type="submit" class="lk">Make default</button></form>
      <?php endif; ?>
      <form method="post" action="<?php echo esc_url( $url ); ?>" style="display:inline" data-confirm="Delete this address?"><?php echo $nonce( 'address_delete' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><input type="hidden" name="id" value="<?php echo esc_attr( $a['id'] ); ?>"><button type="submit" class="lk del">Delete</button></form>
    </div>
  </article>
  <?php endforeach; else : echo layerone_acc_empty( 'edit-address', 'No saved addresses', 'Add an address to check out faster.' ); endif; // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>

<dialog id="adDlg"><form id="adForm" method="post" action="<?php echo esc_url( $url ); ?>" novalidate>
  <h2 id="adTitle">Add a new address</h2>
  <?php echo $nonce( 'address_save' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
  <input type="hidden" name="id" value="">
  <div class="row2">
    <div class="fld"><label for="adLabel">Label</label><input id="adLabel" name="label" placeholder="Home, Office…"></div>
    <div class="fld"><label for="adName">Full name</label><input id="adName" name="name" autocomplete="name" required></div>
  </div>
  <div class="fld"><label for="adPhone">Mobile number</label><input id="adPhone" name="phone" type="tel" inputmode="numeric" pattern="[6-9][0-9]{9}" title="Enter a 10-digit Indian mobile number" required></div>
  <div class="fld"><label for="adA1">Address</label><input id="adA1" name="a1" autocomplete="address-line1" placeholder="House no., building, street" required></div>
  <div class="fld"><label for="adA2">Area / landmark <i>(optional)</i></label><input id="adA2" name="a2" autocomplete="address-line2"></div>
  <div class="row3">
    <div class="fld"><label for="adCity">City</label><input id="adCity" name="city" autocomplete="address-level2" required></div>
    <div class="fld"><label for="adState">State</label><select id="adState" name="state" autocomplete="address-level1" required><option value="">Select state</option><?php foreach ( $states as $code => $name ) : ?><option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $name ); ?></option><?php endforeach; ?></select></div>
    <div class="fld"><label for="adPin">PIN code</label><input id="adPin" name="pin" inputmode="numeric" pattern="[1-9][0-9]{5}" title="Enter a 6-digit PIN code" required></div>
  </div>
  <label class="ckl"><input type="checkbox" name="default" value="1"><i></i><span>Make this my default address</span></label>
  <div class="dlg-act"><button type="button" class="btn btn-line btn-sm" id="adCancel">Cancel</button><button type="submit" class="btn btn-dark btn-sm">Save address</button></div>
</form></dialog>
