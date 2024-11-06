<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../f_config.php';

$_SESSION['SetYear'] = $_SESSION['default_year'] ?? date('Y');
$document = $data['document'] ?? 'invoice';

$str_button_name = 'Rechnungen';
$str_button_name2 = 'Rechnung';

// Konfiguration des ListGenerators
$listConfig = [
    'listId' => 'invoice_list',
    'contentId' => 'content_invoices',
    'itemsPerPage' => 25,
    'sortColumn' => $_GET['sort'] ?? 'invoice_number',
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
        invoices.invoice_id, 
        invoice_number,
        DATE_FORMAT(invoice_date,'%Y-%m-%d') as invoice_date_show,
        due_date,
        customers.company_name,
        customers.contact_person,
        customers.postal_code,
        customers.city,
        ROUND(invoices.total_amount,2) as total_amount,
        invoices.paid,
        customers.email,
        CASE
            WHEN invoices.paid = 1 THEN '<div class=\"info_text\" style=\"color:green\">Bezahlt</div>'
            WHEN CURDATE() > invoices.due_date THEN '<div class=\"info_text\" style=\"color:red\">Überfällig</div>'
            ELSE '<div class=\"info_text\" style=\"color:orange\">Offen</div>'
        END as payment_status,
        IF(customers.email = '', '', CONCAT('<i class=\"icon mail tooltip\" title=\"', customers.email, '\"></i>')) as email_icon
    FROM 
        invoices 
        LEFT JOIN customers ON invoices.customer_id = customers.customer_id
    GROUP BY 
        invoices.invoice_id
";

$listGenerator->setSearchableColumns([
    'invoice_number',
    'customers.company_name',
    'customers.contact_person',
    'invoices.total_amount'
]);
$listGenerator->setDatabase($db, $query, true);

// Spalten definieren
$columns = [
    ['name' => 'invoice_number', 'label' => 'Rechnungsnummer'],
    ['name' => 'invoice_date_show', 'label' => 'Rechnungsdatum'],
    ['name' => 'company_name', 'label' => 'Firma'],
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
        'params' => ['update_id' => 'invoice_id'],
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
        'params' => ['delete_id' => 'invoice_id', 'entity_type' => ['type' => 'fixed', 'value' => 'invoice']],
        'conditions' => [
            function ($row) {
                return $row['paid'] == 0;
            }
        ]
    ],
    'send' => [
        'icon' => 'mail',
        'position' => 'right',
        'class' => 'mini',
        'modalId' => 'modal_form_send',
        'popup' => 'Versenden per Email'
    ]
];

foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

// Modals definieren
$modals = [
    'modal_form' => ['title' => 'Kunden bearbeiten', 'content' => 'form/f_invoices.php', 'size' => 'large'],
    'modal_form_delete' => ['title' => "$str_button_name2 entfernen", 'class' => 'small', 'content' => 'form/f_delete.php'],
    'modal_form_send' => ['title' => "$str_button_name2 versenden", 'content' => 'form_send.php'],
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

// Zähler für E-Mails zum Versenden
$count_open_mail = $db->query("
    SELECT COUNT(*) FROM invoices 
    LEFT JOIN customers ON invoices.customer_id = customers.customer_id
    WHERE invoices.paid = 0
    AND customers.email != ''
")->fetch_row()[0];

if ($count_open_mail > 0) {
    $listGenerator->addExternalButton('send_invoices', [
        'icon' => 'send',
        'class' => 'ui green circular button',
        'position' => 'top',
        'alignment' => 'right',
        'title' => "<span id='count_open_mail'>$count_open_mail</span>",
        'popup' => "Zuversendende $str_button_name",
        'onclick' => "send_all_invoices();"
    ]);
}

// Ermittle das früheste Jahr aus der invoices Tabelle
$earliestYearQuery = "SELECT YEAR(MIN(invoice_date)) as earliest_year FROM invoices";
$result = $db->query($earliestYearQuery);
$earliestYear = $result->fetch_assoc()['earliest_year'];

// Aktuelles Jahr
$currentYear = date('Y');

// Erstelle ein Array mit Jahren vom frühesten Jahr bis zum nächsten Jahr
$years = range($earliestYear, $currentYear + 1);

// Erstelle die Filteroptionen
$yearOptions = [];
foreach ($years as $year) {
    $yearOptions['YEAR(invoice_date)= "' . $year . '"'] = $year;
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
    $monthOptions['DATE_FORMAT(invoice_date, "%m") = "' . $num . '"'] = $name;
}
$listGenerator->addFilter('month_filter', 'Monat', $monthOptions, [
    'filterType' => 'complex',
    'placeholder' => 'Monat auswählen'
]);

// Quartals Filter
$listGenerator->addFilter('quarter_filter', 'Quartal', [
    'QUARTER(invoice_date) = 1' => 'Q1 (Jan-Mär)',
    'QUARTER(invoice_date) = 2' => 'Q2 (Apr-Jun)',
    'QUARTER(invoice_date) = 3' => 'Q3 (Jul-Sep)',
    'QUARTER(invoice_date) = 4' => 'Q4 (Okt-Dez)',
], [
    'filterType' => 'complex',
    'placeholder' => 'Quartal auswählen'
]);

// Komplexe Filter hinzufügen
$listGenerator->addFilter('rechnungs_filter', 'Rechnungsfilter', [
    '1=1' => 'Alle Rechnungen',
    'invoices.paid = 0 AND invoices.due_date < CURDATE()' => 'Überfällige Rechnungen',
    'invoices.paid = 0' => 'Offene Rechnungen',
    'invoices.paid = 1' => 'Bezahlte Rechnungen',
    'invoices.total_amount = 0' => 'Rechnungen mit 0 Summe',
    'customers.email = ""' => 'Rechnungen ohne Emailempfänger',
    'customers.email != ""' => 'Rechnungen mit Emailempfänger'
], [
    'filterType' => 'complex',
    'where' => null,
    'placeholder' => 'Rechnungsfilter auswählen'
]);

// Liste generieren und ausgeben
echo $listGenerator->generateList();

// Datenbankverbindung schließen
if (isset($db)) {
    $db->close();
}
?>

<script>
    function send_all_invoices() {
        // Implementieren Sie hier die Logik zum Versenden aller Rechnungen
        console.log('Alle Rechnungen werden versendet');
    }

    function call_pdf(id) {
        window.open('pdf_generator.php?invoice=' + id, '_blank');
    }
</script>