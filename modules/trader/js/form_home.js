$(document).ready(() => {
	loadEmaForm();
	updateServerInfo(); // Initialer Aufruf, um die Profite beim Laden der Seite zu aktualisieren
});

// Aktualisiere die Profite alle 1 sec
if (typeof window.serverInfoInterval === 'undefined') {
	window.serverInfoInterval = setInterval(updateServerInfo, 10000);
}
