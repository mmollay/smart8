<div id='content_bills'></div>

<script>
	$(document).ready(function () {
		loadListGenerator('lists/bills.php', { saveState: false, contentId: 'content_bills', });
	});
</script>

<?

return;
exit;


include(__DIR__ . '/../../../../smartform/include_list.php');

// echo $_POST['company_id'];

// Wird für das Aufrufen der Details für den Kontenplan benötigt
if (isset($_POST['list_filter'])) {
	$_SESSION["filter"]['bill_list']['account'] = $_POST['list_filter'];
	$_SESSION["filter"]['bill_list']['company_id'] = 'all';
	$_SESSION["filter"]['bill_list']['select_id'] = 'date_storno = "0000-00-00" ';
	$_SESSION["filter"]['bill_list']['SetYear'] = 'DATE_FORMAT(date_create,"%Y") = "' . $_SESSION['SetYear_finance'] . '"';
}

// Wenn von der Startseite auf Mahnen anzeigen geklickt wird
if (isset($_POST['set_filter_remind'])) {
	$_SESSION["filter"]['bill_list']['company_id'] = 'all';
	$_SESSION["filter"]['bill_list']['account'] = 'all';
	$_SESSION["filter"]['bill_list']['select_id'] = 'date_remind < NOW() and date_booking="0000-00-00" and remind_level !=0
	and date_storno="0000-00-00"';
}

$array = call_list(' ../list/earnings.php', '../f_config.php', array('document' => 'rn'));

// $content .= "
// <div class='ui modal modal-sendbill'>
// <i class='close icon'></i>
// <div class='header'>Rechnung(en) versenden</div>
// <div class='content'></div>
// </div>";

$array_json['html'] .= $array['html'];
$array_json['html'] .= $array['js'];
$array_json['html'] .= "
	<script>
		var company_id = '{$_SESSION['faktura_company_id']}';
		var add_bill = '{$_GET['add_bill']}';
	</script>
	<script type=\"text/javascript\" src=\"js/list_bill.js\"></script>
	<script type=\"text/javascript\" src=\"js/form_bill.js\"></script>
	";

echo $array_json['html'];