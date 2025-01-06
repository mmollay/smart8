<?php

// ang_send_mail_subject
// ang_send_mail
// ang_remind_time1

// ls_send_mail_subject
// ls_send_mail
// ls_remind_time1
$add_js .= "<script type=\"text/javascript\" charset=\"utf-8\" src=\"js/form_options.js\"></script>";

//$arr['ajax'] = array (  'success' => "$('.ui.modal').modal('hide'); table_reload();" ,  'dataType' => "html" );

$arr ['ajax'] = array ('success' => "if ( data != 'ok' && data != 'update' ) { alert( 'Fehler: ' + data ) } else { $('.ui.modal').modal('hide'); table_reload(); }",'dataType' => "html" );

$arr ['tab'] = array ('tabs' => array (1 => 'Kontaktdaten',2 => 'Bankdaten',10 => 'Texte','bausteine' => 'Bausteine',5 => 'Mailversand',6 => 'Mahnung',8 => 'Mahnung 3',4 => 'Grafiken',9 => 'SMTP' ),'active' => '1' );
$arr ['sql'] = array ('query' => "SELECT * from company WHERE company_id = '{$_POST['update_id']}'" );

$arr ['field'] [] = array ('tab' => '1','type' => 'div','class' => 'two fields' );
$arr ['field'] ['company_1'] = array ('tab' => '1','type' => 'input','label' => 'Firma','validate' => true,'focus' => true );
$arr ['field'] ['company_2'] = array ('tab' => '1','type' => 'input','label' => 'Firma(Zusatz)' );
$arr ['field'] [] = array ('tab' => '1','type' => 'div_close' );
$arr ['field'] [] = array ('tab' => '1','type' => 'div','class' => 'three fields' );
$arr ['field'] ['title'] = array ('tab' => '1','type' => 'input','label' => 'Titel' );
$arr ['field'] ['firstname'] = array ('tab' => '1','type' => 'input','label' => 'Vorname' );
$arr ['field'] ['secondname'] = array ('tab' => '1','type' => 'input','label' => 'Nachname' );
$arr ['field'] [] = array ('tab' => '1','type' => 'div_close' );
$arr ['field'] ['street'] = array ('tab' => '1','type' => 'input','label' => 'Strasse' );
$arr ['field'] [] = array ('tab' => '1','type' => 'div','class' => 'three fields' );
$arr ['field'] ['zip'] = array ('tab' => '1','type' => 'input','label' => 'PLZ' );
$arr ['field'] ['city'] = array ('tab' => '1','type' => 'input','label' => 'Stadt' );
$arr ['field'] ['country'] = array ('tab' => '1','type' => 'dropdown','array' => 'country','label' => 'Land' );
$arr ['field'] [] = array ('tab' => '1','type' => 'div_close' );
$arr ['field'] [] = array ('tab' => '1','type' => 'div','class' => 'three fields' );
$arr ['field'] ['tel'] = array ('tab' => '1','type' => 'input','label' => 'Tel' );
$arr ['field'] ['email'] = array ('tab' => '1','type' => 'input','label' => 'Email' );
$arr ['field'] ['web'] = array ('tab' => '1','type' => 'input','label' => 'Internet' );
$arr ['field'] [] = array ('tab' => '1','type' => 'div_close' );

$arr ['field'] ['of_jurisdiction'] = array ('tab' => '2','type' => 'input','label' => 'Gerichtsstand' );
$arr ['field'] ['uid'] = array ('tab' => '2','type' => 'input','label' => 'UID' );
$arr ['field'] ['company_number'] = array ('tab' => '2','type' => 'input','label' => 'Firmenbuchnummer' );
$arr ['field'] ['bank_name'] = array ('tab' => '2','type' => 'input','label' => 'Bankname' );
$arr ['field'] ['iban'] = array ('tab' => '2','type' => 'input','label' => 'IBAN' );
$arr ['field'] ['bic'] = array ('tab' => '2','type' => 'input','label' => 'BIC' );
$arr ['field'] ['zvr'] = array ('tab' => '2','type' => 'input','label' => 'ZVR Zahl' );

$bill_number = mysql_singleoutput ( "SELECT MAX(bill_number) as bill_number FROM bills WHERE company_id = '{$_POST['update_id']}' ", "bill_number" ) + 1;

/**
 * **********************
 * Accordion beginnt hier
 * **********************
 */
$arr ['field'] [] = array ('tab' => 'bausteine','type' => 'div','class' => 'ui styled fluid accordion' );

//---->Rechnung
$arr ['field'] [] = array ('tab' => 'bausteine','type' => 'div','class' => 'title active','text' => "<i class='icon dropdown'></i>Rechnung</div>" );

$arr ['field'] [] = array ('tab' => 'bausteine','type' => 'div','class' => 'content active' );
//Content - Begin
$arr ['field'] ['headline'] = array ('tab' => 'bausteine','type' => 'input','label' => 'Titel','placeholder' => 'Rechnung' );
// $arr['field']['subject'] = array ( 'tab' => 'bausteine', 'type' => 'input' , 'label' => 'Betreffzeile' );
// $arr['field']['default_bill_number'] = array ( 'tab' => 'bausteine', 'type' => 'input' , 'label' => 'Rechnungsnummer' , 'value' => $bill_number );
$arr ['field'] ['conditions'] = array ('tab' => 'bausteine','type' => 'textarea','label' => 'Text nach Rechnung' );

$arr ['field'] ['content_footer'] = array ('tab' => 'bausteine','type' => 'textarea','label' => 'Fußzeile' );

//Content - Ende
$arr ['field'] [] = array ('tab' => 'bausteine','type' => 'div_close' );

//---->Angebot
$arr ['field'] [] = array ('tab' => 'bausteine','type' => 'div','class' => 'title','text' => "<i class='icon dropdown'></i>Angebot</div>" );
$arr ['field'] [] = array ('tab' => 'bausteine','type' => 'div','class' => 'content ' );
//Content - Begin
$arr ['field'] ['ang_headline'] = array ('tab' => 'bausteine','type' => 'input','label' => 'Titel','placeholder' => 'Anbot' );
// $arr['field']['ang_subject'] = array ( 'tab' => 'bausteine', 'type' => 'input' , 'label' => 'Betreffzeile' );
$arr ['field'] ['ang_conditions'] = array ('tab' => 'bausteine','type' => 'textarea','label' => 'Text nach Anbot' );
//Content - Ende
$arr ['field'] [] = array ('tab' => 'bausteine','type' => 'div_close' );

$arr ['field'] [] = array ('tab' => 'bausteine','type' => 'div_close' );
/**
 * **********************
 * Accordion endet hier
 * **********************
 */
$arr ['field'] [] = array ('tab' => 'bausteine','type' => 'content','text' => $info_text );

if ($_SERVER ['SERVER_NAME'] == 'localhost') {
	$upload_dir = "/var/www/ssi/smart_users/{$_SESSION['company']}/user{$_SESSION['user_id']}/faktura/";
	$updoad_url = "/smart_users/{$_SESSION['company']}/user{$_SESSION['user_id']}/faktura/";
	$server_name = '';
} else {
	$upload_dir = "/var/www/ssi/smart_users/{$_SESSION['company']}/user{$_SESSION['user_id']}/faktura/";
	$updoad_url = "/smart_users/{$_SESSION['company']}/user{$_SESSION['user_id']}/faktura/";
	// $server_name = 'https://center.ssi.at';
}

// Odner anlegen fuer Bilder fuer Rechnung
$config_faktur_grafics = "../../smart_users/{$_SESSION['company']}/user{$_SESSION['user_id']}/faktura/";
$config_faktur_grafics_dir = "../../../smart_users/{$_SESSION['company']}/user{$_SESSION['user_id']}/faktura/";
$company_dir = $_POST ['update_id'];

// Anlegen wenn nicht vorhanden
exec ( "mkdir $config_faktur_grafics_dir" );
exec ( "mkdir $config_faktur_grafics_dir$company_dir" );

// $grafic_head = mysql_singleoutput ( "SELECT * from company WHERE company_id = '{$_POST['update_id']}' ", "grafic_head" );
// echo $grafic_head;

$info_text = nl2br ( "
		[%bill_number%]...Rechnungsnummer
		[%gender%]........Anrede  Bsp.: Sehr geehrte(r)
		[%firstname%].....Vorname
		[%secondname%]....Nachname
		[%date%]..........Erstelldatum
		[%title%].........Titel
		[%summery%].......Summe der Rechnung
		" );


echo "$upload_dir$company_dir";

$arr ['field'] ['grafic_head'] = array ('tab' => '4','label' => 'PDF-Kopf','server_name' => $server_name,'mode' => 'single','type' => 'uploader','upload_dir' => "$upload_dir$company_dir/",'upload_url' => "$updoad_url$company_dir/",'validate' => 'Bitte ein Bild hochladen',
		'ajax_success' => "$('#key').focus();",
		// 'options' => 'imageMaxWidth:1000,imageMaxHeight:1000' ,
		'dropzone' => array ('style' => 'padding-top:25px; padding-bottom:25px;' ),'card_class' => 'four' );

/**
 * **********************
 * Accordion beginnt hier
 * **********************
 */
$arr ['field'] [] = array ('tab' => '5','type' => 'div','class' => 'ui styled fluid accordion' );

//---->Rechnung
$arr ['field'] [] = array ('tab' => '5','type' => 'div','class' => 'title active','text' => "<i class='icon dropdown'></i>Rechnung</div>" );
//Content - Begin
$arr ['field'] [] = array ('tab' => '5','type' => 'div','class' => 'content active' );
$arr ['field'] ['rn_send_mail_subject'] = array ('tab' => '5','type' => 'input','label' => 'Betreff' );
$arr ['field'] ['rn_send_mail'] = array ('tab' => '5','type' => 'textarea','label' => 'Text' );
$arr ['field'] ['remind_time1'] = array ('tab' => '5','type' => 'input','label' => 'Mahnen nach','label_right' => 'Tagen' );
//Content - Ende
$arr ['field'] [] = array ('tab' => '5','type' => 'div_close' );

//---->Angebot
$arr ['field'] [] = array ('tab' => '5','type' => 'div','class' => 'title','text' => "<i class='icon dropdown'></i>Angebot</div>" );
//Content - Begin
$arr ['field'] [] = array ('tab' => '5','type' => 'div','class' => 'content ' );
$arr ['field'] ['ang_send_mail_subject'] = array ('tab' => '5','type' => 'input','label' => 'Betreff' );
$arr ['field'] ['ang_send_mail'] = array ('tab' => '5','type' => 'textarea','label' => 'Text' );
$arr ['field'] ['ang_remind_time1'] = array ('tab' => '5','type' => 'input','label' => 'Erinnern nach','label_right' => 'Tagen' );
//Content - Ende
$arr ['field'] [] = array ('tab' => '5','type' => 'div_close' );

$arr ['field'] [] = array ('tab' => '5','type' => 'div_close' );
/**
 * **********************
 * Accordion endet hier
 * **********************
 */
$arr ['field'] [] = array ('tab' => '5','type' => 'content','text' => $info_text );

//MAHNEN
/**
 * **********************
 * Accordion beginnt hier
 * **********************
 */
$arr ['field'] [] = array ('tab' => '6','type' => 'div','class' => 'ui styled fluid accordion' );

//---->Mahnen 1
$arr ['field'] [] = array ('tab' => '6','type' => 'div','class' => 'title active','text' => "<i class='icon dropdown'></i>Mahnen 1</div>" );
$arr ['field'] [] = array ('tab' => '6','type' => 'div','class' => 'content active' );
//Content - Begin
$arr ['field'] ['remind_mail_subject1'] = array ('tab' => '6','type' => 'input','label' => 'Betreff' );
$arr ['field'] ['remind_mail1'] = array ('tab' => '6','type' => 'textarea','label' => 'Text' );
$arr ['field'] ['remind_time2'] = array ('tab' => '6','type' => 'input','label' => 'Mahnen nach','label_right' => 'Tagen' );
//Content - Ende
$arr ['field'] [] = array ('tab' => '6','type' => 'div_close' );

//---->Mahnen 2
$arr ['field'] [] = array ('tab' => '6','type' => 'div','class' => 'title','text' => "<i class='icon dropdown'></i>Mahnen 2</div>" );
$arr ['field'] [] = array ('tab' => '6','type' => 'div','class' => 'content' );
//Content - Begin
$arr ['field'] ['remind_mail_subject2'] = array ('tab' => '6','type' => 'input','label' => 'Betreff' );
$arr ['field'] ['remind_mail2'] = array ('tab' => '6','type' => 'textarea','label' => 'Text' );
$arr ['field'] ['remind_time3'] = array ('tab' => '6','type' => 'input','label' => 'Mahnen nach','label_right' => 'Tagen' );
//Content - Ende
$arr ['field'] [] = array ('tab' => '6','type' => 'div_close' );

//---->Mahnen 3
$arr ['field'] [] = array ('tab' => '6','type' => 'div','class' => 'title','text' => "<i class='icon dropdown'></i>Mahnen 3</div>" );
$arr ['field'] [] = array ('tab' => '6','type' => 'div','class' => 'content ' );
//Content - Begin
$arr ['field'] ['remind_mail_subject3'] = array ('tab' => '6','type' => 'input','label' => 'Betreff' );
$arr ['field'] ['remind_mail3'] = array ('tab' => '6','type' => 'textarea','label' => 'Text' );
$arr ['field'] ['remind_time4'] = array ('tab' => '6','type' => 'input','label' => 'Mahnen nach','label_right' => 'Tagen' );
//Content - Ende
$arr ['field'] [] = array ('tab' => '6','type' => 'div_close' );

/**
 * **********************
 * Accordion endet hier
 * **********************
 */
$arr ['field'] [] = array ('tab' => '6','type' => 'div_close' );
$arr ['field'] [] = array ('tab' => '6','type' => 'content','text' => $info_text );

$arr ['field'] [] = array ('tab' => '9','type' => 'div','class' => 'two fields' );
$arr ['field'] ['smtp_email'] = array ('tab' => '9','label' => 'Von','type' => 'input','info' => 'Email des Absenders','placeholder' => 'info@example.com','validate' => true );
$arr ['field'] ['smtp_title'] = array ('tab' => '9','label' => 'Bezeichnung','type' => 'input','info' => 'Bsp.: Max Muster','validate' => true );
$arr ['field'] [] = array ('tab' => '9','type' => 'div_close' );
$arr ['field'] ['smtp_return'] = array ('tab' => '9','label' => 'Return-Email','type' => 'input','info' => 'Mails welche nicht zugestellt werden können' );
$arr ['field'] [''] = array ('tab' => '9','type' => 'header','text' => 'SMTP-Server (Optional)','class' => 'small dividing','info' => 'Wenn Sie über einen eigenen SMTP - Server verfügen, können Sie diesen hier eintragen.' );
$arr ['field'] [] = array ('tab' => '9','type' => 'div','class' => 'fields' );
$arr ['field'] ['smtp_port'] = array ('tab' => '9','label' => 'Port','type' => 'dropdown','array' => array (587 => 587,465 => 465,25 => 25 ),'placeholder' => 'Port' );
$arr ['field'] ['smtp_server'] = array ('tab' => '9','label' => 'Server','type' => 'input' );
$arr ['field'] ['smtp_user'] = array ('tab' => '9','label' => 'User','type' => 'input' );
$arr ['field'] ['smtp_password'] = array ('tab' => '9','label' => 'Passwort','type' => 'password' );
$arr ['field'] ['smtp_secure'] = array ('tab' => '9','label' => 'Secure','type' => 'dropdown','array' => array ('' => 'keine','tls' => 'tls','ssl' => 'ssl' ) );
$arr ['field'] [] = array ('tab' => '9','label' => true,'type' => 'content','text' => "<div class='button ui icon' id=check_smtp><div id='show_smtp_ok'>Überprüfen</div></div>" );
$arr ['field'] [] = array ('tab' => '9','type' => 'div_close' );
