<?php
// modals/preview_template.php
include(__DIR__ . '/../n_config.php');

$template_id = $_POST['template_id'] ?? null;

if ($template_id) {
    $stmt = $db->prepare("
        SELECT name, subject, html_content 
        FROM email_templates 
        WHERE id = ?
    ");
    $stmt->bind_param('i', $template_id);
    $stmt->execute();
    $template = $stmt->get_result()->fetch_assoc();
}
?>

<div class="ui form">
    <div class="field">
        <label>Platzhalter testen mit:</label>
        <select class="ui dropdown" id="previewType">
            <option value="example">Beispieldaten</option>
            <option value="recipient">Echtem Empfänger</option>
        </select>
    </div>

    <!-- Empfänger-Auswahl (anfangs versteckt) -->
    <div class="field" id="recipientSelection" style="display:none;">
        <label>Empfänger auswählen:</label>
        <select class="ui search dropdown" id="previewRecipient">
            <option value="">Empfänger wählen...</option>
            <?php
            $result = $db->query("
                SELECT id, first_name, last_name, email, company 
                FROM recipients 
                WHERE unsubscribed = 0 
                ORDER BY last_name, first_name 
                LIMIT 50
            ");
            while ($recipient = $result->fetch_assoc()) {
                echo "<option value='{$recipient['id']}'>";
                echo htmlspecialchars("{$recipient['last_name']}, {$recipient['first_name']} ");
                if ($recipient['company']) {
                    echo htmlspecialchars("({$recipient['company']}) ");
                }
                echo htmlspecialchars("- {$recipient['email']}");
                echo "</option>";
            }
            ?>
        </select>
    </div>

    <div class="ui divider"></div>

    <!-- Vorschau-Container -->
    <div class="preview-container">
        <div class="ui raised segment">
            <h3 id="previewSubject"><?php echo htmlspecialchars($template['subject'] ?? ''); ?></h3>
            <div class="ui divider"></div>
            <div id="previewContent">
                <?php echo $template['html_content'] ?? ''; ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Dropdown Initialisierung
        $('.ui.dropdown').dropdown();

        // Preview-Typ Änderung
        $('#previewType').on('change', function () {
            const type = $(this).val();
            if (type === 'recipient') {
                $('#recipientSelection').show();
            } else {
                $('#recipientSelection').hide();
                showExamplePreview();
            }
        });

        // Empfänger Änderung
        $('#previewRecipient').on('change', function () {
            const recipientId = $(this).val();
            if (recipientId) {
                loadRecipientPreview(recipientId);
            }
        });

        // Initial Preview mit Beispieldaten
        showExamplePreview();
    });

    function showExamplePreview() {
        const exampleData = {
            anrede: 'Sehr geehrter Herr',
            titel: 'Dr.',
            vorname: 'Max',
            nachname: 'Mustermann',
            firma: 'Beispiel GmbH',
            email: 'max.mustermann@beispiel.de',
            datum: new Date().toLocaleDateString('de-DE'),
            uhrzeit: new Date().toLocaleTimeString('de-DE')
        };

        updatePreview(exampleData);
    }

    function loadRecipientPreview(recipientId) {
        $.ajax({
            url: 'ajax/template/get_recipient_data.php',
            method: 'POST',
            data: {
                recipient_id: recipientId
            },
            success: function (response) {
                if (response.success) {
                    updatePreview(response.data);
                } else {
                    showToast('Fehler beim Laden der Empfängerdaten', 'error');
                }
            }
        });
    }

    function updatePreview(data) {
        let subject = $('#previewSubject').html();
        let content = $('#previewContent').html();

        // Platzhalter ersetzen
        Object.entries(data).forEach(([key, value]) => {
            const regex = new RegExp(`{{${key}}}`, 'g');
            subject = subject.replace(regex, value);
            content = content.replace(regex, value);
        });

        $('#previewSubject').html(subject);
        $('#previewContent').html(content);
    }
</script>

<style>
    .preview-container {
        max-height: 60vh;
        overflow-y: auto;
        margin-top: 1em;
    }

    .ui.search.dropdown {
        width: 100%;
    }

    #recipientSelection {
        margin-top: 1em;
    }

    .preview-container .ui.segment {
        margin: 0;
    }
</style>