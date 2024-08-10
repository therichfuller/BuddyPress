<?php
if ( !defined( 'ABSPATH' ) ) exit;

function etivite_bp_edit_activity_add_edit_link() {
	global $bp;

	if ( !bp_activity_user_can_delete() )
		return;

	//not for forums posts/topics - already auto updates on forum edits
	if ( 'new_forum_topic' == bp_get_activity_type() || 'new_forum_post' == bp_get_activity_type() )
		return;

	//not for minor updates... status only
	if ( !$bp->loggedin_user->is_super_admin && bp_get_activity_user_id() == $bp->loggedin_user->id && 'activity_update' != bp_get_activity_type() )
		return;
		
	//$bp->is_item_admin && $bp->is_single_item

	//Come and see the violence inherent in the system. Help! Help! I'm being repressed!
	if ( !etivite_bp_edit_activity_check_date_recorded( bp_get_activity_date_recorded() ) )
		return;
	
	echo apply_filters( 'etivite_bp_edit_activity_edit_link', '<a rel="nofollow" href="' . $bp->root_domain . '/' . bp_get_activity_slug() . '/edit/' . bp_get_activity_id() . '" class="button item-button bp-secondary-action edit-activity">' . __( 'Edit', 'buddypress' ) . '</a>' );
}
add_action( 'bp_activity_entry_meta', 'etivite_bp_edit_activity_add_edit_link', 1 );

//check if it falls within our set edit time limit
function etivite_bp_edit_activity_check_date_recorded( $date_recorded ) {
	global $bp;
	
	if ( !$date_recorded )
		return false;
		
	if ( $bp->loggedin_user->is_super_admin )
		return true;

	//http://www.php.net/manual/en/datetime.formats.relative.php
	$lock_date = get_option( 'etivite_bp_edit_activity_lock_date');
	if (!$lock_date) $lock_date = '+30 Minutes';

	if ( strtotime( bp_core_current_time() ) <= strtotime( $lock_date, strtotime( $date_recorded ) ) )
		return true;

	return false;
}

function etivite_bp_edit_activity_body_class( $wp_classes ) {
	$wp_classes[] = 'activity-permalink';
	
	return $wp_classes;
}

function etivite_bp_edit_activity_action_edit_router() {
	global $bp, $activity_edit_template;
	
	// Do not proceed if user is not logged in, not viewing activity, or not posting
	if ( !is_user_logged_in() || !bp_is_activity_component() || !bp_is_current_action( 'edit' ) )
		return false;

	if ( empty( $bp->action_variables[0] ) || !is_numeric( $bp->action_variables[0] ) )
		return false;

	$activity_id = $bp->action_variables[0];

	/* Get the activity details */
	$activity_edit_template = bp_activity_get_specific( array( 'activity_ids' => $activity_id ) );

	if ( !$activity = $activity_edit_template['activities'][0] )
		bp_core_redirect( $bp->root_domain );

	/* Check access */
	if ( !$bp->loggedin_user->is_super_admin && $activity->user_id != $bp->loggedin_user->id )
		bp_core_redirect( bp_activity_get_permalink( $activity->id ) );

	/* Check save */
	if ( isset( $_POST['save_changes'] ) ) {

		//Come and see the violence inherent in the system. Help! Help! I'm being repressed!
		if ( !etivite_bp_edit_activity_check_date_recorded( $activity->date_recorded ) )
			bp_core_redirect( bp_activity_get_permalink( $activity->id) );

		/* Check the nonce */
		check_admin_referer( 'etivite_bp_edit_activity_post'. $activity->id );

		if ( !$bp->loggedin_user->is_super_admin )
			$new_action = $activity->action;

		if ( $bp->loggedin_user->is_super_admin && isset( $_POST['activity_action'] ) && !empty( $_POST['activity_action'] ) )
			$new_action = $_POST['activity_action'];

		$new_content = $activity->content;

		if ( isset( $_POST['activity_content'] ) && !empty( $_POST['activity_content'] ) )
			$new_content = $_POST['activity_content'];

		$new_atcontent = bp_activity_at_name_filter( $new_content );
		
		$activity_id = bp_activity_add( array( 'id' => $activity->id, 'action' => $new_action, 'content' => apply_filters( 'etivite_bp_edit_activity_action_edit_content', $new_atcontent ), 'component' => $activity->component, 'type' => $activity->type, 'primary_link' => $activity->primary_link, 'user_id' => $activity->user_id, 'item_id' => $activity->item_id, 'secondary_item_id' => $activity->secondary_item_id, 'recorded_time' => $activity->date_recorded, 'hide_sitewide' => $activity->hide_sitewide ) );
		
		//need to update bp_latest_update on profile page - but have to check if this info is there first
		if ( $activity_id && $activity->type == 'activity_update' && $activity->component == $bp->activity->id ) {

			$profile_status = maybe_unserialize( get_user_meta( $activity->user_id, 'bp_latest_update' ) );

			if ( $profile_status && $profile_status['id'] == $activity->id ) {
				update_usermeta( $activity->user_id, 'bp_latest_update', array( 'id' => $activity->id, 'content' => wp_filter_kses( $new_content ) ) );
			}
		
		}
	
		
		bp_core_redirect( bp_activity_get_permalink( $activity->id ) );
	}
	
	add_filter( 'body_class', 'etivite_bp_edit_activity_body_class', 10, 1 );

	/* else edit */
	bp_core_load_template( 'activity/activity-edit' );
	
}
add_action( 'wp', 'etivite_bp_edit_activity_action_edit_router', 3 );

function etivite_bp_edit_activity_load_template_filter( $found_template, $templates ) {
	global $bp;

	/**
	 * Only filter the template location when we're on the follow component pages.
	 */
	if ( $bp->current_component != $bp->activity->slug || $bp->current_action != 'edit' )
		return $found_template;

	foreach ( (array) $templates as $template ) {
		if ( file_exists( STYLESHEETPATH . '/' . $template ) )
			$filtered_templates[] = STYLESHEETPATH . '/' . $template;
		else
			$filtered_templates[] = dirname( __FILE__ ) . '/templates/' . $template;
	}

	$found_template = $filtered_templates[0];

	return apply_filters( 'etivite_bp_edit_activity_load_template_filter', $found_template );
}
add_filter( 'bp_located_template', 'etivite_bp_edit_activity_load_template_filter', 10, 2 );



function etivite_bp_edit_action() {
	echo etivite_bp_edit_get_action();
}
	function etivite_bp_edit_get_action() {
		global $bp, $activity_edit_template;

		return apply_filters( 'etivite_bp_edit_get_action', $bp->root_domain . '/' . $bp->activity->slug . '/edit/' . $activity_edit_template['activities'][0]->id );
	}


function etivite_bp_edit_the_activity() {
	global $activity_edit_template;
	return $activity_edit_template['activities'][0];
}

function etivite_bp_edit_the_activity_id() {
	echo etivite_bp_edit_get_the_activity_id();
}
	function etivite_bp_edit_get_the_activity_id() {
		global $activity_edit_template;
		return apply_filters( 'etivite_bp_edit_get_the_activity_id', $activity_edit_template['activities'][0]->id );
	}

function etivite_bp_edit_the_activity_action() {
	echo etivite_bp_edit_get_the_activity_action();
}
	function etivite_bp_edit_get_the_activity_action() {
		global $activity_edit_template;
		return apply_filters( 'etivite_bp_edit_get_the_activity_action', esc_attr( $activity_edit_template['activities'][0]->action ) );
	}

function etivite_bp_edit_the_activity_content() {
	echo etivite_bp_edit_get_the_activity_content();
}
	function etivite_bp_edit_get_the_activity_content() {
		global $activity_edit_template;
		return apply_filters( 'etivite_bp_edit_get_the_activity_content', stripslashes( esc_attr( $activity_edit_template['activities'][0]->content ) ) );
	}
	
function etivite_bp_edit_the_avatar($args) {
	echo etivite_bp_edit_get_the_avatar($args);
}
	function etivite_bp_edit_get_the_avatar($args) {
		global $bp, $activity_edit_template;

		$defaults = array(
			'type' => 'thumb',
			'width' => 100,
			'height' => 100,
			'class' => 'avatar',
			'alt' => __( 'Avatar', 'buddypress' ),
			'email' => false
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		$item_id = false;
		if ( (int)$activity_edit_template['activities'][0]->user_id )
			$item_id = $activity_edit_template['activities'][0]->user_id;
		else if ( $activity_edit_template['activities'][0]->item_id )
			$item_id = $activity_edit_template['activities'][0]->item_id;

		$object = 'user';
		if ( $bp->groups->id == $activity_edit_template['activities'][0]->component && !(int) $activity_edit_template['activities'][0]->user_id )
			$object = 'group';
		if ( $bp->blogs->id == $activity_edit_template['activities'][0]->component && !(int) $activity_edit_template['activities'][0]->user_id )
			$object = 'blog';
		
		return apply_filters( 'bp_get_activity_avatar', bp_core_fetch_avatar( array( 'item_id' => $item_id, 'object' => $object, 'type' => $type, 'alt' => $alt, 'class' => $class, 'width' => $width, 'height' => $height ) ) );
	}
?>
