<?php
/**
 * Gestione import/export CSV
 *
 * @package    FoodWise
 * @subpackage FoodWise/includes
 */

class FoodWise_CSV_Import {
    
    /**
     * Campi standard CSV per utenti
     */
    const USER_CSV_FIELDS = array(
        'Test_Name',
        'Day',
        'Month',
        'Year',
        'Test_Completed_Date',
        'Section_Name',
        'Section_Number',
        'Sample_Set_Number',
        'Rep_Number',
        'Panelist_Code'
    );
    
    /**
     * Parse CSV file
     */
    public static function parse_csv($file_path, $delimiter = ',') {
        if (!file_exists($file_path)) {
            return array(
                'success' => false,
                'message' => 'File non trovato.'
            );
        }
        
        $data = array();
        $headers = array();
        $row_number = 0;
        
        if (($handle = fopen($file_path, 'r')) !== false) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $row_number++;
                
                if ($row_number === 1) {
                    // Prima riga = headers
                    $headers = array_map('trim', $row);
                    continue;
                }
                
                // Combina headers con valori
                $row_data = array();
                foreach ($headers as $index => $header) {
                    $row_data[$header] = isset($row[$index]) ? trim($row[$index]) : '';
                }
                
                $data[] = $row_data;
            }
            fclose($handle);
        }
        
        return array(
            'success' => true,
            'headers' => $headers,
            'data' => $data,
            'total_rows' => count($data)
        );
    }
    
    /**
     * Valida i dati CSV
     */
    public static function validate_csv_data($csv_data) {
        $errors = array();
        
        if (!isset($csv_data['headers']) || empty($csv_data['headers'])) {
            $errors[] = 'Il file CSV non contiene headers.';
            return array('valid' => false, 'errors' => $errors);
        }
        
        // Verifica che i campi obbligatori siano presenti
        $required_fields = array('Panelist_Code');
        
        foreach ($required_fields as $field) {
            if (!in_array($field, $csv_data['headers'])) {
                $errors[] = "Campo obbligatorio mancante: {$field}";
            }
        }
        
        if (!empty($errors)) {
            return array('valid' => false, 'errors' => $errors);
        }
        
        return array('valid' => true, 'errors' => array());
    }
    
    /**
     * Import utenti da CSV
     */
    public static function import_users($csv_data, $access_type = 'quiz') {
        global $wpdb;
        
        $validation = self::validate_csv_data($csv_data);
        
        if (!$validation['valid']) {
            return array(
                'success' => false,
                'message' => 'Errori di validazione: ' . implode(', ', $validation['errors'])
            );
        }
        
        $imported = 0;
        $errors = array();
        $standard_fields = self::USER_CSV_FIELDS;
        
        foreach ($csv_data['data'] as $row_index => $row) {
            try {
                // Prepara i dati standard
                $user_data = array(
                    'panelist_code' => isset($row['Panelist_Code']) ? sanitize_text_field($row['Panelist_Code']) : '',
                    'access_type' => $access_type,
                    'test_name' => isset($row['Test_Name']) ? sanitize_text_field($row['Test_Name']) : null,
                    'day' => isset($row['Day']) ? intval($row['Day']) : null,
                    'month' => isset($row['Month']) ? intval($row['Month']) : null,
                    'year' => isset($row['Year']) ? intval($row['Year']) : null,
                    'test_completed_date' => isset($row['Test_Completed_Date']) ? sanitize_text_field($row['Test_Completed_Date']) : null,
                    'section_name' => isset($row['Section_Name']) ? sanitize_text_field($row['Section_Name']) : null,
                    'section_number' => isset($row['Section_Number']) ? intval($row['Section_Number']) : null,
                    'sample_set_number' => isset($row['Sample_Set_Number']) ? intval($row['Sample_Set_Number']) : null,
                    'rep_number' => isset($row['Rep_Number']) ? intval($row['Rep_Number']) : null
                );
                
                // Campi motivazionali (tutti i campi non standard)
                $motivational_fields = array();
                foreach ($row as $key => $value) {
                    if (!in_array($key, $standard_fields)) {
                        $motivational_fields[$key] = sanitize_text_field($value);
                    }
                }
                
                if (!empty($motivational_fields)) {
                    $user_data['motivational_fields'] = json_encode($motivational_fields);
                }
                
                // Inserisce o aggiorna l'utente
                $user_id = FoodWise_Database::upsert_user($user_data);
                
                if ($user_id) {
                    $imported++;
                }
                
            } catch (Exception $e) {
                $errors[] = "Riga " . ($row_index + 2) . ": " . $e->getMessage();
            }
        }
        
        return array(
            'success' => true,
            'imported' => $imported,
            'total' => count($csv_data['data']),
            'errors' => $errors
        );
    }
    
    /**
     * Export utenti in CSV
     */
    public static function export_users_csv($filters = array()) {
        $users = FoodWise_Database::get_users($filters);
        
        if (empty($users)) {
            return array(
                'success' => false,
                'message' => 'Nessun utente da esportare.'
            );
        }
        
        // Prepara i dati per l'export
        $csv_data = array();
        
        // Headers
        $headers = array(
            'Panelist_Code',
            'Access_Type',
            'Test_Name',
            'Day',
            'Month',
            'Year',
            'Test_Completed_Date',
            'Section_Name',
            'Section_Number',
            'Sample_Set_Number',
            'Rep_Number',
            'Created_At'
        );
        
        // Aggiungi headers per campi motivazionali se presenti
        $motivational_headers = array();
        foreach ($users as $user) {
            if (!empty($user->motivational_fields)) {
                $fields = json_decode($user->motivational_fields, true);
                if ($fields) {
                    foreach (array_keys($fields) as $key) {
                        if (!in_array($key, $motivational_headers)) {
                            $motivational_headers[] = $key;
                        }
                    }
                }
            }
        }
        
        $headers = array_merge($headers, $motivational_headers);
        $csv_data[] = $headers;
        
        // Dati
        foreach ($users as $user) {
            $row = array(
                $user->panelist_code,
                $user->access_type,
                $user->test_name,
                $user->day,
                $user->month,
                $user->year,
                $user->test_completed_date,
                $user->section_name,
                $user->section_number,
                $user->sample_set_number,
                $user->rep_number,
                $user->created_at
            );
            
            // Aggiungi campi motivazionali
            $motivational_fields = !empty($user->motivational_fields) ? json_decode($user->motivational_fields, true) : array();
            foreach ($motivational_headers as $header) {
                $row[] = isset($motivational_fields[$header]) ? $motivational_fields[$header] : '';
            }
            
            $csv_data[] = $row;
        }
        
        return array(
            'success' => true,
            'data' => $csv_data,
            'filename' => 'foodwise_users_' . date('Y-m-d_H-i-s') . '.csv'
        );
    }
    
    /**
     * Genera CSV da array
     */
    public static function generate_csv_file($data, $filename) {
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/' . $filename;
        
        $handle = fopen($file_path, 'w');
        
        if ($handle === false) {
            return false;
        }
        
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
        
        return $file_path;
    }
    
    /**
     * Log upload CSV
     */
    public static function log_csv_upload($filename, $records_imported, $upload_type = 'users') {
        global $wpdb;
        $tables = FoodWise_Database::get_table_names();
        
        $data = array(
            'filename' => $filename,
            'uploaded_by' => get_current_user_id(),
            'records_imported' => $records_imported,
            'upload_type' => $upload_type,
            'file_path' => ''
        );
        
        $wpdb->insert(
            $tables['csv_uploads'],
            $data,
            array('%s', '%d', '%d', '%s', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Ottiene lo storico degli upload
     */
    public static function get_upload_history($limit = 50) {
        global $wpdb;
        $tables = FoodWise_Database::get_table_names();
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$tables['csv_uploads']} ORDER BY uploaded_at DESC LIMIT %d",
            $limit
        ));
    }
}
