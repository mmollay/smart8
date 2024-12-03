<?php
require_once __DIR__ . '/../src/bootstrap.php';

$key = $_GET['key'] ?? '';
$success = false;
$message = '';

try {
    if (empty($key)) {
        throw new Exception('Ungültiger Verifizierungslink');
    }

    // Benutzer mit Verify Key suchen
    $stmt = $db->prepare("
        SELECT user_id, user_name
        FROM user2company 
        WHERE verify_key = ? AND verified = 0
        AND reg_date > DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Ungültiger oder abgelaufener Verifizierungslink');
    }

    $user = $result->fetch_assoc();

    // Benutzer verifizieren
    $stmt = $db->prepare("
        UPDATE user2company 
        SET verified = 1,
            verify_key = ''
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $user['user_id']);

    if (!$stmt->execute()) {
        throw new Exception('Fehler bei der Verifizierung');
    }

    $success = true;
    $message = 'Ihre E-Mail-Adresse wurde erfolgreich verifiziert. Sie können sich jetzt anmelden.';

} catch (Exception $e) {
    $message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Mail Verifizierung - Smart System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .verify-container {
            width: 100%;
            max-width: 450px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            text-align: center;
        }

        .logo-container {
            margin-bottom: 2rem;
        }

        .logo-container img {
            max-width: 200px;
            height: auto;
        }
    </style>
</head>

<body>
    <div class="verify-container">
        <div class="logo-container">
            <img src="../img/logo.png" alt="SSI Logo" />
        </div>

        <?php if ($success): ?>
            <div class="ui success message">
                <div class="header">Verifizierung erfolgreich</div>
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php else: ?>
            <div class="ui negative message">
                <div class="header">Verifizierung fehlgeschlagen</div>
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

        <a href="login.php" class="ui large teal button">
            <i class="sign-in icon"></i>
            Zum Login
        </a>
    </div>
</body>

</html>