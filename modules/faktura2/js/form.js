// Funktion für die Verarbeitung von Kontoformularen
function after_form_account(response) {
    if (response.status === 'success') {
        // Schließe das Modal
        $('.ui.modal').modal('hide');

        // Aktualisiere die Kontenliste
        reloadTable();

        // Zeige eine Erfolgsmeldung
        showMessage('Erfolg', 'Das Konto wurde erfolgreich gespeichert.', 'success');
    } else {
        // Zeige eine Fehlermeldung
        showMessage('Fehler', 'Es gab ein Problem beim Speichern des Kontos: ' + response.message, 'error');
    }
}

// Funktion für die Verarbeitung von Lieferantenformularen
function after_form_supplier(response) {
    if (response.status === 'success') {
        $('.ui.modal').modal('hide');
        reloadTable();
        showMessage('Erfolg', 'Der Lieferant wurde erfolgreich gespeichert.', 'success');
    } else {
        showMessage('Fehler', 'Es gab ein Problem beim Speichern des Lieferanten: ' + response.message, 'error');
    }
}

// Funktion für die Verarbeitung von Artikelformularen
function after_form_article(response) {
    if (response.status === 'success') {
        $('.ui.modal').modal('hide');
        reloadTable();
        showMessage('Erfolg', 'Der Artikel wurde erfolgreich gespeichert.', 'success');
    } else {
        showMessage('Fehler', 'Es gab ein Problem beim Speichern des Artikels: ' + response.message, 'error');
    }
}

// Funktion für die Verarbeitung von Rechnungsformularen
function after_form_invoice(response) {
    if (response.status === 'success') {
        $('.ui.modal').modal('hide');
        reloadTable();
        showMessage('Erfolg', 'Die Rechnung wurde erfolgreich gespeichert.', 'success');
    } else {
        showMessage('Fehler', 'Es gab ein Problem beim Speichern der Rechnung: ' + response.message, 'error');
    }
}

// Funktion für die Verarbeitung von Ausgabenformularen
function after_form_expense(response) {
    if (response.status === 'success') {
        $('.ui.modal').modal('hide');
        reloadTable();
        showMessage('Erfolg', 'Die Ausgabe wurde erfolgreich gespeichert.', 'success');
    } else {
        showMessage('Fehler', 'Es gab ein Problem beim Speichern der Ausgabe: ' + response.message, 'error');
    }
}

//after_form_customer
function after_form_customer(response) {
    if (response.status === 'success') {
        $('.ui.modal').modal('hide');
        reloadTable();
        showMessage('Erfolg', 'Der Kunde wurde erfolgreich gespeichert.', 'success');
    } else {
        showMessage('Fehler', 'Es gab ein Problem beim Speichern des Kunden: ' + response.message, 'error');
    }
}


// Hilfsfunktion zum Anzeigen von Nachrichten
// Annahme: Sie haben bereits eine showMessage Funktion implementiert
// Falls nicht, hier ein Beispiel mit Semantic UI:
function showMessage(title, message, type) {
    $('body').toast({
        title: title,
        message: message,
        class: type,
        showProgress: 'bottom'
    });
}
