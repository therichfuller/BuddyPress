=== Plugin Name ===
Contributors: nuprn1, etivite
Donate link: http://etivite.com/donate/
Tags: buddypress, activity stream
Requires at least: PHP 5.2, WordPress 3.2.1 BuddyPress 1.5.1
Tested up to: PHP 5.2.x, WordPress 3.2.1, BuddyPress 1.5.1
Stable tag: 0.5.1

This plugin will "bump" an activity record to the top of the stream when activity comment reply is made.

== Description ==

** IMPORTANT **
This plugin has been updated for BuddyPress 1.5.1


This plugin will "bump" an activity record to the top of the stream when an activity comment reply is made.

The original date_recorded is appended to the time_since filter with an additional class named: time-created. Both timestamps are displayed within the activity stream meta div

= Related Links: = 

* <a href="http://etivite.com" title="Plugin Demo Site">Author's Site</a>
* <a href="http://etivite.com/wordpress-plugins/buddypress-activity-stream-bump-to-top/">BuddyPress Activity Stream Bump - About Page</a>
* <a href="http://etivite.com/api-hooks/">BuddyPress and bbPress Developer Hook and Filter API Reference</a>


== Installation ==

1. Upload the full directory into your wp-content/plugins directory
2. Activate the plugin at the plugin administration page
3. Adjust settings via the Activity Bump admin page

== Frequently Asked Questions ==

= How do I exclude a certain activity type from being bumped? =

The wp-admin screen for this plugin allows you to exclude certain activity types from being bumped.

= How does it work? =

When a new comment is posted to an activity record - this plugin will copy the original date_recorded to the activity_meta table. The main activity date_recorded is then overwritten with the last activity comment reply date.

= I really do not like it and want my old dates back =

Have no fear - you can revert the dates back to the original date_recorded via the plugin's admin page. Perform this action before uninstalling.

= My question isn't answered here =

Please contact me on http://etivite.com


== Changelog ==

= 0.5.1 =

* BUG: fix network admin settings page on multisite
* FEATURE: support for locale mo files

= 0.5.0 =
* FEATURE: Restrict activity bump to certain users (site admins, all members, or wp_cap level)
* BUG: updated for bp 1.5.1


= 0.3.1 =

* Added filter bp_activity_bump_time_since to time-since output
* Added 'updated' string next to bump timestamp

= 0.3.0 =

* Plugin released

= 0.1.0 =

* First bp hack version


== Upgrade Notice ==

= 0.5.0 = 
* BuddyPress 1.5.1 and higher - required.

== Extra Configuration ==

add a filter to bp_activity_bump_time_since (date_recorded, $bumpdate, $content) 
