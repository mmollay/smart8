<?php
// Call Title from db
$query_title = $GLOBALS['mysqli']->query("SELECT distinct(title) FROM client");
while ($fetch_array_title = mysqli_fetch_array($query_title)) {
	if ($fetch_array_title[0] == '') {
	} elseif (!is_numeric($fetch_array_title[0])) {
		$array_title[$fetch_array_title[0]] = $fetch_array_title[0];
	}
}

if ($_POST['update_id']) {
	// $form1->setConfig ( "load_data", "SELECT * from client WHERE client_id = '{$_POST['update_id']}' " );
	$arr['sql'] = array('query' => "SELECT * from client WHERE client_id = '{$_POST['update_id']}'");
	$year = date('Y');
	$company_id = '';
	$client_number = '';
	$query = $GLOBALS['mysqli']->query("
					SELECT
					COUNT(*) status
					FROM client INNER JOIN membership
					ON client.client_id = membership.client_id
					WHERE DATE_FORMAT(date_membership_start,'%Y') <= $year
					AND (DATE_FORMAT(date_membership_stop,'%Y') >= NOW() OR date_membership_stop = '0000-00-00')
					AND membership.client_id='{$_POST['update_id']}' ") or die(mysqli_error($GLOBALS['mysqli']));
	$array = mysqli_fetch_array($query);
} else {
	$company_id = $_SESSION['faktura_company_id'];
	$set_activ = 1;
	$join_date = date('Y-m-d'); //default is actual date
	$arr['hidden']['modus'] = 'add_client';
	// New CLientnumber
	$client_number = mysql_singleoutput("SELECT MAX(client_number) as client_number FROM client", "client_number") + 1;
}

$arr['ajax'] = array('success' => "after_form_client(data)", 'dataType' => "html");

$arr['tab'] = array('tabs' => array(1 => 'Kontaktdaten', 4 => 'Lieferadresse', 2 => 'Erweiterung', 3 => 'Mitglieder/Sektionen'), 'active' => '1');

$arr['field']['abo'] = array('tab' => '1', 'type' => 'checkbox', 'label' => 'abonnieren');
//$arr['field']['company_id'] = array ( 'tab' => '1' , 'type' => 'dropdown' , 'array' => $arr_comp , 'label' => 'Firma' , 'value' => $_SESSION['faktura_company_id'] );

$arr['field']['client_number'] = array('tab' => '1', 'type' => 'input', 'label' => 'Kundennummer', 'value' => $client_number);
$arr['field'][] = array('tab' => '1', 'type' => 'div', 'class' => 'two fields');
$arr['field']['company_1'] = array('tab' => '1', 'type' => 'input', 'label' => 'Firma', 'focus' => true);
$arr['field']['company_2'] = array('tab' => '1', 'type' => 'input', 'label' => 'Firma(Zusatz)');
$arr['field'][] = array('tab' => '1', 'type' => 'div_close');

$arr['field'][] = array('tab' => '1', 'type' => 'div', 'class' => 'four fields');
$arr['field']['gender'] = array('class' => 'four wide', 'tab' => '1', 'type' => 'dropdown', 'label' => 'Titel', 'array' => array('f' => 'Frau', 'm' => 'Herr'));
$arr['field']['title'] = array('class' => 'three wide', 'tab' => '1', 'type' => 'input', 'label' => 'Titel');
$arr['field']['firstname'] = array('class' => 'four wide', 'tab' => '1', 'type' => 'input', 'label' => 'Vorname');
$arr['field']['secondname'] = array('class' => 'five wide', 'tab' => '1', 'type' => 'input', 'label' => 'Nachname');
$arr['field'][] = array('tab' => '1', 'type' => 'div_close');
$arr['field']['birthday'] = array('class' => 'five wide', 'tab' => '1', 'type' => 'date', 'label' => 'Geburtsdatum', );

$arr['field']['street'] = array('tab' => '1', 'type' => 'input', 'label' => 'Strasse');
$arr['field'][] = array('tab' => '1', 'type' => 'div', 'class' => 'three fields');
$arr['field']['zip'] = array('tab' => '1', 'type' => 'input', 'label' => 'PLZ');
$arr['field']['city'] = array('tab' => '1', 'type' => 'input', 'label' => 'Stadt');
$arr['field']['country'] = array('tab' => '1', 'type' => 'dropdown', 'array' => 'country', 'label' => 'Land');
$arr['field'][] = array('tab' => '1', 'type' => 'div_close');

$arr['field'][] = array('tab' => '1', 'type' => 'div', 'class' => 'three fields');
$arr['field']['tel'] = array('tab' => '1', 'type' => 'input', 'label' => 'Tel');
$arr['field']['email'] = array('tab' => '1', 'type' => 'input', 'label' => 'Email');
$arr['field']['web'] = array('tab' => '1', 'type' => 'input', 'label' => 'Internet');
$arr['field'][] = array('tab' => '1', 'type' => 'div_close');
$arr['field'][] = array('tab' => '1', 'type' => 'div', 'class' => 'two fields');
$arr['field']['commend'] = array('tab' => '1', 'type' => 'input', 'label' => 'Kommentar');
$arr['field'][] = array('tab' => '1', 'type' => 'div_close');
$arr['field']['post'] = array('tab' => '1', 'type' => 'checkbox', 'label' => 'Postversand');
$arr['field']['newsletter'] = array('tab' => '1', 'type' => 'checkbox', 'label' => 'Newsletter');

$arr['field']['hp_inside'] = array('tab' => '1', 'type' => 'checkbox', 'label' => 'HP-Download');
$arr['field']['password'] = array('tab' => '1', 'type' => 'input', 'label' => 'Passwort', 'label_right' => '<div id=button_generate_password>Passwort erzeugen</div>', 'label_right_class' => 'button');

$arr['field']['send_date'] = array('type' => 'hidden');
$arr['field']['map_user_id'] = array('type' => 'hidden');
$arr['field']['map_page_id'] = array('type' => 'hidden');
$arr['hidden']['check_update_id'] = $update_id;

$arr['field'][] = array('tab' => '4', 'type' => 'div', 'class' => 'two fields');
$arr['field']['delivery_company1'] = array('tab' => '4', 'type' => 'input', 'label' => 'Firma');
$arr['field']['delivery_company2'] = array('tab' => '4', 'type' => 'input', 'label' => 'Firma(Zusatz)');
$arr['field'][] = array('tab' => '4', 'type' => 'div_close');
$arr['field'][] = array('tab' => '4', 'type' => 'div', 'class' => 'three fields');
$arr['field']['delivery_title'] = array('tab' => '4', 'type' => 'dropdown', 'label' => 'Titel', 'array' => $array_title);
$arr['field']['delivery_firstname'] = array('tab' => '4', 'type' => 'input', 'label' => 'Vorname');
$arr['field']['delivery_secondname'] = array('tab' => '4', 'type' => 'input', 'label' => 'Nachname');
$arr['field'][] = array('tab' => '4', 'type' => 'div_close');
$arr['field']['delivery_street'] = array('tab' => '4', 'type' => 'input', 'label' => 'Strasse');
$arr['field'][] = array('tab' => '4', 'type' => 'div', 'class' => 'three fields');
$arr['field']['delivery_zip'] = array('tab' => '4', 'type' => 'input', 'label' => 'PLZ');
$arr['field']['delivery_city'] = array('tab' => '4', 'type' => 'input', 'label' => 'Stadt');
$arr['field']['delivery_country'] = array('tab' => '4', 'type' => 'dropdown', 'array' => 'country', 'label' => 'Land');
$arr['field'][] = array('tab' => '4', 'type' => 'div_close');

/*
 * Speziell für OEGT!!!!
 * martin@ssi.at 03.10.2011
 */

if ($oegt_modus) {
	include ('../oegt/client_addone2.inc.php');
} else {
	$arr['field']['uid'] = array('tab' => '2', 'type' => 'input', 'label' => 'UID');
	$arr['field']['join_date'] = array('tab' => '2', 'type' => 'date', 'label' => 'Beitrittsdatum', 'value' => $join_date);
}

$add_js .= "<script type=\"text/javascript\" src=\"js/form_client.js\"></script>";