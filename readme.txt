=== Clarity - Ad blocker for WordPress ===
Contributors: khromov
Tags: notifications, ads, adblock
Requires at least: 5.0
Tested up to: 5.6
Requires PHP: 7.0
Stable tag: trunk
License: GPL2

Clarity is an ad blocker for your WordPress admin. It hides obtrusive plugin and theme notifications 
asking you to pay for upgraded version or to collect your personal data.

== Description ==

*Basic usage*

The plugin requires zero configuration. Simply install and activate Clarity and obtrusive ads will disappear. Please see the 
Frequently Asked Questions for information about how to troubleshoot or add new entries to the block list.

== Requirements ==
* PHP 7.0 or higher

== Translations ==
* None

== Installation ==
1. Upload the `clarity-ad-blocker` folder to `/wp-content/plugins/`
2. Activate the plugin (Clarity - Ad blocker for WordPress) through the 'Plugins' menu in WordPress
3. You're done!

== Frequently Asked Questions ==

= A plugin I'm using is still showing notifications! =

Clarity works using a visual block list, similar to a browser ad blocker. Plugins have to be added manually.
You can [create an issue](https://github.com/khromov/clarity/issues/new/choose) to ask for a plugin to be supported.
You can also make a GitHub pull requst to the [official block list](https://github.com/khromov/clarity/blob/master/definitions.txt) - 
in that case, add an entry to the `definitions.txt` file with a CSS selector that hides the notification.

= I am a plugin or theme author, how can I avoid being filtered? =

Please [create an issue](https://github.com/khromov/clarity/issues/new/choose) and select "Ask for filter removal".

Generally, your plugin must fulfill the following:
- Your notifications are shown only on an option page that belongs to your plugin, and nowhere else.
- You have unique CSS classes to make it easy to identify different types of notifications that you use.

== Screenshots ==

1. A collection of popular plugin notification nags
2. Notification nags are hidden after installing and activating Clarity

== Upgrade Notice ==

N/A

== Changelog ==

= 1.0 =

* Initial release