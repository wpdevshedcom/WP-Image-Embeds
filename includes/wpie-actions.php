<?php
/* Fire our meta box setup function on the post editor screen. */
add_action( 'load-post.php', 'wpie_post_meta_boxes_setup' );
add_action( 'load-post-new.php', 'wpie_post_meta_boxes_setup' );


/* Meta box setup function. */
function wpie_post_meta_boxes_setup() {
	/* Add meta boxes on the 'add_meta_boxes' hook. */
	add_action( 'add_meta_boxes', 'wpie_add_post_meta_boxes' );
	
	/* Save post meta on the 'save_post' hook. */
	add_action( 'save_post', 'wpie_save_meta_box_data' );
}


/* Create one or more meta boxes to be displayed on the post editor screen. */
function wpie_add_post_meta_boxes() {
	$screens = array( 'post', 'page' );

	foreach ( $screens as $screen ) {

		add_meta_box(
			'wpie_sectionid',
			__( 'Image Embed Settings', 'wp-image-embeds' ),
			'wpie_meta_box_callback',
			$screen
		);
	}
}

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function wpie_meta_box_callback( $post ) {

	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'wpie_save_meta_box_data', '_cmb_meta_box_nonce' );

	/*
	 * Use get_post_meta() to retrieve an existing value
	 * from the database and use the value for the form.
	 */
	$enable_hotlinked = get_post_meta( $post->ID, '_cmb_enable_hotlinked', true );
	
	?>
	<table class="form-table cmb_metabox">
		<tbody>
			<tr class="cmb-type-checkbox _cmb_enable_hotlinked">
				<th style="width: 18%"><label for="enable_hotlinked"><?php _e( 'Enable Image Embed', 'wp-image-embeds' ); ?></label></th>
				<td>
					<input type="checkbox" class="cmb_option cmb_list" name="enable_hotlinked" id="enable_hotlinked" value="on" <?php checked( $enable_hotlinked, 'on' ); ?> />
					<label for="enable_hotlinked">
						<span class="cmb_metabox_description" style="float: right; width: 94%;">
							<?php _e( 'Check this to enable the WP Image Embed plugin on this page even if you have disabled on pages at the global level. <a href="'. esc_url( get_admin_url( null, 'options-general.php?page=wpie_settings') ) .'">Click here for global settings</a>', 'wp-image-embeds' ); ?>
						</span>
					</label>
				</td>
			</tr>
		</tbody>
	</table>
	
	<?php
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function wpie_save_meta_box_data( $post_id ) {

	/*
	 * We need to verify this came from our screen and with proper authorization,
	 * because the save_post action can be triggered at other times.
	 */

	// Check if our nonce is set.
	if ( ! isset( $_POST['_cmb_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['_cmb_meta_box_nonce'], 'wpie_save_meta_box_data' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	/* OK, it's safe for us to save the data now. */
	
	// Sanitize user input.
	$enable_hotlinked = sanitize_text_field( $_POST['enable_hotlinked'] );
	
	// Update the meta field in the database.
	update_post_meta( $post_id, '_cmb_enable_hotlinked', $enable_hotlinked );
}



/**
 * Add new options settings for WP Image Embeds
 */

function wpie_admin_add_page() {
	add_options_page(
		__( 'Embed Settings', 'wp-image-embeds' ),
		__( 'WP Image Embed', 'wp-image-embeds' ),
		'manage_options',
		'wpie_settings',
		'wpie_options_page'
	);
}
add_action( 'admin_menu', 'wpie_admin_add_page' );


function wpie_options_page() {
	?>
		<form action="options.php" method="post">
			<?php settings_fields( 'wpie_plugin_options' ); ?>
			<?php do_settings_sections( 'wpie_section' ); ?>
		 
			<input name="Submit" type="submit" class="button button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
		</form> 
	<?php
}
 
function wpie_settings_api_init() {	
	register_setting( 'wpie_plugin_options', 'wpie_display_in_posts', 'intval' );
 	register_setting( 'wpie_plugin_options', 'wpie_display_in_pages', 'intval' );
	
	add_settings_section(
		'wpie_setting_section',
		__( 'Display Image Embed Settings', 'wp-image-embeds' ),
		'wpie_setting_section_callback_function',
		'wpie_section'
	);
	
 	add_settings_field(
		'wpie_setting-id',
		__( 'Display Image Embed', 'wp-image-embeds' ),
		'wpie_setting_callback_function',
		'wpie_section',
		'wpie_setting_section'
	);
} 
add_action( 'admin_init', 'wpie_settings_api_init' );

function wpie_setting_section_callback_function() {
	echo '<p>'. __( 'Option to enable or disable the Image Embed on posts and/or pages', 'wp-image-embeds' ) .'</p>';
}

function wpie_setting_callback_function() {
	echo '
		<p>
			<label><input type="checkbox" name="wpie_display_in_posts" id="wpie_display_in_posts" value="1" ' . checked( 1, get_option( 'wpie_display_in_posts' ), false ) . ' /> '. __( 'Enable Embed on Posts?', 'wp-image-embeds' ) .' </label>
		</p>
		<p>	
			<label><input type="checkbox" name="wpie_display_in_pages" id="wpie_display_in_pages" value="1" ' . checked( 1, get_option( 'wpie_display_in_pages' ), false ) . ' /> '. __( 'Enable Embed on Pages?', 'wp-image-embeds' ) .' </label>
		</p>
	';
}