<?
include(__DIR__ . '/functions.inc.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$username = 'smart';
$password = 'Eiddswwenph21;';
$dbname = 'ssi_faktura';

$db = $connection = $GLOBALS['mysqli'] = mysqli_connect($host, $username, $password, $dbname);

//Select the database
if (!mysqli_select_db($db, $dbname)) {
    die('Datenbankauswahl fehlgeschlagen: ' . mysqli_error($db));
}

$_SESSION['user_id'] = $_SESSION['faktura_company_id'] = '40';

// Aktuelles Jahr
$year = date('Y');


if ($_SESSION['user_id'] == '40') {
    $db_faktura = "ssi_faktura";
} else
    $db_faktura = "ssi_faktura" . $_SESSION['user_id'];

// OEGT - Modus
if (isset($_SESSION['user_id']) == '94' and isset($_SESSION['faktura_company_id']) == '30') {
    $oegt_modus = true;
}

if (!mysqli_select_db($db, $db_faktura)) {
    die('Datenbankauswahl fehlgeschlagen: ' . mysqli_error($db));
}

// HACK - soll nur bei Server1 laufen
if (isset($_SESSION['user_id']) == '94' and isset($_SESSION['faktura_company_id']) == '30') {
    $GLOBALS['mysqli']->set_charset('utf8');
}

if (isset($_SERVER['SERVER_NAME']) == 'server1.ssi.at') {
    $GLOBALS['mysqli']->set_charset('utf8');
}

// Wenn noch keine Firma gewählt wurde die Defaultmässig eine gewählt
if (!isset($_SESSION['faktura_company_id'])) {
    $sql_company = "SELECT company_id, company_1 FROM company WHERE user_id = ?";
    $stmt = $GLOBALS['mysqli']->prepare($sql_company);
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['faktura_company_id'] = $row['company_id'];
    } else {
        // Keine Firma gefunden, nichts tun.
    }

    $stmt->close();
}

/**
 * ARRAY - COMPANIES
 */
$sql_company = $GLOBALS['mysqli']->query("SELECT company_id, company_1, grafic_head FROM company where user_id = '{$_SESSION['user_id']}'") or die(mysqli_error($GLOBALS['mysqli']));
while ($sql_array = mysqli_fetch_array($sql_company)) {
    $grafic_head = $sql_array['grafic_head'];
    $company_array[$sql_array['company_id']] = $sql_array['company_1']; // <img src='$grafic_head' class='ui mini image'>";
}

/**
 * ARRAY - FORMAT
 */
$sql_query2 = $GLOBALS['mysqli']->query("SELECT format FROM article_temp GROUP by format") or die(mysqli_error($GLOBALS['mysqli']));
while ($sql_array2 = mysqli_fetch_array($sql_query2)) {
    $format_array[] = $sql_array2['format'];
}

/**
 * ARRAY - ACCOUNTS
 */
$sql_query = $GLOBALS['mysqli']->query("SELECT account_id, title, tax, company_id,
	(SELECT company_1 FROM company where company.company_id = accounts.company_id ) company
	FROM accounts WHERE `option` = 'in' order by accounts.company_id ") or die(mysqli_error($GLOBALS['mysqli'])); // and company_id = '{$_SESSION['faktura_company_id']}'
while ($sql_array = mysqli_fetch_array($sql_query)) {
    // $account_array[$sql_array['account_id']] = "<div class='ui label mini'>" . $sql_array['company'] . "</div> " . $sql_array['title'] . "(" . $sql_array['tax'] . "%)";
    $account_array[$sql_array['account_id']] = $sql_array['title'] . " (" . $sql_array['tax'] . "%)";
}

/**
 * Article - Group
 */
$query = $GLOBALS['mysqli']->query("
	SELECT article_group.group_id group_id,
		(SELECT COUNT(article2group.group_id) FROM article2group WHERE article_group.group_id = article2group.group_id) count , title FROM
		article_group
			
	") or die(mysqli_error($GLOBALS['mysqli']));
while ($array = mysqli_fetch_array($query)) {
    $group_array[$array['group_id']] = $array['title'] . " (" . $array['count'] . ")";
}

/**
 * ARRAY - YEAR
 */

$array_year[''] = 'Alle Jahre';
for ($ii = date("Y", strtotime('+1 year')); $ii > 2011; $ii--) {
    $array_year['DATE_FORMAT(date_create,"%Y") = "' . $ii . '"'] = $ii;
}

/**
 * ARRAY - MONTH
 */
$array_filter_month = array('DATE_FORMAT(date_create,"%m") = "01"' => 'Jänner', 'DATE_FORMAT(date_create,"%m") = "02"' => 'Februar', 'DATE_FORMAT(date_create,"%m") = "03"' => 'März', 'DATE_FORMAT(date_create,"%m") = "04"' => 'April', 'DATE_FORMAT(date_create,"%m") = "05"' => 'Mai', 'DATE_FORMAT(date_create,"%m") = "06"' => 'Juni', 'DATE_FORMAT(date_create,"%m") = "07"' => 'Juli', 'DATE_FORMAT(date_create,"%m") = "08"' => 'Ausgust', 'DATE_FORMAT(date_create,"%m") = "09"' => 'September', 'DATE_FORMAT(date_create,"%m") = "10"' => 'Oktober', 'DATE_FORMAT(date_create,"%m") = "11"' => 'November', 'DATE_FORMAT(date_create,"%m") = "12"' => 'Dezember');

/**
 * ARRAY - Document-Status
 */
$document_array = array(
    'ang' => 'Angebot',
    // 'ls' => 'Lieferschein' ,
    'rn' => 'Rechnung'
);

$array_mwst = array('0' => '0% Mwst.', '10' => '10% Mwst.', '20' => '20% Mwst.');

$_SESSION['default_year'] = date("Y");

$array_account_number = array('AT533293700000608745' => 'Firma (60 8745)', 'AT793293700000606822' => 'Privat (60 6822)');

// Import - Parameter
$array_import = array("email" => "Email", "firstname" => "Vorname", "secondname" => "Nachnname", "gender" => "Gender{f=Frau,m=Herr,c=Firma}", "title" => "Title", "company_1" => "Firma 1", "company_2" => "Firma 2", "client_number" => "Kundennummer", "street" => "Straße", "zip" => "PLZ", "city" => "Stadt", "country" => "Land", "tel" => "Telefon", "web" => "Internet", "birth" => "Beboren");

// Issues - Import - Parameter
$array_issue_import = array("date_create" => "Datum", "brutto" => "Betrag", "description" => "Beschreibung 1", "description2" => "Beschreibung 2", "description3" => "Beschreibung3", "description4" => "Beschreibung 4", "amazon_order_nr" => "Bestellnummer (Amazon)", "account" => "Konto ID");

// Get set_sort from import placeholder
if (isset($_COOKIE['array_template_import_faktura'])) {
    if ($_COOKIE['array_template_import_faktura']) {
        $array_import1 = explode(',', $_COOKIE['array_template_import_faktura']);
        foreach ($array_import1 as $key => $value) {
            $set_array_import[$value] = $array_import[$value];
        }
    }
    $array_import = $set_array_import;
}

for ($ii = date("Y"); $ii > 2011; $ii--) {
    $array_year_finance[$ii] = $ii;
}

/*
 * Userconfig
 */

if ($_SESSION['user_id'] == '40') {
    $show_menu['finance_status'] = true;
    $show_menu['finance_output'] = true;
    $show_menu['smart'] = true;
    $show_menu['todo'] = true;
    $_SESSION['page_id'] = $page_id = 29;
}

if ($_SESSION['user_id'] == '65') {
    $show_menu['finance_status'] = true;
    $show_menu['finance_output'] = true;
    $_SESSION['page_id'] = $page_id = 29;
}

// OEGT 94
if ($_SESSION['user_id'] == '94') {
    include_once('oegt/lang/de.inc.php');
    $_SESSION['page_id'] = $page_id = 143;
    $show_menu['finance_output'] = false;
    $str_date_for_generate_pre = "01-11"; // Day-Month - Generierung ab dem 11ten Monat möglich
    // $show_menu['todo'] = true;
}

if ($_SESSION['user_id'] == '1287') {
    $show_menu['finance_output'] = true;
}

if ($_SESSION['user_id'] == '1080') {
    $_SESSION['page_id'] = $page_id = 89;
}
// Pfad für den Explorer für das verknüpfen der Artikel mit PDFs
$_SESSION['explorer_folder'] = "../../smart_users/user{$_SESSION['user_id']}/explorer/$page_id/";

$array_account = array(
    14 => "Internet und Telefon 20",
    16 => "Lesematerial 10",
    17 => "Outdoormaterial 20",
    65 => "Yoga 10%",
    32 => "Provisionen 20",
    35 => "Büromaterial 0",
    38 => "Büro-, Werbe- und Repräsentations- aufwand 10",
    117 => "Büro-, Werbe- und Repräsentations- aufwand 0",
    41 => "Investitionen > 400 20",
    133 => "Handelsgut 0",
    42 => "Handelsgut 20",
    44 => "Investitionen < 400 (GWG) 20",
    45 => "Survival 0",
    94 => "Survival 10",
    57 => "Survival 20",
    62 => "Yoga 20% 20",
    63 => "Yoga 0% 0",
    70 => "Internet / Telefon 0",
    69 => "Garten 10",
    71 => "Garten 20",
    74 => "Obststadt 0",
    75 => "Obststadt 20",
    93 => "Obststadt 10",
    125 => "Hostel 20",
    126 => "Hostel 0",
    134 => "Fortbildung 0"
);

$array_amazon_family_to_accopunt_old = array(
    ["id" => 1, "account_id" => 126, "name" => 'Armaturen'],
    ["id" => 2, "account_id" => 134, "name" => 'Audio- und visuelle Präsentations- und Kompositionsausrüstung'],
    ["id" => 3, "account_id" => 45, "name" => 'Ausrüstung und Zubehör für Verteidigung und Strafverfolgung sowie Sicherheit und Schutz'],
    ["id" => 4, "account_id" => 133, "name" => 'Batterien, Generatoren und kinetische Kraftübertragung'],
    ["id" => 5, "account_id" => 126, "name" => 'Baumarkt'],
    ["id" => 6, "account_id" => 126, "name" => 'Baumaschinen und Zubehör'],
    ["id" => 7, "account_id" => 45, "name" => 'Bekleidung'],
    ["id" => 8, "account_id" => 126, "name" => 'Beleuchtungskörper und Zubehör'],
    ["id" => 9, "account_id" => 126, "name" => 'Bettwäsche und Tisch- und Küchentextilien sowie Handtücher'],
    ["id" => 10, "account_id" => 45, "name" => 'Brot und Backwaren'],
    ["id" => 11, "account_id" => 117, "name" => 'Bürobedarf'],
    ["id" => 12, "account_id" => 117, "name" => 'Bürogeräte, -material und Zubehör'],
    ["id" => 13, "account_id" => 45, "name" => 'Camping- und Outdoorausrüstung und Zubehör'],
    ["id" => 14, "account_id" => 117, "name" => 'Computerausstattung und Zubehör'],
    ["id" => 15, "account_id" => 117, "name" => 'Elektrische Ausrüstung, Komponente und Zubehör'],
    ["id" => 16, "account_id" => 45, "name" => 'Fitnessgeräte'],
    ["id" => 17, "account_id" => 45, "name" => 'Gepäck, Handtaschen, Rucksäcke und Koffer'],
    ["id" => 18, "account_id" => 126, "name" => 'Haushaltsgeräte'],
    ["id" => 19, "account_id" => 126, "name" => 'Haushaltsküchengeschirr und Küchenbedarf'],
    ["id" => 20, "account_id" => 126, "name" => 'Heizung, Lüftung und Luftzirkulation'],
    ["id" => 21, "account_id" => 126, "name" => 'Industriepumpen und Kompressoren'],
    ["id" => 22, "account_id" => 126, "name" => 'Kleb- und Dichtmittel'],
    ["id" => 23, "account_id" => 117, "name" => 'Kommunikationsgeräte und Zubehör'],
    ["id" => 24, "account_id" => 117, "name" => "Komponenten für Informationstechnologie, Rundfunk und Telekommunikation"],
    ["id" => 25, "account_id" => 45, "name" => "Körperpflegeprodukte"],
    ["id" => 26, "account_id" => 117, "name" => "Lampen, Glühbirnen und Lampenzubehör"],
    ["id" => 27, "account_id" => 45, "name" => "Lebensmittel Getränke und Tabakwaren"],
    ["id" => 28, "account_id" => 45, "name" => "Leichte Waffen und Munition"],
    ["id" => 29, "account_id" => 45, "name" => "Nahrungsergänzungsmittel"],
    ["id" => 30, "account_id" => 45, "name" => "Nüsse und Samen"],
    ["id" => 31, "account_id" => 126, "name" => "Produkte zur Schädlingsbekämpfung"],
    ["id" => 32, "account_id" => 45, "name" => "Schuhe"],
    ["id" => 33, "account_id" => 126, "name" => "Schweiß-, Löt- und Hartlötmaschinen, Verbrauchsmaterialien und Zubehör"],
    ["id" => 34, "account_id" => 126, "name" => "Seile, Ketten, Kabel, Drähte und Gurte"],
    ["id" => 35, "account_id" => 126, "name" => "Sicherheitsüberwachung und -erkennung"],
    ["id" => 36, "account_id" => 45, "name" => "Sport- und Freizeitausrüstung, -bedarf und -zubehör"],
    ["id" => 37, "account_id" => 117, "name" => "Sprachdaten- oder Multimedianetzwerkausstattung oder Plattformen und Zubehör"],
    ["id" => 38, "account_id" => 126, "name" => "Transportkomponenten und -systeme"],
    ["id" => 39, "account_id" => 117, "name" => "Unterhaltungselektronik"],
    ["id" => 40, "account_id" => 126, "name" => "Unterkunftsmöbel"],
    ["id" => 41, "account_id" => 126, "name" => "Werkzeug"]
);

$array_amazon_family_to_account = array(
    ["id" => 1, "account_id" => 126, "name" => 'Armaturen'],
    ["id" => 2, "account_id" => 134, "name" => 'Audio- und visuelle Präsentations- und Kompositionsausrüstung'],
    ["id" => 3, "account_id" => 45, "name" => 'Ausrüstung und Zubehör für Verteidigung und Strafverfolgung sowie Sicherheit und Schutz'],
    ["id" => 4, "account_id" => 133, "name" => 'Batterien, Generatoren und kinetische Kraftübertragung'],
    ["id" => 5, "account_id" => 126, "name" => 'Baumarkt'],
    ["id" => 6, "account_id" => 126, "name" => 'Baumaschinen und Zubehör'],
    ["id" => 7, "account_id" => 45, "name" => 'Bekleidung'],
    ["id" => 8, "account_id" => 126, "name" => 'Beleuchtungskörper und Zubehör'],
    ["id" => 9, "account_id" => 126, "name" => 'Bettwäsche und Tisch- und Küchentextilien sowie Handtücher'],
    ["id" => 10, "account_id" => 45, "name" => 'Brot und Backwaren'],
    ["id" => 11, "account_id" => 117, "name" => 'Bürobedarf'],
    ["id" => 12, "account_id" => 117, "name" => 'Bürogeräte, -material und Zubehör'],
    ["id" => 13, "account_id" => 45, "name" => 'Camping- und Outdoorausrüstung und Zubehör'],
    ["id" => 14, "account_id" => 117, "name" => 'Computerausstattung und Zubehör'],
    ["id" => 15, "account_id" => 117, "name" => 'Elektrische Ausrüstung, Komponente und Zubehör'],
    ["id" => 16, "account_id" => 45, "name" => 'Fitnessgeräte'],
    ["id" => 17, "account_id" => 45, "name" => 'Gepäck, Handtaschen, Rucksäcke und Koffer'],
    ["id" => 18, "account_id" => 126, "name" => 'Haushaltsgeräte'],
    ["id" => 19, "account_id" => 126, "name" => 'Haushaltsküchengeschirr und Küchenbedarf'],
    ["id" => 20, "account_id" => 126, "name" => 'Heizung, Lüftung und Luftzirkulation'],
    ["id" => 21, "account_id" => 126, "name" => 'Industriepumpen und Kompressoren'],
    ["id" => 22, "account_id" => 126, "name" => 'Kleb- und Dichtmittel'],
    ["id" => 23, "account_id" => 117, "name" => 'Kommunikationsgeräte und Zubehör'],
    ["id" => 24, "account_id" => 117, "name" => "Komponenten für Informationstechnologie, Rundfunk und Telekommunikation"],
    ["id" => 25, "account_id" => 45, "name" => "Körperpflegeprodukte"],
    ["id" => 26, "account_id" => 117, "name" => "Lampen, Glühbirnen und Lampenzubehör"],
    ["id" => 27, "account_id" => 45, "name" => "Lebensmittel Getränke und Tabakwaren"],
    ["id" => 28, "account_id" => 45, "name" => "Leichte Waffen und Munition"],
    ["id" => 29, "account_id" => 45, "name" => "Nahrungsergänzungsmittel"],
    ["id" => 30, "account_id" => 45, "name" => "Nüsse und Samen"],
    ["id" => 31, "account_id" => 126, "name" => "Produkte zur Schädlingsbekämpfung"],
    ["id" => 32, "account_id" => 45, "name" => "Schuhe"],
    ["id" => 33, "account_id" => 126, "name" => "Schweiß-, Löt- und Hartlötmaschinen, Verbrauchsmaterialien und Zubehör"],
    ["id" => 34, "account_id" => 34, "name" => "Seile, Ketten, Kabel, Drähte und Gurte"],
    ["id" => 35, "account_id" => 126, "name" => "Sicherheitsüberwachung und -erkennung"],
    ["id" => 36, "account_id" => 45, "name" => "Sport- und Freizeitausrüstung, -bedarf und -zubehör"],
    ["id" => 37, "account_id" => 117, "name" => "Sprachdaten- oder Multimedianetzwerkausstattung oder Plattformen und Zubehör"],
    ["id" => 38, "account_id" => 126, "name" => "Transportkomponenten und -systeme"],
    ["id" => 39, "account_id" => 117, "name" => "Unterhaltungselektronik"],
    ["id" => 40, "account_id" => 126, "name" => "Unterkunftsmöbel"],
    ["id" => 41, "account_id" => 126, "name" => "Werkzeug"],
    ["id" => 42, "account_id" => 34, "name" => "Elektrischer Draht und Kabel und Kabelbaum"],
    ["id" => 43, "account_id" => 126, "name" => "Farbstoffe"],
    ["id" => 44, "account_id" => 45, "name" => "Getränke"],
    ["id" => 45, "account_id" => 38, "name" => "Hausmeisterausrüstung"],
    ["id" => 46, "account_id" => 45, "name" => "Haustierprodukte"],
    ["id" => 47, "account_id" => 117, "name" => "Kunsthandwerkliche Ausrüstung, -Bedarf und Zubehör"],
    ["id" => 48, "account_id" => 126, "name" => "Lager, Lagerbuchsen, Räder und Zahnräder"],
    ["id" => 49, "account_id" => 117, "name" => "Mess-, Beobachtungs- und Prüfgeräte"],
    ["id" => 50, "account_id" => 16, "name" => "Papierprodukte"],
    ["id" => 51, "account_id" => 45, "name" => "Produkte und Zubehör für die Patientenversorgung und -behandlung"],
    ["id" => 52, "account_id" => 126, "name" => "Produktionskomponenten und -material"],
    ["id" => 53, "account_id" => 38, "name" => "Reinigungs- und Hausmeisterbedarf"],
    ["id" => 54, "account_id" => 34, "name" => "Seile Ketten, Kabel, Drähte und Gurte"],
    ["id" => 53, "account_id" => 126, "name" => "Möbel und Einrichtungsgegenstände"],
    ["id" => 54, "account_id" => 117, "name" => 'Elektronische Komponenten und Zubehör'],
);


// http://www.erdkunde-wissen.de/erdkunde/statistiken/kurzel.htm
$array_country = array('AT' => 'Österreich', 'BE' => 'Belgien', 'BG' => 'Bulgarien', 'CA' => 'Canada', 'DK' => 'Dänemark', 'DE' => 'Deutschland', 'EE' => 'Estland', 'FI' => 'Finnland', 'FR' => 'Frankreich', 'GR' => 'Griechenland', 'UK' => 'Großbritannien', 'IE' => 'Irland', 'IT' => 'Italien', 'IL' => 'Israel', 'JA' => 'Japan', 'LI' => 'Liechtenstein', 'LV' => 'Lettland', 'LT' => 'Litauen', 'LU' => 'Luxemburg', 'HR' => 'Kroatien', 'MT' => 'Malta', 'NL' => 'Niederlande', 'NO' => 'Norwegen', 'PL' => 'Polen', 'PT' => 'Portugal', 'RO' => 'Rumänien', 'SK' => 'Slowakei', 'SI' => 'Slowenien', 'ES' => 'Spanien', 'SE' => 'Schweden', 'CH' => 'Schweiz', 'CZ' => 'Tschechische Republik', 'TR' => 'Türkei', 'HU' => 'Ungarn', 'US' => 'USA', 'ZY' => 'Zypern', 'XX' => 'Andere');

$laender['en']['AT'] = "Austria";
$laender['de']['AT'] = "Österreich";
$laender['en']['AF'] = "Afghanistan";
$laender['de']['AF'] = "Afghanistan";
$laender['en']['AL'] = "Albania";
$laender['de']['AL'] = "Albanien";
$laender['en']['AS'] = "American Samoa";
$laender['de']['AS'] = "Amerikanisch Samoa";
$laender['en']['AD'] = "Andorra";
$laender['de']['AD'] = "Andorra";
$laender['en']['AO'] = "Angola";
$laender['de']['AO'] = "Angola";
$laender['en']['AI'] = "Anguilla";
$laender['de']['AI'] = "Anguilla";
$laender['en']['AQ'] = "Antarctica";
$laender['de']['AQ'] = "Antarktis";
$laender['en']['AG'] = "Antigua and Barbuda";
$laender['de']['AG'] = "Antigua und Barbuda";
$laender['en']['AR'] = "Argentina";
$laender['de']['AR'] = "Argentinien";
$laender['en']['AM'] = "Armenia";
$laender['de']['AM'] = "Armenien";
$laender['en']['AW'] = "Aruba";
$laender['de']['AW'] = "Aruba";
$laender['en']['AU'] = "Australia";
$laender['de']['AU'] = "Australien";
$laender['en']['AZ'] = "Azerbaijan";
$laender['de']['AZ'] = "Aserbaidschan";
$laender['en']['BS'] = "Bahamas";
$laender['de']['BS'] = "Bahamas";
$laender['en']['BH'] = "Bahrain";
$laender['de']['BH'] = "Bahrain";
$laender['en']['BD'] = "Bangladesh";
$laender['de']['BD'] = "Bangladesh";
$laender['en']['BB'] = "Barbados";
$laender['de']['BB'] = "Barbados";
$laender['en']['BY'] = "Belarus";
$laender['de']['BY'] = "Weißrussland";
$laender['en']['BE'] = "Belgium";
$laender['de']['BE'] = "Belgien";
$laender['en']['BZ'] = "Belize";
$laender['de']['BZ'] = "Belize";
$laender['en']['BJ'] = "Benin";
$laender['de']['BJ'] = "Benin";
$laender['en']['BM'] = "Bermuda";
$laender['de']['BM'] = "Bermuda";
$laender['en']['BT'] = "Bhutan";
$laender['de']['BT'] = "Bhutan";
$laender['en']['BO'] = "Bolivia";
$laender['de']['BO'] = "Bolivien";
$laender['en']['BA'] = "Bosnia and Herzegovina";
$laender['de']['BA'] = "Bosnien Herzegowina";
$laender['en']['BW'] = "Botswana";
$laender['de']['BW'] = "Botswana";
$laender['en']['BV'] = "Bouvet Island";
$laender['de']['BV'] = "Bouvet Island";
$laender['en']['BR'] = "Brazil";
$laender['de']['BR'] = "Brasilien";
$laender['en']['BN'] = "Brunei Darussalam";
$laender['de']['BN'] = "Brunei Darussalam";
$laender['en']['BG'] = "Bulgaria";
$laender['de']['BG'] = "Bulgarien";
$laender['en']['BF'] = "Burkina Faso";
$laender['de']['BF'] = "Burkina Faso";
$laender['en']['BI'] = "Burundi";
$laender['de']['BI'] = "Burundi";
$laender['en']['KH'] = "Cambodia";
$laender['de']['KH'] = "Kambodscha";
$laender['en']['CM'] = "Cameroon";
$laender['de']['CM'] = "Kamerun";
$laender['en']['CA'] = "Canada";
$laender['de']['CA'] = "Kanada";
$laender['en']['CV'] = "Cape Verde";
$laender['de']['CV'] = "Kap Verde";
$laender['en']['KY'] = "Cayman Islands";
$laender['de']['KY'] = "Cayman Inseln";
$laender['en']['CF'] = "Central African Republic";
$laender['de']['CF'] = "Zentralafrikanische Republik";
$laender['en']['TD'] = "Chad";
$laender['de']['TD'] = "Tschad";
$laender['en']['CL'] = "Chile";
$laender['de']['CL'] = "Chile";
$laender['en']['CN'] = "China";
$laender['de']['CN'] = "China";
$laender['en']['CO'] = "Colombia";
$laender['de']['CO'] = "Kolumbien";
$laender['en']['KM'] = "Comoros";
$laender['de']['KM'] = "Comoros";
$laender['en']['CG'] = "Congo";
$laender['de']['CG'] = "Kongo";
$laender['en']['CK'] = "Cook Islands";
$laender['de']['CK'] = "Cook Inseln";
$laender['en']['CR'] = "Costa Rica";
$laender['de']['CR'] = "Costa Rica";
$laender['en']['CI'] = "Côte d'Ivoire";
$laender['de']['CI'] = "Elfenbeinküste";
$laender['en']['HR'] = "Croatia";
$laender['de']['HR'] = "Kroatien";
$laender['en']['CU'] = "Cuba";
$laender['de']['CU'] = "Kuba";
$laender['en']['CZ'] = "Czech Republic";
$laender['de']['CZ'] = "Tschechien";
$laender['en']['DK'] = "Denmark";
$laender['de']['DK'] = "Dänemark";
$laender['en']['DJ'] = "Djibouti";
$laender['de']['DJ'] = "Djibouti";
$laender['en']['DO'] = "Dominican Republic";
$laender['de']['DO'] = "Dominikanische Republik";
$laender['en']['TP'] = "East Timor";
$laender['de']['TP'] = "Osttimor";
$laender['en']['EC'] = "Ecuador";
$laender['de']['EC'] = "Ecuador";
$laender['en']['EG'] = "Egypt";
$laender['de']['EG'] = "Ägypten";
$laender['en']['SV'] = "El salvador";
$laender['de']['SV'] = "El Salvador";
$laender['en']['GQ'] = "Equatorial Guinea";
$laender['de']['GQ'] = "Äquatorial Guinea";
$laender['en']['ER'] = "Eritrea";
$laender['de']['ER'] = "Eritrea";
$laender['en']['EE'] = "Estonia";
$laender['de']['EE'] = "Estland";
$laender['en']['ET'] = "Ethiopia";
$laender['de']['ET'] = "Äthiopien";
$laender['en']['FK'] = "Falkland Islands";
$laender['de']['FK'] = "Falkland Inseln";
$laender['en']['FO'] = "Faroe Islands";
$laender['de']['FO'] = "Faroe Inseln";
$laender['en']['FJ'] = "Fiji";
$laender['de']['FJ'] = "Fiji";
$laender['en']['FI'] = "Finland";
$laender['de']['FI'] = "Finland";
$laender['en']['FR'] = "France";
$laender['de']['FR'] = "Frankreich";
$laender['en']['GF'] = "French Guiana";
$laender['de']['GF'] = "Französisch Guiana";
$laender['en']['PF'] = "French Polynesia";
$laender['de']['PF'] = "Französisch Polynesien";
$laender['en']['GA'] = "Gabon";
$laender['de']['GA'] = "Gabon";
$laender['en']['GM'] = "Gambia";
$laender['de']['GM'] = "Gambia";
$laender['en']['GE'] = "Georgia";
$laender['de']['GE'] = "Georgien";
$laender['en']['DE'] = "Germany";
$laender['de']['DE'] = "Deutschland";
$laender['en']['GH'] = "Ghana";
$laender['de']['GH'] = "Ghana";
$laender['en']['GI'] = "Gibraltar";
$laender['de']['GI'] = "Gibraltar";
$laender['en']['GR'] = "Greece";
$laender['de']['GR'] = "Griechenland";
$laender['en']['GL'] = "Greenland";
$laender['de']['GL'] = "Grönland";
$laender['en']['GD'] = "Grenada";
$laender['de']['GD'] = "Grenada";
$laender['en']['GP'] = "Guadeloupe";
$laender['de']['GP'] = "Guadeloupe";
$laender['en']['GU'] = "Guam";
$laender['de']['GU'] = "Guam";
$laender['en']['GT'] = "Guatemala";
$laender['de']['GT'] = "Guatemala";
$laender['en']['GN'] = "Guinea";
$laender['de']['GN'] = "Guinea";
$laender['en']['GY'] = "Guyana";
$laender['de']['GY'] = "Guyana";
$laender['en']['HT'] = "Haiti";
$laender['de']['HT'] = "Haiti";
$laender['en']['VA'] = "Vatican";
$laender['de']['VA'] = "Vatikan";
$laender['en']['HN'] = "Honduras";
$laender['de']['HN'] = "Honduras";
$laender['en']['HU'] = "Hungary";
$laender['de']['HU'] = "Ungarn";
$laender['en']['IS'] = "Iceland";
$laender['de']['IS'] = "Island";
$laender['en']['IN'] = "India";
$laender['de']['IN'] = "Indien";
$laender['en']['ID'] = "Indonesia";
$laender['de']['ID'] = "Indonesien";
$laender['en']['IR'] = "Iran";
$laender['de']['IR'] = "Iran";
$laender['en']['IQ'] = "Iraq";
$laender['de']['IQ'] = "Irak";
$laender['en']['IE'] = "Ireland";
$laender['de']['IE'] = "Irland";
$laender['en']['IL'] = "Israel";
$laender['de']['IL'] = "Israel";
$laender['en']['IT'] = "Italy";
$laender['de']['IT'] = "Italien";
$laender['en']['JM'] = "Jamaica";
$laender['de']['JM'] = "Jamaika";
$laender['en']['JP'] = "Japan";
$laender['de']['JP'] = "Japan";
$laender['en']['JO'] = "Jordan";
$laender['de']['JO'] = "Jordanien";
$laender['en']['KZ'] = "Kazakstan";
$laender['de']['KZ'] = "Kasachstan";
$laender['en']['KE'] = "Kenya";
$laender['de']['KE'] = "Kenia";
$laender['en']['KI'] = "Kiribati";
$laender['de']['KI'] = "Kiribati";
$laender['en']['KW'] = "Kuwait";
$laender['de']['KW'] = "Kuwait";
$laender['en']['KG'] = "Kyrgystan";
$laender['de']['KG'] = "Kirgistan";
$laender['en']['LA'] = "Lao";
$laender['de']['LA'] = "Laos";
$laender['en']['LV'] = "Latvia";
$laender['de']['LV'] = "Lettland";
$laender['en']['LB'] = "Lebanon";
$laender['de']['LB'] = "Libanon";
$laender['en']['LS'] = "Lesotho";
$laender['de']['LS'] = "Lesotho";
$laender['en']['LI'] = "Liechtenstein";
$laender['de']['LI'] = "Liechtenstein";
$laender['en']['LT'] = "Lithuania";
$laender['de']['LT'] = "Litauen";
$laender['en']['LU'] = "Luxembourg";
$laender['de']['LU'] = "Luxemburg";
$laender['en']['MO'] = "Macau";
$laender['de']['MO'] = "Macau";
$laender['en']['MK'] = "Macedonia ";
$laender['de']['MK'] = "Mazedonien";
$laender['en']['MG'] = "Madagascar";
$laender['de']['MG'] = "Madagaskar";
$laender['en']['MW'] = "Malawi";
$laender['de']['MW'] = "Malawi";
$laender['en']['MY'] = "Malaysia";
$laender['de']['MY'] = "Malaysia";
$laender['en']['MV'] = "Maldives";
$laender['de']['MV'] = "Malediven";
$laender['en']['ML'] = "Mali";
$laender['de']['ML'] = "Mali";
$laender['en']['MT'] = "Malta";
$laender['de']['MT'] = "Malta";
$laender['en']['MR'] = "Mauritania";
$laender['de']['MR'] = "Mauretanien";
$laender['en']['MU'] = "Mauritius";
$laender['de']['MU'] = "Mauritius";
$laender['en']['YT'] = "Mayotte";
$laender['de']['YT'] = "Mayotte";
$laender['en']['MX'] = "Mexico";
$laender['de']['MX'] = "Mexiko";
$laender['en']['FM'] = "Micronesia";
$laender['de']['FM'] = "Mikronesien";
$laender['en']['MD'] = "Moldova";
$laender['de']['MD'] = "Moldavien";
$laender['en']['MC'] = "Monaco";
$laender['de']['MC'] = "Monaco";
$laender['en']['MN'] = "Mongolia";
$laender['de']['MN'] = "Mongolei";
$laender['en']['MS'] = "Montserrat";
$laender['de']['MS'] = "Montserrat";
$laender['en']['MA'] = "Morocco";
$laender['de']['MA'] = "Marokko";
$laender['en']['MZ'] = "Mozambique";
$laender['de']['MZ'] = "Mosambik";
$laender['en']['MM'] = "Myanmar";
$laender['de']['MM'] = "Myanmar";
$laender['en']['NA'] = "Namibia";
$laender['de']['NA'] = "Namibia";
$laender['en']['NR'] = "Nauru";
$laender['de']['NR'] = "Nauru";
$laender['en']['NP'] = "Nepal";
$laender['de']['NP'] = "Nepal";
$laender['en']['NL'] = "Netherlands";
$laender['de']['NL'] = "Niederlande";
$laender['en']['NZ'] = "New Zealand";
$laender['de']['NZ'] = "Neuseeland";
$laender['en']['NI'] = "Nicaragua";
$laender['de']['NI'] = "Nicaragua";
$laender['en']['NE'] = "Niger";
$laender['de']['NE'] = "Niger";
$laender['en']['NG'] = "Nigeria";
$laender['de']['NG'] = "Nigeria";
$laender['en']['NU'] = "Niue";
$laender['de']['NU'] = "Niue";
$laender['en']['NF'] = "Norfolk Island";
$laender['de']['NF'] = "Norfolk Inseln";
$laender['en']['KP'] = "North Korea";
$laender['de']['KP'] = "Nord Korea";
$laender['en']['NO'] = "Norway";
$laender['de']['NO'] = "Norwegen";
$laender['en']['OM'] = "Oman";
$laender['de']['OM'] = "Oman";
$laender['en']['PK'] = "Pakistan";
$laender['de']['PK'] = "Pakistan";
$laender['en']['PW'] = "Palau";
$laender['de']['PW'] = "Palau";
$laender['en']['PA'] = "Panama";
$laender['de']['PA'] = "Panama";
$laender['en']['PG'] = "Papua New Guinea";
$laender['de']['PG'] = "Papua Neu Guinea";
$laender['en']['PY'] = "Paraguay";
$laender['de']['PY'] = "Paraguay";
$laender['en']['PE'] = "Peru";
$laender['de']['PE'] = "Peru";
$laender['en']['PH'] = "Philippines";
$laender['de']['PH'] = "Philippinen";
$laender['en']['PL'] = "Poland";
$laender['de']['PL'] = "Polen";
$laender['en']['PT'] = "Portugal";
$laender['de']['PT'] = "Portugal";
$laender['en']['PR'] = "Puerto Rico";
$laender['de']['PR'] = "Puerto Rico";
$laender['en']['RO'] = "Romania";
$laender['de']['RO'] = "Rumänien";
$laender['en']['RU'] = "Russia";
$laender['de']['RU'] = "Russland";
$laender['en']['RW'] = "Rwanda";
$laender['de']['RW'] = "Ruanda";
$laender['en']['WS'] = "Samoa";
$laender['de']['WS'] = "Samoa";
$laender['en']['SM'] = "San Marino";
$laender['de']['SM'] = "San Marino";
$laender['en']['SA'] = "Saudi Arabia";
$laender['de']['SA'] = "Saudi-Arabien";
$laender['en']['SN'] = "Senegal";
$laender['de']['SN'] = "Senegal";
$laender['en']['SC'] = "Seychelles";
$laender['de']['SC'] = "Seychellen";
$laender['en']['SL'] = "Sierra Leone";
$laender['de']['SL'] = "Sierra Leone";
$laender['en']['SG'] = "Singapore";
$laender['de']['SG'] = "Singapur";
$laender['en']['SK'] = "Slovakia";
$laender['de']['SK'] = "Slovakei";
$laender['en']['SB'] = "Solomon Islands";
$laender['de']['SB'] = "Solomon Inseln";
$laender['en']['SO'] = "Somalia";
$laender['de']['SO'] = "Somalia";
$laender['en']['ZA'] = "South Africa";
$laender['de']['ZA'] = "Südafrika";
$laender['en']['KR'] = "South Korea";
$laender['de']['KR'] = "Südkorea";
$laender['en']['ES'] = "Spain";
$laender['de']['ES'] = "Spanien";
$laender['en']['LK'] = "Sri Lanka";
$laender['de']['LK'] = "Sri Lanka";
$laender['en']['SD'] = "Sudan";
$laender['de']['SD'] = "Sudan";
$laender['en']['SR'] = "Suriname";
$laender['de']['SR'] = "Suriname";
$laender['en']['SZ'] = "Swaziland";
$laender['de']['SZ'] = "Swasiland";
$laender['en']['SE'] = "Sweden";
$laender['de']['SE'] = "Schweden";
$laender['en']['CH'] = "Switzerland";
$laender['de']['CH'] = "Schweiz";
$laender['en']['SY'] = "Syria";
$laender['de']['SY'] = "Syrien";
$laender['en']['TW'] = "Taiwan";
$laender['de']['TW'] = "Taiwan";
$laender['en']['TJ'] = "Tajikistan";
$laender['de']['TJ'] = "Tadschikistan";
$laender['en']['TZ'] = "Tanzania";
$laender['de']['TZ'] = "Tansania";
$laender['en']['TH'] = "Thailand";
$laender['de']['TH'] = "Thailand";
$laender['en']['TG'] = "Togo";
$laender['de']['TG'] = "Togo";
$laender['en']['TO'] = "Tonga";
$laender['de']['TO'] = "Tonga";
$laender['en']['TT'] = "Trinidad and Tobago";
$laender['de']['TT'] = "Trinidad und Tobago";
$laender['en']['TN'] = "Tunisia";
$laender['de']['TN'] = "Tunesien";
$laender['en']['TR'] = "Turkey";
$laender['de']['TR'] = "Türkei";
$laender['en']['TM'] = "Turkmenistan";
$laender['de']['TM'] = "Turkmenistan";
$laender['en']['TV'] = "Tuvalu";
$laender['de']['TV'] = "Tuvalu";
$laender['en']['UG'] = "Uganda";
$laender['de']['UG'] = "Uganda";
$laender['en']['UA'] = "Ukraine";
$laender['de']['UA'] = "Ukraine";
$laender['en']['AE'] = "United Arab Emirates";
$laender['de']['AE'] = "Vereinigte Arabische Emirate";
$laender['en']['GB'] = "United Kingdom";
$laender['de']['GB'] = "Vereinigtes Königreich";
$laender['en']['US'] = "United States of America";
$laender['de']['US'] = "Vereinigte Staaten von Amerika";
$laender['en']['UY'] = "Uruguay";
$laender['de']['UY'] = "Uruguay";
$laender['en']['UZ'] = "Uzbekistan";
$laender['de']['UZ'] = "Usbekistan";
$laender['en']['VU'] = "Vanuatu";
$laender['de']['VU'] = "Vanuatu";
$laender['en']['VE'] = "Venezuela";
$laender['de']['VE'] = "Venezuela";
$laender['en']['VN'] = "Vietnam";
$laender['de']['VN'] = "Vietnam";
$laender['en']['VG'] = "Virgin Islands";
$laender['de']['VG'] = "Virgin Islands";
$laender['en']['EH'] = "Western Sahara";
$laender['de']['EH'] = "Westsahara";
$laender['en']['YE'] = "Yemen";
$laender['de']['YE'] = "Jemen";
$laender['en']['YU'] = "Yugoslavia";
$laender['de']['YU'] = "Jugoslavien";
$laender['en']['ZR'] = "Zaire";
$laender['de']['ZR'] = "Zaire";
$laender['en']['ZM'] = "Zambia";
$laender['de']['ZM'] = "Sambia";
$laender['en']['ZW'] = "Zimbabwe";
$laender['de']['ZW'] = "Simbabwe";

asort($laender['en']);
asort($laender['de']);