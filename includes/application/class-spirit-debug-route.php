<?php
/**
 * This is the server api endpoint class
 *
 * @since      1.2.3
 * @package    Spirit_Dashboard
 * @subpackage Spirit_Dashboard/includes/application
 * @author     Vaggelis Pallis <info.vpsnak@gmail.com>
 */

class Spirit_Debug_Route extends WP_REST_Controller {
    
    /**
     * The namespace of this controller's route.
     *
     * @since      1.2.3
     * @var string
     */
    protected $namespace = 'spirit-dashboard/v2';
    
    /**
     * The base of this controller's route.
     *
     * @since      1.2.3
     * @var string
     */
    protected $rest_base = 'debug';
    
    /**
     * Parsed data of servers.
     *
     * @since      1.2.3
     * @var array
     */
    protected $wordpress_data;
    
    /**
     * Parsed data of servers.
     *
     * @since      1.2.3
     * @var array
     */
    protected $server_data;
    
    /**
     * Spirit_Debug_Route constructor.
     *
     * @since      1.2.3
     */
    public function __construct () {
        $this->server_data = $this->load_server_data();
        $this->wordpress_data = $this->load_wordpress_data();
    }
    
    /**
     * Register the routes for the objects of the controller.
     *
     * @since      1.2.3
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
    }
    
    /**
     * Parse data for later use.
     *
     * @since      1.2.3
     * @return mixed
     */
    private function load_server_data () {
        
        $data_response['server_architecture'] = function_exists('php_uname') ? php_uname('s') . php_uname('r') . php_uname('m') : false;
        $data_response['web_server_software'] = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : false;
        $data_response['php_version'] = phpversion();
        $data_response['php_sapi'] = function_exists('php_sapi_name') ? php_sapi_name() : false;
        $data_response['max_input_vars'] = !function_exists('ini_get') ? : ini_get('max_input_vars');
        $data_response['max_execution_time'] = !function_exists('ini_get') ? : ini_get('max_execution_time');
        $data_response['memory_limit'] = !function_exists('ini_get') ? : ini_get('memory_limit');
        $data_response['max_input_time'] = !function_exists('ini_get') ? : ini_get('max_input_time');
        $data_response['upload_max_filesize'] = !function_exists('ini_get') ? : ini_get('upload_max_filesize');
        $data_response['post_max_size'] = !function_exists('ini_get') ? : ini_get('post_max_size');
        $data_response['suhosin_installed'] = extension_loaded('suhosin');
        
        return $data_response;
    }
    
    /**
     * Parse data for later use.
     *
     * @since      1.2.3
     * @return mixed
     */
    private function load_wordpress_data () {
        global $wpdb;
        
        $data_response['use_ssl'] = is_ssl();
        $data_response['users_can_register'] = get_option('users_can_register');
        $data_response['comment_status'] = get_option('default_comment_status');
        $data_response['dropins'] = get_dropins();
        $data_response['wp_image_editor'] = _wp_image_editor_choose();
        
        $extension = false;
        if (is_resource($wpdb->dbh)) {
            $extension = 'mysql';
        } elseif (is_object($wpdb->dbh)) {
            $extension = get_class($wpdb->dbh);
        }
        
        $data_response['database']['db_engine'] = $extension;
        $data_response['database']['db_version'] = $wpdb->db_version();
        $data_response['database']['db_host'] = $wpdb->dbhost;
        $data_response['database']['db_name'] = $wpdb->dbname;
        $data_response['database']['db_user'] = $wpdb->dbuser;
        $data_response['database']['db_prefix'] = $wpdb->prefix;
        
        $data_response['filesystem']['wordpress_dir_writable'] = wp_is_writable(ABSPATH);
        $data_response['filesystem']['wp_content_dir_writable'] = wp_is_writable(WP_CONTENT_DIR);
        $data_response['filesystem']['uploads_dir_writable'] = wp_is_writable(wp_upload_dir()['basedir']);
        $data_response['filesystem']['plugins_dir_writable'] = wp_is_writable(WP_PLUGIN_DIR);
        $data_response['filesystem']['themes_dir_writable'] = wp_is_writable(get_template_directory() . '/..');
        
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
            $data_response['config'][$key] = $value;
        }
        
        return $data_response;
    }
    
    public function get_size_info () {
        $uploads_dir = wp_upload_dir();
        
        $sizes = array (
            'wp' => array (
                'path' => ABSPATH,
                'size' => 0,
            ),
            'themes' => array (
                'path' => trailingslashit(get_theme_root()),
                'size' => 0,
            ),
            'plugins' => array (
                'path' => WP_PLUGIN_DIR,
                'size' => 0,
            ),
            'uploads' => array (
                'path' => $uploads_dir['basedir'],
                'size' => 0,
            ),
        );
        
        foreach ($sizes as $size => $config) {
            try {
                $sizes[$size]['size'] = self::get_directory_size($config['path']);
            } catch (Exception $e) {
                $sizes[$size]['size'] = -1;
            }
        }
        
        global $wpdb;
        $db_size = 0;
        $rows = $wpdb->get_results('SHOW TABLE STATUS', ARRAY_A);
        if ($wpdb->num_rows > 0)
            foreach ($rows as $row)
                $db_size += $row['Data_length'] + $row['Index_length'];
        
        $result['uploads'] = size_format($sizes['uploads']['size'], 2);
        $result['themes'] = size_format($sizes['themes']['size'], 2);
        $result['plugins'] = size_format($sizes['plugins']['size'], 2);
        $result['database'] = size_format($db_size, 2);
        $result['wordpress'] = size_format($sizes['wp']['size'], 2);
        $result['total'] = size_format($sizes['wp']['size'] + $db_size, 2);
        
        return $result;
    }
    
    private static function get_directory_size ($path) {
        $size = 0;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file)
            $size += $file->getSize();
        
        return $size;
    }
    
    /**
     * Get server data.
     *
     * Used to get the parsed data.
     * @since      1.2.3
     *
     * @return array|mixed
     */
    public function get_server_data () {
        return $this->server_data;
    }
    
    /**
     * Get wordpress data.
     *
     * Used to get the parsed data.
     * @since      1.2.3
     *
     * @return array|mixed
     */
    public function get_wordpress_data () {
        return $this->wordpress_data;
    }
    
    /**
     * Get a collection of items.
     * @since      1.2.3
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_items ($request) {
        return new WP_REST_Response($this->get_server_data(), 200);
    }
    
    /**
     * Check permissions to use the endpoints.
     * @since      1.2.3
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return bool
     */
    public function permissions_check ($request) {
        return current_user_can('manage_options');
    }
}