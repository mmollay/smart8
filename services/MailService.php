<?php
// services/MailService.php

namespace Smart\Services;

require_once(__DIR__ . '/../vendor/autoload.php');
use \Mailjet\Client;
use \Mailjet\Resources;

class MailService
{
    private static $instance = null;
    private $client;
    private $config;

    private function __construct()
    {
        // Lade Mail-Konfiguration
        $this->config = require __DIR__ . '/../config/mail_config.php';

        // Initialisiere Mailjet-Client
        $this->client = new Client(
            $this->config['mailjet']['api_key'],
            $this->config['mailjet']['api_secret'],
            true,
            ['version' => 'v3.1']
        );
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function sendMail($to, $subject, $htmlContent, $options = [])
    {
        // Default-Absender aus Konfiguration
        $sender = $options['sender'] ?? $this->config['default_sender'];

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => $sender['email'],
                        'Name' => $sender['name']
                    ],
                    'To' => [
                        [
                            'Email' => $to,
                            'Name' => $options['recipient_name'] ?? ''
                        ]
                    ],
                    'Subject' => $subject,
                    'TextPart' => strip_tags($htmlContent),
                    'HTMLPart' => $htmlContent
                ]
            ]
        ];

        // Füge Anhänge hinzu, falls vorhanden
        if (!empty($options['attachments'])) {
            $body['Messages'][0]['Attachments'] = $this->prepareAttachments($options['attachments']);
        }

        try {
            $response = $this->client->post(Resources::$Email, ['body' => $body]);

            // Log the email attempt
            $this->logEmail($to, $subject, $response->success());

            return [
                'success' => $response->success(),
                'data' => $response->getData()
            ];
        } catch (\Exception $e) {
            error_log("Mail sending error: " . $e->getMessage());
            $this->logEmail($to, $subject, false, $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function sendPasswordResetMail($to, $resetToken, $userData = [])
    {
        $resetLink = "https://" . $_SERVER['HTTP_HOST'] . "/auth/new_password.php?token=" . $resetToken;

        $emailContent = "
            <h2>{$this->config['templates']['password_reset']['title']}</h2>
            <p>Sehr geehrte(r) {$userData['firstname']} {$userData['secondname']},</p>
            <p>Sie haben eine Anfrage zum Zurücksetzen Ihres Passworts gestellt.</p>
            <p>Klicken Sie auf den folgenden Link, um ein neues Passwort zu erstellen:</p>
            <p><a href='{$resetLink}'>{$resetLink}</a></p>
            <p>Dieser Link ist 60 Minuten gültig.</p>
            <p>Falls Sie keine Passwort-Zurücksetzung angefordert haben, ignorieren Sie diese E-Mail.</p>
            <br>
            <p>Mit freundlichen Grüßen</p>
            <p>Ihr {$this->config['default_sender']['name']}</p>
        ";

        return $this->sendMail(
            $to,
            $this->config['templates']['password_reset']['subject'],
            $emailContent,
            [
                'recipient_name' => $userData['firstname'] . ' ' . $userData['secondname']
            ]
        );
    }

    private function prepareAttachments($attachments)
    {
        $prepared = [];
        foreach ($attachments as $attachment) {
            if (file_exists($attachment['path'])) {
                $prepared[] = [
                    'ContentType' => mime_content_type($attachment['path']),
                    'Filename' => $attachment['name'],
                    'Base64Content' => base64_encode(file_get_contents($attachment['path']))
                ];
            }
        }
        return $prepared;
    }

    private function logEmail($recipient, $subject, $success, $error = null)
    {
        try {
            global $db; // Oder besser: Dependency Injection

            $stmt = $db->prepare("
                INSERT INTO mail_log (
                    recipient_email,
                    subject,
                    success,
                    error_message,
                    sent_at
                ) VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param('ssis', $recipient, $subject, $success, $error);
            $stmt->execute();
        } catch (\Exception $e) {
            error_log("Mail-Log Error: " . $e->getMessage());
        }
    }
}