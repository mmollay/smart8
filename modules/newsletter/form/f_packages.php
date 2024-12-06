<?php
include __DIR__ . '/../../../smartform2/FormGenerator.php';
include_once __DIR__ . '/../../../config.php';
// Lade Konfiguration
$config = require(__DIR__ . '/../config/config.php');
$packageConfig = $config['packages'];

// Superuser Check
if (!isset($_SESSION['superuser']) || $_SESSION['superuser'] != 1) {
    die(json_encode([
        'success' => false,
        'message' => 'Keine Berechtigung für diese Aktion'
    ]));
}

try {
    $update_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;

    if (!$update_id) {
        throw new Exception('Keine User ID übermittelt');
    }

    $formGenerator = new FormGenerator();

    // Form Grundeinstellungen
    $formGenerator->setFormData([
        'id' => 'packageForm',
        'action' => 'ajax/save_package.php',
        'method' => 'POST',
        'class' => 'ui form',
        'responseType' => 'json',
        'success' => 'afterFormSubmit(response)'
    ]);

    // Hidden Field für User ID
    $formGenerator->addField([
        'type' => 'hidden',
        'name' => 'user_id',
        'value' => $update_id
    ]);

    // User Info Felder
    $formGenerator->addField([
        'type' => 'grid',
        'columns' => 16,
        'fields' => [
            [
                'type' => 'input',
                'name' => 'firstname',
                'label' => 'E-Mail',
                'readonly' => true,
                'width' => 8
            ],
            [
                'type' => 'input',
                'name' => 'secondname',
                'label' => 'Name',
                'readonly' => true,
                'width' => 8
            ]
        ]
    ]);

    // Paket-Optionen erstellen
    $packageOptions = [];
    foreach ($packageConfig as $key => $package) {
        $packageOptions[$key] = sprintf(
            '%s (%s E-Mails/Monat)',
            ucfirst($key),
            number_format($package['emails_per_month'], 0, ',', '.')
        );
    }

    // Aktuelles Paket laden
    $stmt = $db->prepare("
        SELECT package_type 
        FROM newsletter_user_packages 
        WHERE user_id = ? AND valid_until IS NULL
    ");
    $stmt->bind_param("i", $update_id);
    $stmt->execute();
    $currentPackage = $stmt->get_result()->fetch_assoc();

    // Paket-Auswahl Dropdown
    $formGenerator->addField([
        'type' => 'dropdown',
        'name' => 'package_type',
        'label' => 'Newsletter Paket',
        'array' => $packageOptions,
        'value' => $currentPackage['package_type'] ?? '',
        'required' => true,
        'class' => 'search',
        'dropdownSettings' => [
            'placeholder' => 'Bitte Paket auswählen'
        ]
    ]);


    $formGenerator->addField([
        'type' => 'checkbox',
        'name' => 'newsletter_active',
        'label' => 'Newsletter-Zugang aktivieren',
        'class' => 'ui checkbox',
        'value' => '1',
        'checked' => $user['has_newsletter_access'] == 1,  // Status aus DB
        'description' => 'Wenn deaktiviert, wird der Newsletter-Zugang für diesen User gesperrt und das aktuelle Paket beendet.'
    ]);

    // Buttons
    $formGenerator->addButtonElement([
        [
            'type' => 'submit',
            'name' => 'submit',
            'value' => 'Speichern',
            'class' => 'ui primary button'
        ],
        [
            'name' => 'cancel',
            'value' => 'Abbrechen',
            'class' => 'ui button',
            'onclick' => "$('.ui.modal').modal('hide');"
        ]
    ]);


    // Nach der Definition der Formularfelder, aber vor dem generateJS/generateForm
    if ($update_id) {
        try {
            // Erweiterte SQL-Abfrage, die alle benötigten Informationen in einem Join lädt
            $sql = "
            SELECT 
                u.user_name,
                u.firstname,
                u.secondname,
                COALESCE(um.status, 0) as newsletter_active,
                nup.package_type,
                nup.emails_limit
            FROM user2company u
            LEFT JOIN user_modules um ON u.user_id = um.user_id AND um.module_id = 6
            LEFT JOIN newsletter_user_packages nup ON u.user_id = nup.user_id 
                AND nup.valid_until IS NULL
            WHERE u.user_id = ? 
            LIMIT 1
        ";

            $formGenerator->loadValuesFromDatabase($db, $sql, [$update_id]);

        } catch (Exception $e) {
            echo "<div class='ui error message'>Fehler beim Laden der Benutzerdaten: " .
                htmlspecialchars($e->getMessage()) . "</div>";
        }
    }

    // Formular und JavaScript ausgeben
    echo $formGenerator->generateJS();
    echo $formGenerator->generateForm();
    ?>

    <script>
        function afterFormSubmit(response) {
            if (response.success) {
                showToast('Userpaket erfolgreich gespeichert', 'success');
                // Hier können Sie zusätzliche Aktionen nach erfolgreicher Speicherung hinzufügen
                // z.B. Modal schließen, Liste aktualisieren, etc.
                $('.ui.modal').modal('hide');
                if (typeof reloadTable === 'function') {
                    reloadTable();
                }
            } else {
                showToast('Fehler beim Speichern der Gruppe: ' + response.message, 'error');
            }
        }
    </script>


    <?php
} catch (Exception $e) {
    die(json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]));
}

