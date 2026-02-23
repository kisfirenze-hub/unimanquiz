<?php
/**
 * Pannello Admin - Gestione Categorie v3.0
 * Sistema semplificato e robusto
 */
if (!defined('ABSPATH')) exit;

// Gestione salvataggio selezione
if (isset($_POST['foodwise_save_selection_nonce']) && wp_verify_nonce($_POST['foodwise_save_selection_nonce'], 'foodwise_save_selection')) {
    $selected = isset($_POST['selected_categories']) ? array_map('intval', $_POST['selected_categories']) : array();
    FoodWise_Categories::save_selection($selected);
    echo '<div class="notice notice-success is-dismissible"><p><strong>Selezione salvata con successo!</strong> Le categorie selezionate appariranno nel Quiz 1.</p></div>';
}

// Gestione modifica categoria
if (isset($_POST['foodwise_update_category_nonce']) && wp_verify_nonce($_POST['foodwise_update_category_nonce'], 'foodwise_update_category')) {
    $cat_id = intval($_POST['cat_id']);
    $update_data = array(
        'category_name' => sanitize_text_field($_POST['category_name']),
        'blinding_code' => sanitize_text_field($_POST['blinding_code']),
        'simple_type' => sanitize_text_field($_POST['simple_type'])
    );
    FoodWise_Categories::update($cat_id, $update_data);
    echo '<div class="notice notice-success is-dismissible"><p><strong>Categoria aggiornata!</strong></p></div>';
}

$categories = FoodWise_Categories::get_all();
$selected_ids = FoodWise_Categories::get_selected_ids();
?>

<div class="wrap">
    <h1>🍽 Gestione Categorie FoodWise</h1>
    <p class="description">Seleziona le categorie da includere nel Quiz 1 e gestisci i dati anagrafici.</p>
    
    <!-- SEZIONE 1: SELEZIONE CATEGORIE PER QUIZ 1 -->
    <div class="card" style="max-width: none; margin-top: 20px;">
        <h2 style="margin-top: 0;">1️⃣ Selezione Categorie per Quiz 1</h2>
        <p>Spunta le categorie che vuoi far apparire nel Quiz 1. Le domande verranno generate automaticamente e mostrate in ordine casuale agli utenti.</p>
        
        <form method="post" style="margin-top: 20px;">
            <?php wp_nonce_field('foodwise_save_selection', 'foodwise_save_selection_nonce'); ?>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 12px; margin: 20px 0;">
                <?php foreach ($categories as $cat): ?>
                    <label style="display: flex; align-items: center; padding: 12px; background: <?php echo in_array($cat->id, $selected_ids) ? '#e7f5e7' : '#f9f9f9'; ?>; border: 2px solid <?php echo in_array($cat->id, $selected_ids) ? '#4caf50' : '#ddd'; ?>; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                        <input type="checkbox" 
                               name="selected_categories[]" 
                               value="<?php echo $cat->id; ?>" 
                               <?php checked(in_array($cat->id, $selected_ids)); ?>
                               style="margin-right: 10px; width: 18px; height: 18px;">
                        <div>
                            <strong style="font-size: 15px; color: #333;"><?php echo esc_html($cat->category_code); ?>. <?php echo esc_html($cat->category_name); ?></strong>
                            <?php if ($cat->category_code == '13'): ?>
                                <span style="display: block; font-size: 12px; color: #ff9800; margin-top: 4px;">⚠️ Layout speciale (Avanzi)</span>
                            <?php endif; ?>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
            
            <p>
                <button type="submit" class="button button-primary button-large">
                    💾 Salva Selezione Quiz 1
                </button>
                <span style="margin-left: 15px; color: #666;">
                    <strong><?php echo count($selected_ids); ?></strong> categorie selezionate
                </span>
            </p>
        </form>
    </div>
    
    <!-- SEZIONE 2: ANAGRAFICA CATEGORIE -->
    <div class="card" style="max-width: none; margin-top: 30px;">
        <h2 style="margin-top: 0;">2️⃣ Anagrafica Categorie</h2>
        <p>Modifica i dati delle categorie (nome, blinding code, simple type). Il codice categoria non può essere modificato.</p>
        
        <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th style="width: 80px;"><strong>Codice</strong></th>
                    <th><strong>Nome Categoria</strong></th>
                    <th style="width: 120px;"><strong>Blinding Code</strong></th>
                    <th style="width: 120px;"><strong>Simple Type</strong></th>
                    <th style="width: 100px; text-align: center;"><strong>Azioni</strong></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><strong><?php echo esc_html($cat->category_code); ?></strong></td>
                    <td><?php echo esc_html($cat->category_name); ?></td>
                    <td><?php echo esc_html($cat->blinding_code); ?></td>
                    <td><?php echo esc_html($cat->simple_type); ?></td>
                    <td style="text-align: center;">
                        <button type="button" 
                                class="button button-small edit-cat-btn" 
                                data-id="<?php echo $cat->id; ?>"
                                data-code="<?php echo esc_attr($cat->category_code); ?>"
                                data-name="<?php echo esc_attr($cat->category_name); ?>"
                                data-blinding="<?php echo esc_attr($cat->blinding_code); ?>"
                                data-simple="<?php echo esc_attr($cat->simple_type); ?>">
                            ✏️ Modifica
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL MODIFICA CATEGORIA -->
<div id="editCategoryModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:100000; align-items:center; justify-content:center;">
    <div style="background:#fff; width:500px; max-width:90%; padding:30px; border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,0.3);">
        <h2 style="margin-top:0;">✏️ Modifica Categoria</h2>
        <form method="post" id="editCategoryForm">
            <?php wp_nonce_field('foodwise_update_category', 'foodwise_update_category_nonce'); ?>
            <input type="hidden" name="cat_id" id="edit_cat_id">
            
            <p>
                <label><strong>Codice Categoria:</strong></label><br>
                <input type="text" id="edit_cat_code" readonly style="width:100%; padding:8px; background:#f5f5f5; border:1px solid #ddd; border-radius:4px;">
            </p>
            
            <p>
                <label><strong>Nome Categoria:</strong></label><br>
                <input type="text" name="category_name" id="edit_cat_name" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
            </p>
            
            <p>
                <label><strong>Blinding Code:</strong></label><br>
                <input type="text" name="blinding_code" id="edit_cat_blinding" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
            </p>
            
            <p>
                <label><strong>Simple Type:</strong></label><br>
                <input type="text" name="simple_type" id="edit_cat_simple" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
            </p>
            
            <p style="text-align:right; margin-bottom:0;">
                <button type="button" onclick="closeEditModal()" class="button">Annulla</button>
                <button type="submit" class="button button-primary">💾 Salva Modifiche</button>
            </p>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.edit-cat-btn').on('click', function() {
        var data = $(this).data();
        $('#edit_cat_id').val(data.id);
        $('#edit_cat_code').val(data.code);
        $('#edit_cat_name').val(data.name);
        $('#edit_cat_blinding').val(data.blinding);
        $('#edit_cat_simple').val(data.simple);
        $('#editCategoryModal').css('display', 'flex');
    });
});

function closeEditModal() {
    document.getElementById('editCategoryModal').style.display = 'none';
}
</script>

<style>
.card { background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
label:has(input[type="checkbox"]):hover { background: #f0f0f0 !important; }
</style>
