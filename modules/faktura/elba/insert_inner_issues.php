<?php
include (__DIR__ . '/../f_config.php');

foreach ($_POST as $key => $value) {
    if ($value) {
        $GLOBALS[$key] = $GLOBALS['mysqli']->real_escape_string($value);
    }
}

if ($elba_id == 'false' && $automator_id) {

    // Multi-insert "Automator" for elba import
    foreach ($_SESSION['automator'][$automator_id] as $elba_id => $value) {
        if ($value == true) {
            $arr_nr = insert_elba_inner_issue($automator_id, $elba_id);
            if (!$count_issues) {
                $message['date']['first'] = $arr_nr['date'];
                $message['title']['first'] = $arr_nr['title'];
                $message['nr']['first'] = $arr_nr['nr'];
            }
            $message['netto'] += $arr_nr['netto'];
            $message['brutto'] += $arr_nr['brutto'];
            $count_issues++;
        }
    }

    $automator_word = mysql_singleoutput("SELECT word from automator WHERE automator_id= '$automator_id' ");
    $automator_word = $GLOBALS['mysqli']->real_escape_string($automator_word);

    $set_toast_message['netto'] = $message['netto'];
    $set_toast_message['brutto'] = $message['brutto'];
    $set_toast_message['title'] = $automator_word . " (Anzahl:$count_issues)";
    $set_toast_message['date'] = $message['date']['first'] . " - " . $arr_nr['date'];
    $set_toast_message['nr'] = $message['nr']['first'] . " - " . $arr_nr['nr'];

    $count_insert = count($_SESSION['automator'][$automator_id]);

    // echo "$('body').toast({message: '$count_insert neue Einträge wurden erzeugt'});";
    // echo "$('body').toast({displayTime: 0,closeIcon: true,message: '$output'});";

    get_message($set_toast_message);

    echo "$('#table_$automator_id').remove();";
    echo "$('#tr_$automator_id').remove();";
} elseif ($automator_id && $elba_id) {

    // Single insert for elba import
    $arr_nr = insert_elba_inner_issue($automator_id, $elba_id);
    get_message($arr_nr);

    // Sonderfunktion bei direkten einpflegen aus der Elbaliste
    if ($fromlist == 'elba') {
        echo "$('#insertbutton_$elba_id').replaceWith('{$arr_nr['nr']}');";
    } else {
        echo "$('#tr_$elba_id').remove();";
    }


}

// Eintrag in die Datenbank und setzen der Flagge bei Elba
function insert_elba_inner_issue($automator_id, $elba_id)
{

    // Auslesen der Werte von automator_id
    $query_automator = $GLOBALS['mysqli']->query("SELECT * from automator WHERE automator_id = '$automator_id' ") or die(mysqli_error($GLOBALS['mysqli']));
    $array_automator = mysqli_fetch_array($query_automator);
    // ##################################
    $description = $array_automator['description'];
    $account_id = $array_automator['account_id'];
    $client_id = $array_automator['client_id'];
    // ##################################

    // Auslesen der Werte von elba_id
    $query_elba = $GLOBALS['mysqli']->query("SELECT * from data_elba WHERE elba_id = '$elba_id' ") or die(mysqli_error($GLOBALS['mysqli']));
    $array_elba = mysqli_fetch_array($query_elba);
    // ##################################
    $brutto = $amount = -$array_elba['amount'];
    $date_create = $array_elba['date'];
    // ##################################

    // Auslesen der Prozent aus der Datenbank
    $tax = mysql_singleoutput("SELECT tax FROM accounts WHERE account_id = '$account_id' ");

    // auslesen und erzeugen eine neuen fortlaufenden Nummer
    $bill_number = mysql_singleoutput("SELECT MAX(bill_number) as bill_number FROM issues  ", "bill_number") + 1; // WHERE company_id = '{$_SESSION['faktura_company_id']}'

    $netto = $brutto / (100 + $tax) * 100;
    $mwst = $brutto - $netto;

    $GLOBALS['mysqli']->query("INSERT INTO issues SET
		company_id  = '{$_SESSION['faktura_company_id']}',
		client_id   = '$client_id',
		bill_number = '$bill_number',
		date_create = '$date_create',
		date_booking= '$date_create',
		account     = '$account_id',
		description = '$description',
		netto       = '$netto',
		brutto      = '$brutto',
		mwst        = '$mwst',
		tax         = '$tax',
		comment     = '$comment',
        company_1 = '',
        amazon_order_nr = '',
        elba_id     = '$elba_id'
		") or die(mysqli_error($GLOBALS['mysqli']));

    $issue_id = mysqli_insert_id($GLOBALS['mysqli']);

    // Flagge setzen in Elba
    $GLOBALS['mysqli']->query("UPDATE data_elba SET connect_id = '$issue_id' WHERE elba_id = '$elba_id' ") or die(mysqli_error($GLOBALS['mysqli']));

    $tax = mysql_singleoutput("SELECT tax FROM accounts WHERE account_id = '$account_id' ");

    $arr_nr['title'] = $description;
    $arr_nr['nr'] = $bill_number;
    $arr_nr['date'] = $date_create;
    $arr_nr['netto'] = $netto;
    $arr_nr['brutto'] = $brutto;

    return $arr_nr;
}

// Get message for Toast
function get_message($arr_nr)
{
    $title = trim(preg_replace('/\s\s+/', ' ', $arr_nr['title']));
    $date = trim(preg_replace('/\s\s+/', ' ', $arr_nr['date']));
    $netto = trim(preg_replace('/\s\s+/', ' ', number($arr_nr['netto'])));
    $brutto = trim(preg_replace('/\s\s+/', ' ', number($arr_nr['brutto'])));

    $message = "<div class=\'ui toast toast_elba\'>";
    $message .= "<div class=\'content\'>";
    $message .= "<div class=\'ui header\'>Verbucht</div>";
    $message .= "<div class=\'ui divider\'></div>";
    $message .= "<div class=\'ui list\'>";
    $message .= "<div class=\'item\'><i class=\'info icon\'></i><div class=\'content\'>$title</div></div>";
    $message .= "<div class=\'item\'><i class=\'calendar icon\'></i><div class=\'content\'>$date</div></div>";
    $message .= "<div class=\'item\'><i class=\'euro icon\'></i><div class=\'content\'>$netto (netto)</div></div>";
    $message .= "<div class=\'item\'><i class=\'euro icon\'></i><div class=\'content\'>$brutto (brutto)</div></div>";
    $message .= "</div>";
    $message .= "<div class=\'ui icon input focus labeled\'><div class=\'ui label\'>Nr.:</div><input onFocus=\'this.select()\'  type=\'text\' value=\'{$arr_nr['nr']}\'></div>";
    $message .= "</div>";
    $message .= "<div class=\'left basic actions\'><button class=\'ui positive button\'>Schließen</button></div>";
    $message .= "</div>";

    echo "$('.toast-container').remove();";
    echo "$('body').toast({position: 'top center',displayTime: 0,closeIcon: true,message: \"$message\"});";
}