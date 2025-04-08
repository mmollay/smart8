<?php
$total_td = '';

if (!is_array($arr['th']))
	return;

if ($arr['list']['footer'] === false)
	return;

foreach ($arr['th'] as $key => $value) {

	/**
	 * `extract` setzt Variablen basierend auf den Schlüssel-Wert-Paaren aus dem Array $value.
	 * Die Option EXTR_OVERWRITE überschreibt bestehende Variablen mit dem gleichen Namen.
	 */
	//extract($value, EXTR_OVERWRITE);

	$class = $value['class'];
	$align = $value['align'];
	$format = $value['format'];
	$dataType = $value['dataType'];
	$colspan = $value['colspan'];
	$gallery = $value['gallery'];
	$total = $value['total'];

	if (isset($total)) {

		$count_total++;

		if ($arr['mysql']['table_total']) {
			$table_total = $arr['mysql']['table_total'];
		} else
			$table_total = $arr['mysql']['table'];

		// Get Total value
		$query_sql_total = "SELECT SUM($key) sum_$key FROM  $table_total  WHERE 1 $sql_total";

		if ($arr['mysql']['debug'])
			echo "<hr><pre>Total:<br>" . htmlspecialchars($query_sql_total) . "</pre><hr>";

		$query_total = $GLOBALS['mysqli']->query($query_sql_total) or die(mysqli_error($GLOBALS['mysqli']));
		$array_total = mysqli_fetch_array($query_total);

		$value_total = $array_total["sum_$key"];

		if ($align)
			$class .= " $align aligned ";

		if ($format)
			$value_total = formatCurrency($value_total, $format);

	} else
		$value_total = '';

	$total_td_list .= "<td class=' $class' $add_span_td><b>$value_total</b></td>";
}

if ($count_total) {
	if (is_array($arr['tr']['buttons']['left'])) {
		if (is_array($arr['checkbox']))
			$total_td .= "<td></td>";
		$total_td .= "<td><b>Summe</b></td>";
	}

	if ($serial) {
		$total_td .= "<td></td>";
	}

	$total_td .= $total_td_list;

	if (is_array($arr['tr']['buttons']['right']))
		$total_td .= "<td></td>";

	$total_tr = "<tr class='active'>$total_td</tr>";

}
