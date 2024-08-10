<?php 
function etivite_bp_activity_extras_admin_unique_types( ) {
	global $bp, $wpdb;
	
	$count = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT a.type FROM {$bp->activity->table_name} a ORDER BY a.date_recorded DESC" ) );
	
	return $count;
}

function etivite_bp_activity_extras_admin_type_check( $type, $currenttypes ) {
	
	if ( !$currenttypes )
		return;
	
	if ( in_array( $type, $currenttypes) )
		echo 'checked';
		
	return;
}

function etivite_bp_activity_extras_admin() {
	global $bp;

	/* If the form has been submitted and the admin referrer checks out, save the settings */
	if ( isset( $_POST['submit'] ) && check_admin_referer('etivite_bp_activity_extras_admin') ) {
	
		$new = Array();
	
		if( isset($_POST['ab_login_activity_types'] ) && !empty($_POST['ab_login_activity_types']) ) {
			$new['comment_login']['activity_types'] = $_POST['ab_login_activity_types'];
		} else {
			$new['comment_login']['activity_types'] = false;
		}

		if( isset($_POST['ab_can_activity_types'] ) && !empty($_POST['ab_can_activity_types']) ) {
			//TODO - need to check for blog/forum commenting and remove if selected
			
			$new['comment_can']['activity_types'] = $_POST['ab_can_activity_types'];
		} else {
			$new['comment_can']['activity_types'] = false;
		}
		
		if( isset($_POST['ab_can_super_admin'] ) && !empty($_POST['ab_can_super_admin']) && (int)$_POST['ab_can_super_admin'] == 1 ) {
			$new['comment_can']['super_admin'] = true;
		} else {
			$new['comment_can']['super_admin'] = false;
		}	

		if( isset($_POST['ab_can_favorite_types'] ) && !empty($_POST['ab_can_favorite_types']) ) {
			$new['favorite_can']['activity_types'] = $_POST['ab_can_favorite_types'];
		} else {
			$new['favorite_can']['activity_types'] = false;
		}
		
		if( isset($_POST['ab_favorite_can_super_admin'] ) && !empty($_POST['ab_favorite_can_super_admin']) && (int)$_POST['ab_favorite_can_super_admin'] == 1 ) {
			$new['favorite_can']['super_admin'] = true;
		} else {
			$new['favorite_can']['super_admin'] = false;
		}	

		if( isset($_POST['ab_entry_meta'] ) && !empty($_POST['ab_entry_meta']) && (int)$_POST['ab_entry_meta'] == 1 ) {
			$new['entry_meta']['enabled'] = true;
		} else {
			$new['entry_meta']['enabled'] = false;
		}	

		if( isset($_POST['ab_entry_css'] ) && !empty($_POST['ab_entry_css']) && (int)$_POST['ab_entry_css'] == 1 ) {
			$new['entry_css']['enabled'] = true;
		} else {
			$new['entry_css']['enabled'] = false;
		}

		
		update_option( 'etivite_bp_activity_stream_extras', $new );
		
		$updated = true;
	}
	
	$data = maybe_unserialize( get_option( 'etivite_bp_activity_stream_extras') );	

	// Get the proper URL for submitting the settings form. (Settings API workaround) - boone
	$url_base = function_exists( 'is_network_admin' ) && is_network_admin() ? network_admin_url( 'admin.php?page=bp-activity-extras-settings' ) : admin_url( 'admin.php?page=bp-activity-extras-settings' );
	
	$disable_blogforum = isset( $bp->site_options['bp-disable-blogforum-comments'] ) ? $bp->site_options['bp-disable-blogforum-comments'] : false;
	
?>	
	<div class="wrap">
		<h2><?php _e( 'Activity Stream Extras Admin', 'bp-activity-extras' ); ?></h2>

		<?php if ( isset($updated) ) : echo "<div id='message' class='updated fade'><p>" . __( 'Settings Updated.', 'bp-activity-extras' ) . "</p></div>"; endif; ?>
		<form action="<?php echo $url_base ?>" name="bp-activity-extras-settings-form" id="bp-activity-extras-settings-form" method="post">
			
			<h3>Disable Commenting</h3>
			<p class="description">Select which activity types to disable commentings.</p>

			<table class="form-table">
				<?php
				$uniquetypes = etivite_bp_activity_extras_admin_unique_types();

				foreach ($uniquetypes as $types) { 
					$can_display = true;
					if ( $disable_blogforum ) {
						if ( 'new_blog_post' == $types->type || 'new_blog_comment' == $types->type || 'new_forum_topic' == $types->type || 'new_forum_post' == $types->type )
							$can_display = false;
					}
					
					if ( $can_display ) { ?>
					<tr>
						<th><label for="type-can-<?php echo $types->type ?>"><?php echo $types->type ?></label></th>
						<td><input id="type-can-<?php echo $types->type ?>" type="checkbox" <?php etivite_bp_activity_extras_admin_type_check( $types->type, $data['comment_can']['activity_types'] ); ?> name="ab_can_activity_types[]" value="<?php echo $types->type ?>" /></td>
					</tr>
					<?php } ?>
				<?php } ?>
				
				<tr>
					<th><label for="ab_can_super_admin"><?php _e('Allow Site/Group Admins', 'bp-activity-extras') ?></label></th>
					<td><input <?php if ( $data['comment_can']['super_admin'] ) { echo 'checked'; } ?> type="checkbox" name="ab_can_super_admin" id="ab_can_super_admin" value="1" /></td>
				</tr>
				
			</table>			

			<h3>Disable Favoriting</h3>
			<p class="description">Select which activity types to disable favorite action on.</p>

			<table class="form-table">
				<?php
				$uniquetypes = etivite_bp_activity_extras_admin_unique_types();

				foreach ($uniquetypes as $types) { ?>
					<tr>
						<th><label for="fav-type-can-<?php echo $types->type ?>"><?php echo $types->type ?></label></th>
						<td><input id="fav-type-can-<?php echo $types->type ?>" type="checkbox" <?php etivite_bp_activity_extras_admin_type_check( $types->type, $data['favorite_can']['activity_types'] ); ?> name="ab_can_favorite_types[]" value="<?php echo $types->type ?>" /></td>
					</tr>
				<?php } ?>
				
				<tr>
					<th><label for="ab_favorite_can_super_admin"><?php _e('Allow Site/Group Admins', 'bp-activity-extras') ?></label></th>
					<td><input <?php if ( $data['favorite_can']['super_admin'] ) { echo 'checked'; } ?> type="checkbox" name="ab_favorite_can_super_admin" id="ab_favorite_can_super_admin" value="1" /></td>
				</tr>
				
			</table>
			
			<h3>Force Login to View Comments</h3>
			<p class="description">Select which activity types to force login/register in order to view the comments.</p>

			<table class="form-table">
				<?php
				foreach ($uniquetypes as $types) {
					$can_display = true;
					if ( $disable_blogforum ) {
						if ( 'new_blog_post' == $types->type || 'new_blog_comment' == $types->type || 'new_forum_topic' == $types->type || 'new_forum_post' == $types->type )
							$can_display = false;
					}
					
					if ( $can_display ) { ?>
					<tr>
						<th><label for="type-login-<?php echo $types->type ?>"><?php echo $types->type ?></label></th>
						<td><input id="type-login-<?php echo $types->type ?>" type="checkbox" <?php etivite_bp_activity_extras_admin_type_check( $types->type, $data['comment_login']['activity_types'] ); ?> name="ab_login_activity_types[]" value="<?php echo $types->type ?>" /></td>
					</tr>
					<?php } ?>
				<?php } ?>
			</table>
			
			<h3>Add 'View' Buttons</h3>
			<p class="description">Add view buttons to the activity stream meta section (ie, View Blog Post, View Activity Status)</p>

			<table class="form-table">
				<tr>
					<th><label for="ab_entry_meta"><?php _e('Display Extra View Buttons', 'bp-activity-extras') ?></label></th>
					<td><input <?php if ( $data['entry_meta']['enabled'] ) { echo 'checked'; } ?> type="checkbox" name="ab_entry_meta" id="ab_entry_meta" value="1" /></td>
				</tr>				
			</table>

			<h3>Add CSS</h3>
			<p class="description">Add css class to each activity entry (ie, friend, group, admin, user).</p>

			<?php if ( $data['entry_css']['enabled'] ) { ?><p class="description">CSS classes for your stylesheet: <pre>highlight-friend highlight-follow highlight-self</pre></p><?php } ?>

			<table class="form-table">
				<tr>
					<th><label for="ab_entry_css"><?php _e('Add CSS classes to activities', 'bp-activity-extras') ?></label></th>
					<td><input <?php if ( $data['entry_css']['enabled'] ) { echo 'checked'; } ?> type="checkbox" name="ab_entry_css" id="ab_entry_css" value="1" /></td>
				</tr>				
			</table>
			
			<?php wp_nonce_field( 'etivite_bp_activity_extras_admin' ); ?>
			
			<p class="submit"><input type="submit" name="submit" value="Save Settings"/></p>
			
		</form>
		
		<h3>About:</h3>
		<div id="plugin-about" style="margin-left:15px;">
			
			<p>
			<a href="http://etivite.com/wordpress-plugins/buddypress-activity-stream-extras/">Activity Stream Extras - About Page</a><br/> 
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
		
	</div>
<?php
}

?>
