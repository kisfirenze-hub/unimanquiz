<?php
/**
 * Disinstallazione del plugin
 * Questo file viene eseguito quando il plugin viene disinstallato
 *
 * @package    FoodWise
 */

// Se uninstall non viene chiamato da WordPress, esci
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Elimina le tabelle del database
$tables = array(
    $wpdb->prefix . 'foodwise_users',
    $wpdb->prefix . 'foodwise_categories',
    $wpdb->prefix . 'foodwise_quiz_types',
    $wpdb->prefix . 'foodwise_quiz_1_questions',
    $wpdb->prefix . 'foodwise_quiz_2_questions',
    $wpdb->prefix . 'foodwise_submissions',
    $wpdb->prefix . 'foodwise_csv_uploads'
);

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS $table");
}

// Elimina le opzioni
delete_option('foodwise_version');
delete_option('foodwise_db_version');
delete_option('foodwise_access_page_id');
delete_option('foodwise_quiz1_page_id');
delete_option('foodwise_quiz2_page_id');
delete_option('foodwise_viewer_page_id');

// Elimina le pagine create dal plugin
$page_ids = array(
    get_option('foodwise_access_page_id'),
    get_option('foodwise_quiz1_page_id'),
    get_option('foodwise_quiz2_page_id'),
    get_option('foodwise_viewer_page_id')
);

foreach ($page_ids as $page_id) {
    if ($page_id) {
        wp_delete_post($page_id, true);
    }
}

// Pulisce i transient
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_foodwise_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_foodwise_%'");
