<?php
// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'header' , 'text' => '<br>Erweiterte Einstellungen' , 'size' => '3' , 'class' => 'dividing' );
// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'inline fields' );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'ui message' );
$arr['field']['dynamic_name'] = array ( 'tab' => 'first' , 'label' => "Interne Kennung" , 'type' => 'input' , 'value' => $dynamic_name , 'info' => 'Kennung des Feldes, wenn diese auch auf einer andere Seite eingebunden werden soll.' );
$arr['field']['dynamic_modus'] = array ( 'tab' => 'first' , 'type' => 'toggle' , 'label' => 'dynamisieren' , 'value' => $dynamic_modus , "info" => "Aktiviere diese Funktion, wenn dieses Textfeld auch auf einer anderen Seite auch eingebunden werden soll." );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );

$arr['field']['info'] = array ( 'tab' => 'thi' ,
		'type' => 'content' ,
		'message' => 'info' ,
		"text" => "
		<b>Folgende Parameter können in den dynamischen Elementen verwendet werden:</b><br>
		<table border=0>
		<tr><td>Name</td><td>&nbsp; {%client_name%}</td></tr>
		<tr><td>Vorname</td><td>&nbsp; {%client_firstname%}</td></tr>
		<tr><td>Nachname</td><td>&nbsp; {%client_secondname%}</td></tr>
		<tr><td>Email</td><td>&nbsp; {%client_email%}</td></tr>
		<tr><td>Verify_Key</td><td>&nbsp; {%client_verify_key%}</td></tr>
		<tr><td>Token</td><td>&nbsp; {%client_token%}</td></tr>
		</table>" );