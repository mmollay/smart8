<?php
// Disable error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

require_once(__DIR__ . '/../n_config.php');
require_once(__DIR__ . '/../classes/EmailService.php');

// Script start time
$start_time = microtime(true);

try {
    $db = new mysqli($host, $username, $password, $dbname);
    if ($db->connect_error) {
        throw new Exception('Database connection failed');
    }

    // Initialize EmailService
    $emailService = new EmailService(
        $db,
        $apiKey,
        $apiSecret,
        uploadBasePath: $uploadBasePath
    );

    // Search for pending jobs in the database
    $result = $db->query("
        SELECT 
            ej.*,
            ec.subject,
            ec.message,
            s.email as sender_email,
            CONCAT(s.first_name, ' ', s.last_name) as sender_name,
            r.email as recipient_email,
            r.first_name as recipient_first_name, 
            r.last_name as recipient_last_name,
            r.company as recipient_company,
            r.gender as recipient_gender,
            r.title as recipient_title
        FROM email_jobs ej
        JOIN email_contents ec ON ej.content_id = ec.id  
        JOIN senders s ON ej.sender_id = s.id
        JOIN recipients r ON ej.recipient_id = r.id
        WHERE ej.status = 'pending'
        LIMIT 10
    ");

    $jobs = $result->fetch_all(MYSQLI_ASSOC);
    $totalJobs = count($jobs);

    if ($totalJobs == 0) {
        throw new Exception('No pending email jobs found');
    }

    $successCount = 0;
    $failCount = 0;

    foreach ($jobs as $job) {
        // Prepare sender and recipient data
        $sender = [
            'email' => $job['sender_email'],
            'name' => $job['sender_name']
        ];

        $recipient = [
            'email' => $job['recipient_email'],
            'first_name' => $job['recipient_first_name'],
            'last_name' => $job['recipient_last_name'],
            'company' => $job['recipient_company'],
            'gender' => $job['recipient_gender'],
            'title' => $job['recipient_title']
        ];

        // Send email using EmailService
        $result = $emailService->sendSingleEmail(
            $job['content_id'],
            $sender,
            $recipient,
            $job['subject'],
            $job['message'],
            $job['id'],
            false // isTest = false              
        );

        if ($result['success']) {
            $successCount++;
        } else {
            $failCount++;
        }
    }

    // Calculate timing information
    $end_time = microtime(true);
    $duration = $end_time - $start_time;

    // Update content send_status if all recipients have been processed
    foreach ($jobs as $job) {
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total_recipients,
                SUM(CASE WHEN status IN ('success', 'failed', 'bounce', 'blocked') THEN 1 ELSE 0 END) as processed_recipients
            FROM email_jobs  
            WHERE content_id = ?
        ");
        $stmt->bind_param("i", $job['content_id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result['total_recipients'] > 0 && $result['total_recipients'] == $result['processed_recipients']) {
            $emailService->updateNewsletterStatus($job['content_id'], 1); // 1 = completely sent
        }
    }

    // Prepare success response
    $response = [
        'success' => true,
        'message' => 'Email sending completed',
        'total_jobs' => $totalJobs,
        'success_count' => $successCount,
        'fail_count' => $failCount,
        'duration' => number_format($duration, 2),
        'avg_time_per_job' => number_format($duration / $totalJobs, 4),
        'memory_usage' => formatBytes(memory_get_peak_usage(true))
    ];

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
} finally {
    if (isset($db)) {
        $db->close();
    }
}

// Helper function to format memory usage
function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);