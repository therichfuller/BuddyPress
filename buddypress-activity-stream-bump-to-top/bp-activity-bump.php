<?php
if ( !defined( 'ABSPATH' ) ) exit;

function etivite_bp_activity_bump_comment_posted( $comment_id, $params ) {
	global $bp, $wpdb;

	extract( $params, EXTR_SKIP );

	$activity_parent = bp_activity_get_specific( array( 'activity_ids' => $activity_id ) );

	//make sure we have something
	if ( !$activity_parent = $activity_parent['activities'][0] )
		return;

	//Come and see the violence inherent in the system. Help! Help! I'm being repressed!
	if ( etivite_bp_activity_bump_denied_activity_type_check( $activity_parent->type ) )
		return;

	//make sure our user can bump it up
	if ( !etivite_bp_activity_bump_denied_user_check() )
		return;

	//be nice and save the date_recorded
	if ( !bp_activity_get_meta( $activity_id, 'bp_activity_bump_date_recorded') )
		bp_activity_update_meta( $activity_id, 'bp_activity_bump_date_recorded', $activity_parent->date_recorded );

	$activity = new BP_Activity_Activity( $activity_id );
	$activity->date_recorded = gmdate( "Y-m-d H:i:s" );
	if ( !$activity->save() )
		return false;
}
add_action( 'bp_activity_comment_posted', 'etivite_bp_activity_bump_comment_posted', 1, 2 );


function etivite_bp_activity_bump_time_since( $content, $activity ) {
	global $bp;

	if ( !$bumpdate = bp_activity_get_meta( $activity->id, 'bp_activity_bump_date_recorded') )
		return $content;
	
	$content = '<span class="time-since">' . sprintf( __( ' updated %s', 'bp-activity-bump' ), bp_core_time_since( $activity->date_recorded ) ) . '</span>';

	return apply_filters( 'etivite_bp_activity_bump_time_since', '<span class="time-since time-created">' . sprintf( __( ' %s', 'buddypress' ), bp_core_time_since( $bumpdate ) ) . '</span> &middot; ' . $content, $activity->date_recorded, $bumpdate, $content );
	
}
add_filter( 'bp_activity_time_since', 'etivite_bp_activity_bump_time_since', 1, 2 );


function etivite_bp_activity_bump_denied_activity_type_check( $type ) {

	$types = (array) maybe_unserialize( get_option( 'bp_activity_bump_denied_activity_types') );

	return in_array( $type, apply_filters( 'etivite_bp_activity_bump_denied_activity_types', $types ) );
}
function etivite_bp_activity_bump_denied_user_check() {
	global $bp;
	
	//all, super, wp_cap
	$types = maybe_unserialize( get_option( 'bp_activity_bump_denied_user_types') );
	if ( $types && ( $types['super_admin'] || $types['user_cap'] ) ) {

		if ( current_user_can( $types['user_cap'] ) )
			return true;

		if ( $types['super_admin'] && $bp->loggedin_user->is_super_admin )
			return true;

		return false;
	}
	//default to all members
	return true;
}
?>
