<?php 

function etivite_bp_edit_activity_admin() {
	global $bp;

	/* If the form has been submitted and the admin referrer checks out, save the settings */
	if ( isset( $_POST['submit'] ) && check_admin_referer('etivite_bp_edit_activity_admin') ) {
	
		if( isset( $_POST['ab_activity_edit_time'] ) && !empty( $_POST['ab_activity_edit_time'] ) ) {
			update_option( 'etivite_bp_edit_activity_lock_date', $_POST['ab_activity_edit_time'] );
		} else {
			update_option( 'etivite_bp_edit_activity_lock_date', '+30 Minutes' );
		}
		
		$updated = true;
	}
	
?>	
	<div class="wrap">
		<h2><?php _e( 'Activity Edit Admin', 'bp-activity-edit' ); ?></h2>

		<?php if ( isset($updated) ) : echo "<div id='message' class='updated fade'><p>" . __( 'Settings Updated.', 'bp-activity-edit' ) . "</p></div>"; endif; ?>

		<form action="<?php echo network_admin_url('admin.php?page=bp-activity-edit-settings'); ?>" name="bp-activity-edit-settings-form" id="bp-activity-edit-settings-form" method="post">

			<h5><?php _e( 'Activity Edit Timeout', 'bp-activity-edit' ); ?></h5>
	
			<table class="form-table">
				<th><label for="ab_activity_edit_time"><?php _e( "Time length:", 'bp-activity-edit' ) ?></label> </th>
				<td><input type="text" name="ab_activity_edit_time" id="ab_activity_edit_time" value="<?php echo get_option( 'etivite_bp_edit_activity_lock_date'); ?>"/></td>
			</table>
			
			<p class="description">Please Note: Time length uses <a href="http://www.php.net/manual/en/datetime.formats.relative.php">Relative Formats</a>; such as: +30 Minutes +1 Hour +2 Hours +1 week +3 Weeks +1 Month +2 Years</p>
			
			<?php wp_nonce_field( 'etivite_bp_edit_activity_admin' ); ?>
			
			<p class="submit"><input type="submit" name="submit" value="<?php _e('Save Settings','bp-activity-edit') ?>"/></p>
			
		</form>
		
		<h3>About:</h3>
		<div id="plugin-about" style="margin-left:15px;">
			
			<p>
			<a href="http://etivite.com/wordpress-plugins/buddypress-edit-activity-stream/">BuddyPress Edit Activity Stream About Page</a><br/> 
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
