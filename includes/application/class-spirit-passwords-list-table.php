<?php
// Load the parent class if it doesn't exist.
if (!class_exists('WP_List_Table')) {
    include_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Class for displaying the list of spirit password items.
 *
 * @since 1.1.1
 *
 * @package Two_Factor
 */
class Spirit_Passwords_List_Table extends WP_List_Table {
    
    /**
     * Get a list of columns.
     *
     * @since 1.1.1
     *
     * @return array
     */
    public function get_columns () {
        return array (
            'name' => wp_strip_all_tags(__('Name')),
            'created' => wp_strip_all_tags(__('Created')),
            'last_used' => wp_strip_all_tags(__('Last Used')),
            'last_ip' => wp_strip_all_tags(__('Last IP')),
            'revoke' => wp_strip_all_tags(__('Revoke')),
        );
    }
    
    /**
     * Prepares the list of items for displaying.
     *
     * @since 1.1.1
     */
    public function prepare_items () {
        $columns = $this->get_columns();
        $hidden = array ();
        $sortable = array ();
        $primary = 'name';
        $this->_column_headers = array (
            $columns,
            $hidden,
            $sortable,
            $primary
        );
    }
    
    /**
     * Generates content for a single row of the table
     *
     * @since 1.1.1
     *
     * @param object $item The current item.
     * @param string $column_name The current column name.
     * @return false|string
     */
    protected function column_default ($item, $column_name) {
        switch ($column_name) {
            case 'name':
                return esc_html($item['name']);
            case 'created':
                if (empty($item['created'])) {
                    return '&mdash;';
                }
                return date(get_option('date_format', 'r'), $item['created']);
            case 'last_used':
                if (empty($item['last_used'])) {
                    return '&mdash;';
                }
                return date(get_option('date_format', 'r'), $item['last_used']);
            case 'last_ip':
                if (empty($item['last_ip'])) {
                    return '&mdash;';
                }
                return $item['last_ip'];
            case 'revoke':
                return get_submit_button(__('Revoke'), 'delete', 'revoke-spirit-password', false);
            default:
                return 'WTF^^?';
        }
    }
    
    /**
     * Generates custom table navigation to prevent conflicting nonces.
     *
     * @since 1.1.1
     *
     * @param string $which The location of the bulk actions: 'top' or 'bottom'.
     */
    protected function display_tablenav ($which) {
        ?>
		<div class="tablenav <?php echo esc_attr($which); ?>">
            <?php if ('bottom' === $which) : ?>
				<div class="alignright">
                    <?php submit_button(__('Revoke all spirit passwords'), 'delete', 'revoke-all-spirit-passwords', false); ?>
				</div>
            <?php endif; ?>
			<div class="alignleft actions bulkactions">
                <?php $this->bulk_actions($which); ?>
			</div>
            <?php
            $this->extra_tablenav($which);
            $this->pagination($which);
            ?>
			<br class="clear" />
		</div>
        <?php
    }
    
    /**
     * Generates content for a single row of the table.
     *
     * @since 1.1.1
     *
     * @param object $item The current item.
     */
    public function single_row ($item) {
        echo '<tr data-slug="' . esc_attr(Spirit_Passwords::password_unique_slug($item)) . '">';
        $this->single_row_columns($item);
        echo '</tr>';
    }
}
