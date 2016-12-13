<?php
/**
 * Plugin Name: EDD Product Gallery
 * Plugin URI: https://pluginever.com
 * Description: Adds a product gallery for EDD (easy digital downloads ).
 * Version: 1.0
 * Author: manikmist09
 * Author URI: https://pluginever.com
 */



class EDDProductGallery{

	/**
	 * EDDProductGallery constructor.
	 */
	public function __construct() {
		if ( ! defined( 'EDD_PRODUCT_GALLERY_DIR' ) ) {
			define( 'EDD_PRODUCT_GALLERY_DIR', plugin_dir_path( __FILE__ ) );
		}

		include_once( EDD_PRODUCT_GALLERY_DIR . '/includes/metabox.php' );
		add_action( 'admin_footer', array($this, 'media_selector_print_scripts') );
		add_action( 'save_post', array($this, 'save_edd_product_gallery'));
//		add_action( 'edd_checkout_cart_item_title_after', array($this, 'post_custom_gallery'));
		add_action( 'post_thumbnail_html', array($this, 'my_post_thumbnail_fallback'),20, 5 );
		add_action( 'wp_enqueue_scripts', array($this, 'edd_product_gallery_scripts'),20, 5 );
		add_action( 'admin_enqueue_scripts', array($this, 'edd_product_gallery_admin_scripts'),20, 5 );
		add_shortcode('edd_product_gallery', array($this, 'edd_product_gallery_shortcode_callback'));

	}

	/**
	 * Adds all the required scripts for the plugin
	 *
	 */
	function edd_product_gallery_scripts() {
		wp_enqueue_style('edd-product-gallery', plugin_dir_url( __FILE__ ) . 'assets/css/edd-product-gallery.css' );
		wp_enqueue_style('swipebox', plugin_dir_url( __FILE__ ) . 'assets/css/swipebox.min.css' );
		wp_enqueue_script('swipebox', plugin_dir_url( __FILE__ ) . 'assets/js/jquery.swipebox.min.js', array('jquery'), '', false );
	}

	/**
	 * Add admin specific script
	 * @param $hook
	 */
	function edd_product_gallery_admin_scripts($hook){
		if(!in_array($hook, array('post.php', 'post-new.php'))) {
			return;
		}
		wp_enqueue_script('edd-product-gallery-admin-js', plugin_dir_url( __FILE__ ) . 'admin/edd-product-gallery.js', array('jquery'), '', false );

	}

	/**
	 * @param $var
	 * clean the variables
	 * @return array|string
	 */
	function edd_clean( $var ) {
		if ( is_array( $var ) ) {
			return array_map( 'edd_clean', $var );
		} else {
			return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
		}
	}

	/**
	 * @param $post_id
	 * Saves the gallery data
	 * @return mixed
	 */
	function save_edd_product_gallery($post_id){
		global  $post;
		if( ! current_user_can( 'edit_product', $post_id ) ) return $post_id;
		$attachment_ids = isset( $_POST['product_image_gallery'] ) ? array_filter( explode( ',', $this->edd_clean( $_POST['product_image_gallery'] ) ) ) : array();
		if($attachment_ids){
			update_post_meta( $post_id, '_product_image_gallery', implode( ',', $attachment_ids ) );
		}

		if((isset( $_POST['edd_product_gallery_settings'] )) && (in_array($_POST['edd_product_gallery_settings'], array('default', 'shortcode')))){

			update_post_meta( $post_id, '_edd_product_gallery_settings', esc_attr($_POST['edd_product_gallery_settings']) );
		}

	}


	/**
	 * @param $html
	 * @param $post_id
	 * @param $post_thumbnail_id
	 * @param $size
	 * @param $attr
	 *
	 * @return string|void
	 */
	function my_post_thumbnail_fallback( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		$images_html = '';
		$post_type = get_post_type($post_id);
		$image_ids = get_post_meta($post_id, '_product_image_gallery', true);
		if(($post_type  == 'download') && (is_single()) && ($image_ids  !== '')){
			$edd_settings = '';
			$edd_settings = get_post_meta($post_id, '_edd_product_gallery_settings', true);
			if($edd_settings == 'shortcode'){
				return;
			}
			$images_html = $this->generate_edd_product_gallery($post_id);
		}

		$images_html = $html.$images_html;

		return $images_html;
	}


	/**
	 * @param $atts
	 *
	 * @return string|void
	 */
	function edd_product_gallery_shortcode_callback($atts){
		global $post;
		$ar = shortcode_atts(array(
			'id' => $post->ID,
		), $atts);
		$images_html = '';
		$post_id = $ar['id'];
		if(empty($post_id)){
			return;
		}
		$post_type = get_post_type($post_id);
		$image_ids = get_post_meta($post_id, '_product_image_gallery', true);
		if(($post_type  == 'download') && ($image_ids  !== '')){
			$images_html = $this->generate_edd_product_gallery($post_id);
		}


		return $images_html;


	}

	/**
	 * @param $post_id
	 *
	 * @return string
	 */
	function generate_edd_product_gallery($post_id){
		$post_type = get_post_type($post_id);
		$image_ids = get_post_meta($post_id, '_product_image_gallery', true);
		$images_html = '';
		if(($post_type  == 'download') && ($image_ids  !== '') && (is_single())){
			$image_ids = explode(',',$image_ids);
			$images_html .= '<div id="edd-gallery-images">';
			$gallery_id = strtolower(wp_generate_password(5, false));
			foreach ($image_ids as $image_id){
				$images_html .= '<a class="image-item swipebox" rel="gallery-'.$gallery_id.'" href="'.wp_get_attachment_image_url($image_id, 'large').'">';
				$images_html .= wp_get_attachment_image($image_id, array('180', '180'));
				$images_html .= '</a>';
			}
			$images_html .= '</div>';
		}
		return $images_html;

	}


	/**
	 * Admin Style
	 */
	function media_selector_print_scripts() {
		?>
		<style>
			.product_images{
				margin: 0;
			}
			#product_images_container {
				display: block;
				overflow: hidden;
			}
			#product_images_container ul li.image{
				width: 80px;
				float: left;
				cursor: move;
				border: 1px solid #d5d5d5;
				margin: 9px 3px 0 0;
				background: #f7f7f7;
				border-radius: 2px;
				position: relative;
				box-sizing: border-box;
			}
			#product_images_container ul li.image img{
				width: 100%;
				height: auto;
				display: block;
			}

			#product_images_container ul ul.actions li a.delete {
				display: block;
				text-indent: -9999px;
				position: relative;
				height: 1em;
				width: 1em;
				font-size: 1.4em;
			}
			#product_images_container ul ul.actions {
				position: absolute;
				top: -8px;
				right: -8px;
				padding: 2px;
				display: none;
			}
			.inside {
				margin: 0;
				padding: 0;
			}
			.inside .add_product_images {
				/*padding: 0 12px 12px;*/
			}
			.add_product_images{
				clear: both;
				display: block;
				margin: 18px 0;
				padding: 0;
			}
			#product_images_container ul ul.actions li {
				float: right;
				margin: 0 0 0 2px
			}
			#product_images_container ul ul.actions li a {
				width: 1em;
				margin: 0;
				height: 0;
				display: block;
				overflow: hidden
			}
			#product_images_container ul ul.actions li a.tips {
				cursor: pointer
			}
		</style>

		<?php
	}

}

new EDDProductGallery();