<?php
/*
 * Form zum erzeugen eine Webseite
 */
session_start ();
include ('../../../ssi_form2/ssiForm.inc.php');
include_once ('../../login/mysql.inc.php');
include_once ('../../login/config_main.inc.php');

// Form erzeugen
$form = new ssiForm ( "form_template", "admin/ajax/form_template2.php", "ProzessBar" );

$form->setConfig ( "URI", "../ssi_form2" );
$form->setConfig ( "load_jquery_ui", FALSE );
$form->setConfig ( "load_jquery_plugins", FALSE );
$form->setConfig ( "progess", "$('#form_template').html('<div align=center>..in Prozess</div>');" );
$form->setConfig ( "beforeSubmit", "" );
$form->setConfig ( "success", "if ( $('#ProzessBar').val() =='ok' )  {}" );

// Darstellung der Eingabefelder
$form->setField ( 'title', array (
		'text' => 'Titel' 
) );
$form->setField ( 'template_title', array (
		'type' => 'input',
		required => true,
		'required_text' => 'Titel fehlt',
		'size' => '30',
		 'focus' => true,
		'size' => 60 
) );
$form->setField ( 'title', array (
		'text' => 'Beschreibung' 
) );
$form->setField ( 'template_text', array (
		'type' => 'textarea',
		rows => 8,
		textlimit => '250' 
) );

echo $form->getJs ();
echo "<script type=\"text/javascript\">
		$(document).ready(function(){});
</script>";
echo $form->getHtml ();
?>