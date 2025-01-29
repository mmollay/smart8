<?php
require_once(__DIR__ . '/../t_config.php');

// API Credentials holen
$stmt = $db->prepare("SELECT * FROM api_credentials WHERE platform = 'bitget' AND is_active = 1 ORDER BY last_used DESC LIMIT 1");
$stmt->execute();
$cred = $stmt->get_result()->fetch_assoc();

if (!$cred) {
    die("Keine API Credentials gefunden");
}

// Beispiel Order-Updates
$test_updates = [
    'new_order' => [
        'data' => [[
            'symbol' => 'BTCUSDT_UMCBL',
            'orderId' => 'test_order_' . time(),
            'clientOrderId' => '',
            'size' => '0.01',
            'price' => '40000',
            'leverage' => '5',
            'side' => 'buy',
            'status' => 'new',
            'timestamp' => time() * 1000
        ]]
    ],
    'filled_order' => [
        'data' => [[
            'symbol' => 'BTCUSDT_UMCBL',
            'orderId' => 'test_order_' . time(),
            'clientOrderId' => '',
            'size' => '0.01',
            'price' => '40000',
            'priceAvg' => '40000',
            'leverage' => '5',
            'side' => 'buy',
            'status' => 'filled',
            'timestamp' => time() * 1000
        ]]
    ],
    'canceled_order' => [
        'data' => [[
            'symbol' => 'BTCUSDT_UMCBL',
            'orderId' => 'test_order_' . time(),
            'clientOrderId' => '',
            'size' => '0.01',
            'price' => '40000',
            'leverage' => '5',
            'side' => 'buy',
            'status' => 'canceled',
            'timestamp' => time() * 1000
        ]]
    ]
];

?>
<!DOCTYPE html>
<html>
<head>
    <title>BitGet Webhook Tester</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .container {
            padding: 20px;
        }
        .response {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="ui header">BitGet Webhook Tester</h2>
        
        <div class="ui form">
            <div class="field">
                <label>Test Szenario</label>
                <select id="test-scenario" class="ui dropdown">
                    <option value="new_order">Neue Order</option>
                    <option value="filled_order">Ausgeführte Order</option>
                    <option value="canceled_order">Stornierte Order</option>
                </select>
            </div>
            
            <button class="ui primary button" id="send-test">Test Senden</button>
        </div>

        <div class="ui segment response" style="display: none;">
            <h4>Response:</h4>
            <pre id="response-data"></pre>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#send-test').click(function() {
            var scenario = $('#test-scenario').val();
            var timestamp = Date.now().toString();
            var data = <?php echo json_encode($test_updates) ?>[scenario];
            var jsonData = JSON.stringify(data);
            
            // Test-Daten senden
            $.ajax({
                url: 'bitget_webhook.php',
                method: 'POST',
                data: jsonData,
                contentType: 'application/json',
                headers: {
                    'X-Bit-Timestamp': timestamp,
                    'X-Bit-Sign': '<?php echo base64_encode(hash_hmac('sha256', '', $cred['api_secret'], true)); ?>'
                },
                success: function(response) {
                    $('.response').show();
                    $('#response-data').text(JSON.stringify(response, null, 2));
                },
                error: function(xhr, status, error) {
                    $('.response').show();
                    $('#response-data').text('Error: ' + error + '\n' + xhr.responseText);
                }
            });
        });
    });
    </script>
</body>
</html>
