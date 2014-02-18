<?php
/**
 * @package Avatar_Manager
 * @subpackage Uninstaller
 */

// Exits if uninstall is not called from WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit;

if ( ! function_exists( 'avatar_manager_delete_avatar' ) )
	include_once( 'avatar-manager.php' );

// Deletes plugin options.
delete_option( 'avatar_manager' );

// An associative array with criteria to match.
$args = array(
	'meta_key' => 'avatar_manager_custom_avatar'
);

// Retrieves an array of users matching the criteria given in $args.
$users = get_users( $args );

foreach ( $users as $user ) {
	// Deletes an avatar image based on user ID.
	avatar_manager_delete_avatar( $user->ID );
}
?>
