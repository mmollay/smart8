<?php
include __DIR__ . '/../n_config.php';

$content_id = $_POST['content_id'] ?? null;

if ($content_id) {
    // Empfängerzahl ermitteln
    $recipient_query = "
        SELECT COUNT(DISTINCT r.id) as recipient_count
        FROM email_contents ec
        JOIN email_content_groups ecg ON ec.id = ecg.email_content_id
        JOIN recipient_group rg ON ecg.group_id = rg.group_id
        JOIN recipients r ON rg.recipient_id = r.id
        WHERE ec.id = ? AND r.unsubscribed = 0
    ";

    $stmt = $db->prepare($recipient_query);
    $stmt->bind_param('i', $content_id);
    $stmt->execute();
    $recipient_result = $stmt->get_result();
    $recipient_data = $recipient_result->fetch_assoc();
    $recipient_count = $recipient_data['recipient_count'];

    // Newsletter Daten laden
    $query = "
        SELECT 
            ec.*,
            s.first_name,
            s.last_name,
            s.email as sender_email,
            CONCAT(s.first_name, ' ', s.last_name) as sender_name,
            ec.send_status
        FROM 
            email_contents ec
            LEFT JOIN senders s ON ec.sender_id = s.id
        WHERE 
            ec.id = ?
    ";

    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $content_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    $data['message'] = prepareHtmlForEmail($data['message']);

    // Anhänge laden
    $upload_dir = $uploadBasePath . "/{$content_id}/attachements/";
    $attachments = [];
    if (is_dir($upload_dir)) {
        $files = scandir($upload_dir);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $attachments[] = [
                    'name' => $file,
                    'size' => filesize($upload_dir . $file)
                ];
            }
        }
    }
    ?>
    <div class="ui segments" style="margin: 0; max-height: 80vh; display: flex; flex-direction: column;">
        <!-- Email Header -->
        <div class="ui secondary segment" style="background: #f9fafb;">
            <!-- Empfängeranzahl -->
            <div class="ui info message" style="margin: 0 0 1em 0; padding: 0.5em;">
                <i class="users icon"></i>
                Diese E-Mail wird an <strong><?php echo number_format($recipient_count, 0, ',', '.'); ?> Empfänger</strong>
                gesendet.
            </div>

            <!-- Absender -->
            <div class="field">
                <label style="font-weight: bold;">Von:</label>
                <span><?php echo htmlspecialchars($data['sender_name']); ?>
                    &lt;<?php echo htmlspecialchars($data['sender_email']); ?>&gt;</span>
            </div>

            <!-- Betreff -->
            <div class="field">
                <label style="font-weight: bold;">Betreff:</label>
                <span><?php echo htmlspecialchars($data['subject']); ?></span>
            </div>

            <!-- Anhänge -->
            <?php if (!empty($attachments)): ?>
                <div class="field">
                    <label style="font-weight: bold;">Anhänge:</label>
                    <div class="ui labels">
                        <?php foreach ($attachments as $file): ?>
                            <div class="ui basic label">
                                <i class="paperclip icon"></i>
                                <?php echo htmlspecialchars($file['name']); ?>
                                <div class="detail"><?php echo number_format($file['size'] / 1024, 1); ?> KB</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Email Content -->
        <div class="ui segment email-content" style="flex: 1; overflow: auto; padding: 1em 0.5em;">
            <div class="email-body">
                <?php echo $data['message']; ?>
            </div>
        </div>

        <!-- Buttons Footer -->
        <div class="ui secondary clearing segment" style="margin: 0; padding: 1em;">
            <button class="ui right floated red button" onclick="$('.ui.modal').modal('hide');">
                <i class="times icon"></i> Schließen
            </button>
            <?php if ($data['send_status'] == 0): ?>
                <button class="ui right floated primary button" onclick="sendNewsletter(<?php echo $content_id; ?>)">
                    <i class="paper plane icon"></i> Newsletter versenden
                </button>
            <?php else: ?>
                <button class="ui right floated disabled button">
                    <i class="check icon"></i> Bereits versendet
                </button>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .field {
            margin-bottom: 0.5em;
        }

        .field:last-child {
            margin-bottom: 0;
        }

        .ui.basic.label {
            margin: 0.25em;
        }

        .ui.segment {
            border-radius: 0;
        }

        /* Email-Inhalt Styling */
        .email-content {
            background: #ffffff;
            padding: 1em 0.5em !important;
            /* Reduzierter seitlicher Abstand im Content-Bereich */
        }

        .email-body {
            max-width: 900px;
            /* Erhöhte maximale Breite */
            margin: 0 auto;
            padding: 0 0.5em;
            /* Reduzierter seitlicher Abstand im Body */
        }

        /* Bildanpassungen */
        .email-body img {
            max-width: 100% !important;
            height: auto !important;
            display: block;
            margin: 1em auto;
        }

        /* Tabellen responsive machen */
        .email-body table {
            max-width: 100% !important;
            width: 100% !important;
        }

        /* Links styling */
        .email-body a {
            color: #2185d0;
            text-decoration: underline;
        }

        /* Textformatierung */
        .email-body p {
            margin: 0.8em 0;
            /* Leicht reduzierter vertikaler Abstand */
            line-height: 1.5;
        }

        /* Listen styling */
        .email-body ul,
        .email-body ol {
            padding-left: 1.5em;
            /* Reduzierter Einzug für Listen */
            margin: 0.8em 0;
        }

        /* Überschriften */
        .email-body h1,
        .email-body h2,
        .email-body h3,
        .email-body h4,
        .email-body h5,
        .email-body h6 {
            margin: 0.8em 0 0.4em;
            /* Reduzierte Abstände */
            line-height: 1.2;
        }

        /* Blockquotes */
        .email-body blockquote {
            border-left: 3px solid #e0e0e0;
            margin: 0.8em 0;
            padding-left: 1em;
            color: #666;
        }

        /* Responsive Design für kleine Bildschirme */
        @media (max-width: 768px) {
            .email-body {
                padding: 0 0.5em;
            }
        }

        /* Zusätzliche Styles für die Buttons */
        .ui.clearing.segment {
            background: #f9fafb;
            border-top: 1px solid rgba(34, 36, 38, .15);
        }

        .ui.button {
            margin-left: 0.5em;
        }
    </style>

    <script>
        function sendNewsletter(contentId) {
            if (confirm('Möchten Sie den Newsletter wirklich an ' + <?php echo $recipient_count; ?> + ' Empfänger versenden?')) {
                $.ajax({
                    url: 'ajax/send_newsletter.php',
                    method: 'POST',
                    data: { content_id: contentId },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            $('.ui.modal').modal('hide');
                            $('body').toast({
                                message: response.message || 'Newsletter wird versendet.',
                                class: 'success',
                                showProgress: 'bottom'
                            });
                            if (typeof reloadTable === 'function') {
                                reloadTable();
                            }
                        } else {
                            $('body').toast({
                                message: response.message || 'Fehler beim Versenden des Newsletters.',
                                class: 'error',
                                showProgress: 'bottom'
                            });
                        }
                    },
                    error: function () {
                        $('body').toast({
                            message: 'Fehler bei der Serveranfrage.',
                            class: 'error',
                            showProgress: 'bottom'
                        });
                    }
                });
            }
        }
    </script>


    <?php
} else {
    echo '<div class="ui negative message">
            <i class="exclamation triangle icon"></i>
            Keine Newsletter-ID gefunden
          </div>';
}
?>