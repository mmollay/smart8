<?php
/*
 * Anzeige des aktuellen Standes der Finanzen (offene Posten)
 */
$iii = 0;
$counter_sum = 0;
$price_sum = 0;
$counter_inkasso_sum = 0;
$info_list = '';
$mahnen = false;
$button = '';

$while_comp = $GLOBALS ['mysqli']->query ( "SELECT * from company" ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
while ( $array_comp = mysqli_fetch_array ( $while_comp ) ) {
	$iii ++;
	$company_id = $array_comp ['company_id'];
	$company_name = $array_comp ['company_1'];

	// Summe Brutto (mahnen)
	$mysql_query = $GLOBALS ['mysqli']->query ( "SELECT COUNT('bill_id') counter, SUM(brutto) brutto from bills WHERE 1 and date_remind < NOW() and date_booking = '0000-00-00' and remind_level != 0 and date_storno = '0000-00-00' AND company_id = '$company_id' AND document = 'rn' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	$array = mysqli_fetch_array ( $mysql_query );
	$price [$company_id] = $array ['brutto'];
	$counter [$company_id] = $array ['counter'];
	$counter_sum += $array ['counter'];

	// Summe Brutto (allgemein)
	$mysql_query = $GLOBALS ['mysqli']->query ( "SELECT SUM(brutto) brutto from bills WHERE 1 and date_booking = '0000-00-00' and date_storno = '0000-00-00' AND company_id = '$company_id' AND document = 'rn' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	$array = mysqli_fetch_array ( $mysql_query );
	$price_samt [$company_id] = $array ['brutto'];
	$price_sum += $array ['brutto'];

	// Inkasso
	$mysql_query = $GLOBALS ['mysqli']->query ( "SELECT COUNT('bill_id') counter_inkasso, SUM(brutto) brutto from bills WHERE 1 and date_booking = '0000-00-00' and date_remind < NOW() and remind_level = 4 and date_storno = '0000-00-00' AND company_id = '$company_id' AND document = 'rn' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	$array = mysqli_fetch_array ( $mysql_query );
	$price_samt_inkasso [$company_id] = $array ['brutto'];
	$counter_inkasso [$company_id] = $array ['counter_inkasso'];
	$counter_inkasso_sum += $array ['counter_inkasso'];

	// if ($counter[$company_id]) $todo_list .= "<li> Auf <b>$company_name</b> sind <i><b>".$counter[$company_id]."</i></b> zu mahnende Kunden <a href=list_bill.php class=button>Jetzt mahnen</a></li>";
	if ($counter [$company_id]) {
		$smily [$company_id] = "<i class='icon ui big frown red'></i>";
		$mahnen = true;
	} else {
		$smily [$company_id] = "<i class='icon ui big smile green'></i>";
	}

	$info_list .= "
	<tr>
		<td>$company_name</td>
		<td align=right><font color=red>" . number ( $price_samt [$company_id] ) . " </font></td>
		<td align=center><font color=red>" . $counter [$company_id] . " </font></td>
		<td align=center><font color=red>" . $counter_inkasso [$company_id] . " </font></td>
		<td align=center>" . $smily [$company_id] . "</td>
		
	</tr>";
}

if ($mahnen) {
	$button = "<a href=# onclick=\"load_content_semantic('faktura','list_earnings','',{'set_filter_remind' :true})\" class='button red mini ui'>Jetzt mahnen</a>";
}

if ($iii > 1)
	$info_list .= "
	<tr>
	<td>&nbsp;</td>
	<td align=right><b><font color=red>" . number ( $price_sum ) . "</font></b></td> 
	<td align=center><b><font color=red>" . $counter_sum . "</font></b></td>
	<td align=center><b><font color=red>" . $counter_inkasso_sum . "</font></b></td>
	<td>$button</td>
	</tr>
	";

$info_list_table = "
<table class='ui table definition celled'>
<thead>
<tr>
	<th></th>
	<th><b>Offene Posten</b></th>	
	<th>zu mahnen</th>
	<th>davon Inkasso</th>
	<th>$button</th>
</tr>
</thead>
$info_list
</table>";
