<?php

/**********************************************************
 * SSI_FAKTURA - CLIENT - LIST
 *********************************************************/
$year = $_SESSION['SetYear'];

if ($year != 'all' and $year > 0) {
	$add = "AND DATE_FORMAT(date_membership_start,'%Y') <= $year ";
	$add2 = "DATE_FORMAT(date_membership_stop,'%Y-%m-%d') >= NOW() OR ";
}

$arr['mysql'] = array(
	'table' => "client 
			LEFT JOIN bills ON client.client_id = bills.client_id 
			LEFT JOIN membership ON client.client_id = membership.client_id 
			LEFT JOIN sections ON client.client_id = sections.client_id 
			LEFT JOIN tree ON tree.client_faktura_id = client.client_id",
	'field' => "client.client_id, join_date,
		(CASE
		WHEN (client.firstname != '' OR client.secondname != '') AND client.company_1 !='' then CONCAT (client.company_1,' (',client.firstname,' ',client.secondname,')')
		WHEN (client.firstname != '' OR client.secondname != '') AND client.company_1 ='' then CONCAT (client.firstname,' ',client.secondname)
		ELSE client.company_1
		END) as company_1,

		if (reg_date,DATE_FORMAT(reg_date,'%Y-%m-%d'),'') reg_date,
		CONCAT ('<i class=\" ', client.country ,' flag\"></i>') country,
		COUNT(tree.client_faktura_id) tree_count,
		client.client_number client_number,
		if (!abo,'<i class=\"icon disabled checkmark\"></i>','<i class=\"green icon checkmark\"></i>') abo,
		if (!newsletter,'<i class=\"icon disabled checkmark\"></i>','<i class=\"green icon checkmark\"></i>') newsletter,
		if (!client.post,'<i class=\"icon disabled checkmark\"></i>','<i class=\"green icon checkmark\"></i>') post,
		
		IF ((SELECT COUNT(*) FROM membership WHERE 1 $add AND ($add2 date_membership_stop = 0000-00-00) AND client_id = client.client_id),'<div class=icon_hakerl>&#10004;</div>','') activ2,
		client.email email, 
		client.zip zip, client.city city, client.birth birth, send_date, 
		if (delivery_city != '', CONCAT (client.city,' <div class=set_tooltip title=\'',delivery_company1,'<br>',delivery_zip,' ',delivery_city,'\'>[Liefer]</div>'),client.city) city,
		client.client_id, client.company_id,
		ROUND((SELECT SUM(brutto) FROM bills WHERE bills.client_id = client.client_id AND date_storno = '0000-00-00' AND document = 'rn'),2) brutto,
		ROUND((SELECT SUM(booking_total) FROM bills WHERE bills.client_id = client.client_id AND date_storno = '0000-00-00' AND document = 'rn'),2) booking_total,
		ROUND((SELECT SUM(brutto ) - SUM( booking_total ) FROM bills WHERE bills.client_id = client.client_id AND date_storno = '0000-00-00' AND document = 'rn'),2) amound_open,
		if (client.tel, CONCAT('<button class=client_info title=\"Tel:',client.tel,'\">Info</button>'),'') info
		",
	// where => "AND client.company_id = '{$_SESSION['faktura_company_id']}' " ,
	'group' => 'client.client_id',
	'limit' => '30',
	'like' => 'client.client_number,client.company_1,client.firstname,client.secondname, client.city, client.zip, client.email',
	'export' => 'client.client_number,client.company_1,client.firstname,client.secondname, client.city, client.zip, client.email'

);

$arr['list'] = array('id' => 'client_list', 'align' => '', 'size' => 'small', 'class' => 'compact selectable celled striped definition'); // definition
$arr['list']['loading_time'] = true;

$arr['order'] = array('default' => 'client_number desc', 'array' => array('client_number desc' => 'Kundennummer absteigend sortieren', 'client_number' => 'Kundennummer aufsteigend sortieren', 'brutto desc' => 'Betrag absteigend sortieren'));

// Firmen ausgeben
$sql_company = $GLOBALS['mysqli']->query("SELECT company_id, company_1 FROM company where user_id = '{$_SESSION['user_id']}'") or die(mysqli_error($GLOBALS['mysqli']));
while ($sql_array = mysqli_fetch_array($sql_company)) {
	$company_array[$sql_array['company_id']] = $sql_array['company_1'];
}

// $arr['filter']['company_id'] = array ( 'type' => 'dropdown' , 'array' => $company_array , 'placeholder' => '--Alle Firmen--', 'table' => 'client' );

$arr['th']['client_id'] = array('title' => "ID");
$arr['th']['client_number'] = array('title' => "Kd.Nr ");
// $arr['th']['activ'] = array ( 'title' =>"Status" );
// $arr['th']['abo'] = array ( 'title' =>"Abo",  'align' =>'center' );
$arr['th']['company_1'] = array('title' => "Firma");
$arr['th']['zip'] = array('title' => "Plz");
$arr['th']['city'] = array('title' => "Ort");
$arr['th']['country'] = array('title' => "Land");
$arr['th']['tree_count'] = array('title' => 'Bäume', 'info' => 'Baumspender', 'align' => 'center');

$arr['th']['email'] = array('title' => "Email");
$arr['th']['newsletter'] = array('title' => "NL", 'align' => 'center');
// $arr['th']['post'] = array ( 'title' =>"Post",  'align' =>'center' );
$arr['th']['amound_open'] = array('title' => "Offen", 'format' => 'euro', 'align' => 'right');
$arr['th']['booking_total'] = array('title' => "Verbucht", 'format' => 'euro', 'align' => 'right');
$arr['th']['brutto'] = array('title' => "Gesamt", 'format' => 'euro', 'align' => 'right');
$arr['th']['join_date'] = array('title' => "Betritt");

$arr['tr']['buttons']['left'] = array('class' => 'tiny');
$arr['tr']['button']['left']['modal_form'] = array('title' => '', 'icon' => 'edit', 'class' => 'blue mini', 'popup' => 'Bearbeiten');

$arr['tr']['buttons']['right'] = array('class' => 'tiny');
$arr['tr']['button']['right']['modal_form_delete'] = array('title' => '', 'icon' => 'trash', 'class' => 'mini', 'popup' => 'Löschen', 'filter' => array(['field' => 'brutto', 'operator' => '<', 'value' => '0.00'], ['field' => 'tree_count', 'operator' => '==', 'value' => '0', 'link' => 'and']));

$arr['modal']['modal_form'] = array('title' => 'Kunden bearbeiten', 'class' => '', 'url' => 'form_edit.php');
$arr['modal']['modal_form_delete'] = array('title' => 'Kunden entfernen', 'class' => 'small', 'url' => 'form_delete.php');

$arr['top']['button']['modal_form'] = array('title' => 'Neue Kunden anlegen', 'icon' => 'plus', 'class' => 'blue circular');

$arr['checkbox']['button']['modal_form_delete'] = array('title' => 'Delete', 'icon' => 'delete', 'class' => 'red mini');