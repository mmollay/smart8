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

$subject = $_POST['subject'] ?? 'Kein Betreff';
$message = $_POST['message'] ?? 'Keine Nachricht';
$attachments = $_FILES['attachments'] ?? null;

// Annahme: Absenderinformationen sind ebenfalls in den POST-Daten enthalten
$sender_first_name = $_POST['sender_first_name'];
$sender_last_name = $_POST['sender_last_name'];
$sender_company = $_POST['sender_company'];
$sender_email = $_POST['sender_email'];
$sender_gender = $_POST['sender_gender'];
$sender_title = $_POST['sender_title'];
$sender_comment = $_POST['sender_comment'];

// Speichere den Absender in der Datenbank
$stmt = $db->prepare("INSERT INTO senders (first_name, last_name, company, email, gender, title, comment) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $sender_first_name, $sender_last_name, $sender_company, $sender_email, $sender_gender, $sender_title, $sender_comment);
$stmt->execute();
$sender_id = $stmt->insert_id;
$stmt->close();

$emailData = [
    'recipients' => [
        ["first_name" => "Martin", "last_name" => "Mollay", "company" => "SSI", "email" => "martin@ssi.at", "gender" => "male", "title" => "Herr", "comment" => "Kommentar für Martin Mollay"],
        ["first_name" => "Martin", "last_name" => "2", "company" => "SSI", "email" => "mm@ssi.at", "gender" => "male", "title" => "Herr", "comment" => "Kommentar für Martin 2"]
    ],
    'attachments' => []
];

if ($attachments && $attachments['size'][0] > 0) {
    $uploadDir = 'uploads/' . uniqid();
    mkdir($uploadDir, 0777, true);

    for ($i = 0; $i < count($attachments['name']); $i++) {
        if ($attachments['size'][$i] > 0) {
            $filePath = $uploadDir . '/' . basename($attachments['name'][$i]);
            move_uploaded_file($attachments['tmp_name'][$i], $filePath);
            $emailData['attachments'][] = [
                'name' => $attachments['name'][$i],
                'type' => $attachments['type'][$i],
                'path' => $filePath
            ];
        }
    }
}

// Speichere die E-Mail-Inhalte in der Datenbank
$stmt = $db->prepare("INSERT INTO email_contents (subject, message) VALUES (?, ?)");
$stmt->bind_param("ss", $subject, $message);
$stmt->execute();
$content_id = $stmt->insert_id;
$stmt->close();

// Speichere die E-Mail-Daten in einer temporären Datei
$file = tempnam(sys_get_temp_dir(), 'email_');
file_put_contents($file, json_encode($emailData));

// Speichere die Empfänger in der Datenbank und erstelle Jobs
foreach ($emailData['recipients'] as $recipient) {
    $stmt = $db->prepare("INSERT INTO recipients (first_name, last_name, company, email, gender, title, comment) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $recipient['first_name'], $recipient['last_name'], $recipient['company'], $recipient['email'], $recipient['gender'], $recipient['title'], $recipient['comment']);
    $stmt->execute();
    $recipient_id = $stmt->insert_id;
    $stmt->close();

    $stmt = $db->prepare("INSERT INTO email_jobs (content_id, sender_id, recipient_id, data_file, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iiis", $content_id, $sender_id, $recipient_id, $file);
    $stmt->execute();
    $stmt->close();
}

$db->close();

echo json_encode(['file' => $file]);
