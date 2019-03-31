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
 * Version:           1.1.4
 * Author:            Vaggelis Pallis
 * Author URI:        https://github.com/vpsnak
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       spirit-dashboard
 * Requires at least: 4.4.0
 * Tested up to: 5.1.1
 * Stable tag: 5.1.1
 */

if (!defined('WPINC')) {
    die;
}

/**
 * The defines.
 */
define('SPIRIT_DASHBOARD_VERSION', '1.1.4');
define('SPIRIT_DASHBOARD_BASE_URL', plugin_dir_url(__FILE__));
define('SPIRIT_BASE_DIR', __DIR__);
define('SPIRIT_INC_DIR', __DIR__ . '/includes/');
define('SPIRIT_APP_DIR', __DIR__ . '/includes/application/');
define('SPIRIT_SERVER_API', 'https://vpsnak.com/wp-json/');

/**
 * The code that runs during plugin activation.
 */
function activate_spirit_dashboard () {
    include_once(SPIRIT_INC_DIR . 'class-spirit-dashboard-activator.php');
    Spirit_Dashboard_Activator::activate();
}

register_activation_hook(__FILE__, 'activate_spirit_dashboard');

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_spirit_dashboard () {
    include_once(SPIRIT_INC_DIR . 'class-spirit-dashboard-deactivator.php');
    Spirit_Dashboard_Deactivator::deactivate();
}

register_deactivation_hook(__FILE__, 'deactivate_spirit_dashboard');

/**
 * Begins execution of the plugin.
 *
 * @since    0.0.1
 */
function run_spirit_dashboard () {
    include_once(SPIRIT_INC_DIR . '/class-spirit-dashboard.php');
    $plugin = new Spirit_Dashboard();
    $plugin->run();
    
    include_once(SPIRIT_INC_DIR . '/plugin-update-checker/plugin-update-checker.php');
    $UpdateChecker = Puc_v4_Factory::buildUpdateChecker('https://github.com/vpsnak/spirit-dashboard-plugin/', __FILE__, 'spirit-dashboard');
    $UpdateChecker->getVcsApi()->enableReleaseAssets();
}

run_spirit_dashboard();
