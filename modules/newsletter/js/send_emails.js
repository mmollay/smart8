
// Prüft auf ausstehende E-Mails und zeigt/versteckt den Sende-Button
function checkPendingEmails() {
    $.ajax({
        url: 'ajax/check_pending_emails.php',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.hasPendingEmails) {
                if ($('#testSendEmail').length === 0) {
                    $('.ui.left.fixed.menu').append(
                        '<div class="item">' +
                        '<button class="ui blue icon button" id="testSendEmail">' +
                        '<i class="paper plane icon"></i> Test-Versand' +
                        '</button></div>'
                    );
                    initializeSendButton();
                }
            }
        }
    });
}

// Initialisiert den Sende-Button mit Click-Handler
function initializeSendButton() {
    $('#testSendEmail').off('click').on('click', function () {
        var $button = $(this);

        // Button deaktivieren
        $button.addClass('disabled');

        // AJAX-Aufruf zur Hintergrund-Ausführung
        $.ajax({
            url: 'exec/send_email_background.php',
            method: 'POST',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // Toast für Start des Versands
                    $('body').toast({
                        title: 'E-Mail Versand gestartet',
                        message: response.total_jobs + ' E-Mails werden versendet',
                        class: 'info',
                        showProgress: 'bottom',
                        displayTime: 3000
                    });

                    // Starte Status-Prüfung
                    checkSendingStatus();
                } else {
                    // Fehlerbehandlung
                    $('body').toast({
                        title: 'Fehler',
                        message: response.message || 'Versand fehlgeschlagen',
                        class: 'error',
                        showProgress: 'bottom',
                        displayTime: 3000
                    });

                    // Button wieder aktivieren
                    $button.removeClass('disabled');
                }
            },
            error: function () {
                $('body').toast({
                    title: 'Fehler',
                    message: 'Verbindungsfehler',
                    class: 'error',
                    showProgress: 'bottom',
                    displayTime: 3000
                });

                // Button wieder aktivieren
                $button.removeClass('disabled');
            }
        });
    });
}

// Prüft den Versandstatus
function checkSendingStatus() {
    let checkInterval = setInterval(function () {
        $.ajax({
            url: 'ajax/check_sending_status.php',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (!response.isStillSending) {
                    clearInterval(checkInterval);

                    // Button wieder aktivieren
                    $('#testSendEmail').removeClass('disabled');

                    // Erfolgsmeldung
                    $('body').toast({
                        title: 'Fertig',
                        message: 'E-Mail Versand abgeschlossen',
                        class: 'success',
                        showProgress: 'bottom',
                        displayTime: 3000,
                        onClose: function () {
                            if (typeof reloadTable === 'function') {
                                reloadTable();
                            }
                        }
                    });
                }
            }
        });
    }, 2000);
}

// Initialisierung
$(document).ready(function () {
    // Initialer Check
    checkPendingEmails();

    // Periodischer Check
    setInterval(checkPendingEmails, 30000);
});
