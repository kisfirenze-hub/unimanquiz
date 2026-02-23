<?php
/**
 * Gestione database e query
 *
 * @package    FoodWise
 * @subpackage FoodWise/includes
 */

class FoodWise_Database {
    
    /**
     * Ottiene i nomi delle tabelle
     */
    public static function get_table_names() {
        global $wpdb;
        return array(
            'users' => $wpdb->prefix . 'foodwise_users',
            'categories' => $wpdb->prefix . 'foodwise_categories',
            'quiz_types' => $wpdb->prefix . 'foodwise_quiz_types',
            'quiz_1_questions' => $wpdb->prefix . 'foodwise_quiz_1_questions',
            'quiz_2_questions' => $wpdb->prefix . 'foodwise_quiz_2_questions',
            'submissions' => $wpdb->prefix . 'foodwise_submissions',
            'csv_uploads' => $wpdb->prefix . 'foodwise_csv_uploads',
            'quiz_progress' => $wpdb->prefix . 'foodwise_quiz_progress'
        );
    }
    
    /**
     * Ottiene un utente per codice
     */
    public static function get_user_by_code($panelist_code) {
        global $wpdb;
        $tables = self::get_table_names();
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$tables['users']} WHERE panelist_code = %s",
            $panelist_code
        ));
    }
    
    /**
     * Ottiene un utente per ID
     */
    public static function get_user_by_id($user_id) {
        global $wpdb;
        $tables = self::get_table_names();
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$tables['users']} WHERE id = %d",
            $user_id
        ));
    }
    
    /**
     * Inserisce o aggiorna un utente
     */
    public static function upsert_user($data) {
        global $wpdb;
        $tables = self::get_table_names();
        
        // Verifica se l'utente esiste già
        $existing = self::get_user_by_code($data['panelist_code']);
        
        if ($existing) {
            // Update
            $wpdb->update(
                $tables['users'],
                $data,
                array('panelist_code' => $data['panelist_code']),
                array('%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%d', '%d', '%d', '%s'),
                array('%s')
            );
            return $existing->id;
        } else {
            // Insert
            $wpdb->insert(
                $tables['users'],
                $data,
                array('%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%d', '%d', '%d', '%s')
            );
            return $wpdb->insert_id;
        }
    }
    
    /**
     * Ottiene tutte le categorie
     */
    public static function get_categories($active_only = false) {
        global $wpdb;
        $tables = self::get_table_names();
        
        $sql = "SELECT * FROM {$tables['categories']} ORDER BY category_name ASC";
        
        $results = $wpdb->get_results($sql);
        return is_array($results) ? $results : array();
    }
    
    /**
     * Ottiene una categoria per ID
     */
    public static function get_category_by_id($category_id) {
        global $wpdb;
        $tables = self::get_table_names();
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$tables['categories']} WHERE id = %d",
            $category_id
        ));
    }
    
    /**
     * Inserisce o aggiorna una categoria
     */
    public static function upsert_category($data) {
        global $wpdb;
        $tables = self::get_table_names();
        
        // Verifica se la categoria esiste già per codice o ID
        $existing = null;
        if (isset($data['id']) && $data['id'] > 0) {
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$tables['categories']} WHERE id = %d",
                $data['id']
            ));
        } else {
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$tables['categories']} WHERE category_code = %s",
                $data['category_code']
            ));
        }
        
        // Prepara i formati per wpdb
        $formats = array('%s', '%s', '%s', '%s', '%d');
        
        if ($existing) {
            // Update
            $id = $existing->id;
            unset($data['id']);
            $wpdb->update(
                $tables['categories'],
                $data,
                array('id' => $id),
                $formats,
                array('%d')
            );
            return $id;
        } else {
            // Insert
            unset($data['id']);
            $wpdb->insert(
                $tables['categories'],
                $data,
                $formats
            );
            return $wpdb->insert_id;
        }
    }
    
    /**
     * Elimina una categoria
     */
    public static function delete_category($category_id) {
        global $wpdb;
        $tables = self::get_table_names();
        
        return $wpdb->delete(
            $tables['categories'],
            array('id' => $category_id),
            array('%d')
        );
    }
    
    /**
     * Ottiene le domande del Quiz 1
     */
    public static function get_quiz_1_questions($active_only = true) {
        global $wpdb;
        $tables = self::get_table_names();
        
        $sql = "SELECT q.*, c.category_name, c.category_code 
                FROM {$tables['quiz_1_questions']} q
                LEFT JOIN {$tables['categories']} c ON q.category_id = c.id";
        
        if ($active_only) {
            $sql .= " WHERE q.is_active = 1";
        }
        
        $sql .= " ORDER BY q.order_position ASC, c.category_name ASC";
        
        $results = $wpdb->get_results($sql);
        return is_array($results) ? $results : array();
    }
    
    /**
     * Ottiene le domande del Quiz 2
     */
    public static function get_quiz_2_questions($active_only = true) {
        global $wpdb;
        $tables = self::get_table_names();
        
        $sql = "SELECT id, section_title, section_text, subsection_text, question_text, question_type, options, order_position, is_active, is_selected FROM {$tables["quiz_2_questions"]}";
        
        if ($active_only) {
            $sql .= " WHERE is_active = 1";
        }
        
        $sql .= " ORDER BY order_position ASC";
        
        $results = $wpdb->get_results($sql);
        return is_array($results) ? $results : array();
    }
    
    /**
     * Salva una submission
     */
    public static function save_submission($user_id, $quiz_type, $submission_data) {
        global $wpdb;
        $tables = self::get_table_names();
        
        $data = array(
            'user_id' => $user_id,
            'quiz_type' => $quiz_type,
            'submission_data' => json_encode($submission_data),
            'ip_address' => self::get_client_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''
        );
        
        $wpdb->insert(
            $tables['submissions'],
            $data,
            array('%d', '%s', '%s', '%s', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Ottiene le submissions di un utente
     */
    public static function get_user_submissions($user_id, $quiz_type = null) {
        global $wpdb;
        $tables = self::get_table_names();
        
        $sql = "SELECT * FROM {$tables['submissions']} WHERE user_id = %d";
        
        if ($quiz_type) {
            $sql .= $wpdb->prepare(" AND quiz_type = %s", $quiz_type);
            return $wpdb->get_results($wpdb->prepare($sql, $user_id));
        }
        
        return $wpdb->get_results($wpdb->prepare($sql, $user_id));
    }
    
    /**
     * Verifica se un utente ha già completato un quiz
     */
    public static function has_completed_quiz($user_id, $quiz_type) {
        global $wpdb;
        $tables = self::get_table_names();
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$tables['submissions']} WHERE user_id = %d AND quiz_type = %s",
            $user_id,
            $quiz_type
        ));
        
        return $count > 0;
    }
    
    /**
     * Ottiene statistiche compliance
     */
    public static function get_compliance_stats() {
        global $wpdb;
        $tables = self::get_table_names();
        
        // Totale utenti con accesso quiz
        $total_quiz_users = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$tables['users']} WHERE access_type = 'quiz'"
        );
        
        // Utenti che hanno completato quiz 1
        $completed_quiz_1 = $wpdb->get_var(
            "SELECT COUNT(DISTINCT user_id) FROM {$tables['submissions']} WHERE quiz_type = 'quiz_1'"
        );
        
        // Utenti che hanno completato quiz 2
        $completed_quiz_2 = $wpdb->get_var(
            "SELECT COUNT(DISTINCT user_id) FROM {$tables['submissions']} WHERE quiz_type = 'quiz_2'"
        );
        
        return array(
            'total_quiz_users' => $total_quiz_users,
            'completed_quiz_1' => $completed_quiz_1,
            'completed_quiz_2' => $completed_quiz_2,
            'pending_quiz_1' => $total_quiz_users - $completed_quiz_1,
            'pending_quiz_2' => $total_quiz_users - $completed_quiz_2
        );
    }
    
    /**
     * Ottiene tutti gli utenti con filtri
     */
    public static function get_users($filters = array()) {
        global $wpdb;
        $tables = self::get_table_names();
        
        $sql = "SELECT * FROM {$tables['users']} WHERE 1=1";
        
        if (isset($filters['access_type'])) {
            $sql .= $wpdb->prepare(" AND access_type = %s", $filters['access_type']);
        }
        
        if (isset($filters['search'])) {
            $search = '%' . $wpdb->esc_like($filters['search']) . '%';
            $sql .= $wpdb->prepare(" AND (panelist_code LIKE %s OR test_name LIKE %s)", $search, $search);
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if (isset($filters['limit'])) {
            $sql .= $wpdb->prepare(" LIMIT %d", $filters['limit']);
        }
        
        $results = $wpdb->get_results($sql);
        return is_array($results) ? $results : array();
    }
    
    /**
     * Ottiene l'IP del client
     */
    private static function get_client_ip() {
        $ip = '';
        
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field($ip);
    }
    
    /**
     * Ottiene lo stato dei quiz (attivi/disattivi)
     */
    public static function get_quiz_types() {
        global $wpdb;
        $tables = self::get_table_names();
        
        return $wpdb->get_results("SELECT * FROM {$tables['quiz_types']}");
    }
    
    /**
     * Salva il progresso di un quiz
     */
    public static function save_quiz_progress($user_id, $quiz_type, $current_step, $progress_data, $question_order = null) {
        global $wpdb;
        $tables = self::get_table_names();
        
        $data = array(
            'user_id' => $user_id,
            'quiz_type' => $quiz_type,
            'current_step' => $current_step,
            'progress_data' => json_encode($progress_data),
            'updated_at' => current_time('mysql')
        );
        
        if ($question_order !== null) {
            $data['question_order'] = json_encode($question_order);
        }
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$tables['quiz_progress']} WHERE user_id = %d AND quiz_type = %s",
            $user_id, $quiz_type
        ));
        
        if ($existing) {
            return $wpdb->update($tables['quiz_progress'], $data, array('id' => $existing));
        } else {
            return $wpdb->insert($tables['quiz_progress'], $data);
        }
    }

    /**
     * Ottiene il progresso di un quiz
     */
    public static function get_quiz_progress($user_id, $quiz_type) {
        global $wpdb;
        $tables = self::get_table_names();
        
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$tables['quiz_progress']} WHERE user_id = %d AND quiz_type = %s",
            $user_id, $quiz_type
        ));
        
        if ($row) {
            $row->progress_data = json_decode($row->progress_data, true);
            $row->question_order = json_decode($row->question_order, true);
        }
        
        return $row;
    }

    /**
     * Elimina il progresso di un quiz (al completamento)
     */
    public static function delete_quiz_progress($user_id, $quiz_type) {
        global $wpdb;
        $tables = self::get_table_names();
        return $wpdb->delete($tables['quiz_progress'], array('user_id' => $user_id, 'quiz_type' => $quiz_type));
    }

    /**
     * Popola le categorie iniziali
     */
    public static function seed_categories() {
        $categories = array(
            array('1', 'Verdura e ortaggi freschi', '720', 'Sample'),
            array('2', 'Verdura e ortaggi surgelati', '125', 'Sample'),
            array('3', 'Carne', '807', 'Sample'),
            array('4', 'Pesce fresco', '509', 'Sample'),
            array('5', 'Pesce surgelato', '643', 'Sample'),
            array('6', 'Uova', '203', 'Sample'),
            array('7', 'Formaggi e latticini', '448', 'Sample'),
            array('8', 'Salumi e affettati', '419', 'Sample'),
            array('9', 'Legumi (piselli, fagioli, ceci, lenticchie...)', '505', 'Sample'),
            array('10', 'Pane e prodotti da forno freschi', '584', 'Sample'),
            array('11', 'Piatti pronti', '417', 'Sample'),
            array('12', 'Pasta, riso e cereali', '771', 'Sample'),
            array('13', 'Avanzi del pranzo/cena', '285', 'Sample'),
            array('14', 'Pane e prodotti da forno confezionati', '223', 'Sample')
        );

        foreach ($categories as $cat) {
            self::upsert_category(array(
                'category_code' => $cat[0],
                'category_name' => $cat[1],
                'blinding_code' => $cat[2],
                'simple_type' => $cat[3]
            ));
        }
    }

    /**
     * Aggiorna lo stato di un quiz
     */
    public static function update_quiz_status($quiz_type, $is_active) {
        global $wpdb;
        $tables = self::get_table_names();
        
        return $wpdb->update(
            $tables['quiz_types'],
            array('is_active' => $is_active),
            array('quiz_type' => $quiz_type),
            array('%d'),
            array('%s')
        );
    }
}
