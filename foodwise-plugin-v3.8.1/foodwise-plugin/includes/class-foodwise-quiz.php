<?php
/**
 * Gestione quiz
 *
 * @package    FoodWise
 * @subpackage FoodWise/includes
 */

class FoodWise_Quiz {
    
    /**
     * Ottiene i quiz attivi disponibili per un utente
     */
    public static function get_active_quizzes($user_id) {
        $quiz_types = FoodWise_Database::get_quiz_types();
        $available_quizzes = array();
        
        foreach ($quiz_types as $quiz_type) {
            if ($quiz_type->is_active) {
                // Verifica se l'utente ha già completato questo quiz
                $completed = FoodWise_Database::has_completed_quiz($user_id, $quiz_type->quiz_type);
                
                $available_quizzes[] = array(
                    'quiz_type' => $quiz_type->quiz_type,
                    'quiz_name' => $quiz_type->quiz_name,
                    'description' => $quiz_type->description,
                    'completed' => $completed,
                    'can_take' => !$completed // L'utente può fare il quiz solo se non l'ha già completato
                );
            }
        }
        
        return $available_quizzes;
    }
    
    /**
     * Ottiene le domande di un quiz
     */
    public static function get_quiz_questions($quiz_type) {
        if ($quiz_type === 'quiz_1') {
            return self::get_quiz_1_questions();
        } elseif ($quiz_type === 'quiz_2') {
            return self::get_quiz_2_questions();
        }
        
        return array();
    }
    
    /**
     * Ottiene le domande del Quiz 1 (slider) con supporto random e selezione
     */
    public static function get_quiz_1_questions($user_id = null) {
        // Ottiene le categorie selezionate tramite il nuovo sistema wp_options
        $categories = FoodWise_Categories::get_selected_for_quiz();
        
        if (empty($categories)) return array();

        // Verifica se esiste un ordine salvato nei progressi
        $progress = $user_id ? FoodWise_Database::get_quiz_progress($user_id, 'quiz_1') : null;
        
        if ($progress && !empty($progress->question_order)) {
            // Ripristina l'ordine salvato
            $ordered_categories = array();
            $cat_map = array();
            foreach ($categories as $cat) $cat_map[$cat->id] = $cat;
            
            foreach ($progress->question_order as $cat_id) {
                if (isset($cat_map[$cat_id])) {
                    $ordered_categories[] = $cat_map[$cat_id];
                }
            }
            $categories = $ordered_categories;
        } else {
            // Ordine casuale
            shuffle($categories);
        }
        
        $formatted = array();
        foreach ($categories as $cat) {
            $formatted[] = array(
                'id' => $cat->id,
                'category_id' => $cat->id,
                'category_name' => $cat->category_name,
                'category_code' => $cat->category_code,
                'type' => 'slider',
                'is_special' => ($cat->category_code == '13'), // Categoria speciale Avanzi
                'min' => 0,
                'max' => 100,
                'step' => 0.1,
                'min_label' => 'Niente',
                'max_label' => 'Tutto'
            );
        }
        
        return $formatted;
    }
    
    /**
     * Ottiene le domande del Quiz 2 (KAP)
     */
    private static function get_quiz_2_questions() {
        $questions = FoodWise_Database::get_quiz_2_questions(true);
        
        $formatted = array();
        foreach ($questions as $question) {
            $options = !empty($question->options) ? json_decode($question->options, true) : array();
            
            $formatted[] = array(
                'id' => $question->id,
                'section_title' => $question->section_title,
                'section_text' => $question->section_text,
                'subsection_text' => $question->subsection_text,
                'question_text' => $question->question_text,
                'question_type' => $question->question_type,
                'type' => $question->question_type,
                'options' => $options
            );
        }
        
        return $formatted;
    }
    
    /**
     * Salva una submission
     */
    public static function save_submission($user_id, $quiz_type, $answers) {
        // Verifica che l'utente non abbia già completato il quiz
        if (FoodWise_Database::has_completed_quiz($user_id, $quiz_type)) {
            return array(
                'success' => false,
                'message' => 'Hai già completato questo quiz.'
            );
        }
        
        // Valida le risposte
        $validation = self::validate_answers($quiz_type, $answers);
        
        if (!$validation['valid']) {
            return array(
                'success' => false,
                'message' => 'Errori di validazione: ' . implode(', ', $validation['errors'])
            );
        }
        
        // Salva la submission
        $submission_id = FoodWise_Database::save_submission($user_id, $quiz_type, $answers);
        
        if ($submission_id) {
            return array(
                'success' => true,
                'submission_id' => $submission_id,
                'message' => 'Quiz completato con successo!'
            );
        }
        
        return array(
            'success' => false,
            'message' => 'Errore durante il salvataggio del quiz.'
        );
    }
    
    /**
     * Valida le risposte
     */
    private static function validate_answers($quiz_type, $answers) {
        $errors = array();
        
        if (empty($answers)) {
            $errors[] = 'Nessuna risposta fornita.';
            return array('valid' => false, 'errors' => $errors);
        }
        
        // Ottiene le domande del quiz
        $questions = self::get_quiz_questions($quiz_type);
        
        if (empty($questions)) {
            $errors[] = 'Quiz non trovato.';
            return array('valid' => false, 'errors' => $errors);
        }
        
        // Verifica che tutte le domande abbiano una risposta
        foreach ($questions as $question) {
            $question_id = $question['id'];
            
            if (!isset($answers[$question_id])) {
                $errors[] = "Risposta mancante per la domanda ID {$question_id}.";
                continue;
            }
            
            // Validazione specifica per tipo di quiz
            if ($quiz_type === 'quiz_1') {
                // Slider: verifica range 0-100
                // Nota: question_id nel Quiz 1 corrisponde al category_id
                $value = floatval($answers[$question_id]);
                if ($value < 0 || $value > 100) {
                    $errors[] = "Valore non valido per la categoria ID {$question_id} (deve essere tra 0 e 100).";
                }
            } elseif ($quiz_type === 'quiz_2') {
                // KAP: verifica che la risposta non sia vuota
                if (empty($answers[$question_id]) && $answers[$question_id] !== '0' && $answers[$question_id] !== 0) {
                    $errors[] = "Risposta non valida per la domanda ID {$question_id}.";
                }
            }
        }
        
        if (!empty($errors)) {
            return array('valid' => false, 'errors' => $errors);
        }
        
        return array('valid' => true, 'errors' => array());
    }
    
    /**
     * Ottiene le submissions di un utente
     */
    public static function get_user_submissions($user_id, $quiz_type = null) {
        $submissions = FoodWise_Database::get_user_submissions($user_id, $quiz_type);
        
        $formatted = array();
        foreach ($submissions as $submission) {
            $formatted[] = array(
                'id' => $submission->id,
                'quiz_type' => $submission->quiz_type,
                'submission_data' => json_decode($submission->submission_data, true),
                'submitted_at' => $submission->submitted_at,
                'ip_address' => $submission->ip_address
            );
        }
        
        return $formatted;
    }
    
    /**
     * Verifica se un quiz è completato
     */
    public static function check_quiz_completion($user_id, $quiz_type) {
        return FoodWise_Database::has_completed_quiz($user_id, $quiz_type);
    }
    
    /**
     * Ottiene statistiche compliance
     */
    public static function get_compliance_stats() {
        $stats = FoodWise_Database::get_compliance_stats();
        
        // Calcola percentuali
        if ($stats['total_quiz_users'] > 0) {
            $stats['completion_rate_quiz_1'] = round(($stats['completed_quiz_1'] / $stats['total_quiz_users']) * 100, 2);
            $stats['completion_rate_quiz_2'] = round(($stats['completed_quiz_2'] / $stats['total_quiz_users']) * 100, 2);
        } else {
            $stats['completion_rate_quiz_1'] = 0;
            $stats['completion_rate_quiz_2'] = 0;
        }
        
        return $stats;
    }
    
    /**
     * Ottiene dettagli compliance per utente
     */
    public static function get_user_compliance_details() {
        global $wpdb;
        $tables = FoodWise_Database::get_table_names();
        
        $sql = "SELECT 
                    u.id,
                    u.panelist_code,
                    u.test_name,
                    u.access_type,
                    MAX(CASE WHEN s.quiz_type = 'quiz_1' THEN 1 ELSE 0 END) as completed_quiz_1,
                    MAX(CASE WHEN s.quiz_type = 'quiz_2' THEN 1 ELSE 0 END) as completed_quiz_2,
                    MAX(CASE WHEN s.quiz_type = 'quiz_1' THEN s.submitted_at END) as quiz_1_date,
                    MAX(CASE WHEN s.quiz_type = 'quiz_2' THEN s.submitted_at END) as quiz_2_date
                FROM {$tables['users']} u
                LEFT JOIN {$tables['submissions']} s ON u.id = s.user_id
                WHERE u.access_type = 'quiz'
                GROUP BY u.id, u.panelist_code, u.test_name, u.access_type
                ORDER BY u.panelist_code ASC";
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Ottiene tutti i dati delle submissions per export/report
     */
    public static function get_all_submissions_data($quiz_type = null) {
        global $wpdb;
        $tables = FoodWise_Database::get_table_names();
        
        $sql = "SELECT 
                    s.*,
                    u.panelist_code,
                    u.test_name,
                    u.section_name
                FROM {$tables['submissions']} s
                LEFT JOIN {$tables['users']} u ON s.user_id = u.id";
        
        if ($quiz_type) {
            $sql .= $wpdb->prepare(" WHERE s.quiz_type = %s", $quiz_type);
        }
        
        $sql .= " ORDER BY s.submitted_at DESC";
        
        $results = $wpdb->get_results($sql);
        
        // Decodifica i dati JSON
        foreach ($results as &$result) {
            $result->submission_data = json_decode($result->submission_data, true);
        }
        
        return $results;
    }
    
    /**
     * Salva domanda Quiz 2
     */
    public static function save_quiz_2_question($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'foodwise_quiz_2_questions';
        
        if ($data['id'] > 0) {
            $id = $data['id'];
            unset($data['id']);
            return $wpdb->update($table, $data, array('id' => $id));
        } else {
            unset($data['id']);
            return $wpdb->insert($table, $data);
        }
    }
    
    /**
     * Elimina domanda quiz
     */
    public static function delete_quiz_question($id, $quiz_type) {
        global $wpdb;
        $table = ($quiz_type === 'quiz_1') ? $wpdb->prefix . 'foodwise_quiz_1_questions' : $wpdb->prefix . 'foodwise_quiz_2_questions';
        return $wpdb->delete($table, array('id' => $id));
    }
}
