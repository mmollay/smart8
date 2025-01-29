<?php

//APIKey
//bg_cc89302322ccb5c2c3942f70dfbd8d2e
//secretAPI
//c034f42fe42bec1b57982ee642fbf3f339c9b4eb6dd5ff0a68f81fd11ee0cde2


// test_stream.php

include __DIR__ . '/../t_config.php';

// Token aus der Datenbank holen
$query = "SELECT token FROM server_tokens WHERE is_active = 1 ORDER BY token_updated_at DESC LIMIT 1";
$result = $db->query($query);
$token = '';

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $token = $row['token'];
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Trade Stream Test</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.0/dist/semantic.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.0/dist/semantic.min.js"></script>
    <style>
        .log-container {
            height: 400px;
            overflow-y: auto;
            background: #f8f9fa;
            padding: 1em;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 1em 0;
        }

        .log-entry {
            margin-bottom: 0.5em;
            padding: 0.5em;
            border-bottom: 1px solid #eee;
        }

        .log-entry:hover {
            background: #fff;
        }

        .timestamp {
            color: #666;
            font-size: 0.9em;
        }

        .status-connected {
            color: green;
        }

        .status-disconnected {
            color: red;
        }
    </style>
</head>

<body>
    <div class="ui container" style="padding-top: 2em;">
        <h2 class="ui header">
            <i class="exchange icon"></i>
            <div class="content">
                Trade Stream Test
                <div class="sub header">WebSocket Connection Test</div>
            </div>
        </h2>

        <div class="ui segment">
            <div class="ui grid">
                <div class="eight wide column">
                    <div class="ui label">
                        Status: <span id="connection-status" class="status-disconnected">Disconnected</span>
                    </div>
                </div>
                <div class="eight wide column right aligned">
                    <button class="ui primary button" id="connect-btn">Connect</button>
                    <button class="ui red button" id="disconnect-btn" disabled>Disconnect</button>
                </div>
            </div>
        </div>

        <div class="log-container">
            <div id="log"></div>
        </div>
    </div>

    <script>
        let ws = null;
        const token = '<?php echo $token; ?>';

        function addLogEntry(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const logEntry = $('<div class="log-entry">')
                .html(`<span class="timestamp">[${timestamp}]</span> ${message}`);

            if (type === 'error') {
                logEntry.css('color', 'red');
            } else if (type === 'success') {
                logEntry.css('color', 'green');
            }

            $('#log').prepend(logEntry);
        }

        function connect() {
            if (ws) {
                ws.close();
            }

            try {
                ws = new WebSocket('wss://ethtestserver.ssi.at/ws/streamTrades');

                ws.onopen = function () {
                    addLogEntry('Connected to WebSocket server', 'success');
                    $('#connection-status').text('Connected').removeClass('status-disconnected').addClass('status-connected');
                    $('#connect-btn').prop('disabled', true);
                    $('#disconnect-btn').prop('disabled', false);

                    // Send authentication token
                    ws.send(JSON.stringify({
                        type: 'auth',
                        token: token
                    }));
                };

                ws.onmessage = function (event) {
                    try {
                        const data = JSON.parse(event.data);
                        addLogEntry('<pre>' + JSON.stringify(data, null, 2) + '</pre>');
                    } catch (e) {
                        addLogEntry('Received: ' + event.data);
                    }
                };

                ws.onerror = function (error) {
                    addLogEntry('WebSocket Error: ' + error.message, 'error');
                };

                ws.onclose = function () {
                    addLogEntry('Disconnected from WebSocket server', 'error');
                    $('#connection-status').text('Disconnected').removeClass('status-connected').addClass('status-disconnected');
                    $('#connect-btn').prop('disabled', false);
                    $('#disconnect-btn').prop('disabled', true);
                    ws = null;
                };

            } catch (error) {
                addLogEntry('Connection Error: ' + error.message, 'error');
            }
        }

        function disconnect() {
            if (ws) {
                ws.close();
            }
        }

        $('#connect-btn').click(connect);
        $('#disconnect-btn').click(disconnect);

        // Auto-Connect beim Laden
        $(document).ready(function () {
            if (token) {
                connect();
            } else {
                addLogEntry('No valid token found in database', 'error');
            }
        });
    </script>
</body>

</html>