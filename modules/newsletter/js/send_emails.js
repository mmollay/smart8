$(document).ready(function () {
    $('#testSendEmail').on('click', function () {
        var $button = $(this);

        // Button-Zustand ändern
        $button.addClass('loading disabled').text('Wird ausgeführt...');

        // AJAX-Aufruf zur Hintergrund-Ausführung
        $.ajax({
            url: 'exec/send_email_background.php',
            method: 'POST',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // Erfolgreiche Ausführung
                    $button.removeClass('blue loading').addClass('green')
                        .html('<i class="check icon"></i>Erfolgreich: ' + response.success_count + '/' + response.total_jobs);

                    // Detaillierte Informationen in der Konsole ausgeben
                    console.log('E-Mail-Versand abgeschlossen:', response);
                } else {
                    // Fehler bei der Ausführung
                    $button.removeClass('blue loading').addClass('red')
                        .html('<i class="exclamation triangle icon"></i>Fehler aufgetreten');
                }
                reloadTable();
            },
            error: function () {
                // Netzwerk- oder Server-Fehler
                $button.removeClass('blue loading').addClass('red')
                    .html('<i class="exclamation triangle icon"></i>Verbindungsfehler');
            },
            complete: function () {
                // Button nach 5 Sekunden zurücksetzen
                setTimeout(function () {
                    $button.removeClass('green red disabled')
                        .addClass('blue')
                        .html('<i class="paper plane icon"></i>E-Mail Versand testen');
                }, 5000);
            }
        });
    });
});
