<?
include (__DIR__ . '/../config.php');
include (__DIR__ . '/../smartform/include_form.php');

//Hedging Strategy
$arr['form'] = array('action' => "pages/settings_save_data.php", 'id' => 'form_setting', 'class' => 'center segment', 'width' => '800');
$arr['sql'] = array('query' => "SELECT * from clients WHERE client_id  = '{$_SESSION['client_id']}'");
$arr['ajax'] = array('success' => "after_form_setting(data)", 'dataType' => "json");

// Anpassungen für die Ausgabe der E-Mail-Adresse
$arr['field']['email'] = array('type' => 'label', 'label' => 'E-Mail');

// Fortführung der Feldkonfiguration wie zuvor
$arr['field']['first_name'] = array('type' => 'input', 'label' => 'Vorname', 'focus' => true);
$arr['field']['last_name'] = array('type' => 'input', 'label' => 'Nachname');
$arr['field']['phone'] = array('type' => 'input', 'label' => 'Telefon');
$arr['field']['street'] = array('type' => 'input', 'label' => 'Straße');
$arr['field']['zip'] = array('type' => 'input', 'label' => 'PLZ');
$arr['field']['country'] = array('type' => 'dropdown', 'array' => 'country', 'label' => 'Land');
$arr['field']['company'] = array('type' => 'input', 'label' => 'Firma');
// Weiterer Felder...

$arr['button']['submit'] = array('value' => "Speichern", 'color' => 'blue');

$output = call_form($arr, $db);

echo "<h2 class='ui header' style='color: #21ba45;'>SSI-Trader Einstellungen</h2>";
echo $output['html'] . $output['js'] . "<br>";

echo "<script type=\"text/javascript\" src=\"js/form_setting.js\"></script>";
