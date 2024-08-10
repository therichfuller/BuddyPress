<?php
/*
Plugin Name: BuddyPress Groups Auto Join Admins and Mods
Plugin URI: http://wordpress.org/extend/plugins/buddypress-groups-autojoin-admins/
Description: Auto-Join users as Adminstrator or Moderator to every group created and demote creator
Author: rich @etivite
Author URI: http://etivite.com
License: GNU GENERAL PUBLIC LICENSE 3.0 http://www.gnu.org/licenses/gpl.txt
Version: 0.1.1
Text Domain: bp-groupsautojoinadmins
Network: true
*/

function etivite_bp_autojoin_admins_init() {

	if ( file_exists( dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' ) )
		load_textdomain( 'bp-groupsautojoinadmins', dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' );
		
	require( dirname( __FILE__ ) . '/bp-groups-autojoin-admins.php' );
	
	add_action( bp_core_admin_hook(), 'etivite_bp_autojoin_admins_add_admin_menu' );
	
}
add_action( 'bp_include', 'etivite_bp_autojoin_admins_init', 88 );
//add_action( 'bp_init', 'etivite_bp_autojoin_admins_init', 88 );

//add admin_menu page
function etivite_bp_autojoin_admins_add_admin_menu() {
	global $bp;
	
	if ( !is_super_admin() )
		return false;

	//Add the component's administration tab under the "BuddyPress" menu for site administrators
	require ( dirname( __FILE__ ) . '/admin/bp-groups-autojoin-admins-admin.php' );

	add_submenu_page( 'bp-general-settings', __( 'Auto Join Admins Admin', 'bp-groupsautojoinadmins' ), __( 'Auto Join Admins', 'bp-groupsautojoinadmins' ), 'manage_options', 'bp-autojoinadmins-settings', 'etivite_bp_autojoin_admins_admin' );

}

/* Stolen from Welcome Pack - thanks, Paul! then stolen from boone*/
function etivite_bp_autojoin_admin_add_action_link( $links, $file ) {
	if ( 'buddypress-groups-autojoin-admins/bp-groups-autojoin-admins-loader.php' != $file )
		return $links;

	if ( function_exists( 'bp_core_do_network_admin' ) ) {
		$settings_url = add_query_arg( 'page', 'bp-autojoinadmins-settings', bp_core_do_network_admin() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) );
	} else {
		$settings_url = add_query_arg( 'page', 'bp-autojoinadmins-settings', is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) );
	}

	$settings_link = '<a href="' . $settings_url . '">' . __( 'Settings', 'bp-groupsautojoinadmins' ) . '</a>';
	array_unshift( $links, $settings_link );

	return $links;
}
add_filter( 'plugin_action_links', 'etivite_bp_autojoin_admin_add_action_link', 10, 2 );
?>
