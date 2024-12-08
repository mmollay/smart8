<?php
include __DIR__ . '/../ListGenerator.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ListGenerator Konfiguration
$listGenerator = new ListGenerator([
    'listId' => 'userList',
    'contentId' => 'content1',
    'itemsPerPage' => 10,
    'sortColumn' => $_GET['sort'] ?? 'u.id',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'ASC'),
    'search' => $_GET['search'] ?? '',
    'page' => intval($_GET['page'] ?? 1),
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Daten gefunden.',
    'showFooter' => true,
    'showPagination' => true,
    'tableClasses' => 'ui celled table',
    'headerClasses' => 'ui inverted blue table',
    'striped' => true,
    'color' => 'blue',
    'size' => 'small',
    'width' => '100%',
    'debug' => true,
]);

// Datenbank-Verbindung
$db = new mysqli('localhost', 'root', 'Jgewl21;', 'demo');

if ($db->connect_error) {
    die("Verbindung fehlgeschlagen: " . $db->connect_error);
}

// Basis SQL-Abfrage
$query = "
    SELECT 
        u.id, 
        u.first_name, 
        u.last_name, 
        u.email, 
        u.role, 
        u.status,
        d.name AS department_name
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.id
";

// Suchbare Spalten definieren
$listGenerator->setSearchableColumns(['u.first_name', 'u.last_name', 'u.email']);
$listGenerator->setDatabase($db, $query, true);

// Einfache Filter
$listGenerator->addFilter('u.status', 'Status', [
    'Aktiv' => 'Aktiv',
    'Inaktiv' => 'Inaktiv'
]);

$listGenerator->addFilter('u.role', 'Rolle', [
    'Admin' => 'Admin',
    'User' => 'User',
    'Editor' => 'Editor'
]);

// Spalten definieren
$listGenerator->addColumn('id', 'ID');
$listGenerator->addColumn('first_name', 'Vorname');
$listGenerator->addColumn('last_name', 'Nachname');
$listGenerator->addColumn('email', 'E-Mail');
$listGenerator->addColumn('role', 'Rolle');
$listGenerator->addColumn('status', 'Status', [
    'formatter' => function ($value) {
        $color = $value === 'Aktiv' ? 'green' : 'red';
        return "<span class='ui {$color} label'>{$value}</span>";
    },
    'allowHtml' => true
]);
$listGenerator->addColumn('department_name', 'Abteilung');

// Basis-Buttons
$listGenerator->addButton('edit', [
    'icon' => 'edit',
    'position' => 'left',
    'class' => 'blue mini',
    'modalId' => 'modal_form_edit',
    'popup' => 'Bearbeiten',
    'params' => ['update_id' => 'id']
]);

$listGenerator->addButton('delete', [
    'icon' => 'trash',
    'position' => 'right',
    'class' => 'red mini',
    'modalId' => 'modal_form_delete',
    'popup' => 'Löschen',
    'params' => ['delete_id' => 'id']
]);

$listGenerator->addModal('modal_form_edit', [
    'title' => 'Benutzer bearbeiten',
    //'size' => 'fullscreen',
    'scrollingPage' => true,
    'content' => 'formular_simple.php',
    'method' => 'POST',
    'buttons' => [
        'approve' => [
            'text' => 'Speichern',
            'class' => 'orange',
            'icon' => 'check',
            'action' => 'submit',
            //'onclick' => "alert('test')",  // Führt erst alert aus
            'form_id' => 'simpleForm'      // Und submitted dann das Formular
        ],
        'cancel' => [
            'text' => 'Abbrechen',
            'class' => 'cancel',
            'icon' => 'times',
            'action' => 'close'
        ]
    ]
]);

$listGenerator->addModal('modal_form_delete', [
    'title' => 'Benutzer entfernen',
    'size' => 'small',
    'content' => 'form_delete.php',   // content statt url
    'method' => 'POST',
    'class' => 'basic'
]);

// Neuer Benutzer Button
$listGenerator->addExternalButton('new_user', [
    'icon' => 'plus',
    'class' => 'ui blue button',
    'position' => 'top',
    'alignment' => 'left',
    'title' => 'Neuer Benutzer',
    'modalId' => 'modal_form_new_user',
    'popup' => 'Neuen Benutzer anlegen'  // optional: Tooltip
]);

$listGenerator->addModal('modal_form_new_user', [
    'title' => 'Neuer Benutzer',
    'size' => 'small',
    'content' => 'form_new_user.php', // content statt url
    'method' => 'POST',
    'class' => 'basic'
]);

// Liste generieren und ausgeben
echo $listGenerator->generateList();

// Datenbankverbindung schließen
$db->close();
?>