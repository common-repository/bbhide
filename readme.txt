=== BBHide ===
Contributors: del
Donate link: http://goo.gl/CcxWYg
Tags: hide, bbcode, capability, content, hide content
Requires at least: 3.5
Tested up to: 5.0.1
Stable tag: trunk

Implement the classic forum bbcode [hide] for WordPress sites.

== Description ==

This plugin implements the classic forum bbcode [hide]. The plugin allows you to hide text from users who have not been registered on the site for a certain number of days or left a certain number of approved comments.

The BBHide plugin uses the following short code [hide] syntax:

`[hide]text[/hide]` 
If the numbers of comments and days are not set, the default values will be 10.

`[hide comments='20']text[/hide]` 
Hidden text will only be shown to users who have left a certain number of comments.

`[hide days='20']text[/hide]` 
Hidden text will only be shown to users who have been registered on the site for more than a certain number of days.

`[hide comments='20' days='20']text[/hide]` 
Hidden text will only be shown to users who have left a certain number of comments and been registered on the site for more than a certain number of days.

`[hide comments='15' style='grey']text[/hide]` 
The "style" parameter indicates the color of the bar with the warning. The default is green.

You can also use the plugin's button in the visual editor.

If you liked my plugin, please <strong>rate</strong> it.
 

== Installation ==

1. Upload <strong>bbhide</strong> folder to the <strong>/wp-content/plugins/</strong> directory.
2. Activate the plugin through the <strong>Plugins</strong> menu in WordPress.
3. That's all.

Plugin settings are available in "<strong>Settings\BBHide</strong>".

== Frequently Asked Questions ==

= Does the plugin support localization? =

Yes, please use [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/bbhide).


== Screenshots ==

1. Examples of hidden text. 
2. Plugin settings.
3. Visual editor.


== Changelog ==

= 1.00 =
* first version.
