<?php
$add_js .= "<script type=\"text/javascript\" src=\"js/form_issues.js\"></script>";

if ($_POST ['update_id']) {
	$arr ['sql'] = array ('query' => "SELECT *, account_id account  from automator WHERE automator_id = '{$_POST['update_id']}' " );
}

// Konten auslesen aus der Datenbank

$sql_query = $GLOBALS ['mysqli']->query ( "SELECT * FROM accounts WHERE `option` = 'out' order by title " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) ); // AND company_id = '{$_SESSION['faktura_company_id']}'
while ( $sql_array = mysqli_fetch_array ( $sql_query ) ) {
	$account_array_out [$sql_array ['account_id']] = $sql_array ['title'] . "(" . $sql_array ['tax'] . "%)";
}

$sql_query = $GLOBALS ['mysqli']->query ( "SELECT * FROM issues_group order by name" ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) ); // AND company_id = '{$_SESSION['faktura_company_id']}'
while ( $sql_array = mysqli_fetch_array ( $sql_query ) ) {
	if ($sql_array ['name']) {
		$issues_group_array [$sql_array ['issues_group_id']] = $sql_array ['name'];
	}
}

$arr ['field'] ['description'] = array ('type' => 'input','label' => 'Beschreibung','validate' => true,'focus' => true,'class' => 'desc thirteen wide','focus' => true,'search' => true );
$arr ['field'] ['word'] = array ('type' => 'textarea','label' => 'Schlüsselwörter','placeholder' => 'A1 Telekom','search' => true );

$arr ['field'] ['account'] = array ('class' => 'ten wide search','type' => 'dropdown','label' => 'Konto','array' => $account_array_out,'validate' => true,'clear' => true );
$arr ['field'] ['client_id'] = array ('class' => 'search','type' => 'dropdown','label' => "Firma <div id='add_group' data-position='bottom center' class='button mini ui blue label circular'>+</div>",'array' => $issues_group_array,'clear' => true );

$arr ['field'] ['comment'] = array ('type' => 'input','label' => 'Kommentar' );


