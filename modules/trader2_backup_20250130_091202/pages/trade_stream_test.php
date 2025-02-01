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
            height: 300px;
            overflow-y: auto;
            background: #f8f9fa;
            padding: 1em;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 1em 0;
        }

        .signal-container {
            margin: 1em 0;
            padding: 1em;
            border-radius: 4px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
        }

        .action-text {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
        }

        .signal-buy {
            background: #21ba4510;
            border-left: 4px solid #21ba45;
        }
        .signal-buy .action-text {
            color: #21ba45;
            background: #21ba4510;
        }

        .signal-sell {
            background: #db282810;
            border-left: 4px solid #db2828;
        }
        .signal-sell .action-text {
            color: #db2828;
            background: #db282810;
        }

        .signal-hold {
            background: #76767610;
            border-left: 4px solid #767676;
        }
        .signal-hold .action-text {
            color: #767676;
            background: #76767610;
        }

        .price-info {
            font-family: monospace;
            font-size: 1.2em;
            margin-top: 10px;
            padding: 5px;
            background: rgba(0,0,0,0.05);
            border-radius: 4px;
        }

        .confidence-info {
            font-size: 0.9em;
            margin: 5px 0;
            color: #666;
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
                Trade Stream
                <div class="sub header">Live Trading Signals</div>
            </div>
        </h2>

        <div class="ui segment">
            <div class="ui grid">
                <div class="four wide column">
                    <div class="ui label">
                        Status: <span id="connection-status" class="status-disconnected">Disconnected</span>
                    </div>
                </div>
                <div class="four wide column">
                    <div class="ui input">
                        <input type="text" id="symbol" value="ETHUSDT" placeholder="Symbol...">
                    </div>
                </div>
                <div class="eight wide column right aligned">
                    <button class="ui primary button" id="connect-btn">Connect</button>
                    <button class="ui red button" id="disconnect-btn" disabled>Disconnect</button>
                </div>
            </div>
        </div>

        <!-- Signal Container -->
        <div id="signal-container" class="signal-container">
            <div class="action-text" id="action-text">Warte auf Signal...</div>
            <div id="current-signal"></div>
            <div id="signal-details" class="price-info"></div>
        </div>

        <div class="log-container">
            <div id="log"></div>
        </div>
        <div id="current-price"></div>
    </div>

    <script>
        let ws = null;
        const token = '<?php echo $token; ?>';
        let activeUsers = [];
        let lastAnalysis = null;
        let currentPrice = null;

        async function fetchActiveUsers() {
            try {
                const response = await fetch('../ajax/get_model_parameters.php');
                const data = await response.json();
                if (data.success) {
                    activeUsers = data.users;
                    console.log('Aktive Benutzer geladen:', activeUsers);
                }
            } catch (error) {
                console.error('Fehler beim Abrufen der Benutzer:', error);
            }
        }

        async function checkTradeSignals() {
            try {
                const response = await fetch('../ajax/get_latest_analysis.php?symbol=ETHUSDT');
                const data = await response.json();
                
                if (data.success) {
                    lastAnalysis = data.analysis;
                    if (currentPrice && lastAnalysis.entry_price) {
                        processTradeSignals();
                    }
                }
            } catch (error) {
                console.error('Fehler beim Prüfen der Handelssignale:', error);
            }
        }

        function processTradeSignals() {
            if (!lastAnalysis || !activeUsers.length || !currentPrice) return;

            const priceDiff = Math.abs(currentPrice - lastAnalysis.entry_price);
            const signals = [];

            // Nur Signale generieren, wenn der Preisunterschied mindestens 1 Punkt beträgt
            if (priceDiff >= 1) {
                const action = currentPrice > lastAnalysis.entry_price ? 'SELL' : 'BUY';
                
                activeUsers.forEach(user => {
                    const model = user.model;
                    const params = model.parameters;

                    // Prüfe ob alle notwendigen Parameter vorhanden sind
                    if (!params.take_profit || !params.stop_loss || !params.leverage || !params.position_size) {
                        console.warn(`Fehlende Parameter für Benutzer ${user.username}`);
                        return;
                    }

                    const signal = {
                        userId: user.id,
                        username: user.username,
                        modelId: model.id,
                        modelName: model.name,
                        modelDescription: model.description,
                        symbol: 'ETHUSDT',
                        action: action,
                        currentPrice: currentPrice,
                        entryPrice: lastAnalysis.entry_price,
                        priceDifference: priceDiff,
                        takeProfit: action === 'BUY' 
                            ? currentPrice + (currentPrice * (params.take_profit / 100))
                            : currentPrice - (currentPrice * (params.take_profit / 100)),
                        stopLoss: action === 'BUY'
                            ? currentPrice - (currentPrice * (params.stop_loss / 100))
                            : currentPrice + (currentPrice * (params.stop_loss / 100)),
                        leverage: params.leverage,
                        positionSize: params.position_size,
                        timestamp: new Date().toISOString()
                    };
                    
                    signals.push(signal);
                    addLogEntry(`Handelssignal generiert für ${user.username}: ${action} bei ${currentPrice}`, 'success');
                    
                    // Optional: Sende das Signal an den Server zur Speicherung
                    saveTradeSignal(signal);
                });

                console.log('Generierte Handelssignale:', signals);
            }
        }

        async function saveTradeSignal(signal) {
            try {
                const response = await fetch('../ajax/save_trade_signal.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(signal)
                });
                
                const data = await response.json();
                if (!data.success) {
                    console.error('Fehler beim Speichern des Signals:', data.error);
                }
            } catch (error) {
                console.error('Fehler beim Senden des Signals:', error);
            }
        }

        function handleWebSocketMessage(event) {
            try {
                const data = JSON.parse(event.data);
                if (data.k && data.k.c) {
                    currentPrice = parseFloat(data.k.c);
                    $('#current-price').text(currentPrice.toFixed(2));
                    checkTradeSignals();
                }
            } catch (error) {
                console.error('Fehler beim Verarbeiten der WebSocket-Nachricht:', error);
            }
        }

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
                    
                    // Initial signal update
                    checkTradeSignals();
                };

                ws.onmessage = handleWebSocketMessage;

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

        // Event handler for symbol change
        $('#symbol').on('change', function() {
            if (ws) {
                checkTradeSignals();
            }
        });

        // Auto-Connect beim Laden
        $(document).ready(async function () {
            await fetchActiveUsers();
            setInterval(fetchActiveUsers, 60000); // Aktualisiere Benutzer jede Minute
            
            // WebSocket-Verbindung
            connect();
        });
    </script>
</body>

</html>