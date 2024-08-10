<?php
function etivite_bp_autojoin_admins_admin() {
	global $bp;

	if ( isset( $_POST['submit'] ) && check_admin_referer('etivite_bp_groups_autojoin_admin') ) {
			
		$new = Array();

		//check for valid demote
		if( isset( $_POST['gaj_demote_mod'] ) && !empty( $_POST['gaj_demote_mod'] ) ) {

			switch ( $_POST['gaj_demote_mod'] ) {
			    //fall down - sanity check for valid values
			    case 'member':
			    case 'mod':
			        $new['demote_creator'] = $_POST['gaj_demote_mod'];
					break;
				case 'same':
			        $new['demote_creator'] = false;
					break;					
			    default:
					$new['demote_creator'] = false;
			}

		} else {
			$new['demote_creator'] = false;
		}
			
	
		if( isset($_POST['gaj_admin_users'] ) && !empty($_POST['gaj_admin_users']) ) {
			
			$unfiltered_types = explode( ',', $_POST['gaj_admin_users'] );

			foreach( (array) $unfiltered_types as $type ) {
				if ( !empty( $type ) ) {
					
					$suser = get_userdatabylogin( trim($type) );
					if( $suser )  {
						if ( !$new['wp_cap']['admin'] || ( $new['wp_cap']['admin'] && !user_can( $suser->ID, $new['wp_cap']['admin'] ) ) )
							$types[] = $suser->ID;
					}
				}
			}
			
			if ($types) {
				$new['users']['admin'] = $types;
			} else {
				$new['users']['admin'] = false;
			}
			
		} else {
			$new['users']['admin'] = false;
		}

		if( isset($_POST['gaj_mod_users'] ) && !empty($_POST['gaj_mod_users']) ) {
			
			$unfiltered_modusers = explode( ',', $_POST['gaj_mod_users'] );

			foreach( (array) $unfiltered_modusers as $moduser ) {
				if ( !empty( $moduser ) ) {
					$suser = get_userdatabylogin( trim($moduser) );
					if( $suser )  {
							$modusers[] = $suser->ID;
					}
				}
			}
			
			if ( $modusers ) {
				//check if mod user is part of admin users
				if ( $types ) { 
					$modusers = array_diff($modusers, $types);
					$modusers = array_values( $modusers );
				}
				$new['users']['mod'] = $modusers;
			} else {
				$new['users']['mod'] = false;	
			}
			
		} else {
			$new['users']['mod'] = false;
		}
		
		update_option( 'etivite_bp_groups_autojoin_admins', $new );	

		$updated = true;

	}
?>

	<div class="wrap">
		<h2><?php _e( 'Auto Join Group Admins', 'bp-groupsautojoinadmins' ); ?></h2>

		<?php if ( isset($updated) ) : echo "<div id='message' class='updated fade'><p>" . __( 'Settings updated.', 'bp-groupsautojoinadmins' ) . "</p></div>"; endif;

		$data = maybe_unserialize( get_option( 'etivite_bp_groups_autojoin_admins' ) );
		?>
		
		<form action="<?php echo network_admin_url('/admin.php?page=bp-autojoinadmins-settings') ?>" name="groups-autojoin-form" id="groups-autojoin-form" method="post">

			<h4><?php _e( 'Select Default Administrator', 'bp-groupsautojoinadmins' ); ?></h4>
			<table class="form-table">
				<tr>
					<th><label for="gaj_admin_users"><?php _e('Enter Users (by username)','bp-groupsautojoinadmins') ?></label></th>
					<td><textarea rows="5" cols="50" name="gaj_admin_users" id="gaj_admin_users"><?php if ( $data['users']['admin'] ) {
foreach ( $data['users']['admin'] as $key => $luser ) {
	$user_info = get_userdata( $luser );
	if ($user_info) {
		$data['users']['admin'][$key] = $user_info->user_login;
	} else {
		unset( $data['users']['admin'][$key] );
	}
} echo esc_attr( implode( ', ', $data['users']['admin'] ) ); } ?></textarea><br/><?php _e( "Seperate usernames with commas.", 'bp-groupsautojoinadmins' ) ?></td>
				</tr>				
			</table>

			<h4><?php _e( 'Select Default Moderator', 'bp-groupsautojoinadmins' ); ?></h4>
			<table class="form-table">
				<tr>
					<th><label for="gaj_admin_users"><?php _e('Enter Users (by username)','bp-groupsautojoinadmins') ?></label></th>
					<td><textarea rows="5" cols="50" name="gaj_mod_users" id="gaj_mod_users"><?php if ( $data['users']['mod'] ) {
foreach ( $data['users']['mod'] as $key => $luser ) {
	$user_info = get_userdata( $luser );
	if ($user_info) {
		$data['users']['mod'][$key] = $user_info->user_login;
	} else {
		unset( $data['users']['mod'][$key] );
	}
} echo esc_attr( implode( ', ', $data['users']['mod'] ) ); } ?></textarea><br/><?php _e( "Seperate usernames with commas.", 'bp-groupsautojoinadmins' ) ?></td>
				</tr>				
			</table>

			<h4><?php _e( 'Demote Creator', 'bp-groupsautojoinadmins' ); ?></h4>	
			<table class="form-table">
				<tr>
					<th><label for="gaj_demote_mod"><?php _e('Keep as Admin','bp-groupsautojoinadmins') ?></label></th>
					<td><input type="radio" <?php if ( $data['demote_creator'] == false ) { echo 'checked="checked"'; } ?> name="gaj_demote_mod" id="gaj_demote_mod" value="same" /></td>
				</tr>
				<tr>
					<th><label for="gaj_demote_mod_1"><?php _e('Demote to Moderator','bp-groupsautojoinadmins') ?></label></th>
					<td><input type="radio" <?php if ( $data['demote_creator'] == 'mod' ) { echo 'checked="checked"'; } ?> name="gaj_demote_mod" id="gaj_demote_mod_1" value="mod" /></td>
				</tr>			
				<tr>
					<th><label for="gaj_demote_mod_2"><?php _e('Demote to Member','bp-groupsautojoinadmins') ?></label></th>
					<td><input type="radio" <?php if ( $data['demote_creator'] == 'member' ) { echo 'checked="checked"'; } ?> name="gaj_demote_mod" id="gaj_demote_mod_2" value="member" /></td>
				</tr>	
			</table>

			<div class="description">
				<p>*If demoting the group creator - please auto assign an adminstrator otherwise unpredictable results may occur.</p>
			</div>

			<?php wp_nonce_field( 'etivite_bp_groups_autojoin_admin' ); ?>
			
			<p class="submit"><input type="submit" name="submit" value="Save Settings"/></p>
			
		</form>
		
		<h3>About:</h3>
		<div id="plugin-about" style="margin-left:15px;">
			
			<p>
			<a href="http://etivite.com/wordpress-plugins/buddypress-groups-autojoin-admins/">Groups Auto Join Admins - About Page</a><br/> 
			</p>
		
			<div class="plugin-author">
				<strong>Author:</strong> <a href="http://profiles.wordpress.org/users/etivite/"><img style="height: 24px; width: 24px;" class="photo avatar avatar-24" src="http://www.gravatar.com/avatar/9411db5fee0d772ddb8c5d16a92e44e0?s=24&amp;d=monsterid&amp;r=g" alt=""> rich @etivite</a><br/>
				<a href="http://twitter.com/etivite">@etivite</a>
			</div>
		
			<p>
			<a href="http://etivite.com">Author's site</a><br/>
			<a href="http://etivite.com/api-hooks/">Developer Hook and Filter API Reference</a><br/>
			<a href="http://etivite.com/wordpress-plugins/">WordPress Plugins</a><br/>
			</p>
		</div>
		
	</div>
<?php
}

?>
