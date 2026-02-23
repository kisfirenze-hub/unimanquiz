<?php
/**
 * Gestisce l'attivazione del plugin
 *
 * @package    FoodWise
 * @subpackage FoodWise/includes
 */

class FoodWise_Activator {

    /**
     * Attivazione del plugin
     * Crea le tabelle del database e imposta le opzioni iniziali
     */
    public static function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Tabella utenti FoodWise
        $table_users = $wpdb->prefix . 'foodwise_users';
        $sql_users = "CREATE TABLE $table_users (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            panelist_code varchar(50) NOT NULL,
            access_type enum('quiz','viewer') NOT NULL DEFAULT 'quiz',
            test_name varchar(255) DEFAULT NULL,
            day int DEFAULT NULL,
            month int DEFAULT NULL,
            year int DEFAULT NULL,
            test_completed_date date DEFAULT NULL,
            section_name varchar(255) DEFAULT NULL,
            section_number int DEFAULT NULL,
            sample_set_number int DEFAULT NULL,
            rep_number int DEFAULT NULL,
            motivational_fields text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY panelist_code (panelist_code),
            KEY access_type (access_type)
        ) $charset_collate;";
        dbDelta($sql_users);
        
        // Tabella categorie
        $table_categories = $wpdb->prefix . 'foodwise_categories';
        $sql_categories = "CREATE TABLE $table_categories (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            category_code varchar(50) NOT NULL,
            category_name varchar(255) NOT NULL,
            blinding_code varchar(50) DEFAULT NULL,
            simple_type varchar(50) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY category_code (category_code)
        ) $charset_collate;";
        dbDelta($sql_categories);
        
        // Tabella progressi quiz (per riprendere quiz incompleti)
        $table_progress = $wpdb->prefix . 'foodwise_quiz_progress';
        $sql_progress = "CREATE TABLE $table_progress (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            quiz_type enum('quiz_1','quiz_2') NOT NULL,
            current_step int DEFAULT 0,
            progress_data longtext NOT NULL,
            question_order text DEFAULT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_quiz (user_id, quiz_type)
        ) $charset_collate;";
        dbDelta($sql_progress);
        
        // Tabella tipologie quiz
        $table_quiz_types = $wpdb->prefix . 'foodwise_quiz_types';
        $sql_quiz_types = "CREATE TABLE $table_quiz_types (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            quiz_type enum('quiz_1','quiz_2') NOT NULL,
            quiz_name varchar(255) NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            description text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY quiz_type (quiz_type)
        ) $charset_collate;";
        dbDelta($sql_quiz_types);
        
        // Tabella domande Quiz 1
        $table_quiz_1 = $wpdb->prefix . 'foodwise_quiz_1_questions';
        $sql_quiz_1 = "CREATE TABLE $table_quiz_1 (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            category_id bigint(20) NOT NULL,
            question_text text NOT NULL,
            order_position int DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            is_selected tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY category_id (category_id)
        ) $charset_collate;";
        dbDelta($sql_quiz_1);
        
        // Tabella domande Quiz 2
        $table_quiz_2 = $wpdb->prefix . 'foodwise_quiz_2_questions';
        $sql_quiz_2 = "CREATE TABLE $table_quiz_2 (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            section_title varchar(255) DEFAULT NULL,
            section_text text DEFAULT NULL,
            subsection_text text DEFAULT NULL,
            question_text text NOT NULL,
            question_type enum('multiple','true_false','yes_no') NOT NULL,
            options text DEFAULT NULL,
            order_position int DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            is_selected tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql_quiz_2);
        
        // Tabella submissions
        $table_submissions = $wpdb->prefix . 'foodwise_submissions';
        $sql_submissions = "CREATE TABLE $table_submissions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            quiz_type enum('quiz_1','quiz_2') NOT NULL,
            submission_data longtext NOT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            submitted_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY quiz_type (quiz_type),
            KEY submitted_at (submitted_at)
        ) $charset_collate;";
        dbDelta($sql_submissions);
        
        // Tabella upload CSV
        $table_csv = $wpdb->prefix . 'foodwise_csv_uploads';
        $sql_csv = "CREATE TABLE $table_csv (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            filename varchar(255) NOT NULL,
            uploaded_by bigint(20) NOT NULL,
            records_imported int DEFAULT 0,
            upload_type enum('users','reports') NOT NULL,
            file_path varchar(500) DEFAULT NULL,
            uploaded_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY uploaded_by (uploaded_by)
        ) $charset_collate;";
        dbDelta($sql_csv);
        
        // Popolamento categorie iniziali
        FoodWise_Categories::populate_default_categories();

        // Inserimento dati iniziali per i tipi di quiz
        $wpdb->insert(
            $table_quiz_types,
            array(
                'quiz_type' => 'quiz_1',
                'quiz_name' => 'Valutazione dello spreco domestico',
                'is_active' => 1,
                'description' => 'Quiz con slider per valutare lo spreco alimentare per categoria'
            ),
            array('%s', '%s', '%d', '%s')
        );
        
        $wpdb->insert(
            $table_quiz_types,
            array(
                'quiz_type' => 'quiz_2',
                'quiz_name' => 'KAP (Knowledge, Attitude, Practice)',
                'is_active' => 1,
                'description' => 'Quiz con domande a risposta multipla, vero/falso e sì/no'
            ),
            array('%s', '%s', '%d', '%s')
        );
        
        // Imposta opzioni plugin
        add_option('foodwise_version', FOODWISE_VERSION);
        add_option('foodwise_db_version', '1.0');
        
        // Crea pagine necessarie
        self::create_plugin_pages();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Crea le pagine necessarie per il plugin
     */
    private static function create_plugin_pages() {
        // Pagina di accesso
        $access_page = array(
            'post_title'    => 'Accesso FoodWise',
            'post_content'  => '[foodwise_access]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'foodwise-access'
        );
        
        $access_page_id = wp_insert_post($access_page);
        update_option('foodwise_access_page_id', $access_page_id);

        // Pagina Selezione Quiz
        $selection_page = array(
            'post_title'    => 'Selezione Quiz FoodWise',
            'post_content'  => '[foodwise_quiz_selection]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'foodwise-selection'
        );
        
        $selection_page_id = wp_insert_post($selection_page);
        update_option('foodwise_selection_page_id', $selection_page_id);
        
        // Pagina Quiz 1
        $quiz1_page = array(
            'post_title'    => 'Quiz Valutazione Spreco',
            'post_content'  => '[foodwise_quiz_1]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'foodwise-quiz-1'
        );
        
        $quiz1_page_id = wp_insert_post($quiz1_page);
        update_option('foodwise_quiz1_page_id', $quiz1_page_id);
        
        // Pagina Quiz 2
        $quiz2_page = array(
            'post_title'    => 'Quiz KAP',
            'post_content'  => '[foodwise_quiz_2]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'foodwise-quiz-2'
        );
        
        $quiz2_page_id = wp_insert_post($quiz2_page);
        update_option('foodwise_quiz2_page_id', $quiz2_page_id);
        
        // Pagina Viewer
        $viewer_page = array(
            'post_title'    => 'Dashboard Visualizzatore',
            'post_content'  => '[foodwise_viewer]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'foodwise-viewer'
        );
        
        $viewer_page_id = wp_insert_post($viewer_page);
        update_option('foodwise_viewer_page_id', $viewer_page_id);
    }
}
