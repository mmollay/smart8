<?php
require_once(__DIR__ . '/../n_config.php');

// Funktion zum Säubern der Daten
function cleanImportData($data)
{
    // Email säubern
    $email = filter_var(trim($data[0]), FILTER_SANITIZE_EMAIL);

    // Grund säubern
    $reason = trim($data[1]);
    $reason = str_replace(['"', "'"], '', $reason); // Anführungszeichen entfernen
    $reason = preg_replace('/\s+/', ' ', $reason); // Mehrfache Leerzeichen entfernen
    $reason = htmlspecialchars_decode($reason); // HTML-Entities dekodieren
    $reason = utf8_encode($reason); // UTF-8 Kodierung sicherstellen

    // Datum säubern
    $date = trim($data[2]);
    if (!strtotime($date)) {
        $date = date('Y-m-d H:i:s'); // Fallback auf aktuelles Datum
    }

    return [
        'email' => $email,
        'reason' => $reason,
        'created_at' => $date
    ];
}

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
                // Daten säubern
                $cleanData = cleanImportData($data);

                if (!filter_var($cleanData['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Ungültige E-Mail: {$data[0]}";
                    continue;
                }

                // Prüfe auf Duplikat
                $checkStmt->bind_param("si", $cleanData['email'], $userId);
                $checkStmt->execute();
                $result = $checkStmt->get_result();

                if ($result->num_rows > 0) {
                    $duplicates++;
                    continue;
                }

                // Füge neuen Eintrag hinzu
                $insertStmt->bind_param(
                    "sssi",
                    $cleanData['email'],
                    $cleanData['reason'],
                    $cleanData['created_at'],
                    $userId
                );

                if ($insertStmt->execute()) {
                    $imported++;
                } else {
                    $errors[] = "Fehler bei E-Mail {$cleanData['email']}: " . $insertStmt->error;
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
    <style>
        .ui.container {
            padding: 20px;
            max-width: 800px !important;
        }

        textarea {
            font-family: monospace !important;
        }

        .ui.message {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="ui container">
        <a href="../" class="ui left labeled icon button">
            <i class="left arrow icon"></i>
            Zurück zur Übersicht
        </a>

        <h2 class="ui header">
            <i class="ban icon"></i>
            <div class="content">
                Blacklist Daten Import
                <div class="sub header">Importieren Sie mehrere E-Mail-Adressen gleichzeitig in die Blacklist</div>
            </div>
        </h2>

        <?php if (isset($messageText)): ?>
            <div class="ui <?php echo strpos($messageText, 'Fehler') !== false ? 'negative' : 'positive'; ?> message">
                <i class="<?php echo strpos($messageText, 'Fehler') !== false ? 'times' : 'check'; ?> circle icon"></i>
                <?php echo $messageText; ?>
            </div>
        <?php endif; ?>

        <form class="ui form" method="post">
            <div class="field">
                <label>
                    <i class="file text icon"></i>
                    Daten einfügen (Format: E-Mail TAB Grund TAB Datum)
                </label>
                <textarea name="data" rows="10"
                    placeholder="email@domain.com&#9;Grund&#9;2024-12-02 22:49:44"></textarea>
            </div>
            <div class="ui info message">
                <div class="header">Hinweise zum Import</div>
                <ul class="list">
                    <li>Trennen Sie die Felder mit TAB (Tabulator)</li>
                    <li>Jeder Eintrag muss in einer neuen Zeile stehen</li>
                    <li>Die E-Mail-Adresse wird automatisch validiert</li>
                    <li>Bereits vorhandene E-Mails werden übersprungen</li>
                    <li>Ungültiges Datum wird durch das aktuelle Datum ersetzt</li>
                </ul>
            </div>
            <button class="ui primary button" type="submit">
                <i class="upload icon"></i>
                Importieren
            </button>
        </form>
    </div>
</body>

</html>