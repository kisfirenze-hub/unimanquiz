<?php
/**
 * Report e statistiche
 */
if (!defined('ABSPATH')) exit;

$stats = FoodWise_Quiz::get_compliance_stats();
$quiz_1_submissions = FoodWise_Quiz::get_all_submissions_data('quiz_1');
$quiz_2_submissions = FoodWise_Quiz::get_all_submissions_data('quiz_2');
?>

<div class="wrap foodwise-admin">
    <h1>Report e Statistiche</h1>
    
    <div class="foodwise-card">
        <h2>Riepilogo Generale</h2>
        <div class="foodwise-stats-grid">
            <div class="stat-box">
                <div class="stat-number"><?php echo $stats['total_quiz_users']; ?></div>
                <div class="stat-label">Utenti Totali</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $stats['completed_quiz_1']; ?></div>
                <div class="stat-label">Quiz 1 Completati</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $stats['completed_quiz_2']; ?></div>
                <div class="stat-label">Quiz 2 Completati</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $stats['completion_rate_quiz_1']; ?>%</div>
                <div class="stat-label">Tasso Completamento Quiz 1</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $stats['completion_rate_quiz_2']; ?>%</div>
                <div class="stat-label">Tasso Completamento Quiz 2</div>
            </div>
        </div>
    </div>
    
    <div class="foodwise-card">
        <h2>Export Dati</h2>
        <div class="export-buttons">
            <button type="button" class="button button-primary" id="exportQuiz1Btn">
                Export Quiz 1 (<?php echo count($quiz_1_submissions); ?> submissions)
            </button>
            <button type="button" class="button button-primary" id="exportQuiz2Btn">
                Export Quiz 2 (<?php echo count($quiz_2_submissions); ?> submissions)
            </button>
            <button type="button" class="button button-primary" id="exportAllBtn">
                Export Tutti i Dati
            </button>
        </div>
    </div>
    
    <div class="foodwise-card">
        <h2>Submissions Quiz 1 (ultime 50)</h2>
        <div class="table-responsive">
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Panelist Code</th>
                        <th>Test Name</th>
                        <th>Data Submission</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($quiz_1_submissions)): ?>
                    <tr><td colspan="5" style="text-align: center;">Nessuna submission trovata.</td></tr>
                    <?php else: ?>
                        <?php foreach (array_slice($quiz_1_submissions, 0, 50) as $sub): ?>
                        <tr>
                            <td><?php echo esc_html($sub->id); ?></td>
                            <td><strong><?php echo esc_html($sub->panelist_code); ?></strong></td>
                            <td><?php echo esc_html($sub->test_name); ?></td>
                            <td><?php echo esc_html(date('d/m/Y H:i', strtotime($sub->submitted_at))); ?></td>
                            <td>
                                <button class="button button-small view-submission" 
                                        data-submission='<?php echo esc_attr(json_encode($sub->submission_data)); ?>'>
                                    Visualizza
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="foodwise-card">
        <h2>Submissions Quiz 2 (ultime 50)</h2>
        <div class="table-responsive">
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Panelist Code</th>
                        <th>Test Name</th>
                        <th>Data Submission</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($quiz_2_submissions)): ?>
                    <tr><td colspan="5" style="text-align: center;">Nessuna submission trovata.</td></tr>
                    <?php else: ?>
                        <?php foreach (array_slice($quiz_2_submissions, 0, 50) as $sub): ?>
                        <tr>
                            <td><?php echo esc_html($sub->id); ?></td>
                            <td><strong><?php echo esc_html($sub->panelist_code); ?></strong></td>
                            <td><?php echo esc_html($sub->test_name); ?></td>
                            <td><?php echo esc_html(date('d/m/Y H:i', strtotime($sub->submitted_at))); ?></td>
                            <td>
                                <button class="button button-small view-submission" 
                                        data-submission='<?php echo esc_attr(json_encode($sub->submission_data)); ?>'>
                                    Visualizza
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Visualizza Submission -->
<div id="viewSubmissionModal" class="foodwise-modal" style="display: none;">
    <div class="foodwise-modal-content">
        <span class="foodwise-modal-close">&times;</span>
        <h2>Dettaglio Submission</h2>
        <div id="submissionContent"></div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Export Quiz 1
    $('#exportQuiz1Btn').on('click', function() {
        exportSubmissions('quiz_1');
    });
    
    // Export Quiz 2
    $('#exportQuiz2Btn').on('click', function() {
        exportSubmissions('quiz_2');
    });
    
    // Export All
    $('#exportAllBtn').on('click', function() {
        exportSubmissions(null);
    });
    
    function exportSubmissions(quizType) {
        $.post(foodwiseAdmin.ajaxUrl, {
            action: 'foodwise_export_submissions',
            nonce: foodwiseAdmin.nonce,
            quiz_type: quizType
        }, function(response) {
            if (response.success) {
                window.location.href = response.data.download_url;
            } else {
                alert('Errore: ' + response.data.message);
            }
        });
    }
    
    // Visualizza submission
    $('.view-submission').on('click', function() {
        var submissionData = $(this).data('submission');
        var html = '<table class="widefat"><thead><tr><th>Domanda ID</th><th>Risposta</th></tr></thead><tbody>';
        
        $.each(submissionData, function(key, value) {
            html += '<tr><td>' + key + '</td><td><strong>' + value + '</strong></td></tr>';
        });
        
        html += '</tbody></table>';
        
        $('#submissionContent').html(html);
        $('#viewSubmissionModal').show();
    });
    
    $('.foodwise-modal-close').on('click', function() {
        $(this).closest('.foodwise-modal').hide();
    });
});
</script>
