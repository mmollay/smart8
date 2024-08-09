<?php
//Neue Version mit ajax
include __DIR__ . '/../ListGenerator.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$listGenerator = new ListGenerator([
    'listId' => 'martin',
    'contentId' => 'content1',
    'itemsPerPage' => 5,
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
]);

// Datenbank-Verbindung
$db = new mysqli('localhost', 'root', 'Jgewl21;', 'demo');

if ($db->connect_error) {
    die("Verbindung fehlgeschlagen: " . $db->connect_error);
}

// Datenbank-Abfrage
$query = "
        SELECT
            u.id, u.first_name, u.last_name, u.email, u.role, u.status, u.created_at,
            ud.phone, ud.address, ud.birth_date
        FROM
            users u
        LEFT JOIN
            user_details ud ON u.id = ud.user_id
    ";

$listGenerator->setDatabase($db, $query, true);

// Filter für Status
$listGenerator->addFilter(
    'status',
    'Status',
    ['Aktiv' => 'Aktiv', 'Inaktiv' => 'Inaktiv'],
    [
        'placeholder' => 'Status',
        'clearable' => true
    ]
);

// Filter für Rolle
$listGenerator->addFilter(
    'role',
    'Rolle',
    ['Admin' => 'Admin', 'User' => 'User', 'Editor' => 'Editor'],
    [
        'placeholder' => 'Rolle',
        'clearable' => true
    ]
);

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

$listGenerator->addModal('editUser', [
    'title' => 'Benutzer bearbeiten',
    'content' => 'edit_user.php',
    'size' => 'small',
]);

$listGenerator->addButton('edit', [
    'label' => '',
    'icon' => 'edit',
    'position' => 'left',
    'class' => 'ui blue tiny button',
    'modalId' => 'editUser',
    'group' => 'manage1',
    'params' => ['id', 'first_name', 'last_name', 'email']
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
$listGenerator->setButtonColumnTitle('left', 'Bearbeiten');
$listGenerator->setButtonColumnTitle('right', 'Löschen');

?>
<br>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direkte Tabellengenerierung</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.2/dist/semantic.min.css">
</head>

<body>
    <div class="ui container">
        <h1 class="ui header">Direkte Tabellengenerierung</h1>
        <?= $listGenerator->generateList() ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.3/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.2/dist/semantic.min.js"></script>
    <script src="../js/listGenerator.js"></script>
    <script>
        $(document).ready(function () {
            setupListGenerator('content1');
        });
    </script>
</body>

</html>