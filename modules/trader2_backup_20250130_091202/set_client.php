<?php
session_start();
require(__DIR__ . '/t_config.php');

// Debug-Ausgabe
error_log("Set Client - Session ID: " . session_id());
error_log("Set Client - Session Data: " . print_r($_SESSION, true));

// Client ID setzen wenn übergeben
if (isset($_POST['client_id'])) {
    $_SESSION['client_id'] = (int)$_POST['client_id'];
    
    // In der DB speichern
    $stmt = $db->prepare("
        INSERT INTO user_sessions (session_id, client_id) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE client_id = ?
    ");
    $session_id = session_id();
    $client_id = (int)$_POST['client_id'];
    $stmt->bind_param("sii", $session_id, $client_id, $client_id);
    $stmt->execute();

    error_log("Set Client - Client ID gesetzt: " . $client_id);
    header('Location: pages/monitor.php');
    exit;
}

// Verfügbare Clients aus der DB holen
$result = $db->query("SELECT id, username FROM clients ORDER BY id");
$clients = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Set Client ID</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
</head>
<body>
    <div class="ui container" style="padding-top: 50px;">
        <div class="ui segment">
            <h2>Set Client ID</h2>
            <form class="ui form" method="POST">
                <div class="field">
                    <label>Client</label>
                    <select name="client_id" class="ui dropdown" required>
                        <option value="">Wähle Client...</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?php echo $client['id']; ?>">
                                <?php echo htmlspecialchars($client['username'] . ' (ID: ' . $client['id'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="ui button primary" type="submit">Set Client & Login</button>
            </form>
            
            <div class="ui segment">
                <h4>Debug Info</h4>
                <pre>
Session ID: <?php echo session_id(); ?>

Session Data:
<?php print_r($_SESSION); ?>

DB Session:
<?php 
$stmt = $db->prepare("SELECT * FROM user_sessions WHERE session_id = ?");
$session_id = session_id();
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();
print_r($result->fetch_assoc());
?>
                </pre>
            </div>
        </div>
    </div>
</body>
</html>
