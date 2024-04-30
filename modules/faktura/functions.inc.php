<?php
/*
 * verbindet sich mit der Datenbank und schliesst die Session wieder
 */
function runSQL($rsql)
{
	$GLOBALS['mysqli'] = $GLOBALS['mysqli'];
	$result = $GLOBALS['mysqli']->query($rsql) or die($rsql);
	return $result;
	$GLOBALS['mysqli']->close($connect);
}
function number($wert)
{ // $euro ->
	if ($wert < 0)
		$color = "red";
	else
		$color = '';
	$euro = sprintf("%01.2f", $wert);
	$euro = number_format($euro, 2, ',', '.');
	// $euro = number_format ($euro,2,'.',',');
	return "<span class='ui text $color'>$euro</span> &nbsp;";
}

/**
 * date_german2mysql
 * wandelt ein traditionelles deutsches Datum
 * nach MySQL (ISO-Date).
 */
function date_german2mysql($datum)
{
	list($tag, $monat, $jahr) = explode(".", $datum);

	return sprintf("%04d-%02d-%02d", $jahr, $monat, $tag);
}

/**
 * date_mysql2german
 * wandelt ein MySQL-DATE (ISO-Date)
 * in ein traditionelles deutsches Datum um.
 */
function date_mysql2german($datum)
{
	list($jahr, $monat, $tag) = explode("-", $datum);

	return sprintf("%02d.%02d.%04d", $tag, $monat, $jahr);
}
function nr_format($wert)
{
	if ($wert) {
		$wert = number_format($wert, 2, '.', '');
		return preg_replace("/\./", ',', $wert);
	}
}

// Add in Logfile
function logfile($info, $message, $modul = false, $client_id = false, $bill_id = false, $status = false, $MessageID = false)
{
	$GLOBALS['mysqli']->query("INSERT INTO logfile SET
	user_id    = '{$_SESSION['user_id']}',
	client_id  = '$client_id',
	bill_id    = '$bill_id',
	remote_ip  = '{$_SERVER['REMOTE_ADDR']}',
	modul      = '$modul',
	info       = '$info',
    MessageID  = '$MessageID',
	status     = '$status',
	message   = '$message'
	") or die(mysqli_error($GLOBALS['mysqli']));
}


function searchForId($id, $array)
{
	foreach ($array as $key => $val) {
		if ($val['name'] === $id) {
			return $val['account_id'];
		}
	}
	return 0;
}

?>