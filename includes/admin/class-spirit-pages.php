<?php
/**
 * The user class that handles roles and capabilities
 *
 * @since      1.2.3
 * @package    Spirit_Dashboard
 * @subpackage Spirit_Dashboard/includes/application
 * @author     Vaggelis Pallis <info.vpsnak@gmail.com>
 */

class Spirit_Pages {
    /**
     * Settings array
     *
     * @since      1.2.3
     * @var array
     */
    public $settings = array ();
    
    /**
     * Sections array
     *
     * @since      1.2.3
     * @var array
     */
    public $sections = array ();
    
    /**
     * Fields array
     *
     * @since      1.2.3
     * @var array
     */
    public $fields = array ();
    
    /**
     * Script path
     *
     * @since      1.2.3
     * @var string
     */
    public $script_path;
    
    /**
     * Enqueues array
     *
     * @since      1.2.3
     * @var array
     */
    public $enqueues = array ();
    
    /**
     * Admin pages array to enqueue scripts
     *
     * @since      1.2.3
     * @var array
     */
    public $enqueue_on_pages = array ();
    
    /**
     * Admin pages array
     *
     * @since      1.2.3
     * @var array
     */
    public $admin_pages = array ();
    
    /**
     * Admin subpages array
     *
     * @since      1.2.3
     * @var array
     */
    public $admin_subpages = array ();
    
    /**
     * Constructor
     *
     * @since      1.2.3
     */
    public function __construct () {
    }
    
    /**
     * Register pages, scripts and options
     *
     * @since      1.2.3
     */
    public function register () {
        if (!empty($this->enqueues))
            add_action('admin_enqueue_scripts', array (
                $this,
                'admin_scripts'
            ));
        
        if (!empty($this->admin_pages) || !empty($this->admin_subpages))
            add_action('admin_menu', array (
                $this,
                'add_admin_menu'
            ));
        
        if (!empty($this->settings))
            add_action('admin_init', array (
                $this,
                'register_custom_settings'
            ));
    }
    
    /**
     * Dinamically enqueue styles and scripts in admin area
     *
     * @since      1.2.3
     * @param  array $scripts file paths or wp related keywords of embedded files
     * @param  array $pages pages id where to load scripts
     * @return $this
     */
    public function admin_enqueue ($scripts = array (), $pages = array ()) {
        if (empty($scripts))
            return;
        
        $i = 0;
        foreach ($scripts as $key => $value) :
            foreach ($value as $val):
                $this->enqueues[$i] = $this->enqueue_script($val, $key);
                $i++;
            endforeach;
        endforeach;
        
        if (!empty($pages)) :
            $this->enqueue_on_pages = $pages;
        endif;
        
        return $this;
    }
    
    /**
     * Call the right WP functions based on the file or string passed
     *
     * @since      1.2.3
     * @param  array $script file path or wp related keyword of embedded file
     * @param  var $type style | script
     * @return array|string functions
     */
    private function enqueue_script ($script, $type) {
        if ($script === 'media_uploader')
            return 'wp_enqueue_media';
        
        return ($type === 'style') ? array ('wp_enqueue_style' => $script) : array ('wp_enqueue_script' => $script);
    }
    
    /**
     * Print the methods to be called by the admin_enqueue_scripts hook
     *
     * @since      1.2.3
     * @param $hook page id or filename passed by admin_enqueue_scripts
     */
    public function admin_scripts ($hook) {
        // dd( $hook );
        $this->enqueue_on_pages = (!empty($this->enqueue_on_pages)) ? $this->enqueue_on_pages : array ($hook);
        
        if (in_array($hook, $this->enqueue_on_pages)) :
            foreach ($this->enqueues as $enqueue) :
                if ($enqueue === 'wp_enqueue_media') :
                    $enqueue();
                else :
                    foreach ($enqueue as $key => $val) {
                        $key($val, $val);
                    }
                endif;
            endforeach;
        endif;
    }
    
    /**
     * Injects user's defined pages array into $admin_pages array
     *
     * @since      1.2.3
     * @param $pages array of user's defined pages
     * @return $this
     */
    public function addPages ($pages) {
        $this->admin_pages = $pages;
        
        return $this;
    }
    
    public function withSubPage ($title = null) {
        if (empty($this->admin_pages)) {
            return $this;
        }
        
        $adminPage = $this->admin_pages[0];
        
        $subpage = array (
            array (
                'parent_slug' => $adminPage['menu_slug'],
                'page_title' => $adminPage['page_title'],
                'menu_title' => ($title) ? $title : $adminPage['menu_title'],
                'capability' => $adminPage['capability'],
                'menu_slug' => $adminPage['menu_slug'],
                'callback' => $adminPage['callback']
            )
        );
        
        $this->admin_subpages = $subpage;
        
        return $this;
    }
    
    /**
     * Injects user's defined pages array into $admin_subpages array
     *
     * @since      1.2.3
     * @param $pages array of user's defined pages
     * @return $this
     */
    public function addSubPages ($pages) {
        $this->admin_subpages = (count($this->admin_subpages) == 0) ? $pages : array_merge($this->admin_subpages, $pages);
        
        return $this;
    }
    
    /**
     * Call WordPress methods to generate Admin pages and subpages
     *
     * @since      1.2.3
     */
    public function add_admin_menu () {
        foreach ($this->admin_pages as $page) {
            add_menu_page($page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], $page['callback'], $page['icon_url'], $page['position']);
        }
        
        foreach ($this->admin_subpages as $page) {
            add_submenu_page($page['parent_slug'], $page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], $page['callback']);
        }
    }
    
    /**
     * Injects user's defined settings array into $settings array
     *
     * @since      1.2.3
     * @param  $args array of user's defined settings
     * @return $this
     */
    public function add_settings ($args) {
        $this->settings = $args;
        
        return $this;
    }
    
    /**
     * Injects user's defined sections array into $sections array
     *
     * @since      1.2.3
     * @param $args array of user's defined sections
     * @return $this
     */
    public function add_sections ($args) {
        $this->sections = $args;
        
        return $this;
    }
    
    /**
     * Injects user's defined fields array into $fields array
     *
     * @param $args array of user's defined fields
     * @return $this
     */
    public function add_fields ($args) {
        $this->fields = $args;
        
        return $this;
    }
    
    /**
     * Call WordPress methods to register settings, sections, and fields
     *
     * @since      1.2.3
     */
    public function register_custom_settings () {
        foreach ($this->settings as $setting) {
            register_setting($setting["option_group"], $setting["option_name"], array (
                isset($setting["callback"]) ? $setting["callback"] : '',
                'show_in_rest' => true
            ));
        }
        
        foreach ($this->sections as $section) {
            add_settings_section($section["id"], $section["title"], (isset($section["callback"]) ? $section["callback"] : ''), $section["page"]);
        }
        
        foreach ($this->fields as $field) {
            add_settings_field($field["id"], $field["title"], (isset($field["callback"]) ? $field["callback"] : ''), $field["page"], $field["section"], (isset($field["args"]) ? $field["args"] : ''));
        }
    }
    
}