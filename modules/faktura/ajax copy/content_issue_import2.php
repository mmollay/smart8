<?php
require_once ('../config.inc.php');
// require_once ('../functions.inc.php');

$_POST['activate'] = 1;

$setTEXT = $_POST['setTEXT'];
$setDelimiter = $_POST['setDelimiter'];
$update = $_POST['update'];
$import_contact = true;

// $setTemplate = "email,firstname,secondname,gender,title,company_1,company_2,client_number,city,zip,country,tel,web";
$setTemplate = array_keys($array_issue_import);

if (!$setTEXT) {
    echo "<br><br>Keine Daten zum Import vorhanden!";
    return;
}

$line = explode("\n", $setTEXT);

$count_user_first = count($line);
$line = array_unique($line);
$line = array_filter($line);

// Set Delimter "tab"
if ($setDelimiter == 'tab') {
    $setDelimiter = "\t";
}

// Auslesen der Feldnamen fuer die Benennung der Felder
$columns = $setTemplate;

if ($line) {
    foreach ($line as $value) {
        $ii = 0;
        $count++;

        // Split for templates with "delimiter"
        $array_fields = explode($setDelimiter, $value);

        foreach ($array_fields as $fields) {
            $send_array[$array_fields[0]][$columns[$ii]] = $fields;

            if ($columns[$ii] == 'date_create') {
                $date_obj = DateTime::createFromFormat('d/m/Y', $fields);
                $fields = $date_obj->format('Y-m-d'); // Outputs: 2020-10-12
            }

            $fields = trim($fields);
            $GLOBALS[$columns[$ii]] = $GLOBALS['mysqli']->real_escape_string($fields);
            $ii++;
        }

        if ($account_id) {
            // Auslesen der Prozent aus der Datenbank
            $tax = mysql_singleoutput("SELECT tax FROM accounts WHERE account_id = '$account_id' ");
        } else {
            $tax = 0;
            $account_id = 0;
        }

        // auslesen und erzeugen eine neuen fortlaufenden Nummer
        $bill_number = mysql_singleoutput("SELECT MAX(bill_number) as bill_number FROM issues  ", "bill_number") + 1; // WHERE company_id = '{$_SESSION['faktura_company_id']}'

        $brutto = round(preg_replace('/,/', '.', $brutto), 2);

        if ($tax) {
            $netto = round($brutto / (100 + $tax) * 100);
        } else
            $netto = $brutto;

        $mwst = $brutto - $netto;

        $array_descriptions = array($description, $description2, $description3, $description4);
        $set_description = '';
        foreach ($array_descriptions as $value_description) {

            if ($set_description && $value_description)
                $set_description .= " | ";
            $set_description .= $value_description;
        }

        //max 255 Zeichen
        $set_description = substr($set_description, 0, 255);

        $amazon_order_nr = $description4;

        // Get Account_id (Zuweisung über array Verknüpfung in der Config Amazon-Family zu Account_ID (Issues)
        $account_id = searchForId($description2, $array_amazon_family_to_account);

        $sql = "INSERT INTO issues SET
        company_id  = '{$_SESSION['faktura_company_id']}',
        company_1   = '',
        client_id   = 0,
        bill_number = '$bill_number',
        date_create = '$date_create',
        date_booking= '$date_create',
        account     = $account_id,
        `description`    = '$set_description',
        netto       = '$netto',
        brutto      = '$brutto',
        mwst        = '$mwst',
        tax         = $tax,
        comment     = '$comment',
        elba_id     = 0,
        amazon_order_nr  = '$amazon_order_nr' 
        ";

        $GLOBALS['mysqli']->query($sql) or die (mysqli_error($GLOBALS['mysqli']));
    }
    echo "<b>Import von $count Rechnung(en) abgeschlossen:</b><br><br>";
}
