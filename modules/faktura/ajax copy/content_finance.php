<?php
include('../config.inc.php');
include('../inc/text.inc.php');
include('../inc/fu_kontenplan.inc'); // functions

// View for tables
$table_class = 'ui compact small celled table';

/**
 * **********************************************************************
 * CALL "UST-CONCEPT (Details inc/fu_kontenplan.inc)
 * ***********************************************************************
 */
$SetYear = $_SESSION['SetYear_finance'];

if ($SetYear == 0000 or !$SetYear)
	$SetYear = date('Y');

include('../../ssi_smart/smart_form/include_form.php');
$arr['form'] = array('id' => 'form_select_comp', 'action' => 'ajax/set_value.php', 'class' => 'large ui orange', 'width' => '1200');
$arr['ajax'] = array('success' => "load_content_semantic('faktura', $('.item.active.marked').attr('id') );", 'dataType' => 'html'); // location.reload() // $arr['field']['select_company'] = array ( 'settings' => $success , 'type' => 'dropdown' , 'array' => $company_array , 'placeholder' => '--Bitte Firma wählen--' , 'validate' => true , 'value' => $_SESSION['faktura_company_id'] );

$arr['field']['SetYear'] = array('type' => 'dropdown', 'onchange' => "$('#form_select_comp.ui.form').submit()", 'array' => $array_year_finance, 'placeholder' => 'date'("Y"), 'value' => $SetYear);

$output_form = call_form($arr);

$date['quaratal'][1][1] = "$SetYear-01-01";
$date['quaratal'][1][2] = "$SetYear-03-31";
$date['quaratal'][2][1] = "$SetYear-04-01";
$date['quaratal'][2][2] = "$SetYear-06-30";
$date['quaratal'][3][1] = "$SetYear-07-01";
$date['quaratal'][3][2] = "$SetYear-09-31";
$date['quaratal'][4][1] = "$SetYear-10-01";
$date['quaratal'][4][2] = "$SetYear-12-31";

$head_content1 = "<div class='ui header'>Vorsteuer $SetYear</div>";

$content1 .= "<thead>";
$content1 .= "<tr>";
$content1 .= "<th>&nbsp;</th>";
$content1 .= "<th colspan=2>Umsatzsteuer</th>";
$content1 .= "<th colspan=2>Vorsteuer</th>";
$content1 .= "<th colspan=2>Ust-Differenz</th>";
$content1 .= "</tr>";
$content1 .= "</thead>";

// Kopfzeile Verbucht nicht berbucht
$content1 .= "<tr>";
$content1 .= "<td><form action=\"$PHP_SELF\" method=\"POST\" name=\"Senden_change\">" . $link_values_input . $SelectYear . "</form></td>";
$content1 .= "<td class='center aligned'>fakturiert</td>";
$content1 .= "<td class='center aligned'>eingenommen</td>";
$content1 .= "<td class='center aligned'>fakturiert</td>";
$content1 .= "<td class='center aligned'>ausgegeben</td>";
$content1 .= "<td class='center aligned error'>Zahllast</td>";
$content1 .= "</tr>";

for ($ii = 1; $ii <= 4; $ii++) {

	$summe_quartal[1][1] = mwst($date['quaratal'][$ii][1], $date['quaratal'][$ii][2], "eg");
	$summe_quartal[1][2] = mwst($date['quaratal'][$ii][1], $date['quaratal'][$ii][2], "eg2");
	$summe_quartal[1][3] = mwst($date['quaratal'][$ii][1], $date['quaratal'][$ii][2], "ag");
	$summe_quartal[1][4] = mwst($date['quaratal'][$ii][1], $date['quaratal'][$ii][2], "ag2");

	$summe_quartal[1][1] = mwst($date['quaratal'][$ii][1], $date['quaratal'][$ii][2], "eg");
	$summe_quartal[1][2] = mwst($date['quaratal'][$ii][1], $date['quaratal'][$ii][2], "eg2");
	$summe_quartal[1][3] = mwst($date['quaratal'][$ii][1], $date['quaratal'][$ii][2], "ag");
	$summe_quartal[1][4] = mwst($date['quaratal'][$ii][1], $date['quaratal'][$ii][2], "ag2");

	$content1 .= "<tr>";
	$content1 .= "<td>$ii.Quartal</td>";
	$content1 .= "<td class='right aligned'>" . number($summe_quartal[1][1]) . "</td>";
	$content1 .= "<td class='right aligned'>" . number($summe_quartal[1][2]) . "</td>";
	$content1 .= "<td class='right aligned'>" . number($summe_quartal[1][3]) . "</td>";
	$content1 .= "<td class='right aligned'>" . number($summe_quartal[1][4]) . "</td>";
	$content1 .= "<td class='right aligned'><b>" . number($summe_quartal[1][4] - $summe_quartal[1][2]) . "</b></td>";

	// CHART
	$array_quartal_title[$ii] = $ii;
	$array_quartal_earning[$ii] = $summe_quartal[1][2];
	$array_quartal_issues[$ii] = $summe_quartal[1][4];

	// eintrag_buch('',"right","hg_li").
	// eintrag_buch(number($summe_quartal[1][1]-$summe_quartal[1][3]),"right","hg_li2").
	$content1 .= "</tr>";

	$summe_ust2 += round($summe_quartal[1][2], 2);
	$summe_ust4 += round($summe_quartal[1][4], 2);
	$summe_ust += $summe_quartal[1][4] - $summe_quartal[1][2];
}
// $content1 .= "<tr><td colspan=8></td></tr>";

$content1 .= "<tfoot>";
$content1 .= "<tr>";
$content1 .= "<th>Summe</th>";
$content1 .= "<th></th>";
$content1 .= "<th class='right aligned'>" . number($summe_ust2) . "</th>";
$content1 .= "<th></th>";
$content1 .= "<th class='right aligned'>" . number($summe_ust4) . "</th>";
$content1 .= "<th class='right aligned'>" . number($summe_ust) . "</th>";
$content1 .= "</tr>";
$content1 .= "</tfoot>";

$data_quartal_earning = json_encode(array_values($array_quartal_earning));
$data_quartal_issues = json_encode(array_values($array_quartal_issues));
$lables_quartal_array = json_encode(array_values($array_quartal_title));

$js_chart .= "
<script>
new Chart(document.getElementById('chart_quartal'), {
    type: 'line',
    data: {
      labels: $lables_quartal_array,
    		  datasets: [{
  	            label: 'Einnahmen',
  	            backgroundColor: 'green',
  	            borderColor: 'green',
  	            data: $data_quartal_earning,
  	            	fill: false
  	        },
  	        {
  	            label: 'Ausgaben',
  	            backgroundColor: 'red',
  	            borderColor: 'red',
  	            data: $data_quartal_issues,
  	            	fill: false
  	        }]
    },
    options: {
      title: {
        display: true,
        text: 'Einnahmen-Ausgabenentwicklung " . date('Y') . "'
      }
    }
});
</script>";

/**
 * ********************************************************************************
 * //Ausgabe Netto fuer die Monate *
 * //Die Auflistung erfolgt ueber ein for - Schleife *
 * *********************************************************************************
 */
// $content2 .="<tr class=hg_k><td colspan=8 align=center>Netto Gesamt $SetYear</td></tr>";
$head_content2 = "<div class='ui header'>Netto Gesamt $SetYear</div>";

$content2 .= "<thead>";
$content2 .= "<tr>";
$content2 .= "<th>&nbsp;</th>";
$content2 .= "<th colspan=2 class='center aligned'>Einnahmen</th>";
$content2 .= "<th colspan=2 class='center aligned'>Ausgaben</th>";
$content2 .= "<th colspan=2 class='center aligned'>Gewinn/Verlust</th>";
$content2 .= "</tr>";
$content2 .= "</thead>";

$content2 .= "<tr>";
$content2 .= "<td></td>";
$content2 .= "<td class='center aligned'>fakturiert</td>";
$content2 .= "<td class='center aligned'>eingenommen</td>";
$content2 .= "<td class='center aligned'>fakturiert</td>";
$content2 .= "<td class='center aligned'>ausgegeben</td>";
$content2 .= "<td></td>";
$content2 .= "</tr>";

for ($set_monat = 1; $set_monat <= 12; $set_monat++) {

	// Ausgabe der monatlichen Nettobetraege
	$summe_netto_monat[1]['eg'] = fu_summe_netto("eg", $set_monat, $SetYear);
	$summe_netto_monat[2]['eg2'] = fu_summe_netto("eg2", $set_monat, $SetYear);
	$summe_netto_monat[1]['ag'] = fu_summe_netto("ag", $set_monat, $SetYear);
	$summe_netto_monat[2]['ag2'] = fu_summe_netto("ag2", $set_monat, $SetYear);

	$summe_netto_monat_diff[1] = $summe_netto_monat[1]['eg'] - $summe_netto_monat[1]['ag'];
	$summe_netto_monat_diff[2] = $summe_netto_monat[2]['eg2'] - $summe_netto_monat[2]['ag2'];

	if (($summe_netto_monat[1]['eg'] != "" && $summe_netto_monat[1]['eg'] != "0.00") || ($summe_netto_monat[1]['ag'] != "" && $summe_netto_monat[1]['ag'] != "0.00")) {

		$summe_netto_fakturiert += $summe_netto_monat[1]['eg'];
		$summe_netto += $summe_netto_monat[2]['eg2'];

		$summe_netto_ausgabe_fakturiert += $summe_netto_monat[1]['ag'];
		$summe_netto_ausgabe += $summe_netto_monat[2]['ag2'];

		$content2 .= "<tr>";
		$content2 .= "<td>" . $set_monat . ".Monat</td>";
		$content2 .= eintrag_buch(number($summe_netto_monat[1]['eg']), "right", "hg_li2");
		$content2 .= eintrag_buch(number($summe_netto_monat[2]['eg2']), "right", "hg_li");
		$content2 .= eintrag_buch(number($summe_netto_monat[1]['ag']), "right", "hg_li2");
		$content2 .= eintrag_buch(number($summe_netto_monat[2]['ag2']), "right", "hg_li");
		// eintrag_buch(number($summe_netto_monat_diff[1]),"right","hg_li2").
		// eintrag_buch('',"right","hg_li2").
		$content2 .= eintrag_buch(number($summe_netto_monat_diff[2]), "right", "hg_li") . "</tr>";

		// CHART
		$array_month_title[$set_monat] = $set_monat;
		$array_month_earning[$set_monat] = $summe_netto_monat[2]['eg2'];
		$array_month_issues[$set_monat] = $summe_netto_monat[2]['ag2'];
	}
}

if (is_array($array_month_title))
	$lables_month_array = json_encode(array_values($array_month_title));

if (is_array($array_month_earning))
	$data_month_earning = json_encode(array_values($array_month_earning));

if (is_array($array_month_earning))
	$data_month_issues = json_encode(array_values($array_month_issues));

if (is_array($array_month_earning)) {
	$js_chart .= "
    <script>
    new Chart(document.getElementById('chart_month1'), {
        type: 'line',
        data: {
          labels: $lables_month_array,
        		  datasets: [{
      	            label: 'Einnahmen',
      	            backgroundColor: 'green',
      	            borderColor: 'green',
      	            data: $data_month_earning,
      	            	fill: false
      	        },
      	        {
      	            label: 'Ausgaben',
      	            backgroundColor: 'red',
      	            borderColor: 'red',
      	            data: $data_month_issues,
      	            	fill: false
      	        }]
        },
        options: {
          title: {
            display: true,
            text: 'Einnahmen-Ausgabenentwicklung " . date('Y') . "'
          }
        }
    });
    </script>";

	$js_chart .= "
<script>
new Chart(document.getElementById('chart_month2'), {
    type: 'bar',
    data: {
      labels: $lables_month_array,
    		  datasets: [{
  	            label: 'Einnahmen',
  	            backgroundColor: 'green',
  	            borderColor: 'green',
  	            data: $data_month_earning,
  	            	fill: false
  	        },
  	        {
  	            label: 'Ausgaben',
  	            backgroundColor: 'red',
  	            borderColor: 'red',
  	            data: $data_month_issues,
  	            	fill: false
  	        }]
    },
    options: {
    legend: {
    	display: false
    },
  	tooltips: {
    	callbacks: {
      	label: function(tooltipItem) {
        console.log(tooltipItem)
        	return tooltipItem.yLabel;
        }
      }
    }
  }
});
</script>

";
}
// $summe_netto_differenz_fakturiert = $summe_netto_fakturiert - $summe_netto_ausgabe_fakturiert;
$summe_netto_differenz = $summe_netto - $summe_netto_ausgabe;

$content2 .= "<tfoot>";
$content2 .= "<tr>";
$content2 .= "<th>Summe</th>";
$content2 .= "<th class='right aligned'>" . number($summe_netto_fakturiert) . "</th>";
$content2 .= "<th class='right aligned'><b>" . number($summe_netto) . "</b></th>";
$content2 .= "<th class='right aligned'>" . number($summe_netto_ausgabe_fakturiert) . "</th>";
$content2 .= "<th class='right aligned'>" . number($summe_netto_ausgabe) . "</th>";
$content2 .= "<th class='right aligned'><b>" . number($summe_netto_differenz) . "</b></th>";
$content2 .= "</tr>";
$content2 .= "</tfoot>";

/**
 * ********************************************************************************
 * /Ausgabe Brutto fuers Jahr *
 * *********************************************************************************
 */
$summe_brutto[1]['eg'] = fu_summe_netto("eg", "", $SetYear);
$summe_brutto[1]['ag'] = fu_summe_netto("ag", "", $SetYear);
$summe_brutto[2]['eg'] = fu_summe_netto("eg2", "", $SetYear); // nicht verbucht
$summe_brutto[2]['ag'] = fu_summe_netto("ag2", "", $SetYear); // nicht verbucht

/**
 * *********************************************************************************
 * Durchschnittswerte
 * *********************************************************************************
 */
$content_durchschnitt = "
<div class='ui header'>Jahresdurchschnitt $SetYear</div>
<table class='$table_class collapsing'>
<tbody>
<tr><td>Gewinn</td><td class='right aligned'><b>" . number(($summe_brutto[1]['eg'] - $summe_brutto[1]['ag']) / 12) . "</b></td></tr>
<tr><td>Umsatz</td><td class='right aligned'>" . number($summe_brutto[1]['eg'] / 12) . "</td></tr>
<tr><td>Verlust</td><td class='right aligned'>" . number(-$summe_brutto[1]['ag'] / 12) . "</td></tr>
</tbody>
</table>";

$content .= $chooseYear;

$content .= $content_durchschnitt . "
<br>
<div class='ui equal width grid stackable'>
<div class='column'>$head_content1<table class='$table_class'>$content1</table></div>
<div class='column'><canvas id='chart_quartal' ></canvas></div>
</div>

$head_content2
<div class='ui equal width grid stackable'>
<div class='column'>
<table class='$table_class'>$content2</table>
</div>

<div class='column'>
	<canvas id='chart_month1' ></canvas>
	<canvas id='chart_month2' ></canvas>
</div>
</div>
";

$content_ust = "$content";

/**
 * **********************************************************************
 * CALL "STEUERERKLÄRUNG
 * ***********************************************************************
 */
$ausgabe = ausgabe_kontenplan("ag", "$SetYear");
$einnahme = ausgabe_kontenplan("eg", "$SetYear");
/*
 * AFA
 * mm@ssi.at am 06.07.2012
 */
$sql = $GLOBALS['mysqli']->query("SELECT
		description, account, accounts.title title, DATE_FORMAT(date_booking,'%Y') date_create,
		SUM(netto) netto,
		SUM(netto)/3 netto_1_3
		FROM issues INNER JOIN accounts ON issues.account = account_id
		WHERE afa_400 = 1
		GROUP by account_id, DATE_FORMAT(date_booking,'%Y')
		ORDER by DATE_FORMAT(date_booking,'%Y')
		");
while ($array = mysqli_fetch_array($sql)) {
	$desc = $array['description'];
	$account = $array['account'];
	$date = $array['date_create'];
	$netto = $array['netto'];
	$set_netto1_3 = $netto1_3[$date] = $array['netto_1_3'];
	$account_title = $array['title'];
	// $content .="<br> $date-> $account ($account_title) -> $netto -> (1/3 $set_netto1_3)"; // $desc->";
}
for ($year = $SetYear; $year >= $SetYear - 2; $year--) {
	$afa_sumary += $netto1_3[$year];
}

/**
 * *******************************************************************************
 * Umsatzsteuererklärung
 * *******************************************************************************
 */
$content = "<div class='ui header'>" . $str2300AdmTitleGroup[10] . "&nbsp;" . $SetYear . "</div>";
$content .= "<table class='$table_class'>";
$content .= "<tr>";
$content .= "<td width=600>" . $str2300AdmFieldGroup['000'] . "</td>";
$content .= "<td  width=100>000</td>";
$content .= "<td class='right aligned'>" . number($k_summe_value['eg']['netto']) . "</td>";
$content .= "</tr>";
$content .= "<tr>";
$content .= "<td>AFA (" . ($SetYear - 2) . "-" . $SetYear . ")</td>";
$content .= "<td >xxx</td>";
$content .= "<td class='right aligned'>" . number($afa_sumary) . "</td>";
$content .= "</tr>";
$content .= "<tr>";
$content .= "<td>" . $str2300AdmFieldGroup['011'] . "</td>";
$content .= "<td >011</td>";
$content .= "<td class='right aligned'>" . number($k_summe_value['eg']['netto'] - $k_summe_value['eg']['netto20']) . "</td>";
$content .= "</tr>";
$content .= "<tr>";
$content .= "<td>" . $str2300AdmFieldGroup['022'] . "</td>";
$content .= "<td>022</td>";
$content .= "<td class='right aligned'>" . number($k_summe_value['eg']['netto20']) . "</td>";
$content .= "</tr>";

$content .= "<tr><td>" . $str2300AdmFieldGroup['029'] . "</td><td>029</td><td class='right aligned'>" . number($k_summe_value['eg']['steuer_10']) . "</td></tr>";

$content .= "<tr>";
$content .= "<td>" . $str2300AdmFieldGroup['060'] . "</td>";
$content .= "<td>060</td>";
$content .= "<td class='right aligned'>" . number($k_summe_value['ag']['steuer_summe']) . "</td>";
$content .= "</tr>";
$content .= "<tfoot>";
$content .= "<tr>";
$content .= "<th><b>" . $str2300AdmFieldGroup['095'] . "</b></th>";
$content .= "<th>095</th>";
$content .= "<th class='right aligned'><b>" . number($k_summe_value['ag']['steuer_summe'] - $k_summe_value['eg']['steuer_summe']) . "</b></th>";
$content .= "</tr>";
$content .= "</tfoot>";
$content .= "</table>";

/**
 * *******************************************************************************
 * Einkommenssteuererklärung
 * *******************************************************************************
 */
$content .= "<div class='ui header'>" . $str2300AdmTitleGroup[20] . "&nbsp;" . $SetYear . "</div>";
$content .= "<table class='$table_class'>";
$content .= "<tr>";
$content .= "<td width=600>" . $str2300AdmFieldGroup['320'] . "</td>";
$content .= "<td  width=100>320</td>";
$content .= "<td class='right aligned'>" . number($k_summe_value['eg']['netto'] - $k_summe_value['ag']['netto']) . "</td>";
$content .= "</tr>";
$content .= "<tr >";
$content .= "<td>" . $str2300AdmFieldGroup['9040'] . "</td>";
$content .= "<td align=center>9040</td>";
$content .= "<td class='right aligned'><u><b>" . number($k_summe_value['eg']['netto'] - $k_summe_value['ag']['netto']) . "</b></u></td>";
$content .= "</tr>";
$content .= "</table>";

$content_steuer = "$content";

/**
 * **********************************************************************
 * CALL "KONTENPLAN (Details inc/fu_kontenplan.inc)
 * ***********************************************************************
 */

// Ausgabe der Konten (eg)
// $ausgabe = ausgabe_kontenplan("ag","$SetYear");
// $einnahme = ausgabe_kontenplan("eg","$SetYear");

// Auflistung der Kontengruppen
$content_konten_eg = "<div class='ui header'>Einnahmen $SetYear</div>";
$content_konten_eg .= "<table class='$table_class'>";
$content_konten_eg .= ausgabe_kontenplan_gruppe($SetYear);
$content_konten_eg .= "</table>";

$content_konten_eg .= "<div class='ui header'>Konten</div>";
$content_konten_eg .= "<table class='$table_class'>";
$content_konten_eg .= $einnahme;
$content_konten_eg .= "</table>";

$content_konten_ag .= "<div class='ui header'>Ausgaben $SetYear</div>";
$content_konten_ag .= "<table class='$table_class'>";
$content_konten_ag .= $ausgabe;
$content_konten_ag .= "</table > ";

echo "<script> var company_id = {$_SESSION['faktura_company_id']};</script>";
echo "<script type=\"text/javascript\" src=\"js/list_finance.js\"></script>";
echo "$js_chart";

echo "<div id=window_list></div>";
echo $output_form['html'];

echo $output_form['js'];

echo "
<div style='max-width:1200px' >
<div class='ui top attached tabular menu'>
  <a class='item' data-tab='first'>Ust-Konzept</a>
  <a class='active item' data-tab='second_eg'>Einnahmen</a>
  <a class='item' data-tab='second_ag'>Ausgaben</a>
  <a class='item' data-tab='third'>Steuererklärung</a>
</div>
<div class='ui bottom attached tab segment' data-tab='first'>$content_ust</div>
<div class='ui bottom attached active tab segment' data-tab='second_eg'>$content_konten_eg</div>
<div class='ui bottom attached tab segment' data-tab='second_ag'>$content_konten_ag</div>
<div class='ui bottom attached tab segment' data-tab='third'>$content_steuer</div>
</div>
";

echo '<div id=modal_issues class="ui fullscreen modal">
  <i class="close icon"></i>
  <div class="header">Ausgaben</div>
  <div class="content">
    <p></p>
    <p></p>
    <p></p>
  </div>
</div>';

echo '<div id=modal_earnings class="ui fullscreen modal">
  <i class="close icon"></i>
  <div class="header">Einnahmen</div>
  <div class="content">
    <p></p>
    <p></p>
    <p></p>
  </div>
</div>';
