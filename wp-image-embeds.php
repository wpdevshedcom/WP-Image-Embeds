<?php
/**
 * Plugin Name: WP Image Embeds
 * Plugin URI: http://wpdevshed.com/
 * Description: A simple way to passively build links by enabling webmasters and bloggers to embed images from your site (while linking back to you).
 * Version: 1.0
 * Author: WP Dev Shed
 * Author URI: http://wpdevshed.com/
 * Requires at least: 4.1
 * Tested up to: 4.3.1
 * License: GPL2
 *
 * Text Domain: wp-image-embeds
 * Domain Path: /languages
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Check if class already exist
if(! class_exists('WP_Image_Embeds')) :

/**
 * Main Image Embeds Class
 *
 * @class WP_Image_Embeds
 * @version	1.0
 */
final class WP_Image_Embeds {
	
	/**
	 * @var WP_Image_Embeds The single instance of the class
	 * @since 2.1
	 */
	protected static $_instance = null;
	
	/**
	 * Main WP_Image_Embeds Instance
	 *
	 * Ensures only one instance of WP_Image_Embeds is loaded or can be loaded.
	 *
	 * @since 2.1
	 * @static
	 * @see WC()
	 * @return WP_Image_Embeds - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Cloning is forbidden.
	 * @since  1.0
	 * @access public
	 * @return void
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wp-image-embeds' ), '1.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 * @since  1.0
	 * @access public
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wp-image-embeds' ), '1.0' );
	}

	/**
	 * Magic method to prevent a fatal error when calling a method that doesn't exist.
	 *
	 * @since  1.0
	 * @access public
	 * @return void
	 */
	public function __call( $method = '', $args = array() ) {
		_doing_it_wrong( "WP_Image_Embeds::{$method}", __( 'Method does not exist.', 'wp-image-embeds' ), '1.0' );
		unset( $method, $args );
		
		return null;
	}

	/**
	 * @desc	Construct the plugin object
	 */
	private function __construct()
	{
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}
	
	/**
	 * Define Constants
	 */
	private function define_constants() {
		$this->define( 'WPIE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		$this->define( 'WPIE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
		$this->define( 'WPIE_CSS_DIR', WPIE_PLUGIN_URL .'assets/css/' );
		$this->define( 'WPIE_JS_DIR', WPIE_PLUGIN_URL .'assets/js/' );
		$this->define( 'WPIE_LIB_PATH', WPIE_PLUGIN_PATH .'lib/' );
		$this->define( 'WPIE_INC_PATH', WPIE_PLUGIN_PATH .'inc/' );
	}
	
	/**
	 * Define constant if not already set
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}
	
	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		include_once( 'includes/wpie-actions.php' );
	}
	
	/**
	 * Hook into actions and filters
	 * @since  1.0
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ), 0 );
		
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_image_embeds_enqueue_scripts' ) );
		add_action( 'wp_footer', array( $this, 'wp_image_embeds_html_dialog' ) );
		
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'wp_image_embeds_action_links' ) );
	}
	
	/**
	 * Load Image_Embeds text_domain when WordPress Plugins loaded
	 */
	public function init() {
		// Before init action
		do_action( 'before_wpie_init' );

		// Set up localisation
		$this->load_plugin_textdomain();
		
		// Init action
		do_action( 'wpie_init' );
	}
	
	/**
	 * Load Localisation files.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wp-image-embeds', false, plugin_basename( dirname( __FILE__ ) ) . "/languages" );
	}
	
	/**
	 * Display diaglog box in footer area
	 */
	public function wp_image_embeds_html_dialog() {
		ob_start();
		
		$current_page_id	= get_queried_object_id();
		$current_post_type 	= get_post_type();
		
		$display_in_page 	= get_option( 'wpie_display_in_pages', 1 );
		$display_in_post 	= get_option( 'wpie_display_in_posts', 1 );
		$enable_hotlinked 	= get_post_meta( $current_page_id, '_cmb_enable_hotlinked', true );
		
		if( is_page() || is_single() )
			wp_enqueue_script( 'wp-image-embeds-handle', WPIE_JS_DIR . 'wpie-script.js', array('jquery'), '1.0.0', false );
		
		wp_localize_script( 'wp-image-embeds-handle', 'wpie_ajax', array(
			'page_permalink'	=> get_permalink( $current_page_id ),
			'page_title' 		=> get_the_title( $current_page_id ),
			'is_page_enable'	=> ( 1 == $display_in_page ) ? 'yes' : 'no',
			'is_post_enable'	=> ( 1 == $display_in_post ) ? 'yes' : 'no',
			'is_mata_enable'	=> ( 'on' == $enable_hotlinked ) ? 'yes' : 'no'
		) );

		
		if( $enable_hotlinked
			|| ( 'page' == $current_post_type && $display_in_page ) 
			|| ( 'post' == $current_post_type && $display_in_post ) 
		) {
			?>
			<div id="wp-image-embeds-dialogBoxWrapper" data-id=<?php echo $current_page_id; ?> style="position: fixed; bottom: 0; right: 0;">
				<div class="row">
					<div class="push-two eight columns">
						<div id="wp-image-embeds-popup">
							<div id="wp-image-embeds-popup-header">
								<a href="javascript: void(0);" title="Close" id="wp-image-embeds-close"><?php _e( 'close', 'wp-image-embeds' ); ?></a>
								
								<div class="popup-header-title">
									<?php _e( 'Embed this image', 'wp-image-embeds' ); ?>
								</div>
							</div>
							<div id="wp-image-embeds-content">
								<p><?php _e( 'Copy and paste this code to display the image on your site', 'wp-image-embeds' ); ?></p>
								<div id="wp-image-embeds-dialogBox"></div>					
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		
		echo ob_get_clean();
	}
	
	public function wp_image_embeds_action_links( $links ) {
		$links[] = '<a href="'. esc_url( get_admin_url( null, 'options-general.php?page=wpie_settings') ) .'">Settings</a>';
			
        return $links;
	}
	
	/**
	 * @desc	Register scripts
	 */
	public function wp_image_embeds_enqueue_scripts()
	{
		wp_enqueue_style( 'wp-image-embeds-style', WPIE_CSS_DIR . 'style.css' );
	}

}

endif;

/**
 * Returns the main instance of WPIE to prevent the need to use globals.
 *
 * @since  1.0
 * @return WP_Image_Embeds
 */
function WPIE() {
	return WP_Image_Embeds::instance();
}

// Global for backwards compatibility.
$GLOBALS['wpie'] = WPIE();
