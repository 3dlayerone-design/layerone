<?php
/**
 * Product edit screen: the "Flexi Crew" box (kind, figure details, bundle link).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes_product', function () {
	add_meta_box( 'layerone-flexi', 'Flexi Crew', 'layerone_flexi_metabox', 'product', 'normal', 'high' );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || 'product' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'layerone-admin-flexi', get_template_directory_uri() . '/assets/js/admin-flexi.js', array( 'jquery' ), LAYERONE_VERSION, true );
} );

function layerone_flexi_media_field( $name, $label, $value, $type ) {
	$url  = $value ? wp_get_attachment_url( (int) $value ) : '';
	$file = $url ? basename( $url ) : '';
	?>
	<p class="form-field lo-media" data-type="<?php echo esc_attr( $type ); ?>">
		<label><?php echo esc_html( $label ); ?></label>
		<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (int) $value ); ?>">
		<span class="lo-media-name"><?php echo $file ? esc_html( $file ) : 'None'; ?></span>
		<button type="button" class="button lo-media-pick">Choose</button>
		<button type="button" class="button-link lo-media-clear"<?php echo $value ? '' : ' hidden'; ?>>Remove</button>
	</p>
	<?php
}

function layerone_flexi_metabox( $post ) {
	wp_nonce_field( 'layerone_flexi_save', 'layerone_flexi_nonce' );
	$id   = $post->ID;
	$kind = layerone_flexi_kind( $id );
	$term = (int) get_post_meta( $id, '_lo_bundle_term', true );
	$bundles = get_terms( array( 'taxonomy' => LAYERONE_FLEXI_TAX, 'parent' => 0, 'hide_empty' => false ) );
	?>
	<style>
		#layerone-flexi .form-field{display:flex;gap:10px;align-items:center;margin:10px 0}
		#layerone-flexi .form-field>label{width:180px;margin:0}
		#layerone-flexi textarea{width:100%;max-width:560px}
		#layerone-flexi .lo-block{border-top:1px solid #dcdcde;margin-top:12px;padding-top:6px}
	</style>
	<p class="form-field">
		<label for="lo_kind">What is this product?</label>
		<select name="lo_kind" id="lo_kind">
			<option value="">An ordinary product</option>
			<option value="figure" <?php selected( $kind, 'figure' ); ?>>A Flexi Crew figure</option>
			<option value="bundle" <?php selected( $kind, 'bundle' ); ?>>A pre-built bundle</option>
			<option value="box" <?php selected( $kind, 'box' ); ?>>The pick-your-own box</option>
		</select>
	</p>

	<div class="lo-block" data-kind="figure">
		<p class="description">Tick the figure's series in the "Bundles & series" box on the right. Its bundle follows from the series. Set its price and sale price in Product data as usual; figures never show in the shop.</p>
		<p class="form-field"><label for="lo_code">Figure code</label><input type="text" id="lo_code" name="lo_code" value="<?php echo esc_attr( get_post_meta( $id, '_lo_code', true ) ); ?>" placeholder="S01-06-burbu"> <span class="description">Same as the SKU.</span></p>
		<p class="form-field"><label for="lo_no">Figure number</label><input type="number" id="lo_no" name="lo_no" min="0" value="<?php echo esc_attr( get_post_meta( $id, '_lo_no', true ) ); ?>"></p>
		<p class="form-field"><label for="lo_animal">Animal</label><input type="text" id="lo_animal" name="lo_animal" value="<?php echo esc_attr( get_post_meta( $id, '_lo_animal', true ) ); ?>" placeholder="Seahorse"></p>
		<p class="form-field" style="align-items:flex-start"><label for="lo_story">Their story</label><textarea id="lo_story" name="lo_story" rows="4"><?php echo esc_textarea( get_post_meta( $id, '_lo_story', true ) ); ?></textarea></p>
		<p class="form-field" style="align-items:flex-start"><label for="lo_lesson">Lesson (optional)</label><textarea id="lo_lesson" name="lo_lesson" rows="2"><?php echo esc_textarea( get_post_meta( $id, '_lo_lesson', true ) ); ?></textarea></p>
		<?php
		layerone_flexi_media_field( 'lo_video_mp4', 'Video (MP4)', get_post_meta( $id, '_lo_video_mp4', true ), 'video' );
		layerone_flexi_media_field( 'lo_video_webm', 'Video (WebM, optional)', get_post_meta( $id, '_lo_video_webm', true ), 'video' );
		?>
		<p class="description">The product's featured image is the figure's photo.</p>
	</div>

	<div class="lo-block" data-kind="bundle">
		<p class="description">The featured image is the bundle banner, the short description is its one-line tagline. The bundle's figures are all figures in the bundle's series. It shows "Sold out" while any of them is out of stock.</p>
		<p class="form-field">
			<label for="lo_bundle_term">Bundle</label>
			<select name="lo_bundle_term" id="lo_bundle_term">
				<option value="0">Choose a bundle…</option>
				<?php foreach ( is_wp_error( $bundles ) ? array() : $bundles as $b ) : ?>
					<option value="<?php echo (int) $b->term_id; ?>" <?php selected( $term, $b->term_id ); ?>><?php echo esc_html( $b->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p class="form-field"><label for="lo_badge">Badge (optional)</label><input type="text" id="lo_badge" name="lo_badge" value="<?php echo esc_attr( get_post_meta( $id, '_lo_badge', true ) ); ?>" placeholder="Launch pick"></p>
		<?php if ( 'bundle' === $kind && $term ) : ?>
			<p class="description"><?php echo (int) count( layerone_flexi_bundle_figure_ids( $id ) ); ?> figures in this bundle right now.</p>
		<?php endif; ?>
	</div>

	<div class="lo-block" data-kind="box">
		<p class="description">The single product behind every pick-your-own box. Its price comes from WooCommerce &gt; Flexi Crew (box sizes), not from this page.</p>
	</div>
	<?php
}

function layerone_flexi_save_product( $post_id ) {
	if ( ! isset( $_POST['layerone_flexi_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['layerone_flexi_nonce'] ) ), 'layerone_flexi_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	$kind = isset( $_POST['lo_kind'] ) ? sanitize_key( wp_unslash( $_POST['lo_kind'] ) ) : '';
	$kind = in_array( $kind, array( 'figure', 'bundle', 'box' ), true ) ? $kind : '';
	if ( $kind ) {
		update_post_meta( $post_id, '_lo_kind', $kind );
	} else {
		delete_post_meta( $post_id, '_lo_kind' );
	}

	$text = function ( $key, $meta ) use ( $post_id ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $meta, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	};
	$text( 'lo_code', '_lo_code' );
	$text( 'lo_animal', '_lo_animal' );
	$text( 'lo_badge', '_lo_badge' );
	foreach ( array( 'lo_story' => '_lo_story', 'lo_lesson' => '_lo_lesson' ) as $key => $meta ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $meta, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
	foreach ( array( 'lo_no' => '_lo_no', 'lo_bundle_term' => '_lo_bundle_term', 'lo_video_mp4' => '_lo_video_mp4', 'lo_video_webm' => '_lo_video_webm' ) as $key => $meta ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $meta, absint( $_POST[ $key ] ) );
		}
	}

	layerone_flexi_hide_figure( $post_id );
}
add_action( 'save_post_product', 'layerone_flexi_save_product' );
