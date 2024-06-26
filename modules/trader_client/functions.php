<?php

function getBrokerUserByClientId($db, $clientId)
{
    // Überprüfen, ob $clientId gültig ist
    if (!isset($clientId) || $clientId <= 0) {
        return null; // Ungültige client_id
    }

    // Erweiterte SQL-Abfrage, um positive_multiplier und negative_multiplier einzuschließen
//    $sql = "SELECT c.account, c.positive_multiplier, c.negative_multiplier FROM clients AS c WHERE c.client_id = ? LIMIT 1";
    $sql = "SELECT d.account, d.positive_multiplier, d.negative_multiplier FROM deposits AS d WHERE d.client_id = ? LIMIT 1";

    // Prepared Statement vorbereiten
    if ($stmt = $db->prepare($sql)) {
        // Parameter binden und SQL-Abfrage ausführen
        $stmt->bind_param("i", $clientId);
        $stmt->execute();

        // Ergebnis abrufen
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Benutzername (user) und Multiplikatoren auslesen
            $account = $row['account'];

            $positiveMultiplier = $row['positive_multiplier'];
            $negativeMultiplier = $row['negative_multiplier'];

            // Statement schließen
            $stmt->close();

            // Rückgabe als Array
            return [
                'account' => $account,
                'positive_multiplier' => $positiveMultiplier,
                'negative_multiplier' => $negativeMultiplier
            ];
        } else {
            // Statement schließen
            $stmt->close();

            // Kein Benutzer gefunden
            return "Kein Broker-User oder Multiplikatoren für die gegebene client_id gefunden.";
        }
    } else {
        // Fehler beim Vorbereiten des Statements
        return "Fehler beim Vorbereiten der SQL-Abfrage.";
    }

}



function getUserDetails($userId)
{
    global $db; // Verwenden der globalen Datenbankverbindung

    // Die SQL-Abfrage vorbereiten
    $stmt = $db->prepare("SELECT first_name, last_name FROM clients WHERE client_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc(); // Gibt die Nutzerdaten zurück
    } else {
        return null; // Kein Nutzer gefunden
    }
}
