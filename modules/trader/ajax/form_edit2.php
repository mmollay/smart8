<?php
include (__DIR__ . "/../t_config.php");

// Processing the $_POST data
$expectedFields = [
	'update_id',
	'title',
	'text',
	'list_id',
	'form_id',
	'strategy_id',
	'server_id',
	'broker_server',
	'user',
	'password',
	'broker_id',
	'url',
	'name',
	'description',
	'clone', // bisherige Felder
	'first_name',
	'last_name',
	'email',
	'phone',
	'street',
	'zip',
	'country',
	'address',
	'company',
	// Neue Felder für "deposits"
	'amount', // Entspricht deposit_amount im Formular
	'deposit_date',
	// Neue Felder für "profit_shares"
	'profit_percentage', // Entspricht profit_share_percentage im Formular
	'start_date',
	'end_date',
	'paid_out',
	'comment',
	'client_id',
	'deposit_amount',
	'description',
	'profit_share_percentage',
	'lotsize',
	'active',
	'real_account',
	'strategy_default',
	'contract_default',
	'positive_multiplier',
	'negative_multiplier',
	'reverse',
	'account',
	'daily_loss',
	'total_loss'
];


$safePost = [];

foreach ($_POST as $key => $value) {
	if (in_array($key, $expectedFields) || preg_match('/^(Side|Size|EntryPrice|TP|Switch|info)\d+$/', $key)) {
		// Verarbeite bekannte Felder und dynamisch generierte Felder wie Side1, Size1, EntryPrice1, usw.
		$safePost[$key] = $GLOBALS['mysqli']->real_escape_string($value);
	}
}

if (isset($safePost['form_id'])) {

	switch ($safePost['form_id']) {
		case 'form_setting':
			// Prepared statement for replacing/updating in the strategy_group table
			$stmt = $GLOBALS['mysqli']->prepare("REPLACE INTO ssi_trader.setting SET setting_id = ?, title = ?, strategy_id = ?, broker_id = ?, user_id = ?");
			$stmt->bind_param("issii", $_SESSION['user_id'], $safePost['title'], $safePost['strategy_id'], $safePost['broker_id'], $_SESSION['user_id']);
			$stmt->execute();
			$setting_id = $stmt->insert_id;
			$stmt->close();
			echo "ok";
			break;
		case 'form_choose_strategy':

			// nur setting_id updaten
			$stmt = $GLOBALS['mysqli']->prepare("UPDATE ssi_trader.setting SET strategy_id = ?, broker_id = ? WHERE user_id = ?");
			// Angenommen, dass `$safePost['broker_id']` der korrekte Wert für broker_id ist
			$stmt->bind_param("iii", $safePost['strategy_id'], $safePost['broker_id'], $_SESSION['user_id']);
			$stmt->execute();

			// Überprüfen, wie viele Zeilen betroffen waren
			$affected_rows = $stmt->affected_rows;
			$stmt->close();

			if ($affected_rows > 0) {
				echo "ok";
			} else {
				echo "Fehler oder keine Zeilen aktualisiert.";
			}
			break;

		case 'form_home':
			if ($safePost['kill_all'])
				echo "ok";
			break;
	}
}

if ($safePost['clone']) {
	$safePost['update_id'] = '';
}

// Processing based on 'list_id'
if (isset($safePost['list_id'])) {

	switch ($safePost['list_id']) {

		case 'investment':

			// Prepared Statement zum Ersetzen/Einfügen in die deposits Tabelle
			$stmt = $GLOBALS['mysqli']->prepare("REPLACE INTO ssi_trader.deposits (deposit_id, client_id, amount, deposit_date, description, positive_multiplier, negative_multiplier, account) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param("iissssss", $safePost['update_id'], $safePost['client_id'], $safePost['amount'], $safePost['deposit_date'], $safePost['description'], $safePost['positive_multiplier'], $safePost['negative_multiplier'], $safePost['account']);
			$stmt->execute();
			$deposit_id = $stmt->insert_id; // Die ID des betroffenen Eintrags in 'deposits' erhalten
			$stmt->close();

			$safePost['profit_share_percentage'] = 0;	// vorübergehend
			// Prepared Statement zum Ersetzen/Einfügen in die profit_shares Tabelle
			$stmt = $GLOBALS['mysqli']->prepare("REPLACE INTO ssi_trader.profit_shares (profit_share_id, client_id, profit_percentage, start_date, end_date, paid_out, comment) VALUES (?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param("iisssis", $safePost['update_id'], $safePost['client_id'], $safePost['profit_share_percentage'], $safePost['start_date'], $safePost['end_date'], $safePost['paid_out'], $safePost['comment']);
			$stmt->execute();
			$profit_share_id = $stmt->insert_id; // Die ID des betroffenen Eintrags in 'profit_shares' erhalten
			$stmt->close();

			echo "ok";
			break;
		case 'client':

			$token = generateSecureToken($length = 32);
			$safePost['broker_id'] = 0; //Wird nicht mehr benötigt
			if (isset($safePost['update_id']) && $safePost['update_id']) {

				//Auslesen des account aus der db über broker_id
				//$safePost['account'] = getUserByBrokerId($mysqli, $safePost['broker_id']);

				// UPDATE-Statement, wenn update_id existiert
				$safePost['server_id'] = 0;
				$sql = "UPDATE ssi_trader.clients SET first_name = ?, last_name = ?, email = ?, phone = ?, street = ?, zip = ?, country = ?, address = ?, company = ?, password = ?, server_id = ?, broker_id = ? , token = ?, account = ?, positive_multiplier = ?, negative_multiplier = ?, daily_loss = ?, total_loss = ? WHERE client_id = ?";
				$stmt = $GLOBALS['mysqli']->prepare($sql);
				$stmt->bind_param(
					"ssssssssssiisissssi",
					$safePost['first_name'],
					$safePost['last_name'],
					$safePost['email'],
					$safePost['phone'],
					$safePost['street'],
					$safePost['zip'],
					$safePost['country'],
					$safePost['address'],
					$safePost['company'],
					$safePost['password'],
					$safePost['server_id'],
					$safePost['broker_id'],
					$token,
					$safePost['account'],
					$safePost['positive_multiplier'],
					$safePost['negative_multiplier'],
					$safePost['daily_loss'],
					$safePost['total_loss'],
					$safePost['update_id'],
				);


			} else {
				$safePost['server_id'] = 0;
				// INSERT-Statement, wenn keine update_id existiert
				$sql = "INSERT INTO ssi_trader.clients (first_name, last_name, email, phone, street, zip, country, address, company, password, server_id, broker_id, token, account, positive_multiplier, negative_multiplier, daily_loss, total_loss ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
				$stmt = $GLOBALS['mysqli']->prepare($sql);
				$stmt->bind_param(
					"ssssssssssiisissss",
					$safePost['first_name'],
					$safePost['last_name'],
					$safePost['email'],
					$safePost['phone'],
					$safePost['street'],
					$safePost['zip'],
					$safePost['country'],
					$safePost['address'],
					$safePost['company'],
					$safePost['password'],
					$safePost['server_id'],
					$safePost['broker_id'],
					$token,
					$safePost['account'],
					$safePost['positive_multiplier'],
					$safePost['negative_multiplier'],
					$safePost['daily_loss'],
					$safePost['total_loss']
				);
			}
			// Ausführen des Statements
			$stmt->execute();
			// Die ID des betroffenen Client-Eintrags erhalten
			$client_id = $stmt->insert_id;
			// Schließen des Statements
			$stmt->close();
			echo "ok";
			break;


		case 'server':
			if (!$safePost['active'])
				$safePost['active'] = 0;

			$safePost['strategy_id'] = 0; // vorübergehend
			// Prepared Statement zum Ersetzen/Einfügen in die server Tabelle
			$stmt = $GLOBALS['mysqli']->prepare("INSERT INTO ssi_trader.servers (server_id, url, name, description, user_id, strategy_id, broker_id, lotsize, active, strategy_default, contract_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE url = VALUES(url), name = VALUES(name), description = VALUES(description), user_id = VALUES(user_id), strategy_id = VALUES(strategy_id), broker_id = VALUES(broker_id), lotsize = VALUES(lotsize), active = VALUES(active), strategy_default = VALUES(strategy_default), contract_default = VALUES(contract_default)");
			$stmt->bind_param("isssiiisiss", $safePost['update_id'], $safePost['url'], $safePost['name'], $safePost['description'], $_SESSION['user_id'], $safePost['strategy_id'], $safePost['broker_id'], $safePost['lotsize'], $safePost['active'], $safePost['strategy_default'], $safePost['contract_default']);

			$stmt->execute();
			// mysqli->insert_id gibt die ID des zuletzt eingefügten Datensatzes zurück. Bei einem UPDATE, das durch den ON DUPLICATE KEY-Teil des Statements ausgelöst wird, ist dieser Wert 0.
			// Wenn du die server_id nach dem Einfügen oder Aktualisieren benötigst, musst du sie möglicherweise separat behandeln.
			if ($stmt->errno) {
				echo "Fehler: " . $stmt->error;
				$stmt->close();
				exit;
			} else {
				// Wenn safePost['update_id'] größer als 0 ist, wurde ein vorhandener Eintrag aktualisiert.
				// Andernfalls wurde ein neuer Eintrag eingefügt und mysqli->insert_id sollte dessen ID sein.
				$server_id = ($safePost['update_id'] > 0) ? $safePost['update_id'] : $stmt->insert_id;
				$stmt->close();
				echo "ok";
				break;
			}

		case 'broker':
			$safePost['server_id'] = '';
			if ($safePost['real_account'] == 0)
				$safePost['real_account'] = 0;

			// Prepared statement for replacing/updating in the accounts table
			$stmt = $GLOBALS['mysqli']->prepare("REPLACE INTO ssi_trader.broker SET broker_id = ?, broker_server = ?, user = ?, password = ?, user_id = ?, title = ?, real_account = ?	");
			$stmt->bind_param("isssisi", $safePost['update_id'], $safePost['broker_server'], $safePost['user'], $safePost['password'], $_SESSION['user_id'], $safePost['title'], $safePost['real_account']);
			$stmt->execute();
			$account_id = $stmt->insert_id;
			$stmt->close();
			echo "ok";
			break;

		case 'strategy':

			if (!$safePost['reverse'])
				$safePost['reverse'] = 0;
			// Prepared statement for replacing/updating in the strategy_group table
			$stmt = $GLOBALS['mysqli']->prepare("INSERT INTO ssi_trader.hedging_group (group_id, title, text, timestamp, reverse) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), text = VALUES(text), timestamp = VALUES(timestamp), reverse = VALUES(reverse)");
			$stmt->bind_param("isssi", $safePost['update_id'], $safePost['title'], $safePost['text'], $safePost['timestamp'], $safePost['reverse']);
			$stmt->execute();
			if ($GLOBALS['mysqli']->insert_id) {
				$group_id = $GLOBALS['mysqli']->insert_id;
			} else {
				$group_id = $safePost['update_id'];  // Nehmen Sie an, dass dies die ID des bereits vorhandenen Eintrags ist.
			}
			$stmt->close();


			// Deleting existing hedgings for the group
			$deleteStmt = $GLOBALS['mysqli']->prepare("DELETE FROM ssi_trader.hedging WHERE group_id = ? OR group_id = 0 ");
			$deleteStmt->bind_param("i", $group_id);
			$deleteStmt->execute();
			$deleteStmt->close();

			for ($i = 1; $i <= 14; $i++) {
				// Variablennamen entsprechend den neuen Parametern
				$sideVarName = "Side" . $i;
				$sizeVarName = "Size" . $i;
				$entryPriceVarName = "EntryPrice" . $i;
				$tpVarName = "TP" . $i;
				$switchVarName = "Switch" . $i; // Angenommen, dies ist eine Checkbox, die 1 oder 0 zurückgibt
				$infoVarName = "info" . $i;

				// Zugriff auf die Werte aus $safePost und entsprechende Typumwandlungen
				$sideValue = isset($safePost[$sideVarName]) ? $safePost[$sideVarName] : null;
				$sizeValue = isset($safePost[$sizeVarName]) ? floatval(str_replace(',', '.', $safePost[$sizeVarName])) : null;
				$entryPriceValue = isset($safePost[$entryPriceVarName]) ? floatval(str_replace(',', '.', $safePost[$entryPriceVarName])) : null;
				$tpValue = isset($safePost[$tpVarName]) ? floatval(str_replace(',', '.', $safePost[$tpVarName])) : null;
				$switchValue = isset($safePost[$switchVarName]) ? (int) $safePost[$switchVarName] : 0; // Checkbox-Wert zu Integer
				$infoValue = isset($safePost[$infoVarName]) ? $safePost[$infoVarName] : '';

				if (is_numeric($sizeValue) && is_numeric($tpValue)) {
					if ($sizeValue != 0) {
						$insertStmt = $GLOBALS['mysqli']->prepare("INSERT INTO ssi_trader.hedging (group_id, level, Side, Size, EntryPrice, TP, Switch, info) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE Side = VALUES(Side), Size = VALUES(Size), EntryPrice = VALUES(EntryPrice), TP = VALUES(TP), Switch = VALUES(Switch), info = VALUES(info)");
						$insertStmt->bind_param("iiidddis", $group_id, $i, $sideValue, $sizeValue, $entryPriceValue, $tpValue, $switchValue, $infoValue);
						$insertStmt->execute();
						$insertStmt->close();
					}
				} else {
					echo "Ungültige Eingabe bei Level $i";
					exit;
				}
			}

			echo "ok";
			break;
	}
}



function getUserByBrokerId($connection, $brokerId)
{
	// Sicherstellen, dass brokerId ein integer ist, um SQL-Injection zu verhindern
	$brokerId = intval($brokerId);

	// SQL-Abfrage vorbereiten
	$query = "SELECT user FROM ssi_trader.broker WHERE broker_id = ?";

	// Vorbereiten des SQL-Statements
	if ($stmt = $connection->prepare($query)) {
		// Parameter binden
		$stmt->bind_param("i", $brokerId);

		// SQL-Statement ausführen
		$stmt->execute();

		// Ergebnis speichern
		$result = $stmt->get_result();

		// Überprüfen, ob ein Ergebnis vorhanden ist
		if ($row = $result->fetch_assoc()) {
			// 'user' zurückgeben
			return $row['user'];
		} else {
			// Kein Ergebnis gefunden
			return null;
		}
	} else {
		// Fehler beim Vorbereiten des Statements
		return null;
	}
}
