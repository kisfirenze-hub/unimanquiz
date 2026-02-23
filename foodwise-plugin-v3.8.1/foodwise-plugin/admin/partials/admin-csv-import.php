<?php
/**
 * Import CSV
 */
if (!defined('ABSPATH')) exit;

$upload_history = FoodWise_CSV_Import::get_upload_history(20);
?>

<div class="wrap foodwise-admin">
    <h1>Import CSV</h1>
    
    <div class="foodwise-card">
        <h2>Carica File CSV</h2>
        <p>Importa l'anagrafica utenti da file CSV. Il file deve contenere almeno il campo <strong>Panelist_Code</strong>.</p>
        
        <form id="csvUploadForm" enctype="multipart/form-data">
            <table class="form-table">
                <tr>
                    <th><label for="csv_file">File CSV *</label></th>
                    <td>
                        <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                        <p class="description">Formato: CSV con separatore virgola</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="access_type">Tipo di accesso</label></th>
                    <td>
                        <select id="access_type" name="access_type">
                            <option value="quiz">Quiz</option>
                            <option value="viewer">Viewer</option>
                        </select>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" class="button button-primary">Carica e Importa</button>
            </p>
        </form>
        
        <div id="uploadResult" style="display: none; margin-top: 20px;">
            <h3>Risultato Import</h3>
            <div id="uploadResultContent"></div>
        </div>
    </div>
    
    <div class="foodwise-card">
        <h2>Campi CSV Supportati</h2>
        <div class="csv-fields-info">
            <h3>Campi Standard</h3>
            <ul>
                <li><strong>Panelist_Code</strong> (obbligatorio): Codice utente univoco</li>
                <li><strong>Test_Name</strong>: Nome del test</li>
                <li><strong>Day, Month, Year</strong>: Data (giorno, mese, anno separati)</li>
                <li><strong>Test_Completed_Date</strong>: Data completamento test</li>
                <li><strong>Section_Name</strong>: Nome sezione</li>
                <li><strong>Section_Number</strong>: Numero sezione</li>
                <li><strong>Sample_Set_Number</strong>: Numero set campione</li>
                <li><strong>Rep_Number</strong>: Numero replica</li>
            </ul>
            
            <h3>Campi Motivazionali</h3>
            <p>Tutti i campi non standard vengono automaticamente salvati come campi motivazionali in formato JSON.</p>
        </div>
    </div>
    
    <div class="foodwise-card">
        <h2>Storico Upload (ultimi 20)</h2>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome File</th>
                    <th>Tipo</th>
                    <th>Record Importati</th>
                    <th>Caricato da</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($upload_history)): ?>
                <tr><td colspan="6" style="text-align: center;">Nessun upload trovato.</td></tr>
                <?php else: ?>
                    <?php foreach ($upload_history as $upload): ?>
                    <tr>
                        <td><?php echo esc_html($upload->id); ?></td>
                        <td><?php echo esc_html($upload->filename); ?></td>
                        <td><?php echo esc_html(ucfirst($upload->upload_type)); ?></td>
                        <td><strong><?php echo esc_html($upload->records_imported); ?></strong></td>
                        <td><?php echo esc_html(get_userdata($upload->uploaded_by)->display_name); ?></td>
                        <td><?php echo esc_html(date('d/m/Y H:i', strtotime($upload->uploaded_at))); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#csvUploadForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData();
        formData.append('action', 'foodwise_upload_csv');
        formData.append('nonce', foodwiseAdmin.nonce);
        formData.append('csv_file', $('#csv_file')[0].files[0]);
        formData.append('access_type', $('#access_type').val());
        
        $.ajax({
            url: foodwiseAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#uploadResult').show();
                
                if (response.success) {
                    var html = '<div class="notice notice-success"><p>' + response.data.message + '</p></div>';
                    
                    if (response.data.errors && response.data.errors.length > 0) {
                        html += '<h4>Errori:</h4><ul>';
                        response.data.errors.forEach(function(error) {
                            html += '<li>' + error + '</li>';
                        });
                        html += '</ul>';
                    }
                    
                    $('#uploadResultContent').html(html);
                    
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    $('#uploadResultContent').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                $('#uploadResult').show();
                $('#uploadResultContent').html('<div class="notice notice-error"><p>Errore durante l\'upload.</p></div>');
            }
        });
    });
});
</script>
