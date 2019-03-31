<?php
/**
 * The updater class
 *
 * This is used handle the commuinication with the main server
 *
 * @since      1.1.2
 * @package    Spirit_Dashboard
 * @subpackage Spirit_Dashboard/includes/application
 * @author     Vaggelis Pallis <info.vpsnak@gmail.com>
 */

class Spirit_Com {
    
    /**
     * The WP_User who will communicate with the server.
     *
     * @since      1.1.2
     * @var null|WP_User
     */
    protected $user = NULL;
    
    /**
     * Spirit_Com constructor.
     *
     * @since      1.1.2
     */
    public function __construct () {
        $this->create_authorized_user();
    }
    
    /**
     * Create / Get the authorized user.
     *
     * @since      1.1.2
     */
    private function create_authorized_user () {
        if (!function_exists('get_user_by'))
            include_once(ABSPATH . 'wp-includes/pluggable.php');
        
        $user_name = 'spiritdashboard';
        $user_email = 'support@vpsnak.com';
        $user_id = username_exists($user_name);
        if (!$user_id && email_exists($user_email) == false) {
            $random_password = wp_generate_password(16, true, true);
            $user_id = wp_create_user($user_name, $random_password, $user_email);
            $this->user = new WP_User($user_id);
            
            if ($this->user instanceof WP_User)
                $this->user->set_role('administrator');
            
        } else if (is_numeric($user_id)) {
            $this->user = new WP_User($user_id);
            
            if ($this->user instanceof WP_User)
                $this->user->set_role('administrator');
        }
    }
    
    /**
     * Reset user's authentication token.
     *
     * @since      1.1.2
     * @return array|null
     */
    private function reset_authorization_data () {
        if (!$this->user instanceof WP_User)
            return NULL;
        
        include_once('class-spirit-passwords.php');
        Spirit_Passwords::set_user_spirit_passwords($this->user->ID, array ());
        list($new_password, $new_item) = Spirit_Passwords::create_new_spirit_password($this->user->ID, 'Spirit Dashboard');
        
        return array (
            'user_name' => $this->user->user_nicename,
            'token' => $new_password
        );
    }
    
    /**
     * Update the server about this site.
     *
     * @since      1.1.2
     * @return array|null
     */
    public function updateServer () {
        if (!$this->user instanceof WP_User)
            return NULL;
        
        $send = $this->reset_authorization_data();
        $send['site_url'] = get_site_url();
        return $send;
    }
}