<?php
/**
 * Template per il Quiz 2 (KAP) - Versione 3.4 (Pagine Singole)
 */
if (!defined('ABSPATH')) exit;

$user_id = FoodWise_Auth::get_current_user_id();
$questions = FoodWise_Quiz::get_quiz_questions('quiz_2');

if (empty($questions)) {
    echo '<div class="foodwise-quiz-container"><p>Nessuna domanda disponibile per questo quiz.</p></div>';
    return;
}

// Recupera progresso se esistente
$progress = FoodWise_Database::get_quiz_progress($user_id, 'quiz_2');
$total_questions = count($questions);
$current_step = ($progress && $progress->current_step < $total_questions) ? $progress->current_step : 0;
$saved_answers = ($progress) ? $progress->progress_data : array();
?>

<div id="foodwise-quiz-2" class="foodwise-quiz-v3 premium-layout" data-quiz-type="quiz_2">
    <?php if (!empty($questions)): ?>
    <div class="quiz-progress-bar">
        <div class="progress-fill" style="width: <?php echo (($current_step + 1) / count($questions)) * 100; ?>%"></div>
    </div>
    <?php endif; ?>

    <div class="quiz-questions-wrapper">
        <?php foreach ($questions as $index => $question): ?>
            <div class="quiz-step <?php echo ($index == $current_step) ? 'active' : ''; ?>" 
                 data-step="<?php echo $index; ?>" 
                 data-question-id="<?php echo $question['id']; ?>"
                 data-question-type="<?php echo $question['type']; ?>">
                
                <div class="quiz-card">
                    <div class="question-header">
                        <?php if (!empty($question["section_title"])): ?>
                            <h3 class="section-title"><?php echo esc_html($question["section_title"]); ?></h3>
                        <?php endif; ?>
                        <?php if (!empty($question["section_text"])): ?>
                            <p class="section-text"><?php echo esc_html($question["section_text"]); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($question["subsection_text"])): ?>
                            <p class="subsection-text"><?php echo esc_html($question["subsection_text"]); ?></p>
                        <?php endif; ?>
                        <span class="question-number">Domanda <?php echo ($index + 1); ?> di <?php echo count($questions); ?></span>
                        <h2 class="question-text"><?php echo esc_html($question["question_text"]); ?></h2>
                    </div>

                    <div class="question-options">
                        <?php if ($question['type'] === 'multiple'): ?>
                            <div class="options-grid">
                                <?php foreach ($question['options'] as $opt_index => $option): ?>
                                    <label class="option-item <?php echo (isset($saved_answers[$question['id']]) && $saved_answers[$question['id']] == $option) ? 'selected' : ''; ?>">
                                        <input type="radio" name="q<?php echo $question['id']; ?>" value="<?php echo esc_attr($option); ?>" 
                                               <?php checked(isset($saved_answers[$question['id']]) ? $saved_answers[$question['id']] : '', $option); ?>>
                                        <span class="option-label"><?php echo esc_html($option); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif ($question['type'] === 'true_false' || $question['type'] === 'yes_no'): ?>
                            <div class="options-binary">
                                <?php 
                                $opts = ($question['type'] === 'true_false') ? array('Vero', 'Falso') : array('Sì', 'No');
                                foreach ($opts as $opt): 
                                ?>
                                    <label class="option-button <?php echo (isset($saved_answers[$question['id']]) && $saved_answers[$question['id']] == $opt) ? 'selected' : ''; ?>">
                                        <input type="radio" name="q<?php echo $question['id']; ?>" value="<?php echo $opt; ?>"
                                               <?php checked(isset($saved_answers[$question['id']]) ? $saved_answers[$question['id']] : '', $opt); ?>>
                                        <span class="button-text"><?php echo $opt; ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="quiz-footer-nav">
        <button type="button" id="prev-btn" class="nav-btn" <?php echo ($current_step == 0) ? 'style="visibility:hidden;"' : ''; ?>>Indietro</button>
        <div class="step-indicator">
            <span class="current"><?php echo (!empty($questions)) ? ($current_step + 1) : 0; ?></span> / <span class="total"><?php echo count($questions); ?></span>
        </div>
        <button type="button" id="next-btn" class="nav-btn nav-btn-primary">
            <?php echo (!empty($questions) && $current_step == count($questions) - 1) ? 'Concludi' : 'Avanti'; ?>
        </button>
    </div>
</div>

<script>
// Passiamo i dati al file JS
var foodwiseQuiz2Data = {
    totalSteps: <?php echo $total_questions; ?>,
    currentStep: <?php echo $current_step; ?>,
    answers: <?php echo json_encode($saved_answers); ?>
};
</script>
