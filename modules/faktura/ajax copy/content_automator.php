<?php
require_once ('../config.inc.php');
include ('../../ssi_smart/smart_form/include_form.php');

if ($_SESSION['SetYear_automator'])
    $SetYear = $_SESSION['SetYear_automator'];
elseif ($SetYear == 0000 or ! $SetYear)
    $SetYear = date('Y');

$arr['form'] = array('id' => 'form_select_comp','action' => 'ajax/set_value_automator.php');
$arr['ajax'] = array('success' => "load_content_semantic('faktura', $('.item.active.marked').attr('id') );",'dataType' => 'html'); // location.reload() // $arr['field']['select_company'] = array ( 'settings' => $success , 'type' => 'dropdown' , 'array' => $company_array , 'placeholder' => '--Bitte Firma wählen--' , 'validate' => true , 'value' => $_SESSION['faktura_company_id'] );
$arr['field']['SetYear'] = array('type' => 'dropdown','class' => '','onchange' => "$('#form_select_comp.ui.form').submit()",'array' => $array_year_finance,'placeholder' => 'date'("Y"),'value' => $SetYear);

/**
 * ***************************************************************
 * List - ISSUES
 * ***************************************************************
 */
unset($_SESSION['automator']);
$_SESSION['automator'] = array();

// Abrufen der Begriffe
$automator_query = $GLOBALS['mysqli']->query("SELECT automator_id,word,description from automator") or die(mysqli_error($GLOBALS['mysqli']));
while ($automator_array = mysqli_fetch_array($automator_query)) {
    $word_list = $automator_array['word'];
    $description = $automator_array['description'];
    $word_list_label = '';
    $add_mysql = '';
    $word_array = preg_split("/\\n/", $word_list);

    foreach ($word_array as $word) {
        if ($word) {
            $word = trim($word);
            if ($add_mysql) {
                $add_mysql .= ' OR ';
            }
            $add_mysql .= " data_elba.text LIKE '%$word%' ";
            $word_list_label .= "<div class='ui mini label'>$word</div>";
        }
    }

    $automator_id = $automator_array['automator_id'];

    $amount_sum = 0;
    $table_td = '';
    $table_th = '';
    $table_td_info = '';
    $count = '';
    $uuu = '';
    // SET connector_id for elba
    $sql_update = "UPDATE data_elba SET automator_id='$automator_id' WHERE ($add_mysql)";
    $GLOBALS['mysqli']->query($sql_update) or die(mysqli_error($GLOBALS['mysqli']));

    // Auflisten aller gefundenen Felder in Bezug auf den Suchbegriff
    $sql = "SELECT data_elba.elba_id, 
		bill_id,
        IF(LENGTH(text) >= 120, CONCAT(substring(text, 1,120), CONCAT('<span title=\'',text,'\'>[...]</span>')), text) text, date,amount
        FROM 
		data_elba  LEFT JOIN issues ON data_elba.connect_id = issues.bill_id
		WHERE ($add_mysql) AND YEAR(date)=$SetYear  ORDER by date ";

    $query = $GLOBALS['mysqli']->query($sql) or die(mysqli_error($GLOBALS['mysqli']));
    while ($build = mysqli_fetch_array($query)) {
        $bill_id = $build['bill_id'];

        if (! $bill_id) {
            $count ++;
            $elba_id = $build['elba_id'];
            $amount_sum += $build['amount'];
            $_SESSION['automator'][$automator_id][$elba_id] = true;
            if ($build['date'] == $date_check && $amount_check == $build['amount']) 
                $double_warning_icon = "<span data-tooltip='Möglicher Doppeleintrag'><i class='exclamation triangle icon red' ></i></span>";
            else 
                $double_warning_icon = '';
                
            $table_td .= "<tr id='tr_$elba_id' >
            <td>" . ++ $uuu . "</td>
			<td><div class='ui mini icon blue button' onclick=insert_inner_issues('$automator_id','$elba_id') >Einpflegen</div></td>
			<td>" . $build['date'] . "</td><td>" . $build['amount'] . "</td><td>$double_warning_icon" . $build['text'] . "<div style='float:right;'><div class='ui mini icon red button' onclick=remove_inner_elba('$elba_id') ><i class='trash icon'></i></div></div></td>
			</tr> ";
            $amount_check = $build['amount'];
            $date_check = $build['date'];
        }
    }

    // <button class='ui mini blue icon button' onclick=insert_inner_issues('$automator_id') >Alle Felder einpflegen</button>
    $amount_sum_total += $amount_sum;
    if ($table_td) {
        $word_list_view .= "
			<tr class='tr_listvalue' id='tr_$automator_id' >
            <td><a href='#table_$automator_id' class='ui mini icon button' data-tooltip='Zur Übersicht springen'><i class='angle down icon'></i></a></td>
            <td><span class='ui small header'>$description</span><br>$word_list</td>
            <td>$count Einträge</td>
            <td class='right aligned'>" . number($amount_sum) . "</td>
            </tr>
            ";
        $table_th .= "<thead><tr><th colspan=5>";
        $table_th .= "<div class='ui  header'>$description</div>";
        $table_th .= "<button class='ui mini blue icon button' onclick=insert_inner_issues('$automator_id') >Alle Felder einpflegen</button>";
        $table_th .= "<a href='#back' class='ui icon button mini tooltip' title='wieder zur Übersicht'><i class='icon arrow up'></i></a>";
        $table_th .= "</th></tr></thead>";

        $table_td_info .= '<tr><td colspan = 5>';
        $table_td_info .= "Schlüsselwörter: $word_list_label<br>";
        $table_td_info .= "Summe:  " . number($amount_sum);
        $table_td_info .= "</td></tr>";

        $table_issues_output .= "<div id='table_$automator_id'>";
        $table_issues_output .= "<table class='ui very compact small selectable celled table'>$table_th $table_td_info $table_td</table>";
        $table_issues_output .= "<br></div>";
    }
}

/**
 * ***************************************************************
 * List - EARNINGS
 * ***************************************************************
 */
$uuu = '';
$sql_earning = "SELECT bill_id,brutto,bill_number,netto,firstname,secondname,date_send,
(CASE
	WHEN LENGTH(company_1) >= 40 then CONCAT(substring(company_1, 1,40), CONCAT('<span  title=\'',company_1,'\'>[...]</span>'))
	WHEN company_1 = '' then CONCAT (firstname,' ',secondname)
	ELSE company_1
	END) as company_1
 		FROM bills WHERE 1 AND document = 'rn' AND date_booking = '0000-00-00' and date_storno = '0000-00-00' GROUP by bills.bill_id ORDER BY bill_number ";
$automator_earning_query = $GLOBALS['mysqli']->query($sql_earning) or die(mysqli_error($GLOBALS['mysqli']));
while ($automator_earning_array = mysqli_fetch_array($automator_earning_query)) {
    $pattern = $automator_earning_array['bill_number'];
    $bill_number = $automator_earning_array['bill_number'];
    $bill_id = $automator_earning_array['bill_id'];
    $client = $automator_earning_array['company_1'];
    $netto = $automator_earning_array['netto'];
    $brutto = $automator_earning_array['brutto'];
    $company1 = $automator_earning_array['company1'];
    $secondname = $automator_earning_array['secondname'];
    $firstname = $automator_earning_array['firstname'];
    $date_send = $automator_earning_array['date_send'];
    
    
    // SURE - BILLS
    $sql = "SELECT elba_id, text, date,amount
        FROM data_elba WHERE data_elba.text LIKE '%{$automator_earning_array ['bill_number']}%' AND amount = '{$automator_earning_array ['brutto']}' AND  data_elba.date >= '$date_send' ";
    $query = $GLOBALS['mysqli']->query($sql) or die(mysqli_error($GLOBALS['mysqli']));
    $build_earning = mysqli_fetch_array($query);
    $elba_id = $build_earning['elba_id'];
    $amount_earning_sum_total += $build_earning['amount'];
    if ($build_earning['date']) {
        // $build_earning ['text'] = preg_replace ( "[$pattern]", "<span class='label compact ui'>$pattern</span>", $build_earning ['text'] );
        $build_earning['text'] = preg_replace("[$pattern]", "<span class='ui text red'>$pattern</span>", $build_earning['text']);
        $table_earning_td .= "
		<tr class='top aligned' id='tr_earning$bill_id' >
			<td><button class='ui mini green icon button' onclick=call_booking_earning('$bill_id','$elba_id') >Verbuchen</button></td>
			<td>" . ++ $uuu . "</td>
            <td>" . $bill_number . "</td>
			<td>" . $date_send . "</td>
			<td>" . $build_earning['date'] . "</td>
			<td>" . $client . "</td>
			<td>" . $build_earning['text'] . "</td>
			<td class='right aligned' >" . number($build_earning['amount']) . "</td>
		</tr> ";
    } else {
        // POSSIBLE - BILLS
        // check inner DB
        $sql = "SELECT elba_id, text, date,amount
        FROM data_elba WHERE MATCH(data_elba.text) AGAINST('$firstname $secondname' IN NATURAL LANGUAGE MODE ) AND amount = '$brutto' AND  data_elba.date >= '$date_send' "; //
        $query = $GLOBALS['mysqli']->query($sql) or die(mysqli_error($GLOBALS['mysqli']));
        $build_earning = mysqli_fetch_array($query);
        $elba_id = $build_earning['elba_id'];
        $amount_earning_sum_total += $build_earning['amount'];
        if ($build_earning['date']) {
            // $build_earning ['text'] = preg_replace ( "[$pattern]", "<span class='label compact ui'>$pattern</span>", $build_earning ['text'] );
            $build_earning['text'] = preg_replace("[$firstdname]", "<span class='ui text red'>$firstdname</span>", $build_earning['text']);
            $build_earning['text'] = preg_replace("[$secondname]", "<span class='ui text red'>$secondname</span>", $build_earning['text']);
            $table_earning_td .= "
		<tr class='top aligned' id='tr_earning$bill_id' >
			<td><button class='ui mini grey icon button' onclick=call_booking_earning('$bill_id','$elba_id') >Verbuchen</button></td>
			<td>" . ++ $uuu . "</td>
            <td>" . $bill_number . "</td>
			<td>" . $date_send . "</td>
			<td>" . $build_earning['date'] . "</td>
			<td norwap>" . $client . "</td>
			<td>" . $build_earning['text'] . "</td>
			<td class='right aligned' >" . number($build_earning['amount']) . "</td>
		</tr> ";
        }
    }
}

// OUTPUT
echo "<div style='max-width:1400px'>";

/**
 * *********************
 * OUTPUT - List Eearnings
 * *********************
 */
if ($table_earning_td) {
    echo "
	<div class='ui small header'>Einnahmen zum Verbuchen</div>
	<table class='ui very compact small table'>
	<thead><tr>
        <th></th>
        <th>Nr.</th>
        <th>ReNr.</th>
        <th>ReDatum</th>
        <th>Buchung</th>
        <th>Client</th>
        <th>Text</th>
        <th>Betrag</th>
    </tr><thead>
	<tbody>$table_earning_td</tbody>
	<tfoot><tr><td colspan=7><b>Summe:</b></td><td class='right aligned collapsing'>" . number($amount_earning_sum_total) . "</td></tr><tfoot>
	</table>";
}

/**
 * *********************
 * OUTPUT - List Issues
 * *********************
 */
$output_form = call_form($arr);
echo "<span id = back></span>";
echo $output_form['js'];

if (! $word_list_view) {
    $word_list_view = "<tr><td colspan=6 class='center aligned'><br>Keine Eiträge vorhanden<br><br></td></tr>";
}

// if ($word_list_view)
echo "
    <br><div class='ui header small'>Ausgaben zum Einpflegen</div>
	<table id='automator_list' class='ui very compact  small table'>
	<thead><tr><th colspan=6>" . $output_form['html'] . "</th></tr><thead>
	<tbody>$word_list_view</tbody>
	<tfoot><tr><td colspan=3>Summe:</td><td class='right aligned'>" . number($amount_sum_total) . "</td></tr><tfoot>
	</table>";

echo $table_issues_output;

echo "</div>";


