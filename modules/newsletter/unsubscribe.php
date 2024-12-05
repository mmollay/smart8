<?php
$set_unsubscribed = true;
require_once(__DIR__ . '/n_config.php');

$email = isset($_GET['email']) ? filter_var($_GET['email'], FILTER_SANITIZE_EMAIL) : null;
$jobId = isset($_GET['id']) ? intval($_GET['id']) : null;
$confirm = isset($_POST['confirm']) ? true : false;

if (!$email || !$jobId) {
    die('Ungültiger Abmelde-Link');
}

try {
    $stmt = $db->prepare("
        SELECT r.id, r.email, r.unsubscribed, r.unsubscribed_at,
               ej.content_id, ej.message_id 
        FROM recipients r
        JOIN email_jobs ej ON ej.recipient_id = r.id
        WHERE r.email = ? AND ej.id = ?
    ");
    $stmt->bind_param("si", $email, $jobId);
    $stmt->execute();
    $recipient = $stmt->get_result()->fetch_assoc();

    if (!$recipient) {
        throw new Exception('Ungültige E-Mail-Adresse oder Job-ID');
    }
    ?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Newsletter Abmeldung</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.css">
        <style>
            body {
                background-color: #f5f5f5;
                padding: 40px;
            }

            .container {
                max-width: 600px;
                margin: 0 auto;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <div class="ui segment">
                <?php
                if ($recipient['unsubscribed']) {
                    $unsubDate = new DateTime($recipient['unsubscribed_at']);
                    ?>
                    <div class="ui info message">
                        <div class="header">Bereits abgemeldet</div>
                        <p>Diese E-Mail-Adresse wurde bereits am <?php echo $unsubDate->format('d.m.Y H:i'); ?> Uhr abgemeldet.
                        </p>
                    </div>
                    <p>Die E-Mail-Adresse <?php echo htmlspecialchars($email); ?> befindet sich nicht mehr in unserem
                        Newsletter-Verteiler.</p>
                    <?php
                } elseif ($confirm) {
                    // Wenn Bestätigung erfolgt ist, führe Abmeldung durch
                    $db->begin_transaction();

                    try {
                        // Update Empfänger
                        $stmt = $db->prepare("
                        UPDATE recipients 
                        SET unsubscribed = 1, unsubscribed_at = NOW() 
                        WHERE id = ?
                    ");
                        $stmt->bind_param("i", $recipient['id']);
                        $stmt->execute();

                        // Logging
                        $stmt = $db->prepare("
                        INSERT INTO unsubscribe_log 
                        (recipient_id, email, content_id, message_id, timestamp) 
                        VALUES (?, ?, ?, ?, NOW())
                    ");
                        $stmt->bind_param(
                            "isis",
                            $recipient['id'],
                            $recipient['email'],
                            $recipient['content_id'],
                            $recipient['message_id']
                        );
                        $stmt->execute();

                        // Update job status
                        $stmt = $db->prepare("
                        UPDATE email_jobs 
                        SET status = 'unsub', updated_at = NOW()
                        WHERE id = ?
                    ");
                        $stmt->bind_param("i", $jobId);
                        $stmt->execute();

                        $db->commit();
                        ?>
                        <div class="ui success message">
                            <div class="header">Erfolgreich abgemeldet</div>
                            <p>Sie wurden erfolgreich von unserem Newsletter abgemeldet.</p>
                        </div>
                        <p>Ihre E-Mail-Adresse (<?php echo htmlspecialchars($email); ?>) wurde aus unserem Newsletter-Verteiler
                            entfernt.</p>
                        <?php
                    } catch (Exception $e) {
                        $db->rollback();
                        throw $e;
                    }
                } else {
                    // Zeige Bestätigungsformular
                    ?>
                    <h2 class="ui header">Newsletter Abmeldung bestätigen</h2>
                    <p>Möchten Sie sich wirklich mit der E-Mail-Adresse <strong><?php echo htmlspecialchars($email); ?></strong>
                        von unserem Newsletter abmelden?</p>

                    <form class="ui form" method="post">
                        <input type="hidden" name="confirm" value="1">
                        <button class="ui red button" type="submit">
                            <i class="user times icon"></i>
                            Ja, vom Newsletter abmelden
                        </button>
                        <a href="#" class="ui button" onclick="window.close(); return false;">
                            <i class="cancel icon"></i>
                            Abbrechen
                        </a>
                    </form>
                    <?php
                }
                ?>
                <p class="ui small text" style="margin-top: 20px;">
                    Bei Fragen können Sie sich jederzeit an unseren Support wenden.
                </p>
            </div>
        </div>
    </body>

    </html>
    <?php
} catch (Exception $e) {
    ?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Fehler bei der Newsletter Abmeldung</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.css">
        <style>
            body {
                background-color: #f5f5f5;
                padding: 40px;
            }

            .container {
                max-width: 600px;
                margin: 0 auto;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <div class="ui error message">
                <div class="header">Fehler bei der Abmeldung</div>
                <p><?php echo htmlspecialchars($e->getMessage()); ?></p>
            </div>
            <p>Bitte versuchen Sie es später noch einmal oder kontaktieren Sie unseren Support.</p>
        </div>
    </body>

    </html>
    <?php
}