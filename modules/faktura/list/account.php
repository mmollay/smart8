<?php
$option = $data ['option'];

if (isset($_SESSION ['SetYear']) && ! $_SESSION ["filter"] ['account_' . $option . '_list'] ['SetYear']) {
	$_SESSION ["filter"] ['account_' . $option . '_list'] ['SetYear'] = $_SESSION ['SetYear'];
}

if ($option == 'out') {

	/**
	 * *****************
	 * ACCOUNT - OUT
	 * *****************
	 */
	for($ii = date ( "Y", strtotime ( '+1 year' ) ); $ii > 2011; $ii --) {
		$array_year_bill ['DATE_FORMAT(issues.date_booking,"%Y") = "' . $ii . '"'] = $ii;
	}
	
	$arr ['filter'] ['SetYear'] = array ('type' => 'dropdown','array' => $array_year_bill,'placeholder' => 'Alle Jahre','query' => "{value}" );

	$arr ['th'] ['account_id'] = array ('title' => "ID" );
	$arr ['th'] ['title'] = array ('title' => "Beschreibung" );
	$arr ['th'] ['tax'] = array ('title' => "Mwst",'align' => 'center','format' => '%' );
	$arr ['th'] ['netto'] = array ('title' => "Netto",'format' => 'euro','align' => 'right','total' => true );
	$arr ['th'] ['brutto'] = array ('title' => "Brutto",'format' => 'euro','align' => 'right','total' => true );
	$arr ['th'] ['afa_400'] = array ('title' => "Afa" );

	$arr ['mysql'] = array ('field' => "
			account_id, accounts.title title, accounts.tax,account_id,code, accountgroup.title accountgroup,
			code, SUM(netto) netto, SUM(brutto) brutto, if(afa_400,'AFA','') afa_400",'table' => "accounts 
				LEFT JOIN issues ON account = account_id 
				LEFT JOIN accountgroup ON accountgroup.accountgroup_id = accounts.accountgroup_id",'order' => 'account_id','limit' => 50,'group' => 'account_id','where' => "AND accounts.option = '$option'",'like' => 'accounts.title' );
} elseif ($option == 'in') {

	/**
	 * *****************
	 * ACCOUNT - IN
	 * *****************
	 */

	for($ii = date ( "Y", strtotime ( '+1 year' ) ); $ii > 2011; $ii --) {
		$array_year_bill ['DATE_FORMAT(bills.date_booking,"%Y") = "' . $ii . '"'] = $ii;
	}

	$arr ['filter'] ['SetYear'] = array ('type' => 'dropdown','array' => $array_year_bill,'placeholder' => 'Alle Jahre','query' => "{value}" );

	$arr ['th'] ['account_id'] = array ('title' => "ID" );
	$arr ['th'] ['tax'] = array ('title' => "Mwst",'align' => 'center','format' => '%' );
	$arr ['th'] ['title'] = array ('title' => "Beschreibung" );

	// Für Uni deaktivert wegen Ladezeiten
	// if ($_SESSION['company'] != 'uni')
	$arr ['th'] ['netto'] = array ('title' => "Netto",'format' => 'euro','align' => 'right' );

	$arr ['th'] ['accountgroup'] = array ('title' => "Gruppe" );

	$arr ['mysql'] = array (
			//'debug' => true,
			'field' => "account_id, accounts.title as title,  accounts.tax as tax, account_id,code, accountgroup.title accountgroup, SUM((bill_details.netto*count)) netto",
			'table' => "accounts 
				LEFT JOIN (bills,bill_details) ON (account = account_id AND bill_details.bill_id = bills.bill_id) 
				LEFT JOIN accountgroup ON accountgroup.accountgroup_id = accounts.accountgroup_id ",
			'where' => "AND accounts.option = 'in' ", // AND accounts.company_id = '{$_SESSION['faktura_company_id']}'
			'group' => "account_id",'limit' => 50 // 'debug' => true
	);
	
	
}


// Auflistung der Gruppenkonten
$sql_accountgroup = $GLOBALS ['mysqli']->query ( "SELECT accountgroup_id,title FROM accountgroup where user_id = '{$_SESSION['user_id']}' and `option` = '$option' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
while ( $sql_array = mysqli_fetch_array ( $sql_accountgroup ) ) {
	$array_accountgroup [$sql_array ['accountgroup_id']] = $sql_array ['title'];
}

$arr ['filter'] ['accountgroup_id'] = array ('type' => 'dropdown','array' => $array_accountgroup,'placeholder' => '--Alle Gruppen--','table' => 'accountgroup' );
$arr ['filter'] ['tax'] = array ('type' => 'dropdown','array' => $array_mwst,'placeholder' => 'Mwst.','table' => 'accounts' );

$arr ['order'] = array ('default' => 'title','array' => array ('title' => 'Namen A-Z sortieren','netto' => 'Betrag aufsteigen sortieren','netto desc' => 'Betrag absteigend sortieren' ) );

$arr ['list'] = array ('id' => 'account_' . $option . '_list','width' => '1000px','align' => '','size' => 'small','class' => 'compact selectable celled striped definition' ); // definition

$arr ['tr'] ['buttons'] ['left'] = array ('class' => 'tiny' );
$arr ['tr'] ['button'] ['left'] ['modal_form'] = array ('title' => '','icon' => 'edit','class' => 'blue mini','popup' => 'Bearbeiten' );

$arr ['tr'] ['buttons'] ['right'] = array ('class' => 'tiny' );
$arr ['tr'] ['button'] ['right'] ['modal_form_delete'] = array ('title' => '','icon' => 'trash','class' => 'mini','popup' => 'Löschen' );

$arr ['modal'] ['modal_form'] = array ('title' => 'Konto bearbeiten','class' => 'small','url' => 'form_edit.php' );
$arr ['modal'] ['modal_form_delete'] = array ('title' => 'Konto entfernen','class' => 'small','url' => 'form_delete.php' );

$arr ['top'] ['button'] ['modal_form'] = array ('title' => '','icon' => 'plus','class' => 'blue circular','popup' => 'Neues Konto anlegen' );