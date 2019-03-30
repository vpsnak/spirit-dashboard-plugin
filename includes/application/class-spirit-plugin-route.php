<?php
/**
 * This is the plugins api endpoint class
 *
 * @since      1.1.0
 * @package    Spirit_Dashboard
 * @subpackage Spirit_Dashboard/includes/application
 * @author     Vaggelis Pallis <info.vpsnak@gmail.com>
 */

class Spirit_Plugin_Route extends WP_REST_Controller {
    
    /**
     * The namespace of this controller's route.
     *
     * @since      1.1.0
     * @var string
     */
    protected $namespace = 'spirit-dashboard/v2';
    
    /**
     * The base of this controller's route.
     *
     * @since      1.1.0
     * @var string
     */
    protected $rest_base = 'plugin';
    
    /**
     * All plugin files with plugin data.
     *
     * @since      1.1.0
     * @var array
     */
    protected $all_plugins;
    
    /**
     * All outdated plugin files with plugin data.
     *
     * @since      1.1.0
     * @var array
     */
    protected $plugin_updates;
    
    /**
     * All up to date plugin files with plugin data.
     *
     * @since      1.1.0
     * @var array
     */
    protected $plugin_no_updates;
    
    /**
     * Parsed data of plugins.
     *
     * @since      1.1.0
     * @var array
     */
    protected $plugins_data;
    
    /**
     * Spirit_Plugin_Route constructor.
     *
     * @since      1.1.0
     */
    public function __construct () {
        if (!function_exists('get_plugins')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        
        $this->all_plugins = get_plugins();
        $plugin_update_transient = get_site_transient('update_plugins');
        $this->plugin_updates = $plugin_update_transient->response;
        $this->plugin_no_updates = $plugin_update_transient->no_update;
        
        $this->plugins_data = $this->load_plugins_data();
    }
    
    /**
     * Register the routes for the objects of the controller.
     *
     * @since      1.1.0
     */
    public function register_routes () {
        register_rest_route($this->namespace, '/' . $this->rest_base, array (
            array (
                'methods' => WP_REST_Server::READABLE,
                'callback' => array (
                    $this,
                    'get_items'
                ),
                'permission_callback' => array (
                    $this,
                    'permissions_check'
                ),
                'args' => array (),
            )
        ));
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<slug>\S+)', array (
            array (
                'methods' => WP_REST_Server::READABLE,
                'callback' => array (
                    $this,
                    'get_item'
                ),
                'permission_callback' => array (
                    $this,
                    'permissions_check'
                ),
                'args' => array (),
            ),
            array (
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array (
                    $this,
                    'update_item'
                ),
                'permission_callback' => array (
                    $this,
                    'permissions_check'
                ),
                'args' => array (),
            ),
        ));
    }
    
    /**
     * Parse data for later use.
     *
     * @since      1.1.0
     * @return mixed
     */
    private function load_plugins_data () {
        $data_response['plugins']['count'] = count($this->all_plugins);
        $data_response['plugins']['update']['plugins'] = array ();
        
        if ($this->plugin_updates) {
            $data_response['plugins']['update']['count'] = count($this->plugin_updates);
            foreach ($this->plugin_updates as $key => $plugin) {
                $data_response['plugins']['update']['plugins'][$key] = $this->load_plugin_data($key, $plugin);
            }
        } else {
            $data_response['plugins']['update']['count'] = 0;
        }
        
        $data_response['plugins']['no_update']['plugins'] = array ();
        
        if ($this->plugin_no_updates) {
            $data_response['plugins']['no_update']['count'] = count($this->plugin_no_updates);
            foreach ($this->plugin_no_updates as $key => $plugin) {
                $data_response['plugins']['no_update']['plugins'][$key] = $this->load_plugin_data($key, $plugin);
            }
        } else {
            $data_response['plugins']['no_update']['count'] = 0;
        }
        
        return $data_response;
    }
    
    /**
     * Parse anbd merge plugin info with meta data.
     *
     * @since      1.1.0
     *
     * @param $key
     * @param $plugin
     * @return array
     */
    private function load_plugin_data ($key, $plugin) {
        if (!$key || !$plugin)
            return [];
        
        return array (
            'name' => $this->all_plugins[$key]['Name'],
            'url' => $plugin->url,
            'slug' => $key,
            'icons' => $plugin->icons,
            'plugin_uri' => $this->all_plugins[$key]['PluginURI'],
            'author' => $this->all_plugins[$key]['Author'],
            'author_uri' => $this->all_plugins[$key]['AuthorURI'],
            'current_version' => $this->all_plugins[$key]['Version'],
            'latest_version' => $plugin->new_version,
        );
    }
    
    /**
     * Get plugins data.
     *
     * Used to get the parsed data.
     * @since      1.1.0
     *
     * @return array|mixed
     */
    public function get_plugins_data () {
        return $this->plugins_data;
    }
    
    /**
     * Get a single plugin parsed data based on slug.
     * @since      1.1.0
     *
     * @param $key
     * @return array
     */
    public function get_plugin_data ($key) {
        if (array_key_exists($key, $this->plugins_data['plugins']['update']['plugins']))
            return $this->plugins_data['plugins']['update']['plugins'][$key];
        
        if (array_key_exists($key, $this->plugins_data['plugins']['no_update']['plugins']))
            return $this->plugins_data['plugins']['no_update']['plugins'][$key];
        
        return [];
    }
    
    /**
     * Get a collection of items.
     * @since      1.1.0
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_items ($request) {
        return new WP_REST_Response($this->get_plugins_data(), 200);
    }
    
    /**
     * Get one item from the collection.
     * @since      1.1.0
     *
     * @param WP_REST_Request $request
     * @return WP_Error|WP_REST_Response
     */
    public function get_item ($request) {
        $params = $request->get_params();
        
        if (!$params['slug'])
            return new WP_Error('argument-missing', __('Plugin slug missing', 'spirit-dashboard'), array ('status' => 404));
        
        $plugin = $this->get_plugin_data($params['slug']);
        if (empty($plugin))
            return new WP_Error('plugin-missing', __('No plugin found', 'spirit-dashboard'), array ('status' => 404));
        
        return new WP_REST_Response($plugin, 200);
    }
    
    /**
     * Update plugin.
     * @since      1.1.0
     *
     * @param WP_REST_Request $request
     * @return WP_Error|WP_REST_Response
     */
    public function update_item ($request) {
        $params = $request->get_params();
        $plugin_slug = $params['slug'];
        
        if (!$plugin_slug)
            return new WP_Error('argument-missing', __('Plugin slug missing', 'spirit-dashboard'), array ('status' => 404));
        
        $plugin_to_update = $this->needs_update($plugin_slug);
        if (empty($plugin_to_update))
            return new WP_Error('update-fail', __('No plugin updates or plugin is missing', 'spirit-dashboard'), array ('status' => 404));
        
        include_once(__DIR__ . '/class-spirit-updater.php');
        
        $upgrader = new Spirit_Updater();
        $result = $upgrader->update('plugin', $this->plugin_updates[$plugin_slug]);
        
        return new WP_REST_Response(array ($result), 200);
    }
    
    /**
     * Check if the plugin is installed.
     *
     * @since      1.1.0
     *
     * @param $slug
     * @return array
     */
    function needs_update ($slug) {
        $plugin = $this->get_plugin_data($slug);
        
        if (empty($plugin))
            return [];
        
        if ($plugin['current_version'] !== $plugin['latest_version'])
            return $plugin;
        
        return [];
    }
    
    /**
     * Check permissions to use the endpoints.
     * @since      1.1.0
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return bool
     */
    public function permissions_check ($request) {
        return current_user_can('manage_options');
    }
}