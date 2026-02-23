<?php
/**
 * Gestione Categorie FoodWise v3.0
 * Sistema semplificato e robusto
 *
 * @package    FoodWise
 * @subpackage FoodWise/includes
 */

class FoodWise_Categories {
    
    const OPTION_SELECTED = 'foodwise_selected_categories';
    
    /**
     * Categorie predefinite (14 standard)
     */
    public static function get_default_categories() {
        return array(
            array('code' => '1', 'name' => 'Verdura e ortaggi freschi', 'blinding' => '720', 'type' => 'Sample'),
            array('code' => '2', 'name' => 'Verdura e ortaggi surgelati', 'blinding' => '125', 'type' => 'Sample'),
            array('code' => '3', 'name' => 'Carne', 'blinding' => '807', 'type' => 'Sample'),
            array('code' => '4', 'name' => 'Pesce fresco', 'blinding' => '509', 'type' => 'Sample'),
            array('code' => '5', 'name' => 'Pesce surgelato', 'blinding' => '643', 'type' => 'Sample'),
            array('code' => '6', 'name' => 'Uova', 'blinding' => '203', 'type' => 'Sample'),
            array('code' => '7', 'name' => 'Formaggi e latticini', 'blinding' => '448', 'type' => 'Sample'),
            array('code' => '8', 'name' => 'Salumi e affettati', 'blinding' => '419', 'type' => 'Sample'),
            array('code' => '9', 'name' => 'Legumi (piselli, fagioli, ceci, lenticchie...)', 'blinding' => '505', 'type' => 'Sample'),
            array('code' => '10', 'name' => 'Pane e prodotti da forno freschi', 'blinding' => '584', 'type' => 'Sample'),
            array('code' => '11', 'name' => 'Piatti pronti', 'blinding' => '417', 'type' => 'Sample'),
            array('code' => '12', 'name' => 'Pasta, riso e cereali', 'blinding' => '771', 'type' => 'Sample'),
            array('code' => '13', 'name' => 'Avanzi del pranzo/cena', 'blinding' => '285', 'type' => 'Sample'),
            array('code' => '14', 'name' => 'Pane e prodotti da forno confezionati', 'blinding' => '223', 'type' => 'Sample')
        );
    }
    
    /**
     * Popola le categorie predefinite nel database
     */
    public static function populate_default_categories() {
        $defaults = self::get_default_categories();
        foreach ($defaults as $cat) {
            FoodWise_Database::upsert_category(array(
                'category_code' => $cat['code'],
                'category_name' => $cat['name'],
                'blinding_code' => $cat['blinding'],
                'simple_type' => $cat['type']
            ));
        }
    }
    
    /**
     * Ottiene tutte le categorie dal database
     */
    public static function get_all() {
        return FoodWise_Database::get_categories();
    }
    
    /**
     * Ottiene gli ID delle categorie selezionate per il Quiz 1
     */
    public static function get_selected_ids() {
        $selected = get_option(self::OPTION_SELECTED, array());
        return is_array($selected) ? array_map('intval', $selected) : array();
    }
    
    /**
     * Salva la selezione delle categorie per il Quiz 1
     */
    public static function save_selection($category_ids) {
        if (!is_array($category_ids)) {
            $category_ids = array();
        }
        return update_option(self::OPTION_SELECTED, array_map('intval', $category_ids));
    }
    
    /**
     * Ottiene le categorie selezionate per il Quiz 1 (oggetti completi)
     */
    public static function get_selected_for_quiz() {
        $selected_ids = self::get_selected_ids();
        if (empty($selected_ids)) {
            return array();
        }
        
        $all = self::get_all();
        $selected = array();
        
        foreach ($all as $cat) {
            if (in_array($cat->id, $selected_ids)) {
                $selected[] = $cat;
            }
        }
        
        return $selected;
    }
    
    /**
     * Aggiorna una categoria esistente
     */
    public static function update($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'foodwise_categories';
        
        $update_data = array();
        if (isset($data['category_name'])) $update_data['category_name'] = sanitize_text_field($data['category_name']);
        if (isset($data['blinding_code'])) $update_data['blinding_code'] = sanitize_text_field($data['blinding_code']);
        if (isset($data['simple_type'])) $update_data['simple_type'] = sanitize_text_field($data['simple_type']);
        
        if (empty($update_data)) {
            return false;
        }
        
        return $wpdb->update($table, $update_data, array('id' => intval($id)));
    }
}
