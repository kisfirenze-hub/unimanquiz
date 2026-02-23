<?php
/**
 * Form di accesso con codice
 */
if (!defined('ABSPATH')) exit;

// Se già loggato, redirect
if (FoodWise_Auth::is_logged_in()) {
    FoodWise_Auth::redirect_after_login();
}
?>

<div class="foodwise-access-container">
    <div class="foodwise-access-card">
        <div class="foodwise-logo">
            <h1>FoodWise</h1>
            <p>Sistema di valutazione dello spreco alimentare</p>
        </div>
        
        <form id="foodwiseAccessForm" class="foodwise-access-form">
            <div class="form-group">
                <label for="access_code">Inserisci il tuo codice di accesso</label>
                <input type="text" 
                       id="access_code" 
                       name="access_code" 
                       placeholder="Codice" 
                       required 
                       autocomplete="off"
                       class="foodwise-input">
            </div>
            
            <div id="accessMessage" class="foodwise-message" style="display: none;"></div>
            
            <button type="submit" class="foodwise-button foodwise-button-primary">
                Accedi
            </button>
        </form>
        
        <div class="foodwise-access-info">
            <p><small>Inserisci il codice che hai ricevuto per accedere ai quiz o visualizzare i report.</small></p>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#foodwiseAccessForm').on('submit', function(e) {
        e.preventDefault();
        
        var accessCode = $('#access_code').val().trim();
        
        if (!accessCode) {
            showMessage('Inserisci un codice di accesso.', 'error');
            return;
        }
        
        // Disabilita il pulsante
        var submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).text('Accesso in corso...');
        
        $.post(foodwisePublic.ajaxUrl, {
            action: 'foodwise_login',
            nonce: foodwisePublic.nonce,
            access_code: accessCode
        }, function(response) {
            if (response.success) {
                showMessage(response.data.message, 'success');
                
                // Redirect dopo 1 secondo
                setTimeout(function() {
                    window.location.href = response.data.redirect_url;
                }, 1000);
            } else {
                showMessage(response.data.message, 'error');
                submitBtn.prop('disabled', false).text('Accedi');
            }
        }).fail(function() {
            showMessage('Errore di connessione. Riprova.', 'error');
            submitBtn.prop('disabled', false).text('Accedi');
        });
    });
    
    function showMessage(message, type) {
        var messageDiv = $('#accessMessage');
        messageDiv.removeClass('success error').addClass(type);
        messageDiv.text(message).fadeIn();
        
        if (type === 'success') {
            setTimeout(function() {
                messageDiv.fadeOut();
            }, 3000);
        }
    }
});
</script>
