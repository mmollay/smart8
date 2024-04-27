<?php

/**********************************************************
 * SSI_FAKTURA - CLIENT - LIST
 *********************************************************/
$year = $_SESSION ['SetYear'];

if ($year != 'all' and $year > 0) {
	$add = "AND DATE_FORMAT(date_membership_start,'%Y') <= $year ";
	$add2 = "DATE_FORMAT(date_membership_stop,'%Y-%m-%d') >= NOW() OR ";
}

$arr ['mysql'] = array ('table_main' => 'client','table' => "client c
    LEFT JOIN bills b ON c.client_id = b.client_id
    LEFT JOIN sections s ON c.client_id = s.client_id
	LEFT JOIN membership m ON c.client_id = m.client_id 
	LEFT JOIN (
        SELECT client_id, COUNT(*) as activ_membership
        FROM membership
        WHERE 1 $add AND ($add2 date_membership_stop = 0000-00-00)
        GROUP BY client_id
    ) mm ON c.client_id = mm.client_id
     
",'field' => "
	c.client_id,
    abo,
    CASE
        WHEN (c.firstname != '' OR c.secondname != '') AND c.company_1 !='' 
            THEN CONCAT (c.company_1,' (',c.firstname,' ',c.secondname,')')
        WHEN (c.firstname != '' OR c.secondname != '') AND c.company_1 =''
            THEN CONCAT (c.firstname,' ',c.secondname)
        ELSE c.company_1
    END as company_1,
    IF (reg_date, DATE_FORMAT(reg_date,'%Y-%m-%d'), '') reg_date,
    CONCAT ('<i class=\" ', c.country ,' flag\"></i>') country,
    c.client_number,
    IF (!abo,'<i class=\"icon disabled checkmark\"></i>','<i class=\"green icon checkmark\"></i>') abo,
    IF (!newsletter,'<i class=\"icon disabled checkmark\"></i>','<i class=\"green icon checkmark\"></i>') newsletter,
    IF (!c.post,'<i class=\"icon disabled checkmark\"></i>','<i class=\"green icon checkmark\"></i>') post,
	IF (!activ_membership,'<i class=\"icon disabled checkmark\"></i>','<i class=\"green icon checkmark\"></i>') activ,	
    c.email, 
    c.zip, c.city, c.birth, send_date, 
    IF (delivery_city != '', CONCAT (c.city,' <div class=set_tooltip title=\'',delivery_company1,'<br>',delivery_zip,' ',delivery_city,'\'>[Liefer]</div>'), c.city) city,
    c.company_id,
    ROUND(SUM(CASE WHEN b.date_storno = '0000-00-00' THEN b.brutto ELSE 0 END), 2) AS brutto,
    ROUND(SUM(CASE WHEN b.date_storno = '0000-00-00' THEN b.booking_total ELSE 0 END), 2) AS booking_total,
    ROUND(SUM(CASE WHEN b.date_storno = '0000-00-00' THEN b.brutto - b.booking_total ELSE 0 END), 2 ) AS amount_open,
    IF (c.tel, CONCAT('<button class=client_info title=\"Tel:',c.tel,'\">Info</button>'), '') info

		",'group' => 'c.client_id ','limit' => '30','like' => 'c.client_number,c.company_1,c.firstname,c.secondname, c.city, c.zip, c.email','export' => 'c.client_number,c.company_1,c.firstname,c.secondname, c.city, c.zip, c.email' );

// $arr ['mysql'] ['debug'] = true;

$arr ['list'] = array ('id' => 'client_oegt_list','align' => '','size' => 'small','class' => 'compact selectable celled striped definition' ); // definition
$arr ['list'] ['loading_time'] = true;

$arr ['order'] = array ('default' => 'c.client_number desc','array' => array ('c.client_number desc' => 'Kundennummer absteigend sortieren','c.client_number' => 'Kundennummer aufsteigend sortieren','brutto desc' => 'Betrag absteigend sortieren' ) );

// Firmen ausgeben
$sql_company = $GLOBALS ['mysqli']->query ( "SELECT company_id, company_1 FROM company where user_id = '{$_SESSION['user_id']}'" ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
while ( $sql_array = mysqli_fetch_array ( $sql_company ) ) {
	$company_array [$sql_array ['company_id']] = $sql_array ['company_1'];
}
$arr ['filter'] ['company_id'] = array ('type' => 'dropdown','array' => $company_array,'placeholder' => '--Alle Firmen--','table' => 'c' );

$array_filter_client = array (
		'' => 'Alle Kunden',
		'activ_membership > 0' => 'Aktive Kunden',
		'brutto > 0' => 'Kunden mit Umsatz',
		'having amound_open > 0' => 'Kunden mit offenen Umsatz',
		'newsletter = 1' => 'Kunden mit Newsletter' 
);

$arr ['filter'] ['client'] = array ('type' => 'dropdown','class' => 'tertiary grey basic','array' => $array_filter_client,'default_value' => '','placeholder' => '--Kunden--','query' => "{value}" );

$query = $GLOBALS ['mysqli']->query ( " SELECT * from article_temp where account=63" );
while ( $array1 = mysqli_fetch_array ( $query ) ) {
	$id = $array1 ['temp_id'];
	$array_section [$id] = $array1 ['art_title'];
}
$arr ['filter'] ['section_id'] = array ('type' => 'dropdown','array' => $array_section,'table' => 's','placeholder' => '--Sektionen--' );

$query = $GLOBALS ['mysqli']->query ( " SELECT * from article_temp where account=62" );
while ( $array1 = mysqli_fetch_array ( $query ) ) {
	$id = $array1 ['temp_id'];
	$array_membership [$id] = $array1 ['art_title'];
}
$arr ['filter'] ['membership_id'] = array ('type' => 'dropdown','array' => $array_membership,'table' => 'm','placeholder' => '--Membership--' );

// $arr['th']['client_id'] = array ( 'title' =>"ID" );
$arr ['th'] ['client_number'] = array ('title' => "Kd.Nr " );
$arr ['th'] ['activ'] = array ('title' => "Status" );
$arr ['th'] ['abo'] = array ('title' => "Abo",'align' => 'center' );
$arr ['th'] ['company_1'] = array ('title' => "Firma" );
$arr ['th'] ['zip'] = array ('title' => "Plz" );
$arr ['th'] ['city'] = array ('title' => "Ort" );
// $arr ['th'] ['country'] = array ('title' => "Land" );

$arr ['th'] ['email'] = array ('title' => "Email" );
$arr ['th'] ['newsletter'] = array ('title' => "NL",'align' => 'center' );
// $arr['th']['post'] = array ( 'title' =>"Post", 'align' =>'center' );
$arr ['th'] ['amount_open'] = array ('title' => "Offen",'format' => 'euro','align' => 'right' );
$arr ['th'] ['booking_total'] = array ('title' => "Verbucht",'format' => 'euro','align' => 'right' );
$arr ['th'] ['brutto'] = array ('title' => "Gesamt ",'format' => 'euro','align' => 'right' );

$arr ['tr'] ['buttons'] ['left'] = array ('class' => 'tiny' );
$arr ['tr'] ['button'] ['left'] ['modal_form'] = array ('title' => '','icon' => 'edit','class' => 'blue mini','popup' => 'Bearbeiten' );

$arr ['tr'] ['buttons'] ['right'] = array ('class' => 'tiny' );
$arr ['tr'] ['button'] ['right'] ['modal_form_delete'] = array ('title' => '','icon' => 'trash','class' => 'mini','popup' => 'Löschen','filter' => array ([ 'field' => 'brutto','operator' => '<','value' => '0.00' ] ) );

$arr ['modal'] ['modal_form'] = array ('title' => 'Kunden bearbeiten','url' => 'form_edit.php','class' => 'scrolling very wide' );
$arr ['modal'] ['modal_form'] ['button'] ['submit'] = array ('title' => 'Speichern','color' => 'blue','form_id' => 'form_edit' );
$arr ['modal'] ['modal_form'] ['button'] ['cancel'] = array ('title' => 'Schließen','onclick' => "$('#modal_form, #modal_form_edit, #modal_form_clone, #modal_form_new').modal('hide');" );

$arr ['modal'] ['modal_form_delete'] = array ('title' => 'Kunden entfernen','class' => 'small','url' => 'form_delete.php' );

$arr ['modal'] ['modal_form2'] = array ('title' => 'Kunden bearbeiten','class' => 'scrolling very wide' );

$arr ['top'] ['button'] ['modal_form'] = array ('title' => 'Neue Kunden anlegen','icon' => 'plus','class' => 'blue circular' );

$arr ['checkbox'] ['button'] ['modal_form_delete'] = array ('title' => 'Delete','icon' => 'delete','class' => 'red mini' );
function get_count_pre($year, $company_id) {
	return "SELECT * FROM client LEFT JOIN membership
		ON client.client_id = membership.client_id
		WHERE DATE_FORMAT(date_membership_start,'%Y') <= '$year'
		AND (DATE_FORMAT(date_membership_stop,'%Y') = '0000' OR DATE_FORMAT(date_membership_stop,'%Y') >= '$year')
		AND company_id='$company_id'
		AND DATE_FORMAT(send_date,'%Y') != $year";
}

/*
 * PRE-BUTTON zum Voererzeugen und versenden der Rechnungen
 */

$pre_year = date ( "Y", strtotime ( '+1 year' ) );
$str_date_for_generate_pre = "01-11";

if (strtotime ( "now" ) > strtotime ( "$str_date_for_generate_pre-$year" ) && $pre_year > $year) {
	$query = $GLOBALS ['mysqli']->query ( get_count_pre ( $pre_year, $_SESSION ['faktura_company_id'] ) ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	$count_new_bills_for_user_pre = mysqli_num_rows ( $query );
	$button_text = "Pre-Beitragszahlung erzeugen <span id=count_new_bills_for_user_pre>$count_new_bills_for_user_pre";
} else {
	$query = $GLOBALS ['mysqli']->query ( get_count_pre ( $year, $_SESSION ['faktura_company_id'] ) ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	$count_new_bills_for_user = mysqli_num_rows ( $query );
	$button_text = "Beitragszahlung erzeugen <span id=count_new_bills_for_user>$count_new_bills_for_user";
}

$arr ['top'] ['button'] [] = array ('onclick' => "call_generate_bill('all')",'id' => 'generate_bills','title' => "$button_text",'icon' => '','class' => 'green circular' );
