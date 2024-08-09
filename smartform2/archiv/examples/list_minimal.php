<?php
require_once '../ListGenerator.php';

// Erweiterte Benutzerdaten mit zusätzlichen Feldern
$users = [
    ['id' => 1, 'first_name' => 'Max', 'last_name' => 'Mustermann', 'email' => 'max@example.com', 'role' => 'Admin', 'status' => 'Aktiv', 'created_at' => '2023-01-15 10:30:00'],
    ['id' => 2, 'first_name' => 'Anna', 'last_name' => 'Schmidt', 'email' => 'anna@example.com', 'role' => 'User', 'status' => 'Inaktiv', 'created_at' => '2023-02-20 14:45:00'],
    ['id' => 3, 'first_name' => 'Tom', 'last_name' => 'Weber', 'email' => 'tom@example.com', 'role' => 'Editor', 'status' => 'Aktiv', 'created_at' => '2023-03-10 09:15:00'],
    ['id' => 4, 'first_name' => 'Laura', 'last_name' => 'Becker', 'email' => 'laura@example.com', 'role' => 'User', 'status' => 'Aktiv', 'created_at' => '2023-04-05 16:20:00'],
    ['id' => 5, 'first_name' => 'Felix', 'last_name' => 'Koch', 'email' => 'felix@example.com', 'role' => 'Admin', 'status' => 'Inaktiv', 'created_at' => '2023-05-12 11:50:00'],
    ['id' => 6, 'first_name' => 'Sophie', 'last_name' => 'Wagner', 'email' => 'sophie@example.com', 'role' => 'User', 'status' => 'Aktiv', 'created_at' => '2023-06-18 08:30:00'],
    ['id' => 7, 'first_name' => 'Lukas', 'last_name' => 'Hoffmann', 'email' => 'lukas@example.com', 'role' => 'Editor', 'status' => 'Aktiv', 'created_at' => '2023-07-22 13:10:00'],
    ['id' => 8, 'first_name' => 'Emma', 'last_name' => 'Schulz', 'email' => 'emma@example.com', 'role' => 'User', 'status' => 'Inaktiv', 'created_at' => '2023-08-30 15:40:00'],
    ['id' => 9, 'first_name' => 'David', 'last_name' => 'Fischer', 'email' => 'david@example.com', 'role' => 'Admin', 'status' => 'Aktiv', 'created_at' => '2022-09-05 10:20:00'],
    ['id' => 10, 'first_name' => 'Lena', 'last_name' => 'Meyer', 'email' => 'lena@example.com', 'role' => 'User', 'status' => 'Aktiv', 'created_at' => '2022-10-11 17:00:00']
];

// Erweiterte ListGenerator Initialisierung
$listGenerator = new ListGenerator([
    'listId' => 'userList',
    'itemsPerPage' => 5,
]);

$listGenerator->setData($users);

// Weiter externe Buttons
$listGenerator->addExternalButton('addEntry', [
    'icon' => 'plus',
    'class' => 'ui primary button',
    'position' => 'top',
    'alignment' => 'right',
    'title' => 'Neuer Eintrag',
    'callback' => 'addNewEntry',
    'params' => ['type' => 'user'],
    'popup' => [
        'content' => 'Klicken Sie hier, um einen neuen Eintrag hinzuzufügen',
        'position' => 'bottom right',
        'variation' => 'basic',
        'hoverable' => true
    ]
]);




$listGenerator->addButton('delete', [
    'label' => 'Löschen',
    'callback' => 'deleteUser',
    'icon' => 'trash',
    'class' => 'ui red tiny button',
    'position' => 'right',
    'params' => ['id', 'first_name', 'last_name'],
    'title' => 'Benutzer löschen',
    'confirmMessage' => 'Sind Sie sicher, dass Sie den Benutzer {first_name} {last_name} löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden.',
    'popup' => [
        'content' => 'Bearbeiten Sie {first_name} {last_name}',
        'position' => 'top left',
        'variation' => 'basic',
        'hoverable' => true,
        'class' => 'custom-popup-class'
    ]
]);
$listGenerator->addFilter('year', 'Year', [
    '2023' => '2023',
    '2022' => '2022',
    '2021' => '2021'
], [
    'multiple' => true,
    'placeholder' => 'Jahre auswählen',
    'searchable' => true,
    'maxSelections' => 1,
    'fullTextSearch' => true,
    'customClass' => 'year-filter'
]);

$listGenerator->addFilter('status', 'Status', [
    'Aktiv' => 'Aktiv',
    'Inaktiv' => 'Inaktiv'
]);

$listGenerator->setButtonColumnTitle('left', 'Verwaltung');

// Erweiterte Spalten-Definitionen
$listGenerator->addColumn('id', 'ID', ['align' => 'center']);
$listGenerator->addColumn('first_name', 'Vorname', ['align' => 'center']);
$listGenerator->addColumn('last_name', 'Nachname');
$listGenerator->addColumn('email', 'E-Mail');
$listGenerator->addColumn('status', 'Status');
$listGenerator->addColumn('created_at', 'Erstellt am', [
    'formatter' => function ($value) {
        return date('Y-m-d', strtotime($value));
    }
]);

// AJAX-Anfrage Behandlung
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo $listGenerator->generate(true);
    exit;
}

?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Benutzerliste</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.2/dist/semantic.min.css">
</head>

<body>
    <div class="ui container" style="padding-top: 20px;">
        <h1 class="ui header">Simple Benutzerliste</h1>
        <?php echo $listGenerator->generate(); ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.2/dist/semantic.min.js"></script>
    <script>
        function deleteUser(id, params) {
            console.log('Benutzer mit ID ' + id + ' wird gelöscht');
            console.log('Zusätzliche Parameter:', params);

            // Hier den tatsächlichen AJAX-Aufruf zum Löschen des Benutzers implementieren
        }

        function editUser(id, params) {
            console.log('Benutzer mit ID ' + id + ' wird bearbeitet');
            console.log('Zusätzliche Parameter:', params);

            // Hier die Logik zum Bearbeiten des Benutzers implementieren
        }
    </script>
</body>

</html>