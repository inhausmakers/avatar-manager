<?php
/**
 * @package Avatar_Manager
 * @subpackage Uninstaller
 */

wp_die('shish');

// Exit if uninstall is not called from WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit;

// Delete plugin options
delete_option( 'avatar_manager_avatar_uploads' );
delete_option( 'avatar_manager_default_size' );
?>
