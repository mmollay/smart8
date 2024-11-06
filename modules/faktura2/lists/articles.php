<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../f_config.php';

$str_button_name = 'Artikel';
$str_button_name2 = 'Artikel';

// Konfiguration des ListGenerators
$listConfig = [
    'listId' => 'article_list',
    'contentId' => 'content_articles',
    'itemsPerPage' => 25,
    'sortColumn' => $_GET['sort'] ?? 'article_number',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'ASC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => "Keine $str_button_name gefunden.",
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'width' => '100%',
    'tableClasses' => 'ui celled striped definition small compact very selectable table',
    'debug' => true,
];

$listGenerator = new ListGenerator($listConfig);

// Hauptabfrage
$query = "
    SELECT 
        articles.article_id,
        articles.article_number,
        articles.name,
        articles.description,
        articles.unit,
        ROUND(articles.price, 2) as price,
        accounts.account_number,
        accounts.account_name,
        IFNULL(accounts.percentage, 0) as vat_rate
    FROM 
        articles
    LEFT JOIN 
        accounts ON articles.account_id = accounts.account_id
";

$listGenerator->setSearchableColumns([
    'articles.article_number',
    'articles.name',
    'articles.description',
    'accounts.account_number',
    'accounts.account_name'
]);

$listGenerator->setDatabase($db, $query, true);

// Spalten definieren
$columns = [
    ['name' => 'article_number', 'label' => 'Artikelnummer'],
    ['name' => 'name', 'label' => 'Artikelname'],
    ['name' => 'description', 'label' => 'Beschreibung'],
    ['name' => 'unit', 'label' => 'Einheit'],
    [
        'name' => 'price',
        'label' => 'Preis',
        'formatter' => 'euro',
        'align' => 'right'
    ],
    [
        'name' => 'account_number',
        'label' => 'Kontonummer',
        'align' => 'right'
    ],
    [
        'name' => 'account_name',
        'label' => 'Kontoname'
    ],
    [
        'name' => 'vat_rate',
        'label' => 'MwSt. (%)',
        'align' => 'right'
    ],
];

foreach ($columns as $column) {
    $listGenerator->addColumn($column['name'], $column['label'], $column);
}

// Buttons definieren
$buttons = [
    'edit' => [
        'icon' => 'edit',
        'position' => 'left',
        'class' => 'blue mini',
        'modalId' => 'modal_form',
        'popup' => 'Bearbeiten',
        'params' => ['update_id' => 'article_id']
    ],
    'delete' => [
        'icon' => 'trash',
        'position' => 'right',
        'class' => 'mini',
        'modalId' => 'modal_form_delete',
        'popup' => 'Löschen',
        'params' => ['delete_id' => 'article_id', 'entity_type' => ['type' => 'fixed', 'value' => 'article']]
    ]
];

foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

// Modals definieren
$modals = [
    'modal_form' => ['title' => "$str_button_name2 bearbeiten", 'content' => 'form/f_articles.php'],
    'modal_form_delete' => ['title' => "$str_button_name2 entfernen", 'class' => 'small', 'content' => 'form/f_delete.php'],
];

foreach ($modals as $id => $modal) {
    $listGenerator->addModal($id, $modal);
}

// Externe Buttons
$listGenerator->addExternalButton('new_entry', [
    'icon' => 'plus',
    'class' => 'ui blue circular button',
    'position' => 'top',
    'alignment' => 'left',
    'title' => "$str_button_name2 erstellen",
    'modalId' => 'modal_form',
]);

// Filter hinzufügen
$listGenerator->addFilter('price_filter', 'Preis', [
    'articles.price <= 10' => 'Bis 10 €',
    'articles.price > 10 AND articles.price <= 50' => '10 € - 50 €',
    'articles.price > 50 AND articles.price <= 100' => '50 € - 100 €',
    'articles.price > 100' => 'Über 100 €'
], [
    'filterType' => 'complex',
    'placeholder' => 'Preisbereich auswählen'
]);

$listGenerator->addFilter('vat_filter', 'MwSt. Satz', [
    'accounts.percentage = 19' => '19%',
    'accounts.percentage = 7' => '7%',
    'accounts.percentage = 0' => '0%',
    'accounts.percentage NOT IN (19, 7, 0)' => 'Andere'
], [
    'filterType' => 'complex',
    'placeholder' => 'MwSt. Satz auswählen'
]);

// Liste generieren und ausgeben
echo $listGenerator->generateList();

// Datenbankverbindung schließen
if (isset($db)) {
    $db->close();
}
?>