<?php
require_once(__DIR__ . '/../n_config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['data'])) {
    $db->begin_transaction();

    try {
        // Zeilen aufteilen
        $lines = explode("\n", trim($_POST['data']));
        $imported = 0;
        $errors = [];

        // userId statt fester 5 verwenden
        $stmt = $db->prepare("
            INSERT INTO blacklist 
            (email, reason, created_at, source, user_id) 
            VALUES (?, ?, ?, 'manual', ?)
        ");

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line))
                continue;

            // Tabs als Trennzeichen
            $data = explode("\t", $line);

            if (count($data) >= 3) {
                $email = trim($data[0]);
                $reason = trim($data[1]);
                $created_at = trim($data[2]);

                $stmt->bind_param("sssi", $email, $reason, $created_at, $userId);

                if ($stmt->execute()) {
                    $imported++;
                } else {
                    $errors[] = "Fehler bei E-Mail $email: " . $stmt->error;
                }
            }
        }

        $db->commit();
        $message = "$imported Einträge erfolgreich importiert.";
        if (!empty($errors)) {
            $message .= "<br>Fehler: " . implode("<br>", $errors);
        }

    } catch (Exception $e) {
        $db->rollback();
        $message = "Fehler beim Import: " . $e->getMessage();
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
        <!-- Zurück Button oben links -->
        <a href="../" class="ui left labeled icon button">
            <i class="left arrow icon"></i>
            Zurück zur Übersicht
        </a>

        <h2 class="ui header">Blacklist Daten Import</h2>

        <?php if (isset($message)): ?>
            <div class="ui <?php echo strpos($message, 'Fehler') !== false ? 'negative' : 'positive'; ?> message">
                <?php echo $message; ?>
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