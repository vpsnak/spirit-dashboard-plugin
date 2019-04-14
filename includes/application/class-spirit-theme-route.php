<?php
/**
 * This is the themes api endpoint class
 *
 * @since      1.2.2
 * @package    Spirit_Dashboard
 * @subpackage Spirit_Dashboard/includes/application
 * @author     Vaggelis Pallis <info.vpsnak@gmail.com>
 */

class Spirit_Theme_Route extends WP_REST_Controller {
    
    /**
     * The namespace of this controller's route.
     *
     * @since      1.2.2
     * @var string
     */
    protected $namespace = 'spirit-dashboard/v2';
    
    /**
     * The base of this controller's route.
     *
     * @since      1.2.2
     * @var string
     */
    protected $rest_base = 'theme';
    
    /**
     * All theme files with theme data.
     *
     * @since      1.2.2
     * @var array
     */
    protected $all_themes;
    
    /**
     * All outdated theme files with theme data.
     *
     * @since      1.2.2
     * @var array
     */
    protected $theme_updates;
    
    /**
     * Parsed data of themes.
     *
     * @since      1.2.2
     * @var array
     */
    protected $themes_data = NULL;
    
    /**
     * Spirit_Theme_Route constructor.
     *
     * @since      1.2.2
     */
    public function __construct () {
    }
    
    /**
     * Load route data
     *
     * @since      1.2.3
     */
    public function load_data () {
        include_once(ABSPATH . 'wp-admin/includes/theme.php');
        
        $theme_update_transient = get_site_transient('update_themes');
        $this->all_themes = $theme_update_transient->checked;
        $this->theme_updates = $theme_update_transient->response;
        
        $this->themes_data = $this->load_themes_data();
    }
    
    /**
     * Register the routes for the objects of the controller.
     *
     * @since      1.2.2
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
     * @since      1.2.2
     * @return mixed
     */
    private function load_themes_data () {
        $themeIterator = $this->all_themes;
        
        $data_response['count'] = count($this->all_themes);
        $data_response['update']['themes'] = array ();
        $data_response['update']['count'] = 0;
        
        $active_theme = get_option('stylesheet');
        
        if ($this->theme_updates) {
            $data_response['update']['count'] = count($this->theme_updates);
            foreach ($this->theme_updates as $key => $theme) {
                $data_response['update']['themes'][$key] = $this->load_theme_data($key, $theme);
                if (array_key_exists($key, $themeIterator)) {
                    unset($themeIterator[$key]);
                }
            }
            if (array_key_exists($active_theme, $data_response['update']['themes'])) {
                $data_response['update']['themes'][$active_theme]['active'] = true;
            }
        }
        
        $data_response['no_update']['themes'] = array ();
        $data_response['no_update']['count'] = 0;
        
        if ($themeIterator) {
            $data_response['no_update']['count'] += count($themeIterator);
            foreach ($themeIterator as $key => $theme) {
                $data_response['no_update']['themes'][$key] = $this->load_theme_data($key, $theme);
            }
            if (array_key_exists($active_theme, $data_response['no_update']['themes'])) {
                $data_response['no_update']['themes'][$active_theme]['active'] = true;
            }
        }
        
        return $data_response;
    }
    
    /**
     * Check if theme is installed.
     *
     * @since      1.1.2
     *
     * @param $key
     * @return bool
     */
    public function is_theme_installed ($key) {
        return array_key_exists($key, $this->all_themes);
    }
    
    /**
     * Parse and merge theme info with meta data.
     *
     * @since      1.2.2
     *
     * @param $key
     * @param $theme
     * @return array
     */
    private function load_theme_data ($key, $theme) {
        if (!$key || !$theme)
            return [];
        
        if (!$this->is_theme_installed($key))
            return [];
        
        $theme_data = wp_get_theme($key);
        if (!$theme_data->exists())
            return [];
        
        return array (
            'name' => $theme_data->get('Name'),
            'url' => $theme_data->get('ThemeURI') ? : [],
            'slug' => $key,
            'active' => false,
            'image' => $theme_data->get_screenshot() ? $theme_data->get_screenshot() : [],
            'theme_uri' => $theme_data->get('ThemeURI') ? : [],
            'author' => $theme_data->get('Author'),
            'author_uri' => $theme_data->get('AuthorURI') ? : [],
            'current_version' => $theme_data->get('Version'),
            'latest_version' => isset($theme['new_version']) ? $theme['new_version'] : $theme_data->get('Version'),
        );
    }
    
    /**
     * Get themes data.
     *
     * Used to get the parsed data.
     * @since      1.2.2
     *
     * @return array|mixed
     */
    public function get_themes_data () {
        if (!$this->themes_data)
            $this->load_data();
        
        return $this->themes_data;
    }
    
    /**
     * Get a single theme parsed data based on slug.
     * @since      1.2.2
     *
     * @param $key
     * @return array
     */
    public function get_theme_data ($key) {
        if (!$this->is_theme_installed($key))
            return [];
        
        if (array_key_exists($key, $this->themes_data['update']['themes']))
            return $this->themes_data['update']['themes'][$key];
        
        if (array_key_exists($key, $this->themes_data['no_update']['themes']))
            return $this->themes_data['no_update']['themes'][$key];
        
        return [];
    }
    
    /**
     * Get a collection of items.
     * @since      1.2.2
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_items ($request) {
        return new WP_REST_Response($this->get_themes_data(), 200);
    }
    
    /**
     * Get one item from the collection.
     * @since      1.2.2
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_item ($request) {
        $params = $request->get_params();
        
        if (!$params['slug'])
            return new WP_REST_Response(false, 200);
        
        $theme = $this->get_theme_data($params['slug']);
        if (empty($theme))
            return new WP_REST_Response(false, 200);
        
        return new WP_REST_Response($theme, 200);
    }
    
    /**
     * Update theme.
     * @since      1.2.2
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_item ($request) {
        $params = $request->get_params();
        $theme_slug = $params['slug'];
        
        if (!$theme_slug)
            return new WP_REST_Response(false, 200);
        
        $theme_to_update = $this->needs_update($theme_slug);
        if (empty($theme_to_update))
            return new WP_REST_Response(false, 200);
        
        include_once('class-spirit-updater.php');
        
        $upgrader = new Spirit_Updater();
        $result = $upgrader->update('theme', (object)$this->theme_updates[$theme_slug]);
        
        if ($result) {
            delete_site_transient('update_themes');
            wp_update_themes();
            
            return new WP_REST_Response(true, 200);
        }
        
        return new WP_REST_Response(false, 200);
    }
    
    /**
     * Check if the theme is installed.
     *
     * @since      1.2.2
     *
     * @param $slug
     * @return array
     */
    function needs_update ($slug) {
        $theme = $this->get_theme_data($slug);
        
        if (empty($theme))
            return [];
        
        if ($theme['current_version'] !== $theme['latest_version'])
            return $theme;
        
        return [];
    }
    
    /**
     * Check permissions to use the endpoints.
     * @since      1.2.2
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return bool
     */
    public function permissions_check ($request) {
        return current_user_can('manage_options');
    }
}