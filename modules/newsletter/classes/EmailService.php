<?php
require_once(__DIR__ . '/../../../vendor/autoload.php');

use \Mailjet\Client;
use \Mailjet\Resources;

class EmailService
{
    private $db;
    private $mj;
    private $uploadBasePath;

    public function __construct($db, $apiKey, $apiSecret, $uploadBasePath)
    {
        $this->db = $db;
        $this->mj = new Client($apiKey, $apiSecret, true, ['version' => 'v3.1']);
        $this->uploadBasePath = $uploadBasePath;
    }

    public function sendSingleEmail($contentId, array $sender, array $recipient, string $subject, string $message, $jobId = null, $isTest = false)
    {
        try {
            // Debug log
            error_log("Starting email send process for jobId: " . ($jobId ?? 'test'));

            // Validate sender and recipient data
            if (empty($sender['email']) || empty($recipient['email'])) {
                throw new Exception('Sender and recipient email addresses are required');
            }

            // Prepare attachments
            $attachments = $this->prepareAttachments($contentId);

            // Prepare the email data
            $emailData = [
                'Messages' => [
                    [
                        'From' => [
                            'Email' => $sender['email'],
                            'Name' => $sender['name'] ?? ''
                        ],
                        'To' => [
                            [
                                'Email' => $recipient['email'],
                                'Name' => $recipient['name'] ?? ''
                            ]
                        ],
                        'Subject' => $subject,
                        'TextPart' => strip_tags($message),
                        'HTMLPart' => $message,
                        'CustomID' => $isTest ? "test_mail_{$contentId}" : "job_{$jobId}_{$contentId}_" . time(),
                        'TrackOpens' => 'enabled',
                        'TrackClicks' => 'enabled'
                    ]
                ]
            ];

            // Add attachments if there are any
            if (!empty($attachments)) {
                $emailData['Messages'][0]['Attachments'] = $attachments;
            }

            // Debug log the request
            error_log("Sending request to Mailjet with data: " . json_encode([
                'to' => $recipient['email'],
                'from' => $sender['email'],
                'subject' => $subject,
                'customId' => $emailData['Messages'][0]['CustomID']
            ]));

            // Send the email using Mailjet
            $response = $this->mj->post(Resources::$Email, ['body' => $emailData]);

            // Debug log the complete response
            error_log("Mailjet response: " . json_encode($response->getData()));

            if ($response->success()) {
                $responseData = $response->getData();

                // Detailed extraction of MessageID
                $messageId = null;
                if (isset($responseData['Messages'][0]['To'][0]['MessageID'])) {
                    $messageId = $responseData['Messages'][0]['To'][0]['MessageID'];
                    error_log("Successfully extracted MessageID: " . $messageId);
                } else {
                    error_log("Warning: MessageID not found in response: " . json_encode($responseData));
                    // Fallback MessageID generation if needed
                    $messageId = 'MJ_' . time() . '_' . uniqid();
                }

                // Log success status
                if (!$isTest) {
                    try {
                        $stmt = $this->db->prepare("
                            INSERT INTO email_logs 
                            (job_id, status, response, created_at) 
                            VALUES (?, 'send', 'MessageID: ' || ?, NOW())
                        ");
                        $stmt->bind_param("is", $jobId, $messageId);
                        $stmt->execute();
                    } catch (Exception $e) {
                        error_log("Failed to log email success: " . $e->getMessage());
                    }
                }

                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'message' => 'Email sent successfully'
                ];
            } else {
                $error = $response->getData()['Messages'][0]['Errors'][0]['ErrorMessage'] ?? 'Unknown error occurred';
                error_log("Mailjet error: " . $error);

                // Log error status
                if (!$isTest) {
                    try {
                        $stmt = $this->db->prepare("
                            INSERT INTO email_logs 
                            (job_id, status, response, created_at) 
                            VALUES (?, 'error', ?, NOW())
                        ");
                        $stmt->bind_param("is", $jobId, $error);
                        $stmt->execute();
                    } catch (Exception $e) {
                        error_log("Failed to log email error: " . $e->getMessage());
                    }
                }

                return [
                    'success' => false,
                    'error' => $error
                ];
            }

        } catch (Exception $e) {
            error_log("Exception in sendSingleEmail: " . $e->getMessage());

            // Log exception
            if (!$isTest && isset($jobId)) {
                try {
                    $stmt = $this->db->prepare("
                        INSERT INTO email_logs 
                        (job_id, status, response, created_at) 
                        VALUES (?, 'error', ?, NOW())
                    ");
                    $stmt->bind_param("is", $jobId, $e->getMessage());
                    $stmt->execute();
                } catch (Exception $logError) {
                    error_log("Failed to log email exception: " . $logError->getMessage());
                }
            }

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function prepareAttachments($contentId)
    {
        $attachments = [];
        $directory = $this->uploadBasePath . $contentId . "/";

        if (is_dir($directory)) {
            foreach (scandir($directory) as $file) {
                if ($file != "." && $file != "..") {
                    $full_path = $directory . $file;
                    if (is_file($full_path) && is_readable($full_path)) {
                        try {
                            $fileContent = file_get_contents($full_path);
                            if ($fileContent !== false) {
                                $attachments[] = [
                                    'ContentType' => mime_content_type($full_path) ?: 'application/octet-stream',
                                    'Filename' => $file,
                                    'Base64Content' => base64_encode($fileContent)
                                ];
                            }
                        } catch (Exception $e) {
                            error_log("Failed to process attachment {$file}: " . $e->getMessage());
                        }
                    }
                }
            }
        }

        return $attachments;
    }

    public function updateNewsletterStatus($contentId, $status)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE email_contents 
                SET status = ?, 
                    completed_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("ii", $status, $contentId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Failed to update newsletter status: " . $e->getMessage());
            return false;
        }
    }
}