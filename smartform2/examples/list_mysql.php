<?php
//Neue Version mit ajax
include __DIR__ . '/../ListGenerator.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$listGenerator = new ListGenerator([
    'listId' => 'userList',
    'contentId' => 'content1',
    'itemsPerPage' => 3,
    'sortColumn' => $_GET['sort'] ?? 'id',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'ASC'),
    'search' => $_GET['search'] ?? '',
    'page' => intval($_GET['page'] ?? 1),
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Daten gefunden.',
    'showFooter' => true,
    'footerTemplate' => 'Gesamt: {totalRows} Einträge | Seite {currentPage} von {totalPages}',
    'showPagination' => true,
    'tableClasses' => 'ui celled table',
    'headerClasses' => 'ui inverted blue table',
    'rowClasses' => '',
    'cellClasses' => '',
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'compact' => true,
    'color' => 'blue',
    'size' => 'small',
    'width' => '1200px',
    'filterClass' => 'ui message',
    'debug' => true,
]);

// Datenbank-Verbindung
$db = new mysqli('localhost', 'root', 'Jgewl21;', 'demo');

if ($db->connect_error) {
    die("Verbindung fehlgeschlagen: " . $db->connect_error);
}

$query = "
 SELECT
 u.id, u.first_name, u.last_name, u.email, u.role, u.status, u.created_at,
 ud.phone, ud.address, ud.birth_date
 FROM
 users u
 LEFT JOIN
 user_details ud ON u.id = ud.user_id
";


$listGenerator->setSearchableColumns(['first_name', 'last_name', 'email', 'role', 'status']);
$listGenerator->setDatabase($db, $query, true);

$listGenerator->addModal('editUser', [
    'title' => 'Benutzer bearbeiten',
    'content' => 'edit_user.php',
    'size' => 'small',
]);

$listGenerator->addExternalButton('new_entry', [
    'icon' => 'plus',
    'class' => 'ui primary button',
    'position' => 'inline',
    'alignment' => 'right',
    'title' => 'Neuer Eintrag'
]);


$listGenerator->addFilter('status', 'Status', ['Aktiv' => 'Aktiv', 'Inaktiv' => 'Inaktiv']);
$listGenerator->addFilter('role', 'Rolle', ['Admin' => 'Admin', 'User' => 'User', 'Editor' => 'Editor']);

// Spalten definieren
$listGenerator->addColumn('id', 'ID');
$listGenerator->addColumn('first_name', 'Vorname', ['allowHtml' => true]);
$listGenerator->addColumn('last_name', 'Nachname');
$listGenerator->addColumn('email', 'E-Mail');
$listGenerator->addColumn('role', 'Rolle');
$listGenerator->addColumn('status', 'Status');
$listGenerator->addColumn('created_at', 'Erstellt am', [
    'formatter' => function ($value) {
        return date('d.m.Y H:i', strtotime($value));
    }
]);
$listGenerator->addColumn('phone', 'Telefon');
$listGenerator->addColumn('address', 'Adresse');
$listGenerator->addColumn('birth_date', 'Geburtsdatum', [
    'formatter' => function ($value) {
        return $value ? date('d.m.Y', strtotime($value)) : '';
    }
]);

$listGenerator->addButton('edit', [
    'label' => '',
    'icon' => 'edit',
    'position' => 'left',
    'class' => 'ui blue tiny button',
    'modalId' => 'editUser',
    'group' => 'manage1',
    'params' => ['id']
]);

$listGenerator->addButton('delete', [
    'label' => '',
    'icon' => 'trash',
    'class' => 'ui red tiny button',
    'position' => 'right',
    'callback' => 'deleteUser',
    'params' => ['id'],
    'confirmMessage' => 'Sind Sie sicher, dass Sie diesen Benutzer löschen möchten?'
]);


// Setzen der Spaltentitel für die Buttons
$listGenerator->setButtonColumnTitle('left', 'Bearbeiten', 'center');
$listGenerator->setButtonColumnTitle('right', 'Löschen', 'center');

echo $listGenerator->generateList();

if (isset($db)) {
    $db->close();
}
?>