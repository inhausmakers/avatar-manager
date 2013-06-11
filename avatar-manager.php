<?php
/**
 * @package Avatar_Manager
 */
/*
Plugin Name: Avatar Manager
Plugin URI: http://wordpress.org/plugins/avatar-manager/
Description: Avatar Manager for WordPress is a sweet and simple plugin for storing avatars locally and more. Easily.
Version: 1.3.0
Author: Cătălin Dogaru
Author URI: http://cdog.dunked.com/
License: GPLv2 or later
*/

/*
Copyright © 2013 Cătălin Dogaru

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 2 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program; if not, write to the Free Software Foundation, Inc., 51 Franklin
Street, Fifth Floor, Boston, MA 02110-1301, USA.
*/

define( 'AVATAR_MANAGER_VERSION', '1.3.0' );
define( 'AVATAR_MANAGER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AVATAR_MANAGER_AVATAR_UPLOADS', 0 );
define( 'AVATAR_MANAGER_DEFAULT_SIZE', 96 );

/**
 * Sets up plugin defaults and makes Avatar Manager available for translation.
 *
 * @uses load_theme_textdomain() For translation/localization support.
 * @uses plugin_basename() For retrieving the basename of the plugin.
 *
 * @since Avatar Manager 1.0.0
 */
function avatar_manager_init() {
	// Makes Avatar Manager available for translation.
	load_plugin_textdomain( 'avatar-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'init', 'avatar_manager_init' );

/**
 * Registers sanitization callback and plugin setting fields.
 *
 * @uses register_setting() For registering a setting and its sanitization
 * callback.
 * @uses add_settings_field() For registering a settings field to a settings
 * page and section.
 * @uses __() For retrieving the translated string from the translate().
 *
 * @since Avatar Manager 1.0.0
 */
function avatar_manager_admin_init() {
	// Registers plugin setting and its sanitization callback.
	register_setting( 'discussion', 'avatar_manager', 'avatar_manager_sanitize_options' );

	// Registers Avatar Uploads settings field under the Settings Discussion
	// Screen.
	add_settings_field( 'avatar-manager-avatar_uploads', __( 'Avatar Uploads', 'avatar-manager' ), 'avatar_manager_avatar_uploads_settings_field', 'discussion', 'avatars' );

	// Registers Default Size settings field under the Settings Discussion
	// Screen.
	add_settings_field( 'avatar-manager-default-size', __( 'Default Size', 'avatar-manager' ), 'avatar_manager_default_size_settings_field', 'discussion', 'avatars' );
}

add_action( 'admin_init', 'avatar_manager_admin_init' );

/**
 * Returns plugin default options.
 *
 * @since Avatar Manager 1.0.0
 *
 * @return array Plugin default options.
 */
function avatar_manager_get_default_options() {
	$options = array(
		'avatar_uploads' => AVATAR_MANAGER_AVATAR_UPLOADS,
		'default_size'   => AVATAR_MANAGER_DEFAULT_SIZE
	);

	return $options;
}

/**
 * Returns plugin options.
 *
 * @uses get_option() For getting values for a named option.
 * @uses avatar_manager_get_default_options() For retrieving plugin default
 * options.
 *
 * @since Avatar Manager 1.0.0
 *
 * @return array Plugin options.
 */
function avatar_manager_get_options() {
	return get_option( 'avatar_manager', avatar_manager_get_default_options() );
}

/**
 * Sanitizes and validates plugin options.
 *
 * @uses avatar_manager_get_default_options() For retrieving plugin default
 * options.
 * @uses absint() For converting a value to a non-negative integer.
 *
 * @since Avatar Manager 1.0.0
 *
 * @return array Sanitized plugin options.
 */
function avatar_manager_sanitize_options( $input ) {
	$options = avatar_manager_get_default_options();

	if ( isset( $input['avatar_uploads'] ) && trim( $input['avatar_uploads'] ) )
		$options['avatar_uploads'] = trim( $input['avatar_uploads'] ) ? 1 : 0;

	if ( isset( $input['default_size'] ) && is_numeric( trim( $input['default_size'] ) ) ) {
		$options['default_size'] = absint( trim( $input['default_size'] ) );

		if ( $options['default_size'] < 1 )
			$options['default_size'] = 1;
		elseif ( $options['default_size'] > 512 )
			$options['default_size'] = 512;
	}

	return $options;
}

/**
 * Prints Avatar Uploads settings field.
 *
 * @uses avatar_manager_get_options() For retrieving plugin options.
 * @uses _e() For displaying the translated string from the translate().
 * @uses checked() For comparing two given values.
 *
 * @since Avatar Manager 1.0.0
 */
function avatar_manager_avatar_uploads_settings_field() {
	// Retrieves plugin options.
	$options = avatar_manager_get_options();
	?>
	<fieldset>
		<legend class="screen-reader-text">
			<span>
				<?php _e( 'Avatar Uploads', 'avatar-manager' ); ?>
			</span>
		</legend><!-- .screen-reader-text -->
		<label>
			<input <?php checked( $options['avatar_uploads'], 1, true ); ?> name="avatar_manager[avatar_uploads]" type="checkbox" value="1">
			<?php _e( 'Anyone can upload', 'avatar-manager' ); ?>
		</label>
	</fieldset>
	<?php
}

/**
 * Prints Default Size settings field.
 *
 * @uses avatar_manager_get_options() For retrieving plugin options.
 * @uses _e() For displaying the translated string from the translate().
 *
 * @since Avatar Manager 1.0.0
 */
function avatar_manager_default_size_settings_field() {
	// Retrieves plugin options.
	$options = avatar_manager_get_options();
	?>
	<fieldset>
		<legend class="screen-reader-text">
			<span>
				<?php _e( 'Default Size', 'avatar-manager' ); ?>
			</span>
		</legend><!-- .screen-reader-text -->
		<label>
			<?php _e( 'Default size of the avatar image', 'avatar-manager' ); ?>
			<input class="small-text" min="1" name="avatar_manager[default_size]" step="1" type="number" value="<?php echo $options['default_size']; ?>">
		</label>
	</fieldset>
	<?php
}

/**
 * Prints Avatar section.
 *
 * @uses avatar_manager_get_options() For retrieving plugin options.
 * @uses get_post_meta() For retrieving attachment meta fields.
 * @uses remove_filter() For removing a function attached to a specified action
 * hook.
 * @uses _e() For displaying the translated string from the translate().
 * @uses checked() For comparing two given values.
 * @uses get_avatar() For retrieving the avatar for a user.
 * @uses esc_attr() For escaping HTML attributes.
 * @uses add_query_arg() For retrieving a modified URL (with) query string.
 * @uses self_admin_url() For retrieving an admin url link with optional path
 * appended.
 * @uses current_user_can() For checking whether the current user has a certain
 * capability.
 * @uses submit_button() For echoing a submit button, with provided text and
 * appropriate class.
 * @uses __() For retrieving the translated string from the translate().
 *
 * @since Avatar Manager 1.0.0
 *
 * @param array $profileuser User to edit.
 */
function avatar_manager_edit_user_profile( $profileuser ) {
	// Retrieves plugin options.
	$options = avatar_manager_get_options();

	$avatar_type = isset( $profileuser->avatar_manager_avatar_type ) ? $profileuser->avatar_manager_avatar_type : 'gravatar';

	if ( isset( $profileuser->avatar_manager_custom_avatar ) ) {
		// Retrieves attachment meta fields based on attachment ID.
		$custom_avatar_rating   = get_post_meta( $profileuser->avatar_manager_custom_avatar, '_avatar_manager_custom_avatar_rating', true );
		$user_has_custom_avatar = get_post_meta( $profileuser->avatar_manager_custom_avatar, '_avatar_manager_is_custom_avatar', true );
	}

	if ( ! isset( $custom_avatar_rating ) || empty( $custom_avatar_rating ) )
		$custom_avatar_rating = 'G';

	if ( ! isset( $user_has_custom_avatar ) || empty( $user_has_custom_avatar ) )
		$user_has_custom_avatar = false;

	if ( $user_has_custom_avatar )
		// Removes the function attached to the specified action hook.
		remove_filter( 'get_avatar', 'avatar_manager_get_avatar' );
	?>
	<h3>
		<?php _e( 'Avatar', 'avatar-manager' ); ?>
	</h3>
	<table class="form-table">
		<tr>
			<th>
				<?php _e( 'Display this avatar', 'avatar-manager' ); ?>
			</th>
			<td class="avatar-manager">
				<fieldset>
					<legend class="screen-reader-text">
						<span>
							<?php _e( 'Display this avatar', 'avatar-manager' ); ?>
						</span><!-- .screen-reader-text -->
					</legend>
					<label>
						<input <?php checked( $avatar_type, 'gravatar', true ); ?> name="avatar_manager_avatar_type" type="radio" value="gravatar">
						<?php echo get_avatar( $profileuser->ID, 32, '', false ); ?>
						<?php _e( 'Gravatar', 'avatar-manager' ); ?>
						<span class="description">
							<?php _e( '<a href="http://codex.wordpress.org/How_to_Use_Gravatars_in_WordPress" target="_blank">More information</a>', 'avatar-manager' ); ?>
						</span><!-- .description -->
					</label>
					<?php if ( $user_has_custom_avatar ) : ?>
						<br>
						<label>
							<input <?php checked( $avatar_type, 'custom', true ); ?> name="avatar_manager_avatar_type" type="radio" value="custom">
							<?php echo avatar_manager_get_custom_avatar( $profileuser->ID, 32, '', false ); ?>
							<?php _e( 'Custom', 'avatar-manager' ); ?>
						</label>
					<?php endif; ?>
					<?php
					if ( is_admin() && $user_has_custom_avatar && ( current_user_can( 'upload_files' ) || $options['avatar_uploads'] )  ) {
						$href = esc_attr( add_query_arg( array(
							'action'                       => 'update',
							'avatar_manager_action'        => 'remove-avatar',
							'avatar_manager_custom_avatar' => $profileuser->avatar_manager_custom_avatar,
							'user_id'                      => $profileuser->ID
						),
						self_admin_url( IS_PROFILE_PAGE ? 'profile.php' : 'user-edit.php' ) ) );
						?>
						<a class="delete" href="<?php echo wp_nonce_url( $href, 'update-user_' . $profileuser->ID ); ?>" onclick="return showNotice.warn();">
							<?php _e( 'Delete', 'avatar-manager' ); ?>
						</a><!-- .delete -->
						<?php
					}
					?>
				</fieldset>
			</td><!-- .avatar-manager -->
		</tr>
		<?php if ( current_user_can( 'upload_files' ) || $options['avatar_uploads'] ) : ?>
			<tr>
				<th>
					<?php _e( 'Select Image', 'avatar-manager' ); ?>
				</th>
				<td>
					<fieldset>
						<legend class="screen-reader-text">
							<span>
								<?php _e( 'Select Image', 'avatar-manager' ); ?>
							</span>
						</legend><!-- .screen-reader-text -->
						<label class="description" for="avatar-manager-upload-avatar">
							<?php _e( 'Choose an image from your computer:', 'avatar-manager' ); ?>
						</label><!-- .description -->
						<br>
						<input name="avatar_manager_import" type="file">
						<input type="submit" name="avatar-manager-upload-avatar" id="avatar-manager-upload-avatar" class="button" value="<?php esc_attr_e( 'Upload', 'avatar-manager' ); ?>">
					</fieldset>
				</td>
			</tr>
		<?php endif; ?>
		<?php if ( $user_has_custom_avatar ) : ?>
			<tr>
				<th>
					<?php _e( 'Avatar Rating', 'avatar-manager' ); ?>
				</th>
				<td>
					<fieldset>
						<legend class="screen-reader-text">
							<span>
								<?php _e( 'Avatar Rating', 'avatar-manager' ); ?>
							</span>
						</legend><!-- .screen-reader-text -->
						<?php
						$ratings = array(
							// Translators: Content suitability rating:
							// http://bit.ly/89QxZA
							'G'  => __( 'G &#8212; Suitable for all audiences', 'avatar-manager' ),
							// Translators: Content suitability rating:
							// http://bit.ly/89QxZA
							'PG' => __( 'PG &#8212; Possibly offensive, usually for audiences 13 and above', 'avatar-manager' ),
							// Translators: Content suitability rating:
							// http://bit.ly/89QxZA
							'R'  => __( 'R &#8212; Intended for adult audiences above 17', 'avatar-manager' ),
							// Translators: Content suitability rating:
							// http://bit.ly/89QxZA
							'X'  => __( 'X &#8212; Even more mature than above', 'avatar-manager' )
						);

						foreach ( $ratings as $key => $rating ) {
							?>
							<label>
								<input <?php checked( $custom_avatar_rating, $key, true ); ?> name="avatar_manager_custom_avatar_rating" type="radio" value="<?php echo esc_attr( $key ); ?>">
								<?php echo $rating; ?>
							</label>
							<br>
							<?php
						}
						?>
						<span class="description">
							<?php _e( 'Choose a rating for your custom avatar.', 'avatar-manager' ); ?>
						</span><!-- .description -->
					</fieldset>
				</td>
			</tr>
		<?php endif; ?>
	</table><!-- .form-table -->
	<?php
}

add_action( 'show_user_profile', 'avatar_manager_edit_user_profile' );
add_action( 'edit_user_profile', 'avatar_manager_edit_user_profile' );

/**
 * Enqueues plugin scripts and styles for Users Your Profile Screen.
 *
 * @uses wp_register_style() For registering a CSS style file.
 * @uses wp_enqueue_style() For enqueuing a CSS style file.
 * @uses wp_register_script() For registering a JS script file.
 * @uses wp_enqueue_script() For enqueuing a JS script file.
 *
 * @since Avatar Manager 1.0.0
 */
function avatar_manager_admin_enqueue_scripts() {
	global $hook_suffix;

	if ( $hook_suffix == 'profile.php' || $hook_suffix == 'user-edit.php' || !is_admin() ) {
		// Registers plugin CSS style file.
		wp_register_style( 'avatar-manager.css', AVATAR_MANAGER_PLUGIN_URL . 'avatar-manager.css', array(), '1.0.0' );

		// Enqueues plugin CSS style file.
		wp_enqueue_style( 'avatar-manager.css');

		// Registers plugin JS script file.
		wp_register_script( 'avatar-manager.js', AVATAR_MANAGER_PLUGIN_URL . 'avatar-manager.js', array( 'jquery' ), '1.0.0' );

		// Enqueues plugin JS script file.
		wp_enqueue_script( 'avatar-manager.js' );
	}
}

add_action( 'admin_enqueue_scripts', 'avatar_manager_admin_enqueue_scripts' );
add_action( 'wp_enqueue_scripts', 'avatar_manager_admin_enqueue_scripts' );

/**
 * Generates a resized copy of the specified avatar image.
 *
 * @uses wp_upload_dir() For retrieving path information on the currently
 * configured uploads directory.
 * @uses wp_basename() For i18n friendly version of basename().
 * @uses wp_get_image_editor() For retrieving a WP_Image_Editor instance and
 * loading a file into it.
 * @uses is_wp_error() For checking whether the passed variable is a WordPress
 * Error.
 * @uses do_action() For calling the functions added to an action hook.
 *
 * @since Avatar Manager 1.0.0
 *
 * @param string $url URL of the avatar image to resize.
 * @param int $size Size of the new avatar image.
 * @return array Array with the URL of the new avatar image.
 */
function avatar_manager_avatar_resize( $url, $size ) {
	// Retrieves path information on the currently configured uploads directory.
	$upload_dir = wp_upload_dir();

	$filename  = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $url );
	$pathinfo  = pathinfo( $filename );
	$dirname   = $pathinfo['dirname'];
	$extension = $pathinfo['extension'];

	// i18n friendly version of basename().
	$basename = wp_basename( $filename, '.' . $extension );

	$suffix    = $size . 'x' . $size;
	$dest_path = $dirname . '/' . $basename . '-' . $suffix . '.' . $extension;
	$avatar    = array();

	if ( file_exists( $dest_path ) ) {
		$avatar['url']  = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $dest_path );
		$avatar['skip'] = true;
	} else {
		// Retrieves a WP_Image_Editor instance and loads a file into it.
		$image = wp_get_image_editor( $filename );

		if ( ! is_wp_error( $image ) ) {
			// Resizes current image.
			$image->resize( $size, $size, true );

			// Saves current image to file.
			$image->save( $dest_path );

			$avatar['url']  = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $dest_path );
			$avatar['skip'] = false;
		}
	}

	// Calls the functions added to avatar_manager_avatar_resize action hook.
	do_action( 'avatar_manager_avatar_resize', $url, $size );

	return $avatar;
}

/**
 * Deletes an avatar image based on attachment ID.
 *
 * @uses get_post_meta() For retrieving attachment meta fields.
 * @uses wp_upload_dir() For retrieving path information on the currently
 * configured uploads directory.
 * @uses delete_post_meta() For deleting attachment meta fields.
 * @uses get_users() For retrieving an array of users.
 * @uses delete_user_meta() For deleting user meta fields.
 * @uses do_action() For calling the functions added to an action hook.
 *
 * @since Avatar Manager 1.0.0
 *
 * @param int $attachment_id An attachment ID
 */
function avatar_manager_delete_avatar( $attachment_id ) {
	// Retrieves attachment meta field based on attachment ID.
	$is_custom_avatar = get_post_meta( $attachment_id, '_avatar_manager_is_custom_avatar', true );

	if ( ! $is_custom_avatar )
		return;

	// Retrieves path information on the currently configured uploads directory.
	$upload_dir = wp_upload_dir();

	// Retrieves attachment meta field based on attachment ID.
	$custom_avatar = get_post_meta( $attachment_id, '_avatar_manager_custom_avatar', true );

	if ( is_array( $custom_avatar ) ) {
		foreach ( $custom_avatar as $file ) {
			if ( ! $file['skip'] ) {
				$file = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $file['url'] );
				@unlink( $file );
			}
		}
	}

	// Deletes attachment meta fields based on attachment ID.
	delete_post_meta( $attachment_id, '_avatar_manager_custom_avatar' );
	delete_post_meta( $attachment_id, '_avatar_manager_custom_avatar_rating' );
	delete_post_meta( $attachment_id, '_avatar_manager_is_custom_avatar' );

	// An associative array with criteria to match.
	$args = array(
		'meta_key'   => 'avatar_manager_custom_avatar',
		'meta_value' => $attachment_id
	);

	// Retrieves an array of users matching the criteria given in $args.
	$users = get_users( $args );

	foreach ( $users as $user ) {
		// Deletes user meta fields based on user ID.
		delete_user_meta( $user->ID, 'avatar_manager_avatar_type' );
		delete_user_meta( $user->ID, 'avatar_manager_custom_avatar' );
	}

	// Calls the functions added to avatar_manager_delete_avatar action hook.
	do_action( 'avatar_manager_delete_avatar', $attachment_id );
}

add_action( 'delete_attachment', 'avatar_manager_delete_avatar' );

/**
 * Updates user profile based on user ID.
 *
 * @uses avatar_manager_get_options() For retrieving plugin options.
 * @uses sanitize_text_field() For sanitizing a string from user input or from
 * the database.
 * @uses update_user_meta() For updating user meta fields.
 * @uses get_user_meta() For retrieving user meta fields.
 * @uses update_post_meta() For updating attachment meta fields.
 * @uses wp_handle_upload() For handling PHP uploads in WordPress.
 * @uses wp_die() For killing WordPress execution and displaying HTML error
 * message.
 * @uses __() For retrieving the translated string from the translate().
 * @uses avatar_manager_delete_avatar() For deleting an avatar image based on
 * attachment ID.
 * @uses wp_insert_attachment() For inserting an attachment into the media
 * library.
 * @uses wp_generate_attachment_metadata() For generating metadata for an
 * attachment.
 * @uses wp_update_attachment_metadata() For updating metadata for an
 * attachment.
 * @uses avatar_manager_avatar_resize() For generating a resized copy of the
 * specified avatar image.
 * @uses get_edit_user_link() For getting the link to the user's edit profile
 * page in the WordPress admin.
 * @uses add_query_arg() For retrieving a modified URL (with) query string.
 * @uses wp_redirect() For redirecting the user to a specified absolute URI.
 *
 * @since Avatar Manager 1.0.0
 *
 * @param int $user_id User to update.
 */
function avatar_manager_edit_user_profile_update( $user_id ) {

	if ( ! is_admin() )
		require_once(ABSPATH . 'wp-admin/includes/image.php');

	// Retrieves plugin options.
	$options = avatar_manager_get_options();

	// Sanitizes the string from user input.
	$avatar_type = isset( $_POST['avatar_manager_avatar_type'] ) ? sanitize_text_field( $_POST['avatar_manager_avatar_type'] ) : 'gravatar';

	// Updates user meta field based on user ID.
	update_user_meta( $user_id, 'avatar_manager_avatar_type', $avatar_type );

	// Retrieves user meta field based on user ID.
	$custom_avatar = get_user_meta( $user_id, 'avatar_manager_custom_avatar', true );

	if ( ! empty( $custom_avatar ) ) {
		// Sanitizes the string from user input.
		$custom_avatar_rating = isset( $_POST['avatar_manager_custom_avatar_rating'] ) ? sanitize_text_field( $_POST['avatar_manager_custom_avatar_rating'] ) : 'G';

		// Updates attachment meta field based on attachment ID.
		update_post_meta( $custom_avatar, '_avatar_manager_custom_avatar_rating', $custom_avatar_rating );
	}

	if ( isset( $_POST['avatar-manager-upload-avatar'] ) && $_POST['avatar-manager-upload-avatar'] ) {
		if ( ! function_exists( 'wp_handle_upload' ) )
			require_once( ABSPATH . 'wp-admin/includes/file.php' );

		// An associative array with allowed MIME types.
		$mimes = array(
			'bmp'  => 'image/bmp',
			'gif'  => 'image/gif',
			'jpe'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg'  => 'image/jpeg',
			'png'  => 'image/png',
			'tif'  => 'image/tiff',
			'tiff' => 'image/tiff'
		);

		// An associative array to override default variables.
		$overrides = array(
			'mimes'     => $mimes,
			'test_form' => false
		);

		// Handles PHP uploads in WordPress.
		$avatar = wp_handle_upload( $_FILES['avatar_manager_import'], $overrides );

		if ( isset( $avatar['error'] ) )
			// Kills WordPress execution and displays HTML error message.
			wp_die( $avatar['error'],  __( 'Image Upload Error', 'avatar-manager' ) );

		if ( ! empty( $custom_avatar ) )
			// Deletes user's old avatar image.
			avatar_manager_delete_avatar( $custom_avatar );

		// An associative array about the attachment.
		$attachment = array(
			'guid'           => $avatar['url'],
			'post_content'   => $avatar['url'],
			'post_mime_type' => $avatar['type'],
			'post_title'     => basename( $avatar['file'] )
		);

		// Inserts the attachment into the media library.
		$attachment_id = wp_insert_attachment( $attachment, $avatar['file'] );

		// Generates metadata for the attachment.
		$attachment_metadata = wp_generate_attachment_metadata( $attachment_id, $avatar['file'] );

		// Updates metadata for the attachment.
		wp_update_attachment_metadata( $attachment_id, $attachment_metadata );

		$custom_avatar = array();

		// Generates a resized copy of the avatar image.
		$custom_avatar[ $options['default_size'] ] = avatar_manager_avatar_resize( $avatar['url'], $options['default_size'] );

		// Updates attachment meta fields based on attachment ID.
		update_post_meta( $attachment_id, '_avatar_manager_custom_avatar', $custom_avatar );
		update_post_meta( $attachment_id, '_avatar_manager_custom_avatar_rating', 'G' );
		update_post_meta( $attachment_id, '_avatar_manager_is_custom_avatar', true );

		// Updates user meta fields based on user ID.
		update_user_meta( $user_id, 'avatar_manager_avatar_type', 'custom' );
		update_user_meta( $user_id, 'avatar_manager_custom_avatar', $attachment_id );
	}

	if ( isset( $_GET['avatar_manager_action'] ) && $_GET['avatar_manager_action'] ) {
		global $wp_http_referer;

		$action = $_GET['avatar_manager_action'];

		switch ( $action ) {
			case 'remove-avatar':
				// Deletes avatar image based on attachment ID.
				avatar_manager_delete_avatar( $_GET['avatar_manager_custom_avatar'] );

				break;
		}

		// Gets the link to the user's edit profile page in the WordPress admin.
		$edit_user_link = get_edit_user_link( $user_id );

		// Retrieves a modified URL (with) query string.
		$redirect = add_query_arg( 'updated', true, $edit_user_link );

		if ( $wp_http_referer )
			// Retrieves a modified URL (with) query string.
			$redirect = add_query_arg( 'wp_http_referer', urlencode( $wp_http_referer ), $redirect );

		// Redirects the user to a specified absolute URI.
		wp_redirect( $redirect );

		exit;
	}
}

add_action( 'edit_user_profile_update', 'avatar_manager_edit_user_profile_update' );
add_action( 'personal_options_update', 'avatar_manager_edit_user_profile_update' );

/**
 * Returns user custom avatar based on user ID.
 *
 * @uses get_option() For getting values for a named option.
 * @uses avatar_manager_get_options() For retrieving plugin options.
 * @uses get_userdata() For retrieving user data by user ID.
 * @uses is_ssl() For checking if SSL is being used.
 * @uses add_query_arg() For retrieving a modified URL (with) query string.
 * @uses esc_attr() For escaping HTML attributes.
 * @uses get_user_meta() For retrieving user meta fields.
 * @uses get_post_meta() For retrieving attachment meta fields.
 * @uses wp_get_attachment_image_src() For retrieving an array with the image
 * attributes "url", "width" and "height", of an image attachment file.
 * @uses avatar_manager_avatar_resize() For generating a resized copy of the
 * specified avatar image.
 * @uses update_post_meta() For updating attachment meta fields.
 * @uses apply_filters() For calling the functions added to a filter hook.
 *
 * @since Avatar Manager 1.0.0
 *
 * @param int $user_id User to update.
 * @param int $size Size of the avatar image
 * @param string $default URL to a default image to use if no avatar is
 * available.
 * @param string $alt Alternative text to use in image tag. Defaults to blank.
 * @return string <img> tag for the user's avatar.
 */
function avatar_manager_get_custom_avatar( $user_id, $size = '', $default = '', $alt = false ) {
	// Returns if showing avatars is not enabled.
	if ( ! get_option( 'show_avatars' ) )
		return false;

	// Retrieves plugin options.
	$options = avatar_manager_get_options();

	if ( empty( $size ) || ! is_numeric( $size ) ) {
		$size = $options['default_size'];
	} else {
		$size = absint( $size );

		if ( $size < 1 )
			$size = 1;
		elseif ( $size > 512 )
			$size = 512;
	}

	// Retrieves user data by user ID.
	$user = get_userdata( $user_id );

	// Returns if no user data was retrieved.
	if ( empty( $user ) )
		return false;

	$email = $user->user_email;

	if ( empty( $default ) ) {
		// Retrieves values for the named option.
		$avatar_default = get_option( 'avatar_default' );

		if ( empty( $avatar_default ) )
			$default = 'mystery';
		else
			$default = $avatar_default;
	}

	$email_hash = md5( strtolower( trim( $email ) ) );

	if ( is_ssl() )
		$host = 'https://secure.gravatar.com';
	else
		$host = sprintf( 'http://%d.gravatar.com', ( hexdec( $email_hash[0] ) % 2 ) );

	if ( $default == 'mystery' )
		$default = $host . '/avatar/ad516503a11cd5ca435acc9bb6523536?s=' . $size;
	elseif ( $default == 'gravatar_default' )
		$default = '';
	elseif ( strpos( $default, 'http://' ) === 0 )
		// Retrieves a modified URL (with) query string.
		$default = add_query_arg( 's', $size, $default );

	if ( $alt === false )
		$alt = '';
	else
		// Escapes HTML attributes.
		$alt = esc_attr( $alt );

	// Retrieves values for the named option.
	$avatar_rating = get_option( 'avatar_rating' );

	// Retrieves user meta field based on user ID.
	$custom_avatar = get_user_meta( $user_id, 'avatar_manager_custom_avatar', true );

	// Returns if no attachment ID was retrieved.
	if ( empty( $custom_avatar ) )
		return false;

	// Retrieves attachment meta field based on attachment ID.
	$custom_avatar_rating = get_post_meta( $custom_avatar, '_avatar_manager_custom_avatar_rating', true );

	$ratings['G']  = 1;
	$ratings['PG'] = 2;
	$ratings['R']  = 3;
	$ratings['X']  = 4;

	if ( $ratings[ $custom_avatar_rating ] <= $ratings[ $avatar_rating ] ) {
		// Retrieves attachment meta field based on attachment ID.
		$avatar = get_post_meta( $custom_avatar, '_avatar_manager_custom_avatar', true );

		if ( empty( $avatar[ $size ] ) ) {
			// Retrieves an array with the image attributes "url", "width" and
			// "height", of the image attachment file.
			$url = wp_get_attachment_image_src( $custom_avatar, 'full' );

			// Generates a resized copy of the avatar image.
			$avatar[ $size ] = avatar_manager_avatar_resize( $url[0], $size );

			// Updates attachment meta field based on attachment ID.
			update_post_meta( $custom_avatar, '_avatar_manager_custom_avatar', $avatar );
		}

		$src    = $avatar[ $size ]['url'];
		$avatar = '<img alt="' . $alt . '" class="avatar avatar-' . $size . ' photo avatar-default" height="' . $size . '" src="' . $src . '" width="' . $size . '">';
	} else {
		$src  = $host . '/avatar/';
		$src .= $email_hash;
		$src .= '?s=' . $size;
		$src .= '&amp;d=' . urlencode( $default );
		$src .= '&amp;forcedefault=1';

		$avatar = '<img alt="' . $alt . '" class="avatar avatar-' . $size . ' photo avatar-default" height="' . $size . '" src="' . $src . '" width="' . $size . '">';
	}

	// Calls the functions added to avatar_manager_get_custom_avatar filter
	// hook.
	return apply_filters( 'avatar_manager_get_custom_avatar', $avatar, $user_id, $size, $default, $alt );
}

/**
 * Returns the avatar for a user who provided a user ID or email address.
 *
 * @uses get_option() For getting values for a named option.
 * @uses avatar_manager_get_options() For retrieving plugin options.
 * @uses get_userdata() For retrieving user data by user ID.
 * @uses avatar_manager_get_custom_avatar() For retrieving user custom avatar
 * based on user ID.
 * @uses apply_filters() For calling the functions added to a filter hook.
 *
 * @since Avatar Manager 1.0.0
 *
 * @param int|string|object $id_or_email A user ID, email address, or comment
 * object.
 * @param int $size Size of the avatar image
 * @param string $default URL to a default image to use if no avatar is
 * available.
 * @param string $alt Alternative text to use in image tag. Defaults to blank.
 * @return string <img> tag for the user's avatar.
 */
function avatar_manager_get_avatar( $avatar = '', $id_or_email, $size = '', $default = '', $alt = false ) {
	// Returns if showing avatars is not enabled.
	if ( ! get_option( 'show_avatars' ) )
		return false;

	// Retrieves plugin options.
	$options = avatar_manager_get_options();

	if ( empty( $size ) || ! is_numeric( $size ) ) {
		$size = $options['default_size'];
	} else {
		$size = absint( $size );

		if ( $size < 1 )
			$size = 1;
		elseif ( $size > 512 )
			$size = 512;
	}

	$email = '';

	if ( is_numeric( $id_or_email ) ) {
		$id = (int) $id_or_email;

		// Retrieves user data by user ID.
		$user = get_userdata( $id );

		if ( $user )
			$email = $user->user_email;
	} elseif ( is_object( $id_or_email ) ) {
		if ( ! empty( $id_or_email->user_id ) ) {
			$id = (int) $id_or_email->user_id;

			// Retrieves user data by user ID.
			$user = get_userdata( $id );

			if ( $user )
				$email = $user->user_email;
		} elseif ( ! empty( $id_or_email->comment_author_email ) ) {
			$email = $id_or_email->comment_author_email;
		}
	} else {
		$email = $id_or_email;

		if ( $id = email_exists( $email ) )
			// Retrieves user data by user ID.
			$user = get_userdata( $id );
	}

	if ( isset( $user ) )
		$avatar_type = $user->avatar_manager_avatar_type;
	else
		return $avatar;

	if ( $avatar_type == 'custom' )
		// Retrieves user custom avatar based on user ID.
		$avatar = avatar_manager_get_custom_avatar( $user->ID, $size, $default, $alt );

	// Calls the functions added to avatar_manager_get_avatar filter hook.
	return apply_filters( 'avatar_manager_get_avatar', $avatar, $id_or_email, $size, $default, $alt );
}

add_filter( 'get_avatar', 'avatar_manager_get_avatar', 10, 5 );

/**
 * Prevents custom avatars from being applied to the Default Avatar setting.
 *
 * @uses remove_filter() For removing a function attached to a specified action
 * hook.
 *
 * @since Avatar Manager 1.0.0
 *
 * @param array $avatar_defaults An associative array with default avatars.
 * @return array An associative array with default avatars.
 */
function avatar_manager_avatar_defaults( $avatar_defaults ) {
	// Removes the avatar_manager_get_avatar function attached to get_avatar
	// action hook.
	remove_filter( 'get_avatar', 'avatar_manager_get_avatar' );

	return $avatar_defaults;
}

add_filter( 'avatar_defaults', 'avatar_manager_avatar_defaults', 10, 1 );

/**
 * Displays media states for avatar images.
 *
 * @uses get_post_meta() For retrieving attachment meta fields.
 * @uses __() For retrieving the translated string from the translate().
 * @uses apply_filters() For calling the functions added to a filter hook.
 *
 * @since Avatar Manager 1.2.0
 *
 * @param array $media_states An associative array with media states.
 * @return array An associative array with media states.
 */
function avatar_manager_display_media_states( $media_states ) {
	global $post;

	// Retrieves attachment meta field based on attachment ID.
	$meta_avatar = get_post_meta( $post->ID, '_avatar_manager_is_custom_avatar', true );

	if ( ! empty( $meta_avatar ) )
		$media_states[] = __( 'Avatar Image', 'avatar-manager' );

	// Calls the functions added to avatar_manager_display_media_states filter
	// hook.
	return apply_filters( 'avatar_manager_display_media_states', $media_states );
}

add_filter( 'display_media_states', 'avatar_manager_display_media_states', 10, 1 );

/**
 * Deletes user's custom avatar image.
 *
 * @uses get_user_meta() For retrieving user meta fields.
 * @uses do_action() For calling the functions added to an action hook.
 * @uses avatar_manager_delete_avatar() For deleting an avatar image based on
 * attachment ID.
 *
 * @since Avatar Manager 1.3.0
 *
 * @param array $args An associative array with username and passowrd.
 * @return string Avatar type.
 */
function avatar_manager_deleteCustomAvatar( $args ) {
	global $wp_xmlrpc_server;

	if ( count( $args ) < 2 )
		return new IXR_Error( 400, __( 'Insufficient arguments passed to this XML-RPC method.', 'avatar_manager' ) );

	// Sanitizes the string or array of strings from user input.
	$wp_xmlrpc_server->escape( $args );

	$username = $args[0];
	$password = $args[1];

	if ( ! $user = $wp_xmlrpc_server->login( $username, $password ) )
		return $wp_xmlrpc_server->error;

	// Retrieves user meta field based on user ID.
	$custom_avatar = get_user_meta( $user->ID, 'avatar_manager_custom_avatar', true );

	// Returns if no attachment ID was retrieved.
	if ( empty( $custom_avatar ) )
		return new IXR_Error( 404, __( 'Sorry, you don\'t have a custom avatar.', 'avatar_manager' ) );

	// Calls the functions added to xmlrpc_call action hook.
	do_action( 'xmlrpc_call', 'avatarManager.deleteCustomAvatar' );

	// Deletes avatar image based on attachment ID.
	avatar_manager_delete_avatar( $custom_avatar );

	return true;
}

/**
 * Returns user's avatar type.
 *
 * @uses do_action() For calling the functions added to an action hook.
 * @uses get_user_meta() For retrieving user meta fields.
 *
 * @since Avatar Manager 1.3.0
 *
 * @param array $args An associative array with username and passowrd.
 * @return string Avatar type.
 */
function avatar_manager_getAvatarType( $args ) {
	global $wp_xmlrpc_server;

	if ( count( $args ) < 2 )
		return new IXR_Error( 400, __( 'Insufficient arguments passed to this XML-RPC method.', 'avatar_manager' ) );

	// Sanitizes the string or array of strings from user input.
	$wp_xmlrpc_server->escape( $args );

	$username = $args[0];
	$password = $args[1];

	if ( ! $user = $wp_xmlrpc_server->login( $username, $password ) )
		return $wp_xmlrpc_server->error;

	// Calls the functions added to xmlrpc_call action hook.
	do_action( 'xmlrpc_call', 'avatarManager.getAvatarType' );

	// Retrieves user meta field based on user ID.
	$avatar_type = get_user_meta( $user->ID, 'avatar_manager_avatar_type', true );

	// Defaults to Gravatar if avatar type is empty.
	$avatar_type = empty( $avatar_type ) ? 'gravatar' : $avatar_type;

	return $avatar_type;
}

/**
 * Returns user's custom avatar attachment ID, image and rating.
 *
 * @uses get_user_meta() For retrieving user meta fields.
 * @uses do_action() For calling the functions added to an action hook.
 * @uses avatar_manager_get_custom_avatar() For retrieving user custom avatar
 * based on user ID.
 * @uses get_post_meta() For retrieving attachment meta fields.
 *
 * @since Avatar Manager 1.3.0
 *
 * @param array $args An associative array with username, passowrd, avatar image
 * size (optional), default avatar image (optional) and alternate text
 * (optional).
 * @return array An associative array with custom avatar image and rating.
 */
function avatar_manager_getCustomAvatar( $args ) {
	global $wp_xmlrpc_server;

	if ( count( $args ) < 2 )
		return new IXR_Error( 400, __( 'Insufficient arguments passed to this XML-RPC method.', 'avatar_manager' ) );

	// Sanitizes the string or array of strings from user input.
	$wp_xmlrpc_server->escape( $args );

	$username = $args[0];
	$password = $args[1];
	$size     = isset( $args[2] ) ? $args[2] : '';
	$default  = isset( $args[3] ) ? $args[3] : '';
	$alt      = isset( $args[4] ) ? $args[4] : false;

	if ( ! $user = $wp_xmlrpc_server->login( $username, $password ) )
		return $wp_xmlrpc_server->error;

	// Retrieves user meta field based on user ID.
	$custom_avatar = get_user_meta( $user->ID, 'avatar_manager_custom_avatar', true );

	// Returns if no attachment ID was retrieved.
	if ( empty( $custom_avatar ) )
		return new IXR_Error( 404, __( 'Sorry, you don\'t have a custom avatar.', 'avatar_manager' ) );

	// Calls the functions added to xmlrpc_call action hook.
	do_action( 'xmlrpc_call', 'avatarManager.getCustomAvatar' );

	$custom_avatar = array(
		'id'     => $custom_avatar,
		'image'  => avatar_manager_get_custom_avatar( $user->ID, $size, $default, $alt ),
		'rating' => get_post_meta( $custom_avatar, '_avatar_manager_custom_avatar_rating', true )
	);

	return $custom_avatar;
}

/**
 * Sets user's avatar type.
 *
 * @uses get_user_meta() For retrieving user meta fields.
 * @uses do_action() For calling the functions added to an action hook.
 * @uses update_user_meta() For updating user meta fields.
 *
 * @since Avatar Manager 1.3.0
 *
 * @param array $args An associative array with username, passowrd and avatar
 * type.
 * @return bool Operation status.
 */
function avatar_manager_setAvatarType( $args ) {
	global $wp_xmlrpc_server;

	if ( count( $args ) < 3 )
		return new IXR_Error( 400, __( 'Insufficient arguments passed to this XML-RPC method.', 'avatar_manager' ) );

	// Sanitizes the string or array of strings from user input.
	$wp_xmlrpc_server->escape( $args );

	$username    = $args[0];
	$password    = $args[1];
	$avatar_type = $args[2];

	if ( ! $user = $wp_xmlrpc_server->login( $username, $password ) )
		return $wp_xmlrpc_server->error;

	if ( ! in_array(  $avatar_type, array( 'gravatar', 'custom' ) ) )
		return new IXR_Error( 401, __( 'Invalid avatar type.', 'avatar_manager' ) );

	// Retrieves user meta field based on user ID.
	$custom_avatar = get_user_meta( $user->ID, 'avatar_manager_custom_avatar', true );

	// Returns if no attachment ID was retrieved and requested avatar type is
	// set to custom.
	if ( empty( $custom_avatar ) && $avatar_type == 'custom' )
		return new IXR_Error( 404, __( 'Sorry, you don\'t have a custom avatar.', 'avatar_manager' ) );

	// Calls the functions added to xmlrpc_call action hook.
	do_action( 'xmlrpc_call', 'avatarManager.setAvatarType' );

	// Updates user meta field based on user ID.
	update_user_meta( $user->id, 'avatar_manager_avatar_type', $avatar_type );

	return true;
}

/**
 * Sets user's custom avatar rating.
 *
 * @uses get_user_meta() For retrieving user meta fields.
 * @uses do_action() For calling the functions added to an action hook.
 * @uses update_post_meta() For updating attachment meta fields.
 *
 * @since Avatar Manager 1.3.0
 *
 * @param array $args An associative array with username, passowrd and custom
 * avatar rating.
 * @return bool Operation status.
 */
function avatar_manager_setCustomAvatarRating( $args ) {
	global $wp_xmlrpc_server;

	if ( count( $args ) < 3 )
		return new IXR_Error( 400, __( 'Insufficient arguments passed to this XML-RPC method.', 'avatar_manager' ) );

	// Sanitizes the string or array of strings from user input.
	$wp_xmlrpc_server->escape( $args );

	$username             = $args[0];
	$password             = $args[1];
	$custom_avatar_rating = $args[2];

	if ( ! $user = $wp_xmlrpc_server->login( $username, $password ) )
		return $wp_xmlrpc_server->error;

	if ( ! in_array(  $custom_avatar_rating, array( 'G', 'PG', 'R', 'X' ) ) )
		return new IXR_Error( 401, __( 'Invalid custom avatar rating.', 'avatar_manager' ) );

	// Retrieves user meta field based on user ID.
	$custom_avatar = get_user_meta( $user->ID, 'avatar_manager_custom_avatar', true );

	// Returns if no attachment ID was retrieved.
	if ( empty( $custom_avatar ) )
		return new IXR_Error( 404, __( 'Sorry, you don\'t have a custom avatar.', 'avatar_manager' ) );

	// Calls the functions added to xmlrpc_call action hook.
	do_action( 'xmlrpc_call', 'avatarManager.setCustomAvatarRating' );

	// Updates attachment meta field based on attachment ID.
	update_post_meta( $custom_avatar, '_avatar_manager_custom_avatar_rating', $custom_avatar_rating );

	return true;
}

/**
 * Uploads an avatar image and sets it as user's custom avatar image.
 *
 * @uses avatar_manager_get_options() For retrieving plugin options.
 * @uses apply_filters() For calling the functions added to a filter hook.
 * @uses sanitize_file_name() For sanitizing a filename replacing whitespace
 * with dashes.
 * @uses wp_upload_bits() For creating a file in the upload folder with given
 * content.
 * @uses do_action() For calling the functions added to an action hook.
 * @uses get_user_meta() For retrieving user meta fields.
 * @uses avatar_manager_delete_avatar() For deleting an avatar image based on
 * attachment ID.
 * @uses wp_insert_attachment() For inserting an attachment into the media
 * library.
 * @uses wp_generate_attachment_metadata() For generating metadata for an
 * attachment.
 * @uses wp_update_attachment_metadata() For updating metadata for an
 * attachment.
 * @uses avatar_manager_avatar_resize() For generating a resized copy of the
 * specified avatar image.
 * @uses update_post_meta() For updating attachment meta fields.
 * @uses update_user_meta() For updating user meta fields.
 *
 * @since Avatar Manager 1.3.0
 *
 * @param array $args An associative array with username and passowrd.
 * @return string Avatar type.
 */
function avatar_manager_uploadCustomAvatar( $args ) {
	global $wpdb, $wp_xmlrpc_server;

	if ( count( $args ) < 3 )
		return new IXR_Error( 400, __( 'Insufficient arguments passed to this XML-RPC method.', 'avatar_manager' ) );

	$username = $wpdb->escape($args[0]);
	$password = $wpdb->escape($args[1]);
	$avatar   = $args[2];

	if ( ! $user = $wp_xmlrpc_server->login( $username, $password ) )
		return $wp_xmlrpc_server->error;

	// Retrieves plugin options.
	$options = avatar_manager_get_options();

	if ( ! current_user_can( 'upload_files' ) && ! $options['avatar_uploads'] )
		return new IXR_Error( 401, __( 'Sorry, you don\'t have permission to upload files.', 'avatar_manager' ) );

	if ( $upload_error = apply_filters( 'pre_upload_error', false ) )
		return new IXR_Error( 500, $upload_error );

	// Sanitizes a filename replacing whitespace with dashes.
	$filename = sanitize_file_name( $avatar['name'] );

	// An associative array with allowed MIME types.
	$mimes = array(
		'bmp'  => 'image/bmp',
		'gif'  => 'image/gif',
		'jpe'  => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpg'  => 'image/jpeg',
		'png'  => 'image/png',
		'tif'  => 'image/tiff',
		'tiff' => 'image/tiff'
	);

	if ( ! in_array( $avatar['type'], $mimes ) )
		return new IXR_Error( 401, __( 'Sorry, this file type is not permitted for security reasons.', 'avatar_manager' ) );

	// Creates a file in the upload folder with given content.
	$upload = wp_upload_bits( $filename, null, $avatar['bits'] );

	if ( ! empty( $upload['error'] ) )
		return new IXR_Error( 500, $upload['error'] );

	// Calls the functions added to xmlrpc_call action hook.
	do_action( 'xmlrpc_call', 'avatarManager.uploadCustomAvatar' );

	// Retrieves user meta field based on user ID.
	$custom_avatar = get_user_meta( $user->ID, 'avatar_manager_custom_avatar', true );

	if ( ! empty( $custom_avatar ) )
		// Deletes user's old avatar image.
		avatar_manager_delete_avatar( $custom_avatar );

	// An associative array about the attachment.
	$attachment = array(
		'guid'           => $upload['url'],
		'post_content'   => $upload['url'],
		'post_mime_type' => $avatar['type'],
		'post_title'     => $filename
	);

	// Inserts the attachment into the media library.
	$attachment_id = wp_insert_attachment( $attachment, $upload['file'] );

	// Generates metadata for the attachment.
	$attachment_metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );

	// Updates metadata for the attachment.
	wp_update_attachment_metadata( $attachment_id, $attachment_metadata );

	$custom_avatar = array();

	// Generates a resized copy of the avatar image.
	$custom_avatar[ $options['default_size'] ] = avatar_manager_avatar_resize( $upload['url'], $options['default_size'] );

	// Updates attachment meta fields based on attachment ID.
	update_post_meta( $attachment_id, '_avatar_manager_custom_avatar', $custom_avatar );
	update_post_meta( $attachment_id, '_avatar_manager_custom_avatar_rating', 'G' );
	update_post_meta( $attachment_id, '_avatar_manager_is_custom_avatar', true );

	// Updates user meta fields based on user ID.
	update_user_meta( $user->ID, 'avatar_manager_avatar_type', 'custom' );
	update_user_meta( $user->ID, 'avatar_manager_custom_avatar', $attachment_id );

	$struct = array(
		'id'   => strval( $attachment_id ),
		'file' => $filename,
		'url'  => $upload['url'],
		'type' => $avatar['type']
	);

	// Calls the functions added to wp_handle_upload filter hook.
	return apply_filters( 'wp_handle_upload', $struct, 'upload' );
}

/**
 * Extends the WordPress XML-RPC API.
 *
 * @since Avatar Manager 1.3.0
 *
 * @param array $methods An associative array with WordPress XML-RPC API
 * methods.
 * @return array An associative array with WordPress XML-RPC API methods.
 */
function avatar_manager_xmlrpc_methods( $methods ) {
	$methods['avatarManager.deleteCustomAvatar']    = 'avatar_manager_deleteCustomAvatar';
	$methods['avatarManager.getAvatarType']         = 'avatar_manager_getAvatarType';
	$methods['avatarManager.getCustomAvatar']       = 'avatar_manager_getCustomAvatar';
	$methods['avatarManager.setAvatarType']         = 'avatar_manager_setAvatarType';
	$methods['avatarManager.setCustomAvatarRating'] = 'avatar_manager_setCustomAvatarRating';
	$methods['avatarManager.uploadCustomAvatar']    = 'avatar_manager_uploadCustomAvatar';

	return $methods;
}

add_filter( 'xmlrpc_methods', 'avatar_manager_xmlrpc_methods' );
?>
