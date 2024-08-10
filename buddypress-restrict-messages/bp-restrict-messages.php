<?php
if ( !defined( 'ABSPATH' ) ) exit;

/*
- allow samegroups admin/mods
- flood protection for non admins/mods
* create a command structure to execute array of extended interface classes (simple api - each to return check()-> true|false)
*/


Class BP_Restrict_Messages {
	
	private $data = null;
	private $id = null;
	private $disabled = false;

	function bp_restrict_messages( $id = null ) {
		$this->__construct($id);
	}

	function __construct( $id = null ) {
		global $bp;

		//make sure groups n friends are working
		if ( !bp_is_active( 'friends' ) && !bp_is_active( 'groups' ) )
			return;

		//respect the keymaster
		if ( $bp->loggedin_user->is_super_admin || $bp->loggedin_user->id == $bp->displayed_user->id )
			return;
			
		if ( !$id && $bp->displayed_user->id )
			$this->id = $bp->displayed_user->id;

		if ( !$this->id )
			return;

		//respect the keymaster
		if ( $this->has_cap( 'administrator' ) )
			return;

		$this->populate();
		
		//don't load if we don't care
		if ( !$this->data || ( !$this->data['mode']['user']['enabled'] && !$this->data['mode']['sitewide']['enabled'] ) )
			return;
		
		//check for friend or group network hit
		$this->check();
		
	}

	protected function populate() {
		$this->data = maybe_unserialize( get_option( 'etivite_bp_restrict_messages') );
		
		if ( $this->id && $this->data['mode']['user']['enabled'] ) {
			
			$userdata = maybe_unserialize( get_user_meta( $this->id, 'etivite_bp_restrict_messages', true ) );
			
			if ( !$userdata || empty( $userdata ) ) 
				$this->data['mode']['user']['enabled'] = false;
			
			//if negative on default data prefences then set the user flag false so we don't bother processing
			if ( ( empty($userdata['friends']) || !$userdata['friends']['enabled'] ) && ( empty($userdata['samegroup']) || !$userdata['samegroup']['enabled'] ) && ( bp_is_active( 'follow' ) && ( empty($userdata['follow']) || !$userdata['follow']['enabled'] ) ) )
				$this->data['mode']['user']['enabled'] = false;
			
			//push user pref into our class var
			if ( $this->data['mode']['user']['enabled'] )
				$this->data['pref'] = $userdata;
			
		}
			
	}
	
	protected function check() {
		global $bp;

		$this->disabled = true;

// TODO -we need to replace this into a command stack so others may add their own rules via returning a simple true/false from an interface

		//got friends?
		if ( bp_is_active( 'friends' ) && !empty($this->data['pref']['friends']) && $this->data['pref']['friends']['enabled'] && friends_check_friendship( $bp->loggedin_user->id, $this->id ) ) {
			$this->disabled = false;
			return;
		}

		//got groups?
		if ( bp_is_active( 'groups' ) && !empty($this->data['pref']['samegroup']) && $this->data['pref']['samegroup']['enabled'] && $this->has_shared_group() ) {
			$this->disabled = false;
			return;
		}
					
		//got followers?
		if ( bp_is_active( 'follow' ) && !empty( $this->data['pref']['follow']) && $this->data['pref']['follow']['enabled'] && $this->has_shared_follow() ) {
			$this->disabled = false;
			return;
		}

		//got days?
/*
		if ( !empty( $this->data['pref']['daysregistered']) && $this->data['pref']['daysregistered']['enabled'] && $this->is_days_registered() ) {
			$this->disabled = false;
			return;
		}
*/
	}
	
	protected function has_shared_group() {
		global $bp, $wpdb;
		
		$sql = '';
		switch( $this->data['pref']['samegroup']['status'] ) {
			
			case 'public':
				$sql = "SELECT DISTINCT m.group_id FROM {$bp->groups->table_name_members} m, {$bp->groups->table_name} g WHERE g.status != 'private' AND g.status != 'hidden' AND m.user_id = %d AND m.is_confirmed = 1 AND m.is_banned = 0";
				break;
			case 'private':
				$sql = "SELECT DISTINCT m.group_id FROM {$bp->groups->table_name_members} m, {$bp->groups->table_name} g WHERE g.status != 'hidden' AND m.user_id = %d AND m.is_confirmed = 1 AND m.is_banned = 0";
				break;
			case 'hidden':
				$sql = "SELECT DISTINCT group_id FROM {$bp->groups->table_name_members} WHERE user_id = %d AND is_confirmed = 1 AND is_banned = 0";
			
		}

		$loggedingroup_ids = $wpdb->get_col( $wpdb->prepare( "{$sql}", $bp->loggedin_user->id ) );
		$displayedgroup_ids = $wpdb->get_col( $wpdb->prepare( "{$sql}", $this->id ) );

		if ( !$loggedingroup_ids || !$displayedgroup_ids )
			return false;
		
		foreach ($loggedingroup_ids as $ids)
			if ( array_search( $ids, $displayedgroup_ids ) )
				return true;
	
		return false;
		
	}

	protected function has_cap( $cap ) {
		global $wpdb;
		
		if ( !$cap )
			return false;
				
		$displayedcaps = get_user_meta( $this->id, $wpdb->prefix.'capabilities', true );
		
		if ( !$displayedcaps || empty( $displayedcaps ) )
			return false;

		return array_key_exists( $cap, $displayedcaps );
	}
	
	protected function has_shared_follow() {
		
		$follower = bp_follow_is_following( array( 'leader_id' => $this->id, 'follower_id' => bp_loggedin_user_id() ) );
		$leader = bp_follow_is_following( array( 'leader_id' => bp_loggedin_user_id(), 'follower_id' => $this->id ) );

		if ( $follower && $leader )
			return true;
	
		return false;
	}
	
	protected function is_days_registered() {
		global $bp;
		
		//$user_info = get_userdata( $this->id );
		//$user_info->user_registered
		
//TODO - expand this to check if rec can reply back to sender.

		if ( $this->days_since_check( $bp->loggedin_user->userdata->user_registered ) >= $this->data['pref']['daysregistered']['count'] )
			return true;

		return false;
	}
	
	protected function days_since_check( $older_date, $newer_date = false ) {
	
		if ( !is_numeric( $older_date ) ) {
			$time_chunks = explode( ':', str_replace( ' ', ':', $older_date ) );
			$date_chunks = explode( '-', str_replace( ' ', '-', $older_date ) );
			$older_date = gmmktime( (int)$time_chunks[1], (int)$time_chunks[2], (int)$time_chunks[3], (int)$date_chunks[1], (int)$date_chunks[2], (int)$date_chunks[0] );
		}
	
		$newer_date = ( !$newer_date ) ? gmmktime( gmdate( 'H' ), gmdate( 'i' ), gmdate( 's' ), gmdate( 'n' ), gmdate( 'j' ), gmdate( 'Y' ) ) : $newer_date;
		$since = $newer_date - $older_date;
	
		if ( 0 > $since )
			return 0;
	
		$count = floor( $since / 86400 );

		return $count;
	}
	
	
	public function is_disabled() {
		return $this->disabled;
	}

}


//hook to remove button
function etivite_bp_restrict_messages_setup() {

	$restrictpm = New BP_Restrict_Messages();
	
	if ( bp_is_user() && $restrictpm->is_disabled() )
		remove_action( 'bp_member_header_actions',    'bp_send_private_message_button' );
	
}
add_action( 'bp_before_member_header', 'etivite_bp_restrict_messages_setup' );

//failsafe to catch prior to submiting message
function etivite_bp_restrict_messages_recipients( $r ) {
	global $bp;

	if ( $bp->loggedin_user->is_super_admin )
		return $r;
		
	// Loop the recipients and convert all usernames to user_ids where needed
	foreach( (array) $r as $id => $recipient ) {
		$recipient = trim( $recipient );
		
		if ( empty( $recipient ) )
			continue;

		if ( is_numeric( $recipient ) ) {
			$recipient_id = (int) $recipient;
		} else {
			if ( bp_is_username_compatibility_mode() )
				$recipient_id = bp_core_get_userid( $recipient );
			else
				$recipient_id = bp_core_get_userid_from_nicename( $recipient );
		}

		if ( !$recipient_id )
			continue;
			
		$restrictpm = New BP_Restrict_Messages( $recipient_id );	
		if ( $restrictpm->is_disabled() )
			unset( $r[$id] );
	}

	return $r;

}
add_filter( 'bp_messages_recipients', 'etivite_bp_restrict_messages_recipients' );

//catch autocomplete nonfriend ids
function etivite_bp_restrict_messages_autocomplete_ids( $user_ids ) {
	global $bp;

	if ( $bp->loggedin_user->is_super_admin )
		return $user_ids;

	foreach ( $user_ids as $id => $arr) {
		$restrictpm = New BP_Restrict_Messages( $arr );	
		if ( $restrictpm->is_disabled() )
			unset( $user_ids[$id] );
	}
	
	return $user_ids;

}
add_filter( 'bp_core_autocomplete_ids', 'etivite_bp_restrict_messages_autocomplete_ids', 1, 9999 );

//in case a direct link to compose - remove user from list
function etivite_bp_restrict_messages_recipient_usernames( $r ) {
	global $bp;

	if ( $bp->loggedin_user->is_super_admin )
		return $r;

	$r = explode( ' ', $r );

	foreach ( $r as $recipient => $arr ) {
		$arr = trim( $arr );
		
		if ( $user_id = bp_core_get_userid( $arr ) ) {
			$restrictpm = New BP_Restrict_Messages( $user_id );
	
			if ( $restrictpm->is_disabled() )
				unset( $r[$recipient] );
		}
	}
	
	return implode( ' ', $r );
}
add_filter( 'bp_get_message_get_recipient_usernames', 'etivite_bp_restrict_messages_recipient_usernames' );

//if someone else uses the bp functions
function etivite_bp_restrict_messages_send_private_message_link( $r ) {
	global $bp;

	//no worries on member page - as button is removed first via action hook
	if ( bp_is_user() || $bp->loggedin_user->is_super_admin )
		return $r;

	$restrictpm = New BP_Restrict_Messages();
	
	if ( $restrictpm->is_disabled() )
		return false;
		
	return $r;
}
add_filter( 'bp_get_send_private_message_link', 'etivite_bp_restrict_messages_send_private_message_link' );

function etivite_bp_restrict_messages_setup_nav() {
	global $bp;
       
	if ( $bp->loggedin_user->is_super_admin )
		return;

	//don't bother if in admin mode
	$data = maybe_unserialize( get_option( 'etivite_bp_restrict_messages') );
	if ( !$data['mode']['user']['enabled'] )
		return;

	bp_core_new_subnav_item( array(
		'name' => __( 'Messages', 'buddypress' ),
		'slug' => 'messages',
		'parent_slug' => $bp->settings->slug,
		'parent_url' => $bp->loggedin_user->domain . $bp->settings->slug . '/',
		'screen_function' => 'etivite_bp_restrict_messages_settings_menu',
		'position' => 40,
		'user_has_access' => bp_is_my_profile()
	) );
}
add_action( 'bp_setup_nav', 'etivite_bp_restrict_messages_setup_nav' );

function etivite_bp_restrict_messages_settings_menu() {
	global $bp, $current_user, $bp_settings_updated, $pass_error;
	
	if ( isset( $_POST['submit'] ) && wp_verify_nonce( $_POST['etivite_bp_restrict_messages_user_data'], 'etivite_bp_restrict_messages_user_submit' ) ) {

		$new = Array();

		if( isset($_POST['rg_pref_friends'] ) && !empty($_POST['rg_pref_friends']) && (int)$_POST['rg_pref_friends'] == 1 ) {
			$new['friends']['enabled'] = true;
		} else {
			$new['friends']['enabled'] = false;
		}

		if( isset($_POST['rg_pref_follow'] ) && !empty($_POST['rg_pref_follow']) && (int)$_POST['rg_pref_follow'] == 1 ) {
			$new['follow']['enabled'] = true;
		} else {
			$new['follow']['enabled'] = false;
		}

		if( isset($_POST['rg_pref_groups'] ) && !empty($_POST['rg_pref_groups']) && (int)$_POST['rg_pref_groups'] == 1 ) {
			$new['samegroup']['enabled'] = true;
		} else {
			$new['samegroup']['enabled'] = false;
		}
		
		if( isset( $_POST['rg_pref_groups_status'] ) && !empty( $_POST['rg_pref_groups_status'] ) ) {

			switch ( $_POST['rg_pref_groups_status'] ) {
			    case 'public':
					$new['samegroup']['status'] = 'public';
					break;
			    case 'private':
			    	$new['samegroup']['status'] = 'private';
					break;
			    case 'hidden':
					$new['samegroup']['status'] = 'hidden';
					break;
				default :
					$new['samegroup']['status'] = 'public';
			}
		} else {
			$new['samegroup']['status'] = 'public';
		}

		update_user_meta( $bp->loggedin_user->id, 'etivite_bp_restrict_messages', $new );
		
		bp_core_add_message( 'Settings updated!' );
		bp_core_redirect( bp_displayed_user_domain() . $bp->settings->slug . '/messages/' );
		
	}
	
	add_action( 'bp_template_content', 'etivite_bp_restrict_messages_menu_content' );
	
	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) ); 
}
 
 
 
function etivite_bp_restrict_messages_menu_content() {
	global $bp;
	
	$data = maybe_unserialize( get_user_meta( $bp->loggedin_user->id, 'etivite_bp_restrict_messages', true ) );
?>
		<form action="<?php echo bp_displayed_user_domain() . $bp->settings->slug . '/messages/' ?>" name="bp-restrict-messages-settings-form" id="bp-restrict-messages-settings-form" method="post">			

				<h3>Restrictions</h3>
				<p class="description">Select the restriction of privating messages.</p>
				<table class="form-table">
					<tr>
						<th><label for="rg_pref_friends"><?php _e('Friends Only', 'bp-restrict-messages') ?></label></th>
						<td><input <?php if ( $data && $data['friends']['enabled'] ) { echo 'checked'; } ?> type="checkbox" name="rg_pref_friends" id="rg_pref_friends" value="1" /></td>
					</tr>
					<?php if ( bp_is_active( 'follow' ) ) { ?>
						<tr>
							<th><label for="rg_pref_friends"><?php _e('Follow Connection Only', 'bp-restrict-messages') ?></label></th>
							<td><input <?php if ( $data && $data['follow']['enabled'] ) { echo 'checked'; } ?> type="checkbox" name="rg_pref_follow" id="rg_pref_follow" value="1" /></td>
						</tr>
					<?php } ?>
					<tr>
						<th><label for="rg_pref_groups"><?php _e('Groups Only', 'bp-restrict-messages') ?></label></th>
						<td><input <?php if ( $data && $data['samegroup']['enabled'] ) { echo 'checked'; } ?> type="checkbox" name="rg_pref_groups" id="rg_pref_groups" value="1" /></td>
					</tr>
					<tr>
						<td colspan="2">
							<h4>Group Status</h4>
							<p class="description">Select the group status level</p>
							<table class="form-table">
								<tr>
									<th><label for="rg_pref_groups_status"><?php _e('Public','bp-restrict-messages') ?></label></th>
									<td><input type="radio" <?php if ( $data && $data['samegroup']['status'] == 'public' ) { echo 'checked="checked"'; } ?> name="rg_pref_groups_status" id="rg_pref_groups_status" value="public" <?php if ( $data && !$data['samegroup']['enabled'] ) { echo 'disabled'; } ?> /></td>
								</tr>
								<tr>
									<th><label for="rg_pref_groups_status_1"><?php _e('Private','bp-restrict-messages') ?></label></th>
									<td><input type="radio" <?php if ( $data && $data['samegroup']['status'] == 'private' ) { echo 'checked="checked"'; } ?> name="rg_pref_groups_status" id="rg_pref_groups_status_1" value="private" <?php if ( $data && !$data['samegroup']['enabled'] ) { echo 'disabled'; } ?> /></td>
								</tr>
								<tr>
									<th><label for="rg_pref_groups_status_2"><?php _e('Hidden','bp-restrict-messages') ?></label></th>
									<td><input type="radio" <?php if ( $data && $data['samegroup']['status'] == 'hidden' ) { echo 'checked="checked"'; } ?> name="rg_pref_groups_status" id="rg_pref_groups_status_2" value="hidden" <?php if ( $data && !$data['samegroup']['enabled'] ) { echo 'disabled'; } ?> /></td>
								</tr>
							</table>
							
						</td>
					</tr>
				</table>
				
			<?php wp_nonce_field( 'etivite_bp_restrict_messages_user_submit', 'etivite_bp_restrict_messages_user_data' ); ?>
			
			<p class="submit"><input type="submit" name="submit" value="Save Settings"/></p>
			
		</form>
<script type="text/javascript"> jQuery(document).ready( function() { 

jQuery('#rg_pref_groups').click(function() {
    jQuery('input[name="rg_pref_groups_status"]').attr('disabled', !this.checked);
});
    
 });
</script>

		
<?php
}

?>
