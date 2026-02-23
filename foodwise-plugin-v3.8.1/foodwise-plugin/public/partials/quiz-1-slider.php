<?php
/**
 * Quiz 1 - Valutazione Spreco Alimentare v3.0 DEFINITIVO
 * 2 Layout: Standard (scarto-1.png) + Speciale Avanzi (scartoavanzi.png)
 */
if (!defined('ABSPATH')) exit;

$user_id = FoodWise_Auth::get_current_user_id();
$completed = FoodWise_Quiz::check_quiz_completion($user_id, 'quiz_1');

// Recupera categorie selezionate
$selected_categories = FoodWise_Categories::get_selected_for_quiz();

if (empty($selected_categories)) {
    echo '<div style="text-align:center; padding:60px 20px;"><p style="font-size:18px; color:#666;">Nessuna categoria selezionata per il quiz. Contatta l\'amministratore.</p></div>';
    return;
}

// Recupera progressi salvati
$progress = FoodWise_Database::get_quiz_progress($user_id, 'quiz_1');
$current_step = ($progress) ? intval($progress->current_step) : 0;
$saved_answers = ($progress && !empty($progress->progress_data)) ? $progress->progress_data : array();

// Gestione ordine (randomizzazione con persistenza)
$questions = array();
if ($progress && !empty($progress->question_order)) {
    // Usa l'ordine salvato
    $order_ids = $progress->question_order;
    $cat_map = array();
    foreach ($selected_categories as $cat) {
        $cat_map[$cat->id] = $cat;
    }
    foreach ($order_ids as $cat_id) {
        if (isset($cat_map[$cat_id])) {
            $questions[] = $cat_map[$cat_id];
        }
    }
} else {
    // Prima volta: randomizza
    $questions = $selected_categories;
    shuffle($questions);
}

$total_questions = count($questions);
$question_order = array_column($questions, 'id');
?>

<div class="foodwise-quiz-v3" id="quiz1Container">
    <?php if ($completed): ?>
        <div class="quiz-completed">
            <div class="completed-icon">✓</div>
            <h2>Quiz completato</h2>
            <p>Grazie per aver completato la valutazione dello spreco alimentare.</p>
        </div>
    <?php else: ?>
        <form id="quiz1Form" class="quiz-form-v3">
            <?php foreach ($questions as $index => $cat): ?>
                <?php 
                $is_special = ($cat->category_code == '13');
                $is_active = ($index == $current_step);
                // Per categoria 13 il default è 50, per le altre è null o -1 per indicare nessuna scelta fatta
                $saved_value = isset($saved_answers[$cat->id]) ? floatval($saved_answers[$cat->id]) : ($is_special ? 50 : -1);
                ?>
                
                <div class="quiz-step" 
                     id="step-<?php echo $index; ?>" 
                     data-step="<?php echo $index; ?>"
                     data-cat-id="<?php echo $cat->id; ?>"
                     data-is-special="<?php echo $is_special ? '1' : '0'; ?>"
                     style="display: <?php echo $is_active ? 'block' : 'none'; ?>;">
                    
                    <?php if ($is_special): ?>
                        <!-- LAYOUT SPECIALE: Categoria 13 Avanzi (scartoavanzi.png) -->
                        <div class="layout-special">
                            <h2 class="q-title-main">Approssimativamente, <strong>quale percentuale della <u>quantità di avanzi</u> del pranzo/cena viene buttata via?</strong></h2>
                            <p class="q-explanation">(cioè gettata nel bidone dei rifiuti perché andata a male o scaduta oppure è stata cucinata ma non consumata e poi buttata).</p>
                            <p class="q-exclusions">Sono esclusi gli scarti vegetali o animali tipo bucce, gusci, ossa, semi ecc.</p>
                            <p class="q-instructions"><u>Puoi cliccare su qualsiasi punto della linea.</u></p>
                            
                            <div class="slider-wrapper">
                                <div class="slider-track" id="slider-<?php echo $cat->id; ?>" data-cat-id="<?php echo $cat->id; ?>" data-start="<?php echo $saved_value; ?>"></div>
                                <div class="slider-labels">
                                    <span>Niente</span>
                                    <span>Metà</span>
                                    <span>Tutto</span>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- LAYOUT STANDARD: Tutte le altre categorie (scarto-1.png) -->
                        <div class="layout-standard">
                            <h2 class="q-intro">Pensa in generale alla tua <strong>spesa settimanale</strong>.<br>Acquisti questo prodotto?</h2>
                            <h3 class="q-category-name"><?php echo esc_html($cat->category_name); ?></h3>
                            
                            <div class="binary-choice">
                                <button type="button" class="choice-btn btn-yes" data-cat-id="<?php echo $cat->id; ?>">Sì</button>
                                <button type="button" class="choice-btn btn-no" data-cat-id="<?php echo $cat->id; ?>">No</button>
                            </div>
                            
                            <div class="slider-section" id="slider-section-<?php echo $cat->id; ?>" style="display: none;">
                                <h2 class="q-title-main">Approssimativamente, <strong>quale percentuale della <u>quantità che compri</u> viene buttata via?</strong></h2>
                                <p class="q-explanation">(cioè gettata nel bidone dei rifiuti perché andata a male o scaduta oppure è stata cucinata ma non consumata e poi buttata).</p>
                                <p class="q-exclusions">Sono esclusi gli scarti vegetali o animali tipo bucce, gusci, ossa, semi ecc.</p>
                                <p class="q-instructions"><u>Puoi cliccare su qualsiasi punto della linea.</u></p>
                                
                                <div class="slider-wrapper">
                                    <div class="slider-track" id="slider-<?php echo $cat->id; ?>" data-cat-id="<?php echo $cat->id; ?>" data-start="<?php echo $saved_value; ?>"></div>
                                    <div class="slider-labels">
                                        <span>Niente</span>
                                        <span>Metà</span>
                                        <span>Tutto</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
	                    <input type="hidden" name="answers[<?php echo $cat->id; ?>]" class="answer-input" id="answer-<?php echo $cat->id; ?>" value="<?php echo $saved_value; ?>">
                    <input type="hidden" id="choice-<?php echo $cat->id; ?>" value="<?php echo ($saved_value >= 0) ? ($saved_value > 0 ? 'yes' : 'no') : ''; ?>">
                </div>
            <?php endforeach; ?>
            
            <div class="quiz-footer-nav">
                <div class="nav-left">
                    <button type="button" id="btnPrev" class="nav-btn" style="visibility: <?php echo ($current_step == 0) ? 'hidden' : 'visible'; ?>;">Indietro</button>
                </div>
                <div class="nav-right">
                    <button type="button" id="btnNext" class="nav-btn nav-btn-primary" style="display: <?php echo ($current_step == $total_questions - 1) ? 'none' : 'inline-block'; ?>;">Avanti</button>
                    <button type="submit" id="btnSubmit" class="nav-btn nav-btn-primary" style="display: <?php echo ($current_step == $total_questions - 1) ? 'inline-block' : 'none'; ?>;">Invia</button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
var quiz1Data = {
    currentStep: <?php echo $current_step; ?>,
    totalSteps: <?php echo $total_questions; ?>,
    questionOrder: <?php echo json_encode($question_order); ?>,
    userId: <?php echo $user_id; ?>
};
</script>
