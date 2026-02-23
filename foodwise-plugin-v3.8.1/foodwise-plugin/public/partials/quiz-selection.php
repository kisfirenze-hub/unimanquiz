<?php
/**
 * Dashboard di selezione quiz post-accesso
 */
if (!defined('ABSPATH')) exit;

$user_id = FoodWise_Auth::get_current_user_id();
$quiz1_active = get_option('foodwise_quiz1_active', 1);
$quiz2_active = get_option('foodwise_quiz2_active', 1);

$quiz1_completed = FoodWise_Quiz::check_quiz_completion($user_id, 'quiz_1');
$quiz2_completed = FoodWise_Quiz::check_quiz_completion($user_id, 'quiz_2');

$quiz1_url = get_permalink(get_option('foodwise_quiz1_page_id'));
$quiz2_url = get_permalink(get_option('foodwise_quiz2_page_id'));
?>

<div class="foodwise-selection-container">
    <div class="selection-header">
        <h1>Benvenuto in FoodWise</h1>
        <p>Seleziona il quiz che desideri compilare</p>
    </div>

    <div class="selection-grid">
        <?php if ($quiz1_active): ?>
            <a href="<?php echo esc_url($quiz1_url); ?>" class="selection-card <?php echo $quiz1_completed ? 'completed' : ''; ?>">
                <div class="card-icon">📊</div>
                <div class="card-content">
                    <h3>Quiz 1: Valutazione Spreco</h3>
                    <p>Valuta la quantità di cibo che viene buttata via per diverse categorie.</p>
                    <?php if ($quiz1_completed): ?>
                        <span class="status-badge">Completato</span>
                    <?php else: ?>
                        <span class="status-badge pending">Da completare</span>
                    <?php endif; ?>
                </div>
            </a>
        <?php endif; ?>

        <?php if ($quiz2_active): ?>
            <a href="<?php echo esc_url($quiz2_url); ?>" class="selection-card <?php echo $quiz2_completed ? 'completed' : ''; ?>">
                <div class="card-icon">📝</div>
                <div class="card-content">
                    <h3>Quiz 2: Questionario KAP</h3>
                    <p>Conoscenze, Attitudini e Pratiche riguardo lo spreco alimentare.</p>
                    <?php if ($quiz2_completed): ?>
                        <span class="status-badge">Completato</span>
                    <?php else: ?>
                        <span class="status-badge pending">Da completare</span>
                    <?php endif; ?>
                </div>
            </a>
        <?php endif; ?>
    </div>

    <div class="selection-footer">
        <button id="foodwiseLogout" class="foodwise-button-link">Esci dal sistema</button>
    </div>
</div>

<style>
.foodwise-selection-container {
    max-width: 1000px;
    margin: 60px auto;
    padding: 0 20px;
    text-align: center;
}
.selection-header h1 {
    font-size: 36px;
    color: #1a1a1a;
    margin-bottom: 10px;
}
.selection-header p {
    font-size: 18px;
    color: #666;
    margin-bottom: 50px;
}
.selection-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 50px;
}
.selection-card {
    background: #fff;
    border-radius: 15px;
    padding: 40px 30px;
    text-decoration: none !important;
    color: inherit !important;
    border: 2px solid #eee;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}
.selection-card:hover {
    border-color: #004a99;
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,74,153,0.1);
}
.selection-card.completed {
    background: #f9f9f9;
    opacity: 0.8;
}
.card-icon {
    font-size: 50px;
    margin-bottom: 20px;
}
.card-content h3 {
    font-size: 22px;
    margin-bottom: 15px;
    color: #1a1a1a;
}
.card-content p {
    font-size: 16px;
    color: #666;
    margin-bottom: 25px;
    line-height: 1.5;
}
.status-badge {
    display: inline-block;
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    background: #e8f5e9;
    color: #2e7d32;
}
.status-badge.pending {
    background: #e3f2fd;
    color: #1565c0;
}
.selection-footer {
    margin-top: 40px;
}
.foodwise-button-link {
    background: none;
    border: none;
    color: #666;
    text-decoration: underline;
    cursor: pointer;
    font-size: 16px;
}
.foodwise-button-link:hover {
    color: #1a1a1a;
}
@media (max-width: 600px) {
    .selection-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#foodwiseLogout').on('click', function() {
        $.post(foodwisePublic.ajaxUrl, {
            action: 'foodwise_logout',
            nonce: foodwisePublic.nonce
        }, function(response) {
            if (response.success) {
                window.location.href = response.data.redirect_url;
            }
        });
    });
});
</script>
