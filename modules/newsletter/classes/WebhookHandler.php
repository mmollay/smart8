<?php
class WebhookHandler
{
    private $db;
    private $emailService;
    private $apiSecret;

    public function __construct($db, $emailService, $apiSecret)
    {
        $this->db = $db;
        $this->emailService = $emailService;
        $this->apiSecret = $apiSecret;
    }

    public function handleRequest()
    {
        try {
            // Verifiziere Mailjet-Signatur
            if (!$this->verifyMailjetSignature()) {
                $this->logError('Invalid signature');
                http_response_code(403);
                return ['success' => false, 'error' => 'Invalid signature'];
            }

            // Lese Event-Daten
            $input = file_get_contents('php://input');
            if (empty($input)) {
                $this->logError('No input received');
                return ['success' => false, 'error' => 'No input'];
            }

            $events = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logError('Invalid JSON: ' . json_last_error_msg());
                return ['success' => false, 'error' => 'Invalid JSON'];
            }

            // Verarbeite Events
            $processed = 0;
            foreach ($events as $event) {
                if ($this->processEvent($event)) {
                    $processed++;
                }
            }

            return [
                'success' => true,
                'processed' => $processed,
                'total' => count($events)
            ];

        } catch (Exception $e) {
            $this->logError('Error processing webhook: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function verifyMailjetSignature()
    {
        $headers = getallheaders();
        $signature = isset($headers['X-Mailjet-Signature']) ? $headers['X-Mailjet-Signature'] : '';

        // Implementieren Sie hier Ihre Signaturprüfung
        // Beispiel:
        // return hash_equals($signature, hash_hmac('sha256', file_get_contents('php://input'), $this->apiSecret));

        // Vorübergehend:
        return true;
    }

    private function processEvent($event)
    {
        try {
            // Log eingehendes Event
            $this->logEvent($event);

            // Event an EmailService weiterleiten
            return $this->emailService->handleMailjetEvent($event);

        } catch (Exception $e) {
            $this->logError('Error processing event: ' . $e->getMessage());
            return false;
        }
    }

    private function logEvent($event)
    {
        $logFile = dirname(__DIR__) . '/logs/webhook_' . date('Y-m-d') . '.log';
        $logEntry = date('Y-m-d H:i:s') . ' Event received: ' . json_encode($event) . PHP_EOL;
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    private function logError($message)
    {
        $logFile = dirname(__DIR__) . '/logs/webhook_errors.log';
        $logEntry = date('Y-m-d H:i:s') . ' Error: ' . $message . PHP_EOL;
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}