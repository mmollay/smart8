<?php
include (__DIR__ . '/../f_config.php');
$search_text = $GLOBALS['mysqli']->real_escape_string($_POST['search_text']);
$bill_id = $GLOBALS['mysqli']->real_escape_string($_POST['bill_id']);

if ($bill_id) {
	$query = $GLOBALS['mysqli']->query("SELECT * FROM issues WHERE bill_id = '$bill_id'") or die(mysqli_error($GLOBALS['mysqli']));
}
if ($search_text) {
	$query = $GLOBALS['mysqli']->query("SELECT * FROM issues WHERE description LIKE '$search_text%'  order by bill_id desc") or die(mysqli_error($GLOBALS['mysqli']));
}
if ($bill_id or $search_text) {
	$array = mysqli_fetch_array($query);
	$array['brutto'] = number_format($array['brutto'], 2, ',', '');

	if ($array['account']) {
		echo "if (!$('#dropdown_account').val() ) { $('#dropdown_account').dropdown('set selected','{$array['account']}'); $('#description').val('{$array['description']}') };";
		echo "if (!$('#dropdown_client_id').val() ) $('#dropdown_client_id').dropdown('set selected','{$array['client_id']}');";
		echo "if (!$('#brutto').val() ) $('#brutto').val('{$array['brutto']}');";
	}
}
// echo $array ['account'];
