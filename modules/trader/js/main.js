function loadScriptIfNotAlreadyLoaded(src) {

    let scripts = document.getElementsByTagName('script');
    for (let i = 0; i < scripts.length; i++) {

        if (scripts[i].getAttribute('src') == src) {
            console.log('Skript ist bereits geladen:', src);
            return; // Skript ist bereits geladen, breche ab
        }
    }

    // Wenn das Skript nicht gefunden wurde, lade es
    let script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = src;
    document.head.appendChild(script);
    console.log('Skript geladen:', src);
}


function showToast(message, type = 'info', persist = false) {
    const toastSettings = {
        class: type, // Vereinfachte Zuweisung basierend auf dem 'type'
        message: message,
        position: 'top right',
        displayTime: persist ? 0 : 5000, // Wenn persist wahr ist, verschwindet der Toast nicht automatisch
        closeIcon: true,
        opacity: 0.9
    };

    // Entfernt alle bestehenden Toasts vor dem Anzeigen eines neuen, wenn persistiert werden soll
    if (persist) {
        $('.toast-box').remove();
    }

    $('body').toast(toastSettings);
}

// JavaScript-Code, um die Bestellungen zu holen und Toast-Nachrichten anzuzeigen
function fetchOrders() {

    // Zeige eine Benachrichtigung, dass der Ladevorgang begonnen hat
    showToast('Fetching orders...', 'info');

    // Deaktiviere den Button, um weitere Klicks zu verhindern
    var fetchButton = document.querySelector("button[onclick='fetchOrders();']");
    fetchButton.disabled = true;

    $.ajax({
        url: 'exec/fetch_orders.php?token=52a36a36e2e6da849685b71f466dde56',
        type: 'GET',
        dataType: 'json', // Erwartet JSON als Rückgabetyp
        success: function (response) {
            console.log(response);
            // Verarbeite Erfolgsnachrichten
            if (response.data && response.data.length > 0) {
                response.data.forEach(function (item) {
                    showToast(item.message, item.type);
                });
            }
            // Verarbeite Fehlermeldungen
            if (response.errors && response.errors.length > 0) {
                response.errors.forEach(function (error) {
                    showToast(error.message, error.type);
                });
            }
            // Wenn keine URLs gefunden wurden oder ein anderer Fehler auftrat
            if (!response.success) {
                showToast('An operation error occurred.', 'error');
            }
        },
        error: function (xhr, status, error) {
            showToast('AJAX request failed: ' + error, 'error');
        },
        complete: function () {
            // Reaktiviere den Button, wenn der AJAX-Aufruf abgeschlossen ist
            fetchButton.disabled = false;
            // Optional: Aktualisiere/entferne die Benachrichtigung, dass der Ladevorgang abgeschlossen ist
            //showToast('Fetch operation complete.', 'success');
        }
    });
}

function generateToken() {
    // Zeige eine dauerhafte Informationsmeldung beim Start des Prozesses
    showToast('Token-Erzeugung gestartet...', 'info', true);

    $.ajax({
        url: 'exec/generate_token.php',
        type: 'POST',
        dataType: 'json',
        success: function (response) {
            console.log(response);
            if (response.success) {
                // Entferne alle Toasts und zeige eine Erfolgsmeldung
                $('.toast-box').remove();
                showToast(response.message, 'success');
            } else {
                // Entferne alle Toasts und zeige eine Fehlermeldung
                $('.toast-box').remove();
                showToast(response.message, 'error');
            }
        },
        error: function (xhr, status, error) {
            // Entferne alle Toasts und zeige eine Fehlermeldung
            $('.toast-box').remove();
            showToast('AJAX request failed: ' + error, 'error');
        }
    });
}

function impersonateUser(token) {

    // Prüfen, ob der Token ein gültiger hexadezimaler String der Länge 64 ist
    if (!/^[a-f0-9]{64}$/i.test(token)) {
        console.error('Ungültiger Token');
        return; // Beendet die Funktion frühzeitig, wenn der Token ungültig ist
    }

    const formData = new FormData();
    formData.append('token', token);

    fetch('../trader_client/user_impersonation.php', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (response.ok) {
                return response.text();
            }
            throw new Error('Etwas ist schief gelaufen beim Versuch, die Impersonation durchzuführen.');
        })
        .then(data => {
            console.log(data); // Erfolgsmeldung behandeln
            // Öffnet die Weiterleitung in einem neuen Fenster/Tab
            window.open('../trader_client/index.php', '_blank');
        })
        .catch(error => {
            console.error(error);
        });
}


function after_form_setting(json) {
    console.log(json); // Protokolliert das empfangene JSON im Konsolenfenster
    $('#modal_form_send').modal('hide');
    // Überprüfen, ob die Operation erfolgreich war
    if (json.message) {
        $('body').toast({
            class: 'success', // Fomantic-UI-Klassifizierung für Erfolgstextfarbe
            message: json.message, // Zeigt die Nachricht aus dem JSON
            position: 'top center', // Standardposition
            displayTime: 4000, // Anzeigedauer des Toasts in Millisekunden
        });
    } else if (json.success) {
        // Erfolgsmeldung anzeigen
        $('body').toast({
            class: 'success', // Fomantic-UI-Klassifizierung für Erfolgstextfarbe
            message: json.message, // Zeigt die Nachricht aus dem JSON
            position: 'top center', // Standardposition
            displayTime: 4000, // Anzeigedauer des Toasts in Millisekunden

        });
    } else {
        // Fehlermeldung anzeigen
        $('body').toast({
            class: 'error', // Fomantic-UI-Klassifizierung für Fehlertextfarbe
            message: json.message || 'Ein unbekannter Fehler ist aufgetreten.', // Zeigt die Fehlermeldung oder eine Standardmeldung
            position: 'top center', // Standardposition
            displayTime: 4000, // Anzeigedauer des Toasts in Millisekunden

        });
    }
}
