<?php
class BrevoWebhookHandler
{
    private $db;
    private $emailService;
    private $apiKey;

    public function __construct($db, $emailService, $apiKey)
    {
        $this->db = $db;
        $this->emailService = $emailService;
        $this->apiKey = $apiKey;
    }

    public function handleRequest()
    {
        try {
            // Verifiziere Brevo-Signatur (wenn implementiert)
            if (!$this->verifyBrevoSignature()) {
                $this->logError('Ungültige Signatur');
                http_response_code(403);
                return ['success' => false, 'error' => 'Ungültige Signatur'];
            }

            // Lese Event-Daten
            $input = file_get_contents('php://input');
            if (empty($input)) {
                $this->logError('Keine Eingabedaten empfangen');
                return ['success' => false, 'error' => 'Keine Eingabedaten'];
            }

            $events = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logError('Ungültiges JSON: ' . json_last_error_msg());
                return ['success' => false, 'error' => 'Ungültiges JSON'];
            }

            // Brevo sendet möglicherweise ein einzelnes Event oder ein Array von Events
            if (!isset($events[0]) && isset($events['event'])) {
                $events = [$events];
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
            $this->logError('Fehler bei der Verarbeitung des Webhooks: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function verifyBrevoSignature()
    {
        $headers = getallheaders();
        $signature = isset($headers['X-Brevo-Signature']) ? $headers['X-Brevo-Signature'] : '';
        
        // Bei Brevo die Signaturprüfung implementieren
        // Hier sollte eine Implementierung nach Brevo's Dokumentation erfolgen
        // Beispiel: return hash_equals($signature, hash_hmac('sha256', file_get_contents('php://input'), $this->apiKey));
        
        // Vorübergehend:
        return true;
    }

    private function processEvent($event)
    {
        try {
            // Log eingehendes Event
            $this->logEvent($event);

            // Event an EmailService weiterleiten
            return $this->emailService->handleBrevoEvent($event);

        } catch (Exception $e) {
            $this->logError('Fehler bei der Verarbeitung des Events: ' . $e->getMessage());
            return false;
        }
    }

    private function logEvent($event)
    {
        $logFile = dirname(__DIR__) . '/logs/webhook_' . date('Y-m-d') . '.log';
        $logEntry = date('Y-m-d H:i:s') . ' Event empfangen: ' . json_encode($event) . PHP_EOL;
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    private function logError($message)
    {
        $logFile = dirname(__DIR__) . '/logs/webhook_errors.log';
        $logEntry = date('Y-m-d H:i:s') . ' Fehler: ' . $message . PHP_EOL;
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}
