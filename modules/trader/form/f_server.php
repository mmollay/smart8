<?php
if ($_GET['clone']) {
    $addCloneName = " (Clone)";
    $arr['field']['clone'] = array('type' => 'hidden', 'value' => '1');
}

// AJAX Konfiguration für erfolgreiches Absenden des Formulars
$arr['ajax'] = array(
    'success' => "afterFormSubmit(data)", // JavaScript-Funktion, die nach dem erfolgreichen Absenden aufgerufen wird
    'dataType' => "html" // Erwarteter Datentyp der Antwort
);

// SQL-Abfrage (Beispiel, anpassen nach Bedarf)
$arr['sql'] = array(
    'query' => "SELECT *, strategy_default, CONCAT(name,'$addCloneName') name FROM ssi_trader.servers WHERE server_id = '{$_POST['update_id']}'"
);

$arr['field']['active'] = array(
    'tab' => '1',
    'type' => 'checkbox', // Änderung zu 'checkbox' für ein Kontrollkästchen
    'label' => 'Server is active', // Kontrollkästchen für die Inaktivität des Servers
);

$arr['field']['name'] = array(
    'tab' => '1',
    'type' => 'input',
    'label' => 'Matchode (Title)', // Eingabefeld für den Namen des Servers
    'validate' => true,
    'placeholder' => 'Martin Server'
);

// Definition der Eingabefelder für das Server-Formular
$arr['field']['url'] = array(
    'tab' => '1',
    'type' => 'input',
    'label' => 'URL', // Eingabefeld für die Server-IP-Adresse
    'focus' => true, // Fokus auf dieses Feld beim Laden des Formulars
    'placeholder' => 'https://trade.example.com:443', // Platzhalter für das Eingabefeld
    'validate' => true
);


$token = $_SESSION['token'][$_POST['update_id']];
$ServerUrl = getServerUrl($mysqli, $_POST['update_id']);
$arrayStrategies = getStrategyNames($ServerUrl, $token);
//strategy dropdown
//Version wo die Strageien (werte) aus der Datenbank geholt werden
//$array_sql_hedging = "SELECT group_id, title FROM ssi_trader.hedging_group ORDER BY title ASC";
//$arr['field']["strategy_id"] = array('type' => 'dropdown', 'label' => "Strategy $i", 'array_mysql' => $array_sql_hedging, 'text' => 'title', 'class' => 'fluid search selection', 'validate' => true);

$arr['field'][] = array('type' => 'div', 'class' => 'fields equal width');
$arr['field']['lotsize'] = array('tab' => '1', 'type' => 'input', 'placeholder' => '0.1', 'label' => 'Lot Size', 'validate' => true);
$arr['field']["strategy_default"] = array('type' => 'dropdown', 'label' => "Strategy", 'array' => $arrayStrategies, 'text' => 'title', 'class' => 'fluid search selection');
$arr['field']['contract_default'] = array('type' => 'dropdown', 'label' => "Contract", 'array' => $arrayContracts, 'class' => 'fluid search selection', 'placeholder' => 'Contract');
$arr['field'][] = array('type' => 'div_close');


$updateId = isset($_POST['update_id']) ? intval($_POST['update_id']) : null;

$array_sql_broker = "
SELECT 
    broker_id, 
    CONCAT(broker_server, ' (' , COALESCE(title, 'user'),')') AS title 
FROM 
    ssi_trader.broker 
WHERE 
    broker_id NOT IN (
        SELECT 
            broker_id 
        FROM 
            ssi_trader.servers 
        WHERE 
            broker_id IS NOT NULL" .
    ($updateId ? " AND server_id != {$updateId}" : "") . "
    )
ORDER BY 
    title ASC";

//$array_sql_broker = "SELECT broker_id, CONCAT(broker_server, '(' , COALESCE(title, 'user'),')') AS title FROM ssi_trader.broker ORDER BY title ASC";
$arr['field']["broker_id"] = array('type' => 'dropdown', 'label' => "Broker $i", 'array_mysql' => $array_sql_broker, 'text' => 'title', 'class' => 'fluid search selection', 'clear' => true);

$arr['field']['description'] = array(
    'tab' => '1',
    'type' => 'textarea', // Änderung zu 'textarea' für längere Beschreibungen
    'label' => 'Description', // Eingabefeld für die Beschreibung des Servers
    'rows' => 3
);


// JavaScript-Ressourcen für das Formular
$add_js .= "<script type=\"text/javascript\" src=\"js/form_after.js\"></script>"; // Ensure the file name is correct