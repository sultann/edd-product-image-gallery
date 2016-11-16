<?php
/**
 * Plugin Name: EDD Product Gallery
 * Plugin URI: #
 * Description: Adds JVZoo integration to Easy Digital Downloads to allow the automatic sending of a digital download link to a customer that makes a purchase through JVZoo
 * Version: 1.0
 * Author: WPFisher
 * Author URI: https://wpfisher.com
 */



class EDDProductGallery{

	/**
	 * EDDProductGallery constructor.
	 */
	public function __construct() {
		if ( ! defined( 'EDD_PRODUCT_GALLERY_DIR' ) ) {
			define( 'EDD_PRODUCT_GALLERY_DIR', plugin_dir_path( __FILE__ ) );
		}
		//include_once( EDD_JVZOO_PLUGIN_DIR . 'includes/settings.php' );
		include_once( EDD_PRODUCT_GALLERY_DIR . '/includes/metabox.php' );
		add_action( 'admin_footer', array($this, 'media_selector_print_scripts') );
	}

	function media_selector_print_scripts() {

		$my_saved_attachment_post_id = get_option( 'media_selector_attachment_id', 0 );

		?><script type='text/javascript'>
			jQuery( document ).ready( function( $ ) {
				console.log('Working');
				// Uploading files
				var file_frame;
				var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
				var set_to_post_id = <?php echo $my_saved_attachment_post_id; ?>; // Set this
				jQuery('#edd_product_gallery').on('click', function( event ){
					console.log('CLicked');
					event.preventDefault();
					// If the media frame already exists, reopen it.
					if ( file_frame ) {
						// Set the post ID to what we want
						file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
						// Open frame
						file_frame.open();
						return;
					} else {
						// Set the wp.media post id so the uploader grabs the ID we want when initialised
						wp.media.model.settings.post.id = set_to_post_id;
					}
					// Create the media frame.
					file_frame = wp.media.frames.file_frame = wp.media({
						title: 'Select a image to upload',
						button: {
							text: 'Use this image',
						},
						multiple: true	// Set to true to allow multiple files to be selected
					});
					// When an image is selected, run a callback.
					file_frame.on( 'select', function() {
						// We set multiple to false so only get one image from the uploader
						attachment = file_frame.state().get('selection').first().toJSON();
						// Do something with attachment.id and/or attachment.url here
						$( '#image-preview' ).attr( 'src', attachment.url ).css( 'width', 'auto' );
						$( '#image_attachment_id' ).val( attachment.id );
						// Restore the main post ID
						wp.media.model.settings.post.id = wp_media_post_id;
					});
					// Finally, open the modal
					file_frame.open();
				});
				// Restore the main ID when the add media button is pressed
				jQuery( 'a.add_media' ).on( 'click', function() {
					wp.media.model.settings.post.id = wp_media_post_id;
				});
			});
		</script><?php
	}
}

new EDDProductGallery();