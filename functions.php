<?
function getUserDetails($userId, $db)
{
    // Bereiten Sie das SQL-Statement vor
    $stmt = $db->prepare("SELECT * FROM user2company WHERE user_id = ?");

    // Binden Sie die Parameter an das vorbereitete Statement
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    // Holen Sie das Ergebnis des ausgeführten Statements
    $result = $stmt->get_result();

    // Überprüfen Sie, ob Zeilen im Ergebnis vorhanden sind
    if ($result->num_rows > 0) {
        // Holen Sie die Daten des Nutzers als assoziatives Array
        $user = $result->fetch_assoc();
        return $user; // Gibt das komplette Nutzer-Array zurück
    } else {
        return null; // Kein Nutzer gefunden
    }
}



// Read Value from Table
function mysql_singleoutput($sql, $indexColumn = false)
{
    $query = $GLOBALS['mysqli']->query($sql) or die(mysqli_error($GLOBALS['mysqli']));
    $array = mysqli_fetch_array($query);
    if ($indexColumn)
        return $array[$indexColumn];
    else
        return $array[0];
}


// Wandelt deutsches Format in englisches um
function nr_format2english($wert1)
{
    if ($wert1)
        return preg_replace("/,/", '.', $wert1);
}