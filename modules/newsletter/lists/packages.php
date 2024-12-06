<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include_once __DIR__ . '/../../../config.php';

// Superuser Check
if (!isset($_SESSION['superuser']) || $_SESSION['superuser'] != 1) {
    exit('Keine Berechtigung');
}

// ListGenerator Konfiguration
$listConfig = [
    'listId' => 'packages',
    'contentId' => 'content_packages',
    'itemsPerPage' => 25,
    'sortColumn' => $_GET['sort'] ?? 'u.user_id',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'DESC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Benutzer gefunden.',
    'striped' => true,
    'selectable' => false,
    'celled' => true,
    'width' => '1200px',
];

$listGenerator = new ListGenerator($listConfig);

// Erweiterte Basis-Query mit Paket-Informationen
$baseQuery = "
    SELECT
        u.user_id,
        u.user_name,
        u.firstname,
        u.secondname,
        u.company1,
        CASE
            WHEN um.module_id = 6 THEN 
                '<div class=\"ui green mini label\"><i class=\"check icon\"></i>Aktiv</div>'
            ELSE 
                '<div class=\"ui grey mini label\"><i class=\"times icon\"></i>Inaktiv</div>'
        END as newsletter_status,
        COALESCE(um.module_id, 0) as has_newsletter,
        CASE 
            WHEN nup.package_type IS NOT NULL THEN
                CONCAT(
                    '<div class=\"ui label\">',
                    UPPER(LEFT(nup.package_type, 1)), LOWER(SUBSTRING(nup.package_type, 2)),
                    ' (', FORMAT(nup.emails_limit, 0), ' Emails)',
                    '</div>'
                )
            ELSE 
                '<div class=\"ui grey basic label\">Kein Paket</div>'
        END as package_info,
        COALESCE(nup.emails_sent, 0) as emails_used,
        COALESCE(nup.emails_limit, 0) as emails_total,
        CONCAT(
            ROUND(
                IF(nup.emails_limit > 0, 
                   (nup.emails_sent / nup.emails_limit) * 100,
                   0
                ), 
                1
            ),
            '%'
        ) as usage_percent
    FROM user2company u
    LEFT JOIN user_modules um ON u.user_id = um.user_id AND um.module_id = 6 AND um.status = 1
    LEFT JOIN newsletter_user_packages nup ON u.user_id = nup.user_id AND nup.valid_until IS NULL
    WHERE u.locked = 0
    GROUP BY u.user_id
";

$listGenerator->setSearchableColumns(['user_name', 'firstname', 'secondname', 'company1']);
$listGenerator->setDatabase($db, $baseQuery);

// Filter für Package-Typen
$listGenerator->addFilter('package_type', 'Paket', [
    'free' => 'Free',
    'standard' => 'Standard',
    'professional' => 'Professional'
]);

// Spalten definieren
$columns = [
    ['name' => 'user_name', 'label' => '<i class="mail icon"></i>E-Mail'],
    ['name' => 'firstname', 'label' => '<i class="user icon"></i>Vorname'],
    ['name' => 'secondname', 'label' => '<i class="user icon"></i>Nachname'],
    ['name' => 'company1', 'label' => '<i class="building icon"></i>Firma'],
    ['name' => 'newsletter_status', 'label' => '<i class="envelope icon"></i>Newsletter Zugang'],
    ['name' => 'package_info', 'label' => '<i class="box icon"></i>Aktuelles Paket'],
    ['name' => 'usage_percent', 'label' => '<i class="chart bar icon"></i>Nutzung']
];

foreach ($columns as $column) {
    $listGenerator->addColumn($column['name'], $column['label'], ['allowHtml' => true]);
}

// Modal für die Paket-Zuweisung
$listGenerator->addModal('modal_package', [
    'title' => 'Newsletter Paket zuweisen',
    'content' => 'form/f_packages.php',
    'size' => 'tiny'
]);

// Button zum Zuweisen des Pakets
$listGenerator->addButton('assign_package', [
    'icon' => 'box',
    'position' => 'right',
    'class' => 'ui blue mini button',
    'modalId' => 'modal_package',
    'popup' => 'Paket zuweisen',
    'params' => ['user_id' => 'user_id']
]);

$listGenerator->setButtonColumnTitle('right', 'Aktionen', 'right');

// Liste generieren
echo $listGenerator->generateList();

// DB Verbindung schließen
if (isset($db)) {
    $db->close();
}