<?php
/**
 * Componenti pubblici del plugin
 *
 * @package    FoodWise
 * @subpackage FoodWise/public
 */

class FoodWise_Public {
    
    /**
     * Inizializzazione
     */
    public function init() {
        // Inizializza sessione
        FoodWise_Auth::init();
        
        // Shortcodes
        add_shortcode('foodwise_access', array($this, 'shortcode_access'));
        add_shortcode('foodwise_quiz_selection', array($this, 'shortcode_quiz_selection'));
        add_shortcode('foodwise_quiz_1', array($this, 'shortcode_quiz_1'));
        add_shortcode('foodwise_quiz_2', array($this, 'shortcode_quiz_2'));
        add_shortcode('foodwise_viewer', array($this, 'shortcode_viewer'));
        
        // Enqueue scripts e styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // AJAX handlers (anche per utenti non loggati)
        add_action('wp_ajax_foodwise_login', array($this, 'ajax_login'));
        add_action('wp_ajax_nopriv_foodwise_login', array($this, 'ajax_login'));
        add_action('wp_ajax_foodwise_submit_quiz', array($this, 'ajax_submit_quiz'));
        add_action('wp_ajax_nopriv_foodwise_submit_quiz', array($this, 'ajax_submit_quiz'));
        add_action('wp_ajax_foodwise_save_progress', array($this, 'ajax_save_progress'));
        add_action('wp_ajax_nopriv_foodwise_save_progress', array($this, 'ajax_save_progress'));
        add_action('wp_ajax_foodwise_logout', array($this, 'ajax_logout'));
        add_action('wp_ajax_nopriv_foodwise_logout', array($this, 'ajax_logout'));
    }
    
    /**
     * Enqueue styles
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'foodwise-public',
            FOODWISE_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            FOODWISE_VERSION
        );
    }
    
    /**
     * Enqueue scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        
        // noUiSlider per slider touch-friendly
        wp_enqueue_style(
            'nouislider',
            'https://cdn.jsdelivr.net/npm/nouislider@15.6.1/dist/nouislider.min.css',
            array(),
            '15.6.1'
        );
        
        wp_enqueue_script(
            'nouislider',
            'https://cdn.jsdelivr.net/npm/nouislider@15.6.1/dist/nouislider.min.js',
            array(),
            '15.6.1',
            true
        );
        
        wp_enqueue_script(
            'foodwise-quiz-slider',
            FOODWISE_PLUGIN_URL . 'assets/js/quiz-slider.js',
            array('jquery', 'nouislider'),
            FOODWISE_VERSION,
            true
        );
        
        wp_enqueue_script(
            'foodwise-quiz-kap',
            FOODWISE_PLUGIN_URL . 'assets/js/quiz-kap.js',
            array('jquery'),
            FOODWISE_VERSION,
            true
        );
        
        // Localizza script (foodwisePublic usato da quiz-slider.js, quiz-kap.js e shortcode inline)
        $public_ajax_data = array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('foodwise_public_nonce')
        );
        wp_localize_script('foodwise-quiz-slider', 'foodwisePublic', $public_ajax_data);
        wp_localize_script('foodwise-quiz-kap', 'foodwisePublic', $public_ajax_data);
    }
    
    /**
     * Shortcode: Form di accesso
     */
    public function shortcode_access($atts) {
        ob_start();
        include FOODWISE_PLUGIN_DIR . 'public/partials/access-form.php';
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Selezione Quiz
     */
    public function shortcode_quiz_selection($atts) {
        // Verifica accesso
        if (!FoodWise_Auth::is_logged_in() || !FoodWise_Auth::has_quiz_access()) {
            return '<p>Accesso non autorizzato. <a href="' . get_permalink(get_option('foodwise_access_page_id')) . '">Effettua l\'accesso</a></p>';
        }
        
        ob_start();
        include FOODWISE_PLUGIN_DIR . 'public/partials/quiz-selection.php';
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Quiz 1
     */
    public function shortcode_quiz_1($atts) {
        // Verifica accesso
        if (!FoodWise_Auth::is_logged_in() || !FoodWise_Auth::has_quiz_access()) {
            return '<p>Accesso non autorizzato. <a href="' . get_permalink(get_option('foodwise_access_page_id')) . '">Effettua l\'accesso</a></p>';
        }
        
        ob_start();
        include FOODWISE_PLUGIN_DIR . 'public/partials/quiz-1-slider.php';
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Quiz 2
     */
    public function shortcode_quiz_2($atts) {
        // Verifica accesso
        if (!FoodWise_Auth::is_logged_in() || !FoodWise_Auth::has_quiz_access()) {
            return '<p>Accesso non autorizzato. <a href="' . get_permalink(get_option('foodwise_access_page_id')) . '">Effettua l\'accesso</a></p>';
        }
        
        ob_start();
        include FOODWISE_PLUGIN_DIR . 'public/partials/quiz-2-kap.php';
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Dashboard Viewer
     */
    public function shortcode_viewer($atts) {
        // Verifica accesso
        if (!FoodWise_Auth::is_logged_in() || !FoodWise_Auth::has_viewer_access()) {
            return '<p>Accesso non autorizzato. <a href="' . get_permalink(get_option('foodwise_access_page_id')) . '">Effettua l\'accesso</a></p>';
        }
        
        ob_start();
        include FOODWISE_PLUGIN_DIR . 'public/partials/viewer-dashboard.php';
        return ob_get_clean();
    }
    
    /**
     * AJAX: Login
     */
    public function ajax_login() {
        check_ajax_referer('foodwise_public_nonce', 'nonce');
        
        $code = isset($_POST['access_code']) ? sanitize_text_field($_POST['access_code']) : '';
        
        $result = FoodWise_Auth::validate_access_code($code);
        
        if ($result['success']) {
            // Crea sessione
            FoodWise_Auth::create_session($result['user']->id, $result['user']->access_type);
            
            // Determina URL di redirect
            if ($result['user']->access_type === 'quiz') {
                $selection_page_id = get_option('foodwise_selection_page_id');
                if ($selection_page_id) {
                    $redirect_url = get_permalink($selection_page_id);
                } else {
                    $redirect_url = get_permalink(get_option('foodwise_quiz1_page_id'));
                }
            } else {
                $redirect_url = get_permalink(get_option('foodwise_viewer_page_id'));
            }
            
            wp_send_json_success(array(
                'message' => $result['message'],
                'redirect_url' => $redirect_url
            ));
        } else {
            wp_send_json_error(array('message' => $result['message']));
        }
        wp_die();
    }
    
    /**
     * AJAX: Salva Progresso Quiz
     */
    public function ajax_save_progress() {
        check_ajax_referer('foodwise_public_nonce', 'nonce');
        
        if (!FoodWise_Auth::is_logged_in()) {
            wp_send_json_error(array('message' => 'Sessione scaduta.'));
            wp_die();
        }
        
        $user_id = FoodWise_Auth::get_current_user_id();
        $quiz_type = isset($_POST['quiz_type']) ? sanitize_text_field($_POST['quiz_type']) : '';
        $current_step = isset($_POST['current_step']) ? intval($_POST['current_step']) : 0;

        // Leggi progress_data (JSON stringa) con fallback a answers (array o JSON)
        if (isset($_POST['progress_data']) && $_POST['progress_data'] !== '') {
            $answers = json_decode(stripslashes($_POST['progress_data']), true);
        } elseif (isset($_POST['answers'])) {
            $raw = $_POST['answers'];
            $answers = is_array($raw) ? array_map('sanitize_text_field', $raw) : json_decode(stripslashes($raw), true);
        } else {
            $answers = array();
        }
        if (!is_array($answers)) {
            $answers = array();
        }

        $order = isset($_POST['order']) ? $_POST['order'] : null;
        
        $result = FoodWise_Database::save_quiz_progress($user_id, $quiz_type, $current_step, $answers, $order);
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Progresso salvato.'));
        } else {
            wp_send_json_error(array('message' => 'Errore durante il salvataggio del progresso.'));
        }
        wp_die();
    }

    /**
     * AJAX: Submit Quiz
     */
    public function ajax_submit_quiz() {
        check_ajax_referer('foodwise_public_nonce', 'nonce');
        
        if (!FoodWise_Auth::is_logged_in()) {
            wp_send_json_error(array('message' => 'Sessione scaduta. Effettua nuovamente l\'accesso.'));
            wp_die();
        }
        
        $user_id = FoodWise_Auth::get_current_user_id();
        $quiz_type = isset($_POST['quiz_type']) ? sanitize_text_field($_POST['quiz_type']) : '';
        $answers = isset($_POST['answers']) ? json_decode(stripslashes($_POST['answers']), true) : array();
        
        // Sanitizza le risposte
        $sanitized_answers = array();
        if (is_array($answers)) {
            foreach ($answers as $question_id => $answer) {
                $sanitized_answers[intval($question_id)] = sanitize_text_field($answer);
            }
        }
        
        $result = FoodWise_Quiz::save_submission($user_id, $quiz_type, $sanitized_answers);
        
        if ($result['success']) {
            // Elimina il progresso al completamento
            FoodWise_Database::delete_quiz_progress($user_id, $quiz_type);

            // Aggiungi redirect_url alla risposta
            $selection_page_id = get_option('foodwise_selection_page_id');
            $result['redirect_url'] = $selection_page_id ? get_permalink($selection_page_id) : home_url('/');

            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
        wp_die();
    }
    
    /**
     * AJAX: Logout
     */
    public function ajax_logout() {
        FoodWise_Auth::logout();
        wp_send_json_success(array(
            'message' => 'Logout effettuato.',
            'redirect_url' => get_permalink(get_option('foodwise_access_page_id'))
        ));
        wp_die();
    }
}
