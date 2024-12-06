<?php
include __DIR__ . '/../ListGenerator.php';

// ListGenerator initialisieren
$listGenerator = new ListGenerator([
    'listId' => 'productList',
    'contentId' => 'content1',
    'itemsPerPage' => 10,
    'sortColumn' => $_GET['sort'] ?? 'id',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'ASC'),
    'search' => $_GET['search'] ?? '',
    'page' => intval($_GET['page'] ?? 1),
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Produkte gefunden.',
    'tableClasses' => 'ui celled table',
    'headerClasses' => 'ui table',
    'color' => 'blue',
    'size' => 'small'
]);

// Datenbankverbindung
$db = new mysqli('localhost', 'root', 'deinpasswort', 'deinedatenbank');

if ($db->connect_error) {
    die("Verbindung fehlgeschlagen: " . $db->connect_error);
}

// SQL-Abfrage
$query = "
    SELECT 
        id,
        name,
        price,
        category,
        stock,
        active
    FROM products
";

// Durchsuchbare Spalten festlegen
$listGenerator->setSearchableColumns(['name', 'category']);
$listGenerator->setDatabase($db, $query);

// Filter hinzufügen
$listGenerator->addFilter('category', 'Kategorie', [
    'Hardware' => 'Hardware',
    'Software' => 'Software',
    'Zubehör' => 'Zubehör'
]);

$listGenerator->addFilter('active', 'Status', [
    '1' => 'Aktiv',
    '0' => 'Inaktiv'
]);

// Spalten definieren
$listGenerator->addColumn('id', 'ID');
$listGenerator->addColumn('name', 'Produktname');
$listGenerator->addColumn('price', 'Preis', [
    'formatter' => 'euro',
    'align' => 'right'
]);
$listGenerator->addColumn('category', 'Kategorie');
$listGenerator->addColumn('stock', 'Lagerbestand', [
    'align' => 'right'
]);
$listGenerator->addColumn('active', 'Status', [
    'formatter' => function ($value) {
        return $value == 1 ?
            "<span class='ui green label'>Aktiv</span>" :
            "<span class='ui red label'>Inaktiv</span>";
    },
    'allowHtml' => true
]);

// Buttons für Aktionen
$listGenerator->addButton('edit', [
    'icon' => 'edit',
    'position' => 'left',
    'class' => 'blue mini',
    'modalId' => 'modal_edit_product',
    'popup' => 'Bearbeiten',
    'params' => ['product_id' => 'id']
]);

$listGenerator->addButton('delete', [
    'icon' => 'trash',
    'position' => 'right',
    'class' => 'red mini',
    'modalId' => 'modal_delete_product',
    'popup' => 'Löschen',
    'params' => ['product_id' => 'id']
]);

// Modals definieren
$listGenerator->addModal('modal_edit_product', [
    'title' => 'Produkt bearbeiten',
    'class' => 'small',
    'url' => 'form_edit_product.php'
]);

$listGenerator->addModal('modal_delete_product', [
    'title' => 'Produkt löschen',
    'class' => 'tiny',
    'url' => 'form_delete_product.php'
]);

// Externe Buttons (über der Liste)
$listGenerator->addExternalButton('new_product', [
    'icon' => 'plus',
    'class' => 'ui blue button',
    'position' => 'top',
    'alignment' => 'left',
    'title' => 'Neues Produkt',
    'modalId' => 'modal_new_product'
]);

// Modal für neues Produkt
$listGenerator->addModal('modal_new_product', [
    'title' => 'Neues Produkt anlegen',
    'class' => 'small',
    'url' => 'form_new_product.php'
]);

// Liste generieren und ausgeben
echo $listGenerator->generateList();

// Datenbankverbindung schließen
$db->close();
?>