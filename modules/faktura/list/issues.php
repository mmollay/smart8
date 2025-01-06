<?php
if (isset ($_SESSION['SetYear']) && !$_SESSION["filter"]['issues_list']['SetYear']) {
	$_SESSION["filter"]['issues_list']['SetYear'] = 'DATE_FORMAT(date_create,"%Y") = "' . $_SESSION['SetYear'] . '"';
}

$query = $GLOBALS['mysqli']->query("SELECT article_group.group_id group_id,COUNT(article2group.group_id) count, title from article_group INNER JOIN article2group
		ON article_group.group_id = article2group.group_id WHERE company_id = '{$_SESSION['faktura_company_id']}' GROUP by article2group.group_id") or die (mysqli_error($GLOBALS['mysqli']));
while ($array = mysqli_fetch_array($query)) {
	$group_array[$array['group_id']] = $array['title'] . " (" . $array['count'] . ")";
}

// Aufruf der Konten
$sql = $GLOBALS['mysqli']->query("
		SELECT CONCAT(accounts.title, ' (',accounts.tax,'%)') title, account_id from accounts
		INNER JOIN issues ON account = account_id
		WHERE `option` = 'out'
		AND issues.company_id = '{$_SESSION['faktura_company_id']}'
		GROUP by account_id ORDER by accounts.title, accounts.tax	
		") or die (mysqli_error($GLOBALS['mysqli']));
while ($array = mysqli_fetch_array($sql)) {
	$title = $array['title'];
	$id = $array['account_id'];
	$group_filter_array[$id] = $title;
}

$sql_query = $GLOBALS['mysqli']->query("SELECT * FROM issues_group order by name") or die (mysqli_error($GLOBALS['mysqli'])); // AND company_id = '{$_SESSION['faktura_company_id']}'
while ($sql_array = mysqli_fetch_array($sql_query)) {
	if ($sql_array['name']) {
		$issues_group_array[$sql_array['issues_group_id']] = $sql_array['name'];
	}
}

/**
 * ****************************
 * Zusatzfilter
 * Kommentare
 * ****************************
 */
$sql_query = $GLOBALS['mysqli']->query("SELECT * FROM issues WHERE comment > '' ") or die (mysqli_error($GLOBALS['mysqli'])); // AND company_id = '{$_SESSION['faktura_company_id']}'
$count_comment = mysqli_num_rows($sql_query);

$sql_query = $GLOBALS['mysqli']->query("SELECT * FROM issues WHERE issues.account=0 ") or die (mysqli_error($GLOBALS['mysqli'])); // AND company_id = '{$_SESSION['faktura_company_id']}'
$count_issues_no_account = mysqli_num_rows($sql_query);


$array_more_filter = array(
	'issues.comment > "" ' => "Nur Kommentare ($count_comment)",
	'issues.account=0 ' => "Einem Konto nicht zugewiesen ($count_issues_no_account)"
);

//when issues.amazon_order_nr>'' THEN CONCAT('<a href=',issues.amazon_order_nr,' target=\"_blank\"><i class=\"icon amazon\"></i></a>')

$arr['mysql'] = array(
	'field' => "bill_id, 
        case
            when issues.account=0 THEN '<i class=\"icon exclamation triangle red tooltip\" data-html=\"Bitte Konto zuweisen\">'
            when issues.comment>'' THEN CONCAT('<i class=\"icon star red tooltip\" data-html=\"<b>ToDo:</b><br>',issues.comment,'\">')
            when issues.amazon_order_nr>'' THEN CONCAT('<a href=',issues.amazon_order_nr,' target=\"_blank\"><i class=\"icon amazon\"></i></a>')
		
			ELSE ''
        END AS comment,
		IF(LENGTH(description) >= 50, CONCAT('<div data-variation=\'wide\' class=\'tooltip\' title=\'',description,'\'>',substring(description, 1,50), CONCAT('[...]</div>')), description) description,
		IF(LENGTH(data_elba.text) >= 30, CONCAT('<div data-variation=\'very wide\' class=\'tooltip\' title=\'',data_elba.text,'\'>',substring(data_elba.text, 1,30), CONCAT('[...]</div>')), data_elba.text) elba_text,
		bill_number,company_1,date_create,date_booking,accounts.title title,netto,brutto,issues.tax tax, name, `option` in_out,issues.account",
	'table' => "issues 
            LEFT JOIN accounts ON account = account_id  
            LEFT JOIN issues_group ON issues_group_id = client_id
            LEFT JOIN data_elba ON data_elba.elba_id = issues.elba_id
       ",
	'order' => 'bill_number desc ',
	'limit' => 100,
	'group' => 'bill_id',
	// 'debug' => true,
	'like' => 'bill_number, description, title, netto, brutto,name,	data_elba.text'
);

$arr['order'] = array('default' => 'bill_number desc', 'array' => array('date_create desc' => 'Datum', 'bill_number desc' => 'Rechnungsnummer', 'in_out' => 'Nach Optionen sortieren', 'netto, date_create ' => 'Betrag aufsteigen sortieren', 'netto desc, date_create' => 'Betrag absteigend sortieren', 'description' => 'Beschreibung'));

$arr['list'] = array('id' => 'issues_list', 'width' => '', 'align' => '', 'size' => 'small', 'class' => 'compact selectable celled striped  definition'); // definition
//$arr['list']['serial'] = true;
//'serial' => false,

$arr['filter']['SetYear'] = array('type' => 'dropdown', 'query' => "{value}", 'array' => $array_year, 'placeholder' => 'Alle Jahre');
$arr['filter']['select_month'] = array('type' => 'dropdown', 'query' => "{value}", 'array' => $array_filter_month, 'placeholder' => '--Alle Monate--');
$arr['filter']['account_id'] = array('type' => 'dropdown', 'array' => $group_filter_array, 'placeholder' => '--Alle Konten--');

$arr['filter']['issues_group_id'] = array('type' => 'dropdown', 'array' => $issues_group_array, 'placeholder' => '--Alle Firmen--', 'table' => 'issues_group');
$arr['filter']['more'] = array('type' => 'dropdown', 'array' => $array_more_filter, 'placeholder' => '--Andere Filter--', 'query' => "{value}");

$arr['checkbox']['button']['modal_form_delete'] = array('title' => 'Löschen', 'icon' => 'delete', 'class' => 'red');
$arr['th']['comment'] = array('title' => "", 'align' => 'center');
//$arr['th']['elba_id'] = array ( 'title' =>"Elba" );
$arr['th']['bill_number'] = array('title' => "ReNr.");
$arr['th']['date_create'] = array('title' => "Datum");
$arr['th']['description'] = array('title' => "Beschreibung");
$arr['th']['title'] = array('title' => "Konto");
$arr['th']['elba_text'] = array('title' => "Elba");

//$arr ['th'] ['name'] = array ('title' => "Firma" );
//$arr ['th'] ['title'] = array ('title' => "Konto",'class' => 'two wide' );
//$arr['th']['in_out'] = array ( 'title' =>"" );
$arr['th']['netto'] = array('title' => "Netto", 'format' => 'euro_color', 'align' => 'right', 'class' => '', 'total' => true);
$arr['th']['brutto'] = array('title' => "Brutto", 'format' => 'euro_color', 'align' => 'right', 'class' => '', 'total' => true);

$arr['tr']['buttons']['left'] = array('class' => 'tiny');
$arr['tr']['button']['left']['modal_form_edit'] = array('title' => '', 'icon' => 'edit', 'class' => 'blue mini', 'popup' => 'Bearbeiten');
$arr['tr']['button']['left']['modal_form_clone'] = array('title' => '', 'icon' => 'copy', 'popup' => 'Klonen');

$arr['tr']['buttons']['right'] = array('class' => 'tiny');
$arr['tr']['button']['right']['modal_form_delete'] = array('title' => '', 'icon' => 'trash', 'class' => 'mini', 'popup' => 'Löschen');

$arr['modal']['modal_form_delete'] = array('title' => 'Ausgabe(n) entfernen', 'class' => 'small', 'url' => 'form_delete.php');

$arr['modal']['modal_form_edit'] = array('title' => 'Ausgabe bearbeiten', 'class' => 'small', 'url' => 'form_edit.php');
$arr['modal']['modal_form_edit']['button']['submit'] = array('title' => 'Speichern', 'color' => 'green', 'form_id' => 'form_edit'); //form_id = > ID formular
$arr['modal']['modal_form_edit']['button']['cancel'] = array('title' => 'Schließen', 'color' => 'grey', 'icon' => 'close');

$arr['modal']['modal_form_clone'] = array('title' => 'Ausgabe bearbeiten(Geclont)', 'class' => '', 'url' => 'form_edit.php?clone');
$arr['modal']['modal_form_clone']['button']['submit'] = array('title' => 'Speichern', 'color' => 'green', 'form_id' => 'form_edit'); //form_id = > ID formular
$arr['modal']['modal_form_clone']['button']['cancel'] = array('title' => 'Schließen', 'color' => 'grey', 'icon' => 'close');

$arr['top']['button']['modal_form_edit'] = array('title' => 'Neue Ausgabe erstellen', 'icon' => 'plus', 'class' => 'blue circular');