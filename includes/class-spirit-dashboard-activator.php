<?php

/**
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.0.1
 * @package    Spirit_Dashboard
 * @subpackage Spirit_Dashboard/includes
 * @author     Vaggelis Pallis <info.vpsnak@gmail.com>
 */
class Spirit_Dashboard_Activator {
    
    /**
     * Activate sequence.
     *
     * @since    0.0.1
     */
    public static function activate () {
        if (!wp_next_scheduled('update_spirit_server')) {
            wp_schedule_event(time(), 'twicedaily', 'update_spirit_server');
        }
    }
    
}
