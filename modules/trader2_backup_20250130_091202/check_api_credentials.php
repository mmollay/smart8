<?php
require_once(__DIR__ . '/t_config.php');

// Alle API Credentials abrufen
$result = $db->query("
    SELECT 
        id,
        user_id,
        platform,
        SUBSTRING(api_key, 1, 10) as api_key_preview,
        SUBSTRING(api_secret, 1, 10) as api_secret_preview,
        is_active,
        created_at,
        last_used
    FROM api_credentials 
    ORDER BY id DESC
");

// HTML Ausgabe
?>
<!DOCTYPE html>
<html>
<head>
    <title>API Credentials Check</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
</head>
<body>
    <div class="ui container" style="padding: 20px;">
        <h2 class="ui header">API Credentials</h2>
        
        <div class="ui segment">
            <h4>Aktuelle Datenbank: <?= $db->query("SELECT DATABASE()")->fetch_row()[0] ?></h4>
        </div>
        
        <table class="ui celled table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Platform</th>
                    <th>API Key (Preview)</th>
                    <th>API Secret (Preview)</th>
                    <th>Active</th>
                    <th>Created</th>
                    <th>Last Used</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['user_id']) ?></td>
                    <td><?= htmlspecialchars($row['platform']) ?></td>
                    <td><?= htmlspecialchars($row['api_key_preview']) ?>...</td>
                    <td><?= htmlspecialchars($row['api_secret_preview']) ?>...</td>
                    <td>
                        <div class="ui toggle checkbox">
                            <input type="checkbox" 
                                   data-id="<?= $row['id'] ?>" 
                                   <?= $row['is_active'] ? 'checked' : '' ?>>
                            <label></label>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td><?= htmlspecialchars($row['last_used']) ?></td>
                    <td>
                        <button class="ui tiny button" onclick="testCredentials(<?= $row['id'] ?>)">
                            Test
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="ui modal">
            <i class="close icon"></i>
            <div class="header">API Test Result</div>
            <div class="content">
                <p></p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js"></script>
    <script>
    $('.checkbox').checkbox({
        onChange: function() {
            const id = $(this).find('input').data('id');
            const active = $(this).checkbox('is checked');
            
            $.post('ajax/toggle_api_status.php', {
                id: id,
                active: active ? 1 : 0
            }).done(function(response) {
                console.log('Status updated:', response);
            }).fail(function(error) {
                console.error('Error:', error);
                $(this).checkbox('toggle');
            });
        }
    });

    function testCredentials(id) {
        $.get('ajax/test_api.php', {
            id: id
        }).done(function(response) {
            $('.ui.modal .content p').text(response.message);
            $('.ui.modal').modal('show');
        }).fail(function(error) {
            $('.ui.modal .content p').text('Error: ' + error.responseText);
            $('.ui.modal').modal('show');
        });
    }
    </script>
</body>
</html>
