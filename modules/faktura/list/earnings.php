<?php
$_SESSION['SetYear'] = $_SESSION['default_year'];

if (isset($_SESSION['SetYear']) && !isset($_SESSION["filter"]['bill_list']['SetYear'])) {
	$_SESSION["filter"]['bill_list']['SetYear'] = 'DATE_FORMAT(date_create,"%Y") = "' . $_SESSION['SetYear'] . '"';
}

$document = $data['document'];

if (!$document)
	$document = 'rn';

if ($document == 'ang') {
	$str_button_name = 'Angebote';
	$str_button_name2 = 'Angebot';
} elseif ($document == 'rn') {
	$str_button_name = 'Rechnungen';
	$str_button_name2 = 'Rechnung';
}

if ($document == 'rn') {
	// Ruft für Rechnung deas jeweilige Leven aus
	function mysql_remind_level($level)
	{
		$button_book = "<button class=\'button tooltip green ui icon mini\' title=\'Juhuu - verbuchen! :)\' onclick=call_booking(',bills.bill_id,')><i class=\'icon unlock\'></i></button>";

		if ($level == 1)
			$button_color = 'yellow';
		if ($level == 2)
			$button_color = 'orange';
		if ($level == 3)
			$button_color = 'red';

		if ($level == 4) {
			return "
		WHEN (remind_level > 3 and date_remind > NOW()) then CONCAT('$button_book <span style=color:grey  class=info_text>Inkasso in ',DATEDIFF(date_remind,NOW()) ,' Tagen</span>')
		WHEN remind_level > 3 then CONCAT('<div class=\'buttons ui mini\'>$button_book <button class=\'button ui red\' onclick=send_pdf(',bills.bill_id,'\,\'\'\,true)>Inkasso seit ',DATEDIFF(NOW(),date_remind) ,' Tagen</button></div>')
		";
		} else {
			return "
		WHEN (remind_level = $level and date_remind > NOW()) then CONCAT('$button_book <span class=info_text style=color:grey>Mahnung ', remind_level,' in ',DATEDIFF(date_remind,NOW()) ,' Tagen </span>')
		WHEN remind_level = $level then
		CONCAT('
		$button_book
		<div class=\'buttons ui mini\'>
		<button class=\'button ui $button_color\' onclick=send_pdf(',bills.bill_id,'\,true) title=\'seit ',DATEDIFF(NOW(),date_remind) ,' Tagen\'>Mahnung ', remind_level,'</button>
		<button class=\'button icon ui tooltip\' onclick=remind_back(',bills.bill_id,') title=\'Mahnung zurücksetzen\' ><i class=\'icon level down\'></i></button>
		</div>
		')
		";
		}
	}
} else if ($document == 'ang') {
	// Ruft für Rechnung deas jeweilige Leven aus
	function mysql_remind_level($level)
	{
		if ($level == 1)
			$button_color = 'yellow';
		if ($level == 2)
			$button_color = 'orange';
		if ($level == 3)
			$button_color = 'red';

		return "
		WHEN (remind_level = $level and date_remind > NOW()) then CONCAT('<span class=info_text style=color:grey>Erinneren in ', remind_level,' in ',DATEDIFF(date_remind,NOW()) ,' Tagen </span>')
		WHEN remind_level = $level then
		CONCAT('
		<div class=\'buttons ui mini\'>
		<button class=\'button ui $button_color\' onclick=send_pdf(',bills.bill_id,'\,true) title=\'seit ',DATEDIFF(NOW(),date_remind) ,' Tagen\'>Erinnern in ', remind_level,'</button>
		</div>
		')
		";
	}
}

// Wird benötigt für das aufrufen von PDFs (siehe pdf_generator.php)
$_SESSION['set_faktura_module'] = $document;

// Erweiterung bei UNI-Darstellung
// if ($company_id == 7) $document = '';

$arr['mysql']['field'] = "
		bills.bill_id bill_id,bill_number,client_number,DATE_FORMAT(date_create,'%Y-%m-%d') date_create_show ,date_booking,date_send,date_remind,remind_level,bills.netto netto,post,date_booking,firstname,secondname,zip,city,
		ROUND(brutto,2) brutto,date_storno, (SELECT company_1 FROM company WHERE company_id = bills.company_id) title_company, 
		if (email = '', '',CONCAT('<i class=\'icon mail tooltip\' title=',email,'></i>')) email,	
		if (ROUND(booking_total) != ROUND(brutto), ROUND(brutto-booking_total,2),CONCAT('0,00')) booking_total,		
		(CASE 
		WHEN status = 'queued' then CONCAT('Versendet')
		WHEN status = 'sent' then '<h5 style=\"font-size:12px\" class=\"ui grey header\">Zugestellt</h5>'
		WHEN status = 'open' then '<h5 style=\"font-size:12px\" class=\"ui green header\">Geöffnet</h5>'
		WHEN status = 'click' then '<h5 style=\"font-size:12px\" class=\"ui green header\">Angegklickt</h5>'
		WHEN status = 'blocked' then '<h5 style=\"font-size:12px\" class=\"ui black header\">Geblockt</h5>'
		WHEN status = 'bounce' then '<h5 style=\"font-size:12px\" class=\"ui red header\">Unzustellbar</h5>'
		WHEN status = 'spam' then '<h5 style=\"font-size:12px\" class=\"ui red header\">Spam</h5>'
		WHEN status = 'unsub' then '<h5 style=\"font-size:12px\" class=\"ui red header\">Abgemeldet</h5>'
		ELSE status
		END) as status,
		(CASE
		WHEN date_storno != '0000-00-00' then CONCAT('<div class=info_text style=color:red>',date_storno,' (Storno)</div>')
		WHEN date_booking != '0000-00-00' then CONCAT('<div class=info_text style=color:green>',date_booking,' (Verbucht)</div>')
		WHEN date_send = '0000-00-00' then CONCAT('<button class=\'ui button blue mini\' onclick=send_pdf(',bills.bill_id,') ><i class=\"icon send\"></i> Versenden</button>')
		" . mysql_remind_level(1) . mysql_remind_level(2) . mysql_remind_level(3) . mysql_remind_level(4) . "
		END) as send_status,
		if (tel, CONCAT('<button class=client_info title=\"Tel:',tel,'\">Info</button>'),'') tel,
		(CASE
		WHEN LENGTH(company_1) >= 40 then CONCAT(substring(company_1, 1,40), CONCAT('<span class=\'km_info\' title=\'',company_1,'\'>[...]</span>'))
		WHEN company_1 = '' then CONCAT (firstname,' ',secondname)
		ELSE company_1
		END) as company_1";
$arr['mysql']['table'] = "bills 
			LEFT JOIN bill_details ON bills.bill_id = bill_details.bill_id 
			LEFT JOIN logfile ON (bills.bill_id = logfile.bill_id AND log_id = (SELECT MAX(log_id) FROM logfile WHERE bill_id = bills.bill_id))";
// $arr ['mysql'] ['table_total'] = 'bills';
$arr['mysql']['where'] = "AND (document = '$document' OR document = '') ";
$arr['mysql']['like'] = "bill_number, title, company_1, firstname, secondname, brutto";
$arr['mysql']['order'] = 'bill_number desc';
$arr['mysql']['limit'] = 25;
$arr['mysql']['group'] = 'bills.bill_id';
// $arr ['mysql'] ['debug'] = true;
$arr['mysql']['export'] = 'bills.bill_id,bill_number,description,company_1,company_2,firstname,secondname,street,zip,city,country,date_create,brutto';

$arr['order'] = array('default' => 'date_create desc', 'array' => array('date_create desc' => 'Datum absteigend sortieren', 'bill_number desc' => 'Folgenummer aufsteigend sortieren', 'brutto desc' => 'Betrag absteigend sortieren'));

$arr['list'] = array('id' => 'bill_list', 'width' => '100%', 'align' => '', 'size' => 'small', 'class' => 'very compact striped selectable celled'); // definition //striped first last head foot stuck unstackable celled very compact selectable
$arr['list']['loading_time'] = true;
$arr['list']['serial'] = true;
$arr['list']['auto_reload'] = array('label' => 'Auto Reload', 'loader' => false);
// $array_filter = array ( 1 => 'Alle Rechnungen' ,

$array_filter = array('date_storno = "0000-00-00" ' => 'Alle Rechnungen', 'date_remind < NOW() and date_booking = "0000-00-00" and remind_level != 0 and date_storno = "0000-00-00"' => 'Zu mahnenden Kunden', 'date_booking = "0000-00-00" ' => 'Alle Rechnungen mit Storno', 'date_booking = "0000-00-00" and date_storno = "0000-00-00" ' => 'Offene Rechnungen', '(ROUND(booking_total,2) != ROUND(brutto,2)) and date_booking != "0000-00-00" ' => 'Verbuchte Rechnungen mit offenen Betr&auml;gen', 'date_booking != "0000-00-00" and date_storno = "0000-00-00" ' => 'Verbuchte Rechnungen', 'remind_level = 0 and date_send = "0000-00-00" and date_booking = "0000-00-00" and date_storno = "0000-00-00" ' => 'Noch nicht versendete Rechnungen', 'remind_level = 4 and date_remind < NOW() and date_booking = "0000-00-00" and date_storno = "0000-00-00" ' => 'Inkasso Fälle', 'date_storno != "0000-00-00" ' => 'Stornierte Rechnungen', 'netto = "0.000"' => 'Rechnungen mit 0 Summe', 'DATE_FORMAT(date_booking,"%Y") = "' . date('Y') . '"' => "Verbuchte Rechnungen" . date('Y'), 'email = ""' => 'Rechnungen ohne Emailempf&auml;nger', 'email != ""' => 'Rechnungen mit Emailempf&auml;nger', 'client_id ="" ' => 'Rechnungen ohne Kundennummer');

$arr['filter']['company_id'] = array('type' => 'dropdown', 'array' => $company_array, 'table' => 'bills', 'placeholder' => '--Alle Firmen--');
$arr['filter']['SetYear'] = array('type' => 'dropdown', 'array' => $array_year, 'placeholder' => 'Alle Jahre', 'query' => "{value}", 'default_value' => '2020');
$arr['filter']['select_month'] = array('type' => 'dropdown', 'query' => "{value}", 'array' => $array_filter_month, 'placeholder' => '--Alle Monate--');
if ($document == 'rn')
	$arr['filter']['select_id'] = array('type' => 'dropdown', 'query' => "{value}", 'array' => $array_filter, 'placeholder' => '--Kein Filter--');

// geht nicht weil die Verknüpfung nicht stimmt
$arr['filter']['account'] = array('type' => 'dropdown', 'array' => $account_array, 'placeholder' => '--Alle Konten--');

$arr['th']['title_company'] = array('title' => "Firma");
$arr['th']['date_create_show'] = array('title' => "Datum");
$arr['th']['bill_number'] = array('title' => "Folgenummer");
$arr['th']['company_1'] = array('title' => "Firma");
$arr['th']['send_status'] = array('title' => "Status");
$arr['th']['status'] = array('title' => "<i class='icon mail'></i>", 'align' => 'center');
// $arr['th']['netto'] = array ( 'title' =>"Netto", format=>'euro', 'align' =>right );
// $arr['th']['booking_total'] = array ( 'title' =>"Offen", format=>'euro', 'align' =>right );
$arr['th']['brutto'] = array('title' => "Brutto", 'format' => 'euro', 'align' => 'right', 'total' => true);
$arr['tr']['buttons']['left'] = array('class' => 'tiny');
$arr['tr']['button']['left']['modal_form_edit'] = array('filter' => array(['field' => 'date_booking', 'operator' => '==', 'value' => '0000-00-00']), 'icon' => 'edit', 'class' => 'blue mini', 'popup' => 'Bearbeiten');
$arr['tr']['button']['left']['modal_form_clone'] = array('icon' => 'copy', 'popup' => 'Klonen');
$arr['tr']['buttons']['right'] = array('class' => 'tiny');
$arr['tr']['button']['right']['modal_form_print'] = array('filter' => array(['field' => 'remind_level', 'operator' => '==', 'value' => '0'], ['link' => 'and', 'field' => 'post', 'operator' => '==', 'value' => '1']), 'icon' => 'envelope open', 'popup' => 'Mit Post versenden und einbuchen', 'onclick' => "window.open('pdf_generator.php?bill={id}', '_blank');");
$arr['tr']['button']['right']['pdf'] = array('title' => '', 'popup' => "PDF & Drucken", 'icon' => 'print', 'class' => 'mini', 'onclick' => "call_pdf('{id}')");
$arr['tr']['button']['right']['modal_form_delete'] = array('filter' => array(['field' => 'date_booking', 'operator' => '==', 'value' => '0000-00-00']), 'icon' => 'trash', 'class' => 'mini', 'popup' => 'Löschen');

if ($document == 'rn') {
	$arr['tr']['button']['right']['unlock'] = array('filter' => array(['field' => 'date_booking', 'operator' => '!=', 'value' => '0000-00-00']), 'icon' => 'lock', 'popup' => 'Verbuchen aufheben', 'onclick' => "call_unbooking('{id}')");
	$arr['tr']['button']['right']['storno'] = array('filter' => array(['field' => 'date_booking', 'operator' => '==', 'value' => '0000-00-00']), 'icon' => 'remove circle', 'popup' => 'Stornieren', 'onclick' => "storno_bill('{id}')");
}
$arr['tr']['button']['right']['modal_form_send'] = array('title' => '', 'icon' => 'mail', 'popup' => 'Versenden per Email');
$arr['tr']['button']['right']['modal_logbook'] = array('title' => '', 'icon' => 'file text outline', 'popup' => 'Logbuch einsehen');

$arr['modal']['modal_logbook'] = array('title' => "<i class='icon edit'></i> Lookbuch bearbeiten", 'class' => '', 'url' => "form_logbook.php?update_id={id}");

$arr['modal']['modal_form_clone'] = array('title' => "<i class='icon copy'></i> $str_button_name2 bearbeiten", 'class' => 'long', 'url' => 'form_edit.php?clone=1');
$arr['modal']['modal_form_clone']['button']['submit'] = array('title' => 'Speichern', 'color' => 'green', 'form_id' => 'form_edit'); // form_id = > ID formular
$arr['modal']['modal_form_clone']['button']['cancel'] = array('title' => 'Schließen', 'color' => 'grey', 'icon' => 'close');

$arr['modal']['modal_form_edit'] = array('title' => "<i class='icon edit'></i> $str_button_name2 bearbeiten", 'class' => 'long', 'url' => 'form_edit.php');
$arr['modal']['modal_form_edit']['button']['submit'] = array('title' => 'Speichern', 'color' => 'green', 'form_id' => 'form_edit'); // form_id = > ID formular
$arr['modal']['modal_form_edit']['button']['cancel'] = array('title' => 'Schließen', 'color' => 'grey', 'icon' => 'close');

$arr['modal']['modal_form_new'] = array('title' => "<i class='icon edit'></i> $str_button_name2 erstellen", 'class' => '', 'url' => "form_edit.php?document=$document");
$arr['modal']['modal_form_new']['button']['submit'] = array('title' => 'Speichern', 'color' => 'green', 'form_id' => 'form_edit'); // form_id = > ID formular
$arr['modal']['modal_form_new']['button']['cancel'] = array('title' => 'Schließen', 'color' => 'grey', 'icon' => 'close');

$arr['modal']['modal_form_delete'] = array('title' => "$str_button_name2 entfernen", 'class' => 'small', 'url' => 'form_delete.php');
$arr['modal']['modal_form_send'] = array('title' => "$str_button_name2 versenden", 'class' => '', 'url' => 'form_send.php');
$arr['modal']['modal_form_print'] = array('title' => 'War der Druckervorgang erfolgreich?', 'class' => 'small', 'url' => 'form_print.php?all=1');

$arr['top']['button']['modal_form_new'] = array('title' => "$str_button_name2 erstellen", 'icon' => 'plus', 'class' => 'blue circular');

/*
 * Counter for Email for send
 */
$mysql_query = $GLOBALS['mysqli']->query("
		SELECT bill_id FROM bills 
		WHERE COALESCE(remind_level, 0) = 0
		AND date_booking = '0000-00-00'
		AND date_send = '0000-00-00'
		AND date_storno = '0000-00-00'
		AND email != ''
		AND document = '$document'
		 ") or die(mysqli_error($GLOBALS['mysqli']));
$count_open_mail = mysqli_num_rows($mysql_query);

/*
 * Counter for print bills
 */
$mysql_query = $GLOBALS['mysqli']->query("
		SELECT bill_id FROM bills
		WHERE COALESCE(remind_level, 0) = 0
		AND date_booking = '0000-00-00'
		AND date_storno = '0000-00-00'
		AND document = '$document'
		AND (email = '' OR post = 1) ") or die(mysqli_error($GLOBALS['mysqli']));
$count_open_print = mysqli_num_rows($mysql_query);

if ($count_open_print)
	$arr['top']['button']['modal_form_print'] = array('popup' => "$str_button_name zum ausdrucken", 'title' => "<span id=count_open_print>$count_open_print</span>", 'icon' => 'print', 'class' => 'green circular', 'onclick' => "window.open('pdf_generator.php?bill=all', '_blank');");

if ($count_open_mail)
	$arr['top']['button'][] = array('popup' => "Zuversendende $str_button_name", 'id' => 'send_bills', 'title' => "<span id=count_open_mail>$count_open_mail</span>", 'icon' => 'send', 'class' => 'green circular');
