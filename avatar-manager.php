<?php
/**
 * @package Avatar_Manager
 */
/*
Plugin Name: Avatar Manager
Plugin URI: https://github.com/cdog/avatar-manager
Description: Avatar Manager for WordPress is a sweet and simple plugin for storing avatars locally and more.
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
 * @uses register_setting() For registering a setting and its sanitization callback.
 * @uses add_settings_field() For registering a settings field to a settings page and section.
 *
 * @since Avatar Manager 1.0.0
 */
function avatar_manager_admin_init() {
	// Registers Avatar Uploads setting and its sanitization callback.
	register_setting( 'discussion', 'avatar_manager_avatar_uploads', 'avatar_manager_sanitize_options' );

	// Registers Avatar Uploads settings field under the Settings Discussion Screen.
	add_settings_field( 'avatar-manager-avatar_uploads', __( 'Avatar Uploads', 'avatar-manager' ), 'avatar_manager_avatar_uploads_settings_field', 'discussion', 'avatars' );

	// Registers Default Size setting and its sanitization callback.
	register_setting( 'discussion', 'avatar_manager_default_size', 'avatar_manager_sanitize_options' );

	// Registers Default Size settings field under the Settings Discussion Screen.
	add_settings_field( 'avatar-manager-default-size', __( 'Default Size', 'avatar-manager' ), 'avatar_manager_default_size_settings_field', 'discussion', 'avatars' );
}

add_action( 'admin_init', 'avatar_manager_admin_init' );

/**
 * Returns plugin default options.
 *
 * @since Avatar Manager 1.0.0
 */
function avatar_manager_get_default_options() {
	$options = array(
		'avatar_manager_avatar_uploads' => AVATAR_MANAGER_AVATAR_UPLOADS,
		'avatar_manager_default_size'   => AVATAR_MANAGER_DEFAULT_SIZE
	);

	return $options;
}

/**
 * Sanitizes and validates plugin options.
 *
 * @see avatar_manager_get_default_options()
 * @since Avatar Manager 1.0.0
 */
function avatar_manager_sanitize_options( $input ) {
	$options = avatar_manager_get_default_options();

	if ( isset( $input['avatar_manager_avatar_uploads'] ) )
		$options['avatar_manager_avatar_uploads'] = $input['avatar_manager_avatar_uploads'] ? 0 : 1;

	if ( isset( $input['avatar_manager_default_size'] ) && is_numeric( $input['avatar_manager_default_size'] ) )
		$options['avatar_manager_default_size'] = $input['avatar_manager_default_size'] ? 0 : 1;

	return $options;
}

/**
 * Prints the Avatar Uploads settings field.
 *
 * @uses checked() For comparing two given values.
 * @uses get_option() For getting values from the options database table.
 * @since Avatar Manager 1.0.0
 */
function avatar_manager_avatar_uploads_settings_field() {
	?>
	<fieldset>
		<legend class="screen-reader-text">
			<span>
				<?php _e( 'Avatar Uploads', 'avatar-manager' ); ?>
			</span>
		</legend>
		<label>
			<input <?php checked( get_option( 'avatar_manager_avatar_uploads', AVATAR_MANAGER_AVATAR_UPLOADS ), 1, true ); ?> name="avatar_manager_avatar_uploads" type="checkbox" value="1">
			<?php _e( 'Anyone can upload', 'avatar-manager' ); ?>
		</label>
	</fieldset>
	<?php
}

/**
 * Prints the Default Size settings field.
 *
 * @uses get_option() For getting values from the options database table.
 * @since Avatar Manager 1.0.0
 */
function avatar_manager_default_size_settings_field() {
	?>
	<fieldset>
		<legend class="screen-reader-text">
			<span>
				<?php _e( 'Default Size', 'avatar-manager' ); ?>
			</span>
		</legend>
		<label>
			<?php _e( 'Default size of the avatar image', 'avatar-manager' ); ?>
			<input class="small-text" min="0" name="avatar_manager_default_size" step="1" type="number" value="<?php echo get_option( 'avatar_manager_default_size', AVATAR_MANAGER_DEFAULT_SIZE ); ?>">
		</label>
	</fieldset>
	<?php
}
?>
