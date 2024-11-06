<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../f_config.php';

$str_button_name = 'ELBA-Transaktionen';
$str_button_name2 = 'ELBA-Transaktion';

// Konfiguration des ListGenerators
$listConfig = [
    'listId' => 'elba_list',
    'contentId' => 'content_elba',
    'itemsPerPage' => 25,
    'sortColumn' => $_GET['sort'] ?? 'date',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'DESC'),
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
        elba_id, 
        account,
        date,
        text,
        amount,
        booking_date,
        timestamp,
        comment,
        connect_id,
        automator_id
    FROM 
        data_elba
";

$listGenerator->setSearchableColumns([
    'account',
    'text',
    'comment'
]);
$listGenerator->setDatabase($db, $query, true);

// Spalten definieren
$columns = [
    ['name' => 'date', 'label' => 'Datum'],
    ['name' => 'account', 'label' => 'Konto'],
    ['name' => 'text', 'label' => 'Beschreibung'],
    [
        'name' => 'amount',
        'label' => 'Betrag',
        'formatter' => 'euro',
        'align' => 'right'
    ],
    ['name' => 'booking_date', 'label' => 'Buchungsdatum'],
    ['name' => 'comment', 'label' => 'Kommentar'],
];

foreach ($columns as $column) {
    $listGenerator->addColumn($column['name'], $column['label'], $column);
}

// Buttons definieren
$buttons = [
    'delete' => [
        'icon' => 'trash',
        'position' => 'right',
        'class' => 'mini',
        'modalId' => 'modal_form_delete',
        'popup' => 'Löschen',
        'params' => ['delete_id' => 'elba_id', 'entity_type' => ['type' => 'fixed', 'value' => 'elba']],
    ]
];

foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

// Modals definieren
$modals = ['modal_form_delete' => ['title' => "$str_button_name2 entfernen", 'class' => 'small', 'content' => 'form/f_delete.php'],];

foreach ($modals as $id => $modal) {
    $listGenerator->addModal($id, $modal);
}

// Filter hinzufügen
$listGenerator->addFilter('amount_filter', 'Betrag', [
    'amount > 0' => 'Einnahmen',
    'amount < 0' => 'Ausgaben',
    'amount = 0' => 'Nullbuchungen'
], [
    'filterType' => 'complex',
    'placeholder' => 'Betrag filtern'
]);

$listGenerator->addFilter('date_filter', 'Zeitraum', [
    'date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)' => 'Letzter Monat',
    'date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)' => 'Letzte 3 Monate',
    'date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)' => 'Letzte 6 Monate',
    'YEAR(date) = YEAR(CURDATE())' => 'Dieses Jahr'
], [
    'filterType' => 'complex',
    'placeholder' => 'Zeitraum auswählen'
]);

// Externe Buttons
$listGenerator->addExternalButton('import_elba', [
    'icon' => 'upload',
    'class' => 'ui blue circular button',
    'position' => 'top',
    'alignment' => 'left',
    'title' => "ELBA-Daten importieren",
    'modalId' => 'modal_import_elba',
]);

// Liste generieren und ausgeben
echo $listGenerator->generateList();

// Datenbankverbindung schließen
if (isset($db)) {
    $db->close();
}
?>

<script>
    function importELBA() {
        // Implementieren Sie hier die Logik zum Importieren von ELBA-Daten
        console.log('ELBA-Daten werden importiert');
    }
</script>