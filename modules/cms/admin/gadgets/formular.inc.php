<?php
include_once (__DIR__ . '/functions.php');
include_once (__DIR__ . '/../rights.inc.php');

// -----------> Allgemein
$arr ['field'] [] = array ('tab' => 'first','type' => 'accordion','title' => 'Allgemein','active' => true );
$arr ['field'] [] = array ('tab' => 'first','type' => 'div','class' => 'ui message' );
if (check_module_user ( $_SESSION ['user_id'], 'newsletter' )) {
	$query_sender = $GLOBALS ['mysqli']->query ( "SELECT id, from_email, from_name FROM {$cfg_mysql['db_nl']}.sender WHERE user_id='{$_SESSION['user_id']}' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	while ( $fetch_sender = mysqli_fetch_array ( $query_sender ) ) {
		// $fetch_from_id = $fetch_sender['id'];
		// $from_email = $fetch_sender['from_email'];
		// $from_name = $fetch_sender['from_name'];
		$array_sender [$fetch_sender ['id']] = "{$fetch_sender['from_name']} ({$fetch_sender['from_email']})";
	}
}

if (! $send_button)
	$send_button = 'Nachricht senden';

if ($set_modul ['newsletter']) {

	$arr ['field'] ['camp_key_formular'] = array ('tab' => 'first','label' => "Listbuilding (Formular) <a href='../ssi_newsletter/' target=new><i class='icon setting'></i></a>",'info' => 'Es werden alle User gespeichert in diesem Listbuilding gespeichert, welche dieses Formular ausfüllen.',
			'type' => 'dropdown','clearable' => true,'search' => true,'array' => call_array_formular ( $cfg_mysql ['db_nl'] ),'emptyfield' => '--Liste w&auml;hlen--','value' => $camp_key_formular );

	$arr ['field'] ['camp_key'] = array ('tab' => 'first','label' => "Listbuilding (Newsletter) <a href='../ssi_newsletter/' target=new><i class='icon setting'></i></a>",'info' => 'Wenn Formular auch als Newsletteranmeldung verwendet werden soll.','type' => 'dropdown','clearable' => true,
			'search' => true,'array' => call_array_formular ( $cfg_mysql ['db_nl'] ),'emptyfield' => '--Liste w&auml;hlen--','value' => $camp_key );


	// $arr['field']["no_confermation"] = array ( 'tab' => 'first' , 'label' => 'Bestätigung deaktivieren' , 'type' => 'checkbox' , 'value' => $no_confermation, 'info'=>'Eintragen in Listbuildung ohne Bestätigung vom User ausführen' );
	$arr ['field'] ['from_id'] = array ('tab' => 'first','label' => 'Absender','type' => 'dropdown','array' => $array_sender,'value' => $from_id );
}

$arr ['field'] ['receive_email'] = array ('tab' => 'first','label' => "Empfängermail",'type' => 'input','value' => "$receive_email",'info' => 'Achtung diese Mail muss von SSI verifiziert werden, da die Nachricht sonst nicht zu gestellt werden kann! Standarmässig wird der Login-User verwendet.',
		'size' => 60 );

$arr ['field'] ['subject_text'] = array ('tab' => 'first','label' => 'Betreff','type' => 'input','value' => $subject_text,'info' => 'Es können auch Platzhalter wie {%firstname%},{%secondname%},{%email%} und {%company%} werden. Diese müssen allerdings den jeweiligen Feldern zugewiesen werden.',
		'clearable' => true );

$arr ['field'] ['recaptcha'] = check_recaptcha_setting ( $recaptcha );

$arr ['field'] [] = array ('tab' => 'first','type' => 'div_close' );

// -----------> Absendebutton
$arr ['field'] [] = array ('tab' => 'first','type' => 'accordion','title' => 'Absenden','split' => true );

$arr ['field'] [] = array ('tab' => 'first','type' => 'div','class' => 'buttons_url ui message' ); // fields two
$arr ['field'] ['send_button'] = array ('tab' => 'first','label' => "Sendebutton",'type' => 'input','value' => "$send_button" );

// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div', 'class'=>'two fields');
$arr ['field'] ['button_icon'] = array ('tab' => 'first','label' => "Icon",'type' => 'icon','value' => $button_icon );
// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close');
$arr ['field'] [] = array ('tab' => 'first','type' => 'div_close' );

$arr ['field'] ['submit_text'] = array ('tab' => 'first','label' => 'Bestätigungstext nach der Versendung','type' => 'ckeditor_inline','toolbar' => 'mini','value' => $submit_text,'info' => 'Dieser Text wird nach der Versendung statt dem Forumlar eingeblendet.' );
$arr ['field'] [''] = array ('tab' => 'first','type' => 'button','class_button' => 'mini blue','value' => 'Text übernehmen','onclick' => "save_value_element('$update_id','submit_text',$('#submit_text').html(),'formular');" );
$arr ['field'] ["button_url"] = array ('tab' => 'first','label' => 'Weiterleitung','type' => 'dropdown','array' => $array_sites,'value' => $button_url,'placeholder' => 'zu Seite verlinken','info' => 'Nach Absendung wird direkt auf eine  gewählte Seite weitergeleitet' );
$arr ['field'] ["button_target"] = array ('tab' => 'first','label' => 'in neuer Seite öffnen','type' => 'checkbox','value' => $button_target );
$arr ['field'] ["button_link"] = array ('tab' => 'first','label' => '','type' => 'input','value' => $button_link,'placeholder' => 'zu externen Link' );

// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'accordion' , 'title' =>'Felder einfügen' , 'split' => true );

// $add_item_menu = "<div class='tooltip item ui vertical menu' title='Ziehe das gewünschte Feld in das Formular' >
// <div style='cursor:move' class='new_form_field item' id='input' >Input</div>
// <div style='cursor:move' class='new_form_field item' id='select' >Select</div>
// <div style='cursor:move' class='new_form_field item' id='radio' >Radio</div>
// <div style='cursor:move' class='new_form_field item' id='checkbox' >Checkbox</div>
// <div style='cursor:move' class='new_form_field item' id='textarea' >Textarea</div>
// <div style='cursor:move' class='new_form_field item' id='text' >Text</div>
// </div>";

// $arr['field'][] = array ( 'tab' => 'first' , 'label' => "Icon" , 'type' => 'content' , 'text' => $add_item_menu );

$arr ['field'] [] = array ('tab' => 'first','type' => 'accordion','close' => true );

// $add_gadgets_js .= "<script>appendScript('gadgets/formular/admin/jquery-quickedit.js');</script>";
// $add_gadgets_js .= "<script>appendScript('gadgets/formular/admin/main.js');</script>";
// $add_gadgets_js .= "<script>load_edit_formular();</script>";
