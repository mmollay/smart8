<?
// Filter zurück setzen beim neuladen der der Tabelle
include ('../config.inc.php');

$_SESSION ['filter_table'] = '';
$_SESSION ['list_search'] = '';
$_SESSION ['list_filter'] = '';
$_SESSION ['filter_section'] = '';
$_SESSION ['filter_membership'] = '';

// Billgenerator just for User : 94 (OEGT)
if ($_SESSION ['user_id'] == '94' and $_SESSION ['company_id'] == '30') {

	/*
	 * PRE-BUTTON zum Voererzeugen und versenden der Rechnungen
	 */

	$pre_year = date ( "Y", strtotime ( '+1 year' ) );

	if (strtotime ( "now" ) > strtotime ( "$str_date_for_generate_pre-$year" ) && $pre_year > $year) {
		$query = $GLOBALS ['mysqli']->query ( get_count_pre ( $pre_year, $_SESSION ['faktura_company_id'] ) ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
		$count_new_bills_for_user_pre = mysqli_num_rows ( $query );
		$content2 .= "<a href='#' id=generate_bills_pre>[ Pre-Beitragszahlung erzeugen <span id=count_new_bills_for_user_pre>$count_new_bills_for_user_pre</span> ]</a>";
	} else {
		$query = $GLOBALS ['mysqli']->query ( get_count_pre ( $year, $_SESSION ['faktura_company_id'] ) ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
		$count_new_bills_for_user = mysqli_num_rows ( $query );
		$content2 .= "<a href='#' id=generate_bills>[ Beitragszahlung erzeugen <span id=count_new_bills_for_user>$count_new_bills_for_user</span> ]</a>";
	}

	// FILTER - sectionen
	$option = '';
	$query = $GLOBALS ['mysqli']->query ( " SELECT * from article_temp where account=63" );
	while ( $array1 = mysqli_fetch_array ( $query ) ) {
		$id = $array1 ['temp_id'];
		$array [$id] = $array1 ['art_title'];
		$select_section [$_SESSION ['filter_section']] = 'selected';
		$option .= "<option $select_section[$id] value=$id>" . $array1 ['art_title'] . "</option>";
	}
	$select_filter_section = '<select name=filter_section id = "filter_section" ><option value="">--Sections--</option>' . $option . '</select>';

	// FIlTER - membership
	$option = '';
	$query = $GLOBALS ['mysqli']->query ( " SELECT * from article_temp where account=62" );
	while ( $array1 = mysqli_fetch_array ( $query ) ) {
		$id = $array1 ['temp_id'];
		$array [$id] = $array1 ['art_title'];
		$select_membership [$_SESSION ['filter_membership']] = 'selected';
		$option .= "<option $select_membership[$id] value=$id>" . $array1 ['art_title'] . "</option>";
	}
	$select_filter_section .= '<select name=filter_membership id = "filter_membership" ><option value="">--Membership--</option>' . $option . '</select>';

	$style_link = "oegt/list_client.js";
} else {
	$style_link = "js/list_client.js";
}

$content .= "
<script>
var company_id = {$_SESSION['faktura_company_id']};
</script>
<script type=\"text/javascript\" src=\"$style_link\"></script>";

$content .= "<button id=add_client>$strButtunNewClient</button><br>";

//<option value=3 {$selectet[3]}>Inaktive Kunden</option>

$content .= "<hr>
Filter:
<form id=submit>
<select id=list_filter name=list_filter>
<option value=1 {$selectet[1]}>Alle Kunden</option>
<option value=2 {$selectet[2]}>Aktive Kunden</option>
<option value=4 {$selectet[4]}>Kunden mit Umsatz</option>
<option value=5 {$selectet[5]}>Kunden mit offenen Umsatz</option>
<option value=6 {$selectet[6]}>Kunden mit Newsletter (Aktiv)</option>
</select>
$select_filter_section
<input type=text id=list_search>
<input type='submit' value=Filtern>
</form>
";
$content .= "<div align=right style='position:relative; right:15px;'>";
$content .= " <a href='plugin/export.php?file=client'>[ Export ]</a>";
$content .= "$content2";

$content .= "</div>";
$content .= "<div id=window></div><div id=window_progress></div><div id=list></div>";
echo $content;
function get_count_pre($year, $company_id) {
	return "SELECT * FROM client LEFT JOIN membership
		ON client.client_id = membership.client_id
		WHERE DATE_FORMAT(date_membership_start,'%Y') <= '$year'
		AND (DATE_FORMAT(date_membership_stop,'%Y') = '0000' OR DATE_FORMAT(date_membership_stop,'%Y') >= '$year')
		AND company_id='$company_id'
		AND DATE_FORMAT(send_date,'%Y') != $year";
}
