function checkPendingEmails() {
    $.ajax({
        url: 'ajax/check_pending_emails.php',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.hasPendingEmails) {
                if ($('#testSendEmail').length === 0) {
                    $('.ui.left.fixed.menu').append(
                        '<div class="item"><button class="ui blue icon button" id="testSendEmail">' +
                        '<i class="paper plane icon"></i></button></div>'
                    );
                    initializeSendButton();
                }
            } else {
                $('#testSendEmail').closest('.item').remove();
            }
        }
    });
}

function startEmailCheck(initialSendId) {
    let checkInterval = setInterval(function () {
        $.ajax({
            url: 'ajax/check_sending_status.php',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (!response.isStillSending) {
                    clearInterval(checkInterval);

                    // Setze Button zurück
                    const $button = $('#testSendEmail');
                    const $icon = $button.find('i');
                    $icon.removeClass('sync alternate spinning').addClass('paper plane');
                    $button.removeClass('disabled');

                    // Zeige Abschlussmeldung
                    $('body').toast({
                        title: 'E-Mail Versand abgeschlossen',
                        message: 'Alle E-Mails wurden verarbeitet',
                        class: 'success',
                        showProgress: 'bottom',
                        displayTime: 4000
                    });

                    // Aktualisiere UI
                    reloadTable();
                    checkPendingEmails();
                }
            }
        });
    }, 2000); // Prüfe alle 2 Sekunden
}

function initializeSendButton() {
    $('#testSendEmail').off('click').on('click', function () {
        var $button = $(this);
        var $icon = $button.find('i');

        // Button-Zustand ändern und rotierendes Icon anzeigen
        $button.addClass('disabled');
        $icon.removeClass('paper plane').addClass('sync alternate spinning');

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
                        displayTime: 4000
                    });

                    // Starte Überprüfung des Versandstatus
                    startEmailCheck(response.send_id);
                } else {
                    // Fehlerbehandlung
                    $('body').toast({
                        title: 'Fehler beim E-Mail Versand',
                        message: 'Der Versand konnte nicht gestartet werden.',
                        class: 'error',
                        showProgress: 'bottom',
                        displayTime: 4000
                    });

                    // Setze Button zurück
                    $icon.removeClass('sync alternate spinning').addClass('paper plane');
                    $button.removeClass('disabled');
                }
            },
            error: function () {
                // Verbindungsfehler
                $('body').toast({
                    title: 'Verbindungsfehler',
                    message: 'Fehler bei der Verbindung zum Server',
                    class: 'error',
                    showProgress: 'bottom',
                    displayTime: 4000
                });

                // Setze Button zurück
                $icon.removeClass('sync alternate spinning').addClass('paper plane');
                $button.removeClass('disabled');
            }
        });
    });
}

$(document).ready(function () {
    // Prüfe alle 30 Sekunden auf neue E-Mails
    setInterval(checkPendingEmails, 30000);

    // Initialer Check
    checkPendingEmails();
});