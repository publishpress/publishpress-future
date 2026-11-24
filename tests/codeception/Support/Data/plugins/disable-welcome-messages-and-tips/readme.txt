=== Disable Welcome Messages and Tips ===
Contributors: Jules Colle
Author: Jules Colle
Website: https://bdwm.be
Tags: gutenberg, block-editor, notifications
Requires at least: 5.0
Tested up to: 6.0
Stable tag: 1.0.9
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Hide Welcome Messages and Tips, and disable default full screen mode in the Gutenberg Block Editor

== Description ==

Hide Welcome Messages and Tips, and disable default full screen mode in the Gutenberg Block Editor

== Installation ==

Please follow the [standard installation procedure for WordPress plugins](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins).

Follow [this tutorial](https://conditional-fields-cf7.bdwm.be/conditional-fields-for-contact-form-7-tutorial/) if you are not sure how to use the plugin.

== Frequently Asked Questions ==

= Why would I use this? =

* Useful if you already know your way around Gutenberg and you don't want to see the popups.
* Anther great use case is E2E testing. Just enable this plugin in your testing environment.

== Screenshots ==

== Changelog ==

= 1.0.9 (2022-01-27) =
* Change `window.onload = fn` to `jQuery(window).load(fn)` to avoid conflicts with plugins/themes overwriting `window.onload`

= 1.0.8 (2020-09-24) =
* Change `window.onload = fn` to `jQuery(window).load(fn)` to avoid conflicts with plugins/themes overwriting `window.onload`

= 1.0.7 (2020-09-24) =
* Get rid of JS warning

= 1.0.6 (2020-09-24) =
* Make sure that the Options modal and other modals still work.

= 1.0.5 (2020-07-29) =
* Test with WP 5.5

= 1.0.3 (2020-07-29) =
* Also disable full screen mode by default. Cause everybody hates full screen mode, right?

= 1.0.2 (2020-07-25) =
* Update style rules

= 1.0.1 (2020-07-25) =
* Real initial release

= 1.0.0 (2020-07-20) =
* Initial release