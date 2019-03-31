=== Plugin Name ===
Contributors: vpsnak
Donate link: https://github.com/vpsnak
Tags: json, rest, api, rest-api
Requires at least: 4.4.0
Tested up to: 5.1.1
Stable tag: 5.1.1
License: GPLv2+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Here is a short description of the plugin.  This should be no more than 150 characters.  No markup here.

== Description ==

This plugin is used to add endpoints for listing and updating Wordpress core, plugins, themes, translations.
This plugin only supports WordPress >= 4.4.

The new routes available will be:

* `/spirit-dashboard/v2/app` list data for Wordpress core, plugins and themes.
* `/spirit-dashboard/v2/plugin` list data for Wordpress plugins
* `/spirit-dashboard/v2/plugin/<plugin_slug>` list / update Wordpress plugin (GET / POST)

== Installation ==

1. Get a zip of the plugin from https://github.com/vpsnak/spirit-dashboard-plugin/releases/latest
2. Upload the zip through the the Plugins > Add New menu un Wordpress
3. Activate the plugin through the 'Plugins' menu in WordPress