<?php
/**
 * Gestisce la disattivazione del plugin
 *
 * @package    FoodWise
 * @subpackage FoodWise/includes
 */

class FoodWise_Deactivator {

    /**
     * Disattivazione del plugin
     * Pulisce i transient e flush delle rewrite rules
     */
    public static function deactivate() {
        // Pulisce i transient cache
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_foodwise_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_foodwise_%'");
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
