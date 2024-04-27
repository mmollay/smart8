<?php
unset($_SESSION['temp_cart']);
$clone = $_GET['clone'];
$update_id = $_POST['update_id'];

if ($_GET['document'])
	$document = $_GET['document'];

// fügt bei den Rechnungen ohne Status den document status hinzu
$GLOBALS['mysqli']->query("UPDATE bills SET document = 'rn' WHERE document = '' or document = 'nr' ");

$sql_query3 = $GLOBALS['mysqli']->query("SELECT temp_id, art_nr, art_title FROM article_temp order by art_nr") or die(mysqli_error($GLOBALS['mysqli'])); // WHERE company_id = '{$_SESSION['faktura_company_id']}'
while ($sql_array3 = mysqli_fetch_array($sql_query3)) {
	$article_array[$sql_array3['temp_id']] = $sql_array3['art_nr'] . "->" . $sql_array3['art_title'];
}

// Clients auslesen aus der Datenbank
$sql_query4 = $GLOBALS['mysqli']->query("SELECT
				client_id,client_number,
				(CASE
				WHEN (firstname != '' OR secondname != '') AND company_1 !='' then CONCAT (company_1,' (',firstname,' ',secondname,')')
				WHEN (firstname != '' OR secondname != '') AND company_1 ='' then CONCAT (firstname,' ',secondname)
				ELSE company_1
				END) as name
				FROM client
				ORDER BY name ") or die(mysqli_error($GLOBALS['mysqli']));
while ($sql_array4 = mysqli_fetch_array($sql_query4)) {
	$client_array[$sql_array4['client_id']] = "{$sql_array4['client_number']} -" . $sql_array4['name'];
}

if (!$update_id or $clone) {

	$set_focus = true;
	$company_id = $default_company_id = $_SESSION['faktura_company_id'];

	// Set year
	if ($clone) {
		// $description = mysql_singleoutput("SELECT description FROM bills WHERE bill_id = '{$_POST['update_id']}' ","description");
		$clone_query = $GLOBALS['mysqli']->query("SELECT description, bills.company_id company_id, bills.document document
						FROM bills  WHERE bill_id ='{$_POST['update_id']}'") or die(mysqli_error($GLOBALS['mysqli']));
		$clone_array = mysqli_fetch_array($clone_query);
		$default_company_id = $company_id = $clone_array['company_id'];
		$document = $clone_array['document'];
		$call_year = date('Y');
		$call_year_last = $call_year - 1;
		$call_last_next = $call_year + 1;
		$description = preg_replace("/$call_year/", "$call_last_next", $description);
		$description = preg_replace("/$call_year_last/", "$call_year", $description);
	} else {
		// $description = mysql_singleoutput ( "SELECT subject FROM company WHERE company_id = '{$_SESSION['faktura_company_id']}' ", "subject" );
		// $client_number = mysql_singleoutput ( "SELECT MAX(client_number) as client_number FROM client", "client_number" ) + 1;
	}

	$year = date('Y');
	// Herauslesen ob der Defaultwert hoeher ist als der hoechste Wert im Rechungslauf
	// ZUSATZ: Wenn bereits Rechnung für das nächste Jahr erstellt wurden, wird vom bestehenden Jahr die letzte fortlaufende Nummer verwenden
	$bill_number = mysql_singleoutput("SELECT MAX(bill_number) as bill_number FROM bills WHERE DATE_FORMAT(date_create,'%Y') = '$year' AND document = '$document' AND company_id='$company_id' order by date_create", "bill_number") + 1;

	$date_create = date("Y-m-d");
	$update_id = '';
	$date_booking = ' ';
	$booking_total = '0.00';
}

$arr['ajax'] = array(
	'success' => "
if (data == 'ang' || data == 'rn') { 
	$('#modal_form_edit, #modal_form_clone, #modal_form_new').modal('hide'); $('.ui.flyout').flyout('hide');  $('.ui.modal>.content').empty(); table_reload('list_offered'); 
	if (data == 'ang') { load_content_semantic('faktura','list_offered');  } 
	else if (data == 'rn') { load_content_semantic('faktura','list_earnings');  }
} else {
	alert('Es scheint ein Fehler vorhanden zu sein! Bitte Eingabe überrpüfen. Fehlermeldung:'+data);
}
",
	'dataType' => "html"
);

$arr['tab'] = array('content_class' => 'pointing secondary red', 'class' => 'secondary red', 'active' => 1, 'tabs' => [1 => 'Step 1 (Empfänger)', 2 => 'Step 2 (Positionen)', 3 => 'Step 3 (Zusatz)']); // , 4 => 'Buchung'

$arr['sql'] = array(
	'query' => "
SELECT *,DATE_FORMAT(date_create,'%Y-%m-%d') date_create,bills.company_id faktura_company_id 
from bills LEFT JOIN client ON bills.client_id = client.client_id
WHERE bill_id = '{$_POST['update_id']}'"
);

// if ($clone) {
$arr['field']['document'] = array('tab' => '1', 'type' => 'radio', 'label' => 'Dokumentart wählen', 'array' => $document_array, 'validate' => true, 'value' => $document, 'onchange' => "set_document_settings()");
// } else {
// $arr['field']['document'] = array ( 'type' => 'hidden' , 'value' => $document );
// }

$arr['field']['faktura_company_id'] = array('tab' => '1', 'type' => 'dropdown', 'label' => 'Firma wählen', 'array' => $company_array, 'validate' => true, 'value_default' => $default_company_id, "onchange" => "set_document_settings()");
$arr['field'][] = array('tab' => '1', 'type' => 'div', 'class' => 'two fields');
$arr['field']['bill_number'] = array('tab' => '1', 'type' => 'input', 'label' => 'Folgenummer', 'value_default' => $bill_number, 'validate' => true);
$arr['field']['date_create'] = array('tab' => '1', 'type' => 'date', 'value' => $date_create, 'label' => 'Erstelldatum', 'validate' => true);
$arr['field'][] = array('tab' => '1', 'type' => 'div_close');

$arr['field']['client_id'] = array('tab' => '1', 'type' => 'dropdown', 'class' => 'search', 'array' => $client_array, 'label' => 'Kunden wählen', 'focus' => $set_focus, 'placeholder' => '--bitte wählen--', 'clearable' => true);
$arr['field'][] = array('tab' => '1', 'type' => 'div', 'class' => 'two fields');
$arr['field']['client_number'] = array('tab' => '1', 'type' => 'input', 'label' => 'Kundennummer', 'value' => $client_number, 'label_right' => '');
$arr['field'][] = array(
	'tab' => '1',
	'type' => 'content',
	'label' => '&nbsp;',
	'text' => "
		<div class='button small ui icon' id=new_client_number data-tooltip='Neue Kundennummer erzeugen'><i class='refresh icon'></i></div>
		<div class='button small ui icon' id=rem_client  data-tooltip='Felder leeren um neuen Kunden anzulegen'><i class='remove icon'></i></div>
		<div class='button ui small icon green' id=add_client><i class='add user icon'></i> Neuen Kunden anlegen</div>
		<div class='button ui small icon green' id=mod_client><i class='refresh icon'></i> Überschreiben</div>"
);
$arr['field'][] = array('tab' => '1', 'type' => 'div_close');

$arr['field'][] = array('tab' => '1', 'type' => 'div', 'class' => 'two fields');
$arr['field']['company_1'] = array('tab' => '1', 'type' => 'input', 'label' => 'Firma');
$arr['field']['company_2'] = array('tab' => '1', 'type' => 'input', 'label' => 'Firma(Zusatz)');
$arr['field'][] = array('tab' => '1', 'type' => 'div_close');

$arr['field'][] = array('tab' => '1', 'type' => 'div', 'class' => 'four fields');
$arr['field']['gender'] = array('class' => 'four wide', 'tab' => '1', 'type' => 'dropdown', 'label' => 'Anrede', 'placeholder' => '--bitte wählen--', 'array' => array('f' => 'Frau', 'm' => 'Herr'));
$arr['field']['title'] = array('class' => 'three wide', 'tab' => '1', 'type' => 'input', 'label' => 'Titel');
$arr['field']['firstname'] = array('class' => 'four wide', 'tab' => '1', 'type' => 'input', 'label' => 'Vorname');
$arr['field']['secondname'] = array('class' => 'five wide', 'tab' => '1', 'type' => 'input', 'label' => 'Nachname');
$arr['field'][] = array('tab' => '1', 'type' => 'div_close');
$arr['field']['email'] = array('tab' => '1', 'type' => 'input', 'label' => 'Email');

$arr['field']['street'] = array('tab' => '1', 'type' => 'input', 'label' => 'Strasse');
$arr['field'][] = array('tab' => '1', 'type' => 'div', 'class' => 'three fields');
$arr['field']['zip'] = array('class' => 'three wide', 'tab' => '1', 'type' => 'input', 'label' => 'PLZ');
$arr['field']['city'] = array('class' => 'seven wide', 'tab' => '1', 'type' => 'input', 'label' => 'Stadt');
$arr['field']['country'] = array('class' => 'six wide', 'tab' => '1', 'type' => 'dropdown', 'array' => 'country', 'label' => 'Land', 'value_default' => 'at');
$arr['field'][] = array('tab' => '1', 'type' => 'div_close');

$arr['field'][] = array('tab' => '1', 'type' => 'div', 'class' => 'ui fluid accordion');
$arr['field'][] = array('tab' => '1', 'type' => 'div', 'class' => 'title', 'text' => "<i class='icon dropdown'></i>Mehr</div>");
$arr['field'][] = array('tab' => '1', 'type' => 'div', 'class' => 'content');
$arr['field'][] = array('tab' => '1', 'type' => 'div', 'class' => 'three fields');
$arr['field']['tel'] = array('tab' => '1', 'type' => 'input', 'label' => 'Tel');
$arr['field']['web'] = array('tab' => '1', 'type' => 'input', 'label' => 'Internet');
$arr['field']['uid'] = array('tab' => '1', 'type' => 'input', 'label' => 'UID');
$arr['field'][] = array('tab' => '1', 'type' => 'div_close');
$arr['field'][] = array('tab' => '1', 'type' => 'div', 'class' => 'two fields');
$arr['field']['commend'] = array('tab' => '1', 'type' => 'input', 'label' => 'Kommentar');
$arr['field'][] = array('tab' => '1', 'type' => 'div_close');
$arr['field']['post'] = array('tab' => '1', 'type' => 'checkbox', 'label' => 'Postversand');
$arr['field']['newsletter'] = array('tab' => '1', 'type' => 'checkbox', 'label' => 'Newsletter');
$arr['field'][] = array('tab' => '1', 'type' => 'div_close');
$arr['field'][] = array('tab' => '1', 'type' => 'div_close');

$arr['field']['map_page_id'] = array('type' => 'hidden');
$arr['field']['map_user_id'] = array('type' => 'hidden');
$arr['field']['sendet'] = array('type' => 'hidden');

if ($update_id) {
	$arr['field']['bill_id'] = array('type' => 'hidden');
}

$arr['field']['description'] = array('tab' => '2', 'type' => 'input', 'label' => 'Betreff', 'label_right' => 'Merken', 'label_right_class' => "button", 'label_right_id' => "save_desc", 'value_default' => $description);

$arr['field'][] = array('tab' => '2', 'type' => 'div', 'class' => 'two fields');
$arr['field']['select_temp'] = array('tab' => '2', 'type' => 'dropdown', 'class' => 'search', 'array' => $article_array, 'label' => 'Artikel');
$arr['field'][] = array(
	'tab' => '2',
	'type' => 'content',
	'label' => '&nbsp;',
	'text' => "
<a href=# class='ui small button icon' id=mod_temp><i class='refresh icon'></i> Artikel überschreiben</a>
<a href=# class='ui small blue button icon' id=add_temp><i class='plus icon'></i> Artikel anlegen</a>"
);
$arr['field'][] = array('tab' => '2', 'type' => 'div_close');

$arr['field'][] = array('tab' => '2', 'type' => 'div', 'class' => 'two fields');
$arr['field']['art_nr'] = array('tab' => '2', 'type' => 'input', 'label' => 'Artikel-Nr');
$arr['field']['art_title'] = array('tab' => '2', 'type' => 'input', 'label' => 'Titel');
$arr['field'][] = array('tab' => '2', 'type' => 'div_close');

$arr['field']['art_text'] = array('tab' => '2', 'type' => 'textarea');

$arr['field'][] = array('tab' => '2', 'type' => 'div', 'class' => 'four fields');
$arr['field']['netto'] = array('tab' => '2', 'type' => 'input', 'label' => 'Netto', 'format' => 'euro', 'class' => 'four wide');
$arr['field']['count'] = array('tab' => '2', 'type' => 'input', 'label' => 'Anzahl', 'class' => 'four wide');
$arr['field']['format'] = array('tab' => '2', 'type' => 'input', 'label' => 'Einheit', 'class' => 'four wide');
$arr['field']['account'] = array('tab' => '2', 'type' => 'dropdown', 'array' => $account_array, 'label' => 'Konto', 'class' => 'six wide'); // , 'label_right' => '<span id=add_account></span>' );
$arr['field'][] = array('tab' => '2', 'type' => 'div_close');

$arr['field'][] = array(
	'tab' => '2',
	'type' => 'content',
	'align' => 'center',
	'text' => '
				<button id=add_art class="ui mini icon button blue"><i class="icon arrow down"></i> Position übernehmen <i class="icon arrow down"></i></button><input class="button mini ui" type=button id=cancel_art value="Abbrechen"><p>
				<div id=position_list></div>'
);

$arr['field']['update_temp'] = array('type' => 'hidden');

$arr['field']['discount'] = array('tab' => '3', 'type' => 'input', 'label' => 'Rabatt', 'label_right' => '%', 'format' => 'euro');
$arr['field']['no_mwst'] = array('tab' => '3', 'type' => 'checkbox', 'label' => 'Mwst. freie Rechnung');

$arr['field']['no_endsummery'] = array('tab' => '3', 'type' => 'checkbox', 'label' => 'Keine Endsumme anzeigen (Speziell für Angebote)');

$arr['field']['text_after'] = array('tab' => '3', 'type' => 'textarea', 'label' => 'Text nach Dokument');

// $arr['field'][] = array ( 'tab' => '4' , 'type' => 'div' , 'class' => 'two fields' );
// $arr['field']['date_booking'] = array ( 'tab' => '4' , 'type' => 'date' , 'label' => 'Buchungsdatum' );
// $arr['field']['booking_total'] = array ( 'tab' => '4' , 'type' => 'input' , 'label' => 'Verbuchter Betrag' , 'label_right' => 'Euro' , 'format' => 'euro' , 'value_default' => $booking_total );
// $arr['field'][] = array ( 'tab' => '4' , 'type' => 'div_close' );

// $arr['field']['booking_command'] = array ( 'tab' => '4' , 'type' => 'textarea' , 'label' => 'Buchungs-Kommentar' );
// $arr['field']['date_storno'] = array ( 'tab' => '4' , 'type' => 'date' , 'label' => 'Storno Datum' );

$arr['field']['bill_option'] = array('type' => 'hidden', 'value' => 'out');

$add_js .= "<script type=\"text/javascript\">call_form_bill('{$_POST['update_id']}','$clone');</script>";
// $add_js .= "<script type=\"text/javascript\" src=\"js/form_bill.js\"></script>";

