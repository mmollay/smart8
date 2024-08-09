<?

// Datenbankverbindung
$db = new mysqli('localhost', 'root', 'Jgewl21;', 'demo');

if ($db->connect_error) {
    die("Verbindung fehlgeschlagen: " . $db->connect_error);
}