
<?php
header ( 'Content-Type: text/html; charset=UTF-8' );
if (ob_get_level () == 0)
	ob_start ();

$text = $_POST ['firstname']; // Annahme: Der Text kommt aus einem Formular
if ($_POST ['secondname'])
	$text .= " " . $_POST ['secondname'];
$text .= ' is great! ';
for($i = 0; $i < strlen ( $text ); $i ++) {
	echo $text [$i];
	ob_flush ();
	flush ();
	usleep ( 500000 ); // Warte 0.5 Sekunden (500.000 Mikrosekunden)
}

echo "<br>Done.";
ob_end_flush ();
?>
