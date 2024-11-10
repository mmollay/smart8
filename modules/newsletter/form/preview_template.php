<?php
include(__DIR__ . '/../../../smartform2/FormGenerator.php');
include(__DIR__ . '/../n_config.php');

$template_id = $_POST['template_id'] ?? null;

// Template-Daten laden
if ($template_id) {
    $stmt = $db->prepare("SELECT name, subject, html_content FROM email_templates WHERE id = ?");
    $stmt->bind_param('i', $template_id);
    $stmt->execute();
    $template = $stmt->get_result()->fetch_assoc();
}

// FormGenerator initialisieren
$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'previewForm',
    'class' => 'ui form',
    'method' => 'POST',
    'action' => 'ajax/form_save.php',
    'success' => ''
]);

// Info-Segment mit Template-Name
$formGenerator->addField([
    'type' => 'segment',
    'class' => 'ui info segment',
    'fields' => [
        [
            'type' => 'custom',
            'html' => '<h4 class="ui header">' . htmlspecialchars($template['name'] ?? 'Template Vorschau') . '</h4>' .
                '<p>Hier können Sie das Template mit verschiedenen Daten testen.</p>'
        ]
    ]
]);

// Grid für die Auswahlfelder
$formGenerator->addField([
    'type' => 'grid',
    'columns' => 16,
    'fields' => [
        [
            'type' => 'dropdown',
            'name' => 'previewType',
            'label' => 'Platzhalter testen mit:',
            'array' => [
                'example' => 'Beispieldaten',
                'recipient' => 'Echtem Empfänger'
            ],
            'width' => 8,
            'dropdownSettings' => [
                'onChange' => 'function(value) { handlePreviewTypeChange(value); }'
            ]
        ],
        [
            'type' => 'dropdown',
            'name' => 'previewRecipient',
            'label' => 'Empfänger auswählen:',
            'array' => getRecipientsList($db),
            'width' => 8,
            'class' => 'search',
            'dropdownSettings' => [
                'onChange' => 'function(value) { if(value) { loadRecipientPreview(value); } }'
            ],
            'containerAttributes' => [
                'id' => 'recipientSelection',
                'style' => 'display: none;'
            ]
        ]
    ]
]);

// Vorschau-Container
$formGenerator->addField([
    'type' => 'segment',
    'class' => 'ui raised segment preview-container',
    'fields' => [
        [
            'type' => 'custom',
            'html' => '
                <div class="ui ribbon label">
                    <i class="mail icon"></i> E-Mail Vorschau
                </div>
                <h3 id="previewSubject">' . htmlspecialchars($template['subject'] ?? '') . '</h3>
                <div class="ui divider"></div>
                <div id="previewContent">' . ($template['html_content'] ?? '') . '</div>
            '
        ]
    ]
]);

// Buttons
$formGenerator->addButtonElement([
    [
        'name' => 'refresh',
        'value' => 'Aktualisieren',
        'class' => 'ui primary button',
        'icon' => 'sync',
        'onclick' => 'refreshPreview()'
    ],
    [
        'name' => 'close',
        'value' => 'Schließen',
        'class' => 'ui button',
        'icon' => 'close',
        'onclick' => "$('.ui.modal').modal('hide');"
    ]
], [
    'layout' => 'grouped',
    'alignment' => 'right'
]);

echo $formGenerator->generateJS();
echo $formGenerator->generateForm();

// Helper function to get recipients list
function getRecipientsList($db)
{
    $recipients = [];
    $result = $db->query("
        SELECT id, first_name, last_name, email, company 
        FROM recipients 
        WHERE unsubscribed = 0 
        ORDER BY last_name, first_name 
        LIMIT 50
    ");

    while ($row = $result->fetch_assoc()) {
        $label = htmlspecialchars("{$row['last_name']}, {$row['first_name']} ");
        if ($row['company']) {
            $label .= htmlspecialchars("({$row['company']}) ");
        }
        $label .= htmlspecialchars("- {$row['email']}");
        $recipients[$row['id']] = $label;
    }

    return $recipients;
}
?>

<script>
    $(document).ready(function () {
        // Initial Preview
        showExamplePreview();
    });

    function handlePreviewTypeChange(type) {
        $('#recipientSelection').toggle(type === 'recipient');
        if (type === 'example') {
            showExamplePreview();
        }
    }

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
        $('.preview-container').addClass('loading');

        $.ajax({
            url: 'ajax/template/get_recipient_data.php',
            method: 'POST',
            data: { recipient_id: recipientId },
            success: function (response) {
                if (response.success) {
                    updatePreview(response.data);
                } else {
                    showToast('Fehler beim Laden der Empfängerdaten', 'error');
                }
            },
            error: function () {
                showToast('Netzwerkfehler beim Laden der Daten', 'error');
            },
            complete: function () {
                $('.preview-container').removeClass('loading');
            }
        });
    }

    function updatePreview(data) {
        let subject = $('#previewSubject').html();
        let content = $('#previewContent').html();

        Object.entries(data).forEach(([key, value]) => {
            const regex = new RegExp(`{{${key}}}`, 'g');
            subject = subject.replace(regex, value);
            content = content.replace(regex, value);
        });

        $('#previewSubject').html(subject);
        $('#previewContent').html(content);
    }

    function refreshPreview() {
        const type = $('#previewType').val();
        if (type === 'recipient') {
            const recipientId = $('#previewRecipient').val();
            if (recipientId) {
                loadRecipientPreview(recipientId);
            }
        } else {
            showExamplePreview();
        }
    }
</script>

<style>
    .preview-container {
        max-height: 60vh;
        overflow-y: auto;
        margin: 1em 0;
        position: relative;
    }

    .preview-container.loading:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        z-index: 1;
    }

    .preview-container.loading:after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 30px;
        height: 30px;
        border: 3px solid #f0f0f0;
        border-top: 3px solid #2185d0;
        border-radius: 50%;
        z-index: 2;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: translate(-50%, -50%) rotate(0deg);
        }

        100% {
            transform: translate(-50%, -50%) rotate(360deg);
        }
    }

    .ui.ribbon.label {
        margin-bottom: 1em;
    }

    #previewSubject {
        margin-top: 0.5em;
        color: #2185d0;
    }
</style>