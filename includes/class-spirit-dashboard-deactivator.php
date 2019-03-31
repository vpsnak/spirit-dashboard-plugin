<?php

/**
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      0.0.1
 * @package    Spirit_Dashboard
 * @subpackage Spirit_Dashboard/includes
 * @author     Vaggelis Pallis <info.vpsnak@gmail.com>
 */
class Spirit_Dashboard_Deactivator {
    
    /**
     * Deactivate sequence.
     *
     * @since    0.0.1
     */
    public static function deactivate () {
        wp_schedule_event(time(), 'twicedaily', 'update_spirit_server');
    }
    
}
