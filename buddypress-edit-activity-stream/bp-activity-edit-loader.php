<?php
/*
Plugin Name: BuddyPress Edit Activity Stream
Plugin URI: http://wordpress.org/extend/plugins/buddypress-edit-activity-stream/
Description: Allow user (set timeout) and admins to edit activity updates
Author: rich @etivite
Author URI: http://etivite.com
License: GNU GENERAL PUBLIC LICENSE 3.0 http://www.gnu.org/licenses/gpl.txt
Version: 0.5.1
Text Domain: bp-activity-edit
Network: true
*/

function etivite_bp_edit_activity_init() {

	if ( file_exists( dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' ) )
		load_textdomain( 'bp-activity-edit', dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' );
		
	require( dirname( __FILE__ ) . '/bp-activity-edit.php' );
	
	add_action( bp_core_admin_hook(), 'etivite_bp_edit_activity_admin_add_admin_menu' );
	
}
add_action( 'bp_include', 'etivite_bp_edit_activity_init', 88 );
//add_action( 'bp_init', 'etivite_bp_edit_activity_init', 88 );

//add admin_menu page
function etivite_bp_edit_activity_admin_add_admin_menu() {
	global $bp;

	if ( !is_super_admin() )
		return false;
	
	//Add the component's administration tab under the "BuddyPress" menu for site administrators
	require ( dirname( __FILE__ ) . '/admin/bp-activity-edit-admin.php' );

	add_submenu_page( 'bp-general-settings', __( 'Activity Edit Admin', 'bp-activity-edit' ), __( 'Activity Edit', 'bp-activity-edit' ), 'manage_options', 'bp-activity-edit-settings', 'etivite_bp_edit_activity_admin' );
}

/* Stolen from Welcome Pack - thanks, Paul! then stolen from boone*/
function etivite_bp_edit_activity_admin_add_action_link( $links, $file ) {
	if ( 'buddypress-edit-activity-stream/bp-activity-edit-loader.php' != $file )
		return $links;

	if ( function_exists( 'bp_core_do_network_admin' ) ) {
		$settings_url = add_query_arg( 'page', 'bp-activity-edit-settings', bp_core_do_network_admin() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) );
	} else {
		$settings_url = add_query_arg( 'page', 'bp-activity-edit-settings', is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) );
	}

	$settings_link = '<a href="' . $settings_url . '">' . __( 'Settings', 'bp-activity-edit' ) . '</a>';
	array_unshift( $links, $settings_link );

	return $links;
}
add_filter( 'plugin_action_links', 'etivite_bp_edit_activity_admin_add_action_link', 10, 2 );
?>
