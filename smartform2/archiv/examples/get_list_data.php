<?php
// get_list_data.php

require_once '../ListGenerator.php';
require_once __DIR__ . '/mysql.php';

// ListGenerator Initialisierung
$listGenerator = new ListGenerator([
    'listId' => 'userList',
    'itemsPerPage' => 10,
    'sortColumn' => $_GET['sortColumn'] ?? 'id',
    'sortDirection' => $_GET['sortDirection'] ?? 'ASC',
    'search' => $_GET['search'] ?? '',
    'page' => intval($_GET['page'] ?? 1),
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Benutzer gefunden.',
    'showFooter' => true,
    'footerText' => 'Gesamt: {totalRows} Benutzer | Seite {currentPage} von {totalPages}',
    'showPagination' => true,
]);

// Datenbank-Abfrage
$query = "SELECT id, first_name, last_name, email, role, status FROM users";
$listGenerator->setDatabase($db, $query, true);

// Spalten definieren
$listGenerator->addColumn('id', 'ID');
$listGenerator->addColumn('first_name', 'Vorname');
$listGenerator->addColumn('last_name', 'Nachname');
$listGenerator->addColumn('email', 'E-Mail');
$listGenerator->addColumn('role', 'Rolle');
$listGenerator->addColumn('status', 'Status');

// Button zum Bearbeiten hinzufügen
$listGenerator->addButton('edit', [
    'label' => '',
    'icon' => 'edit',
    'class' => 'ui blue tiny button',
    'position' => 'right',
    'params' => ['id'],
    'callback' => 'editUser'
]);

// Liste generieren und zurückgeben
echo $listGenerator->generate();