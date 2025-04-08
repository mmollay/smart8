<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE);
error_reporting(1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	include_once('../config/config.php');
	$filename = $root . $_POST['url'];
	
	// Überprüfen, ob die Datei existiert
	if (!file_exists($filename)) {
		die("Fehler: Die Datei '$filename' existiert nicht.");
	}

	// Überprüfen, ob die Datei lesbar ist
	if (!is_readable($filename)) {
		die("Fehler: Die Datei '$filename' kann nicht gelesen werden.");
	}

	// Debug-Information ausgeben
	echo "Versuche, Bild zu laden: $filename<br>";
	echo "Dateityp: " . strtolower(pathinfo($filename, PATHINFO_EXTENSION)) . "<br>";

	// Determine the image type based on the file extension
	$imageType = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

	// Load the image based on its type
	$image = false;
	switch ($imageType) {
		case 'jpeg':
		case 'jpg':
			$image = @imagecreatefromjpeg($filename);
			break;
		case 'png':
			$image = @imagecreatefrompng($filename);
			break;
		case 'gif':
			$image = @imagecreatefromgif($filename);
			break;
		case 'bmp':
			$image = @imagecreatefrombmp($filename);
			break;
		case 'webp':
			$image = @imagecreatefromwebp($filename);
			break;
		default:
			die("Nicht unterstütztes Bildformat: $imageType");
	}

	// Überprüfen, ob das Bild erfolgreich geladen wurde
	if ($image === false) {
		die("Fehler: Das Bild konnte nicht geladen werden. Möglicherweise ist die Datei beschädigt oder hat ein ungültiges Format.");
	}

	// Check if the 'direction' parameter is set
	$direction = isset($_POST['direction']) ? $_POST['direction'] : 'right';

	// Determine the angle of rotation
	$angle = ($direction === 'right') ? -90 : 90;

	// Perform the rotation
	$rotatedImage = imagerotate($image, $angle, 0);

	// Ressourcen freigeben
	imagedestroy($image);

	// Save the rotated image based on its type
	switch ($imageType) {
		case 'jpeg':
		case 'jpg':
			imagejpeg($rotatedImage, $filename, 100);
			break;
		case 'png':
			imagepng($rotatedImage, $filename);
			break;
		case 'gif':
			imagegif($rotatedImage, $filename);
			break;
		case 'bmp':
			imagebmp($rotatedImage, $filename);
			break;
		case 'webp':
			imagewebp($rotatedImage, $filename);
			break;
	}

	// Ressourcen freigeben
	imagedestroy($rotatedImage);

	// Cache-Verzeichnis für Thumbnails identifizieren
	$thumbCacheDir = '../cache/images';
	if (!is_dir($thumbCacheDir)) {
		// Versuche alternative Cache-Verzeichnisse
		$possibleCacheDirs = [
			'../cache/images',
			'../../_files/cache/images',
			'../_files/cache/images',
			'../cache',
			'../_files/cache'
		];
		
		foreach ($possibleCacheDirs as $dir) {
			if (is_dir($dir)) {
				$thumbCacheDir = $dir;
				break;
			}
		}
	}
	
	// Dateiname ohne Pfad extrahieren
	$fileBasename = basename($filename);
	
	// Cache-Dateien suchen und löschen, die mit dem Basisnamen des Bildes beginnen
	if (is_dir($thumbCacheDir)) {
		$cacheFiles = glob($thumbCacheDir . '/*' . $fileBasename . '*');
		if ($cacheFiles) {
			foreach ($cacheFiles as $cacheFile) {
				if (is_file($cacheFile)) {
					@unlink($cacheFile);
				}
			}
		}
	}
	
	// Setze eventuell vorhandenen Cache-Timestamp zurück
	// Berühre die original Datei, um das Änderungsdatum zu aktualisieren
	touch($filename);
	
	// Aktualisiere auch den Zeitstempel des übergeordneten Verzeichnisses, um den Verzeichnis-Cache zu invalidieren
	// Dies ist der gleiche Mechanismus, der beim Löschen oder Hinzufügen von Dateien verwendet wird
	@touch(dirname($filename));
	
	// Erfolg melden
	echo "Das Bild wurde erfolgreich rotiert und der Cache aktualisiert.";
}
?>