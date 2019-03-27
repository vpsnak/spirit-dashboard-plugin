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
 * @since      1.0.0
 * @package    Spirit_Dashboard
 * @subpackage Spirit_Dashboard/includes
 * @author     Vaggelis Pallis <info.vpsnak@gmail.com>
 */

add_action('rest_api_init', function () {
    register_rest_route('spirit-dashboard/v2', '/app', array(
        'methods' => 'GET',
        'callback' => 'get_app_data',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        }
    ));

    function get_plugin_collection()
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return get_plugins();
    }

    function get_plugin_info($data)
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $all_plugins = get_plugins();

        if (!empty($all_plugins[$data['slug']])) {
            return $all_plugins[$data['slug']];
        } else {
            return [];
        }
    }

    function is_plugin_installed($slug)
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $all_plugins = get_plugins();

        if (!empty($all_plugins[$slug])) {
            return true;
        } else {
            return false;
        }
    }

    function upgrade_plugin($plugin_slug)
    {
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        wp_cache_flush();

        $upgrader = new Plugin_Upgrader();
        $upgraded = $upgrader->upgrade($plugin_slug);

        return $upgraded;
    }

    function get_app_data()
    {
        $data_response['wordpress'] = get_site_transient('update_core');
        $data_response['plugins'] =  get_site_transient('update_plugins');
        $data_response['themes'] = get_site_transient('update_themes');

        $data_response['server'] = array(
            'php_version' => phpversion()
        );

        return ($data_response);
    }
});
