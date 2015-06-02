<?php
/**
 * Include and setup custom metaboxes and fields.
 *
 * @category YourThemeOrPlugin
 * @package  Metaboxes
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     https://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress
 */

add_filter( 'cmb_meta_boxes', 'ih_sample_metaboxes' );
/**
 * Define the metabox and field configurations.
 *
 * @param  array $meta_boxes
 * @return array
 */
function ih_sample_metaboxes( array $meta_boxes ) {

	// Start with an underscore to hide fields from custom fields list
	$prefix = '_cmb_';
	
	$meta_boxes['page_embed_metabox'] = array(
		'id'         => 'page-embed-meta',
		'title'      => __( 'Image Embed Settings', 'cmb' ),
		'pages'      => array( 'page' ), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		// 'cmb_styles' => true, // Enqueue the CMB stylesheet on the frontend
		'fields'     => array(
			array(
				'name' 		=> __( 'Enable Image Embed', 'cmb' ),
				'desc' 		=> __( 'Check this to enable the WP Image Embed plugin on this page even if you have disabled on pages at the global level', 'cmb' ),
				'id'   		=> $prefix . 'enable_hotlinked',
				'type' 		=> 'checkbox'
			),
		),
	);

	$meta_boxes['post_embed_metabox'] = array(
		'id'         => 'post-embed-meta',
		'title'      => __( 'Image Embed Settings', 'cmb' ),
		'pages'      => array( 'post' ), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		// 'cmb_styles' => true, // Enqueue the CMB stylesheet on the frontend
		'fields'     => array(
			array(
				'name' 		=> __( 'Enable Image Embed', 'cmb' ),
				'desc' 		=> __( 'Check this to enable the WP Image Embed plugin on this post even if you have disabled on posts at the global level', 'cmb' ),
				'id'   		=> $prefix . 'enable_hotlinked',
				'type' 		=> 'checkbox'
			),
		),
	);
	
	
	return $meta_boxes;
}


add_action( 'init', 'ih_initialize_cmb_meta_boxes', 9999 );
/**
 * Initialize the metabox class.
 */
function ih_initialize_cmb_meta_boxes() {

	if ( ! class_exists( 'cmb_Meta_Box' ) )
		require_once IH_LIB_PATH . 'metabox/meta/init.php';

}