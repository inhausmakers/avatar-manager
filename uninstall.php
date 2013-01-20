<?php
/**
 * @package Avatar_Manager
 * @subpackage Uninstaller
 */

// Exit if uninstall is not called from WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit;

delete_option( 'avatar_manager_avatar_uploads' );
delete_option( 'avatar_manager_default_size' );
?>
