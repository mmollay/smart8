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
	'tableClasses' => 'ui celled striped definition small compact table',
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
        gender, 
        comment
    FROM senders
	
    GROUP BY id
";

$listGenerator->setSearchableColumns(['first_name', 'last_name', 'email', 'company']);
$listGenerator->setDatabase($db, $query, true);

// Spalten definieren
$columns = [
	['name' => 'full_name', 'label' => "<i class='user icon'></i>Name"],
	['name' => 'company', 'label' => "<i class='building icon'></i>Firma"],
	['name' => 'email', 'label' => "<i class='mail icon'></i>Absende-Email"],
	['name' => 'comment', 'label' => "Kommentar"],
];

foreach ($columns as $column) {
	$listGenerator->addColumn($column['name'], $column['label'], ['allowHtml' => true]);
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

// Datenbankverbindung schließen
if (isset($db)) {
	$db->close();
}