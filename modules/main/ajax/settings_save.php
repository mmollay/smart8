<?
include (__DIR__ . '/../../../config.php');
header('Content-Type: application/json');
// Stellen Sie sicher, dass $mysqli die aktive und korrekt konfigurierte Datenbankverbindung ist.

// Formulardaten empfangen (normalerweise von einem POST-Request)
$client_id = isset($_POST['update_id']) ? (int) $_POST['update_id'] : 0;
$first_name = $mysqli->real_escape_string($_POST['first_name']);
$last_name = $mysqli->real_escape_string($_POST['last_name']);
$phone = $mysqli->real_escape_string($_POST['phone']);
$street = $mysqli->real_escape_string($_POST['street']);
$zip = $mysqli->real_escape_string($_POST['zip']);
$country = $mysqli->real_escape_string($_POST['country']);
$company = $mysqli->real_escape_string($_POST['company']);

// Überprüfen, ob die client_id gültig ist
if ($client_id === 0) {
    echo json_encode(['error' => 'Ungültige client_id.']);
    exit;
}

// SQL-Statement zum Aktualisieren der Daten
$sql = "UPDATE user2company SET 
        firstname = '$first_name', 
        secondname = '$last_name', 
        telefon = '$phone', 
        street = '$street', 
        zip = '$zip', 
        country = '$country', 
        company1 = '$company' 
        WHERE user_id = $client_id";


// Ausführung des SQL-Statements
if ($mysqli->query($sql) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'Daten erfolgreich aktualisiert.']);
} else {
    echo json_encode(['success' => false, 'message' => "Fehler bei der Aktualisierung: " . $mysqli->error]);
}

// Schließen der Datenbankverbindung
$mysqli->close();
?>