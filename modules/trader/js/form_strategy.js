
function after_form_setting(json) {
	console.log(json); // Protokolliert das empfangene JSON im Konsolenfenster
	$('#modal_form_send').modal('hide');
	// Überprüfen, ob die Operation erfolgreich war
	if (json.success) {
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