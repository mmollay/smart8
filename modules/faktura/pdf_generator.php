<?
header('Content-Type: text/html; Charset-utf-8');
/*
 * mm@ssi.at am 05.03.2012
 * UPDATE: Firmenkopf angepasst auf neuen Standard
 */
include (__DIR__ . '/f_config.php');

/**
 * ***********************************************************
 * //Standardtexte
 * /************************************************************
 */
$default_text['kundennr'] = 'Kundennummer:';
$default_text['datum'] = 'Datum:';
$default_text['maturity'] = 'Zu zahlen bis';

$default_text['lieferdatum'] = 'Lieferdatum:';
$default_text['versand_vorgabe'] = 'Lieferart:';
$default_text['ge_ust'] = 'KD.Ust-ID:';

$default_text['rechnung'] = 'Rechnung';
$default_text['angebot'] = 'Angebot';
// Standardtext bei der Rechungsaufschluesselung
$default_text['pos'] = 'Pos';
$default_text['menge'] = 'Menge';
$default_text['text'] = 'Text';
$default_text['einzelpreis'] = 'Einzelpreis';
$default_text['gesamtpreis'] = 'Gesamtpreis';
$default_text['rabatt'] = 'Rabatt';
$default_text['gesamtbetrag'] = 'Gesamtbetrag';
$default_text['zwischensumme'] = 'Zwischensumme';
$default_text['gesamtrabatt'] = "abzgl. {gesamtrabatt} % Gesamtrabatt";
// $default_text['gesamt_netto'] = 'Gesamt Netto';
$default_text['gesamt_netto'] = 'Summe';
$default_text['zzgl_0'] = 'zzgl. 0,00 % Ust.';
$default_text['zzgl_10'] = 'zzgl. 10,00 % Ust.';
$default_text['zzgl_12'] = 'zzgl. 12,00 % Ust.';
$default_text['zzgl_20'] = 'zzgl. 20,00 % Ust.';
$default_text['steuerfrei'] = 'innergemeinschafltiche steuerfreie Lieferung gem. Art.6 Abs.1iVm Art.7 UstG';
$default_text['steuerfrei2'] = 'steuerfrei';
$default_text['ara_gesamt'] = 'ARA Gesamt';
$default_text['frachtkosten'] = 'Frachtkosten';
// Textausgabe nach der Rechnungsaufstellung
$default_text['lieferanschrift'] = 'Lieferanschrift';
$default_text['lieferscheinnr'] = 'Lieferscheinnr.';

if ($_POST['bill'])
    $bill_id = $_POST['bill'];

if ($_GET['bill'])
    $bill_id = $_GET['bill'];

if (!$bill_id) {
    echo "Rechnungsnummer ist nicht definiert.";
    exit();
}

$bill_array = array();


/**
 * ***********************************************************
 * Auslesen der eignen Firmendaten fuer den Firmenkopf
 * ************************************************************
 */
// db_ausgeben($id_firma,bills5);

// Multi print
// SELECT * FROM bills where company_id = '{$_SESSION['faktura_company_id']}' $mysql_list_filter (old version with Filterfunction)
if ($bill_id == 'all') {
    $sql_bill = $GLOBALS['mysqli']->query("
			SELECT * FROM bills
			WHERE remind_level = 0
			AND company_id = '{$_SESSION['faktura_company_id']}'
			AND date_booking = '0000-00-00'
			AND date_storno = '0000-00-00'
			AND (email = '' OR post = 1)
			") or die(mysqli_error($GLOBALS['mysqli']));
    while ($array = mysqli_fetch_array($sql_bill)) {
        $bill_array[] = $array['bill_id'];
    }
} else {
    $bill_array[] = $_GET['bill'];
}

// PDF HEAD - AND FOOT

require_once (__DIR__ . "/../../php_functions/fpdf/class.fpdf_table.php");
require_once (__DIR__ . "/../../php_functions/fpdf/table_def.inc");
include_once (__DIR__ . "/../../php_functions/fpdf/exfpdf.php");
include_once (__DIR__ . "/../../php_functions/fpdf/easyTable.php");

// $pdf=new FPDF_TABLE();
$pdf = new FPDF_MARTIN('P', 'mm', 'A4');

foreach ($bill_array as $bill_id) {

    $rechnung = array();
    $artikel = array();

    // (SELECT SUM(netto*count*((tax+100)/100))-(SUM(netto*count)/100*discount) FROM `bill_details` WHERE bill_id = $bill_id) brutto_total,
    // (SELECT SUM(netto*count)-(SUM(netto*count)/100*discount) FROM `bill_details` WHERE bill_id = $bill_id) netto_total,
    // (SELECT SUM(netto*count*tax/100)-(SUM(netto*count*tax/100)/100*discount) FROM `bill_details` WHERE bill_id = $bill_id) mwst_total,

    // (brutto-netto) mwst_total,
    if (!$bill_id) {
        echo "Keine Rechnungs ID vorhanden";
        exit();
    }
    // Rechnungsdaten auslesen
    $sql1 = $GLOBALS['mysqli']->query("SELECT
			netto  netto_total, no_endsummery,
			brutto brutto_total,document,
			(SELECT SUM(netto*count*tax/100)-(SUM(netto*count*tax/100)/100*discount) FROM `bill_details` WHERE bill_id = $bill_id AND tax = 10) mwst_10_total,
			(SELECT SUM(netto*count*tax/100)-(SUM(netto*count*tax/100)/100*discount) FROM `bill_details` WHERE bill_id = $bill_id AND tax = 12) mwst_12_total,
			(SELECT SUM(netto*count*tax/100)-(SUM(netto*count*tax/100)/100*discount) FROM `bill_details` WHERE bill_id = $bill_id AND tax = 20) mwst_20_total,
			gender,client_id,company_id,company_1,company_2,title,firstname,secondname,street,zip,city,client_number,uid,country,discount,text_after,date_create,bill_number,description,no_mwst
			FROM bills WHERE bill_id = '$bill_id' ") or die(mysqli_error($GLOBALS['mysqli']));
    $array1 = mysqli_fetch_array($sql1);

    $document = $array1['document'];
    $no_endsummery = $array1['no_endsummery'];
    $gender = $array1['gender'];
    $client_id = $array1['client_id'];
    $company_id = $array1['company_id'];
    if (!$company_id) {
        echo "Company_ID ist nicht definiert";
        exit();
    }
    $sql2 = $GLOBALS['mysqli']->query("SELECT * FROM company WHERE company_id = '$company_id' ") or die(mysqli_error($GLOBALS['mysqli']));
    $array2 = mysqli_fetch_array($sql2);

    $content_footer = $array2['content_footer'];

    $fa_name1 = $array2['company_1'];
    $fa_name2 = $array2['company_2'];
    $fa_strasse = $array2['street'];
    $fa_plz = $array2['zip'];
    $fa_ort = $array2['city'];
    $fa_atu = $array2['uid'];
    $fa_fax = $array2['fax'];
    $fa_tel = $array2['tel'];
    $fa_email = $array2['email'];
    $fa_internet = $array2['web'];
    $fa_fbnr = $array2['company_number'];
    $fa_bankname1 = $array2['bank_name'];
    $fa_blz1 = $array2['blz'];
    $fa_kto1 = $array2['kdo'];
    $fa_iban1 = $array2['iban'];
    $fa_bic1 = $array2['bic'];
    $fa_zvr1 = $array2['zvr'];
    $grafic_head = $array2['grafic_head'];
    $fa_gericht = $array2['of_jurisdiction'];

    if ($document == 'ang') {
        // Angebot
        $headline = $array2['ag_headline'];
        $conditions = $array2['ag_conditions'];
        if (!$headline)
            $headline = 'Angebot';
        $default_text['rechnung-title'] = 'Angebotsnummer:';
    } elseif ($document == 'ls') {
        // Lieferschein
        $headline = $array2['ls_headline'];
        $conditions = $array2['ls_conditions'];
        if (!$headline)
            $headline = 'Lieferschein';
        $default_text['rechnung-title'] = 'Lieferscheinnummer:';
    } else {
        // Rechnungen
        $headline = $array2['headline'];
        $conditions = $array2['conditions'];
        if (!$headline)
            $headline = 'Rechnung';
        $default_text['rechnung-title'] = 'Rechnungsnummer:';
    }

    // Set LOGO - HEADER for PDF-Bill
    $_SESSION['faktura_header'] = "../.." . $_SESSION['path_user'] . "user{$_SESSION['user_id']}/faktura/$company_id/" . $grafic_head;
    //$_SESSION['faktura_header'] = "../../smart_users/user{$_SESSION['user_id']}/faktura/" . $grafic_head;

    // Templatewerte
    $absenderadresse = "$fa_name1 $fa_name2 $fa_strasse, $fa_plz $fa_ort  ";

    // $kopfzeile['text1'] = "<img src=vorlage/img/logo1.gif>";
    // $kopfzeile['text2'] = "<img src=vorlage/img/logo2.gif>";

    $fusszeile['text1'] = mb_convert_encoding($absenderadresse, 'ISO-8859-1', 'UTF-8');

    $fusszeile['text2'] = '';
    if ($fa_tel)
        $fusszeile['text2'] .= "Tel.: $fa_tel   ";
    if ($fa_fax)
        $fusszeile['text2'] .= "Fax: $fa_fax   ";
    if ($fa_email)
        $fusszeile['text2'] .= "Email: $fa_email    ";
    if ($fa_interet)
        $fusszeile['text2'] .= "Internet: $fa_internet   ";

    $fusszeile['text3'] = '';
    if ($fa_atu)
        $fusszeile['text3'] .= "UID: $fa_atu   ";
    if ($ara_nummer)
        $fusszeile['text3'] .= "ARA: $ara_nummer   ";
    if ($fa_fbnr)
        $fusszeile['text3'] .= "Firmenbuchnummer: $fa_fbnr ";
    if ($fa_zvr1)
        $fusszeile['text3'] .= "ZVR Zahl: $fa_zvr1 ";
    if ($fa_gericht)
        $fusszeile['text3'] .= "Gerichtsstand: $fa_gericht ";

    $fusszeile['text4'] = '';
    if ($fa_bankname1)
        $fusszeile['text4'] .= "Bankname: $fa_bankname1   ";
    if ($fa_blz1)
        $fusszeile['text4'] .= "BLZ: $fa_blz1  ";
    if ($fa_kto1)
        $fusszeile['text4'] .= "Kto-Nr: $fa_kto1  ";
    if ($fa_iban1)
        $fusszeile['text4'] .= "IBAN: $fa_iban1  ";
    if ($fa_bic1)
        $fusszeile['text4'] .= "BIC: $fa_bic1 ";

    /**
     * ***********************************************************
     * Kundendaten auslesen
     * /************************************************************
     */
    // db_ausgeben($id,bills1);

    // Adresse Kunde
    $kunde['firma'] = trim($array1['company_1']);
    $kunde['zusatz'] = trim($array1['company_2']);

    $kunde['name'] = '';
    /*
     * Anrede (ist aber in pdf1.php derzeit deaktiviert 05.03.12)
     */
    if ($kunde['firma'] or $kunde['zusatz'])
        $kunde['anrede'] = 'Firma';
    else {
        if ($gender == 'm')
            $kunde['anrede'] = 'Herrn';
        elseif ($gender == 'f')
            $kunde['anrede'] = 'Frau';
    }

    // if ($array1['title']) $kunde['name'] .= $array1['title']." ";
    if ($array1['firstname'])
        $kunde['name'] .= $array1['firstname'] . " ";
    if ($array1['secondname'])
        $kunde['name'] .= $array1['secondname'] . " ";

    $kunde['name'] = trim($kunde['name']);


    $array_country = call_array('country');
    $array1['country'] = strtolower($array1['country']);
    $kunde['land'] = $array_country[$array1['country']];

    $kunde['strasse'] = $array1['street'];
    $kunde['plz'] = $array1['zip'];
    $kunde['ort'] = $array1['city'];
    $kunde['nummer'] = $array1['client_number'];
    $kunde['eg_ust'] = $array1['uid']; // UIDNUMMER des Kunden
    $gesamtrabatt = nr_format($array1['discount']);

    $default_text['gesamtrabatt'] = preg_replace("[{gesamtrabatt}]", $gesamtrabatt, $default_text['gesamtrabatt']);

    $text_after = $array1['text_after'];

    // Faelligkeit ausrechnen fuer Bill

    $rechnung['maturity'] = strtotime('+12 day', strtotime($array1['date_create']));
    $rechnung['maturity'] = date_mysql2german(date('Y-m-d', $rechnung['maturity']));

    $rechnung['datum'] = date_mysql2german($array1['date_create']);
    // $rechnung['maturity'] = $array1['date_create'];

    // $rechnung['lieferdatum'] = date_mysql2german($lieferdatum);
    // $rechnung['versand_vorgabe'] = $versand_vorgabe;

    if ($_SESSION['company_id'] == '31')
        $number_add = 'WTM-';
    else if ($_SESSION['company_id'] == '30')
        $number_add = 'ÖGT-';
    else
        $number_add = '';


    $rechnung['nummer'] = iconv('UTF-8', 'windows-1252', $number_add . $array1['bill_number']);
    $rechnung['nummer_pdf'] = iconv('UTF-8', 'windows-1252', $array1['bill_number']);

    $rechnung['betreff'] = $array1['description'];

    $rechnung['zahlungsbedingung'] = $conditions;
    // $rechnung['frachtkosten'] = $frachtkosten;
    $rechnung['mwst_frei'] = $array1['no_mwst'];

    // Auslesen der Lieferscheinnummer bei Rechungsausgabe
    if ($auftragsart == 3) {
        // $rechnung['lieferscheinnr']= db_wert_auslesen2('fa_auftrag','belegnummer',"where id_vorgang='$id_vorgang' and auftragsart='2'");
    }

    $anhang['text'] = '';

    if ($text_after)
        $anhang['text'] .= "$text_after\n\n";

    if ($rechnung['zahlungsbedingung']) {
        $anhang['text'] .= $rechnung['zahlungsbedingung'];
    }

    /**
     * ***********************************************************
     * Ausgabe der einzelnen Positionen
     * /************************************************************
     */

    $array_liste = $GLOBALS['mysqli']->query(" SELECT * from bill_details where bill_id='$bill_id' ");
    while ($ausgabe_liste = mysqli_fetch_array($array_liste)) {
        $i++;
        $artikel[$i]['pos'] = $i;
        $artikel[$i]['menge'] = nr_format($ausgabe_liste['count']);
        $artikel[$i]['einheit'] = $ausgabe_liste['format'];
        $artikel[$i]['text1'] = $ausgabe_liste['art_title'];
        $artikel[$i]['tax'] = $ausgabe_liste['tax'] . "%";
        // if ($id_vorlage == 2)
        $artikel[$i]['text1'] .= "\n" . $ausgabe_liste['art_text'];
        $ausgabe_liste['art_text'];
        // Ausgabe von Rabatt
        // $ausgabe_liste['rabatt'] = "10";

        // Berechnung des Rabattpreises
        $artikel[$i]['rabatt_preis'] = $ausgabe_liste['netto'] / 100 * $ausgabe_liste['rabatt'];
        // Berechung der einzelnen Artikel inkl. Abzug von Rabatt
        $artikel[$i]['einzelgesamtpreis'] = ($ausgabe_liste['netto'] - $artikel[$i]['rabatt_preis']) * $ausgabe_liste['count'];
        // Formatierung fuer die Ausage
        $artikel[$i]['einzelpreis'] = nr_format($ausgabe_liste['netto']);
        $artikel[$i]['gesamtpreis'] = nr_format($artikel[$i]['einzelgesamtpreis']);
        // Summenbildung
        $rechnung['gesamt_netto'] += $artikel[$i]['einzelgesamtpreis'];
        $rechnung['ara_preis_summe'] += $ausgabe_liste['ara_preis'] * $ausgabe_liste['menge'];
        $artikel[$i]['menge'] = nr_format($ausgabe_liste['count']);
        $artikel[$i]['rabatt'] = nr_format($ausgabe_liste['rabatt']) . "%";
        if ($ausgabe_liste['rabatt'] > 0)
            $set_rapatt_header = 1;
    }

    $rechnung['ara_gesamt'] = nr_format($rechnung['ara_preis_summe']);

    /**
     * *********************************************************************
     * Zusatzberrechung Abzug GesamtRabatt
     * *********************************************************************
     */
    if ($gesamtrabatt and $rechnung['gesamt_netto']) {
        $rechnung['zwischensumme'] = nr_format($rechnung['gesamt_netto']);
        $rechnung['gesamtrabatt'] = $rechnung['gesamt_netto'] / 100 * $gesamtrabatt;
        $rechnung['gesamt_netto'] -= $rechnung['gesamtrabatt'];
        $rechnung['format_gesamtrabatt'] = "-" . nr_format($rechnung['gesamtrabatt']);
    }

    /**
     * ********************************************************************
     * //Berechnung der Summe und der Mwst.
     * und Formatierung der Ausgabewerte
     * /*********************************************************************
     */

    // Summenbildung fuer Zusaetze (ARA,Frachtkosten)
    // $zusatz_summe = $rechnung['frachtkosten']+$rechnung['ara_preis_summe'];
    // if ($rechnung['frachtkosten']) $rechnung['frachtkosten'] = nr_format($rechnung['frachtkosten']);

    // Steuer wird dazugerechnet
    if (!$rechnung['mwst_frei']) {
        $default_text['eg_ust_10'] = $default_text['zzgl_10'];
        $default_text['eg_ust_12'] = $default_text['zzgl_12'];
        $default_text['eg_ust_20'] = $default_text['zzgl_20'];
        // $rechnung['zzgl_20'] = ($rechnung['gesamt_netto']+$zusatz_summe)/100*20;
        // $zusatz_summe_brutto = $zusatz_summe;
    }
    /*
     * elseif ($rechnung['mwst_frei']){
     * $rechnung['zzgl_20'] = '';
     * //$zusatz_summe_brutto = $zusatz_summe;
     * }
     * else {
     * /*
     * if ($id_vorlage==2)
     * $default_text['eg_ust'] = $default_text['steuerfrei2'];
     * else
     * $default_text['eg_ust'] = $default_text['steuerfrei'];
     */
    // $rechnung['zzgl_20'] = '';
    // }

    // $rechnung['gesamt_brutto'] = nr_format($rechnung['gesamt_netto']+$rechnung['zzgl_20']+$zusatz_summe_brutto);
    // $rechnung['gesamt_netto'] = nr_format($rechnung['gesamt_netto']+$zusatz_summe);
    // $rechnung['zzgl_20'] = nr_format($rechnung['zzgl_20']);

    $rechnung['zzgl_10'] = nr_format($array1['mwst_10_total']);
    $rechnung['zzgl_12'] = nr_format($array1['mwst_12_total']);
    $rechnung['zzgl_20'] = nr_format($array1['mwst_20_total']);
    $rechnung['gesamt_netto'] = nr_format($array1['netto_total']);
    $rechnung['gesamt_brutto'] = nr_format($array1['brutto_total']);
    // $rechnung['gesamt_brutto'] = nr_format($array1['brutto_total']);

    $re_kuerzel = strtoupper($document);

    $pdf_dateiname = $kunde['nummer'] . "-" . $re_kuerzel . "-" . $rechnung['nummer_pdf'] . '.pdf';

    // Werte in das Templates uebertragen
    include ("pdf1.php");
}

if (!$pdf_output['modus'])
    $pdf_output['modus'] = 'I';

$pdf->Output($pdf_output['path'] . $pdf_dateiname, $pdf_output['modus']);


function call_array($key)
{
    $array['color'] = array(
        'tranparent' => '<a class="ui tranparent empty circular label"></a>Transparent',
        'basic' => '<a class="ui basic empty circular label"></a>Basic',
        'teal' => '<a class="ui teal empty circular label"></a>Teal',
        'orange' => '<a class="ui orange empty circular label"></a>Orange',
        'yellow' => '<a class="ui yellow empty circular label"></a>Gelb',
        'olive' => '<a class="ui olive empty circular label"></a>Olive',
        'green' => '<a class="ui green empty circular label"></a>Grün',
        'blue' => '<a class="ui blue empty circular label"></a>Blau',
        'violet' => '<a class="ui violet empty circular label"></a>Violet',
        'purple' => '<a class="ui purple empty circular label"></a>Purple',
        'pink' => '<a class="ui pink empty circular label"></a>Pink',
        'brown' => '<a class="ui brown empty circular label"></a>Braun',
        'red' => '<a class="ui red empty circular label"></a>Rot',
        'grey' => '<a class="ui grey empty circular label"></a>Grau',
        'black' => '<a class="ui black empty circular label"></a>Schwarz'
    );

    $array['timezone'] = DateTimeZone::listIdentifiers();

    $array['country'] = array(
        'at' => 'Österreich',
        'de' => 'Deutschland',
        'ch' => 'Schweiz',
        'af' => 'Afghanistan',
        'ax' => 'Aland Islands',
        'al' => 'Albania',
        'dz' => 'Algeria',
        'as' => 'American Samoa',
        'ad' => 'Andorra',
        'ao' => 'Angola',
        'ai' => 'Anguilla',
        'ag' => 'Antigua',
        'ar' => 'Argentina',
        'am' => 'Armenia',
        'aw' => 'Aruba',
        'au' => 'Australia',
        'at' => 'Österreich',
        'az' => 'Azerbaijan',
        'bs' => 'Bahamas',
        'bh' => 'Bahrain',
        'bd' => 'Bangladesh',
        'bb' => 'Barbados',
        'by' => 'Belarus',
        'be' => 'Belgium',
        'bz' => 'Belize',
        'bj' => 'Benin',
        'bm' => 'Bermuda',
        'bt' => 'Bhutan',
        'bo' => 'Bolivia',
        'ba' => 'Bosnia',
        'bw' => 'Botswana',
        'bv' => 'Bouvet Island',
        'br' => 'Brazil',
        'vg' => 'British Virgin Islands',
        'bn' => 'Brunei',
        'bg' => 'Bulgaria',
        'bf' => 'Burkina Faso',
        'ar' => 'Burma',
        'bi' => 'Burundi',
        'tc' => 'Caicos Islands',
        'kh' => 'Cambodia',
        'cm' => 'Cameroon',
        'ca' => 'Canada',
        'cv' => 'Cape Verde',
        'ky' => 'Cayman Islands',
        'cf' => 'Central African Republic',
        'td' => 'Chad',
        'cl' => 'Chile',
        'cn' => 'China',
        'cx' => 'Christmas Island',
        'cc' => 'Cocos Islands',
        'co' => 'Colombia',
        'km' => 'Comoros',
        'cg' => 'Congo Brazzaville',
        'cd' => 'Congo',
        'ck' => 'Cook Islands',
        'cr' => 'Costa Rica',
        'cici' => 'Cote Divoire',
        'hr' => 'Croatia',
        'cu' => 'Cuba',
        'cy' => 'Cyprus',
        'cz' => 'Czech Republic',
        'dk' => 'Denmark',
        'dj' => 'Djibouti',
        'dm' => 'Dominica',
        'do' => 'Dominican Republic',
        'ec' => 'Ecuador',
        'eg' => 'Egypt',
        'sv' => 'El Salvador',
        'gb' => 'England',
        'gq' => 'Equatorial Guinea',
        'er' => 'Eritrea',
        'ee' => 'Estonia',
        'et' => 'Ethiopia',
        'eu' => 'European Union',
        'fk' => 'Falkland Islands',
        'fo' => 'Faroe Islands',
        'fj' => 'Fiji',
        'fi' => 'Finland',
        'fr' => 'France',
        'gf' => 'French Guiana',
        'pf' => 'French Polynesia',
        'tf' => 'French Territories',
        'ga' => 'Gabon',
        'gm' => 'Gambia',
        'ge' => 'Georgia',
        'de' => 'Deutschland',
        'gh' => 'Ghana',
        'gi' => 'Gibraltar',
        'gr' => 'Greece',
        'gl' => 'Greenland',
        'gd' => 'Grenada',
        'gp' => 'Guadeloupe',
        'gu' => 'Guam',
        'gt' => 'Guatemala',
        'gw' => 'Guinea-Bissau',
        'gn' => 'Guinea',
        'gy' => 'Guyana',
        'ht' => 'Haiti',
        'hm' => 'Heard Island',
        'hn' => 'Honduras',
        'hk' => 'Hong Kong',
        'hu' => 'Hungary',
        'is' => 'Iceland',
        'in' => 'India',
        'io' => 'Indian Ocean Territory',
        'id' => 'Indonesia',
        'ir' => 'Iran',
        'iq' => 'Iraq',
        'ie' => 'Ireland',
        'il' => 'Israel',
        'it' => 'Italy',
        'jm' => 'Jamaica',
        'jp' => 'Japan',
        'jo' => 'Jordan',
        'kz' => 'Kazakhstan',
        'ke' => 'Kenya',
        'ki' => 'Kiribati',
        'kw' => 'Kuwait',
        'kg' => 'Kyrgyzstan',
        'la' => 'Laos',
        'lv' => 'Latvia',
        'lb' => 'Lebanon',
        'ls' => 'Lesotho',
        'lr' => 'Liberia',
        'ly' => 'Libya',
        'li' => 'Liechtenstein',
        'lt' => 'Lithuania',
        'lu' => 'Luxembourg',
        'mo' => 'Macau',
        'mk' => 'Macedonia',
        'mg' => 'Madagascar',
        'mw' => 'Malawi',
        'my' => 'Malaysia',
        'mv' => 'Maldives',
        'ml' => 'Mali',
        'mt' => 'Malta',
        'mh' => 'Marshall Islands',
        'mq' => 'Martinique',
        'mr' => 'Mauritania',
        'mu' => 'Mauritius',
        'yt' => 'Mayotte',
        'mx' => 'Mexico',
        'fm' => 'Micronesia',
        'md' => 'Moldova',
        'mc' => 'Monaco',
        'mn' => 'Mongolia',
        'me' => 'Montenegro',
        'ms' => 'Montserrat',
        'ma' => 'Morocco',
        'mz' => 'Mozambique',
        'na' => 'Namibia',
        'nr' => 'Nauru',
        'np' => 'Nepal',
        'an' => 'Netherlands Antilles',
        'nl' => 'Netherlands',
        'nc' => 'New Caledonia',
        'pg' => 'New Guinea',
        'nz' => 'New Zealand',
        'ni' => 'Nicaragua',
        'ne' => 'Niger',
        'ng' => 'Nigeria',
        'nu' => 'Niue',
        'nf' => 'Norfolk Island',
        'kp' => 'North Korea',
        'mp' => 'Northern Mariana Islands',
        'no' => 'Norway',
        'om' => 'Oman',
        'pk' => 'Pakistan',
        'pw' => 'Palau',
        'ps' => 'Palestine',
        'pa' => 'Panama',
        'py' => 'Paraguay',
        'pe' => 'Peru',
        'ph' => 'Philippines',
        'pn' => 'Pitcairn Islands',
        'pl' => 'Poland',
        'pt' => 'Portugal',
        'pr' => 'Puerto Rico',
        'qa' => 'Qatar',
        're' => 'Reunion',
        'ro' => 'Romania',
        'ru' => 'Russia',
        'rw' => 'Rwanda',
        'sh' => 'Saint Helena',
        'kn' => 'Saint Kitts and Nevis',
        'lc' => 'Saint Lucia',
        'pm' => 'Saint Pierre',
        'vc' => 'Saint Vincent',
        'ws' => 'Samoa',
        'sm' => 'San Marino',
        'gs' => 'Sandwich Islands',
        'st' => 'Sao Tome',
        'sa' => 'Saudi Arabia',
        'sn' => 'Senegal',
        'cs' => 'Serbia',
        'rs' => 'Serbia',
        'sc' => 'Seychelles',
        'sl' => 'Sierra Leone',
        'sg' => 'Singapore',
        'sk' => 'Slovakia',
        'si' => 'Slovenia',
        'sb' => 'Solomon Islands',
        'so' => 'Somalia',
        'za' => 'South Africa',
        'kr' => 'South Korea',
        'es' => 'Spain',
        'lk' => 'Sri Lanka',
        'sd' => 'Sudan',
        'sr' => 'Suriname',
        'sj' => 'Svalbard',
        'sz' => 'Swaziland',
        'se' => 'Sweden',
        'ch' => 'Switzerland',
        'sy' => 'Syria',
        'tw' => 'Taiwan',
        'tj' => 'Tajikistan',
        'tz' => 'Tanzania',
        'th' => 'Thailand',
        'tl' => 'Timorleste',
        'tg' => 'Togo',
        'tk' => 'Tokelau',
        'to' => 'Tonga',
        'tt' => 'Trinidad',
        'tn' => 'Tunisia',
        'tr' => 'Turkey',
        'tm' => 'Turkmenistan',
        'tv' => 'Tuvalu',
        'ug' => 'Uganda',
        'ua' => 'Ukraine',
        'ae' => 'United Arab Emirates',
        'us' => 'United States',
        'uy' => 'Uruguay',
        'um' => 'Us Minor Islands',
        'vi' => 'Us Virgin Islands',
        'uz' => 'Uzbekistan',
        'vu' => 'Vanuatu',
        'va' => 'Vatican City',
        've' => 'Venezuela',
        'vn' => 'Vietnam',
        'wf' => 'Wallis and Futuna',
        'eh' => 'Western Sahara',
        'ye' => 'Yemen',
        'zm' => 'Zambia',
        'zw' => 'Zimbabwe'
    );

    return $array[$key];
}