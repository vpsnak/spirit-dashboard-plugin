<?php

/**
 * The core plugin class.
 *
 * @since      0.0.1
 * @package    Spirit_Dashboard
 * @subpackage Spirit_Dashboard/includes
 * @author     Vaggelis Pallis <info.vpsnak@gmail.com>
 */
class Spirit_Dashboard {
    
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    0.0.1
     * @access   protected
     * @var      Spirit_Dashboard_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;
    
    /**
     * The unique identifier of this plugin.
     *
     * @since    0.0.1
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;
    
    /**
     * The current version of the plugin.
     *
     * @since    0.0.1
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;
    
    /**
     * The server communication handler.
     *
     * @since    1.1.3
     * @access   protected
     * @var      Spirit_Com $server
     */
    protected $server;
    
    /**
     * Define the core functionality of the plugin.
     *
     * @since    0.0.1
     */
    public function __construct () {
        if (defined('SPIRIT_DASHBOARD_VERSION')) {
            $this->version = SPIRIT_DASHBOARD_VERSION;
        } else {
            $this->version = '0.0.1';
        }
        $this->plugin_name = 'spirit-dashboard';
        
        $this->load_dependencies();
        
    }
    
    /**
     * Load the required dependencies for this plugin.
     *
     * @since    0.0.1
     * @access   private
     */
    private function load_dependencies () {
        
        include_once(SPIRIT_INC_DIR . 'class-spirit-dashboard-loader.php');
        
        include_once(SPIRIT_INC_DIR . 'rest-spirit-dashboard.php');
        
        include_once(SPIRIT_APP_DIR . 'class-spirit-passwords.php');
        
        include_once(SPIRIT_APP_DIR . 'class-spirit-communication.php');
        
        $this->loader = new Spirit_Dashboard_Loader();
        
        $this->server = new Spirit_Com();
    }
    
    /**
     * The init of the plugin.
     *
     * @since    0.0.1
     */
    public function run () {
        $this->loader->run();
        Spirit_Passwords::add_hooks();
        add_action('update_spirit_server', array (
            $this->server,
            'update_server'
        ));
    
        include_once(SPIRIT_ADMIN_DIR . 'spirit-register-pages.php');
    }
    
    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     0.0.1
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name () {
        return $this->plugin_name;
    }
    
    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     0.0.1
     * @return    Spirit_Dashboard_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader () {
        return $this->loader;
    }
    
    /**
     * Retrieve the version number of the plugin.
     *
     * @since     0.0.1
     * @return    string    The version number of the plugin.
     */
    public function get_version () {
        return $this->version;
    }
    
}
