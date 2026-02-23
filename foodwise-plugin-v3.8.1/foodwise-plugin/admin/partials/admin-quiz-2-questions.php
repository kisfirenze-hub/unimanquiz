<?php
/**
 * Gestione Domande Quiz 2
 */
if (!defined('ABSPATH')) exit;

$questions = FoodWise_Database::get_quiz_2_questions(false);
?>

<div class="wrap foodwise-admin">
    <h1>Gestione Domande Quiz 2 (KAP)</h1>
    <p>In questa sezione puoi creare le domande per il test Knowledge, Attitude, Practice.</p>
    
    <div class="foodwise-dashboard-grid">
        <!-- Form Aggiunta/Modifica -->
        <div class="foodwise-card">
            <h2 id="form-title-q2">Aggiungi/Modifica Domanda KAP</h2>
            <form id="quiz2QuestionForm">
                <input type="hidden" name="id" id="q2_id" value="0">
                
                <div class="form-group">
                    <label for="section_title">Titolo Sezione</label>
                    <input type="text" name="section_title" id="q2_section_title" class="widefat">
                </div>
                
                <div class="form-group">
                    <label for="section_text">Testo sotto al titolo</label>
                    <textarea name="section_text" id="q2_section_text" rows="3" class="widefat"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="subsection_text">Sotto sezione</label>
                    <textarea name="subsection_text" id="q2_subsection_text" rows="3" class="widefat"></textarea>
                </div>

                <div class="form-group">
                    <label for="question_text">Testo della Domanda</label>
                    <textarea name="question_text" id="q2_question_text" rows="3" class="widefat" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="question_type">Tipo di Risposta</label>
                    <select name="question_type" id="q2_question_type" class="widefat" required>
                        <option value="multiple">Risposta Multipla</option>
                        <option value="true_false">Vero / Falso</option>
                        <option value="yes_no">Sì / No</option>
                    </select>
                </div>
                
                <div class="form-group" id="options-group">
                    <label for="options">Opzioni (una per riga, solo per Risposta Multipla)</label>
                    <textarea name="options" id="q2_options" rows="4" class="widefat" placeholder="Opzione 1&#10;Opzione 2&#10;Opzione 3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="order_position">Posizione Ordine</label>
                    <input type="number" name="order_position" id="q2_order_position" value="0" class="widefat">
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" id="q2_is_active" value="1" checked>
                        Attiva
                    </label>
                </div>
                
                <div class="foodwise-actions">
                    <button type="submit" class="button button-primary">Salva Domanda</button>
                    <button type="button" id="resetQ2Form" class="button">Reset</button>
                </div>
            </form>
        </div>
        
        <!-- Elenco Domande -->
        <div class="foodwise-card full-width">
            <h2>Domande KAP Configurate</h2>
            <div class="table-responsive">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th>Titolo Sezione</th>
                            <th>Testo sotto al titolo</th>
                            <th>Sotto sezione</th>
                            <th>Testo Domanda</th>
                            <th width="120">Tipo</th>
                            <th>Opzioni</th>
                            <th width="80">Ordine</th>
                            <th width="80">Stato</th>
                            <th width="150">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($questions)): ?>
                            <tr><td colspan="7">Nessuna domanda configurata.</td></tr>
                        <?php else: ?>
                            <?php foreach ($questions as $q): ?>
                                <?php 
                                    $opts = json_decode($q->options, true);
                                    $opts_text = is_array($opts) ? implode(', ', $opts) : '';
                                ?>
                                <tr>
                                    <td><?php echo $q->id; ?></td>
                                    <td><?php echo esc_html($q->section_title); ?></td>
                                    <td><?php echo esc_html($q->section_text); ?></td>
                                    <td><?php echo esc_html($q->subsection_text); ?></td>
                                    <td><strong><?php echo esc_html($q->question_text); ?></strong></td>
                                    <td><?php echo esc_html($q->question_type); ?></td>
                                    <td><?php echo esc_html($opts_text); ?></td>
                                    <td><?php echo $q->order_position; ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $q->is_active ? 'active' : 'inactive'; ?>">
                                            <?php echo $q->is_active ? 'Attiva' : 'Inattiva'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="button edit-q2" 
                                                data-id="<?php echo $q->id; ?>"
                                                data-title="<?php echo esc_attr($q->section_title); ?>"
                                                data-section-text="<?php echo esc_attr($q->section_text); ?>"
                                                data-subsection-text="<?php echo esc_attr($q->subsection_text); ?>"
                                                data-text="<?php echo esc_attr($q->question_text); ?>"
                                                data-type="<?php echo $q->question_type; ?>"
                                                data-options="<?php echo esc_attr(is_array($opts) ? implode("\n", $opts) : ''); ?>"
                                                data-order="<?php echo $q->order_position; ?>"
                                                data-active="<?php echo $q->is_active; ?>">Modifica</button>
                                        <button class="button delete-q2" data-id="<?php echo $q->id; ?>">Elimina</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Mostra/nascondi opzioni in base al tipo
    $('#q2_question_type').on('change', function() {
        if ($(this).val() === 'multiple') {
            $('#options-group').show();
        } else {
            $('#options-group').hide();
        }
    });

    // Salva Domanda
    $('#quiz2QuestionForm').on('submit', function(e) {
        e.preventDefault();
        var data = $(this).serializeArray();
        data.push({name: 'action', value: 'foodwise_save_quiz_2_question'});
        data.push({name: 'nonce', value: foodwiseAdmin.nonce});
        
        if (!$('#q2_is_active').is(':checked')) {
            data.push({name: 'is_active', value: 0});
        }

        $.post(foodwiseAdmin.ajaxUrl, data, function(response) {
            if (response.success) {
                alert(response.data.message);
                location.reload();
            } else {
                alert(response.data.message);
            }
        });
    });

    // Modifica
    $('.edit-q2').on('click', function() {
        var btn = $(this);
        $('#q2_id').val(btn.data('id'));
        $("#q2_section_title").val(btn.data("title"));
        $("#q2_section_text").val(btn.data("section-text"));
        $("#q2_subsection_text").val(btn.data("subsection-text"));
        $("#q2_question_text").val(btn.data("text"));
        $('#q2_question_type').val(btn.data('type')).trigger('change');
        $('#q2_options').val(btn.data('options'));
        $('#q2_order_position').val(btn.data('order'));
        $('#q2_is_active').prop('checked', btn.data('active') == 1);
        $('#form-title-q2').text('Modifica Domanda ID: ' + btn.data('id'));
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    });

    // Elimina
    $('.delete-q2').on('click', function() {
        if (confirm('Sei sicuro di voler eliminare questa domanda?')) {
            $.post(foodwiseAdmin.ajaxUrl, {
                action: 'foodwise_delete_quiz_question',
                nonce: foodwiseAdmin.nonce,
                id: $(this).data('id'),
                quiz_type: 'quiz_2'
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            });
        }
    });

    $('#resetQ2Form').on('click', function() {
        $('#quiz2QuestionForm')[0].reset();
        $('#q2_id').val(0);
        $('#q2_question_type').trigger('change');
        $('#form-title-q2').text('Aggiungi/Modifica Domanda KAP');
    });
});
</script>
