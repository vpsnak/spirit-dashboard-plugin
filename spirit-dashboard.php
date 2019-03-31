<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/vpsnak
 * @since             0.0.1
 * @package           Spirit_Dashboard
 *
 * @wordpress-plugin
 * Plugin Name:       Spirit Dashboard
 * Plugin URI:        https://github.com/vpsnak/spirit-dashboard-plugin
 * Description:       This plugin is used to add endpoints for listing and updating Wordpress core, plugins, themes, translations.
 * Version:           1.1.1
 * Author:            Vaggelis Pallis
 * Author URI:        https://github.com/vpsnak
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       spirit-dashboard
 * Requires at least: 4.4.0
 * Tested up to: 5.1.1
 * Stable tag: 5.1.1
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 0.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('SPIRIT_DASHBOARD_VERSION', '1.1.1');

define('SPIRIT_DASHBOARD_BASE_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-spirit-dashboard-activator.php
 */
function activate_spirit_dashboard () {
    require_once plugin_dir_path(__FILE__) . 'includes/class-spirit-dashboard-activator.php';
    Spirit_Dashboard_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-spirit-dashboard-deactivator.php
 */
function deactivate_spirit_dashboard () {
    require_once plugin_dir_path(__FILE__) . 'includes/class-spirit-dashboard-deactivator.php';
    Spirit_Dashboard_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_spirit_dashboard');
register_deactivation_hook(__FILE__, 'deactivate_spirit_dashboard');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-spirit-dashboard.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.0.1
 */
function run_spirit_dashboard () {
    
    $plugin = new Spirit_Dashboard();
    $plugin->run();
    
    require_once plugin_dir_path(__FILE__) . 'includes/plugin-update-checker/plugin-update-checker.php';
    $UpdateChecker = Puc_v4_Factory::buildUpdateChecker('https://github.com/vpsnak/spirit-dashboard-plugin/', __FILE__, 'spirit-dashboard');
    $UpdateChecker->getVcsApi()->enableReleaseAssets();
}

run_spirit_dashboard();
