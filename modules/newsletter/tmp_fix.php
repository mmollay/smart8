<?php
// Lese die Datei ein
$content = file_get_contents("n_config.php");

// Suche nach der Zeile mit dem uploadBasePath und ersetze sie
$search = "//upload path für Attachements der Emails 
\$uploadBasePath = \$_ENV[\"UPLOAD_PATH\"] . \"/\" . \$_SESSION[\"user_id\"] . \"/newsletters\";";

$replace = "//upload path für Attachements der Emails 
// Im CLI-Modus verwenden wir einen Standard-Pfad
if (\$isCliMode) {
    \$uploadBasePath = \$_ENV[\"UPLOAD_PATH\"] . \"/cli/newsletters\";
} else {
    \$uploadBasePath = \$_ENV[\"UPLOAD_PATH\"] . \"/\" . \$_SESSION[\"user_id\"] . \"/newsletters\";
}";

// Ersetze den Text
$content = str_replace($search, $replace, $content);

// Speichere die Datei
file_put_contents("n_config.php", $content);

echo "n_config.php wurde erfolgreich aktualisiert.";
?>
