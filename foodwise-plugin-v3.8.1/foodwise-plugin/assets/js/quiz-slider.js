/**
 * FoodWise Quiz 1 v3.0 - JavaScript con Debug
 * Gestione navigazione, slider, salvataggio progressi
 */

(function() {
    'use strict';
    
    // Attende che jQuery e noUiSlider siano disponibili
    function initQuiz() {
        if (typeof jQuery === 'undefined') {
            console.error('FoodWise Quiz 1: jQuery non caricato!');
            setTimeout(initQuiz, 100);
            return;
        }
        
        var $ = jQuery;
        console.log('FoodWise Quiz 1: Script caricato');
    
    if (!$('#quiz1Container').length) {
        console.log('FoodWise Quiz 1: Container non trovato');
        return;
    }
    
    if (typeof quiz1Data === 'undefined') {
        console.log('FoodWise Quiz 1: quiz1Data non definito');
        return;
    }
    
    if (typeof noUiSlider === 'undefined') {
        console.error('FoodWise Quiz 1: noUiSlider non caricato!');
        alert('Errore: libreria slider non caricata. Ricarica la pagina.');
        return;
    }
    
    console.log('FoodWise Quiz 1: Inizializzazione...', quiz1Data);
    
    var sliders = {};
    var currentStep = quiz1Data.currentStep;
    var totalSteps = quiz1Data.totalSteps;
    
    // ========================================
    // INIZIALIZZAZIONE SLIDER (noUiSlider)
    // ========================================
    $('.slider-track').each(function() {
        var $slider = $(this);
        var catId = $slider.data('cat-id');
        var startValue = parseFloat($slider.data('start'));
        
        console.log('Inizializzazione slider per categoria:', catId, 'valore iniziale:', startValue);
        
        try {
            noUiSlider.create(this, {
                start: [startValue],
                connect: [true, false],
                range: {
                    'min': 0,
                    'max': 100
                },
                step: 0.1
            });
            
            // Aggiorna input nascosto al movimento
            this.noUiSlider.on('update', function(values, handle) {
                var val = parseFloat(values[handle]);
                $('#answer-' + catId).val(val);
                console.log('Slider aggiornato cat', catId, ':', val);
            });
            
            sliders[catId] = this.noUiSlider;
            console.log('Slider creato con successo per cat', catId);
        } catch(e) {
            console.error('Errore creazione slider per cat', catId, ':', e);
        }
    });
    
    console.log('Sliders inizializzati:', Object.keys(sliders).length);
    
    // ========================================
    // GESTIONE PULSANTI SÌ/NO
    // ========================================
    $('.choice-btn').on('click', function() {
        var $btn = $(this);
        var catId = $btn.data('cat-id');
        var $sliderSection = $('#slider-section-' + catId);
        var $answerInput = $('#answer-' + catId);
        
        console.log('Click su pulsante:', $btn.hasClass('btn-yes') ? 'Sì' : 'No', 'cat:', catId);
        
        // Feedback visivo
        $btn.siblings('.choice-btn').removeClass('active');
        $btn.addClass('active');
        
        if ($btn.hasClass('btn-yes')) {
            // Mostra slider
            console.log('Mostrando slider per cat', catId);
            $sliderSection.fadeIn(400);
            $('#choice-' + catId).val('yes');
            
            // Imposta valore corrente dello slider (default 50 se era -1)
            if (sliders[catId]) {
                var currentVal = parseFloat($answerInput.val());
                if (currentVal < 0) {
                    sliders[catId].set(50);
                    $answerInput.val(50);
                } else {
                    $answerInput.val(sliders[catId].get());
                }
            }
        } else {
            // Nascondi slider e imposta valore 0
            console.log('Nascondendo slider per cat', catId);
            $sliderSection.fadeOut(300);
            $answerInput.val(0);
            $('#choice-' + catId).val('no');
            
            // Passa automaticamente alla prossima domanda
            setTimeout(function() {
                if (currentStep < totalSteps - 1) {
                    console.log('Passaggio automatico alla prossima domanda');
                    goToNextStep();
                }
            }, 800);
        }
    });
    
    // ========================================
    // NAVIGAZIONE
    // ========================================
    $('#btnNext').on('click', function() {
        console.log('Click su Avanti');
        
        // Validazione: deve aver scelto Sì o No (tranne per cat 13 che ha slider diretto)
        var $currentStepEl = $('#step-' + currentStep);
        var catId = $currentStepEl.data('cat-id');
        var isSpecial = $currentStepEl.data('is-special') == '1';
        
        if (!isSpecial) {
            var choice = $('#choice-' + catId).val();
            if (!choice) {
                alert('Per favore, seleziona Sì o No prima di continuare.');
                return;
            }
        }
        
        if (currentStep < totalSteps - 1) {
            goToNextStep();
        }
    });
    
    $('#btnPrev').on('click', function() {
        console.log('Click su Indietro');
        if (currentStep > 0) {
            goToPrevStep();
        }
    });
    
    function goToNextStep() {
        currentStep++;
        console.log('Vai a step:', currentStep);
        showStep(currentStep);
        saveProgress();
    }
    
    function goToPrevStep() {
        currentStep--;
        console.log('Torna a step:', currentStep);
        showStep(currentStep);
        saveProgress();
    }
    
    function showStep(stepIndex) {
        // Nascondi tutti gli step
        $('.quiz-step').hide();
        
        // Mostra lo step corrente
        $('#step-' + stepIndex).fadeIn(300);
        
        // Aggiorna pulsanti footer
        if (stepIndex === 0) {
            $('#btnPrev').css('visibility', 'hidden');
        } else {
            $('#btnPrev').css('visibility', 'visible');
        }
        
        if (stepIndex === totalSteps - 1) {
            $('#btnNext').hide();
            $('#btnSubmit').show();
        } else {
            $('#btnNext').show();
            $('#btnSubmit').hide();
        }
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    // ========================================
    // SALVATAGGIO PROGRESSI
    // ========================================
    function saveProgress() {
        var answers = {};
        $('.answer-input').each(function() {
            var catId = $(this).attr('id').replace('answer-', '');
            answers[catId] = $(this).val();
        });
        
        console.log('Salvataggio progressi...', answers);
        
        $.post(foodwisePublic.ajaxUrl, {
            action: 'foodwise_save_progress',
            nonce: foodwisePublic.nonce,
            quiz_type: 'quiz_1',
            current_step: currentStep,
            progress_data: JSON.stringify(answers),
            order: quiz1Data.questionOrder
        }).done(function(response) {
            console.log('Progressi salvati:', response);
        }).fail(function(error) {
            console.error('Errore salvataggio progressi:', error);
        });
    }
    
    // ========================================
    // INVIO FINALE QUIZ
    // ========================================
    $('#quiz1Form').on('submit', function(e) {
        e.preventDefault();
        
        console.log('Invio quiz finale...');
        
        var $submitBtn = $('#btnSubmit');
        $submitBtn.prop('disabled', true).text('Invio in corso...');
        
        var answers = {};
        $('.answer-input').each(function() {
            var catId = $(this).attr('id').replace('answer-', '');
            answers[catId] = $(this).val();
        });
        
        $.post(foodwisePublic.ajaxUrl, {
            action: 'foodwise_submit_quiz',
            nonce: foodwisePublic.nonce,
            quiz_type: 'quiz_1',
            answers: JSON.stringify(answers)
        }, function(response) {
            console.log('Risposta invio quiz:', response);
            if (response.success) {
                window.location.href = response.data.redirect_url;
            } else {
                alert(response.data.message || 'Errore durante l\'invio del quiz.');
                $submitBtn.prop('disabled', false).text('Invia');
            }
        }).fail(function(error) {
            console.error('Errore invio quiz:', error);
            alert('Errore di connessione. Riprova.');
            $submitBtn.prop('disabled', false).text('Invia');
        });
    });
    
    // ========================================
    // RIPRISTINO STATO (per categorie standard con risposta salvata)
    // ========================================
    $('.quiz-step').each(function() {
        var $step = $(this);
        var catId = $step.data('cat-id');
        var isSpecial = $step.data('is-special') == '1';
        var savedValue = parseFloat($('#answer-' + catId).val());
        var choice = $('#choice-' + catId).val();
        
        if (!isSpecial) {
            if (choice === 'yes') {
                var $yesBtn = $step.find('.btn-yes[data-cat-id="' + catId + '"]');
                var $sliderSection = $('#slider-section-' + catId);
                $yesBtn.addClass('active');
                $sliderSection.show();
            } else if (choice === 'no') {
                var $noBtn = $step.find('.btn-no[data-cat-id="' + catId + '"]');
                $noBtn.addClass('active');
            }
        }
    });
    
    console.log('FoodWise Quiz 1: Inizializzazione completata');
    }
    
    // Avvia l'inizializzazione quando il DOM è pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initQuiz);
    } else {
        initQuiz();
    }
})();
