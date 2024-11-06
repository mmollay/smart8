<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../f_config.php';

$_SESSION['SetYear'] = $_SESSION['default_year'] ?? date('Y');
$document = $data['document'] ?? 'rn';

$str_button_name = ($document == 'ang') ? 'Angebote' : 'Rechnungen';
$str_button_name2 = ($document == 'ang') ? 'Angebot' : 'Rechnung';

// Konfiguration des ListGenerators
$listConfig = [
    'listId' => 'bill_list',
    'contentId' => 'content_bills',
    'itemsPerPage' => 25,
    'sortColumn' => $_GET['sort'] ?? 'bill_number',
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

// Jahresbedingung für die Abfrage
$yearCondition = '';
if (isset($_SESSION['SetYear']) && $_SESSION['SetYear'] != 'all') {
    $year = $_SESSION['SetYear'];
    $yearCondition = "AND DATE_FORMAT(date_membership_start,'%Y') <= $year 
        AND (DATE_FORMAT(date_membership_stop,'%Y-%m-%d') >= NOW() OR date_membership_stop = '0000-00-00')";
}

// Hauptabfrage
$query = "
    SELECT 
        bills.bill_id, 
        bill_number,
        client_number,
        DATE_FORMAT(date_create,'%Y-%m-%d') as date_create_show,
        date_booking,
        date_send,
        date_remind,
        remind_level,
        bills.netto,
        post,
        firstname,
        secondname,
        zip,
        city,
        ROUND(brutto,2) as brutto,
        date_storno, 
        (SELECT company_1 FROM company WHERE company_id = bills.company_id) as title_company, 
        IF(email = '', '', CONCAT('<i class=\"icon mail tooltip\" title=\"', email, '\"></i>')) as email,    
        IF(ROUND(booking_total) != ROUND(brutto), ROUND(brutto-booking_total,2), '0,00') as booking_total,        
        CASE 
            WHEN status = 'queued' THEN 'Versendet'
            WHEN status = 'sent' THEN '<h5 style=\"font-size:12px\" class=\"ui grey header\">Zugestellt</h5>'
            WHEN status = 'open' THEN '<h5 style=\"font-size:12px\" class=\"ui green header\">Geöffnet</h5>'
            WHEN status = 'click' THEN '<h5 style=\"font-size:12px\" class=\"ui green header\">Angegklickt</h5>'
            WHEN status = 'blocked' THEN '<h5 style=\"font-size:12px\" class=\"ui black header\">Geblockt</h5>'
            WHEN status = 'bounce' THEN '<h5 style=\"font-size:12px\" class=\"ui red header\">Unzustellbar</h5>'
            WHEN status = 'spam' THEN '<h5 style=\"font-size:12px\" class=\"ui red header\">Spam</h5>'
            WHEN status = 'unsub' THEN '<h5 style=\"font-size:12px\" class=\"ui red header\">Abgemeldet</h5>'
            ELSE status
        END as status,
        CASE
            WHEN date_storno != '0000-00-00' THEN CONCAT('<div class=\"info_text\" style=\"color:red\">', date_storno, ' (Storno)</div>')
            WHEN date_booking != '0000-00-00' THEN CONCAT('<div class=\"info_text\" style=\"color:green\">', date_booking, ' (Verbucht)</div>')
            WHEN date_send = '0000-00-00' THEN CONCAT('<button class=\"ui button blue mini\" onclick=\"send_pdf(', bills.bill_id, ')\"><i class=\"icon send\"></i> Versenden</button>')
            ELSE
                CASE
                    WHEN remind_level = 1 AND date_remind > NOW() THEN CONCAT('<span class=\"info_text\" style=\"color:grey\">Mahnung 1 in ', DATEDIFF(date_remind, NOW()), ' Tagen</span>')
                    WHEN remind_level = 1 THEN CONCAT('<button class=\"ui button yellow mini\" onclick=\"send_pdf(', bills.bill_id, ', true)\" title=\"seit ', DATEDIFF(NOW(), date_remind), ' Tagen\">Mahnung 1</button>')
                    WHEN remind_level = 2 AND date_remind > NOW() THEN CONCAT('<span class=\"info_text\" style=\"color:grey\">Mahnung 2 in ', DATEDIFF(date_remind, NOW()), ' Tagen</span>')
                    WHEN remind_level = 2 THEN CONCAT('<button class=\"ui button orange mini\" onclick=\"send_pdf(', bills.bill_id, ', true)\" title=\"seit ', DATEDIFF(NOW(), date_remind), ' Tagen\">Mahnung 2</button>')
                    WHEN remind_level = 3 AND date_remind > NOW() THEN CONCAT('<span class=\"info_text\" style=\"color:grey\">Mahnung 3 in ', DATEDIFF(date_remind, NOW()), ' Tagen</span>')
                    WHEN remind_level = 3 THEN CONCAT('<button class=\"ui button red mini\" onclick=\"send_pdf(', bills.bill_id, ', true)\" title=\"seit ', DATEDIFF(NOW(), date_remind), ' Tagen\">Mahnung 3</button>')
                    WHEN remind_level > 3 AND date_remind > NOW() THEN CONCAT('<span class=\"info_text\" style=\"color:grey\">Inkasso in ', DATEDIFF(date_remind, NOW()), ' Tagen</span>')
                    WHEN remind_level > 3 THEN CONCAT('<button class=\"ui button red mini\" onclick=\"send_pdf(', bills.bill_id, ', \'\', true)\">Inkasso seit ', DATEDIFF(NOW(), date_remind), ' Tagen</button>')
                    ELSE ''
                END
        END as send_status,
        IF(tel, CONCAT('<button class=\"client_info\" title=\"Tel:', tel, '\">Info</button>'), '') as tel,
        CASE
            WHEN LENGTH(company_1) >= 40 THEN CONCAT(SUBSTRING(company_1, 1, 40), '<span class=\"km_info\" title=\"', company_1, '\">[...]</span>')
            WHEN company_1 = '' THEN CONCAT(firstname, ' ', secondname)
            ELSE company_1
        END as company_1
    FROM 
        bills 
        LEFT JOIN bill_details ON bills.bill_id = bill_details.bill_id 
        LEFT JOIN logfile ON (bills.bill_id = logfile.bill_id AND log_id = (SELECT MAX(log_id) FROM logfile WHERE bill_id = bills.bill_id))
        WHERE document = 'rn'
    GROUP BY 
        bills.bill_id
";

$listGenerator->setSearchableColumns([
    'bill_number',
    'title',
    'company_1',
    'firstname',
    'secondname',
    'brutto'
]);
$listGenerator->setDatabase($db, $query, true);


// Spalten definieren
$columns = [
    ['name' => 'title_company', 'label' => 'Firma'],
    ['name' => 'date_create_show', 'label' => 'Datum'],
    ['name' => 'bill_number', 'label' => 'Folgenummer'],
    ['name' => 'company_1', 'label' => 'Firma'],
    ['name' => 'send_status', 'label' => 'Status', 'allowHtml' => true],
    ['name' => 'status', 'label' => "<i class='icon mail'></i>", 'align' => 'center', 'allowHtml' => true],
    [
        'name' => 'brutto',
        'label' => 'Brutto',
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
        'modalId' => 'modal_form_edit',
        'popup' => 'Bearbeiten',
        'params' => ['update_id' => 'bill_id'],
        'conditions' => [
            function ($row) {
                return $row['date_booking'] == '0000-00-00';
            }
        ]
    ],
    'clone' => [
        'icon' => 'copy',
        'position' => 'left',
        'class' => 'mini',
        'modalId' => 'modal_form_clone',
        'popup' => 'Klonen',
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
        'params' => ['delete_id' => 'bill_id'],
        'conditions' => [
            function ($row) {
                return $row['date_booking'] == '0000-00-00';
            }
        ]
    ],
    'send' => [
        'icon' => 'mail',
        'position' => 'right',
        'class' => 'mini',
        'modalId' => 'modal_form_send',
        'popup' => 'Versenden per Email'
    ],
    'logbook' => [
        'icon' => 'file text outline',
        'position' => 'right',
        'class' => 'mini',
        'modalId' => 'modal_logbook',
        'popup' => 'Logbuch einsehen'
    ]
];

if ($document == 'rn') {
    $buttons['unlock'] = [
        'icon' => 'lock',
        'position' => 'right',
        'class' => 'mini',
        'popup' => 'Verbuchen aufheben',
        'onclick' => "call_unbooking('{id}')",
        'conditions' => [
            function ($row) {
                return $row['date_booking'] != '0000-00-00';
            }
        ]
    ];
    $buttons['storno'] = [
        'icon' => 'remove circle',
        'position' => 'right',
        'class' => 'mini',
        'popup' => 'Stornieren',
        'onclick' => "storno_bill('{id}')",
        'conditions' => [
            function ($row) {
                return $row['date_booking'] == '0000-00-00';
            }
        ]
    ];
}

foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

// Modals definieren
$modals = [
    'modal_logbook' => ['title' => "<i class='icon edit'></i> Logbuch bearbeiten", 'content' => "form_logbook.php?update_id={id}"],
    'modal_form_clone' => ['title' => "<i class='icon copy'></i> $str_button_name2 bearbeiten", 'class' => 'long', 'content' => 'form/f_bills.php?clone=1'],
    'modal_form' => ['title' => 'Kunden bearbeiten', 'content' => 'form/f_bills.php', 'size' => 'large'],
    'modal_form_new' => ['title' => "<i class='icon edit'></i> $str_button_name2 erstellen", 'content' => "form/f_bills.php?document=$document"],
    'modal_form_delete' => ['title' => "$str_button_name2 entfernen", 'class' => 'small', 'content' => 'form_delete.php'],
    'modal_form_send' => ['title' => "$str_button_name2 versenden", 'content' => 'form_send.php'],
    'modal_form_print' => ['title' => 'War der Druckervorgang erfolgreich?', 'class' => 'small', 'content' => 'form_print.php?all=1'],
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
    SELECT COUNT(*) FROM bills 
    WHERE COALESCE(remind_level, 0) = 0
    AND date_booking = '0000-00-00'
    AND date_send = '0000-00-00'
    AND date_storno = '0000-00-00'
    AND email != ''
    AND document = '$document'
")->fetch_row()[0];

// Zähler für zu druckende Rechnungen
$count_open_print = $db->query("
    SELECT COUNT(*) FROM bills
    WHERE COALESCE(remind_level, 0) = 0
    AND date_booking = '0000-00-00'
    AND date_storno = '0000-00-00'
    AND document = '$document'
    AND (email = '' OR post = 1)
")->fetch_row()[0];

if ($count_open_mail > 0) {
    $listGenerator->addExternalButton('send_bills', [
        'icon' => 'send',
        'class' => 'ui green circular button',
        'position' => 'top',
        'alignment' => 'right',
        'title' => "<span id='count_open_mail'>$count_open_mail</span>",
        'popup' => "Zuversendende $str_button_name",
        'onclick' => "send_all_bills();"
    ]);
}


// Bestehende Filter beibehalten

// Ermittle das früheste Jahr aus der bills Tabelle
$earliestYearQuery = "SELECT YEAR(MIN(date_create)) as earliest_year FROM bills";
$result = $db->query($earliestYearQuery);
$earliestYear = $result->fetch_assoc()['earliest_year'];

// Aktuelles Jahr
$currentYear = date('Y');

// Erstelle ein Array mit Jahren vom frühesten Jahr bis zum nächsten Jahr
$years = range($earliestYear, $currentYear + 1);

// Erstelle die Filteroptionen
$yearOptions = [];
foreach ($years as $year) {
    $yearOptions['YEAR(date_create)= "' . $year . '"'] = $year;
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
    $monthOptions['DATE_FORMAT(date_create, "%m") = "' . $num . '"'] = $name;
}
$listGenerator->addFilter('month_filter', 'Monat', $monthOptions, [
    'filterType' => 'complex',
    'placeholder' => 'Monat auswählen'
]);

// Quartals Filter
$listGenerator->addFilter('quarter_filter', 'Quartal', [
    'QUARTER(date_create) = 1' => 'Q1 (Jan-Mär)',
    'QUARTER(date_create) = 2' => 'Q2 (Apr-Jun)',
    'QUARTER(date_create) = 3' => 'Q3 (Jul-Sep)',
    'QUARTER(date_create) = 4' => 'Q4 (Okt-Dez)',
], [
    'filterType' => 'complex',
    'placeholder' => 'Quartal auswählen'
]);

// Komplexe Filter hinzufügen
$listGenerator->addFilter('rechnungs_filter', 'Rechnungsfilter', [
    'date_storno = "0000-00-00"' => 'Alle Rechnungen',
    'date_remind < NOW() and date_booking = "0000-00-00" and remind_level != 0 and date_storno = "0000-00-00"' => 'Zu mahnende Kunden',
    'date_booking = "0000-00-00"' => 'Alle Rechnungen mit Storno',
    'date_booking = "0000-00-00" and date_storno = "0000-00-00"' => 'Offene Rechnungen',
    '(ROUND(booking_total,2) != ROUND(brutto,2)) and date_booking != "0000-00-00"' => 'Verbuchte Rechnungen mit offenen Beträgen',
    'date_booking != "0000-00-00" and date_storno = "0000-00-00"' => 'Verbuchte Rechnungen',
    'remind_level = 0 and date_send = "0000-00-00" and date_booking = "0000-00-00" and date_storno = "0000-00-00"' => 'Noch nicht versendete Rechnungen',
    'remind_level = 4 and date_remind < NOW() and date_booking = "0000-00-00" and date_storno = "0000-00-00"' => 'Inkasso Fälle',
    'date_storno != "0000-00-00"' => 'Stornierte Rechnungen',
    'netto = "0.000"' => 'Rechnungen mit 0 Summe',
    'DATE_FORMAT(date_booking,"%Y") = "' . date('Y') . '"' => "Verbuchte Rechnungen " . date('Y'),
    'email = ""' => 'Rechnungen ohne Emailempfänger',
    'email != ""' => 'Rechnungen mit Emailempfänger',
    'client_id = ""' => 'Rechnungen ohne Kundennummer'
], [
    'filterType' => 'complex',
    'where' => null, // Die WHERE-Bedingung wird dynamisch aus den Optionen generiert
    'placeholder' => 'Rechnungsfilter auswählen'
]);

// Zahlungsstatus Filter
// $listGenerator->addFilter('payment_status', 'Zahlungsstatus', [
//     'date_booking = "0000-00-00" AND date_storno = "0000-00-00"' => 'Unbezahlt',
//     'date_booking != "0000-00-00"' => 'Bezahlt',
//     'date_storno != "0000-00-00"' => 'Storniert',
//     'date_booking != "0000-00-00" AND DATEDIFF(date_booking, date_create) <= 30' => 'Pünktlich bezahlt',
//     'date_booking != "0000-00-00" AND DATEDIFF(date_booking, date_create) > 30' => 'Verspätet bezahlt',
// ], [
//     'filterType' => 'complex',
//     'placeholder' => 'Zahlungsstatus wählen'
// ]);

// Liste generieren und ausgeben
echo $listGenerator->generateList();

// Datenbankverbindung schließen
if (isset($db)) {
    $db->close();
}
?>

<script>
    function send_all_bills() {
        // Implementieren Sie hier die Logik zum Versenden aller Rechnungen
        console.log('Alle Rechnungen werden versendet');
    }

    function call_pdf(id) {
        window.open('pdf_generator.php?bill=' + id, '_blank');
    }

    function call_unbooking(id) {
        // Implementieren Sie hier die Logik zum Aufheben der Verbuchung
        console.log('Verbuchung aufgehoben für ID: ' + id);
    }

    function storno_bill(id) {
        // Implementieren Sie hier die Logik zum Stornieren einer Rechnung
        console.log('Rechnung storniert mit ID: ' + id);
    }

    function send_pdf(id, isReminder = false, isInkasso = false) {
        let url = 'pdf_generator.php?bill=' + id;
        if (isReminder) url += '&remind=1';
        if (isInkasso) url += '&inkasso=1';
        window.open(url, '_blank');
    }
</script>