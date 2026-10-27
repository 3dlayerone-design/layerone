<?php
/**
 * Flexi Crew settings: box sizes and prices, bonus figure, cart tier steps.
 * Page: WooCommerce > Flexi Crew. Stored in the option "layerone_flexi_settings".
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function layerone_flexi_defaults() {
	return array(
		'sizes'       => array(
			array( 'n' => 6, 'price' => 1199, 'note' => 'Starter box', 'tag' => '' ),
			array( 'n' => 12, 'price' => 1999, 'note' => 'Save 17% · one full bundle', 'tag' => 'Most picked' ),
			array( 'n' => 24, 'price' => 3499, 'note' => 'Save 27%', 'tag' => '' ),
			array( 'n' => 48, 'price' => 5999, 'note' => 'Save 37% · plus 1 bonus figure of your choice', 'tag' => 'Best value' ),
		),
		'bonus_size'  => 48, /* the box size that includes a free extra figure */
		'bonus_count' => 1,
		'tiers'       => '6,15,30,50', /* figures-in-your-boxes steps shown in the cart */
	);
}

function layerone_flexi_settings() {
	$saved = get_option( 'layerone_flexi_settings', array() );
	$out   = wp_parse_args( is_array( $saved ) ? $saved : array(), layerone_flexi_defaults() );
	if ( empty( $out['sizes'] ) || ! is_array( $out['sizes'] ) ) {
		$out['sizes'] = layerone_flexi_defaults()['sizes'];
	}
	return $out;
}

function layerone_flexi_sanitize_settings( $in ) {
	$out = array( 'sizes' => array() );
	foreach ( (array) ( isset( $in['sizes'] ) ? $in['sizes'] : array() ) as $row ) {
		$n = isset( $row['n'] ) ? absint( $row['n'] ) : 0;
		if ( $n < 1 ) {
			continue;
		}
		$out['sizes'][] = array(
			'n'     => $n,
			'price' => isset( $row['price'] ) ? (float) $row['price'] : 0,
			'note'  => isset( $row['note'] ) ? sanitize_text_field( $row['note'] ) : '',
			'tag'   => isset( $row['tag'] ) ? sanitize_text_field( $row['tag'] ) : '',
		);
	}
	usort( $out['sizes'], function ( $a, $b ) {
		return $a['n'] <=> $b['n'];
	} );
	if ( ! $out['sizes'] ) {
		$out['sizes'] = layerone_flexi_defaults()['sizes'];
	}
	$out['bonus_size']  = isset( $in['bonus_size'] ) ? absint( $in['bonus_size'] ) : 0;
	$out['bonus_count'] = isset( $in['bonus_count'] ) ? absint( $in['bonus_count'] ) : 0;
	$tiers              = array_filter( array_map( 'absint', explode( ',', isset( $in['tiers'] ) ? (string) $in['tiers'] : '' ) ) );
	sort( $tiers );
	$out['tiers'] = $tiers ? implode( ',', $tiers ) : layerone_flexi_defaults()['tiers'];
	return $out;
}

add_action( 'admin_init', function () {
	register_setting( 'layerone_flexi', 'layerone_flexi_settings', array( 'sanitize_callback' => 'layerone_flexi_sanitize_settings' ) );
} );

add_action( 'admin_menu', function () {
	add_submenu_page( 'woocommerce', 'Flexi Crew', 'Flexi Crew', 'manage_woocommerce', 'layerone-flexi', 'layerone_flexi_settings_page' );
}, 60 );

function layerone_flexi_settings_page() {
	$s     = layerone_flexi_settings();
	$sizes = $s['sizes'];
	$box   = (int) get_option( 'layerone_flexi_box_product' );
	?>
	<div class="wrap">
		<h1>Flexi Crew</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'layerone_flexi' ); ?>

			<h2>Box sizes and prices</h2>
			<p>Pick-your-own boxes. The price of a box comes from this table, never from the browser. Prices are in the store currency.</p>
			<table class="widefat striped" id="lo-sizes" style="max-width:820px">
				<thead><tr><th>Figures</th><th>Price</th><th>Note</th><th>Badge</th><th></th></tr></thead>
				<tbody>
				<?php foreach ( $sizes as $i => $z ) : ?>
					<tr>
						<td><input type="number" min="1" style="width:80px" name="layerone_flexi_settings[sizes][<?php echo (int) $i; ?>][n]" value="<?php echo esc_attr( $z['n'] ); ?>"></td>
						<td><input type="number" min="0" step="0.01" style="width:110px" name="layerone_flexi_settings[sizes][<?php echo (int) $i; ?>][price]" value="<?php echo esc_attr( $z['price'] ); ?>"></td>
						<td><input type="text" class="regular-text" name="layerone_flexi_settings[sizes][<?php echo (int) $i; ?>][note]" value="<?php echo esc_attr( $z['note'] ); ?>"></td>
						<td><input type="text" style="width:130px" name="layerone_flexi_settings[sizes][<?php echo (int) $i; ?>][tag]" value="<?php echo esc_attr( $z['tag'] ); ?>"></td>
						<td><button type="button" class="button-link-delete lo-size-del">Remove</button></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<p><button type="button" class="button" id="lo-size-add">+ Add size</button></p>

			<h2>Bonus figure</h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="lo_bonus_size">Box size with a bonus</label></th>
					<td><input type="number" min="0" id="lo_bonus_size" name="layerone_flexi_settings[bonus_size]" value="<?php echo esc_attr( $s['bonus_size'] ); ?>"> <span class="description">0 = no bonus.</span></td>
				</tr>
				<tr>
					<th scope="row"><label for="lo_bonus_count">Free extra figures</label></th>
					<td><input type="number" min="0" id="lo_bonus_count" name="layerone_flexi_settings[bonus_count]" value="<?php echo esc_attr( $s['bonus_count'] ); ?>"> <span class="description">The customer picks these themselves, at no charge.</span></td>
				</tr>
			</table>

			<h2>Cart progress bar</h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="lo_tiers">Steps (figures in boxes)</label></th>
					<td><input type="text" class="regular-text" id="lo_tiers" name="layerone_flexi_settings[tiers]" value="<?php echo esc_attr( $s['tiers'] ); ?>"> <span class="description">Comma separated, for example 6,15,30,50.</span></td>
				</tr>
			</table>

			<h2>Box product</h2>
			<p>
				<?php
				if ( $box && get_post( $box ) ) {
					printf( 'The pick-your-own box is the product <a href="%s">#%d %s</a>. Do not delete it.', esc_url( get_edit_post_link( $box ) ), (int) $box, esc_html( get_the_title( $box ) ) );
				} else {
					echo 'No box product yet.';
				}
				?>
			</p>

			<?php submit_button(); ?>
		</form>
	</div>
	<script>
	(function () {
		var body = document.querySelector('#lo-sizes tbody');
		document.getElementById('lo-size-add').addEventListener('click', function () {
			var i = body.rows.length + Date.now() % 1000;
			var tr = document.createElement('tr');
			tr.innerHTML = '<td><input type="number" min="1" style="width:80px" name="layerone_flexi_settings[sizes][' + i + '][n]"></td>' +
				'<td><input type="number" min="0" step="0.01" style="width:110px" name="layerone_flexi_settings[sizes][' + i + '][price]"></td>' +
				'<td><input type="text" class="regular-text" name="layerone_flexi_settings[sizes][' + i + '][note]"></td>' +
				'<td><input type="text" style="width:130px" name="layerone_flexi_settings[sizes][' + i + '][tag]"></td>' +
				'<td><button type="button" class="button-link-delete lo-size-del">Remove</button></td>';
			body.appendChild(tr);
		});
		body.addEventListener('click', function (e) {
			if (e.target.classList.contains('lo-size-del')) e.target.closest('tr').remove();
		});
	})();
	</script>
	<?php
}
