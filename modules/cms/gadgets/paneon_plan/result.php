<?php
include ('array.php');
function get_values4email($head, $array) {
	foreach ( $array as $key => $title ) {

		$value = $_POST [$key];

		if (! $title)
			$title = $key;
		$td .= "<tr><td>$title</td><td>$value</td></tr>";
	}

	if ($head && $td)
		$td_head = "<tr><td colspan=2><b>$head</b></td></tr>";

	return $td_head . $td;
}

//foreach ( $_POST as $key => $value ) {}

// Auslesen, Anzahl der Checkboxfelder Ausgabe der "Stufe" nach Auswertung!
foreach ( $array_title as $key1 => $value ) {
	foreach ( $array_value [$key1] as $key => $value ) {
		if ($_POST [$key]) {
			$krankheit [$key1] .= $value . ",";
			${$key1} ++;
			$stufe = $array_level [$key1];
		}
	}
}

// Wandelt $array in $varable um: $array_result['text']['1'] => $text
foreach ( $array_result as $key => $value ) {
	${$key} = $array_result [$key] [$stufe];
}
echo "<div align =left>";
echo "<br><div class='ui icon $color huge message'><i class='$icon icon'></i> <div class='content'><div class='header'>$text</div>$info</div></div>";

if ($video) {
	echo "<div  class='ui small embed ' data-source='youtube' data-id='$video'></div>";
	echo "<script>$('.ui.embed').embed();</script>";
}

//if ($stufe >= 3)
//	echo "<div class='ui button'>Produktempfehlung: KOMBI-Set (AKTIV Pulver & PLUS Kapseln)</div>";

echo "</div>";

/**
 * ************************************
 * Emailtext
 * ************************************
 */

$td .= get_values4email ( 'Kunde', array (
		'user_name' => 'Name',
		'user_email' => 'Email',
		'user_comment' => 'Nachricht'
) );

foreach ( $arr ['field'] as $key => $value ) {
	$array_tier [$key] = $value ['label'];
}
$td .= get_values4email ( 'Tier', $array_tier );


$td .= "<tr><td colspan=2><b>Krankheiten</b></td></tr>";

foreach ( $array_title as $key1 => $value ) {
	if ($krankheit [$key1])
		$td .= "<tr><td>$value</td><td>" . $krankheit [$key1] . "</td></tr>";
	if ($_POST ['comment' . $key1])
		$td .= "<tr><td colspan=2>" . $_POST ['comment' . $key1] . "</td></tr>";
}

echo "<div class='ui header'>Email an xxx</div>";
echo "<table class='ui very basic  celled table small celled striped very compact'>";
echo "<tbody>$td</tbody>";

echo "</table><br><br>";

exit ();

// Vorlage Seite:
// https://www.anifit.at/content/tierfutter/ernaehrung/futter_rechner/berechnung_der_f%C3%BCtterungsempfehlung/

// Der Richtwert pro Tag und kg Körpergewicht beträgt: 20g ab 20 kg, unter 20 kg: 25g, unter 10 kg: 25-30g, unter 5kg 30-40g
// Es gilt dabei nicht das derzeitige Körpergewicht, sondern das Zielgewicht, welches er haben sollte.

// Der individuelle Bedarf ist je nach Rasse und Aktivität unterschiedlich.
// Richtwert: 40-60 g/kg Körpergewicht, große Rassen (ab ca. 6 kg) oder zumAbnehmen eher 40g/kg

// INPUT
$val ['animal'] = 'dog'; // cat
$val ['animal_weight_real'] = '10kg';
$val ['animal_weight_optimal'] = '15kg';
$val ['animal_weight_age'] = '10';
$val ['animal_race'] ['cat'] = 'small'; // big //Persa
$val ['animal_race'] ['dog'] = 'Rasse'; // big
$val ['animal_special'] = 'Allergie'; // big
$val ['activity'] = 'comfortable'; // active, extreme, sporty
$val ['user_name'] = 'Martin';
$val ['user_email'] = 'martin@ssi.at';
$val ['user_tel'] = '0650 0000000';

$info_box = 'Gewünschtes Gewicht';

// Empfänger für Auswertung
$submit_email = 'post@paneon.net';

// BERECHNUNG
switch ($val ['animal']) {
	case "dog" :

		if ($val ['animal_weight_optimal'] >= 20) {
			$gram_per_kg = '20';
		} elseif ($val ['animal_weight_optimal'] < 20) {
			$gram_per_kg = '25';
		} elseif ($val ['animal_weight_optimal'] < 10) {
			$gram_per_kg = '30';
		} elseif ($val ['animal_weight_optimal'] < 5) {
			$gram_per_kg = '40';
		}

	case "cat" :
		if ($animal_race ['cat'] == 'big') {
			$gram_per_kg = '50';
		} else if ($animal_race ['cat'] == 'small') {
			$gram_per_kg = '40';
		}
}

$food_weight = $gram_per_kg * $val ['animal_weight_optimal'];

// OUTPUT
echo "<div align=center>";

echo "Hier bekommst Du Deine individuelle Beratung";
echo "<br>BUTTON WEITER";

echo "<hr style='color:red'><p><div style='color:red'>----NEXT  PAGE----</div><p>";

echo "<div style='width:300px; border:1px solid red; text-align:left;'>";
foreach ( $val as $key => $value ) {
	echo "INTPUT: $key => $value<br>";
}
echo "</div>";
echo "<br>BUTTON SENDEN";

echo "<hr style='color:red'><p><div style='color:red'>----NEXT  PAGE----</div><p>";

echo "1. Futtermenge bei <b>$animal</b> Optimalgewichtt: " . $val ['animal_weight_optimal'] . " (derzeit: $animal_weight_real) : $food_weight g<br>";
echo "2. Tipps (kommt von Martin)<br>";
echo "3. Mail an $submit_email";
echo "4. Datenbank";
echo "<br><hr>";
echo "(click) JETZT BESTELLEN";
echo "</div>";
