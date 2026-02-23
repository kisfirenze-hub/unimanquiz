<?php
/**
 * Pannello amministrazione
 *
 * @package    FoodWise
 * @subpackage FoodWise/admin
 */

class FoodWise_Admin {
    
    /**
     * Inizializzazione
     */
    public function init() {
        // Menu amministrazione
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Enqueue scripts e styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // AJAX handlers
        add_action('wp_ajax_foodwise_upload_csv', array($this, 'ajax_upload_csv'));
        add_action('wp_ajax_foodwise_toggle_quiz', array($this, 'ajax_toggle_quiz'));
        add_action('wp_ajax_foodwise_save_category', array($this, 'ajax_save_category'));
        add_action('wp_ajax_foodwise_delete_category', array($this, 'ajax_delete_category'));
        add_action('wp_ajax_foodwise_generate_codes', array($this, 'ajax_generate_codes'));
        add_action('wp_ajax_foodwise_export_users', array($this, 'ajax_export_users'));
        add_action('wp_ajax_foodwise_export_submissions', array($this, 'ajax_export_submissions'));
        add_action('wp_ajax_foodwise_save_quiz_2_question', array($this, 'ajax_save_quiz_2_question'));
        add_action('wp_ajax_foodwise_delete_quiz_question', array($this, 'ajax_delete_quiz_question'));
    }
    
    /**
     * Aggiunge il menu amministrazione
     */
    public function add_admin_menu() {
        add_menu_page(
            'FoodWise',
            'FoodWise',
            'manage_options',
            'foodwise',
            array($this, 'display_dashboard'),
            'dashicons-food',
            30
        );
        
        add_submenu_page(
            'foodwise',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'foodwise',
            array($this, 'display_dashboard')
        );
        
        add_submenu_page(
            'foodwise',
            'Utenti',
            'Utenti',
            'manage_options',
            'foodwise-users',
            array($this, 'display_users')
        );
        
        add_submenu_page(
            'foodwise',
            'Categorie',
            'Categorie',
            'manage_options',
            'foodwise-categories',
            array($this, 'display_categories')
        );
        
        add_submenu_page(
            'foodwise',
            'Impostazioni Quiz',
            'Impostazioni Quiz',
            'manage_options',
            'foodwise-quiz-settings',
            array($this, 'display_quiz_settings')
        );
        
        add_submenu_page(
            'foodwise',
            'Import CSV',
            'Import CSV',
            'manage_options',
            'foodwise-csv-import',
            array($this, 'display_csv_import')
        );
        
        add_submenu_page(
            'foodwise',
            'Report',
            'Report',
            'manage_options',
            'foodwise-reports',
            array($this, 'display_reports')
        );
        
        add_submenu_page(
            'foodwise',
            'Domande Quiz 2',
            'Domande Quiz 2',
            'manage_options',
            'foodwise-quiz-2-questions',
            array($this, 'display_quiz_2_questions')
        );
    }
    
    /**
     * Enqueue styles
     */
    public function enqueue_styles($hook) {
        if (strpos($hook, 'foodwise') === false) {
            return;
        }
        
        wp_enqueue_style(
            'foodwise-admin',
            FOODWISE_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            FOODWISE_VERSION
        );
    }
    
    /**
     * Enqueue scripts
     */
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'foodwise') === false) {
            return;
        }
        
        wp_enqueue_script(
            'foodwise-admin',
            FOODWISE_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            FOODWISE_VERSION,
            true
        );
        
        // Localizza script per AJAX
        wp_localize_script('foodwise-admin', 'foodwiseAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('foodwise_admin_nonce')
        ));
        
        // Chart.js per grafici
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js',
            array(),
            '3.9.1',
            true
        );
    }
    
    /**
     * Display Dashboard
     */
    public function display_dashboard() {
        include FOODWISE_PLUGIN_DIR . 'admin/partials/admin-dashboard.php';
    }
    
    /**
     * Display Utenti
     */
    public function display_users() {
        include FOODWISE_PLUGIN_DIR . 'admin/partials/admin-users.php';
    }
    
    /**
     * Display Categorie
     */
    public function display_categories() {
        include FOODWISE_PLUGIN_DIR . 'admin/partials/admin-categories.php';
    }
    
    /**
     * Display Impostazioni Quiz
     */
    public function display_quiz_settings() {
        include FOODWISE_PLUGIN_DIR . 'admin/partials/admin-quiz-settings.php';
    }
    
    /**
     * Display Import CSV
     */
    public function display_csv_import() {
        include FOODWISE_PLUGIN_DIR . 'admin/partials/admin-csv-import.php';
    }
    
    /**
     * Display Report
     */
    public function display_reports() {
        include FOODWISE_PLUGIN_DIR . 'admin/partials/admin-reports.php';
    }
    
    /**
     * Display Domande Quiz 2
     */
    public function display_quiz_2_questions() {
        include FOODWISE_PLUGIN_DIR . 'admin/partials/admin-quiz-2-questions.php';
    }
    
    /**
     * AJAX: Salva Domanda Quiz 2
     */
    public function ajax_save_quiz_2_question() {
        check_ajax_referer('foodwise_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error(array('message' => 'Permessi insufficienti.'));
        
        $options_raw = isset($_POST['options']) ? sanitize_textarea_field($_POST['options']) : '';
        $options_array = array_filter(array_map('trim', explode("\n", $options_raw)));
        
        $data = array(
            'id' => isset($_POST['id']) ? intval($_POST['id']) : 0,
            'section_title' => isset($_POST['section_title']) ? sanitize_text_field($_POST['section_title']) : '',
            'section_text' => isset($_POST['section_text']) ? sanitize_textarea_field($_POST['section_text']) : '',
            'subsection_text' => isset($_POST['subsection_text']) ? sanitize_textarea_field($_POST['subsection_text']) : '',
            'question_text' => isset($_POST['question_text']) ? sanitize_textarea_field($_POST['question_text']) : '',
            'question_type' => isset($_POST['question_type']) ? sanitize_text_field($_POST['question_type']) : 'multiple',
            'options' => json_encode(array_values($options_array)),
            'order_position' => isset($_POST['order_position']) ? intval($_POST['order_position']) : 0,
            'is_active' => isset($_POST['is_active']) ? intval($_POST['is_active']) : 1
        );
        
        $result = FoodWise_Quiz::save_quiz_2_question($data);
        if ($result) wp_send_json_success(array('message' => 'Domanda salvata.'));
        else wp_send_json_error(array('message' => 'Errore durante il salvataggio.'));
    }
    
    /**
     * AJAX: Elimina Domanda Quiz
     */
    public function ajax_delete_quiz_question() {
        check_ajax_referer('foodwise_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error(array('message' => 'Permessi insufficienti.'));
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $quiz_type = isset($_POST['quiz_type']) ? sanitize_text_field($_POST['quiz_type']) : 'quiz_1';
        
        $result = FoodWise_Quiz::delete_quiz_question($id, $quiz_type);
        if ($result) wp_send_json_success(array('message' => 'Domanda eliminata.'));
        else wp_send_json_error(array('message' => 'Errore durante l\'eliminazione.'));
    }
    
    /**
     * AJAX: Upload CSV
     */
    public function ajax_upload_csv() {
        check_ajax_referer('foodwise_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permessi insufficienti.'));
        }
        
        if (!isset($_FILES['csv_file'])) {
            wp_send_json_error(array('message' => 'Nessun file caricato.'));
        }
        
        $file = $_FILES['csv_file'];
        $access_type = isset($_POST['access_type']) ? sanitize_text_field($_POST['access_type']) : 'quiz';
        
        // Verifica tipo file
        $file_type = wp_check_filetype($file['name']);
        if ($file_type['ext'] !== 'csv') {
            wp_send_json_error(array('message' => 'Il file deve essere in formato CSV.'));
        }
        
        // Parse CSV
        $csv_result = FoodWise_CSV_Import::parse_csv($file['tmp_name']);
        
        if (!$csv_result['success']) {
            wp_send_json_error(array('message' => $csv_result['message']));
        }
        
        // Import utenti
        $import_result = FoodWise_CSV_Import::import_users($csv_result, $access_type);
        
        if ($import_result['success']) {
            // Log upload
            FoodWise_CSV_Import::log_csv_upload($file['name'], $import_result['imported'], 'users');
            
            wp_send_json_success(array(
                'message' => "Importati {$import_result['imported']} utenti su {$import_result['total']} totali.",
                'imported' => $import_result['imported'],
                'total' => $import_result['total'],
                'errors' => $import_result['errors']
            ));
        } else {
            wp_send_json_error(array('message' => $import_result['message']));
        }
    }
    
    /**
     * AJAX: Toggle Quiz
     */
    public function ajax_toggle_quiz() {
        check_ajax_referer('foodwise_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permessi insufficienti.'));
        }
        
        $quiz_type = isset($_POST['quiz_type']) ? sanitize_text_field($_POST['quiz_type']) : '';
        $is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 0;
        
        $result = FoodWise_Database::update_quiz_status($quiz_type, $is_active);
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Stato quiz aggiornato.'));
        } else {
            wp_send_json_error(array('message' => 'Errore durante l\'aggiornamento.'));
        }
    }
    
    /**
     * AJAX: Salva Categoria
     */
    public function ajax_save_category() {
        check_ajax_referer('foodwise_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permessi insufficienti.'));
        }
        
        $category_data = array(
            'id' => isset($_POST['id']) ? intval($_POST['id']) : 0,
            'category_code' => isset($_POST['category_code']) ? sanitize_text_field($_POST['category_code']) : '',
            'category_name' => isset($_POST['category_name']) ? sanitize_text_field($_POST['category_name']) : '',
            'blinding_code' => isset($_POST['blinding_code']) ? sanitize_text_field($_POST['blinding_code']) : '',
            'simple_type' => isset($_POST['simple_type']) ? sanitize_text_field($_POST['simple_type']) : '',
            'is_selected' => isset($_POST['is_selected']) ? intval($_POST['is_selected']) : 0
        );
        
        $result = FoodWise_Categories::save_category($category_data);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * AJAX: Elimina Categoria
     */
    public function ajax_delete_category() {
        check_ajax_referer('foodwise_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permessi insufficienti.'));
        }
        
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        
        $result = FoodWise_Categories::delete_category($category_id);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * AJAX: Genera Codici
     */
    public function ajax_generate_codes() {
        check_ajax_referer('foodwise_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permessi insufficienti.'));
        }
        
        $count = isset($_POST['count']) ? intval($_POST['count']) : 10;
        $access_type = isset($_POST['access_type']) ? sanitize_text_field($_POST['access_type']) : 'quiz';
        $prefix = isset($_POST['prefix']) ? sanitize_text_field($_POST['prefix']) : '';
        
        $codes = FoodWise_Auth::generate_codes_batch($count, $access_type, $prefix);
        
        wp_send_json_success(array(
            'message' => "Generati {$count} codici.",
            'codes' => $codes
        ));
    }
    
    /**
     * AJAX: Export Utenti
     */
    public function ajax_export_users() {
        check_ajax_referer('foodwise_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permessi insufficienti.'));
        }
        
        $filters = array();
        if (isset($_POST['access_type'])) {
            $filters['access_type'] = sanitize_text_field($_POST['access_type']);
        }
        
        $result = FoodWise_CSV_Import::export_users_csv($filters);
        
        if ($result['success']) {
            $file_path = FoodWise_CSV_Import::generate_csv_file($result['data'], $result['filename']);
            
            if ($file_path) {
                wp_send_json_success(array(
                    'message' => 'Export completato.',
                    'download_url' => wp_upload_dir()['url'] . '/' . $result['filename']
                ));
            }
        }
        
        wp_send_json_error(array('message' => 'Errore durante l\'export.'));
    }
    
    /**
     * AJAX: Export Submissions
     */
    public function ajax_export_submissions() {
        check_ajax_referer('foodwise_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permessi insufficienti.'));
        }
        
        $quiz_type = isset($_POST['quiz_type']) ? sanitize_text_field($_POST['quiz_type']) : null;
        
        $submissions = FoodWise_Quiz::get_all_submissions_data($quiz_type);
        
        if (empty($submissions)) {
            wp_send_json_error(array('message' => 'Nessuna submission da esportare.'));
        }
        
        $csv_data = array();
        
        if ($quiz_type === 'quiz_1') {
            // Export Quiz 1: panellist code - nome categoria - scarto
            $csv_data[] = array('panellist code', 'nome categoria', 'scarto');
            
            foreach ($submissions as $submission) {
                if (isset($submission->submission_data) && is_array($submission->submission_data)) {
                    foreach ($submission->submission_data as $cat_id => $value) {
                        $category = FoodWise_Database::get_category_by_id($cat_id);
                        $cat_name = $category ? $category->category_name : "Cat_{$cat_id}";
                        $csv_data[] = array(
                            $submission->panelist_code,
                            $cat_name,
                            $value
                        );
                    }
                }
            }
        } elseif ($quiz_type === 'quiz_2') {
            // Export Quiz 2: panellist code, quiz type, Submitted at, question_1, question_2...
            $headers = array('panellist code', 'quiz type', 'Submitted at');
            
            // Ottieni tutte le domande del Quiz 2 per gli headers e per mappare le posizioni
            $questions = FoodWise_Database::get_quiz_2_questions(false); // Prendi tutte, anche inattive se hanno risposte
            $question_map = array();
            foreach ($questions as $q) {
                $headers[] = "question_{$q->id}";
                $question_map[$q->id] = $q;
            }
            
            $csv_data[] = $headers;
            
            foreach ($submissions as $submission) {
                $row = array(
                    $submission->panelist_code,
                    'quiz 2',
                    $submission->submitted_at
                );
                
                if (isset($submission->submission_data) && is_array($submission->submission_data)) {
                    foreach ($questions as $q) {
                        $answer = isset($submission->submission_data[$q->id]) ? $submission->submission_data[$q->id] : '';
                        
                        // Se la domanda è a risposta multipla, esporta la posizione (1-based)
                        if ($q->question_type === 'multiple' && !empty($answer)) {
                            $options = json_decode($q->options, true);
                            if (is_array($options)) {
                                $pos = array_search($answer, $options);
                                if ($pos !== false) {
                                    $answer = $pos + 1; // Posizione 1-based
                                }
                            }
                        }
                        
                        $row[] = $answer;
                    }
                }
                
                $csv_data[] = $row;
            }
        } else {
            // Export generico se non specificato
            $csv_data[] = array('ID', 'Panelist Code', 'Quiz Type', 'Submitted At', 'Data');
            foreach ($submissions as $submission) {
                $csv_data[] = array(
                    $submission->id,
                    $submission->panelist_code,
                    $submission->quiz_type,
                    $submission->submitted_at,
                    json_encode($submission->submission_data)
                );
            }
        }
        
        $filename = 'foodwise_export_' . ($quiz_type ? $quiz_type . '_' : '') . date('Y-m-d_H-i-s') . '.csv';
        $file_path = FoodWise_CSV_Import::generate_csv_file($csv_data, $filename);
        
        if ($file_path) {
            wp_send_json_success(array(
                'message' => 'Export completato.',
                'download_url' => wp_upload_dir()['url'] . '/' . $filename
            ));
        }
        
        wp_send_json_error(array('message' => 'Errore durante l\'export.'));
    }
}
