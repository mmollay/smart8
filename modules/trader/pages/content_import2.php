<?php
require_once ('../t_config.php');

$setTEXT = $_POST['setTEXT'];

if (!$setTEXT) {
    echo "<br><br>Keine Daten zum Import vorhanden!";
    return;
}

$conn = new mysqli($cfg_mysql['server'], $cfg_mysql['user'], $cfg_mysql['password'], $cfg_mysql['db']);

// Überprüfen der Verbindung
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Zerlege den String in Zeilen
$lines = explode("\n", $setTEXT);
$importCount = 0;
$duplicateCount = 0;

foreach ($lines as $line) {
    $pattern = "/Buy :: (\d+) Sell :: (\d+) Time :: ([\d-]+\s[\d:]+) \+\d+ UTC Price ::([\d.]+)/";
    if (preg_match($pattern, $line, $matches)) {
        $buy = $matches[1];
        $sell = $matches[2];
        $time = $matches[3];
        $price = $matches[4];

        // Überprüfe, ob der Eintrag bereits existiert
        $stmt = $conn->prepare("SELECT COUNT(*) FROM stocks_data WHERE time = ? AND price = ?");
        $stmt->bind_param("sd", $time, $price);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_row();

        if ($row[0] == 0) {
            // Der Eintrag existiert noch nicht, füge ihn ein
            $insertStmt = $conn->prepare("INSERT INTO stocks_data (buy, sell, time, price) VALUES (?, ?, ?, ?)");
            $insertStmt->bind_param("iisd", $buy, $sell, $time, $price);
            $insertStmt->execute();
            $insertStmt->close();
            $importCount++;
        } else {
            // Der Eintrag ist ein Duplikat
            $duplicateCount++;
        }

        $stmt->close();
    }
}

echo "Es wurden $importCount Datensätze importiert. ";
echo "$duplicateCount Duplikate gefunden.";

// Verbindung schließen
$conn->close();
