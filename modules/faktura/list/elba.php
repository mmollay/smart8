<?php
if ($_SESSION['SetYear'] && ! $_SESSION["filter"]['issues_list']['SetYear']) {
    $_SESSION["filter"]['issues_list']['SetYear'] = $_SESSION['SetYear'];
}

/**
 * ARRAY - YEAR
 */
// $array_year['DATE_FORMAT(date,"%Y") = "2017"'] = 'Alle Jahre';
for ($ii = date("Y", strtotime('+1 year')); $ii > 2011; $ii --) {
    $array_elba_year['DATE_FORMAT(date,"%Y") = "' . $ii . '"'] = $ii;
}

$array_account_number = array(
    // '4548 2520 0992 8035' => 'Visa (4548 2520 0992 8035)',
    'AT533293700000608745' => 'Firma (60 8745)','AT793293700000606822' => 'Privat (60 6822)','AT473293700000632521' => 'Obststadt (63 2521)');

/**
 * ARRAY - MONTH
 */
$array_filter_elba_month = array('DATE_FORMAT(date,"%m") = "01"' => '[01] Jänner','DATE_FORMAT(date,"%m") = "02"' => '[02] Februar','DATE_FORMAT(date,"%m") = "03"' => '[03] März','DATE_FORMAT(date,"%m") = "04"' => '[04] April','DATE_FORMAT(date,"%m") = "05"' => '[05] Mai','DATE_FORMAT(date,"%m") = "06"' => '[06] Juni','DATE_FORMAT(date,"%m") = "07"' => '[07] Juli','DATE_FORMAT(date,"%m") = "08"' => '[08] Ausgust','DATE_FORMAT(date,"%m") = "09"' => '[09] September','DATE_FORMAT(date,"%m") = "10"' => '[10] Oktober','DATE_FORMAT(date,"%m") = "11"' => '[11] November','DATE_FORMAT(date,"%m") = "12"' => '[12] Dezember');

/**
 * ****************************
 * Zusatzfilter
 * Kommentare
 * ****************************
 */
$sql_query = $GLOBALS['mysqli']->query("SELECT * FROM issues WHERE comment > '' ") or die(mysqli_error($GLOBALS['mysqli'])); // AND company_id = '{$_SESSION['faktura_company_id']}'
$count_comment = mysqli_num_rows($sql_query);

$array_more_filter = array(
    'connect_id > 0' => "alle Verbuchten",
    'connect_id = 0' => "alle NICHT Verbuchten",
    'connect_id = 0 && automator_id > 0' => "Noch zu Verbuchende",
    'automator_id > 0' => "Alle Automatisierten",
    'automator_id = 0' => "Alle NICHT Automatisierten",
    'issues.bill_id = 0' => "Alle NICHT Verbuchte",
    'comment > "" ' => "Nur Kommentare ($count_comment)"
);

$array_inout_filter = array('amount < 0 ' => "Ausgaben",'amount > 0 ' => "Einnahmen");

$arr['mysql'] = array('field' => "
        data_elba.elba_id, automator_id, if (data_elba.comment>'', CONCAT('<i class=\"icon star red tooltip\" data-html=\"<b>ToDo:</b><br>',data_elba.comment,'\">'),'') comment,
		IF(LENGTH(text) >= 200, CONCAT(substring(text, 1,200), CONCAT('<span class=\'km_info\' title=\'',text,'\'>[...]</span>')), text) text, data_elba.date, amount, data_elba.account,
        (CASE 
		WHEN issues.bill_id then issues.bill_number
		WHEN bills.bill_id then bills.bill_number
		WHEN automator_id then CONCAT('<div id=\'insertbutton_',data_elba.elba_id,'\' class=\'ui mini icon blue button tooltip\' onclick=\"insert_inner_issues(\'',automator_id,'\',\'',data_elba.elba_id,'\',\'elba\');\" title=\'Direktes einpflegen\'>Einpflegen</div>')
		END) as bill_id
    
    ",'table' => "data_elba 
        LEFT JOIN issues ON data_elba.elba_id = issues.elba_id
        LEFT JOIN bills ON data_elba.connect_id = bills.bill_id
    ",'group' => 'data_elba.elba_id','limit' => 100,'like' => 'text, amount, date, issues.bill_number');

$arr['order'] = array('default' => 'date desc','array' => array('date desc' => 'Datum absteigend','date' => 'Datum aufsteigend','amount desc' => 'Betrag absteigend','amount ' => 'Betrag aufsteigend'));

$arr['list'] = array('id' => 'elba_list','width' => '','align' => '','width' => '','size' => 'small','class' => 'very compact celled striped definition','hover' => true); // definition

$arr['filter']['SetYear'] = array('type' => 'dropdown','query' => "{value}",'array' => $array_elba_year,'placeholder' => 'Alle Jahre');

$arr['filter']['select_month'] = array('type' => 'dropdown','query' => "{value}",'array' => $array_filter_elba_month,'placeholder' => '--Alle Monate--');

$arr['filter']['account'] = array('type' => 'dropdown','array' => $array_account_number,'placeholder' => '--Alle Konten--','table' => 'data_elba');

$arr['filter']['inout'] = array('type' => 'dropdown','array' => $array_inout_filter,'placeholder' => 'Ein- und Ausgang','query' => "{value}");
$arr['filter']['more'] = array('type' => 'dropdown','array' => $array_more_filter,'placeholder' => 'Verbuchte/Unverbuchte','query' => "{value}");

$arr['th']['automator_id'] = array('title' => "<i class='icon grey star'></i>",'tooltip' => 'Automatisiert','replace' => array('>0' => "<i class='icon orange star'></i>",'0' => "<i class='icon grey star'></i>"),'align' => 'center');
$arr['th']['elba_id'] = array('title' => "ID",'align' => 'center');

$arr['th']['bill_id'] = array('title' => "ReNr.",'align' => 'center');

$arr['th']['account'] = array('title' => "Konto");

$arr['th']['date'] = array('title' => "Datum");
$arr['th']['amount'] = array('nowrap' => '1','title' => "Betrag",'format' => 'euro_color','align' => 'right');
$arr['th']['text'] = array('title' => "Beschreibung");
// $arr['th']['comment'] = array(
// 'title' => "",
// 'align' => 'center'
// );

$arr['tr']['buttons']['left'] = array('class' => 'tiny');
$arr['tr']['button']['left']['modal_form_edit'] = array('title' => '','icon' => 'robot','class' => 'blue mini','popup' => 'Automation zuweisen','filter' => array(['field' => 'automator_id','operator' => '==','value' => '0']));
// $arr ['tr'] ['button'] ['left'] ['modal_form_edit'] = array ('title' => '','icon' => 'robot','class' => 'blue mini','popup' => 'Automation zuweisen' );
// $arr['tr']['buttons']['right'] = array(
// 'class' => 'tiny'
// );
// $arr['tr']['button']['right']['modal_form_delete'] = array(
// 'title' => '',
// 'icon' => 'trash',
// 'class' => 'mini',
// 'popup' => 'Löschen'
// );

$arr['modal']['modal_form_automator_edit'] = array('title' => 'Automator hinzufügen','class' => 'small');
$arr['modal']['modal_form_edit'] = array('title' => 'Elba-Automator bearbeiten','class' => 'small','url' => 'form_edit.php');

