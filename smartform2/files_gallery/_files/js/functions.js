// Funktion zum Aktualisieren der src-Attribute von <img> und href-Attribute von <a> mit bestimmten Klassen
function updateImageSources() {
	// Aktualisiere <img> Elemente
	var imgElements = document.querySelectorAll('.pswp__img');
	updateAttribute(imgElements, 'src');

	// Aktualisiere <a> Elemente
	var aElements = document.querySelectorAll('.files-a-img');
	updateAttribute(aElements, 'href');
}

// Hilfsfunktion zum Aktualisieren eines Attributs (src oder href) für eine NodeList von Elementen
function updateAttribute(elements, attribute) {
	for (var i = 0; i < elements.length; i++) {
		var element = elements[i];
		var value = element[attribute];

		// Entferne bestehende Zeitstempel aus der URL
		var valueWithoutTimestamp = value.split('?')[0];

		// Füge einen neuen Zeitstempel hinzu
		var timestamp = new Date().getTime();
		element[attribute] = valueWithoutTimestamp + '?timestamp=' + timestamp;
	}
}


// Hilfsfunktion zum Aktualisieren der src-Attribute und Tauschen von Breite und Höhe im Style-Attribut
function updateImagesSrc(images, encodedBasename) {

	for (var i = 0; i < images.length; i++) {
		// Überprüfen, ob das Bild existiert und eine src-Eigenschaft hat
		if (images[i] && images[i].src && images[i].src.includes(encodedBasename)) {
			var oldSrc = images[i].src.split('?')[0]; // Aufteilen der URL am '?' und Beibehalten des ersten Teils
			var timestamp = new Date().getTime(); // Generieren eines Zeitstempels
			images[i].src = oldSrc + '?timestamp=' + timestamp; // Hinzufügen des Zeitstempels zum src-Attribut
		}
	}
}


// Funktion zum Entfernen aller Elemente mit der Klasse 'pswp__img--placeholder'
function removePlaceholderImages() {
	var placeholderImages = document.querySelectorAll('.pswp__img');
	placeholderImages.forEach(function (image) {
		image.remove(); // Entfernt das Element aus dem DOM
	});
}

// Event-Listener-Funktion, die auf den Button-Klick reagiert
function onPopupButtonClick(event) {
	// Überprüfe, ob das geklickte Element die richtige Klasse und das Datenattribut hat
	if (event.target.classList.contains('dropdown-item') && event.target.getAttribute('data-action') === 'popup') {
		//removePlaceholderImages();
		setTimeout(function () {
			updateImageSources();
			//window.location.reload(true);
		}, 300);
	}
}

// Füge dem gesamten Dokument einen Event Listener für das Klick-Event hinzu
document.addEventListener('click', onPopupButtonClick);


//Übergibt den Link des Images
function sendUrl(item) {
	if (window.parent.$) {
		console.log(item);
		var url = item.url_path;
		var id = window.parent.$('#hiddenVariable').val();
		window.top.$("#" + id).val(url).focus();
		window.parent.$('#flyout_finder').flyout('hide');
	}
}


// Function to rotate an image
function rotate(item, direction) {
	var url = item.url_path; // The URL path of the image
	var basename = item.basename; // The base name of the image file

	// Creating an AJAX request
	var xhr = new XMLHttpRequest();
	xhr.open("POST", "_files/plugins/image_rotate.php", true); // Replace '_files/plugins/image_rotate.php' with the path to your PHP script
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.onreadystatechange = function () {
		if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
			// Here you can process the server's response, e.g., update the image
			//location.reload();
			updateImageSourceIfFilenameFound(basename);
			updateImageSources();
		}
	};
	// Sending the data to the PHP script, including the direction
	xhr.send("url=" + encodeURIComponent(url) + "&direction=" + encodeURIComponent(direction));
}

// Funktion, um zu prüfen, ob die Popup-Klasse vorhanden ist
function isPopupOpen() {
	return document.documentElement.classList.contains('popup-open');
}

function updateImageSourceIfFilenameFound(basename) {
	var encodedBasename = encodeURIComponent(basename); // Kodieren des Basenamens
	
	// Für alle Bilder im 'files-container'
	var container = document.getElementById('files-container');
	if (container) {
		var images = container.querySelectorAll('img.files-img');
		var links = container.querySelectorAll('a.files-a-img');
		
		// Aktualisiere alle img.files-img Elemente
		for (var i = 0; i < images.length; i++) {
			var img = images[i];
			if (img.dataset.src && img.dataset.src.includes(encodedBasename)) {
				// Vollständig neue URL ohne Cache-Parameter generieren
				var oldSrc = img.dataset.src;
				var filePart = oldSrc.split('&resize=')[0]; // Behalte nur den Dateinamen-Teil
				var resizePart = oldSrc.match(/&resize=\d+/);
				
				if (resizePart) {
					// Timestamp zur Umgehung des Browser-Caches
					var timestamp = new Date().getTime();
					var newSrc = filePart + resizePart[0] + '&t=' + timestamp;
					
					// Beide Attribute aktualisieren: data-src und src
					img.dataset.src = newSrc;
					img.src = newSrc;
					
					// Bei Bildern in der Galerie auch width/height anpassen für gedrehte Bilder
					// Wenn das Bild gedreht wurde, könnten Breite und Höhe getauscht werden
					if (img.parentElement && img.parentElement.style.getPropertyValue('--ratio')) {
						// Überprüfen ob es ein 1:1 Verhältnis ist (quadratisches Bild)
						var ratio = parseFloat(img.parentElement.style.getPropertyValue('--ratio'));
						if (ratio !== 1) {
							// Tausche Breite und Höhe
							var tempWidth = img.width;
							img.width = img.height;
							img.height = tempWidth;
							
							// Aktualisiere das Verhältnis im Parent-Element
							img.parentElement.style.setProperty('--ratio', 1/ratio);
						}
					}
				}
			}
		}
		
		// Aktualisiere auch die Links zu den Bildern
		for (var j = 0; j < links.length; j++) {
			var link = links[j];
			if (link.dataset.path && link.dataset.path.includes(basename)) {
				// Timestamp hinzufügen, um Cache zu umgehen
				var timestamp = new Date().getTime();
				var href = link.getAttribute('href').split('?')[0];
				link.setAttribute('href', href + '?t=' + timestamp);
			}
		}
	}
	
	// Aktualisiere Bilder im PhotoSwipe Popup (falls geöffnet)
	var pswpContainer = document.querySelector('.pswp__container');
	if (pswpContainer) {
		var pswpImages = pswpContainer.querySelectorAll('.pswp__img');
		for (var k = 0; k < pswpImages.length; k++) {
			var pswpImg = pswpImages[k];
			if (pswpImg.src && pswpImg.src.includes(encodedBasename)) {
				// Neuen Zeitstempel hinzufügen, um Cache zu umgehen
				var timestamp = new Date().getTime();
				pswpImg.src = pswpImg.src.split('?')[0] + '?t=' + timestamp;
			}
		}
	}
	
	// Wenn ein Popup geöffnet ist, Seite neu laden nach kurzer Verzögerung
	if (isPopupOpen()) {
		setTimeout(function() {
			location.reload();
		}, 500);
	}
}
