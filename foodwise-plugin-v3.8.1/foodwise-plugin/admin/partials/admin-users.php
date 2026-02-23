<?php
/**
 * Gestione utenti amministrazione
 *
 * @package    FoodWise
 * @subpackage FoodWise/admin/partials
 */

if (!defined('ABSPATH')) {
    exit;
}

// Filtri
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$access_type_filter = isset($_GET['access_type']) ? sanitize_text_field($_GET['access_type']) : '';

$filters = array();
if ($search) {
    $filters['search'] = $search;
}
if ($access_type_filter) {
    $filters['access_type'] = $access_type_filter;
}

$users = FoodWise_Database::get_users($filters);
$compliance_details = FoodWise_Quiz::get_user_compliance_details();
?>

<div class="wrap foodwise-admin">
    <h1>Gestione Utenti</h1>
    
    <!-- Filtri e Ricerca -->
    <div class="foodwise-card">
        <form method="get" action="">
            <input type="hidden" name="page" value="foodwise-users">
            <div class="foodwise-filters">
                <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Cerca per codice o nome test...">
                
                <select name="access_type">
                    <option value="">Tutti i tipi</option>
                    <option value="quiz" <?php selected($access_type_filter, 'quiz'); ?>>Quiz</option>
                    <option value="viewer" <?php selected($access_type_filter, 'viewer'); ?>>Viewer</option>
                </select>
                
                <button type="submit" class="button">Filtra</button>
                <a href="?page=foodwise-users" class="button">Reset</a>
            </div>
        </form>
    </div>
    
    <!-- Azioni Bulk -->
    <div class="foodwise-card">
        <h2>Azioni</h2>
        <div class="foodwise-actions">
            <button type="button" class="button" id="generateCodesBtn">Genera Codici</button>
            <button type="button" class="button" id="exportUsersBtn">Export CSV</button>
        </div>
    </div>
    
    <!-- Tabella Utenti -->
    <div class="foodwise-card">
        <h2>Elenco Utenti (<?php echo count($users); ?>)</h2>
        <div class="table-responsive">
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Panelist Code</th>
                        <th>Tipo Accesso</th>
                        <th>Test Name</th>
                        <th>Section</th>
                        <th>Data</th>
                        <th>Creato</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">Nessun utente trovato.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo esc_html($user->id); ?></td>
                            <td><strong><?php echo esc_html($user->panelist_code); ?></strong></td>
                            <td>
                                <span class="status-badge <?php echo $user->access_type; ?>">
                                    <?php echo esc_html(ucfirst($user->access_type)); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($user->test_name); ?></td>
                            <td><?php echo esc_html($user->section_name); ?></td>
                            <td>
                                <?php 
                                if ($user->day && $user->month && $user->year) {
                                    echo esc_html("{$user->day}/{$user->month}/{$user->year}");
                                }
                                ?>
                            </td>
                            <td><?php echo esc_html(date('d/m/Y H:i', strtotime($user->created_at))); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Tabella Compliance -->
    <div class="foodwise-card">
        <h2>Dettaglio Compliance Utenti</h2>
        <div class="table-responsive">
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Panelist Code</th>
                        <th>Test Name</th>
                        <th>Quiz 1</th>
                        <th>Data Quiz 1</th>
                        <th>Quiz 2</th>
                        <th>Data Quiz 2</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($compliance_details)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Nessun dato disponibile.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($compliance_details as $detail): ?>
                        <tr>
                            <td><strong><?php echo esc_html($detail->panelist_code); ?></strong></td>
                            <td><?php echo esc_html($detail->test_name); ?></td>
                            <td>
                                <?php if ($detail->completed_quiz_1): ?>
                                    <span class="status-badge active">✓ Completato</span>
                                <?php else: ?>
                                    <span class="status-badge inactive">✗ Non completato</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                if ($detail->quiz_1_date) {
                                    echo esc_html(date('d/m/Y H:i', strtotime($detail->quiz_1_date)));
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($detail->completed_quiz_2): ?>
                                    <span class="status-badge active">✓ Completato</span>
                                <?php else: ?>
                                    <span class="status-badge inactive">✗ Non completato</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                if ($detail->quiz_2_date) {
                                    echo esc_html(date('d/m/Y H:i', strtotime($detail->quiz_2_date)));
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Genera Codici -->
<div id="generateCodesModal" class="foodwise-modal" style="display: none;">
    <div class="foodwise-modal-content">
        <span class="foodwise-modal-close">&times;</span>
        <h2>Genera Codici di Accesso</h2>
        <form id="generateCodesForm">
            <table class="form-table">
                <tr>
                    <th><label for="codes_count">Numero di codici</label></th>
                    <td><input type="number" id="codes_count" name="count" value="10" min="1" max="1000" required></td>
                </tr>
                <tr>
                    <th><label for="codes_access_type">Tipo di accesso</label></th>
                    <td>
                        <select id="codes_access_type" name="access_type" required>
                            <option value="quiz">Quiz</option>
                            <option value="viewer">Viewer</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="codes_prefix">Prefisso (opzionale)</label></th>
                    <td><input type="text" id="codes_prefix" name="prefix" maxlength="4" placeholder="es: FW"></td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" class="button button-primary">Genera</button>
                <button type="button" class="button" onclick="jQuery('#generateCodesModal').hide();">Annulla</button>
            </p>
        </form>
        <div id="generatedCodesResult" style="display: none;">
            <h3>Codici Generati</h3>
            <textarea id="generatedCodesText" readonly style="width: 100%; height: 200px;"></textarea>
            <button type="button" class="button" onclick="copyGeneratedCodes()">Copia</button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Modal Genera Codici
    $('#generateCodesBtn').on('click', function() {
        $('#generateCodesModal').show();
        $('#generatedCodesResult').hide();
    });
    
    $('.foodwise-modal-close').on('click', function() {
        $(this).closest('.foodwise-modal').hide();
    });
    
    // Form Genera Codici
    $('#generateCodesForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            action: 'foodwise_generate_codes',
            nonce: foodwiseAdmin.nonce,
            count: $('#codes_count').val(),
            access_type: $('#codes_access_type').val(),
            prefix: $('#codes_prefix').val()
        };
        
        $.post(foodwiseAdmin.ajaxUrl, formData, function(response) {
            if (response.success) {
                var codesText = response.data.codes.map(function(c) {
                    return c.code;
                }).join('\n');
                
                $('#generatedCodesText').val(codesText);
                $('#generatedCodesResult').show();
                alert(response.data.message);
            } else {
                alert('Errore: ' + response.data.message);
            }
        });
    });
    
    // Export Utenti
    $('#exportUsersBtn').on('click', function() {
        if (!confirm('Esportare tutti gli utenti in CSV?')) {
            return;
        }
        
        var formData = {
            action: 'foodwise_export_users',
            nonce: foodwiseAdmin.nonce,
            access_type: '<?php echo esc_js($access_type_filter); ?>'
        };
        
        $.post(foodwiseAdmin.ajaxUrl, formData, function(response) {
            if (response.success) {
                window.location.href = response.data.download_url;
            } else {
                alert('Errore: ' + response.data.message);
            }
        });
    });
});

function copyGeneratedCodes() {
    var textarea = document.getElementById('generatedCodesText');
    textarea.select();
    document.execCommand('copy');
    alert('Codici copiati negli appunti!');
}
</script>
