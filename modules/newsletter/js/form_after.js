$(document).ready(function () {
	$('#send_email').on('click', function (e) {
		e.preventDefault(); // Verhindert das Standard-Link-Verhalten

		// Hier Ihre Logik einfügen
		console.log('Newsletter senden wurde geklickt!');

		// Beispiel: Laden einer Seite via AJAX
		$.ajax({
			url: 'modules/newsletter/send_email.php',
			method: 'GET',
			success: function (response) {
				$('#pageContent').html(response);
			},
			error: function (xhr, status, error) {
				console.error('Ein Fehler ist aufgetreten:', error);
			}
		});
	});
});


function afterFormSubmit(id, customTitle = 'Data has been saved') {

	// Verwenden des customTitle, falls angegeben, sonst den Standardtitel
	if (id === 'ok') {
		$('#message').message({ status: 'info', title: customTitle });
		$('.ui.modal').modal('hide');
		table_reload();
	} else {
		$('#message').message({
			status: 'error', title: 'System error: ' + id
		});
	}
}

// Funktion zum Senden des Newsletters über AJAX Table Reload
function sendNewsletter(contentId) {
	$.ajax({
		url: 'ajax/send_newsletter.php',
		method: 'POST',
		data: { content_id: contentId },
		success: function (response) {
			try {
				var data = JSON.parse(response);
				if (data.status === 'success') {
					$('body').toast({
						message: 'Newsletter wird gesendet.',
						class: 'success'
					});
					table_reload();
				} else {
					$('body').toast({
						message: 'Fehler beim Senden des Newsletters: ' + data.message,
						class: 'error'
					});
				}
			} catch (e) {
				console.error('Error parsing JSON:', response);
				$('body').toast({
					message: 'Fehler beim Verarbeiten der Server-Antwort.',
					class: 'error'
				});
			}
		},
		error: function (xhr, status, error) {
			console.error('AJAX error:', status, error);
			$('body').toast({
				message: 'Fehler beim Senden der Anfrage.',
				class: 'error'
			});
		}
	});
}
