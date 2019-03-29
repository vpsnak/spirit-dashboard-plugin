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

add_action('rest_api_init', function() {
    register_rest_route('spirit-dashboard/v2', '/app', array (
        'methods' => 'GET',
        'callback' => 'get_app_data',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        }
    ));
    
    function get_plugin_collection () {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        return get_plugins();
    }
    
    function get_plugin_info ($data) {
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
    
    function is_plugin_installed ($slug) {
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
    
    function upgrade_plugin ($plugin_slug) {
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        wp_cache_flush();
        
        $upgrader = new Plugin_Upgrader();
        $upgraded = $upgrader->upgrade($plugin_slug);
        
        return $upgraded;
    }
    
    function get_app_data () {
        $custom_logo_id = get_theme_mod('custom_logo');
        $data_response['info'] = array (
            'name' => get_bloginfo('name'),
            'url' => get_bloginfo('url'),
            'logo' => wp_get_attachment_image_src($custom_logo_id, 'full')[0],
        );
        $update_core = get_site_transient('update_core');
        $data_response['wordpress'] = array (
            'current_version' => $update_core->updates[0]->current,
            'latest_version' => $update_core->updates[0]->version,
            'php_version' => phpversion(),
            'last_check' => date('d-m-Y H:m', $update_core->last_checked),
        );
        
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $all_plugins = get_plugins();
        $data_response['plugins']['count'] = count($all_plugins);
        $data_response['plugins']['update']['plugins'] = array ();
        $update_plugins = get_site_transient('update_plugins');
        $plugin_update = $update_plugins->response;
        if ($plugin_update) {
            $data_response['plugins']['update']['count'] = count($plugin_update);
            foreach ($plugin_update as $key => $plugin) {
                $data_response['plugins']['update']['plugins'][$key] = array (
                    'name' => $all_plugins[$key]['Name'],
                    'url' => $plugin->url,
                    'icons' => $plugin->icons,
                    'plugin_uri' => $all_plugins[$key]['PluginURI'],
                    'author' => $all_plugins[$key]['Author'],
                    'author_uri' => $all_plugins[$key]['AuthorURI'],
                    'current_version' => $all_plugins[$key]['Version'],
                    'latest_version' => $plugin->new_version,
                );
            }
        } else {
            $data_response['plugins']['update']['count'] = 0;
        }
        
        $plugin_no_update = $update_plugins->no_update;
        $data_response['plugins']['no_update']['plugins'] = array ();
        if ($plugin_no_update) {
            $data_response['plugins']['no_update']['count'] = count($plugin_no_update);
            foreach ($plugin_no_update as $key => $plugin) {
                $data_response['plugins']['no_update']['plugins'][$key] = array (
                    'name' => $all_plugins[$key]['Name'],
                    'url' => $plugin->url,
                    'icons' => $plugin->icons,
                    'plugin_uri' => $all_plugins[$key]['PluginURI'],
                    'author' => $all_plugins[$key]['Author'],
                    'author_uri' => $all_plugins[$key]['AuthorURI'],
                    'current_version' => $all_plugins[$key]['Version'],
                    'latest_version' => $plugin->new_version,
                );
            }
        } else {
            $data_response['plugins']['no_update']['count'] = 0;
        }
        
        $update_themes = get_site_transient('update_themes');
        $checked_themes = $update_themes->checked;
        $themes_update = $update_themes->response;
        $data_response['themes']['count'] = count($checked_themes);
        $data_response['themes']['update']['themes'] = array ();
        if ($themes_update) {
            $data_response['themes']['update']['count'] = count($themes_update);
            foreach ($themes_update as $key => $theme) {
                $data_response['themes']['update']['themes'][$key] = array (
                    'name' => $theme['theme'],
                    'url' => $theme['url'],
                    'current_version' => $checked_themes[$key],
                    'latest_version' => $theme['new_version'],
                );
            }
        } else {
            $data_response['themes']['update']['count'] = 0;
        }
        
        return ($data_response);
    }
});
