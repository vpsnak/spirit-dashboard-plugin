<?php

/**
 * Class for displaying, modifying, & sanitizing spirit passwords.
 *
 * @since 1.1.1
 *
 * @package Two_Factor
 */
class Spirit_Passwords {
    
    /**
     * The user meta spirit password key.
     * @type string
     */
    const USERMETA_KEY_SPIRIT_PASSWORDS = '_spirit_passwords';
    
    /**
     * The length of generated spirit passwords.
     *
     * @type integer
     */
    const PW_LENGTH = 24;
    
    /**
     * Add various hooks.
     *
     * @since 1.1.1
     */
    public static function add_hooks () {
        add_filter('authenticate', array (
            __CLASS__,
            'authenticate'
        ), 10, 3);
        add_action('show_user_profile', array (
            __CLASS__,
            'show_user_profile'
        ));
        add_action('edit_user_profile', array (
            __CLASS__,
            'show_user_profile'
        ));
        add_action('rest_api_init', array (
            __CLASS__,
            'rest_api_init'
        ));
        add_filter('determine_current_user', array (
            __CLASS__,
            'rest_api_auth_handler'
        ), 20);
        add_filter('wp_rest_server_class', array (
            __CLASS__,
            'wp_rest_server_class'
        ));
        
        /*
            Disable REST API link in HTTP headers
            Link: <https://example.com/wp-json/>; rel="https://api.w.org/"
        */
        remove_action('template_redirect', 'rest_output_link_header', 11);
        
        /*
            Disable REST API links in HTML <head>
            <link rel='https://api.w.org/' href='https://example.com/wp-json/' />
        */
        remove_action('wp_head', 'rest_output_link_wp_head', 10);
        remove_action('xmlrpc_rsd_apis', 'rest_output_rsd');

//        if (version_compare(get_bloginfo('version'), '4.7', '>=')) {
//
//            add_filter('rest_authentication_errors', function($access) {
//                if (!is_user_logged_in()) {
//                    return new WP_Error('rest_login_required', json_encode($access).'REST API restricted to authenticated users.', array ('status' => rest_authorization_required_code()));
//                }
//                return $access;
//            });
//        } else {
//            // REST API 1.x
//            add_filter('json_enabled', '__return_false');
//            add_filter('json_jsonp_enabled', '__return_false');
//
//            // REST API 2.x
//            add_filter('rest_enabled', '__return_false');
//            add_filter('rest_jsonp_enabled', '__return_false');
//        }

        self::fallback_populate_username_password();
    }
    
    /**
     * Prevent caching of unauthenticated status.  See comment below.
     *
     * We don't actually care about the `wp_rest_server_class` filter, it just
     * happens right after the constant we do care about is defined.
     *
     * @since 1.1.1
     */
    public static function wp_rest_server_class ($class) {
        global $current_user;
        if (defined('REST_REQUEST') && REST_REQUEST && $current_user instanceof WP_User && 0 === $current_user->ID) {
            /*
             * For our authentication to work, we need to remove the cached lack
             * of a current user, so the next time it checks, we can detect that
             * this is a rest api request and allow our override to happen.  This
             * is because the constant is defined later than the first get current
             * user call may run.
             */
            $current_user = null;
        }
        return $class;
    }
    
    /**
     * Handle declaration of REST API endpoints.
     *
     * @since 1.1.1
     */
    public static function rest_api_init () {
        // List existing spirit passwords
        register_rest_route('spirit-dashboard/v2', '/passwords/(?P<user_id>[\d]+)', array (
            'methods' => WP_REST_Server::READABLE,
            'callback' => __CLASS__ . '::rest_list_spirit_passwords',
            'permission_callback' => __CLASS__ . '::rest_edit_user_callback',
        ));
        
        // Add new spirit passwords
        register_rest_route('spirit-dashboard/v2', '/passwords/(?P<user_id>[\d]+)/add', array (
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => __CLASS__ . '::rest_add_spirit_password',
            'permission_callback' => __CLASS__ . '::rest_edit_user_callback',
            'args' => array (
                'name' => array (
                    'required' => true,
                ),
            ),
        ));
        
        // Delete an spirit password
        register_rest_route('spirit-dashboard/v2', '/passwords/(?P<user_id>[\d]+)/(?P<slug>[\da-fA-F]{12})', array (
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => __CLASS__ . '::rest_delete_spirit_password',
            'permission_callback' => __CLASS__ . '::rest_edit_user_callback',
        ));
        
        // Delete all spirit passwords for a given user
        register_rest_route('spirit-dashboard/v2', '/passwords/(?P<user_id>[\d]+)', array (
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => __CLASS__ . '::rest_delete_all_spirit_passwords',
            'permission_callback' => __CLASS__ . '::rest_edit_user_callback',
        ));
        
        // Some hosts that run PHP in FastCGI mode won't be given the Authentication header.
        register_rest_route('spirit-dashboard/v2', '/test-basic-authorization-header/', array (
            'methods' => WP_REST_Server::READABLE . ', ' . WP_REST_Server::CREATABLE,
            'callback' => __CLASS__ . '::rest_test_basic_authorization_header',
        ));
    }
    
    /**
     * REST API endpoint to list existing spirit passwords for a user.
     *
     * @since 1.1.1
     *
     * @param $data
     *
     * @return array
     */
    public static function rest_list_spirit_passwords ($data) {
        $spirit_passwords = self::get_user_spirit_passwords($data['user_id']);
        $with_slugs = array ();
        
        if ($spirit_passwords) {
            foreach ($spirit_passwords as $item) {
                $item['slug'] = self::password_unique_slug($item);
                unset($item['raw']);
                unset($item['password']);
                
                $item['created'] = date(get_option('date_format', 'r'), $item['created']);
                
                if (empty($item['last_used'])) {
                    $item['last_used'] = '—';
                } else {
                    $item['last_used'] = date(get_option('date_format', 'r'), $item['last_used']);
                }
                
                if (empty($item['last_ip'])) {
                    $item['last_ip'] = '—';
                }
                
                $with_slugs[$item['slug']] = $item;
            }
        }
        
        return $with_slugs;
    }
    
    /**
     * REST API endpoint to add a new spirit password for a user.
     *
     * @since 1.1.1
     *
     * @param $data
     *
     * @return array
     */
    public static function rest_add_spirit_password ($data) {
        list($new_password, $new_item) = self::create_new_spirit_password($data['user_id'], $data['name']);
        
        // Some tidying before we return it.
        $new_item['slug'] = self::password_unique_slug($new_item);
        $new_item['created'] = date(get_option('date_format', 'r'), $new_item['created']);
        $new_item['last_used'] = '—';
        $new_item['last_ip'] = '—';
        unset($new_item['password']);
        
        return array (
            'row' => $new_item,
            'password' => self::chunk_password($new_password)
        );
    }
    
    /**
     * REST API endpoint to delete a given spirit password.
     *
     * @since 1.1.1
     *
     * @param $data
     *
     * @return bool
     */
    public static function rest_delete_spirit_password ($data) {
        return self::delete_spirit_password($data['user_id'], $data['slug']);
    }
    
    /**
     * REST API endpoint to delete all of a user's spirit passwords.
     *
     * @since 1.1.1
     *
     * @param $data
     *
     * @return int The number of deleted passwords
     */
    public static function rest_delete_all_spirit_passwords ($data) {
        return self::delete_all_spirit_passwords($data['user_id']);
    }
    
    /**
     * Whether or not the current user can edit the specified user.
     *
     * @since 1.1.1
     *
     * @param $data
     *
     * @return bool
     */
    public static function rest_edit_user_callback ($data) {
        return current_user_can('edit_user', $data['user_id']);
    }
    
    /**
     * Loosely Based on https://github.com/WP-API/Basic-Auth/blob/master/basic-auth.php
     *
     * @since 1.1.1
     *
     * @param $input_user
     *
     * @return WP_User|bool
     */
    public static function rest_api_auth_handler ($input_user) {
        // Don't authenticate twice
        if (!empty($input_user)) {
            return $input_user;
        }
        
        // Check that we're trying to authenticate
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            return $input_user;
        }
        
        $user = self::authenticate($input_user, $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        
        if ($user instanceof WP_User) {
            return $user->ID;
        }
        
        // If it wasn't a user what got returned, just pass on what we had received originally.
        return $input_user;
    }
    
    /**
     * Test whether PHP can see Basic Authorization headers passed to the web server.
     *
     * @return WP_Error|array
     */
    public static function rest_test_basic_authorization_header () {
        $response = array ();
        
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $response['PHP_AUTH_USER'] = $_SERVER['PHP_AUTH_USER'];
        }
        
        if (isset($_SERVER['PHP_AUTH_PW'])) {
            $response['PHP_AUTH_PW'] = $_SERVER['PHP_AUTH_PW'];
        }
        
        if (empty($response)) {
            return new WP_Error('no-credentials', __('No HTTP Basic Authorization credentials were found submitted with this request.'), array ('status' => 404));
        }
        
        return $response;
    }
    
    /**
     * Some servers running in CGI or FastCGI mode don't pass the Authorization
     * header on to WordPress.  If it's been rewritten to the `REMOTE_USER` header,
     * fill in the proper $_SERVER variables instead.
     */
    public static function fallback_populate_username_password () {
        // If we don't have anything to pull from, return early.
        if (!isset($_SERVER['REMOTE_USER']) && !isset($_SERVER['REDIRECT_REMOTE_USER'])) {
            return;
        }
        
        // If either PHP_AUTH key is already set, do nothing.
        if (isset($_SERVER['PHP_AUTH_USER']) || isset($_SERVER['PHP_AUTH_PW'])) {
            return;
        }
        
        // From our prior conditional, one of these must be set.
        $header = isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] : $_SERVER['REDIRECT_REMOTE_USER'];
        
        // Test to make sure the pattern matches expected.
        if (!preg_match('%^Basic [a-z\d/+]*={0,2}$%i', $header)) {
            return;
        }
        
        // Removing `Bearer ` the token would start six characters in.
        $token = substr($header, 6);
        $userpass = base64_decode($token);
        list($user, $pass) = explode(':', $userpass);
        
        // Now shove them in the proper keys where we're expecting later on.
        $_SERVER['PHP_AUTH_USER'] = $user;
        $_SERVER['PHP_AUTH_PW'] = $pass;
        
        return array (
            $user,
            $pass
        );
    }
    
    /**
     * Filter the user to authenticate.
     *
     * @since 1.1.1
     *
     * @param WP_User $input_user User to authenticate.
     * @param string $username User login.
     * @param string $password User password.
     *
     * @return mixed
     */
    public static function authenticate ($input_user, $username, $password) {
        $api_request = (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) || (defined('REST_REQUEST') && REST_REQUEST);
        if (!apply_filters('spirit_password_is_api_request', $api_request)) {
            return $input_user;
        }
        
        $user = get_user_by('login', $username);
        
        // If the login name is invalid, short circuit.
        if (!$user) {
            return $input_user;
        }
        
        /*
         * Strip out anything non-alphanumeric. This is so passwords can be used with
         * or without spaces to indicate the groupings for readability.
         *
         * Generated spirit passwords are exclusively alphanumeric.
         */
        $password = preg_replace('/[^a-z\d]/i', '', $password);
        
        $hashed_passwords = get_user_meta($user->ID, self::USERMETA_KEY_SPIRIT_PASSWORDS, true);
        
        // If there aren't any, there's nothing to return.  Avoid the foreach.
        if (empty($hashed_passwords)) {
            return $input_user;
        }
        
        foreach ($hashed_passwords as $key => $item) {
            if (wp_check_password($password, $item['password'], $user->ID)) {
                $item['last_used'] = time();
                $item['last_ip'] = $_SERVER['REMOTE_ADDR'];
                $hashed_passwords[$key] = $item;
                update_user_meta($user->ID, self::USERMETA_KEY_SPIRIT_PASSWORDS, $hashed_passwords);
                return $user;
            }
        }
        
        // By default, return what we've been passed.
        return $input_user;
    }
    
    /**
     * Display the spirit password section in a users profile.
     *
     * This executes during the `show_user_security_settings` action.
     *
     * @since 1.1.1
     *
     * @param WP_User $user WP_User object of the logged-in user.
     */
    public static function show_user_profile ($user) {
        wp_enqueue_script('spirit-passwords-js', SPIRIT_DASHBOARD_BASE_URL . 'assets/js/spirit-passwords.js', array ());
        wp_localize_script('spirit-passwords-js', 'appPass', array (
            'root' => esc_url_raw(rest_url()),
            'namespace' => 'spirit-dashboard/v2',
            'nonce' => wp_create_nonce('wp_rest'),
            'user_id' => $user->ID,
            'text' => array (
                'no_credentials' => __('Due to a potential server misconfiguration, it seems that HTTP Basic Authorization may not work for the REST API on this site: `Authorization` headers are not being sent to WordPress by the web server. <a href="https://github.com/georgestephanis/application-passwords/wiki/Basic-Authorization-Header----Missing">Learn more about this problem, and a possible solution.</a>'),
            ),
        ));
        
        ?>
		<div class="spirit-passwords hide-if-no-js" id="spirit-passwords-section">
			<h2 id="spirit-passwords"><?php esc_html_e('Spirit Passwords'); ?></h2>
			<p><?php esc_html_e('Spirit passwords allow authentication via non-interactive systems, such as XMLRPC or the REST API, without providing your actual password. Spirit passwords can be easily revoked. They cannot be used for traditional logins to your website.'); ?></p>
			<div class="create-spirit-password">
				<input type="text" size="30" name="new_spirit_password_name" placeholder="<?php esc_attr_e('New Spirit Password Name'); ?>" class="input" />
                <?php submit_button(__('Add New'), 'secondary', 'do_new_spirit_password', false); ?>
			</div>
			<div class="spirit-passwords-list-table-wrapper">
                <?php
                include_once('class-spirit-passwords-list-table.php');
                $spirit_passwords_list_table = new Spirit_Passwords_List_Table();
                $spirit_passwords_list_table->items = array_reverse(self::get_user_spirit_passwords($user->ID));
                $spirit_passwords_list_table->prepare_items();
                $spirit_passwords_list_table->display();
                ?>
			</div>
		</div>
		<style>
			.app-pass-dialog-background {
				opacity: 1.0;
				background: rgba(0, 0, 0, 0.7);
			}
			.app-pass-dialog {
				padding: 20px;
			}
			.new-spirit-password-content {
				padding-bottom: 20px;
				padding-top: 10px;
			}
		</style>
		<script type="text/html" id="tmpl-new-spirit-password">
			<div class="new-spirit-password notification-dialog-wrap">
				<div class="app-pass-dialog-background notification-dialog-background">
					<div class="app-pass-dialog notification-dialog">
						<div class="new-spirit-password-content">
                            <?php
                            printf(esc_html_x('Your new password for %1$s is: %2$s', 'application, password'), '<strong>{{ data.name }}</strong>', '<kbd>{{ data.password }}</kbd>');
                            ?>
						</div>
						<p><?php esc_attr_e('Be sure to save this in a safe location.  You will not be able to retrieve it.'); ?></p>
						<button class="button button-primary spirit-password-modal-dismiss"><?php esc_attr_e('Dismiss'); ?></button>
					</div>
				</div>
			</div>
		</script>
		<script type="text/html" id="tmpl-spirit-password-row">
			<tr data-slug="{{ data.slug }}">
				<td class="name column-name has-row-actions column-primary" data-colname="<?php esc_attr_e('Name'); ?>">
					{{ data.name }}
				</td>
				<td class="created column-created" data-colname="<?php esc_attr_e('Created'); ?>">
					{{ data.created }}
				</td>
				<td class="last_used column-last_used" data-colname="<?php esc_attr_e('Last Used'); ?>">
					{{ data.last_used }}
				</td>
				<td class="last_ip column-last_ip" data-colname="<?php esc_attr_e('Last IP'); ?>">
					{{ data.last_ip }}
				</td>
				<td class="revoke column-revoke" data-colname="<?php esc_attr_e('Revoke'); ?>">
					<input type="submit" name="revoke-spirit-password" class="button delete" value="<?php esc_attr_e('Revoke'); ?>">
				</td>
			</tr>
		</script>
		<script type="text/html" id="tmpl-spirit-password-notice">
			<div class="notice notice-{{ data.type }}"><p>{{{ data.message }}}</p></div>
		</script>
        <?php
//        require_once 'class-spirit-communication.php';
//
//
//        $com = new Spirit_Com();
//        var_dump($com->update_server());
//		include_once SPIRIT_INC_DIR . 'rest-spirit-dashboard.php';
//		var_dump(get_app_data());
    }
    
    /**
     * Generate a new spirit password.
     *
     * @since 1.1.1
     *
     * @param int $user_id User ID.
     * @param string $name Password name.
     * @return array          The first key in the array is the new password, the second is its row in the table.
     */
    public static function create_new_spirit_password ($user_id, $name) {
        $new_password = wp_generate_password(self::PW_LENGTH, false);
        $hashed_password = wp_hash_password($new_password);
        
        $new_item = array (
            'name' => $name,
            'password' => $hashed_password,
            'created' => time(),
            'last_used' => null,
            'last_ip' => null,
        );
        
        $passwords = self::get_user_spirit_passwords($user_id);
        if (!$passwords) {
            $passwords = array ();
        }
        
        $passwords[] = $new_item;
        self::set_user_spirit_passwords($user_id, $passwords);
        
        return array (
            $new_password,
            $new_item
        );
    }
    
    /**
     * Delete a specified spirit password.
     *
     * @since 1.1.1
     *
     * @see Spirit_Passwords::password_unique_slug()
     *
     * @param int $user_id User ID.
     * @param string $slug The generated slug of the password in question.
     * @return bool Whether the password was successfully found and deleted.
     */
    public static function delete_spirit_password ($user_id, $slug) {
        $passwords = self::get_user_spirit_passwords($user_id);
        
        foreach ($passwords as $key => $item) {
            if (self::password_unique_slug($item) === $slug) {
                unset($passwords[$key]);
                self::set_user_spirit_passwords($user_id, $passwords);
                return true;
            }
        }
        
        // Specified Spirit Password not found!
        return false;
    }
    
    /**
     * Deletes all spirit passwords for the given user.
     *
     * @since 1.1.1
     *
     * @param int $user_id User ID.
     * @return int   The number of passwords that were deleted.
     */
    public static function delete_all_spirit_passwords ($user_id) {
        $passwords = self::get_user_spirit_passwords($user_id);
        
        if (is_array($passwords)) {
            self::set_user_spirit_passwords($user_id, array ());
            return sizeof($passwords);
        }
        
        return 0;
    }
    
    /**
     * Generate a unique repeateable slug from the hashed password, name, and when it was created.
     *
     * @since 1.1.1
     *
     * @param array $item The current item.
     * @return string
     */
    public static function password_unique_slug ($item) {
        $concat = $item['name'] . '|' . $item['password'] . '|' . $item['created'];
        $hash = md5($concat);
        return substr($hash, 0, 12);
    }
    
    /**
     * Sanitize and then split a password into smaller chunks.
     *
     * @since 1.1.1
     *
     * @param string $raw_password Users raw password.
     * @return string
     */
    public static function chunk_password ($raw_password) {
        $raw_password = preg_replace('/[^a-z\d]/i', '', $raw_password);
        return trim(chunk_split($raw_password, 4, ' '));
    }
    
    /**
     * Get a users spirit passwords.
     *
     * @since 1.1.1
     *
     * @param int $user_id User ID.
     * @return array
     */
    public static function get_user_spirit_passwords ($user_id) {
        $passwords = get_user_meta($user_id, self::USERMETA_KEY_SPIRIT_PASSWORDS, true);
        if (!is_array($passwords)) {
            return array ();
        }
        return $passwords;
    }
    
    /**
     * Set a users spirit passwords.
     *
     * @since 1.1.1
     *
     * @param int $user_id User ID.
     * @param array $passwords Spirit passwords.
     *
     * @return bool
     */
    public static function set_user_spirit_passwords ($user_id, $passwords) {
        return update_user_meta($user_id, self::USERMETA_KEY_SPIRIT_PASSWORDS, $passwords);
    }
}
