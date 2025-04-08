<?php 
$seite_id = $GLOBALS['seite_id'];
$set_user_id = $GLOBALS['set_user_id'];

// Falls template im Ajax-bereich geladen wird
if ($GLOBALS['set_ajax'])
	$path = "../../";
	// Splitten und Nummer übergeben falls vorhanden ist
	$matches = preg_split ( "/#/", $array[1] );
	
	switch ($matches[0]) {
		
		case 'income_calculator' :
			include ($path . 'gadgets/income_calculator/include.inc.php');
			break;
		case 'date' :
			date_default_timezone_set ( 'Europe/Berlin' );
			return date ( 'd.m.Y' );
			break;
		case 'wtm' :
			$_SESSION['group_id'] = $group_id = $matches[1];
			include_once ($path . 'gadgets/wtm/include.inc.php');
			return $output;
			break;
		default :
			if (is_file ( $path . 'gadgets/' . $matches[0] . '/include.inc.php' ))
				include ($path . 'gadgets/' . $matches[0] . '/include.inc.php');
				return $output;
	}
?>