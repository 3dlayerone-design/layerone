<?php
/**
 * Customizer repeater control (announcement bar messages). Loaded from layerone_customize(),
 * because WP_Customize_Control only exists once the Customizer is running.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Layerone_Repeater_Control extends WP_Customize_Control {
	public $type = 'layerone_repeater';

	public function render_content() {
		?>
		<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php if ( $this->description ) : ?><span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span><?php endif; ?>
		<div class="lo-rep"></div>
		<button type="button" class="button lo-rep-add">+ Add message</button>
		<?php
	}
}
