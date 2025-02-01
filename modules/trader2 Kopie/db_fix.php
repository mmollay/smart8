<?php
require_once(__DIR__ . '/t_config.php');

$sql = "ALTER TABLE users MODIFY company varchar(200) NULL DEFAULT NULL";
if ($db->query($sql)) {
    echo "Company field updated successfully\n";
} else {
    echo "Error updating company field: " . $db->error . "\n";
}
