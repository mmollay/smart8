<?php
// Disable error reporting for production environment
error_reporting(0);
ini_set('display_errors', 0);

require __DIR__ . '/../../../vendor/autoload.php';

// Script start time
$start_time = microtime(true);

include (__DIR__ . '/../n_config.php');

$db = new mysqli($host, $username, $password, $dbname);
if ($db->connect_error) {
    exit(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

use \Mailjet\Resources;

// Function to get attachments
function getAttachments($base_path)
{
    $attachments = [];
    $directory = $base_path;

    // Check if the directory exists
    if (!is_dir($directory)) {
        return $attachments;
    }

    // Iterate through all files in the directory
    $files = scandir($directory);
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            $full_path = $directory . $file;

            if (is_file($full_path)) {
                $attachments[] = [
                    'ContentType' => mime_content_type($full_path),
                    'Filename' => $file,
                    'Base64Content' => base64_encode(file_get_contents($full_path))
                ];
            }

        }
    }

    return $attachments;
}

// Search for pending jobs in the database
$result = $db->query("SELECT * FROM email_jobs WHERE status = 'pending' LIMIT 10");
$jobs = $result->fetch_all(MYSQLI_ASSOC);
$totalJobs = count($jobs);

if ($totalJobs == 0) {
    $db->close();
    exit(json_encode(['success' => true, 'message' => 'No pending email jobs found']));
}

$mj = new \Mailjet\Client($apiKey, $apiSecret, true, ['version' => 'v3.1']);
$successCount = 0;
$failCount = 0;


foreach ($jobs as $row) {
    // Get email contents
    $contentResult = $db->query("SELECT * FROM email_contents WHERE id = " . $row['content_id']);
    $contentRow = $contentResult->fetch_assoc();

    $base_attachment_path = "/Applications/XAMPP/htdocs/smart/smart8/uploads/users/" . $row['content_id'] . "/";

    // Get sender
    $senderResult = $db->query("SELECT * FROM senders WHERE id = " . $row['sender_id']);
    $senderRow = $senderResult->fetch_assoc();

    // Get recipient
    $recipientResult = $db->query("SELECT * FROM recipients WHERE id = " . $row['recipient_id']);
    $recipientRow = $recipientResult->fetch_assoc();

    // Get attachments
    $attachments = getAttachments($base_attachment_path);

    $email = [
        'From' => [
            'Email' => $senderRow['email'],
            'Name' => $senderRow['first_name'] . ' ' . $senderRow['last_name']
        ],
        'To' => [
            [
                'Email' => $recipientRow['email'],
                'Name' => $recipientRow['first_name'] . ' ' . $recipientRow['last_name']
            ]
        ],
        'Subject' => $contentRow['subject'],
        'TextPart' => $contentRow['message'],
        'HTMLPart' => nl2br($contentRow['message']),
        'CustomID' => "email_job_" . $row['id'],
        'Attachments' => $attachments
    ];

    $response = $mj->post(Resources::$Email, ['body' => ['Messages' => [$email]]]);

    if ($response->success()) {
        $status = 'success';
        $db->query("UPDATE email_jobs SET status = 'success' WHERE id = " . $row['id']);
        $successCount++;
    } else {
        $status = 'failed';
        $db->query("UPDATE email_jobs SET status = 'failed' WHERE id = " . $row['id']);
        $failCount++;
    }

    // Create log entry
    $logStmt = $db->prepare("INSERT INTO email_logs (job_id, status, response) VALUES (?, ?, ?)");
    $logResponse = json_encode($response->getBody());
    $logStmt->bind_param("iss", $row['id'], $status, $logResponse);
    $logStmt->execute();
    $logStmt->close();
}

$db->close();

// Calculate end time and duration
$end_time = microtime(true);
$duration = $end_time - $start_time;

// Prepare JSON response
$response = [
    'success' => true,
    'message' => "Email sending completed",
    'total_jobs' => $totalJobs,
    'success_count' => $successCount,
    'fail_count' => $failCount,
    'duration' => number_format($duration, 2),
    'avg_time_per_job' => number_format($duration / $totalJobs, 4)
];

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>