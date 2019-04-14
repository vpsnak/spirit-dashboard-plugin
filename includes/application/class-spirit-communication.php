<?php
/**
 * The updater class
 *
 * This is used handle the communication with the main server
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
        
        $user_name = 'SpiritDashboard';
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
            'username' => $this->user->user_nicename,
            'token' => $new_password
        );
    }
    
    /**
     * Update the server about this site.
     *
     * @since      1.1.2
     * @return array|null
     */
    public function update_server () {
        if (!$this->user instanceof WP_User)
            return NULL;
        
        $site_domain = wp_parse_url(get_site_url())['host'];
        
        $request_args = array (
            'headers' => array (
                'Authorization' => 'Bearer ' . $this->get_token()
            )
        );
        // @TODO Change unique check on server from title to licence key
        $response = wp_remote_get(SPIRIT_SERVER_API . "wp/v2/spirit-sites?search=$site_domain&_fields[]=id", $request_args);
        if (is_array($response)) {
            $already_registered = json_decode($response['body'], true);
            include_once(SPIRIT_INC_DIR . 'rest-spirit-dashboard.php');
            
            $user = $this->reset_authorization_data();
            
            include_once(ABSPATH . 'wp-includes/update.php');
            wp_version_check();
            wp_update_plugins();
            wp_update_themes();
            
            // @TODO Change status to pending when the server will check the status
            $request_args = array_merge($request_args, array (
                'method' => 'POST',
                'body' => array (
                    'status' => 'publish',
                    'spirit_site_meta' => array (
                        "spirit_site_url" => $site_domain,
                        "spirit_site_user" => $user['username'],
                        "spirit_site_token" => $user['token'],
                        "spirit_site_json" => json_encode(get_app_data())
                    )
                )
            ));
            if (empty($already_registered)) {
                $response = wp_remote_post(SPIRIT_SERVER_API . "wp/v2/spirit-sites?title=$site_domain", $request_args);
                $rr = array (
                    'yes',
                    $response['body']
                );
            } else {
                $id = $already_registered[0]['id'];
                $response = wp_remote_post(SPIRIT_SERVER_API . "wp/v2/spirit-sites/$id/?title=$site_domain", $request_args);
                $rr = array (
                    'no',
                    $response['body']
                );
            }
        } else {
            return NULL;
        }
        
        return $rr[1];
    }
    
    /**
     * Request auth token from server
     *
     * @since      1.2.3
     * @return array|null|WP_Error
     */
    private function request_token () {
        $args = array (
            'method' => 'POST',
            'body' => array (
                'username' => get_option('sd-username'),
                'password' => get_option('sd-password')
            )
        );
        
        $get_token = wp_remote_post(SPIRIT_SERVER_API . "jwt-auth/v1/token", $args);
        if (is_wp_error($get_token))
            return NULL;
        
        return $get_token;
    }
    
    /**
     * Validate auth token
     *
     * @since      1.2.3
     * @return array|null|WP_Error
     */
    private function validate_token () {
        $args = array (
            'method' => 'POST',
            'headers' => array (
                'Authorization' => 'Bearer ' . get_option('spirit_licence_server')
            )
        );
        
        $get_token = wp_remote_post(SPIRIT_SERVER_API . "jwt-auth/v1/token/validate", $args);
        if (is_wp_error($get_token))
            return NULL;
        
        return $get_token;
    }
    
    /**
     * Check and retrieve auth token from api calls
     *
     * @since      1.2.3
     * @return mixed
     */
    public function get_token () {
        if (!get_option('spirit_licence_server')) {
            add_option('spirit_licence_server', '', '', 'yes');
        }
        //        else {
        //            update_option('spirit_licence_server', 'hard-coded-token');
        //        }
        
        $token = get_option('spirit_licence_server');
        $validate_token = $this->validate_token();
        $response = json_decode($validate_token['body'], true);
        if (!isset($response['data']) || !isset($response['data']['status']) || $response['data']['status'] != 200) {
            $request_token = $this->request_token();
            $response = json_decode($request_token['body'], true);
            if (isset($response['token'])) {
                $token = $response['token'];
                update_option('spirit_licence_server', $token);
            }
        }
        
        return $token;
    }
}