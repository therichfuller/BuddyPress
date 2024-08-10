<?php
if ( !defined( 'ABSPATH' ) ) exit;

function etivite_bp_activity_extras_css_class( $class ) {
	global $bp, $activities_template;

	if ( !is_user_logged_in() )
		return $class;

	if ( bp_is_active('friends') && $activities_template->activity->user_id != $bp->loggedin_user->id ) {
		if ( friends_check_friendship_status( $bp->loggedin_user->id, $activities_template->activity->user_id ) == 'is_friend' )
			return trim( $class . ' highlight-friend' );
	}
	
	if ( function_exists('bp_follow_is_following') && $activities_template->activity->user_id != $bp->loggedin_user->id ) {
		if ( bp_follow_is_following( array( 'leader_id' => $activities_template->activity->user_id, 'follower_id' => $bp->loggedin_user->id ) ) )
			return trim( $class . ' highlight-follow' );
	}
	
	if ( $activities_template->activity->user_id == $bp->loggedin_user->id )
		return trim( $class .' highlight-self' );
		
	//if admin?
	
	//if item_admin?

	return $class;

}

function etivite_bp_activity_extras_entry_meta() {

	if ( bp_get_activity_object_name() == 'blogs' ) {
		switch ( bp_get_activity_type() ) {
			case 'new_blog':
				echo '<a class="button item-button bp-secondary-action view-activity-blog-post" href="'. bp_get_activity_thread_permalink() .'">'. __( "View Blog", "bp-activity-extras" ) .'</a>';
				break;
			case 'new_blog_post':
				echo '<a class="button item-button bp-secondary-action view-activity-blog-post" href="'. bp_get_activity_thread_permalink() .'">'. __( "View Blog Post", "bp-activity-extras" ) .'</a>';
				break;
			case 'new_blog_comment':
				echo '<a class="button item-button bp-secondary-action view-activity-blog-comment" href="'. bp_get_activity_thread_permalink() .'">'. __( "View Blog Comment", "bp-activity-extras" ) .'</a>';
				break;
		}	
	}
	
	if ( bp_get_activity_object_name() == 'activity' && bp_get_activity_type() == 'activity_update' )
		echo '<a class="button item-button bp-secondary-action view-activity-status" href="'. bp_get_activity_thread_permalink() .'">'. __( "View Activity Status", "bp-activity-extras" ) .'</a>';
}

function etivite_bp_activity_extras_before_activity_loop() {

	$data = maybe_unserialize( get_option( 'etivite_bp_activity_stream_extras') );

	if ( $data['entry_meta']['enabled'] )
		add_action('bp_activity_entry_meta', 'etivite_bp_activity_extras_entry_meta');
	
	if ( $data['entry_css']['enabled'] )	
		add_filter( 'bp_get_activity_css_class', 'etivite_bp_activity_extras_css_class' );
	
}
add_action( 'bp_before_activity_loop', 'etivite_bp_activity_extras_before_activity_loop' );



//block only activity stream replies - show parent
function etivite_bp_activity_extras_get_comments() {

	if ( is_user_logged_in() )
		return;

	if ( !etivite_bp_activity_extras_get_comments_hide_check( bp_get_activity_type() ) )
		return;
		
	add_filter( 'bp_activity_get_comment_count', 'etivite_bp_activity_extras_remove_comment_count', 9999 );

	echo '<div class="activity-comments acomment-loginrequired"><ul><li><div class="acomment-avatar"></div><div class="acomment-meta"></div><div class="acomment-content">';

	echo sprintf( __( 'To view this reply - please <a rel="nofollow" href="%s" title="Create an account">log in</a> first.', 'buddypress' ), bp_get_root_domain() . '/wp-login.php?redirect_to=' . urlencode( bp_activity_get_permalink( bp_get_activity_id() ) ) );
	if ( bp_get_signup_allowed() )
		echo '<span class="acomment-loginlink">'. sprintf( __( ' You can also <a rel="nofollow" href="%s" title="Create an account">create an account</a>.', 'buddypress' ), bp_get_signup_page(false) ) .'</span>';

	echo '</div><div class="acomment-options"></div></li></ul></div>';

}
add_action( 'bp_before_activity_entry_comments', 'etivite_bp_activity_extras_get_comments' );


function etivite_bp_activity_extras_remove_comment_count( $c ) {
	return 0;
}
//

function etivite_bp_activity_extras_feed_item_description( $content ) {
	
	if ( is_user_logged_in() )
		return;

	if ( !etivite_bp_activity_extras_get_comments_hide_check( bp_get_activity_type() ) )
		return;
	
	$content = sprintf( __( 'To view this reply - please <a rel="nofollow" href="%s" title="Create an account">log in</a> first.', 'buddypress' ), $bp->root_domain . '/wp-login.php?redirect_to=' . urlencode( bp_activity_get_permalink( $activities_template->activity->id ) ) );
	if ( bp_get_signup_allowed() )
		$content .= sprintf( __( ' You can also <a rel="nofollow" href="%s" title="Create an account">create an account</a>.', 'buddypress' ), bp_get_signup_page(false) );

	return ent2ncr( convert_chars( str_replace( '%s', '', $content ) ) );
	
}
add_filter( 'bp_get_activity_feed_item_description', 'etivite_bp_activity_extras_feed_item_description', 9999 );

//any type here to define
function etivite_bp_activity_extras_get_comments_hide_check( $type ) {
	$types = maybe_unserialize( get_option( 'etivite_bp_activity_stream_extras') );

	if ( !$types['comment_login']['activity_types'] )
		return false;
		
	return in_array( $type, $types['comment_login']['activity_types'] );
}


//block commenting on certain activity types selected via admin settings
function etivite_bp_activity_extras_can_comment( $can_comment ) {
	if ( etivite_bp_activity_extras_can_comment_check( bp_get_activity_type(), $can_comment ) ) {
		return true;
	} else {
		return false;
	}
}
add_filter( 'bp_activity_can_comment', 'etivite_bp_activity_extras_can_comment', 9999 );

function etivite_bp_activity_extras_can_comment_reply( $can_comment, $comment ) {
	if ( etivite_bp_activity_extras_can_comment_check( $comment->type, $can_comment ) ) {
		return true;
	} else {
		return false;
	}
}
add_filter( 'bp_activity_can_comment_reply', 'etivite_bp_activity_extras_can_comment_reply', 9999, 2 );

function etivite_bp_activity_extras_can_comment_check( $type, $can_comment ) {
	global $bp;
	
	$types = maybe_unserialize( get_option( 'etivite_bp_activity_stream_extras') );

	//keep the chain linked
	$can_c = $can_comment;
	
	//this type is predetermined for blockage
	if ( $types['comment_can']['activity_types'] && in_array( $type, $types['comment_can']['activity_types'] ) )
		$can_c = false;
		
	//allow admins to comment on all types?
	if ( $types['comment_can']['super_admin'] && ( $bp->loggedin_user->is_super_admin || ($bp->is_item_admin && $bp->is_single_item) ) )
		$can_c = true;
		
	return $can_c;
	
}

function etivite_bp_activity_extras_can_favorite( $can_fav ) {
	if ( etivite_bp_activity_extras_can_favorite_check( bp_get_activity_type(), $can_fav ) ) {
		return true;
	} else {
		return false;
	}
}
add_filter( 'bp_activity_can_favorite', 'etivite_bp_activity_extras_can_favorite', 9999 );

function etivite_bp_activity_extras_can_favorite_check( $type, $can_fav ) {
	global $bp;
	
	$types = maybe_unserialize( get_option( 'etivite_bp_activity_stream_extras') );

	//keep the chain linked
	$can_c = $can_fav;
	
	//this type is predetermined for blockage
	if ( $types['favorite_can']['activity_types'] && in_array( $type, $types['favorite_can']['activity_types'] ) )
		$can_c = false;
		
	//allow admins to comment on all types?
	if ( $types['favorite_can']['super_admin'] && ( $bp->loggedin_user->is_super_admin || ($bp->is_item_admin && $bp->is_single_item) ) )
		$can_c = true;
		
	return $can_c;
	
}

?>
