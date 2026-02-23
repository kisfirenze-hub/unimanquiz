<?php
/**
 * Impostazioni Quiz
 */
if (!defined('ABSPATH')) exit;

$quiz_types = FoodWise_Database::get_quiz_types();
?>

<div class="wrap foodwise-admin">
    <h1>Impostazioni Quiz</h1>
    
    <div class="foodwise-card">
        <h2>Attivazione/Disattivazione Quiz</h2>
        <p>Controlla quali quiz sono visibili agli utenti. Gli utenti vedono sempre e solo 2 quiz alla volta.</p>
        
        <table class="widefat">
            <thead>
                <tr>
                    <th>Quiz</th>
                    <th>Descrizione</th>
                    <th>Stato</th>
                    <th>Azione</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quiz_types as $quiz): ?>
                <tr>
                    <td><strong><?php echo esc_html($quiz->quiz_name); ?></strong></td>
                    <td><?php echo esc_html($quiz->description); ?></td>
                    <td>
                        <span class="status-badge <?php echo $quiz->is_active ? 'active' : 'inactive'; ?>">
                            <?php echo $quiz->is_active ? 'Attivo' : 'Disattivo'; ?>
                        </span>
                    </td>
                    <td>
                        <label class="switch">
                            <input type="checkbox" 
                                   class="toggle-quiz" 
                                   data-quiz-type="<?php echo esc_attr($quiz->quiz_type); ?>"
                                   <?php checked($quiz->is_active, 1); ?>>
                            <span class="slider"></span>
                        </label>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="foodwise-card">
        <h2>Informazioni Quiz</h2>
        
        <h3>Quiz 1 - Valutazione dello spreco domestico</h3>
        <ul>
            <li>Tipo: Slider touch-friendly</li>
            <li>Range valori: 0-100 (con primo decimale)</li>
            <li>Visualizzazione: "Niente" → "Tutto"</li>
            <li>Una domanda per ogni categoria</li>
        </ul>
        
        <h3>Quiz 2 - KAP (Knowledge, Attitude, Practice)</h3>
        <ul>
            <li>Domande a risposta multipla</li>
            <li>Domande Vero/Falso</li>
            <li>Domande Sì/No</li>
        </ul>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.toggle-quiz').on('change', function() {
        var quizType = $(this).data('quiz-type');
        var isActive = $(this).is(':checked') ? 1 : 0;
        
        $.post(foodwiseAdmin.ajaxUrl, {
            action: 'foodwise_toggle_quiz',
            nonce: foodwiseAdmin.nonce,
            quiz_type: quizType,
            is_active: isActive
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Errore: ' + response.data.message);
            }
        });
    });
});
</script>
