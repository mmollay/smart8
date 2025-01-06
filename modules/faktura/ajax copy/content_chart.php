<?php
include("../../ssi_smart/smart_form/include_list.php");
//https://www.chartjs.org/
include('../config.inc.php');
include('../inc/fu_kontenplan.inc'); // functions

ksort($array_year_finance);
foreach ($array_year_finance as $year) {
	$array_earning[$year] = call_data_earning_netto($year);
	$array_issues[$year] = call_data_issues_netto($year);
}

$array_chart = call_list('../list/chart.php', '../config.inc.php');

$data_earning = json_encode(array_values($array_earning));
$data_issues = json_encode(array_values($array_issues));
$lables_array = json_encode(array_keys($array_year_finance));
?>
<div class="ui equal width grid stackable">
	<div class="column">
		<canvas id='chart1'></canvas>
	</div>
	<div class="column">
		<canvas id='chart2'></canvas>
	</div>
</div>
<?= $array_chart['html'] . $array_chart['js'] ?>

<script>
	new Chart(document.getElementById("chart1"), {
		//type: 'line',
		type: 'bar',
		data: {
			labels: <?= $lables_array ?>,
			datasets: [{
				label: "Einnahmen",
				backgroundColor: 'green',
				borderColor: 'green',
				data: <?= $data_earning ?>,
				fill: false
			},
			{
				label: "Ausgaben",
				backgroundColor: 'red',
				borderColor: 'red',
				data: <?= $data_issues ?>,
				fill: false
			}]
		},
		options: {
			ticks: { beginAtZero: true },
			title: {
				display: true,
				text: 'Einnahmen-Ausgabenentwicklung von 2011 bis <?= date('Y') ?>'
			}
		}
	});
</script>

<script>
	new Chart(document.getElementById("chart2"), {
		//type: 'line',
		type: 'line',
		data: {
			labels: <?= $lables_array ?>,
			datasets: [{
				label: "Einnahmen",
				backgroundColor: 'green',
				borderColor: 'green',
				data: <?= $data_earning ?>,
				fill: false,
				tension: 0.4
			},
			{
				label: "Ausgaben",
				backgroundColor: 'red',
				borderColor: 'red',
				data: <?= $data_issues ?>,
				fill: false,
				tension: 0.4
			}]
		},
		options: {
			ticks: { beginAtZero: true },
			title: {
				display: true,
				text: 'Einnahmen-Ausgabenentwicklung von 2011 bis <?= date('Y') ?>'
			}
		}
	});

</script>


<?php
// Auslesen der Summe der Einnahmen aus dem jeweiligen Jahr
function call_data_earning_brutto($year)
{
	$sql_string = "SELECT SUM(booking_total) FROM bills WHERE DATE_FORMAT(date_booking,'%Y') = '$year' AND document = 'rn'";
	$query = $GLOBALS['mysqli']->query($sql_string);
	$array = mysqli_fetch_array($query);
	return $array[0];
}



function call_data_earning_netto($year)
{
	$sql_string = "SELECT SUM(netto) FROM bills WHERE DATE_FORMAT(date_booking,'%Y') = '$year' AND document = 'rn'";
	$query = $GLOBALS['mysqli']->query($sql_string);
	$array = mysqli_fetch_array($query);
	return $array[0];
}


// Auslesn der Summer der Ausgaben aus dem jeweilingen Jahr
function call_data_issues_brutto($year)
{
	$sql_string = "SELECT SUM(issues.netto)+SUM(IF(accounts.tax='20', issues.netto * 0.2, 0))+SUM(IF(accounts.tax='10', issues.netto * 0.1, 0)) FROM issues,accounts WHERE account_id = account  AND DATE_FORMAT(date_booking,'%Y') = '$year'";
	$query = $GLOBALS['mysqli']->query($sql_string);
	$array = mysqli_fetch_array($query);
	return $array[0];
}

function call_data_issues_netto($year)
{
	$sql_string = "SELECT SUM(issues.netto) FROM issues,accounts WHERE account_id = account  AND DATE_FORMAT(date_booking,'%Y') = '$year'";
	$query = $GLOBALS['mysqli']->query($sql_string);
	$array = mysqli_fetch_array($query);
	return $array[0];
}

