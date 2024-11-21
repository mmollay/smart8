<?php
// auth/password_helper.php

class PasswordHelper
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createResetToken($userId)
    {
        $token = bin2hex(random_bytes(32));

        // Alte Token löschen
        $stmt = $this->db->prepare("DELETE FROM password_resets WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        // Neuen Token speichern
        $stmt = $this->db->prepare(
            "INSERT INTO password_resets (user_id, token, expires_at) 
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))"
        );
        $stmt->bind_param("is", $userId, $token);

        if ($stmt->execute()) {
            return $token;
        }
        return false;
    }

    public function validateToken($token)
    {
        $stmt = $this->db->prepare(
            "SELECT pr.user_id, u.firstname, u.secondname, u.user_name 
             FROM password_resets pr 
             JOIN user2company u ON pr.user_id = u.user_id 
             WHERE pr.token = ? AND pr.expires_at > NOW()"
        );
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    public function updatePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("UPDATE user2company SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);

        if ($stmt->execute()) {
            // Token löschen nach erfolgreicher Aktualisierung
            $stmt = $this->db->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            return true;
        }
        return false;
    }
}