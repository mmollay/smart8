<?php
require_once(__DIR__ . '/n_config.php');

// Parameter aus URL holen und validieren
$email = isset($_GET['email']) ? filter_var($_GET['email'], FILTER_SANITIZE_EMAIL) : null;
$jobId = isset($_GET['id']) ? intval($_GET['id']) : null;

// Überprüfe ob die Parameter vorhanden sind
if (!$email || !$jobId) {
    die('Ungültiger Abmelde-Link');
}

try {
    // Prüfe erst, ob der Empfänger bereits abgemeldet ist
    $stmt = $db->prepare("
        SELECT r.id, r.email, r.unsubscribed, r.unsubscribed_at,
               ej.content_id, ej.message_id 
        FROM recipients r
        JOIN email_jobs ej ON ej.recipient_id = r.id
        WHERE r.email = ? AND ej.id = ?
    ");
    $stmt->bind_param("si", $email, $jobId);
    $stmt->execute();
    $result = $stmt->get_result();
    $recipient = $result->fetch_assoc();

    if (!$recipient) {
        throw new Exception('Ungültige E-Mail-Adresse oder Job-ID');
    }

    // HTML Header für beide Fälle
    ?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Newsletter Abmeldung</title>
        <meta charset="utf-8">
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                margin: 40px;
                background-color: #f5f5f5;
            }

            .container {
                max-width: 600px;
                margin: 0 auto;
                background: white;
                padding: 20px;
                border-radius: 5px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .message {
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 4px;
            }

            .success-message {
                color: #155724;
                background-color: #d4edda;
                border: 1px solid #c3e6cb;
            }

            .info-message {
                color: #0c5460;
                background-color: #d1ecf1;
                border: 1px solid #bee5eb;
            }

            .error-message {
                color: #721c24;
                background-color: #f8d7da;
                border: 1px solid #f5c6cb;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <?php

            // Wenn bereits abgemeldet, zeige Info-Nachricht
            if ($recipient['unsubscribed']) {
                $unsubDate = new DateTime($recipient['unsubscribed_at']);
                ?>
                <div class="message info-message">
                    Diese E-Mail-Adresse wurde bereits am <?php echo $unsubDate->format('d.m.Y H:i'); ?> Uhr abgemeldet.
                </div>
                <p>
                    Die E-Mail-Adresse <?php echo htmlspecialchars($email); ?> befindet sich nicht mehr
                    in unserem Newsletter-Verteiler.
                </p>
                <p>
                    Falls Sie sich zu einem späteren Zeitpunkt wieder anmelden möchten,
                    kontaktieren Sie uns bitte.
                </p>
                <?php
            } else {
                // Wenn noch nicht abgemeldet, führe Abmeldung durch
                $db->begin_transaction();

                try {
                    // Update Empfänger-Status
                    $stmt = $db->prepare("
                UPDATE recipients 
                SET unsubscribed = 1,
                    unsubscribed_at = NOW() 
                WHERE id = ?
            ");
                    $stmt->bind_param("i", $recipient['id']);
                    $stmt->execute();

                    // Logge Abmeldung
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

                    // Status in email_jobs aktualisieren
                    $stmt = $db->prepare("
                UPDATE email_jobs 
                SET status = 'unsub',
                    updated_at = NOW()
                WHERE id = ?
            ");
                    $stmt->bind_param("i", $jobId);
                    $stmt->execute();

                    $db->commit();
                    ?>
                    <div class="message success-message">
                        Sie wurden erfolgreich von unserem Newsletter abgemeldet.
                    </div>
                    <p>
                        Ihre E-Mail-Adresse (<?php echo htmlspecialchars($email); ?>) wurde aus unserem
                        Newsletter-Verteiler entfernt. Sie erhalten von uns keine weiteren Newsletter mehr.
                    </p>
                    <p>
                        Falls Sie sich zu einem späteren Zeitpunkt wieder anmelden möchten,
                        kontaktieren Sie uns bitte.
                    </p>
                    <?php

                } catch (Exception $e) {
                    $db->rollback();
                    throw $e;
                }
            }
            ?>
        </div>
    </body>

    </html>
    <?php

} catch (Exception $e) {
    // Fehlerseite anzeigen
    ?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Fehler bei der Newsletter Abmeldung</title>
        <meta charset="utf-8">
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                margin: 40px;
                background-color: #f5f5f5;
            }

            .container {
                max-width: 600px;
                margin: 0 auto;
                background: white;
                padding: 20px;
                border-radius: 5px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .error-message {
                color: #721c24;
                background-color: #f8d7da;
                border: 1px solid #f5c6cb;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 4px;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <div class="error-message">
                Bei der Abmeldung ist ein Fehler aufgetreten.
            </div>
            <p>
                Bitte versuchen Sie es später noch einmal oder kontaktieren Sie unseren Support.
            </p>
            <p>
                Fehlermeldung: <?php echo htmlspecialchars($e->getMessage()); ?>
            </p>
        </div>
    </body>

    </html>
    <?php
}