### [All Versions](https://github.com/vpsnak/spirit-dashboard-plugin/releases)

## 1.1.2
###### *Mar 31, 2019*

#### Changes
- [New] Communication skeleton to update licence server
- [Bug | Api] Check if plugin is installed before fetch his data for the api

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
- Bug fixes and cleanup

#### Changes

- [Files] Clear files not needed
- [Api] Customize api endpoint to get all the data needed

## 0.1.1
###### *Mar 29, 2019*

Here are some highlights:
- Initial release
- Rest api endpoint to get info from wordpres / plugins / themes