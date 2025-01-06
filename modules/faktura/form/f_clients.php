<?php
include(__DIR__ . '/../f_config.php');
include(__DIR__ . '/../../../smartform2/FormGenerator.php');

$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'clientForm',
    'action' => 'process_client_form.php',
    'class' => 'ui form',
    'method' => 'POST',
    'responseType' => 'json',
    'success' => "after_form_client(response);"
]);

// Laden der Titel aus der Datenbank
$query_title = $GLOBALS['mysqli']->query("SELECT DISTINCT(title) FROM client");
$array_title = [];
while ($fetch_array_title = mysqli_fetch_array($query_title)) {
    if ($fetch_array_title[0] != '' && !is_numeric($fetch_array_title[0])) {
        $array_title[$fetch_array_title[0]] = $fetch_array_title[0];
    }
}

// Bestimmen des Modus (Bearbeiten oder Neu)
if (isset($_POST['update_id'])) {
    $formGenerator->loadValuesFromDatabase($GLOBALS['mysqli'], "SELECT * from client WHERE client_id = ?", [$_POST['update_id']]);

    // Zusätzliche Abfrage für Mitgliedschaftsstatus
    $year = date('Y');
    $query = $GLOBALS['mysqli']->prepare("
        SELECT COUNT(*) as status
        FROM client INNER JOIN membership
        ON client.client_id = membership.client_id
        WHERE DATE_FORMAT(date_membership_start,'%Y') <= ?
        AND (DATE_FORMAT(date_membership_stop,'%Y') >= NOW() OR date_membership_stop = '0000-00-00')
        AND membership.client_id = ?
    ");
    $query->bind_param("is", $year, $_POST['update_id']);
    $query->execute();
    $result = $query->get_result();
    $membershipStatus = $result->fetch_assoc()['status'];
} else {
    //$company_id = $_SESSION['faktura_company_id'];
    $set_activ = 1;
    $join_date = date('Y-m-d');
    $formGenerator->addField([
        'type' => 'hidden',
        'name' => 'modus',
        'value' => 'add_client'
    ]);

    // Neue Kundennummer generieren
    $client_number = getNextClientNumber($mysqli);
}

// Tabs definieren
$formGenerator->addField([
    'type' => 'tab',
    'tabs' => [
        '1' => 'Kontaktdaten',
        '4' => 'Lieferadresse',
        '2' => 'Erweiterung',
        '3' => 'Mitglieder/Sektionen'
    ],
    'active' => '1'
]);

// Felder für Tab 1: Kontaktdaten
$formGenerator->addField([
    'type' => 'checkbox',
    'name' => 'abo',
    'label' => 'abonnieren',
    'tab' => '1'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'client_number',
    'label' => 'Kundennummer',
    'value' => $client_number ?? '',
    'tab' => '1'
]);

$formGenerator->addField([
    'type' => 'grid',
    'columns' => 2,
    'tab' => '1',
    'fields' => [
        [
            'type' => 'input',
            'name' => 'company_1',
            'label' => 'Firma',
            'width' => 1
        ],
        [
            'type' => 'input',
            'name' => 'company_2',
            'label' => 'Firma(Zusatz)',
            'width' => 1
        ]
    ]
]);

$formGenerator->addField([
    'type' => 'grid',
    'columns' => 4,
    'tab' => '1',
    'fields' => [
        [
            'type' => 'dropdown',
            'name' => 'gender',
            'label' => 'Titel',
            'array' => ['f' => 'Frau', 'm' => 'Herr'],
            'width' => 1
        ],
        [
            'type' => 'input',
            'name' => 'title',
            'label' => 'Titel',
            'width' => 1
        ],
        [
            'type' => 'input',
            'name' => 'firstname',
            'label' => 'Vorname',
            'width' => 1
        ],
        [
            'type' => 'input',
            'name' => 'secondname',
            'label' => 'Nachname',
            'width' => 1
        ]
    ]
]);

$formGenerator->addField([
    'type' => 'calendar',
    'name' => 'birthday',
    'label' => 'Geburtsdatum',
    'tab' => '1'
]);

// Weitere Felder für Tab 1...

// Felder für Tab 4: Lieferadresse
$formGenerator->addField([
    'type' => 'grid',
    'columns' => 2,
    'tab' => '4',
    'fields' => [
        [
            'type' => 'input',
            'name' => 'delivery_company1',
            'label' => 'Firma',
            'width' => 1
        ],
        [
            'type' => 'input',
            'name' => 'delivery_company2',
            'label' => 'Firma(Zusatz)',
            'width' => 1
        ]
    ]
]);

// Weitere Felder für Tab 4...

// Felder für Tab 2: Erweiterung
if (!isset($oegt_modus) || !$oegt_modus) {
    $formGenerator->addField([
        'type' => 'input',
        'name' => 'uid',
        'label' => 'UID',
        'tab' => '2'
    ]);

    $formGenerator->addField([
        'type' => 'calendar',
        'name' => 'join_date',
        'label' => 'Beitrittsdatum',
        'value' => $join_date ?? '',
        'tab' => '2'
    ]);
} else {
    // Include OEGT specific fields
    include('../oegt/client_addone2.inc.php');
}

// Buttons
$formGenerator->addButtonElement([
    [
        'type' => 'submit',
        'name' => 'submit',
        'value' => 'Speichern',
        'icon' => 'save',
        'class' => 'ui primary button'
    ],
    [
        'type' => 'button',
        'name' => 'cancel',
        'value' => 'Abbrechen',
        'icon' => 'cancel',
        'class' => 'ui button',
        'onclick' => "$('.ui.modal').modal('hide');"
    ]
], [
    'layout' => 'grouped',
    'alignment' => 'right'
]);

echo $formGenerator->generateJS();
echo $formGenerator->generateForm();

function getNextClientNumber($mysqli)
{
    // Vorbereiten der SQL-Abfrage
    $query = "SELECT MAX(client_number) as max_client_number FROM client";

    // Ausführen der Abfrage
    $result = $mysqli->query($query);

    if ($result) {
        // Abrufen des Ergebnisses
        $row = $result->fetch_assoc();

        // Extrahieren der maximalen Kundennummer
        $maxClientNumber = $row['max_client_number'];

        // Freigeben des Ergebnisses
        $result->free();

        // Rückgabe der nächsten Kundennummer
        return $maxClientNumber + 1;
    } else {
        // Fehlerbehandlung, falls die Abfrage fehlschlägt
        error_log("Fehler bei der Abfrage der maximalen Kundennummer: " . $mysqli->error);
        return false;
    }
}