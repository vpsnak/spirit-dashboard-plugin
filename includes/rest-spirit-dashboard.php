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

add_action('rest_api_init', function() {
    $spirit_plugin_route = new Spirit_Plugin_Route();
    $spirit_plugin_route->register_routes();
    $spirit_theme_route = new Spirit_Theme_Route();
    $spirit_theme_route->register_routes();
    
    register_rest_route('spirit-dashboard/v2', '/app', array (
        'methods' => 'GET',
        'callback' => 'get_app_data',
        'permission_callback' => function() {
            return current_user_can('manage_options');
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
    include_once ABSPATH . 'wp-includes/theme.php';
    $arr = array ();
    $arr['use_ssl'] = is_ssl();
    $arr['users_can_register'] =  get_option( 'users_can_register' );
    $arr['comment_status'] =  get_option( 'default_comment_status' );
    
    $arr['dropins'] = get_dropins();
    $arr['wp_image_editor'] = _wp_image_editor_choose();
    if (class_exists('Imagick')) {
        // Save the Imagick instance for later use.
        $imagick = new Imagick();
        $arr['imagick'] = $imagick->getVersion();
    } else {
        $arr['imagick'] = 'Imagick not available';
    }
    if (function_exists('gd_info')) {
        $arr['gd_info'] = gd_info();
    } else {
        $arr['gd_info'] = false;
    }
    $arr['web_server_software'] = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : false;
    
    $arr['server_architecture'] = function_exists('php_uname') ? php_uname('s') . php_uname('r') . php_uname('m') : false;
    $arr['php_sapi'] = function_exists('php_sapi_name') ? php_sapi_name() : false;
    
    $arr['max_input_vars'] = !function_exists('ini_get') ? : ini_get('max_input_vars');
    $arr['max_execution_time'] = !function_exists('ini_get') ? : ini_get('max_execution_time');
    $arr['memory_limit'] = !function_exists('ini_get') ? : ini_get('memory_limit');
    $arr['max_input_time'] = !function_exists('ini_get') ? : ini_get('max_input_time');
    $arr['upload_max_filesize'] = !function_exists('ini_get') ? : ini_get('upload_max_filesize');
    $arr['post_max_size'] = !function_exists('ini_get') ? : ini_get('post_max_size');
    
    $arr['suhosin_installed'] = extension_loaded('suhosin');
    
    $arr['wordpress_dir_writable'] = wp_is_writable(ABSPATH);
    $arr['wp_content_dir_writable'] = wp_is_writable(WP_CONTENT_DIR);
    $arr['uploads_dir_writable'] = wp_is_writable(wp_upload_dir()['basedir']);
    $arr['plugins_dir_writable'] = wp_is_writable(WP_PLUGIN_DIR);
    $arr['themes_dir_writable'] = wp_is_writable(get_template_directory() . '/..');
    
    $wpconfig = array (
        'ABSPATH' => ABSPATH,
        'WP_HOME' => WP_HOME,
        'WP_SITEURL' => WP_SITEURL,
        'WP_DEBUG' => WP_DEBUG,
        'WP_MAX_MEMORY_LIMIT' => WP_MAX_MEMORY_LIMIT,
        'WP_DEBUG_DISPLAY' => WP_DEBUG_DISPLAY,
        'WP_DEBUG_LOG' => WP_DEBUG_LOG,
        'SCRIPT_DEBUG' => SCRIPT_DEBUG,
        'CONCATENATE_SCRIPTS' => CONCATENATE_SCRIPTS,
        'COMPRESS_SCRIPTS' => COMPRESS_SCRIPTS,
        'COMPRESS_CSS' => COMPRESS_CSS
    );
    foreach ($wpconfig as $key => $value) {
        $arr[$key] = $value;
    }
    
    return $arr;
}

function get_app_data () {
    global $wpdb;
    $plugin_api = new Spirit_Plugin_Route();
    $theme_api = new Spirit_Theme_Route();
    
    $data_response['info'] = array (
        'name' => get_bloginfo('name'),
        'url' => get_bloginfo('url'),
        'logo' => get_site_icon_url() ? : [],
    );
    
    $update_core = get_site_transient('update_core');
    if (!$update_core)
        wp_version_check();
    $update_core = get_site_transient('update_core');
    if (is_resource($wpdb->dbh)) {
        // Old mysql extension.
        $extension = 'mysql';
    } elseif (is_object($wpdb->dbh)) {
        // mysqli or PDO.
        $extension = get_class($wpdb->dbh);
    } else {
        // Unknown sql extension.
        $extension = null;
    }
    
    $data_response['wordpress'] = array (
        'current_version' => $update_core->updates[0]->current,
        'latest_version' => $update_core->updates[0]->version,
        'users' => count_users(),
        'php_version' => phpversion(),
        'db_engine' => $extension,
        'db_version' => $wpdb->db_version(),
        'db_host' => $wpdb->dbhost,
        'db_name' => $wpdb->dbname,
        'db_user' => $wpdb->dbuser,
        'db_prefix' => $wpdb->prefix,
        'last_check' => date('d-m-Y H:m', $update_core->last_checked),
    );
    
    $data_response['plugins'] = $plugin_api->get_plugins_data();
    $data_response['themes'] = $theme_api->get_themes_data();
    
    return $data_response;
}
