<?
include (__DIR__ . '/../../config.php');


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
$_SESSION['user_id'] = $_SESSION['login_user_id'];

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
    include_once ('oegt/lang/de.inc.php');
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
