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

class Spirit_Updater {
    
    /**
     * Update an item, if appropriate.
     *
     * @since      1.1.0
     *
     * @param string $type The type of update being checked: 'core', 'theme', 'plugin', 'translation'.
     * @param object $item The update offer.
     * @return null|WP_Error
     */
    public function update ($type, $item) {
        if (!function_exists('request_filesystem_credentials'))
            include_once(ABSPATH . 'wp-admin/includes/file.php');
        
        include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
        include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php');
        include_once(ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php');
        include_once(ABSPATH . 'wp-admin/includes/class-wp-automatic-updater.php');
        
        $upgrader = new WP_Automatic_Updater();
        
        add_filter('wp_doing_cron', array (
            $this,
            'get_true'
        ));
        add_filter("auto_update_{$type}", array (
            $this,
            'get_true'
        ));
        
        $result = $upgrader->update($type, $item);
        
        remove_filter("auto_update_{$type}", array (
            $this,
            'get_true'
        ));
        remove_filter("wp_doing_cron", array (
            $this,
            'get_true'
        ));
        
        return $result;
    }
    
    /**
     * Used to disable AutoUpdater to run our updates.
     *
     * @since      1.1.0
     *
     * @return true
     */
    public function get_true () {
        return true;
    }
}