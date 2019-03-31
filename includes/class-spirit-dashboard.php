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
        
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-spirit-dashboard-loader.php';
        
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/rest-spirit-dashboard.php';
        
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/application/class-spirit-passwords.php';
        
        $this->loader = new Spirit_Dashboard_Loader();
        
    }
    
    /**
     * The init of the plugin.
     *
     * @since    0.0.1
     */
    public function run () {
        $this->loader->run();
        Spirit_Passwords::add_hooks();
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
