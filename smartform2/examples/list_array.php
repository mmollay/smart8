<?php
//Neue Version mit ajax
include __DIR__ . '/../ListGenerator.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$listType = $_GET['listType'] ?? 'mysql';

$listGenerator = new ListGenerator([
    'listId' => 'martin',
    'contentId' => 'content2',
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
]);


// Array data
$orders = [
    ['id' => 1, 'customer' => 'Max Mustermann', 'total' => 1599.98, 'status' => 'Versandt', 'order_date' => '2023-03-01 09:15:00'],
    ['id' => 2, 'customer' => 'Anna Schmidt', 'total' => 599.99, 'status' => 'In Bearbeitung', 'order_date' => '2023-03-02 11:30:00'],
    ['id' => 3, 'customer' => 'Tom Weber', 'total' => 79.99, 'status' => 'Bezahlt', 'order_date' => '2023-03-03 14:45:00'],
    ['id' => 4, 'customer' => 'Laura Becker', 'total' => 69.98, 'status' => 'Versandt', 'order_date' => '2023-03-04 10:00:00'],
    ['id' => 5, 'customer' => 'Felix Koch', 'total' => 1039.98, 'status' => 'Storniert', 'order_date' => '2023-03-05 16:20:00'],
    ['id' => 6, 'customer' => 'Maria Schneider', 'total' => 299.99, 'status' => 'Bezahlt', 'order_date' => '2023-03-06 13:10:00'],
    ['id' => 7, 'customer' => 'Paul Wagner', 'total' => 849.97, 'status' => 'Versandt', 'order_date' => '2023-03-07 09:30:00'],
    ['id' => 8, 'customer' => 'Sophie Hoffmann', 'total' => 129.99, 'status' => 'In Bearbeitung', 'order_date' => '2023-03-08 15:45:00'],
];
$listGenerator->setData($orders);

// Spalten definieren
$listGenerator->addColumn('id', 'Bestellnummer');
$listGenerator->addColumn('customer', 'Kunde');
$listGenerator->addColumn('total', 'Gesamtbetrag', [
    'formatter' => function ($value) {
        return number_format($value, 2, ',', '.') . ' €';
    }
]);
$listGenerator->addColumn('status', 'Status');
$listGenerator->addColumn('order_date', 'Bestelldatum', [
    'formatter' => function ($value) {
        return date('d.m.Y H:i', strtotime($value));
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
    'callback' => 'deleteOrder',
    'params' => ['id'],
    'confirmMessage' => 'Sind Sie sicher, dass Sie diese Bestellung löschen möchten?'
]);

// Setzen der Spaltentitel für die Buttons
$listGenerator->setButtonColumnTitle('left', 'Bearbeiten');
$listGenerator->setButtonColumnTitle('right', 'Löschen');

echo $listGenerator->generateList();

if (isset($db)) {
    $db->close();
}
?>