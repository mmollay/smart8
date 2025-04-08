<?
include_once ('../../../login/config_main.inc.php');
include ('../../../ssi_form2/ssiForm.inc.php');

$GLOBALS['mysqli']->query ("UPDATE smart_page  SET set_public =  1, set_public_timestamp = NOW() WHERE page_id = '{$_SESSION['smart_page_id']}' ");


echo "<div align =center>";
// Form erzeugen
echo nl2br ( "
		<div style='font-size:20px'>Veröffentlichung beantragt!</div>
		  <div class='column'><i class='wait huge icon'></i></div>
		In der Regel erfolgt die Freigabe in den nächsten 24 Stunden.
		" );
echo "</div>";
?>