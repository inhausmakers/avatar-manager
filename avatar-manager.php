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
?>
