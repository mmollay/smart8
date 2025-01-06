<?php
// Parameter wird direkt über ein array bei Aufruf übergeben -(siehe content_list_accoutngroups_in.php
$option = $data['option'];

if ($_SESSION['SetYear'] && ! $_SESSION["filter"]['accountgroup_' . $option . '_list']['SetYear']) {
    $_SESSION["filter"]['accountgroup_' . $option . '_list']['SetYear'] = $_SESSION['SetYear'];
}

// (SELECT SUM((bill_details.netto * count)) FROM bill_details INNER JOIN bills ON bill_details.bill_id = bills.bill_id INNER JOIN accounts ON account_id = account AND accounts.accountgroup_id = id) netto_sum",
if ($option == 'in') {
    $arr['mysql'] = array('table' => "accountgroup 
            LEFT JOIN accounts ON accounts.accountgroup_id = accountgroup.accountgroup_id  
			LEFT JOIN (bills,bill_details) ON bill_details.bill_id = bills.bill_id AND accounts.account_id = account  AND document = 'rn'  AND accounts.option = 'in' 
			",'field' => "accountgroup.accountgroup_id id, accountgroup.title, 
		SUM((bill_details.netto - bill_details.netto/100*rabatt)*count - ((bill_details.netto - bill_details.netto/100*rabatt)*count)/100*discount) AS netto_sum,
		SUM(IF(accounts.tax='20' && no_mwst='' , (((bill_details.netto-bill_details.netto/100*rabatt)*count)- ((bill_details.netto-bill_details.netto/100*rabatt)*count)/100* discount)* 0.2 , 0)) AS mwst20,
		SUM(IF(accounts.tax='10' && no_mwst='', (((bill_details.netto-bill_details.netto/100*rabatt)*count)- ((bill_details.netto-bill_details.netto/100*rabatt)*count)/100* discount)* 0.1 , 0)) AS mwst10
		",'where' => "AND accountgroup.option = 'in'",'group' => "accountgroup.accountgroup_id");
} else {

    $arr['mysql'] = array('table' => "accountgroup
            LEFT JOIN accounts ON accounts.accountgroup_id = accountgroup.accountgroup_id 
            LEFT JOIN issues ON accounts.account_id = account
			",'field' => "accountgroup.accountgroup_id id, accountgroup.title,
        SUM(netto) AS netto_sum
		
",'where' => "AND accountgroup.option = 'out'",'group' => "accountgroup.accountgroup_id");
}

for ($ii = date("Y", strtotime('+1 year')); $ii > 2011; $ii --) {
    $array_year_bill['DATE_FORMAT(bills.date_booking,"%Y") = "' . $ii . '"'] = $ii;
}

$arr['filter']['SetYear'] = array('type' => 'dropdown','array' => $array_year_bill,'placeholder' => 'Alle Jahre','query' => "{value}");

$arr['list'] = array('id' => "accountgroup_" . $option . "_list",'width' => '800px','align' => '','size' => 'small','class' => 'compact selectable celled striped definition'); // definition

$arr['th']['id'] = array('title' => "ID");
$arr['th']['title'] = array('title' => "Beschreibung");
$arr['th']['netto_sum'] = array('title' => "Netto",'format' => 'euro','align' => 'right');
$arr['th']['mwst20'] = array('title' => "Mwst 20%",'format' => 'euro','align' => 'right');
$arr['th']['mwst10'] = array('title' => "Mwst 10%",'format' => 'euro','align' => 'right');

$arr['tr']['buttons']['left'] = array('class' => 'tiny');
$arr['tr']['button']['left']['modal_form'] = array('title' => '','icon' => 'edit','class' => 'blue mini','popup' => 'Bearbeiten');

$arr['tr']['buttons']['right'] = array('class' => 'tiny');
$arr['tr']['button']['right']['modal_form_delete'] = array('title' => '','icon' => 'trash','class' => 'mini','popup' => 'Löschen');

$arr['modal']['modal_form'] = array('title' => 'Kontogruppe bearbeiten','class' => 'small','url' => 'form_edit.php');
$arr['modal']['modal_form_delete'] = array('title' => 'Kontogroppe entfernen','class' => 'small','url' => 'form_delete.php');

$arr['top']['button']['modal_form'] = array('title' => 'Kontogruppe anlegen','icon' => 'plus','class' => 'mini blue ');