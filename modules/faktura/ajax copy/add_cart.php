<?php
/*
 * Cart in SESSION SPEICHERN
 */
error_reporting(E_ALL ^ E_NOTICE);
session_start();

$module = $_SESSION['set_faktura_module'];
if (!$module)
    $module = 'bill';

function number_mysql2german($wert)
{
    $euro = sprintf("%01.2f", $wert);
    return "€ " . number_format($euro, 2, ',', '.');
}

if ($_POST['rm_article']) {
    unset($_SESSION['temp_cart'][$_POST['rm_article']]);
}

// Wenn Ein neuer Artikel aufgenommen wird
if ($_POST['add_article']) {

    if ($_POST['update_temp'])
        $set_key = $_POST['update_temp'];
    elseif (!is_array($_SESSION['temp_cart']) or !array_keys($_SESSION['temp_cart']))
        $set_key = 1;
    else
        $set_key = max(array_keys($_SESSION['temp_cart'])) + 1;

    $company_id = $_SESSION['company_id'];

    $temp_id = $_POST['temp_id'];
    $format = $_POST['format'];
    $count = $_POST['count'];
    $art_nr = $_POST['art_nr'];
    $art_title = $_POST['art_title'];
    $art_text = $_POST['art_text'];
    $account = $_POST['account'];
    $netto = preg_replace("/,/", ".", $_POST['netto']);

    $_SESSION['temp_cart'][$set_key]['temp_id'] = $temp_id;
    $_SESSION['temp_cart'][$set_key]['format'] = $format;
    $_SESSION['temp_cart'][$set_key]['count'] = $count;
    $_SESSION['temp_cart'][$set_key]['art_nr'] = $art_nr;
    $_SESSION['temp_cart'][$set_key]['art_title'] = $art_title;
    $_SESSION['temp_cart'][$set_key]['art_text'] = $art_text;
    $_SESSION['temp_cart'][$set_key]['account'] = $account;
    $_SESSION['temp_cart'][$set_key]['netto'] = $netto;
}

/*
 * Auslesen aktueller Daten aus der Datenbank bei Update
 */
if ($_POST['update_id']) {

    require ("../config.inc.php");
    // Auslesen der Details
    $sql_details = $GLOBALS['mysqli']->query("SELECT * from bill_details WHERE bill_id = {$_POST['update_id']} ") or die(mysqli_error($GLOBALS['mysqli']));
    while ($array_details = mysqli_fetch_array($sql_details)) {
        $id = $array_details['detail_id'];

        // Set new Year
        if ($_POST['clone']) {
            $call_year = date('Y');
            $call_year_last = $call_year - 1;
            $call_last_next = $call_year + 1;
            $array_details['art_text'] = preg_replace("/$call_year/", "$call_last_next", $array_details['art_text']);
            $array_details['art_text'] = preg_replace("/$call_year_last/", "$call_year", $array_details['art_text']);
        }

        $_SESSION['temp_cart'][$id]['temp_id'] = $array_details['temp_id'];
        $_SESSION['temp_cart'][$id]['format'] = $array_details['format'];
        $_SESSION['temp_cart'][$id]['count'] = $array_details['count'];
        $_SESSION['temp_cart'][$id]['art_nr'] = $array_details['art_nr'];
        $_SESSION['temp_cart'][$id]['art_title'] = $array_details['art_title'];
        $_SESSION['temp_cart'][$id]['art_text'] = $array_details['art_text'];
        $_SESSION['temp_cart'][$id]['account'] = $array_details['account'];
        $_SESSION['temp_cart'][$id]['netto'] = $array_details['netto'];
    }
}

if (!$_SESSION['temp_cart']) {
    echo "<div class='message huge ui'><br>Kein Artikel vorhanden<br><br></div>";
    return;
}

// Darstellung erzeugen
foreach ($_SESSION['temp_cart'] as $key => $value) {
    $iii++;
    $nr = $_SESSION['temp_cart'][$key]['art_nr'];
    $title = $_SESSION['temp_cart'][$key]['art_title'];
    $text = $_SESSION['temp_cart'][$key]['art_text'];
    $count = $_SESSION['temp_cart'][$key]['count'];
    $account = $_SESSION['temp_cart'][$key]['account'];
    $netto = $_SESSION['temp_cart'][$key]['netto'];
    $format = $_SESSION['temp_cart'][$key]['format'];
    $sum = $netto * $count;
    $sum_total += $sum;

    $buttons = "
	<div data-tooltip='Artikel entfernen' class='tooltip ui button mini icon red' onclick=fu_del_temp_article('$key')><i class='icon delete'></i></div>
	<div data-tooltip='Artikel bearbeiten' class='tooltip ui button mini icon blue' onclick=fu_edit_temp_article('$key')><i class='icon edit'></i></div>
	";
    $list .= "
	<tr>
	<td align=right>$iii<br></td>
	<td>$nr</td>
	<td><div align=right>$count x $format</div></td>
	<td><div align=right>" . number_mysql2german($netto) . "</div></td>
			<td><div align=right>" . number_mysql2german($sum) . "</div></td>
			</tr>
			<tr><td >$buttons</td><td colspan=4 align=left >$title<br>" . nl2br($text) . "</td></tr>
	";
}

echo "
<table class='ui large celled table' >

<thead>
<tr><th>Pos.</th><th>Artikel-Nr.<th>Menge</th><th>Preis</th><th>Gesamt</th></tr>
</thead>
<tbody>$list
<tr><td colspan=5><div align=right>Gesamt <b>" . number_mysql2german($sum_total) . "</b></div></td></tr>
</tbody>
</table>";

?>