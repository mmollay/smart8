<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../n_config.php';

// Konfiguration des ListGenerators
$listConfig = [
	'listId' => 'senders',
	'contentId' => 'content_senders',
	'itemsPerPage' => 25,
	'sortColumn' => 'id',
	'sortDirection' => 'DESC',
	'search' => $_GET['search'] ?? '',
	'showNoDataMessage' => true,
	'noDataMessage' => 'Keine Daten gefunden.',
	'striped' => true,
	'selectable' => true,
	'celled' => true,
	'width' => '1200px',
];

$listGenerator = new ListGenerator($listConfig);

// Korrigierte Datenbank-Abfrage mit CONCAT
$query = "
    SELECT 
        id, 
        CONCAT(
            IFNULL(CONCAT(title, ' '), ''),
            first_name, 
            ' ', 
            last_name
        ) AS full_name,
        company, 
        email,
        test_email,
        CASE 
            WHEN test_email IS NOT NULL AND test_email != '' THEN 
                CONCAT(
                    '<div class=\"ui mini labels test-email-container\">',
                    REPLACE(
                        REPLACE(
                            GROUP_CONCAT(
                                CONCAT(
                                    '<div class=\"ui basic label\">',
                                    '<i class=\"envelope icon\"></i>',
                                    TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(test_email, '\n', n.n), '\n', -1)),
                                    '</div>'
                                )
                                ORDER BY n.n
                                SEPARATOR ''
                            ),
                            ',', ''
                        ),
                        '\r', ''
                    ),
                    '</div>'
                )
            ELSE 
                '<div class=\"ui mini grey label\">Keine Test-Emails konfiguriert</div>'
        END as formatted_test_email,
        gender, 
        comment
    FROM senders
    CROSS JOIN (
        SELECT a.N + b.N * 10 + 1 n
        FROM 
            (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a,
            (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b
        ORDER BY n
    ) n
    WHERE 
        user_id = '$userId'
        AND n.n <= 1 + LENGTH(test_email) - LENGTH(REPLACE(test_email, '\n', ''))
    GROUP BY id
";

$listGenerator->setSearchableColumns(['first_name', 'last_name', 'email', 'test_email', 'company']);
$listGenerator->setDatabase($db, $query, true);

// Spalten definieren
$columns = [
	['name' => 'full_name', 'label' => "<i class='user icon'></i>Name"],
	['name' => 'company', 'label' => "<i class='building icon'></i>Firma"],
	['name' => 'email', 'label' => "<i class='mail icon'></i>Absende-Email"],
	[
		'name' => 'formatted_test_email',
		'label' => "<i class='paper plane icon'></i>Test-Emails",
		'allowHtml' => true,
		'width' => '300px'
	],
	['name' => 'comment', 'label' => "Kommentar"],
];

foreach ($columns as $column) {
	$listGenerator->addColumn($column['name'], $column['label'], [
		'allowHtml' => $column['allowHtml'] ?? false,
		'width' => $column['width'] ?? null
	]);
}

// Modals definieren
$listGenerator->addModal('modal_form_newsletter', [
	'title' => 'Absender bearbeiten',
	'content' => 'form/f_senders.php',
	'size' => 'small',
]);

$listGenerator->addModal('modal_form_delete', [
	'title' => 'Absender entfernen',
	'content' => 'pages/form_delete.php',
	'size' => 'small',
]);

// Buttons definieren
$buttons = [
	'edit' => [
		'icon' => 'edit',
		'position' => 'left',
		'class' => 'ui blue mini button',
		'modalId' => 'modal_form_newsletter',
		'popup' => 'Bearbeiten',
		'params' => ['update_id' => 'id']
	],
	'delete' => [
		'icon' => 'trash',
		'position' => 'right',
		'class' => 'ui mini button',
		'modalId' => 'modal_form_delete',
		'popup' => 'Löschen',
		'params' => ['delete_id' => 'id']
	],
];

foreach ($buttons as $id => $button) {
	$listGenerator->addButton($id, $button);
}

// Top-Button definieren
$listGenerator->addExternalButton('new_sender', [
	'icon' => 'plus',
	'class' => 'ui blue circular button',
	'position' => 'inline',
	'alignment' => '',
	'title' => 'Neuen Absender anlegen',
	'modalId' => 'modal_form_newsletter',
	'popup' => ['content' => 'Neuen Absender anlegen']
]);

// Setzen der Spaltentitel für die Buttons
$listGenerator->setButtonColumnTitle('left', '', 'center');
$listGenerator->setButtonColumnTitle('right', '', 'right');

// Liste generieren und ausgeben
echo $listGenerator->generateList();
?>

<style>
	.test-email-container {
		display: flex;
		flex-wrap: wrap;
		gap: 4px;
	}

	.ui.mini.basic.label {
		margin: 0;
		padding: 4px 8px;
		font-size: 0.85em;
	}

	.ui.mini.grey.label {
		margin: 0;
		padding: 4px 8px;
		font-size: 0.85em;
		background-color: #f5f5f5 !important;
		color: #767676 !important;
	}

	.ui.mini.basic.label i.icon {
		margin-right: 4px;
		opacity: 0.8;
	}

	.test-email-container .ui.basic.label:hover {
		background-color: #f8f9fa !important;
	}
</style>

<?php
// Datenbankverbindung schließen
if (isset($db)) {
	$db->close();
}