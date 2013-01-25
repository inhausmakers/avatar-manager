<?php
/**
 * @package Avatar_Manager
 */
/*
Plugin Name: Avatar Manager
Plugin URI: https://github.com/cdog/avatar-manager
Description: Avatar Manager for WordPress is a sweet and simple plugin for storing avatars locally and more. Easily.
Version: 1.0.0
Author: Cătălin Dogaru
Author URI: http://swarm.cs.pub.ro/~cdogaru/
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

define( 'AVATAR_MANAGER_VERSION', '1.0.0' );
define( 'AVATAR_MANAGER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AVATAR_MANAGER_AVATAR_UPLOADS', 0 );
define( 'AVATAR_MANAGER_DEFAULT_SIZE', 96 );

/**
 * Sets up plugin defaults and makes Avatar Manager available for translation.
 *
 * @uses load_theme_textdomain() For translation/localization support.
 *
 * @since Avatar Manager 1.0.0
 */
function avatar_manager_init() {
	// Makes Avatar Manager available for translation.
	load_plugin_textdomain( 'avatar-manager', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'avatar_manager_init' );

/**
 * Registers sanitization callback and plugin setting fields.
 *
 * @uses register_setting() For registering a setting and its sanitization
 * callback.
 * @uses add_settings_field() For registering a settings field to a settings
 * page and section.
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
 * Enqueues scripts and styles for dashboard.
 *
 * @uses wp_register_style() For registering a CSS style file.
 * @uses wp_enqueue_style() For enqueuing a CSS style file.
 *
 * @since Avatar Manager 1.0.0
 */
function avatar_manager_enqueue_scripts() {
	global $hook_suffix;

	if ( $hook_suffix == 'profile.php' ) {
		wp_register_style( 'avatar-manager.css', AVATAR_MANAGER_PLUGIN_URL . 'avatar-manager.css', array(), '1.0.0' );
		wp_enqueue_style( 'avatar-manager.css');

		wp_register_script( 'avatar-manager.js', AVATAR_MANAGER_PLUGIN_URL . 'avatar-manager.js', array( 'jquery' ), '1.0.0' );
		wp_enqueue_script( 'avatar-manager.js' );
	}
}

add_action( 'admin_enqueue_scripts', 'avatar_manager_enqueue_scripts' );

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
 * @see avatar_manager_get_default_options()
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
 * @see avatar_manager_get_default_options()
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
 * @see avatar_manager_get_options()
 * @uses checked() For comparing two given values.
 * @uses get_option() For getting values from the options database table.
 *
 * @since Avatar Manager 1.0.0
 */
function avatar_manager_avatar_uploads_settings_field() {
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
 * @see avatar_manager_get_options()
 * @uses get_option() For getting values from the options database table.
 *
 * @since Avatar Manager 1.0.0
 */
function avatar_manager_default_size_settings_field() {
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
 * @since Avatar Manager 1.0.0
 *
 * @param array $profileuser User to edit.
 */
function avatar_manager_edit_user_profile( $profileuser ) {
	$options     = avatar_manager_get_options();
	$avatar_type = isset( $profileuser->avatar_manager_avatar_type ) ? $profileuser->avatar_manager_avatar_type : 'gravatar';

	if ( isset( $profileuser->avatar_manager_custom_avatar ) ) {
		$custom_avatar_rating   = get_post_meta( $profileuser->avatar_manager_custom_avatar, '_avatar_manager_custom_avatar_rating', true );
		$user_has_custom_avatar = get_post_meta( $profileuser->avatar_manager_custom_avatar, '_avatar_manager_is_custom_avatar', true );
	}

	if ( ! isset( $custom_avatar_rating ) || empty( $custom_avatar_rating ) )
		$custom_avatar_rating = 'G';

	if ( ! isset( $user_has_custom_avatar ) || empty( $user_has_custom_avatar ) )
		$user_has_custom_avatar = false;
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
							<?php echo get_avatar( $profileuser->ID, 32, '', false ); ?>
							<?php _e( 'Custom', 'avatar-manager' ); ?>
						</label>
					<?php endif; ?>
					<?php
					if ( $user_has_custom_avatar && (current_user_can( 'upload_files' ) || $options['avatar_uploads']) ) {
						$href = esc_attr( add_query_arg( array(
							'action'  => 'avatar-manager-remove-avatar',
							'user_id' => $profileuser->ID
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
						<?php submit_button( __( 'Upload', 'avatar-manager' ), 'button', 'avatar-manager-upload-avatar', false ); ?>
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
 * Resizes an avatar image.
 *
 * @since Avatar Manager 1.0.0
 *
 * @param string $url URL of the avatar image to resize.
 * @param int $size Size of the new avatar image.
 * @return array Array with the URL of the new avatar image.
 */
function avatar_manager_avatar_resize( $url, $size ) {
	$upload_dir = wp_upload_dir();
	$filename   = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $url );
	$pathinfo   = pathinfo( $filename );
	$dirname    = $pathinfo['dirname'];
	$extension  = $pathinfo['extension'];
	$basename   = wp_basename( $filename, ".$ext" );
	$suffix     = $size . 'x' . $size;
	$dest_path  = $dirname . '/' . $basename . '-' . $suffix . '.' . $extension;
	$avatar     = array();

	if ( file_exists( $dest_path ) ) {
		$avatar['url']  = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $dest_path );
		$avatar['skip'] = true;
	} else {
		$filename = image_resize( $filename, $size, $size, true );

		if ( ! is_wp_error( $filename ) ) {
			$avatar['url']  = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $filename );
			$avatar['skip'] = false;
		}
	}

	return $avatar;
}

/**
 * Updates user profile.
 *
 * @since Avatar Manager 1.0.0
 *
 * @param array $user_id User to update.
 */
function avatar_manager_edit_user_profile_update( $user_id ) {
	$user->avatar_type = isset( $_POST['avatar_manager_avatar_type'] ) ? sanitize_text_field( $_POST['avatar_manager_avatar_type'] ) : 'gravatar';

	if ( isset( $userdata->avatar_manager_custom_avatar ) ) {
		$user->avatar_manager_custom_avatar  = $userdata->avatar_manager_custom_avatar;
		$custom_avatar_rating = isset( $_POST['avatar_manager_custom_avatar_rating'] ) ? sanitize_text_field( $_POST['avatar_manager_custom_avatar_rating'] ) : 'G';

		update_post_meta( $user->avatar_manager_custom_avatar, '_avatar_manager_custom_avatar_rating', $custom_avatar_rating );
	}

	if ( isset( $_POST['avatar-manager-upload-avatar'] ) && $_POST['avatar-manager-upload-avatar'] ) {
		if ( isset( $user->custom_avatar ) )
			custom_avatar_cleanup( $user->custom_avatar );

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

		$overrides = array( 'mimes' => $mimes, 'test_form' => false );
		$file      = wp_handle_upload( $_FILES['avatar_manager_import'], $overrides );

		if ( isset( $file['error'] ) )
			wp_die( $file['error'],  __( 'Image Upload Error', 'avatar-manager' ) );

		$url      = $file['url'];
		$type     = $file['type'];
		$file     = $file['file'];
		$filename = basename( $file );

		// Construct the object array
		$object = array(
			'guid'           => $url,
			'post_content'   => $url,
			'post_mime_type' => $type,
			'post_title'     => $filename
		);

		// Save the data
		$attachment_id = wp_insert_attachment( $object, $file );

		// Add the attachment meta-data
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $file ) );

		$size          = get_option( 'avatar_size' );
		$custom_avatar = array();

		// Resize the avatar image to default size
		$custom_avatar[ $size ] = avatar_manager_avatar_resize( $url, $size );

		update_post_meta( $attachment_id, '_avatar_manager_custom_avatar', $custom_avatar );
		update_post_meta( $attachment_id, '_avatar_manager_custom_avatar_rating', 'G' );
		update_post_meta( $attachment_id, '_avatar_manager_is_custom_avatar', true );

		// Add the user meta-data
		$user->avatar_type   = 'custom';
		$user->custom_avatar = $attachment_id;
	}
}

add_action( 'personal_options_update', 'avatar_manager_edit_user_profile_update' );
add_action( 'edit_user_profile_update', 'avatar_manager_edit_user_profile_update' );
?>
