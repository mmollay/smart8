<?php
include(__DIR__ . '/../f_config.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Überprüfen und Bereinigen der Eingabedaten
$delete_id = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT) ?? 0;
$entity_type = filter_input(INPUT_POST, 'entity_type', FILTER_UNSAFE_RAW) ?? '';
$entity_type = trim(strip_tags($entity_type));

// Funktion zum Abrufen des Namens der zu löschenden Entität
function getEntityName($db, $entity_type, $delete_id)
{
	$allowed_entities = [
		'customer' => ['table' => 'customers', 'id' => 'customer_id', 'name' => 'company_name'],
		'supplier' => ['table' => 'suppliers', 'id' => 'supplier_id', 'name' => 'company_name'],
		'article' => ['table' => 'articles', 'id' => 'article_id', 'name' => 'name'],
		'invoice' => ['table' => 'invoices', 'id' => 'invoice_id', 'name' => 'invoice_number'],
		'account' => ['table' => 'accounts', 'id' => 'account_id', 'name' => 'account_name'],
		'elba' => ['table' => 'data_elba', 'id' => 'elba_id', 'name' => 'text'],
	];

	if (!isset($allowed_entities[$entity_type])) {
		return "Unbekannt";
	}

	$entity = $allowed_entities[$entity_type];
	$sql = "SELECT {$entity['name']} FROM {$entity['table']} WHERE {$entity['id']} = ?";

	try {
		$stmt = $db->prepare($sql);
		$stmt->bind_param("i", $delete_id);
		$stmt->execute();
		$stmt->bind_result($name);
		$stmt->fetch();
		$stmt->close();
		return $name ?? "Unbekannt";
	} catch (Exception $e) {
		error_log("Fehler beim Abrufen des Entitätsnamens: " . $e->getMessage());
		return "Fehler aufgetreten";
	}
}

$entity_name = getEntityName($db, $entity_type, $delete_id);
?>

<div class="ui form">
	<h3 class="ui header">Bestätigung</h3>
	<p>Sind Sie sicher, dass Sie folgenden Datensatz löschen möchten?</p>
	<p><strong><?= htmlspecialchars($entity_name, ENT_QUOTES, 'UTF-8') ?></strong></p>
	<input type="hidden" name="delete_id" value="<?= $delete_id ?>">
	<input type="hidden" name="entity_type" value="<?= htmlspecialchars($entity_type, ENT_QUOTES, 'UTF-8') ?>">
	<div class="ui warning message">
		<div class="header">Warnung</div>
		<p>Diese Aktion kann nicht rückgängig gemacht werden!</p>
	</div>
	<div class="ui two buttons">
		<button class="ui negative button" onclick="deleteEntity()">Löschen</button>
		<button class="ui button" onclick="$('.ui.modal').modal('hide')">Abbrechen</button>
	</div>
</div>

<script>
	function deleteEntity() {
		var deleteId = $('input[name="delete_id"]').val();
		var entityType = $('input[name="entity_type"]').val();
		$.ajax({
			url: 'save/process_delete.php',
			method: 'POST',
			data: { delete_id: deleteId, entity_type: entityType },
			dataType: 'json',
			success: function (response) {
				if (response.status === 'success') {
					showMessage('Erfolg', response.message, 'success');
					$('.ui.modal').modal('hide');
					reloadTable();
				} else {
					showMessage('Fehler', response.message, 'error');
				}
			},
			error: function (xhr, status, error) {
				console.error("AJAX Fehler: " + status + ": " + error);
				showMessage('Fehler', 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.', 'error');
			}
		});
	}
</script>