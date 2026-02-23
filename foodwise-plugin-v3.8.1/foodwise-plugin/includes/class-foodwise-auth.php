<?php
/**
 * Gestione autenticazione tramite codici
 *
 * @package    FoodWise
 * @subpackage FoodWise/includes
 */

class FoodWise_Auth {
    
    /**
     * Nome della sessione
     */
    const SESSION_NAME = 'foodwise_user_session';
    
    /**
     * Inizializza la sessione
     */
    public static function init() {
        if (!session_id() && !headers_sent()) {
            session_start();
        }
    }
    
    /**
     * Valida un codice di accesso
     */
    public static function validate_access_code($code) {
        $code = sanitize_text_field($code);
        
        if (empty($code)) {
            return array(
                'success' => false,
                'message' => 'Inserire un codice di accesso valido.'
            );
        }
        
        // Cerca l'utente nel database
        $user = FoodWise_Database::get_user_by_code($code);
        
        if (!$user) {
            return array(
                'success' => false,
                'message' => 'Codice di accesso non valido.'
            );
        }
        
        return array(
            'success' => true,
            'user' => $user,
            'message' => 'Accesso effettuato con successo.'
        );
    }
    
    /**
     * Crea una sessione per l'utente
     */
    public static function create_session($user_id, $access_type) {
        self::init();
        
        $_SESSION[self::SESSION_NAME] = array(
            'user_id' => $user_id,
            'access_type' => $access_type,
            'login_time' => time()
        );
        
        // Imposta anche un cookie per persistenza
        setcookie(
            self::SESSION_NAME,
            base64_encode(json_encode(array(
                'user_id' => $user_id,
                'access_type' => $access_type
            ))),
            time() + (86400 * 30), // 30 giorni
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
        
        return true;
    }
    
    /**
     * Verifica se c'è una sessione attiva
     */
    public static function check_session() {
        self::init();
        
        // Verifica sessione PHP
        if (isset($_SESSION[self::SESSION_NAME])) {
            return $_SESSION[self::SESSION_NAME];
        }
        
        // Verifica cookie
        if (isset($_COOKIE[self::SESSION_NAME])) {
            $cookie_data = json_decode(base64_decode($_COOKIE[self::SESSION_NAME]), true);
            if ($cookie_data && isset($cookie_data['user_id'])) {
                // Ripristina la sessione
                $_SESSION[self::SESSION_NAME] = array(
                    'user_id' => $cookie_data['user_id'],
                    'access_type' => $cookie_data['access_type'],
                    'login_time' => time()
                );
                return $_SESSION[self::SESSION_NAME];
            }
        }
        
        return false;
    }
    
    /**
     * Ottiene l'utente corrente
     */
    public static function get_current_user() {
        $session = self::check_session();
        
        if (!$session) {
            return false;
        }
        
        return FoodWise_Database::get_user_by_id($session['user_id']);
    }
    
    /**
     * Ottiene l'ID dell'utente corrente
     */
    public static function get_current_user_id() {
        $session = self::check_session();
        
        if (!$session) {
            return false;
        }
        
        return $session['user_id'];
    }
    
    /**
     * Ottiene il tipo di accesso corrente
     */
    public static function get_access_type() {
        $session = self::check_session();
        
        if (!$session) {
            return false;
        }
        
        return $session['access_type'];
    }
    
    /**
     * Verifica se l'utente è loggato
     */
    public static function is_logged_in() {
        return self::check_session() !== false;
    }
    
    /**
     * Verifica se l'utente ha accesso quiz
     */
    public static function has_quiz_access() {
        return self::get_access_type() === 'quiz';
    }
    
    /**
     * Verifica se l'utente ha accesso viewer
     */
    public static function has_viewer_access() {
        return self::get_access_type() === 'viewer';
    }
    
    /**
     * Logout
     */
    public static function logout() {
        self::init();
        
        // Rimuove la sessione
        if (isset($_SESSION[self::SESSION_NAME])) {
            unset($_SESSION[self::SESSION_NAME]);
        }
        
        // Rimuove il cookie
        setcookie(
            self::SESSION_NAME,
            '',
            time() - 3600,
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
        
        return true;
    }
    
    /**
     * Genera un codice univoco
     */
    public static function generate_unique_code($prefix = '', $length = 8) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = $prefix;
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        // Verifica che il codice non esista già
        $existing = FoodWise_Database::get_user_by_code($code);
        
        if ($existing) {
            // Riprova con un nuovo codice
            return self::generate_unique_code($prefix, $length);
        }
        
        return $code;
    }
    
    /**
     * Genera codici in batch
     */
    public static function generate_codes_batch($count, $access_type, $prefix = '') {
        $codes = array();
        
        for ($i = 0; $i < $count; $i++) {
            $code = self::generate_unique_code($prefix);
            
            // Crea l'utente nel database
            $user_data = array(
                'panelist_code' => $code,
                'access_type' => $access_type
            );
            
            $user_id = FoodWise_Database::upsert_user($user_data);
            
            $codes[] = array(
                'code' => $code,
                'user_id' => $user_id,
                'access_type' => $access_type
            );
        }
        
        return $codes;
    }
    
    /**
     * Redirect alla pagina appropriata in base al tipo di accesso
     */
    public static function redirect_after_login() {
        $access_type = self::get_access_type();
        
        if ($access_type === 'quiz') {
            // Redirect alla dashboard selezione quiz
            $selection_page_id = get_option('foodwise_selection_page_id');
            if ($selection_page_id) {
                wp_redirect(get_permalink($selection_page_id));
                exit;
            }
            // Fallback al quiz 1
            $quiz1_page_id = get_option('foodwise_quiz1_page_id');
            if ($quiz1_page_id) {
                wp_redirect(get_permalink($quiz1_page_id));
                exit;
            }
        } elseif ($access_type === 'viewer') {
            // Redirect alla dashboard viewer
            $viewer_page_id = get_option('foodwise_viewer_page_id');
            if ($viewer_page_id) {
                wp_redirect(get_permalink($viewer_page_id));
                exit;
            }
        }
        
        // Fallback alla home
        wp_redirect(home_url());
        exit;
    }
}
