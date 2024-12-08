<?php
require_once(__DIR__ . '/../n_config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['data'])) {
    $db->begin_transaction();
    try {
        // Zeilen aufteilen
        $lines = explode("\n", trim($_POST['data']));
        $imported = 0;
        $duplicates = 0;
        $errors = [];

        // Prepare Statements
        $checkStmt = $db->prepare("
            SELECT id FROM blacklist 
            WHERE email = ? AND user_id = ?
        ");

        $insertStmt = $db->prepare("
            INSERT INTO blacklist
            (email, reason, created_at, source, user_id)
            VALUES (?, ?, ?, 'manual', ?)
        ");

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line))
                continue;

            $data = explode("\t", $line);
            if (count($data) >= 3) {
                $email = trim($data[0]);
                $reason = trim($data[1]);
                $created_at = trim($data[2]);

                // Prüfe auf Duplikat
                $checkStmt->bind_param("si", $email, $userId);
                $checkStmt->execute();
                $result = $checkStmt->get_result();

                if ($result->num_rows > 0) {
                    $duplicates++;
                    continue;
                }

                // Füge neuen Eintrag hinzu
                $insertStmt->bind_param("sssi", $email, $reason, $created_at, $userId);
                if ($insertStmt->execute()) {
                    $imported++;
                } else {
                    $errors[] = "Fehler bei E-Mail $email: " . $insertStmt->error;
                }
            }
        }

        $db->commit();

        // Detaillierte Erfolgsmeldung
        $message = [];
        if ($imported > 0) {
            $message[] = "$imported Einträge erfolgreich importiert";
        }
        if ($duplicates > 0) {
            $message[] = "$duplicates Einträge übersprungen (bereits vorhanden)";
        }

        $messageText = implode(", ", $message);

        if (!empty($errors)) {
            $messageText .= "<br>Fehler: " . implode("<br>", $errors);
        }

    } catch (Exception $e) {
        $db->rollback();
        $messageText = "Fehler beim Import: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Blacklist Import</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
</head>

<body>
    <div class="ui container" style="padding: 20px;">
        <a href="../" class="ui left labeled icon button">
            <i class="left arrow icon"></i>
            Zurück zur Übersicht
        </a>

        <h2 class="ui header">Blacklist Daten Import</h2>

        <?php if (isset($messageText)): ?>
            <div class="ui <?php echo strpos($messageText, 'Fehler') !== false ? 'negative' : 'positive'; ?> message">
                <?php echo $messageText; ?>
            </div>
        <?php endif; ?>

        <form class="ui form" method="post">
            <div class="field">
                <label>Daten einfügen (Format: E-Mail TAB Grund TAB Datum)</label>
                <textarea name="data" rows="10"
                    placeholder="email@domain.com&#9;Grund&#9;2024-12-02 22:49:44"></textarea>
            </div>
            <button class="ui primary button" type="submit">Importieren</button>
        </form>
    </div>
</body>

</html>