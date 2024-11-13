<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/../n_config.php');
require_once(__DIR__ . '/../classes/EmailService.php');
require_once(__DIR__ . '/../classes/PlaceholderService.php');

// Script start time
$start_time = microtime(true);
$log = [];

try {
    // Initialize database connection
    $db = new mysqli($host, $username, $password, $dbname);
    if ($db->connect_error) {
        throw new Exception("Database connection failed: " . $db->connect_error);
    }
    $db->set_charset('utf8mb4');

    // Initialize Services
    $emailService = new EmailService(
        $db,
        $apiKey,
        $apiSecret,
        uploadBasePath: $uploadBasePath
    );
    $placeholderService = PlaceholderService::getInstance();

    // Search for pending jobs in the database
    $result = $db->query("
        SELECT 
            ej.*,
            ec.subject,
            ec.message,
            s.email as sender_email,
            s.first_name as sender_first_name,
            s.last_name as sender_last_name,
            CONCAT(s.first_name, ' ', s.last_name) as sender_name,
            s.company as sender_company,
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
        ORDER BY ej.created_at ASC
        LIMIT 10
    ");

    $jobs = $result->fetch_all(MYSQLI_ASSOC);
    $totalJobs = count($jobs);

    if ($totalJobs == 0) {
        throw new Exception('No pending email jobs found');
    }

    $log[] = "Found {$totalJobs} pending email jobs";

    $successCount = 0;
    $failCount = 0;
    $processedJobs = [];

    foreach ($jobs as $job) {
        try {
            // Create placeholders for current recipient
            $recipientPlaceholders = $placeholderService->createPlaceholders([
                'first_name' => $job['recipient_first_name'],
                'last_name' => $job['recipient_last_name'],
                'email' => $job['recipient_email'],
                'company' => $job['recipient_company'],
                'gender' => $job['recipient_gender'],
                'title' => $job['recipient_title']
            ]);

            // Replace placeholders in subject and message
            $subject = $placeholderService->replacePlaceholders($job['subject'], $recipientPlaceholders);
            $message = $placeholderService->replacePlaceholders($job['message'], $recipientPlaceholders);

            // Generate unsubscribe link
            $unsubscribeUrl = "https://" . $_SERVER['HTTP_HOST'] . "/unsubscribe.php?email=" .
                urlencode($job['recipient_email']) . "&id=" . $job['id'];
            $unsubscribeLink = "<br><br><hr><p style='font-size: 12px; color: #666;'>
                Falls Sie keine weiteren E-Mails erhalten möchten, 
                können Sie sich hier <a href='{$unsubscribeUrl}'>abmelden</a>.</p>";
            $message .= $unsubscribeLink;

            // Prepare sender data
            $sender = [
                'email' => $job['sender_email'],
                'name' => $job['sender_name']
            ];

            // Prepare recipient data
            $recipient = [
                'email' => $job['recipient_email'],
                'name' => "{$job['recipient_first_name']} {$job['recipient_last_name']}"
            ];

            // Send email using EmailService
            $result = $emailService->sendSingleEmail(
                $job['content_id'],
                $sender,
                $recipient,
                $subject,
                $message,
                $job['id']
            );

            if ($result['success']) {
                // Update job status to 'send' instead of 'sent'
                $stmt = $db->prepare("
                    UPDATE email_jobs 
                    SET 
                        status = 'send',          -- Changed from 'sent' to 'send'
                        sent_at = NOW(),
                        message_id = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->bind_param("si", $result['message_id'], $job['id']);
                $stmt->execute();

                // Log initial status
                $stmt = $db->prepare("
                    INSERT INTO status_log 
                    (event, timestamp, message_id, email) 
                    VALUES ('send', NOW(), ?, ?)   -- Changed from 'sent' to 'send'
                ");
                $stmt->bind_param("ss", $result['message_id'], $job['recipient_email']);
                $stmt->execute();

                $successCount++;
                $log[] = "Successfully sent email to {$job['recipient_email']} (Job ID: {$job['id']})";
            } else {
                // Update job status to failed
                $stmt = $db->prepare("
                    UPDATE email_jobs 
                    SET 
                        status = 'failed',
                        error_message = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $errorMessage = substr($result['error'] ?? 'Unknown error', 0, 255);
                $stmt->bind_param("si", $errorMessage, $job['id']);
                $stmt->execute();

                $failCount++;
                $log[] = "Failed to send email to {$job['recipient_email']}: " . ($result['error'] ?? 'Unknown error') . " (Job ID: {$job['id']})";
            }

            $processedJobs[] = $job['id'];

        } catch (Exception $e) {
            $failCount++;
            $log[] = "Error processing job {$job['id']}: " . $e->getMessage();

            // Update job status to failed
            try {
                $stmt = $db->prepare("
                    UPDATE email_jobs 
                    SET 
                        status = 'failed',
                        error_message = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $errorMessage = substr($e->getMessage(), 0, 255);
                $stmt->bind_param("si", $errorMessage, $job['id']);
                $stmt->execute();
            } catch (Exception $updateError) {
                $log[] = "Failed to update error status for job {$job['id']}: " . $updateError->getMessage();
            }
        }
    }

    // Update newsletter status
    foreach (array_unique(array_column($jobs, 'content_id')) as $contentId) {
        $emailService->updateNewsletterStatus($contentId, 1);
        $log[] = "Newsletter ID {$contentId} marked as sent";
    }

    // Calculate timing and memory information
    $end_time = microtime(true);
    $duration = $end_time - $start_time;
    $memoryUsage = memory_get_peak_usage(true);

    // Prepare success response
    $response = [
        'success' => true,
        'message' => 'Email sending completed',
        'statistics' => [
            'total_jobs' => $totalJobs,
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'processed_jobs' => $processedJobs,
            'duration_seconds' => number_format($duration, 2),
            'avg_time_per_mail' => $totalJobs > 0 ? number_format($duration / $totalJobs, 4) : 0,
            'memory_usage' => formatBytes($memoryUsage),
            'memory_limit' => ini_get('memory_limit')
        ],
        'log' => $log
    ];

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage(),
        'log' => $log
    ];
} finally {
    if (isset($db) && $db instanceof mysqli) {
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

// Output buffering to prevent header issues
$output = ob_get_clean();
if (!empty($output)) {
    $log[] = "Warning: Unexpected output: " . $output;
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);