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

require_once __DIR__ . '/application/class-spirit-plugin-route.php';

add_action('rest_api_init', function() {
    $spirit_plugin_route = new Spirit_Plugin_Route();
    $spirit_plugin_route->register_routes();
    
    register_rest_route('spirit-dashboard/v2', '/app', array (
        'methods' => 'GET',
        'callback' => 'get_app_data',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        }
    ));
    
});

function get_app_data () {
    $plugin_api = new Spirit_Plugin_Route();
    
    $custom_logo_id = get_theme_mod('custom_logo');
    $data_response['info'] = array (
        'name' => get_bloginfo('name'),
        'url' => get_bloginfo('url'),
        'logo' => wp_get_attachment_image_src($custom_logo_id, 'full')[0] ? : [],
    );
    $update_core = get_site_transient('update_core');
    $data_response['wordpress'] = array (
        'current_version' => $update_core->updates[0]->current,
        'latest_version' => $update_core->updates[0]->version,
        'php_version' => phpversion(),
        'last_check' => date('d-m-Y H:m', $update_core->last_checked),
    );
    
    $data_response['plugins'] = $plugin_api->get_plugins_data();
    
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
