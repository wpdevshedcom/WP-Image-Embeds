<?php

/**
 * Plugin Name: WP Image Embeds
 * Plugin URI: http://wpdevshed.com/
 * Description: A simple way to passively build links by enabling webmasters and bloggers to embed images from your site (while linking back to you).
 * Version: 1.0
 * Author: WP Dev Shed
 * Author URI: http://wpdevshed.com/
 * License: GPL2
 */

define( 'IH_DOMAIN_NAME', 'rs-client-portal' );
define( 'IH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'IH_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'IH_CSS_DIR', IH_PLUGIN_URL .'assets/css/' );
define( 'IH_JS_DIR', IH_PLUGIN_URL .'assets/js/' );
define( 'IH_LIB_PATH', IH_PLUGIN_PATH .'lib/' );


// Check if class already exist
if(! class_exists('RS_Image_Hotlinked'))
{
	class RS_Image_Hotlinked
	{
		/**
		 * @desc	Construct the plugin object
		 */
		function __construct()
		{
			// include required files
			$this->include_files();

			// register plugin init
			add_action( 'init', array( $this, 'init') );
			
			// enqueue styles/scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'ih_plugin_enqueue_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'ih_plugin_enqueue_scripts' ) );
			
			add_action( 'customize_register', array( $this, 'ih_image_hotlinked_plugin_customizer' ) );

			add_action( 'wp_footer', array( $this, 'dialog_html_form' ) );
		}


		function include_files() {
			include_once( IH_LIB_PATH . 'metabox/init.php' );
		}

		function init() {
			global $post;	
		}
		
		/**
		 * Display diaglog box in footer area
		 */
		function dialog_html_form() {
			global $post;

			ob_start();
			
			$image_embed = '';
			$image_embed_html_filter = '';
			$current_post_type = get_post_type();
			
			$display_in_page = get_theme_mod( 'image_hotlinked_display_in_page' );
			$display_in_post = get_theme_mod( 'image_hotlinked_display_in_posts' );
			echo $is_mata_enable  = get_post_meta( $post->ID, '_cmb_enable_hotlinked', true );

			wp_enqueue_script( 'ih-handle', IH_JS_DIR . 'ih-script.js', array('jquery'), '1.0.0', false );
			wp_localize_script( 'ih-handle', 'ih_ajax', array(
				'page_permalink'	=> get_permalink( $post->ID ),
				'page_title' 		=> get_the_title( $post->ID ),
				'is_page_enable'	=> 1 == $display_in_page ? 'yes' : 'no',
				'is_post_enable'	=> 1 == $display_in_post ? 'yes' : 'no',
				'is_mata_enable'	=> 'on' == $is_mata_enable ? 'yes' : 'no'
			) );


			$enable_hotlinked = get_post_meta( $post->ID, '_cmb_enable_hotlinked', true );
			if( $enable_hotlinked
				|| ( 'page' == $current_post_type && $display_in_page ) 
				|| ( 'post' == $current_post_type && $display_in_post ) 
			) {
				?>
				<div id="rs-ih-dialogBoxWrapper" data-id=<?php echo $post->ID; ?> style="position: fixed; bottom: 0; right: 0;">
					<div class="row">
						<div class="push-two eight columns">
							<div id="rs-ih-popup">
								<div id="rs-ih-popup-header">
									<a href="javascript: void(0);" title="Close" id="ih_close">close</a>
									
									<div class="popup-header-title">
										Embed this image
									</div>
								</div>
								<div id="rs-ih-content">
									<p>Copy and paste this code to display the image on your site</p>
									<div id="rs-ih-dialogBox"></div>					
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
			} // close if( $enable_hotlinked )
			
			$image_embed = ob_get_contents();
			ob_end_clean();

			//echo 'Post: ' . get_theme_mod( 'image_hotlinked_display_in_posts' );
			//echo 'Page: ' . get_theme_mod( 'image_hotlinked_display_in_page' );

			// check if post or page are enabled
			/*if( ( 'post' == $current_post_type && get_theme_mod( 'image_hotlinked_display_in_posts' ) ) 
				|| ( 'page' == $current_post_type && get_theme_mod( 'image_hotlinked_display_in_page' ) ) 
				) {
				$image_embed_html_filter = $image_embed;
			}

			echo $image_embed_html_filter;*/

			echo $image_embed;
		}
		
		
		/*
		 * Load customize object
		 */
		function ih_image_hotlinked_plugin_customizer( $wp_customize ) {
			/* category link in homepage option */
			$wp_customize->add_section( 'image_hotlinked_display_section' , array(
				'title'       => __( 'Display Image Embed', 'image_embed' ),
				'priority'    => 34,
				'description' => __( 'Option to enable or disable the Image Embed on posts and/or pages', 'image_embed' ),
			) );

			$wp_customize->add_setting( 'image_hotlinked_display_in_posts', array (
				'default' 	=> 1,
				'sanitize_callback' => array( $this, 'ih_social_share_sanitize_checkbox' ),
			) );
			$wp_customize->add_control('image_hotlinked_display_in_posts', array(
				'label' 	=> __('Enable Embed on Posts?', 'image_embed'),
				'section' 	=> 'image_hotlinked_display_section',
				'settings' 	=> 'image_hotlinked_display_in_posts',
				'type' 		=> 'checkbox',
			));

			$wp_customize->add_setting( 'image_hotlinked_display_in_page', array (
				'default' 	=> 1,
				'sanitize_callback' => array( $this, 'ih_social_share_sanitize_checkbox' ),
			) );
			$wp_customize->add_control('image_hotlinked_display_in_page', array(
				'label' 	=> __( 'Enable Embed on Pages?', 'image_embed' ),
				'section' 	=> 'image_hotlinked_display_section',
				'settings' 	=> 'image_hotlinked_display_in_page',
				'type' 		=> 'checkbox',
			));
		}
		
		/**
		 * Sanitize checkbox
		 */
		function ih_social_share_sanitize_checkbox( $input ) {
			if ( $input == 1 ) {
				return 1;
			} else {
				return '';
			}
		}

		/**
		 * @desc	Register styles
		 */
		function ih_plugin_enqueue_styles()
		{
			wp_enqueue_style( 'woo-style', IH_CSS_DIR . 'style.css' );
		}
		
		/**
		 * @desc	Register scripts
		 */
		function ih_plugin_enqueue_scripts()
		{
			if( is_page() || is_single() )
				wp_enqueue_script( 'script-name', IH_JS_DIR . 'script.js', array('jquery'), '1.0.0', false );
		}

	}
}

new RS_Image_Hotlinked();