<?php
/**
 * Dashboard Visualizzatore
 */
if (!defined('ABSPATH')) exit;

$user = FoodWise_Auth::get_current_user();
$stats = FoodWise_Quiz::get_compliance_stats();
$quiz_1_submissions = FoodWise_Quiz::get_all_submissions_data('quiz_1');
$quiz_2_submissions = FoodWise_Quiz::get_all_submissions_data('quiz_2');
?>

<div class="foodwise-viewer-container">
    <div class="foodwise-viewer-header">
        <h1>Dashboard Visualizzatore</h1>
        <p>Codice: <strong><?php echo esc_html($user->panelist_code); ?></strong></p>
        <button type="button" class="foodwise-logout-btn" id="logoutBtn">Esci</button>
    </div>
    
    <div class="foodwise-stats-overview">
        <h2>Statistiche Generali</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_quiz_users']; ?></div>
                <div class="stat-label">Utenti Totali</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['completed_quiz_1']; ?></div>
                <div class="stat-label">Quiz 1 Completati</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['completed_quiz_2']; ?></div>
                <div class="stat-label">Quiz 2 Completati</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['completion_rate_quiz_1']; ?>%</div>
                <div class="stat-label">Tasso Quiz 1</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['completion_rate_quiz_2']; ?>%</div>
                <div class="stat-label">Tasso Quiz 2</div>
            </div>
        </div>
    </div>
    
    <div class="foodwise-data-section">
        <h2>Submissions Quiz 1 (<?php echo count($quiz_1_submissions); ?>)</h2>
        <div class="table-container">
            <table class="foodwise-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Codice</th>
                        <th>Test</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($quiz_1_submissions)): ?>
                    <tr><td colspan="4">Nessuna submission disponibile.</td></tr>
                    <?php else: ?>
                        <?php foreach (array_slice($quiz_1_submissions, 0, 100) as $sub): ?>
                        <tr>
                            <td><?php echo esc_html($sub->id); ?></td>
                            <td><?php echo esc_html($sub->panelist_code); ?></td>
                            <td><?php echo esc_html($sub->test_name); ?></td>
                            <td><?php echo esc_html(date('d/m/Y H:i', strtotime($sub->submitted_at))); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="foodwise-data-section">
        <h2>Submissions Quiz 2 (<?php echo count($quiz_2_submissions); ?>)</h2>
        <div class="table-container">
            <table class="foodwise-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Codice</th>
                        <th>Test</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($quiz_2_submissions)): ?>
                    <tr><td colspan="4">Nessuna submission disponibile.</td></tr>
                    <?php else: ?>
                        <?php foreach (array_slice($quiz_2_submissions, 0, 100) as $sub): ?>
                        <tr>
                            <td><?php echo esc_html($sub->id); ?></td>
                            <td><?php echo esc_html($sub->panelist_code); ?></td>
                            <td><?php echo esc_html($sub->test_name); ?></td>
                            <td><?php echo esc_html(date('d/m/Y H:i', strtotime($sub->submitted_at))); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
