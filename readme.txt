=== Rollover Themes List ===
Contributors: dsader
Donate link: http://dsader.snowotherway.org
Tags: multisite, network, themes, themes table, themes list, rollover themes, rollover themes list, replace themes page, theme tags, set default theme
Requires at least: 3.2.1
Tested up to: 3.2.1
Stable tag: Trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

An "mu-plugin" to replaces default Appearance->Themes page. Themes list 100 themes per page, but only one screenshot until mouse rollover preview.

== Description ==

I run a WP3 multisite network with a couple hundred blogs and I have installed a couple hundred themes. The current themes page eats a lot of bandwidth by loading every screen shot on the page, and only shows 15 themes per page. And although most themes have tags, there is no tag filter to search through themes.

So I replaced the default Appearance->Themes page. Themes list in a table a 100 themes per page, but no screenshot until mouse rollover preview. Themes table filters by clicking tag links or using the dropdown tag filter. Adds a button to set the default theme (as defined by the WP_DEFAULT_THEME constant).

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `ds_wp3_rollover_themes.php` to the `/wp-content/mu-plugins/` directory
2. Define constants(optional) near the top of the plugin code:
`define( 'DS_THEMES_PER_PAGE','100' ); 
define( 'DS_THEMES_DISABLE_ORIGINAL_MENU', 'TRUE' );
define( 'DS_THEMES_SHOW_SCREENSHOT_THUMB', 'TRUE' );
//define( 'WP_DEFAULT_THEME', 'twentyten' );`
3. Browse your new themes page Appearance->Themes

== Frequently Asked Questions ==
* I have a theme with no tags, how do I add some? Add tags to <a href="http://codex.wordpress.org/Theme_Development#Theme_Stylesheet">the stylesheet header</a> of the style.css
* Will allowed blog themes be listed from SuperAdmin->Sites->Edit->Allowed Themes? Yes.
* Will disabled themes from Super->Admin->Themes be listed? No.
* Will Child themes appear in the list? Yes, if network enabled or blog allowed.
* Will each row in the table have an upgrade link if the theme is in the WorPress theme repository? Yes, but I tend to ignore those links as theme updates are listed automagically under Dashboard->Updates for SuperAdmins currently.
* Do you have an update to Usethemes Revisted? No. I no longer allow theme-editor.php by any network user, including SuperAdmins.

== Screenshots ==

1. Theme table top with paged navigation and dropdown tag filter.
2. Theme table bottom with Default theme button, paged navigation, and clickable tag links.

== Notes ==

* The original mouseover screenshot code and table view comes from the Userthemes Revisted plugin.  I am no longer actively developing Userthemes Revisted.

== Changelog ==
= 3.0.1.6 =

* Tested up to: WP 3.2.1

= 3.0.1.4 =

* Added Appearance screen_icons, bugfix pagination navigation

= 3.0.1.2 =

* Added thumb sized screenshots to each row, define( 'DS_THEMES_SHOW_SCREENSHOT_THUMB', 'TRUE' );

= 3.0.1 =

* Initial Release for WP3.x multisite

== Upgrade Notice ==
= 3.0.1.4 =
* Tested up to WP 3.0.3

= 3.0.1 =
* WPMU2.9.2 version no longer supported.

