<?php
/**
 * Dashboard amministrazione
 *
 * @package    FoodWise
 * @subpackage FoodWise/admin/partials
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ottiene le statistiche
$stats = FoodWise_Quiz::get_compliance_stats();
$quiz_types = FoodWise_Database::get_quiz_types();
$total_users = FoodWise_Database::get_users();
$total_categories = FoodWise_Categories::get_all();
?>

<div class="wrap foodwise-admin">
    <h1>Dashboard FoodWise</h1>
    
    <div class="foodwise-dashboard-grid">
        
        <!-- Statistiche Generali -->
        <div class="foodwise-card">
            <h2>Statistiche Generali</h2>
            <div class="foodwise-stats-grid">
                <div class="stat-box">
                    <div class="stat-number"><?php echo count($total_users); ?></div>
                    <div class="stat-label">Utenti Totali</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo $stats['total_quiz_users']; ?></div>
                    <div class="stat-label">Utenti Quiz</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo count($total_categories); ?></div>
                    <div class="stat-label">Categorie</div>
                </div>
            </div>
        </div>
        
        <!-- Compliance Quiz -->
        <div class="foodwise-card">
            <h2>Compliance Quiz</h2>
            <div class="compliance-stats">
                <div class="compliance-item">
                    <h3>Quiz 1 - Valutazione Spreco</h3>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $stats['completion_rate_quiz_1']; ?>%"></div>
                    </div>
                    <p>
                        <strong><?php echo $stats['completed_quiz_1']; ?></strong> completati su 
                        <strong><?php echo $stats['total_quiz_users']; ?></strong> 
                        (<?php echo $stats['completion_rate_quiz_1']; ?>%)
                    </p>
                    <p class="pending">In attesa: <?php echo $stats['pending_quiz_1']; ?></p>
                </div>
                
                <div class="compliance-item">
                    <h3>Quiz 2 - KAP</h3>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $stats['completion_rate_quiz_2']; ?>%"></div>
                    </div>
                    <p>
                        <strong><?php echo $stats['completed_quiz_2']; ?></strong> completati su 
                        <strong><?php echo $stats['total_quiz_users']; ?></strong> 
                        (<?php echo $stats['completion_rate_quiz_2']; ?>%)
                    </p>
                    <p class="pending">In attesa: <?php echo $stats['pending_quiz_2']; ?></p>
                </div>
            </div>
        </div>
        
        <!-- Stato Quiz -->
        <div class="foodwise-card">
            <h2>Stato Quiz</h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Quiz</th>
                        <th>Stato</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quiz_types as $quiz): ?>
                    <tr>
                        <td><?php echo esc_html($quiz->quiz_name); ?></td>
                        <td>
                            <span class="status-badge <?php echo $quiz->is_active ? 'active' : 'inactive'; ?>">
                                <?php echo $quiz->is_active ? 'Attivo' : 'Disattivo'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Grafico Compliance -->
        <div class="foodwise-card full-width">
            <h2>Grafico Compliance</h2>
            <canvas id="complianceChart" width="400" height="150"></canvas>
        </div>
        
        <!-- Link Rapidi -->
        <div class="foodwise-card">
            <h2>Link Rapidi</h2>
            <ul class="quick-links">
                <li><a href="?page=foodwise-users" class="button">Gestione Utenti</a></li>
                <li><a href="?page=foodwise-categories" class="button">Gestione Categorie</a></li>
                <li><a href="?page=foodwise-csv-import" class="button">Import CSV</a></li>
                <li><a href="?page=foodwise-reports" class="button">Visualizza Report</a></li>
            </ul>
        </div>
        
        <!-- Pagine Plugin -->
        <div class="foodwise-card">
            <h2>Pagine Plugin</h2>
            <ul class="page-links">
                <li>
                    <strong>Accesso:</strong> 
                    <a href="<?php echo get_permalink(get_option('foodwise_access_page_id')); ?>" target="_blank">
                        Visualizza
                    </a>
                </li>
                <li>
                    <strong>Quiz 1:</strong> 
                    <a href="<?php echo get_permalink(get_option('foodwise_quiz1_page_id')); ?>" target="_blank">
                        Visualizza
                    </a>
                </li>
                <li>
                    <strong>Quiz 2:</strong> 
                    <a href="<?php echo get_permalink(get_option('foodwise_quiz2_page_id')); ?>" target="_blank">
                        Visualizza
                    </a>
                </li>
                <li>
                    <strong>Viewer:</strong> 
                    <a href="<?php echo get_permalink(get_option('foodwise_viewer_page_id')); ?>" target="_blank">
                        Visualizza
                    </a>
                </li>
            </ul>
        </div>
        
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Grafico Compliance
    var ctx = document.getElementById('complianceChart').getContext('2d');
    var complianceChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Quiz 1', 'Quiz 2'],
            datasets: [{
                label: 'Completati',
                data: [<?php echo $stats['completed_quiz_1']; ?>, <?php echo $stats['completed_quiz_2']; ?>],
                backgroundColor: '#4CAF50'
            }, {
                label: 'In attesa',
                data: [<?php echo $stats['pending_quiz_1']; ?>, <?php echo $stats['pending_quiz_2']; ?>],
                backgroundColor: '#FF9800'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
