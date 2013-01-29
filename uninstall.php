<?php
/**
 * @package Avatar_Manager
 * @subpackage Uninstaller
 */

// Exit if uninstall is not called from WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit;

// Delete plugin options
delete_option( 'avatar_manager' );

// An associative array with criteria to match.
$args = array(
	'meta_key'   => 'avatar_manager_custom_avatar'
);

// Retrieves an array of users matching the criteria given in $args.
$users = get_users( $args );

foreach ( $users as $user ) {
	// Deletes avatar image based on attachment ID.
	avatar_manager_delete_avatar( $user->avatar_manager_custom_avatar );
}
?>
