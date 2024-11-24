<?php
namespace Smart\Services;

class GoogleAuthService
{
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $db;

    public function __construct($db, array $config)
    {
        $this->db = $db;
        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->redirectUri = $config['redirect_uri'];
    }

    private function createNewUser($googleUser)
    {
        // Generate random password and verify key
        $password = bin2hex(random_bytes(16));
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $verifyKey = md5(uniqid(rand(), true)); // Generiere einen eindeutigen Verify-Key

        $stmt = $this->db->prepare("
            INSERT INTO user2company (
                company_id,
                user_name,
                firstname,
                secondname,
                password,
                verified,
                right_id,
                user_checked,
                zip,
                city,
                street,
                gender,
                country,
                number_of_smartpage,
                verify_key,
                superuser,
                locked,
                parent_id
            ) VALUES (
                1,
                ?,
                ?,
                ?,
                ?,
                1,
                1000,
                '1',
                0,
                '',
                '',
                'n',
                'at',
                1,
                ?,
                0,
                0,
                0
            )
        ");

        $email = $googleUser['email'];
        $firstName = $googleUser['given_name'] ?? '';
        $lastName = $googleUser['family_name'] ?? '';

        $stmt->bind_param(
            "sssss",
            $email,
            $firstName,
            $lastName,
            $hashedPassword,
            $verifyKey
        );

        if ($stmt->execute()) {
            $userId = $stmt->insert_id;

            // Optional: Log die erfolgreiche Erstellung
            error_log("Neuer Benutzer über Google Auth erstellt: ID = $userId, Email = $email");

            return $this->loginExistingUser($userId);
        }

        // Log den Fehler für Debugging
        error_log("Fehler beim Erstellen des Benutzers: " . $stmt->error);

        return [
            'success' => false,
            'message' => 'Fehler beim Erstellen des Accounts'
        ];
    }

    private function loginExistingUser($userId)
    {
        $_SESSION['client_id'] = $userId;
        return [
            'success' => true,
            'message' => 'Login erfolgreich',
            'redirect' => '../modules/main/index.php'
        ];
    }

    public function getAuthUrl()
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'email profile',
            'access_type' => 'online',
            'prompt' => 'select_account'
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    public function handleCallback($code)
    {
        // Exchange code for access token
        $tokenData = $this->getAccessToken($code);
        if (!$tokenData) {
            return false;
        }

        // Get user info with access token
        $userInfo = $this->getUserInfo($tokenData['access_token']);
        if (!$userInfo) {
            return false;
        }

        // Check if user exists
        $stmt = $this->db->prepare("SELECT user_id FROM user2company WHERE user_name = ?");
        $stmt->bind_param("s", $userInfo['email']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User exists - log them in
            $user = $result->fetch_assoc();
            return $this->loginExistingUser($user['user_id']);
        } else {
            // Create new user
            return $this->createNewUser($userInfo);
        }
    }

    private function getAccessToken($code)
    {
        $ch = curl_init('https://oauth2.googleapis.com/token');

        $postData = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code'
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData)
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    private function getUserInfo($accessToken)
    {
        $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken
            ]
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}