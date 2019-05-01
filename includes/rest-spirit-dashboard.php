<?php
/**
 * The core plugin api.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.0.1
 * @package    Spirit_Dashboard
 * @subpackage Spirit_Dashboard/includes
 * @author     Vaggelis Pallis <info.vpsnak@gmail.com>
 */

include_once(__DIR__ . '/application/class-spirit-plugin-route.php');
include_once(__DIR__ . '/application/class-spirit-theme-route.php');
include_once(__DIR__ . '/application/class-spirit-debug-route.php');

add_action('rest_api_init', function() {
    
    register_rest_route('spirit-dashboard/v2', '/app', array (
        'methods' => 'GET',
        'callback' => 'get_app_data',
        'permission_callback' => function() {
            return current_user_can('manage_options') || current_user_can('can_see_sites');
        }
    ));
    register_rest_route('spirit-dashboard/v2', '/dev', array (
        'methods' => 'GET',
        'callback' => 'get_app_dev',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        }
    ));
    
});

function get_app_dev () {
    $update_core = get_site_transient('update_core');
    
    return $update_core;
}

function get_app_data () {
    $plugin_api = new Spirit_Plugin_Route();
    $theme_api = new Spirit_Theme_Route();
    $debug_api = new Spirit_Debug_Route();
    
    $data_response['info'] = array (
        'name' => get_bloginfo('name'),
        'url' => get_bloginfo('url'),
        'logo' => get_site_icon_url() ? : [],
    );
    
    $update_core = get_site_transient('update_core');
    if (!$update_core)
        wp_version_check();
    $update_core = get_site_transient('update_core');
    
    $data_response['wordpress'] = $debug_api->get_wordpress_data();
    $data_response['wordpress']['server'] = $debug_api->get_server_data();
    $data_response['wordpress']['installation'] = $debug_api->get_installation_data();
    
    $data_response['wordpress']['current_version'] = $update_core->version_checked;
    $data_response['wordpress']['latest_version'] = $update_core->updates[0]->version;
    $data_response['wordpress']['users'] = count_users();
    $data_response['wordpress']['php_version'] = phpversion();
    $data_response['wordpress']['last_check'] = date('d-m-Y H:m', $update_core->last_checked);
    
    
    $data_response['plugins'] = $plugin_api->get_plugins_data();
    $data_response['themes'] = $theme_api->get_themes_data();
    
    return $data_response;
}
