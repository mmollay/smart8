<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../f_config.php';

$_SESSION['SetYear'] = $_SESSION['default_year'] ?? date('Y');
$document = $data['document'] ?? 'expense';

$str_button_name = 'Ausgaben';
$str_button_name2 = 'Ausgabe';

// Konfiguration des ListGenerators
$listConfig = [
    'listId' => 'expense_list',
    'contentId' => 'content_expenses',
    'itemsPerPage' => 25,
    'sortColumn' => $_GET['sort'] ?? 'expense_number',
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
        expenses.expense_id, 
        expense_number,
        DATE_FORMAT(expense_date,'%Y-%m-%d') as expense_date_show,
        due_date,
        suppliers.company_name,
        suppliers.contact_person,
        suppliers.postal_code,
        suppliers.city,
        ROUND(expenses.total_amount,2) as total_amount,
        expenses.paid,
        suppliers.email,
        CASE
            WHEN expenses.paid = 1 THEN '<div class=\"info_text\" style=\"color:green\">Bezahlt</div>'
            WHEN CURDATE() > expenses.due_date THEN '<div class=\"info_text\" style=\"color:red\">Überfällig</div>'
            ELSE '<div class=\"info_text\" style=\"color:orange\">Offen</div>'
        END as payment_status,
        IF(suppliers.email = '', '', CONCAT('<i class=\"icon mail tooltip\" title=\"', suppliers.email, '\"></i>')) as email_icon
    FROM 
        expenses 
        LEFT JOIN suppliers ON expenses.supplier_id = suppliers.supplier_id
    GROUP BY 
        expenses.expense_id
";

$listGenerator->setSearchableColumns([
    'expense_number',
    'suppliers.company_name',
    'suppliers.contact_person',
    'expenses.total_amount'
]);
$listGenerator->setDatabase($db, $query, true);

// Spalten definieren
$columns = [
    ['name' => 'expense_number', 'label' => 'Ausgabennummer'],
    ['name' => 'expense_date_show', 'label' => 'Ausgabendatum'],
    ['name' => 'company_name', 'label' => 'Lieferant'],
    ['name' => 'contact_person', 'label' => 'Ansprechpartner'],
    ['name' => 'payment_status', 'label' => 'Status', 'allowHtml' => true],
    ['name' => 'email_icon', 'label' => "<i class='icon mail'></i>", 'align' => 'center', 'allowHtml' => true],
    [
        'name' => 'total_amount',
        'label' => 'Betrag',
        'formatter' => 'euro',
        'align' => 'right',
        'showTotal' => true,
        'totalType' => 'sum',
        'totalLabel' => 'Gesamt:'
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
        'params' => ['update_id' => 'expense_id'],
        'conditions' => [
            function ($row) {
                return $row['paid'] == 0;
            }
        ]
    ],
    'print' => [
        'icon' => 'print',
        'position' => 'right',
        'class' => 'mini',
        'popup' => 'PDF & Drucken',
        'onclick' => "call_pdf('{id}')"
    ],
    'delete' => [
        'icon' => 'trash',
        'position' => 'right',
        'class' => 'mini',
        'modalId' => 'modal_form_delete',
        'popup' => 'Löschen',
        'params' => ['delete_id' => 'expense_id', 'entity_type' => ['type' => 'fixed', 'value' => 'expense']],
        'conditions' => [
            function ($row) {
                return $row['paid'] == 0;
            }
        ]
    ]
];

foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

// Modals definieren
$modals = [
    'modal_form' => ['title' => "<i class='icon edit'></i> $str_button_name2 bearbeiten", 'content' => 'form/f_expenses.php'],
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

// Ermittle das früheste Jahr aus der expenses Tabelle
$earliestYearQuery = "SELECT YEAR(MIN(expense_date)) as earliest_year FROM expenses";
$result = $db->query($earliestYearQuery);
$earliestYear = $result->fetch_assoc()['earliest_year'];

// Aktuelles Jahr
$currentYear = date('Y');

// Erstelle ein Array mit Jahren vom frühesten Jahr bis zum nächsten Jahr
$years = range($earliestYear, $currentYear + 1);

// Erstelle die Filteroptionen
$yearOptions = [];
foreach ($years as $year) {
    $yearOptions['YEAR(expense_date)= "' . $year . '"'] = $year;
}

// Füge den Filter hinzu
$listGenerator->addFilter('year_filter', 'Jahr', $yearOptions, [
    'filterType' => 'complex',
    'placeholder' => 'Jahr auswählen'
]);

// Monats Filter
$months = [
    '01' => 'Januar',
    '02' => 'Februar',
    '03' => 'März',
    '04' => 'April',
    '05' => 'Mai',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'August',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Dezember'
];
$monthOptions = [];
foreach ($months as $num => $name) {
    $monthOptions['DATE_FORMAT(expense_date, "%m") = "' . $num . '"'] = $name;
}
$listGenerator->addFilter('month_filter', 'Monat', $monthOptions, [
    'filterType' => 'complex',
    'placeholder' => 'Monat auswählen'
]);

// Quartals Filter
$listGenerator->addFilter('quarter_filter', 'Quartal', [
    'QUARTER(expense_date) = 1' => 'Q1 (Jan-Mär)',
    'QUARTER(expense_date) = 2' => 'Q2 (Apr-Jun)',
    'QUARTER(expense_date) = 3' => 'Q3 (Jul-Sep)',
    'QUARTER(expense_date) = 4' => 'Q4 (Okt-Dez)',
], [
    'filterType' => 'complex',
    'placeholder' => 'Quartal auswählen'
]);

// Komplexe Filter hinzufügen
$listGenerator->addFilter('ausgaben_filter', 'Ausgabenfilter', [
    '1=1' => 'Alle Ausgaben',
    'expenses.paid = 0 AND expenses.due_date < CURDATE()' => 'Überfällige Ausgaben',
    'expenses.paid = 0' => 'Offene Ausgaben',
    'expenses.paid = 1' => 'Bezahlte Ausgaben',
    'expenses.total_amount = 0' => 'Ausgaben mit 0 Summe',
    'suppliers.email = ""' => 'Ausgaben ohne Emailempfänger',
    'suppliers.email != ""' => 'Ausgaben mit Emailempfänger'
], [
    'filterType' => 'complex',
    'where' => null,
    'placeholder' => 'Ausgabenfilter auswählen'
]);

// Liste generieren und ausgeben
echo $listGenerator->generateList();

// Datenbankverbindung schließen
if (isset($db)) {
    $db->close();
}
?>

<script>
    function call_pdf(id) {
        window.open('pdf_generator.php?expense=' + id, '_blank');
    }
</script>