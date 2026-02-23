/**
 * FoodWise Quiz 2 (KAP) - jQuery Version 3.6
 * Gestisce la navigazione a pagine singole e il salvataggio dei progressi con jQuery.
 */
(function($) {
    'use strict';

    // Stato del quiz
    var state = {
        currentStep: 0,
        totalSteps: 0,
        answers: {},
        quizType: 'quiz_2',
        container: null
    };

    /**
     * Inizializzazione del Quiz
     */
    function init() {
        state.container = $('#foodwise-quiz-2');
        if (!state.container.length) return;

        console.log('FoodWise Quiz 2: Inizializzazione v3.6 (jQuery)...');

        // Carica dati passati dal PHP
        if (typeof foodwiseQuiz2Data !== 'undefined') {
            state.totalSteps = foodwiseQuiz2Data.totalSteps || 0;
            state.currentStep = (foodwiseQuiz2Data.currentStep < state.totalSteps) ? foodwiseQuiz2Data.currentStep : 0;
            state.answers = foodwiseQuiz2Data.answers || {};
        }

        // Aggancia eventi ai pulsanti di navigazione
        $('#prev-btn').on('click', function() {
            if (state.currentStep > 0) {
                goToStep(state.currentStep - 1);
            }
        });

        $('#next-btn').on('click', function() {
            if (state.currentStep < state.totalSteps - 1) {
                // Verifica se la domanda corrente ha una risposta
                if (validateCurrentStep()) {
                    goToStep(state.currentStep + 1);
                } else {
                    alert('Per favore, rispondi alla domanda prima di proseguire.');
                }
            } else {
                // Ultimo step: Concludi
                if (validateCurrentStep()) {
                    finishQuiz();
                } else {
                    alert('Per favore, rispondi alla domanda prima di concludere.');
                }
            }
        });

        // Aggancia eventi alle opzioni (radio buttons)
        state.container.on('change', 'input[type="radio"]', function() {
            var $this = $(this);
            var questionId = $this.closest('.quiz-step').data('question-id');
            var value = $this.val();
            
            // Salva risposta nello stato
            state.answers[questionId] = value;
            
            // Aggiorna stile visuale
            var $parentLabel = $this.closest('label');
            $this.closest('.question-options').find('label').removeClass('selected');
            $parentLabel.addClass('selected');

            // Salvataggio automatico progressi
            saveProgress();

            // Se è una domanda binaria (Sì/No, Vero/Falso), passa avanti automaticamente dopo un breve ritardo
            var questionType = $this.closest('.quiz-step').data('question-type');
            if (questionType === 'true_false' || questionType === 'yes_no') {
                setTimeout(function() {
                    if (state.currentStep < state.totalSteps - 1) {
                        goToStep(state.currentStep + 1);
                    }
                }, 600);
            }
        });

        // Mostra lo step iniziale, assicurandosi che sia valido
        if (state.totalSteps > 0 && state.currentStep >= state.totalSteps) {
            state.currentStep = 0;
        }
        goToStep(state.currentStep);
    }

    /**
     * Navigazione tra gli step
     */
    function goToStep(stepIndex) {
        var $steps = state.container.find('.quiz-step');
        $steps.each(function(index) {
            if (index === stepIndex) {
                $(this).addClass('active').show();
            } else {
                $(this).removeClass('active').hide();
            }
        });
        state.currentStep = stepIndex;

        // Aggiorna barra di progresso
        var $progressFill = state.container.find('.progress-fill');
        if ($progressFill.length) {
            var percent = ((state.currentStep + 1) / state.totalSteps) * 100;
            $progressFill.css('width', percent + '%');
        }

        // Aggiorna indicatori numerici
        var $currentIndicator = state.container.find('.step-indicator .current');
        if ($currentIndicator.length) {
            $currentIndicator.text(state.currentStep + 1);
        }

        // Gestione visibilità pulsanti
        var $prevBtn = $('#prev-btn');
        var $nextBtn = $('#next-btn');
        if ($prevBtn.length) {
            $prevBtn.css('visibility', (state.currentStep === 0) ? 'hidden' : 'visible');
        }
        if ($nextBtn.length) {
            $nextBtn.text((state.currentStep === state.totalSteps - 1) ? 'Concludi' : 'Avanti');
        }

        // Scroll in alto
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    }

    /**
     * Validazione dello step corrente
     */
    function validateCurrentStep() {
        var $activeStep = state.container.find('.quiz-step.active');
        if (!$activeStep.length) return false;
        var questionId = $activeStep.data('question-id');
        return state.answers.hasOwnProperty(questionId) && state.answers[questionId] !== '';
    }

    /**
     * Salvataggio progressi via AJAX (jQuery)
     */
    function saveProgress() {
        if (typeof foodwisePublic === 'undefined' || !foodwisePublic.ajaxUrl) return;

        $.ajax({
            url: foodwisePublic.ajaxUrl,
            type: 'POST',
            data: {
                action: 'foodwise_save_progress',
                nonce: foodwisePublic.nonce,
                quiz_type: state.quizType,
                current_step: state.currentStep,
                progress_data: JSON.stringify(state.answers)
            },
            success: function(response) {
                // console.log('Progresso salvato:', response);
            },
            error: function(xhr, status, error) {
                console.error('Errore salvataggio progressi:', error);
            }
        });
    }

    /**
     * Conclusione del Quiz
     */
    function finishQuiz() {
        if (typeof foodwisePublic === 'undefined' || !foodwisePublic.ajaxUrl) return;

        var $nextBtn = $('#next-btn');
        if ($nextBtn.length) {
            $nextBtn.prop('disabled', true);
            $nextBtn.text('Invio in corso...');
        }

        $.ajax({
            url: foodwisePublic.ajaxUrl,
            type: 'POST',
            data: {
                action: 'foodwise_submit_quiz',
                nonce: foodwisePublic.nonce,
                quiz_type: state.quizType,
                answers: JSON.stringify(state.answers)
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.redirect_url;
                } else {
                    alert('Errore durante l\'invio: ' + (response.data.message || 'Riprova più tardi.'));
                    if ($nextBtn.length) {
                        $nextBtn.prop('disabled', false);
                        $nextBtn.text('Concludi');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Errore invio quiz:', error);
                alert('Errore di connessione. Riprova.');
                if ($nextBtn.length) {
                    $nextBtn.prop('disabled', false);
                    $nextBtn.text('Concludi');
                }
            }
        });
    }

    // Avvio al caricamento del DOM
    $(document).ready(init);

})(jQuery);
