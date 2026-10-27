<?php
/**
 * Customizer settings: Header and Footer (Appearance > Customize).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function layerone_customizer_defaults() {
	return array(
		/* header */
		'lo_ann_on'        => true,
		'lo_ann_slides'    => wp_json_encode( array(
			array( 'bold' => 'Free shipping', 'text' => 'on orders over ₹999 · Printed to order in NCR', 'link' => 'Meet the Flexi Crew', 'url' => '' ),
			array( 'bold' => 'Printed to order', 'text' => 'in tough PLA+ · Dispatched in 2–5 days', 'link' => 'Shop Home & Desk', 'url' => '' ),
			array( 'bold' => 'Custom gifts', 'text' => 'with your photo or logo', 'link' => 'Start a custom order', 'url' => '' ),
		), JSON_UNESCAPED_UNICODE ),
		'lo_ann_speed'     => 4,
		'lo_logo_a'        => 'Layer',
		'lo_logo_b'        => 'One',
		'lo_search_hint'   => 'Search organisers, collectibles, custom gifts…',
		'lo_nav_note'      => 'Printed to order in NCR',
		'lo_whatsapp'      => '918874102088',
		'lo_whatsapp_text' => 'WhatsApp support',
		'lo_free_ship'     => 999,
		'lo_custom_url'    => '',
		'lo_wishlist_url'  => '',
		/* footer */
		'lo_ft_about'      => '3D printed home and desk decor, flexi collectibles and custom gifts. Printed to order in NCR, India.',
		'lo_ft_email'      => '3d.layer.one@gmail.com',
		'lo_ft_phone'      => 'WhatsApp +91 88741 02088',
		'lo_ft_hours'      => 'Mon–Sat, 10am – 7pm',
		'lo_ft_instagram'  => '',
		'lo_ft_amazon'     => '',
		'lo_ft_copyright'  => 'LayerOne. All rights reserved.',
		'lo_ft_secure'     => 'Secure checkout',
		'lo_ft_payments'   => 'UPI, VISA, Mastercard, RuPay, COD',
		'lo_ft_mark'       => 'LayerOne',
	);
}

/* Keep only well-formed messages from the repeater's JSON. */
function layerone_sanitize_slides( $value ) {
	$rows = json_decode( (string) $value, true );
	$out  = array();
	if ( is_array( $rows ) ) {
		foreach ( $rows as $r ) {
			$row = array(
				'bold' => sanitize_text_field( isset( $r['bold'] ) ? $r['bold'] : '' ),
				'text' => sanitize_text_field( isset( $r['text'] ) ? $r['text'] : '' ),
				'link' => sanitize_text_field( isset( $r['link'] ) ? $r['link'] : '' ),
				'url'  => esc_url_raw( isset( $r['url'] ) ? $r['url'] : '' ),
			);
			if ( '' !== $row['bold'] . $row['text'] ) {
				$out[] = $row;
			}
		}
	}
	return wp_json_encode( $out, JSON_UNESCAPED_UNICODE );
}

function layerone_customize_controls_assets() {
	wp_enqueue_script( 'layerone-customizer', get_template_directory_uri() . '/assets/js/customizer-controls.js', array( 'customize-controls' ), LAYERONE_VERSION, true );
	wp_enqueue_style( 'layerone-customizer', get_template_directory_uri() . '/assets/css/customizer-controls.css', array(), LAYERONE_VERSION );
}
add_action( 'customize_controls_enqueue_scripts', 'layerone_customize_controls_assets' );

function layerone_customize( $wp_customize ) {
	$d = layerone_customizer_defaults();

	require_once get_template_directory() . '/inc/class-repeater-control.php';
	$wp_customize->add_section( 'layerone_announcement', array(
		'title'       => __( 'Announcement bar', 'layer-one' ),
		'description' => __( 'The green strip above the header. With two or more messages they slide in one after another.', 'layer-one' ),
		'priority'    => 29,
	) );
	$wp_customize->add_setting( 'lo_ann_on', array( 'default' => true, 'sanitize_callback' => 'rest_sanitize_boolean' ) );
	$wp_customize->add_control( 'lo_ann_on', array( 'label' => __( 'Show the announcement bar', 'layer-one' ), 'section' => 'layerone_announcement', 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'lo_ann_slides', array( 'default' => $d['lo_ann_slides'], 'sanitize_callback' => 'layerone_sanitize_slides' ) );
	$wp_customize->add_control( new Layerone_Repeater_Control( $wp_customize, 'lo_ann_slides', array(
		'label'       => __( 'Messages', 'layer-one' ),
		'description' => __( 'Link text and link are optional. A link with no URL goes to the shop.', 'layer-one' ),
		'section'     => 'layerone_announcement',
	) ) );
	$wp_customize->add_setting( 'lo_ann_speed', array( 'default' => $d['lo_ann_speed'], 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( 'lo_ann_speed', array(
		'label'       => __( 'Seconds each message stays', 'layer-one' ),
		'section'     => 'layerone_announcement',
		'type'        => 'range',
		'input_attrs' => array( 'min' => 2, 'max' => 10, 'step' => 1 ),
	) );

	$sections = array(
		'layerone_header' => array(
			'title'  => __( 'Header', 'layer-one' ),
			'fields' => array(
				'lo_logo_a'        => array( 'Logo text, first part (used when no logo image is set)', 'text' ),
				'lo_logo_b'        => array( 'Logo text, coloured part', 'text' ),
				'lo_search_hint'   => array( 'Search placeholder', 'text' ),
				'lo_nav_note'      => array( 'Right side of the menu bar: note', 'text' ),
				'lo_whatsapp'      => array( 'WhatsApp number with country code, digits only (header and footer)', 'text' ),
				'lo_whatsapp_text' => array( 'WhatsApp link text in header (empty hides it)', 'text' ),
				'lo_free_ship'     => array( 'Free shipping above (₹), used in the cart panel', 'number' ),
				'lo_custom_url'    => array( 'Mobile tab "Custom": URL (empty = /custom/)', 'url' ),
				'lo_wishlist_url'  => array( 'Wishlist icon: URL (empty hides the icon)', 'url' ),
			),
		),
		'layerone_footer' => array(
			'title'  => __( 'Footer', 'layer-one' ),
			'fields' => array(
				'lo_ft_about'     => array( 'About text', 'textarea' ),
				'lo_ft_email'     => array( 'Email (empty hides it)', 'text' ),
				'lo_ft_phone'     => array( 'WhatsApp line text (links to the WhatsApp number; empty hides it)', 'text' ),
				'lo_ft_hours'     => array( 'Opening hours (empty hides it)', 'text' ),
				'lo_ft_instagram' => array( 'Instagram URL (empty hides the icon)', 'url' ),
				'lo_ft_amazon'    => array( 'Amazon store URL (empty hides the icon)', 'url' ),
				'lo_ft_copyright' => array( 'Copyright text after the year', 'text' ),
				'lo_ft_secure'    => array( 'Secure checkout label (empty hides it)', 'text' ),
				'lo_ft_payments'  => array( 'Payment badges, comma separated', 'text' ),
				'lo_ft_mark'      => array( 'Big watermark text', 'text' ),
			),
		),
	);

	foreach ( $sections as $section_id => $section ) {
		$wp_customize->add_section( $section_id, array(
			'title'    => $section['title'],
			'priority' => 30,
		) );

		foreach ( $section['fields'] as $id => $f ) {
			$sanitize = 'sanitize_text_field';
			if ( 'url' === $f[1] ) {
				$sanitize = 'esc_url_raw';
			} elseif ( 'number' === $f[1] ) {
				$sanitize = 'absint';
			} elseif ( 'textarea' === $f[1] ) {
				$sanitize = 'sanitize_textarea_field';
			}
			$wp_customize->add_setting( $id, array(
				'default'           => isset( $d[ $id ] ) ? $d[ $id ] : '',
				'sanitize_callback' => $sanitize,
			) );
			$wp_customize->add_control( $id, array(
				'label'   => $f[0],
				'section' => $section_id,
				'type'    => $f[1],
			) );
		}
	}
}
add_action( 'customize_register', 'layerone_customize' );
