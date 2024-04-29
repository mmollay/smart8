<?php
/*
 * Auslesen aktueller Positionen
 */
include (__DIR__ . '/../f_config.php');

// Check ob Eintrag bereits vorhanden ist
// Art_NR UND Art_Title
if ($_POST['modus'] == 'add_client') {
	$check_client_number = mysql_singleoutput("SELECT client_number FROM client WHERE client_number='{$_POST['client_number']}' AND company_id = '{$_POST['company_id']}' ", "client_number");
	$check_company_1 = mysql_singleoutput("SELECT * FROM client WHERE company_id = '{$_POST['company_id']}' AND company_1 = '{$_POST['company_1']}' ", "company_1");
	$check_email = mysql_singleoutput("SELECT * FROM client WHERE email = '{$_POST['email']}' AND company_id = '{$_POST['company_id']}' ", "email");
	// Clientnumber setzen
} else {
	$check_client_number = mysql_singleoutput("SELECT client_number FROM client WHERE client_number='{$_POST['client_number']}' AND company_id = '{$_POST['company_id']}' AND client_id != '{$_POST['client_id']}' ", "client_number");
	// $check_email = mysql_singleoutput("SELECT email FROM client WHERE email = '{$_POST['email']}' AND company_id = '{$_SESSION['faktura_company_id']}' AND client_id != '{$_POST['client_id']}' ","email");
}

if (!$_POST['client_number']) {
	echo 'empty_client_number';
} elseif ($check_email) {
	echo 'email_exists';
} elseif ($check_client_number) {
	echo 'double_client_number';
} elseif ($check_company_1) {
	echo 'double_company_name';
} else {
	// Template anlegen
	$GLOBALS['mysqli']->query("REPLACE INTO client SET
	activ         = '{$_POST['activ']}',
	abo           = '{$_POST['abo']}',
	activate      = '{$_POST['activate']}',
	send_date     = '{$_POST['send_date']}',
	client_id     = '{$_POST['client_id']}',
	client_number = '{$_POST['client_number']}',
	company_id = '{$_POST['company_id']}',
	company_1 = '{$_POST['company_1']}',
	company_2 = '{$_POST['company_2']}',
	title = '{$_POST['title']}',
	gender = '{$_POST['gender']}',
	firstname = '{$_POST['firstname']}',
	secondname = '{$_POST['secondname']}',
	street = '{$_POST['street']}',
	city = '{$_POST['city']}',
	zip = '{$_POST['zip']}',
	country = '{$_POST['country']}',
	tel = '{$_POST['tel']}',
	mobil = '{$_POST['mobil']}',
	fax = '{$_POST['fax']}',
	email = '{$_POST['email']}',
	web = '{$_POST['web']}',
	uid = '{$_POST['uid']}',
	post = '{$_POST['post']}',
	newsletter = '{$_POST['newsletter']}',
	password = '{$_POST['password']}',
	id_card_no = '{$_POST['id_card_no']}',
	student = '{$_POST['student']}',
	matrical_nr = '{$_POST['matrical_nr']}',
	specialist_species_for = '{$_POST['specialist_species_for']}',
	own_practice = '{$_POST['own_practice']}',
	group_practice = '{$_POST['group_practice']}',
	employed = '{$_POST['employed']}',
	industry = '{$_POST['industry']}',
	administration = '{$_POST['administration']}',
	university = '{$_POST['university']}',
	no_exercise = '{$_POST['no_exercise']}',
	retirement = '{$_POST['retirement']}',
	other = '{$_POST['other']}',
	hp_inside = ' {$_POST['hp_inside']}',
	birth = ' {$_POST['birth']}',
	`commend` = '{$_POST['commend']}',
	delivery_company1   = '{$_POST['delivery_company1']}',
	delivery_company2   = '{$_POST['delivery_company2']}',
	delivery_title      = '{$_POST['delivery_title']}',
	delivery_gender     = '{$_POST['delivery_gender']}',
	delivery_firstname  = '{$_POST['delivery_firstname']}',
	delivery_secondname = '{$_POST['delivery_secondname']}',
	delivery_street     = '{$_POST['delivery_street']}',
	delivery_city       = '{$_POST['delivery_city']}',
	delivery_zip        = '{$_POST['delivery_zip']}',
	delivery_country    = '{$_POST['delivery_country']}',
	delivery_tel        = '{$_POST['delivery_tel']}'

	") or die(mysqli_error($GLOBALS['mysqli']));
	$client_id = mysqli_insert_id($GLOBALS['mysqli']);
	echo $client_id;

	/*
	 * Eweiterung OEGT
	 */
	if ($oegt_modus) {
		include_once ('../oegt/save_client.php');
	}
}
?>