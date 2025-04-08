<?php
// echo $_POST['firstname'];
// echo $_POST['secondname'];
// echo $_POST['verify_text'];

//OR
//preparation for Database injection
foreach ( $_POST as $key => $value ) {
	//$GLOBALS[$key] = $GLOBALS['mysqli']->real_escape_string ( $value );
	$GLOBALS[$key] = $value ;
	echo "$key = $value<br>";
}

//Multi - Array "," gentrennt
//$_POST['groups'] = explode(',',$_POST['groups']);

//echo "alert('Erfolgreich- $firstname')";

//Wenn dataType : script gewählt wurde
//echo "$('#form_message').html(\"<div class='ui green message'><i class='close icon'></i><div id='form_message_info' class='header'>Daten wurden gespeichert</div>\");";

//Wenn dataType : html gewählt wuerde
echo "$email erfolgreich angemeldet";