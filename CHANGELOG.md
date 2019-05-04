### [All Versions](https://github.com/vpsnak/spirit-dashboard-plugin/releases)

## 1.2.8 - Plugins Endpoint Testing
###### *May 04, 2019*

#### Changes
- [New] upgrade script to run on time snippets

## 1.2.7 - Plugins Endpoint Testing
###### *May 02, 2019*

#### Changes
- [New] added tweak on htaccess for basic auth

## 1.2.6 - Plugins Endpoint Testing
###### *May 02, 2019*

#### Changes
- [Tweak] load route (debug,plugin,theme) data in constructor
- [Tweak] moved endpoint registration to main plugin class file
- [New] updater endpoint and update action for core,plugins and themes

## 1.2.5 - Plugins Endpoint Testing
###### *Apr 17, 2019*

#### Changes
- [Fix] Plugin deactivating after update
- [New] update server after updating plugin

## 1.2.4 - Plugins Endpoint Testing
###### *Apr 14, 2019*

#### Changes
- [Fix] Some api routes did't load data
- [New] check for updates after updating a plugin / theme and update the server

## 1.2.3 - Plugins Endpoint Testing
###### *Apr 14, 2019*

Here are some highlights:
- Admin pages
- More data for the api
- Safer authentication

#### Changes
- [Fix] Theme update now works
- [New] Admin page for api settings
- [New | Api] Debug api endpoint
- [Fix] Admin page for api settings field password and now saves the values
- [New | Api] Update server now uses username and password to request for token
- [Tweak] Changed route constructors to functions that loads data

## 1.2.2 - Plugins Endpoint Testing
###### *Apr 10, 2019*

Here are some highlights:
- Theme api route

#### Changes
- [New | Api] Theme api route handler with more data for themes
- [BUG] theme update not working
- [Dev] added a dev endpoint to test sites for some data before deploy them in production
- [Tweak] Changed authentication from basic to jwt and enabled token for alpha week

## 1.2.0 - Plugins Endpoint Testing
###### *Apr 3, 2019*

Here are some highlights:
- Moved activation/deactivation/uninstall in one file
- All plugins now displayed correctly with their data


#### Changes
- [Tweak] Changed server update interval to 1 hour for testing
- [Tweak] Moved activation/deactivation/uninstall in main file for cleanup
- [Fix] Some plugins were not returned in response
- [Tweak] Changed authenticated user username to start deploying on some sites for testing.
- [Remove] Removed activation and deactivation class (maybe add them back in the future)
- [Tweak | Api] Replaced WP_ERROR with WP_REST_Response 200 with data []
- [Fix | Api] Plugin endpoint get didn't work after patching the plugin-route data key (get_plugin_data)

## 1.1.5
###### *Apr 1, 2019*

#### Changes
- [Tweak] Communication data transfer to server
- [New] Cron to push data to the licence server (activation / deactivation)
- [Patch | Api] Plugin route was returning plugin object inside of plugin object
- [Tweak] Cleanup code and includes
- [New] Plugin path defines to keep code cleaner
- [Patch] Server registration had wrong search query
- [Patch] Enable REST API to let the react app authenticate @TODO re-disable with better way

## 1.1.2
###### *Mar 31, 2019*

#### Changes
- [New] Communication skeleton to update licence server
- [Patch | Api] Check if plugin is installed before fetch his data for the api

## 1.1.1
###### *Mar 31, 2019*

Here are some highlights:
- Revokable authentication tokens for users (thanks to [georgestephanis/application-passwords](https://github.com/georgestephanis/application-passwords)).
- Basic authentication to access private endpoints (thanks to [WP-API/Basic-Auth](https://github.com/WP-API/Basic-Auth)).
- Required authentication for all REST endpoints (thanks to [Disable WP REST API](https://wordpress.org/plugins/disable-wp-rest-api/)).

#### Changes
- [Tweak] Changed readme and changelog file to test how the plugin will parse them
- [New | Api] Integration of application passwords to authenticate without sending user passwords
- [Tweak] Required authentication for all REST endpoints

## 1.1.0
###### *Mar 30, 2019*

Here are some highlights:
- Endpoint to list / update plugins

#### Changes
- [Tweak] Init application folder for all core classes
- [Tweak] Connected app endpoint to get data from plugins api
- [New] Created plugin endpoint to handle plugins data and actions `spirit-dashboard/v2/plugin`
- [New] Added updater using wordpress updater to handle core / plugin / theme / translation updates
- [New] Added silence to some directories
- [New] Added readme / changelog and more accurate info about this plugin

## 1.0.1
###### *Mar 29, 2019*

Here are some highlights:
- Plugin update checker from git repo (thanks to [YahnisElsts/plugin-update-checker](https://github.com/YahnisElsts/plugin-update-checker)).
- Patch fixes and cleanup

#### Changes

- [Files] Clear files not needed
- [Api] Customize api endpoint to get all the data needed

## 0.1.1
###### *Mar 29, 2019*

Here are some highlights:
- Initial release
- Rest api endpoint to get info from wordpres / plugins / themes