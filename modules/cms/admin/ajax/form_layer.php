<?php
include_once ('../../../login/config_main.inc.php');
include ('../../../ssi_form2/ssiForm.inc.php');

$layer_id = $_POST ['update_id'];

/*
 * Einstellungen für den Layer
 */
// Form erzeugen
$form3 = new ssiForm ( "form", "admin/ajax/form_layer2.php", "ProzessBar" );
$form3->setConfig ( "URI", "../ssi_form2" );
$form3->setConfig ( "load_jquery_ui", FALSE );
$form3->setConfig ( "load_jquery_plugins", FALSE );

if ($layer_id) {
	$form3->setConfig ( "load_data", "SELECT matchcode as layer_matchcode,layer_fixed,  smart_id_layer2id_site.layer_id as layer_allocation FROM smart_layer LEFT JOIN smart_id_layer2id_site ON  smart_layer.layer_id = smart_id_layer2id_site.layer_id WHERE smart_layer.layer_id = '$layer_id'" );
}

// if (!$check_site) $check_site = 'all';
// $arry_layer_site = array($layer_id=>'Gültig für die aktuelle Seite','all'=>'Für alle Seiten gültig');
// $form3->setVar('array_layer_site',$arry_layer_site);

// js after Save - Form
$form3->setConfig ( "success", "
if ( $('#ProzessBar').val()) {
	
	content = $('#ProzessBar').val();
 
	if (content == 'update') {
		$('#ProzessBarBox').message({ status:'info', title: 'Layeroptionen gespeichert' });
	}
	else {
		$('#new_layer').fadeIn().append(content);	
		
		// Layer veränderbar machen in Groesse und Position
		SetNewLayer();
	
	
		//Messageausgabe
		$('#ProzessBarBox').message({ status:'info', title: 'Neuer Layer wurde erzeugt!' });
	}
}
else {
	$('#ProzessBarBox').message({ title: 'Layer konnte nicht angelegt werden' });
}
" );

// Darstellung der Eingabefelder
$form3->setField ( 'layer_id', "type=>hidden##value=>$layer_id" );
$form3->setField ( 'layer_matchcode', array (
		"text" => Kennung,
		'type' => 'input',
		required => TRUE,
		'size' => 30,
		 'focus' => true 
) );

// $form3->setField('line1','text=><hr>');

$form3->setField ( "layer_allocation", "type=>checkbox##title=> Nur auf aktueller Seite anzeigen" );
//$form3->setField('layer_fixed', "group=>1##title=>Layer fixieren##type=>checkbox");

echo $form3->getJs ();
echo $form3->getHtml ();
?>