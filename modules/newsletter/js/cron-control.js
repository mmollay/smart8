function startCronProcess() {
    const btn = $('#startCronBtn');
    btn.addClass('loading disabled');

    $.ajax({
        url: 'ajax/start_cron.php',
        method: 'POST',
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                $('body').toast({
                    class: 'success',
                    message: 'Newsletter-Versand wurde gestartet',
                    showProgress: 'bottom',
                    displayTime: 3000
                });
            } else {
                $('body').toast({
                    class: 'error',
                    message: response.error || 'Fehler beim Starten des Versands',
                    showProgress: 'bottom',
                    displayTime: 3000
                });
            }
        },
        error: function () {
            $('body').toast({
                class: 'error',
                message: 'Verbindungsfehler',
                showProgress: 'bottom',
                displayTime: 3000
            });
        },
        complete: function () {
            setTimeout(() => {
                btn.removeClass('loading disabled');
            }, 3000);
        }
    });
}
