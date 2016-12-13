<?php
/**
 * Add the JVZoo Meta Box
 *
 * @since 1.0
 */
function wpf_edd_product_gallery() {
	add_meta_box( 'edd_product_gallery_settings', __( 'EDD Product Gallery Settings', 'edd_product_gallery_settings' ), 'wpf_edd_product_gallery_settings_meta_box', 'download', 'side', 'core' );
	add_meta_box( 'edd_product_gallery', __( 'EDD Product Gallery', 'edd_product_gallery' ), 'wpf_edd_product_gallery_meta_box', 'download', 'side', 'core' );

}
add_action( 'add_meta_boxes', 'wpf_edd_product_gallery', 100 );


/**
 * Render the JVZoo information meta box
 *
 * @since 1.0
 */
function wpf_edd_product_gallery_meta_box() {
	global $post;
	?>
	<div id="product_images_container">
		<ul class="product_images">
			<?php
			if ( metadata_exists( 'post', $post->ID, '_product_image_gallery' ) ) {
				$product_image_gallery = get_post_meta( $post->ID, '_product_image_gallery', true );
			} else {
				// Backwards compat
				$attachment_ids = get_posts( 'post_parent=' . $post->ID . '&numberposts=-1&post_type=attachment&orderby=menu_order&order=ASC&post_mime_type=image&fields=ids&meta_key=_woocommerce_exclude_image&meta_value=0' );
				$attachment_ids = array_diff( $attachment_ids, array( get_post_thumbnail_id() ) );
				$product_image_gallery = implode( ',', $attachment_ids );
			}

			$attachments         = array_filter( explode( ',', $product_image_gallery ) );
			$update_meta         = false;
			$updated_gallery_ids = array();

			if ( ! empty( $attachments ) ) {
				foreach ( $attachments as $attachment_id ) {
					$attachment = wp_get_attachment_image( $attachment_id, 'thumbnail' );

					// if attachment is empty skip
					if ( empty( $attachment ) ) {
						$update_meta = true;
						continue;
					}

					echo '<li class="image" data-attachment_id="' . esc_attr( $attachment_id ) . '">
								' . $attachment . '
								<ul class="actions">
									<li><a href="#" class="delete tips" data-tip="' . esc_attr__( 'Delete image', 'woocommerce' ) . '"><i alt="f153" class="dashicons dashicons-dismiss">dismiss</i></a></li>
								</ul>
							</li>';

					// rebuild ids to be saved
					$updated_gallery_ids[] = $attachment_id;
				}

				// need to update product meta to set new gallery ids
				if ( $update_meta ) {
					update_post_meta( $post->ID, '_product_image_gallery', implode( ',', $updated_gallery_ids ) );
				}
			}
			?>
		</ul>
		<input type="hidden" id="product_image_gallery" name="product_image_gallery" value="<?php echo esc_attr( $product_image_gallery ); ?>" />
	</div>
	<p class="add_product_images hide-if-no-js">
		<a href="#" data-choose="<?php esc_attr_e( 'Add Images to Product Gallery', 'woocommerce' ); ?>" data-update="<?php esc_attr_e( 'Add to gallery', 'woocommerce' ); ?>" data-delete="<?php esc_attr_e( 'Delete image', 'woocommerce' ); ?>" data-text="<?php esc_attr_e( 'Delete', 'woocommerce' ); ?>"><?php _e( 'Add product gallery images', 'woocommerce' ); ?></a>
	</p>
	<?php
}

function wpf_edd_product_gallery_settings_meta_box(){
	$selected = '';
	global $post;
	$selected = get_post_meta($post->ID, '_edd_product_gallery_settings', true);
	?>
	<select name="edd_product_gallery_settings" id="edd_product_gallery_settings" style="width: 98%;">
		<option value="default" <?php echo ($selected == 'default')? 'selected="selected"': '';?>>Default</option>
		<option value="shortcode" <?php echo ($selected == 'shortcode')? 'selected="selected"': '';?>>Use Short Code</option>
	</select>
	<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="By Default product gallery thumbnails will be visible under the main product image. But if you wish to disable that and want in custom location then select short code from the above dropdown and use <code>[edd_product_gallery]</code> shortcode."></span>
	<p class="description" id="edd_product_gallery_settings-description">To place product gallery in custom location select short code from above dropdown and use <code>[edd_product_gallery]</code> shortcode.</p>
	<?php
}