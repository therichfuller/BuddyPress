=== Plugin Name ===
Contributors: nuprn1, etivite
Donate link: http://etivite.com/donate/
Tags: buddypress, activity stream
Requires at least: PHP 5.2, WordPress 3.2.1, BuddyPress 1.5.1
Tested up to: PHP 5.2.x, WordPress 3.2.1, BuddyPress 1.5.1
Stable tag: 0.5.1

This plugin allows an user to edit their activity stream status update within a specified time period.

== Description ==

** IMPORTANT **
This plugin has been updated for BuddyPress 1.5.1

Allows site admins and users to edit any activity update (except forum topics and replies) within a specified time period.


= Related Links: = 

* <a href="http://etivite.com" title="Plugin Demo Site">Author's Site</a>
* <a href="http://etivite.com/wordpress-plugins/buddypress-edit-activity-stream/">BuddyPress Edit Activity Stream - About Page</a>
* <a href="http://etivite.com/api-hooks/">BuddyPress and bbPress Developer Hook and Filter API Reference</a>


== Installation ==

1. Upload the full directory into your wp-content/plugins directory
2. Activate the plugin at the plugin administration page
3. Adjust settings via the Activity Edit admin page under the BuddyPress Menu


== Frequently Asked Questions ==

= What is the time interval for locking out the edit? =

Please set the time length that conforms to http://www.php.net/manual/en/datetime.formats.relative.php

= How do I change the theme for the edit page? =

Copy the file buddypress-edit-activity-stream/templates/activity/activity-edit.php to your child bp-themes/activity/ directory

= Why can't I edit my activity reply comment? =

Currently the bp-core file does not include a filter on the admin links (reply delete) within the activity comments.

= My question isn't answered here =

Please contact me at http://etivite.com


== Changelog ==

= 0.5.1 =

* BUG: fix network admin settings page on multisite
* FEATURE: support for locale mo files

= 0.5.0 =

* WordPress 3.2.1/BP 1.5.1 only
* BUG: slash issue on edit textarea

= 0.3.1 =

* WordPress 3.0.1/BP 1.2.6 changes - no logic change

= 0.3.0 =

* FEATURE: support for activity stream hashtag plugin

= 0.2.0 =

* BUG: update _usermeta bp_last_update if editing activity->ids match up

= 0.1.0 =

* First [BETA] version


== Upgrade Notice ==

= 0.5.1 =
* BuddyPress 1.5.1 and higher - required.


== Extra Configuration ==

