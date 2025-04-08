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
	placeholderImages.forEach(function(image) {
		image.remove(); // Entfernt das Element aus dem DOM
	});
}

// Event-Listener-Funktion, die auf den Button-Klick reagiert
function onPopupButtonClick(event) {
	// Überprüfe, ob das geklickte Element die richtige Klasse und das Datenattribut hat
	if (event.target.classList.contains('dropdown-item') && event.target.getAttribute('data-action') === 'popup') {
		//removePlaceholderImages();
		setTimeout(function() {
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
	xhr.onreadystatechange = function() {
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

    // Behandlung der Bilder im 'files-container'
    var container = document.getElementById('files-container');
    var images = container.getElementsByTagName('img');
    
    for (var i = 0; i < images.length; i++) {
        if (images[i].src.includes(encodedBasename)) {
            var url = new URL(images[i].src, window.location.href);
            var randomValue = Math.random().toString(36).substring(2, 15); // Generieren eines neuen zufälligen Werts

            // Hinzufügen des zufälligen Werts als Query-Parameter
            url.searchParams.append('random', randomValue);

            images[i].src = url.href; // Ändern des src-Attributs des img-Elements
        }
    }

    // Aktualisieren der Bilder mit der Klasse 'pswp__img'
    var pswpImages = document.getElementsByClassName('pswp__img');
    updateImagesSrc(pswpImages, encodedBasename);

    if (isPopupOpen()) {
        location.reload();
    }
}




