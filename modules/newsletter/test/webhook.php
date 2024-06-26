<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$dbname = 'ssi_newsletter';
$username = 'smart';
$password = 'Eiddswwenph21;';

$db = new mysqli($host, $username, $password, $dbname);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data) {
    foreach ($data as $event) {
        $customID = $event['CustomID'];
        preg_match('/email_job_(\d+)/', $customID, $matches);
        $job_id = $matches[1];
        $status = $event['event'];
        $logResponse = json_encode($event);

        // Log-Eintrag aktualisieren oder erstellen
        $logStmt = $db->prepare("INSERT INTO email_logs (job_id, status, response) VALUES (?, ?, ?)");
        $logStmt->bind_param("iss", $job_id, $status, $logResponse);
        $logStmt->execute();
        $logStmt->close();

        // Aktualisiere den Status in der email_jobs Tabelle
        $db->query("UPDATE email_jobs SET status = '$status' WHERE id = " . $job_id);
    }
}

$db->close();
