<?php 

function etivite_bp_restrict_messages_admin() {
	global $bp;

	/* If the form has been submitted and the admin referrer checks out, save the settings */
	if ( isset( $_POST['submit'] ) && check_admin_referer('etivite_bp_restrict_messages_admin') ) {
	
		$new = Array();
		
		//check for valid mode
		if( isset( $_POST['rg_mode'] ) && !empty( $_POST['rg_mode'] ) ) {

			switch ( $_POST['rg_mode'] ) {
				
			    //fall down - sanity check for valid values
			    case 'admin':
					$new['mode']['user']['enabled'] = false;
					$new['mode']['sitewide']['enabled'] = true;
					
					
					//friends
					if( isset($_POST['rg_pref_friends'] ) && !empty($_POST['rg_pref_friends']) && (int)$_POST['rg_pref_friends'] == 1 ) {
						$new['pref']['friends']['enabled'] = true;
					} else {
						$new['pref']['friends']['enabled'] = false;
					}


					//follow
					if( isset($_POST['rg_pref_follow'] ) && !empty($_POST['rg_pref_follow']) && (int)$_POST['rg_pref_follow'] == 1 ) {
						$new['pref']['follow']['enabled'] = true;
					} else {
						$new['pref']['follow']['enabled'] = false;
					}


					//days
					if( isset($_POST['rg_pref_daysregistered'] ) && !empty($_POST['rg_pref_daysregistered']) && (int)$_POST['rg_pref_daysregistered'] == 1 ) {
						
						//grab count
						$f = (int)$_POST['rg_pref_daysregistered_count'];
					
						$enabled = true;
						if ( !$f || $f < 1 ) {
							$f = 0;
							$enabled = false;
						}
						$new['pref']['daysregistered']['enabled'] = $enabled;
						$new['pref']['daysregistered']['count'] = $f;
						
					} else {
						$new['pref']['daysregistered']['enabled'] = false;
						$new['pref']['daysregistered']['count'] = 0;
					}
					

					//groups
					if( isset($_POST['rg_pref_groups'] ) && !empty($_POST['rg_pref_groups']) && (int)$_POST['rg_pref_groups'] == 1 ) {
						$new['pref']['samegroup']['enabled'] = true;
					} else {
						$new['pref']['samegroup']['enabled'] = false;
					}
					
					if( isset( $_POST['rg_pref_groups_status'] ) && !empty( $_POST['rg_pref_groups_status'] ) ) {
			
						switch ( $_POST['rg_pref_groups_status'] ) {
						    case 'public':
								$new['pref']['samegroup']['status'] = 'public';
								break;
						    case 'private':
						    	$new['pref']['samegroup']['status'] = 'private';
								break;
						    case 'hidden':
								$new['pref']['samegroup']['status'] = 'hidden';
								break;
							default :
								$new['pref']['samegroup']['status'] = 'public';
						}
					} else {
						$new['pref']['samegroup']['status'] = 'public';
					}
			    
					
					break;
					
				case 'user':
					$new['mode']['user']['enabled'] = true;
					$new['mode']['sitewide']['enabled'] = false;
					$new['pref']['daysregistered']['enabled'] = false;
					$new['pref']['daysregistered']['count'] = 0;
					$new['pref']['friends']['enabled'] = false;
					$new['pref']['follow']['enabled'] = false;
					$new['pref']['follow']['status'] = 'shared';
					$new['pref']['samegroup']['enabled'] = false;
					$new['pref']['samegroup']['status'] = 'public';
					break;					
			    
			    default:
					$new['mode']['user']['enabled'] = false;
					$new['mode']['sitewide']['enabled'] = false;
								
			}

		} else {
			$new['mode']['user']['enabled'] = false;
			$new['mode']['sitewide']['enabled'] = false;
		}
		
		update_option( 'etivite_bp_restrict_messages', $new );
		
		$updated = true;
	}
	
	$data = maybe_unserialize( get_option( 'etivite_bp_restrict_messages') );
	
	//tidy new data
	if ( empty( $data['pref']['follow'] ) )
		$data['pref']['follow']['enabled'] = false;
	if ( empty( $data['pref']['daysregistered'] ) )  {
		$data['pref']['daysregistered']['enabled'] = false;
		$data['pref']['daysregistered']['count'] = 0;
	}

	// Get the proper URL for submitting the settings form. (Settings API workaround) - boone
	$url_base = function_exists( 'is_network_admin' ) && is_network_admin() ? network_admin_url( 'admin.php?page=bp-restrict-messages-settings' ) : admin_url( 'admin.php?page=bp-restrict-messages-settings' );
	
?>	
	<div class="wrap">
		<h2><?php _e( 'Restrict Messages Admin', 'bp-restrict-messages' ); ?></h2>

		<?php if ( isset($updated) ) : echo "<div id='message' class='updated fade'><p>" . __( 'Settings Updated.', 'bp-restrict-messages' ) . "</p></div>"; endif; ?>
		<form action="<?php echo $url_base ?>" name="bp-restrict-messages-settings-form" id="bp-restrict-messages-settings-form" method="post">			

			<h3>Mode</h3>
			<p class="description">Select if site admin or users can set private messaging restrictions</p>
			<table class="form-table">
				<tr>
					<th><label for="rg_mode"><?php _e('Admin','bp-restrict-messages') ?></label></th>
					<td><input type="radio" <?php if ( $data['mode']['sitewide']['enabled'] ) { echo 'checked="checked"'; } ?> name="rg_mode" id="rg_mode" value="admin" /></td>
				</tr>
				<tr>
					<th><label for="rg_mode_1"><?php _e('User','bp-restrict-messages') ?></label></th>
					<td><input type="radio" <?php if ( $data['mode']['user']['enabled'] ) { echo 'checked="checked"'; } ?> name="rg_mode" id="rg_mode_1" value="user" /></td>
				</tr>
			</table>
			
			<div id="admin-pref" <?php if ( !$data['mode']['sitewide']['enabled'] ) { echo 'style="display:none"'; } ?>>
				<h3>Restrictions</h3>
				<p class="description">Select the restriction of privating messages.</p>
				<table class="form-table">
<?php
/*
					<tr>
						<th><label for="rg_pref_daysregistered"><?php _e('Days Registered (Sender)', 'bp-restrict-messages') ?></label></th>
						<td><input <?php if ( $data['pref']['daysregistered']['enabled'] ) { echo 'checked'; } ?> type="checkbox" name="rg_pref_daysregistered" id="rg_pref_daysregistered" value="1" /> <input type="text" name="rg_pref_daysregistered_count" value="<?php echo $data['pref']['daysregistered']['count']; ?>" /></td>
					</tr>
*/
?>
					<tr>
						<th><label for="rg_pref_friends"><?php _e('Friends Only', 'bp-restrict-messages') ?></label></th>
						<td><input <?php if ( $data['pref']['friends']['enabled'] ) { echo 'checked'; } ?> type="checkbox" name="rg_pref_friends" id="rg_pref_friends" value="1" /></td>
					</tr>
					
					<?php if ( bp_is_active( 'follow' ) ) { ?>
						<tr>
							<th><label for="rg_pref_friends"><?php _e('Follow Connection Only', 'bp-restrict-messages') ?></label></th>
							<td><input <?php if ( $data && $data['pref']['follow']['enabled'] ) { echo 'checked'; } ?> type="checkbox" name="rg_pref_follow" id="rg_pref_follow" value="1" /></td>
						</tr>					
					<?php } ?>
					
					<tr>
						<th><label for="rg_pref_groups"><?php _e('Groups Only', 'bp-restrict-messages') ?></label></th>
						<td><input <?php if ( $data['pref']['samegroup']['enabled'] ) { echo 'checked'; } ?> type="checkbox" name="rg_pref_groups" id="rg_pref_groups" value="1" /></td>
					</tr>
					<tr>
						<td colspan="2">
							<h4>Group Status</h4>
							<p class="description">Select the group status level</p>
							<table class="form-table">
								<tr>
									<th><label for="rg_pref_groups_status"><?php _e('Public','bp-restrict-messages') ?></label></th>
									<td><input type="radio" <?php if ( $data['pref']['samegroup']['status'] == 'public' ) { echo 'checked="checked"'; } ?> name="rg_pref_groups_status" id="rg_pref_groups_status" value="public" /></td>
								</tr>
								<tr>
									<th><label for="rg_pref_groups_status_1"><?php _e('Private','bp-restrict-messages') ?></label></th>
									<td><input type="radio" <?php if ( $data['pref']['samegroup']['status'] == 'private' ) { echo 'checked="checked"'; } ?> name="rg_pref_groups_status" id="rg_pref_groups_status_1" value="private" /></td>
								</tr>
								<tr>
									<th><label for="rg_pref_groups_status_2"><?php _e('Hidden','bp-restrict-messages') ?></label></th>
									<td><input type="radio" <?php if ( $data['pref']['samegroup']['status'] == 'hidden' ) { echo 'checked="checked"'; } ?> name="rg_pref_groups_status" id="rg_pref_groups_status_2" value="hidden" /></td>
								</tr>
							</table>
							
						</td>
					</tr>
				</table>
			</div>
			
			<?php wp_nonce_field( 'etivite_bp_restrict_messages_admin' ); ?>
			
			<p class="submit"><input type="submit" name="submit" value="Save Settings"/></p>
			
		</form>
		
		<h3>About:</h3>
		<div id="plugin-about" style="margin-left:15px;">
			
			<p>
			<a href="http://etivite.com/wordpress-plugins/buddypress-restrict-messages/">Restrict Messages - About Page</a><br/> 
			</p>
		
			<div class="plugin-author">
				<strong>Author:</strong> <a href="http://profiles.wordpress.org/users/etivite/"><img style="height: 24px; width: 24px;" class="photo avatar avatar-24" src="http://www.gravatar.com/avatar/9411db5fee0d772ddb8c5d16a92e44e0?s=24&amp;d=monsterid&amp;r=g" alt=""> rich @etivite</a><br/>
				<a href="http://twitter.com/etivite">@etivite</a> <a href="https://plus.google.com/114440793706284941584">+etivite</a>
			</div>
		
			<p>
			<a href="http://etivite.com">Author's site</a><br/>
			<a href="http://etivite.com/api-hooks/">Developer Hook and Filter API Reference</a><br/>
			<a href="http://etivite.com/wordpress-plugins/">WordPress Plugins</a><br/>
			</p>
		</div>
		
<script type="text/javascript"> jQuery(document).ready( function() { 
	jQuery('input[name="rg_mode"]').click(function() {
		var selected = jQuery(this).val();
       
		if (selected == 'admin') {
			jQuery('#admin-pref').slideDown();
		} else {
			jQuery('#admin-pref').slideUp();
		}
    });
    
 });
</script>
		
	</div>
<?php
}

?>
