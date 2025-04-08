<?php
/*******************************************************************************
 * TOUR - Parameter für die Erklärung der Webseite und deren Nutzung
 /*******************************************************************************/
// $array_intro[number]['position']
$array_intro ['1'] ['bottom'] = "Die Toolbar brauchst du um an deiner Webseite schrauben zu k�nnen.";
$array_intro ['2'] ['right'] = "Hier kommst du zur�ck zur Haupseite";
$array_intro ['3'] ['right'] = "Wenn du die Webseite bearbeiten willst, muss das Schloss ge�ffnet sein.";
$array_intro ['4'] ['right'] = "Hier kannst du den Titel, Metatexte den Men�namen ver�ndern und die Seite l�schen.";
$array_intro ['5'] ['right'] = "Verwalten von Bildern, PDFs und mehr. Die hochgeladenen Deteien k�nnen danach in die Webseite eingepflegt werden.";
$array_intro ['6'] ['right'] = "Hier erfolgt das gestalten der Webseite. Von der Gr��e der Schrift bis dem w�hlen Hintergrundfarbe, gibt noch zahlreiche andere Funktionen zu entdecken. ";
$array_intro ['7'] ['right'] = "Ist ein individueller Platz ausserhalb des vorgegebenen Rahmens gew�nscht, kannst du hier einen Layer erstellen und beliebig positionieren.";
$array_intro ['8'] ['right'] = "Dieser Button gibt dir eine �bersicht der bestehenden Seiten. Ausserdem kannst du hier die Eigenschaften der Seite direkt bearbeiten.";
$array_intro ['9'] ['right'] = "Hier findest du eine Vielzahl von zus�tzlichen Modulen f�r die Gestaltung und Erweiterung deiner Webseite.<br>Hier eine kleine �bersicht:
		<ul>
		<li>weitere Textfelder</li>
		<li>Galerien</li>
		<li>Gästebuch</li>
		<li>Facebook-PlugIn</li>
		<li>uvm.</li>
		</ul>
		";

$array_intro ['10'] ['right'] = "Nach Bearbeitung der Webseite, brauchst du nur auf das Herz klicken und schon wird deine Webseite ver�ffentlicht.";
$array_intro ['11'] ['left'] = "Hier kannst du die Startseite definieren, und Google-Analytics einstellen";
$array_intro ['12'] ['left'] = "Wenn du eine sch�ne Webseite hast du gerne teilen willst, kannst du hier eine Vorlage erzeugen und diese zur Verf�gung stellen.";

// Auslesen und umwandeln für die Tour
foreach ( $array_intro as $key => $array_intro2 ) {
	foreach ( $array_intro2 as $position => $value ) {
		$value = preg_replace ( array (
				"/�/",
				"/�/",
				"/�/",
				"/�/",
				"/�/",
				"/�/",
				"/�/"
		), array (
				"&szlig;",
				"&ouml;",
				"&Ouml;",
				"&uuml;",
				"&Uuml;",
				"&auml;",
				"&Auml;"
		), $value );
		$intro [$key] = "data-step='$key' data-position='$position' data-intro='$value' ";
	}
}

$button_tour = "<span class=buttonset_tour>" . "<button id=button_tour onclick='javascript:introJs().start();' title='Erfahre mehr &uuml;ber die Nutzung des Smat-Kits'>Tour starten</button>" . "<button id=button_tour_close>Schlie&szlig;en</button>" . "</span>";
