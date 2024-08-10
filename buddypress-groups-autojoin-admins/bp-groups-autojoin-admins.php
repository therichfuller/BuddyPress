<?php
if ( !defined( 'ABSPATH' ) ) exit;

//if a new group is created - lets always make sure our default admin is attached
function etivite_bp_autojoin_admins_created_group( $args ) {
	global $bp;
	
	if ( !$args )
		return;
	
	$add = maybe_unserialize( get_option( 'etivite_bp_groups_autojoin_admins' ) );
	if ( $add ) {

		//if demote creator to mod/member
		if ( $add['demote_mod'] && !$bp->loggedin_user->is_super_admin ) {
			
			if ( $add['demote_mod'] == 'mod' ) {
				$member = new BP_Groups_Member( $bp->loggedin_user->id, $args );
				$member->is_admin = 0;
				$member->is_mod = 1;
				$member->user_title = __( 'Group Mod', 'buddypress' );
				$member->date_modified = bp_core_current_time();
				$member->save();
			} elseif ( $add['demote_mod'] == 'member' ) {
				$member = new BP_Groups_Member( $bp->loggedin_user->id, $args );
				$member->is_admin = 0;
				$member->is_mod = 0;
				$member->user_title = '';
				$member->date_modified = bp_core_current_time();
				$member->save();
			}
			
		}

		//loop admin ids
		foreach( $add['users']['admin'] as $adminuser ) {
			$member = new BP_Groups_Member( $adminuser, $args );
			$member->is_admin = 1;
			$member->is_mod = 0;
			$member->user_title = __( 'Group Admin', 'buddypress' );
			$member->is_confirmed = 1;
			$member->date_modified = bp_core_current_time();
			$member->save();
		}
		
		//loop mod ids
		foreach( $add['users']['mod'] as $moduser ) {
			$member = new BP_Groups_Member( $moduser, $args );
			$member->is_admin = 0;
			$member->is_mod = 1;
			$member->user_title = __( 'Group Mod', 'buddypress' );
			$member->is_confirmed = 1;
			$member->date_modified = bp_core_current_time();
			$member->save();
		}
	}
}
add_action( 'groups_created_group', 'etivite_bp_autojoin_admins_created_group', 1, 1);

?>
