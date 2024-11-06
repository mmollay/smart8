<?
include(__DIR__ . '/functions.inc.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$username = 'smart';
$password = 'Eiddswwenph21;';
$dbname = 'ssi_fakturaV2';

$GLOBALS['db'] = $db = $connection = $GLOBALS['mysqli'] = mysqli_connect($host, $username, $password, $dbname);

//Select the database
if (!mysqli_select_db($db, $dbname)) {
    die('Datenbankauswahl fehlgeschlagen: ' . mysqli_error($db));
}


// Funktion zum Abrufen der Konten
function getAccountArray($db)
{
    $accounts = [];
    $query = "SELECT account_id, CONCAT(account_number, ' - ', account_name, ' (', IFNULL(percentage, ''), '%)') AS account_display 
              FROM accounts 
              WHERE account_type IN ('Income', 'Expense')
              ORDER BY account_number";
    $result = $db->query($query);
    while ($row = $result->fetch_assoc()) {
        $accounts[$row['account_id']] = $row['account_display'];
    }
    return $accounts;
}

// Diese Funktion wird nicht direkt in f_invoices.php verwendet, 
// aber sie könnte nützlich sein für die get_article_data.php Datei
function getArticleArray($db)
{
    $articles = [];
    $query = "SELECT article_id, article_number, name FROM articles ORDER BY name";
    $result = $db->query($query);
    while ($row = $result->fetch_assoc()) {
        $articles[$row['article_id']] = $row['article_number'] . ' - ' . $row['name'];
    }
    return $articles;
}