<?php
/**
 * The updater class
 *
 * This is used to update core, themes, plugins and translations
 * using WP AutoUpdater class
 *
 * @since      1.1.0
 * @package    Spirit_Dashboard
 * @subpackage Spirit_Dashboard/includes/application
 * @author     Vaggelis Pallis <info.vpsnak@gmail.com>
 */

class Spirit_Updater extends WP_REST_Controller {
    /**
     * The namespace of this controller's route.
     *
     * @since      x.x.x
     * @var string
     */
    protected $namespace = 'spirit-dashboard/v2';
    
    /**
     * The base of this controller's route.
     *
     * @since      x.x.x
     * @var string
     */
    protected $rest_base = 'updater';
    
    /**
     * Register the routes for the objects of the controller.
     *
     * @since      x.x.x
     */
    public function register_routes () {
        register_rest_route($this->namespace, '/' . $this->rest_base, array (
            array (
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array (
                    $this,
                    'post_handler'
                ),
                'permission_callback' => array (
                    $this,
                    'permissions_check'
                ),
                'args' => array (),
            )
        ));
    }
    
    /**
     * Handle psot action.
     * @since      x.x.x
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function post_handler ($request) {
        $params = $request->get_params();
        $result = [];
        if (!array_key_exists('action', $params))
            return new WP_REST_Response($result, 200);
        
        $upgrader = $this;
        $action = $params['action'];
        
        switch ($action) {
            case 'core_update':
                //                if (!get_site_transient('update_core'))
                wp_version_check();
                $update_core = get_site_transient('update_core');
                $result = $upgrader->update('core', $update_core);
                break;
            case 'plugin_update':
            case 'plugin_update_bulk':
                if (!array_key_exists('items', $params))
                    break;
                
                include_once(SPIRIT_APP_DIR . 'class-spirit-plugin-route.php');
                $pluginController = new Spirit_Plugin_Route();
                $plugins = array ();
                foreach ($params['items'] as $item)
                    if ($pluginController->needs_update($item) !== [])
                        $plugins[] = $item;
                
                if (count($plugins) > 0)
                    $result = $upgrader->update('plugin', $plugins);
                break;
            case 'theme_update':
            case 'theme_update_bulk':
                if (!array_key_exists('items', $params))
                    break;
                
                include_once(SPIRIT_APP_DIR . 'class-spirit-theme-route.php');
                $themeController = new Spirit_Theme_Route();
                $themes = array ();
                foreach ($params['items'] as $item)
                    if ($themeController->needs_update($item) !== [])
                        $themes[] = $item;
                
                if (count($themes) > 0)
                    $result = $upgrader->update('theme', $themes);
                break;
        };
        
        return new WP_REST_Response($result, 200);
    }
    
    /**
     * Update an item, if appropriate.
     *
     * @since      1.1.0
     *
     * @param string $type The type of update being checked: 'core', 'theme', 'plugin', 'translation'.
     * @param $item
     * @return array|false
     */
    public function update ($type, $item) {
        if (!function_exists('request_filesystem_credentials'))
            include_once(ABSPATH . 'wp-admin/includes/file.php');
        
        include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
        include_once(ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php');
        
        $result = [];
        include_once(ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php');
        $skin = new WP_Ajax_Upgrader_Skin;
        switch ($type) {
            case 'core':
                // The Core upgrader doesn't use the Upgrader's skin during the actual main part of the upgrade, instead, firing a filter.
                add_filter('update_feedback', array (
                    $skin,
                    'feedback'
                ));
                
                if (!isset($item->updates) || !is_array($item->updates))
                    break;
                
                if (!function_exists('get_core_checksums'))
                    include_once(ABSPATH . 'wp-admin/includes/update.php');
                
                $updates = $item->updates;
                $locale = get_locale();
                foreach ($updates as $update) {
                    if ($update->current > $item->version_checked && $update->locale == $locale) {
                        $upgrader = new Core_Upgrader($skin);
                        $response = $upgrader->upgrade($update, array (
                            'clear_update_cache' => false,
                            'allow_relaxed_file_ownership' => true,
                        ));
                        $result = $response;
                        break;
                    }
                }
                break;
            case 'plugin':
                $upgrader = new Plugin_Upgrader($skin);
                $response = $upgrader->bulk_upgrade($item, array (
                    'clear_update_cache' => false,
                ));
                $result = array_keys($response);
                break;
            case 'theme':
                $upgrader = new Theme_Upgrader($skin);
                $response = $upgrader->bulk_upgrade($item, array (
                    'clear_update_cache' => false,
                ));
                $result = array_keys($response);
                break;
        }
        
        return $result;
    }
    
    /**
     * Check permissions to use the endpoints.
     * @since      x.x.x
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return bool
     */
    public function permissions_check ($request) {
        return current_user_can('manage_options');
    }
}