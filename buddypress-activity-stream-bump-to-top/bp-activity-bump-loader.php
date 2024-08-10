<?php
/*
Plugin Name: BuddyPress Activity Stream Bump to Top
Plugin URI: http://wordpress.org/extend/plugins/buddypress-activity-stream-bump-to-top/
Description: Bumps an activity record to the top of the stream on activity comment replies
Author: rich @etivite
Author URI: http://etivite.com
License: GNU GENERAL PUBLIC LICENSE 3.0 http://www.gnu.org/licenses/gpl.txt
Version: 0.5.1
Text Domain: bp-activity-bump
Network: true
*/

//TODO - link to show unbumped activity items?
//not yet using the current has_activities BP_Activity_Activity:get way... i may be able to get around it by building a custom db query to pull out a list of activity_ids (meeting the criteria for included bumped activity types with no activity comments) then pass that into BP_Activity_Activity:get_specific

function etivite_bp_activity_bump_init() {

	if ( file_exists( dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' ) )
		load_textdomain( 'bp-activity-bump', dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' );
		
	require( dirname( __FILE__ ) . '/bp-activity-bump.php' );
	
	add_action( bp_core_admin_hook(), 'etivite_bp_activity_bump_admin_add_admin_menu' );
	
}
add_action( 'bp_include', 'etivite_bp_activity_bump_init', 88 );
//add_action( 'bp_init', 'etivite_bp_activity_bump_init', 88 );

//add admin_menu page
function etivite_bp_activity_bump_admin_add_admin_menu() {
	global $bp;
	
	if ( !is_super_admin() )
		return false;

	//Add the component's administration tab under the "BuddyPress" menu for site administrators
	require ( dirname( __FILE__ ) . '/admin/bp-activity-bump-admin.php' );

	add_submenu_page( 'bp-general-settings', __( 'Activity Bump Admin', 'bp-activity-bump' ), __( 'Activity Bump', 'bp-activity-bump' ), 'manage_options', 'bp-activity-bump-settings', 'etivite_bp_activity_bump_admin' );	

}

/* Stolen from Welcome Pack - thanks, Paul! then stolen from boone*/
function etivite_bp_activity_bump_admin_add_action_link( $links, $file ) {
	if ( 'buddypress-activity-stream-bump-to-top/bp-activity-bump-loader.php' != $file )
		return $links;

	if ( function_exists( 'bp_core_do_network_admin' ) ) {
		$settings_url = add_query_arg( 'page', 'bp-activity-bump-settings', bp_core_do_network_admin() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) );
	} else {
		$settings_url = add_query_arg( 'page', 'bp-activity-bump-settings', is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) );
	}

	$settings_link = '<a href="' . $settings_url . '">' . __( 'Settings', 'bp-activity-bump' ) . '</a>';
	array_unshift( $links, $settings_link );

	return $links;
}
add_filter( 'plugin_action_links', 'etivite_bp_activity_bump_admin_add_action_link', 10, 2 );
?>
