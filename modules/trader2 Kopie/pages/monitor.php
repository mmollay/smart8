<?php
require_once(__DIR__ . '/../t_config.php');
require_once(__DIR__ . '/../bitget/bitget_api.php');

// Nur AJAX-Response zurückgeben wenn AJAX Request
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');

    try {
        // API Credentials holen
        $stmt = $db->prepare("
            SELECT * FROM api_credentials 
            WHERE platform = 'bitget' 
            AND is_active = 1 
            ORDER BY last_used DESC 
            LIMIT 1
        ");
        $stmt->execute();
        $cred = $stmt->get_result()->fetch_assoc();

        if (!$cred) {
            throw new Exception("Keine aktiven API Credentials gefunden");
        }

        // BitGet API initialisieren
        $bitget = new BitGetAPI($cred['api_key'], $cred['api_secret'], $cred['api_passphrase']);

        // Daten abrufen
        $data = [
            'positions' => $bitget->getPositions(),
            'activeOrders' => $bitget->getActiveOrders('BTCUSDT'),
            'accountBalance' => $bitget->getAccountBalance(),
            'btcPrice' => $bitget->getMarketPrice('BTCUSDT')
        ];

        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// HTML Template
?>
<style>
    .container {
        padding: 20px;
    }

    .price-up {
        color: green;
    }

    .price-down {
        color: red;
    }

    .ui.statistics .statistic {
        margin: 0 2em 2em 0;
    }

    .ui.statistics .statistic .value {
        font-size: 2em !important;
    }

    .ui.statistics .statistic .label {
        font-size: 1em !important;
    }
</style>
</head>

<body>
    <div class="ui container">
        <h2 class="ui header">BitGet Monitor</h2>

        <!-- Account Overview -->
        <div class="ui statistics" id="account-overview">
            <!-- wird durch AJAX gefüllt -->
        </div>

        <!-- Current Price -->
        <h3 class="ui header">Market Price</h3>
        <table class="ui celled table">
            <thead>
                <tr>
                    <th>Symbol</th>
                    <th>Last Price</th>
                    <th>24h High</th>
                    <th>24h Low</th>
                    <th>24h Volume</th>
                </tr>
            </thead>
            <tbody id="market-price">
                <!-- wird durch AJAX gefüllt -->
            </tbody>
        </table>

        <!-- Open Positions -->
        <h3 class="ui header">Open Positions</h3>
        <table class="ui celled table">
            <thead>
                <tr>
                    <th>Symbol</th>
                    <th>Side</th>
                    <th>Size</th>
                    <th>Entry Price</th>
                    <th>Mark Price</th>
                    <th>Unrealized PnL</th>
                    <th>Margin</th>
                    <th>Leverage</th>
                </tr>
            </thead>
            <tbody id="open-positions">
                <!-- wird durch AJAX gefüllt -->
            </tbody>
        </table>

        <!-- Active Orders -->
        <h3 class="ui header">Active Orders</h3>
        <table class="ui celled table">
            <thead>
                <tr>
                    <th>Symbol</th>
                    <th>Side</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Size</th>
                    <th>Filled</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody id="active-orders">
                <!-- wird durch AJAX gefüllt -->
            </tbody>
        </table>
    </div>

    <script>
        function updateMonitor() {
            $.get('pages/monitor.php?ajax=1')
                .done(function (response) {
                    if (!response.success) {
                        console.error('Error:', response.message);
                        return;
                    }

                    const data = response.data;

                    // Account Overview aktualisieren
                    let overviewHtml = '';
                    if (data.accountBalance && Array.isArray(data.accountBalance)) {
                        const account = data.accountBalance[0];
                        overviewHtml += `
                        <div class="statistic">
                            <div class="value">${parseFloat(account.available).toFixed(2)}</div>
                            <div class="label">Available USDT</div>
                        </div>
                        <div class="statistic">
                            <div class="value">${parseFloat(account.equity).toFixed(2)}</div>
                            <div class="label">Equity USDT</div>
                        </div>
                        <div class="statistic">
                            <div class="value ${parseFloat(account.unrealizedPL) >= 0 ? 'price-up' : 'price-down'}">
                                ${parseFloat(account.unrealizedPL).toFixed(2)}
                            </div>
                            <div class="label">Unrealized PnL</div>
                        </div>
                        <div class="statistic">
                            <div class="value">${(parseFloat(account.crossRiskRate) * 100).toFixed(2)}%</div>
                            <div class="label">Risk Rate</div>
                        </div>
                    `;
                    }
                    $('#account-overview').html(overviewHtml);

                    // Market Price aktualisieren
                    let priceHtml = '';
                    if (data.btcPrice) {
                        priceHtml = `
                        <tr>
                            <td>BTCUSDT</td>
                            <td>${parseFloat(data.btcPrice.last || 0).toFixed(2)}</td>
                            <td>${parseFloat(data.btcPrice.high24h || 0).toFixed(2)}</td>
                            <td>${parseFloat(data.btcPrice.low24h || 0).toFixed(2)}</td>
                            <td>${parseFloat(data.btcPrice.volume24h || 0).toFixed(2)}</td>
                        </tr>
                        `;
                    }
                    $('#market-price').html(priceHtml);

                    // Open Positions aktualisieren
                    let positionsHtml = '';
                    if (data.positions && Array.isArray(data.positions)) {
                        data.positions.forEach(function (position) {
                            if (parseFloat(position.total) > 0) {
                                const markPrice = position.markPrice || 0;
                                const unrealizedPL = position.unrealizedPL || 0;
                                const margin = position.margin || 0;

                                positionsHtml += `
                                <tr>
                                    <td>${position.symbol}</td>
                                    <td>${position.holdSide}</td>
                                    <td>${parseFloat(position.total).toFixed(4)}</td>
                                    <td>${parseFloat(position.averageOpenPrice).toFixed(2)}</td>
                                    <td>${parseFloat(markPrice).toFixed(2)}</td>
                                    <td class="${parseFloat(unrealizedPL) >= 0 ? 'price-up' : 'price-down'}">
                                        ${parseFloat(unrealizedPL).toFixed(2)}
                                    </td>
                                    <td>${parseFloat(margin).toFixed(2)}</td>
                                    <td>${position.leverage}x</td>
                                </tr>
                                `;
                            }
                        });
                    }
                    $('#open-positions').html(positionsHtml || '<tr><td colspan="8">Keine offenen Positionen</td></tr>');

                    // Active Orders aktualisieren
                    let ordersHtml = '';
                    if (data.activeOrders && Array.isArray(data.activeOrders)) {
                        data.activeOrders.forEach(function (order) {
                            const orderCategory = order.orderCategory === 'tp' ? 'Take Profit' :
                                order.orderCategory === 'sl' ? 'Stop Loss' :
                                    'Normal';
                            ordersHtml += `
                            <tr>
                                <td>${order.symbol}</td>
                                <td class="${order.side === 'buy' ? 'price-up' : 'price-down'}">${order.side.toUpperCase()}</td>
                                <td>${order.orderType} (${orderCategory})</td>
                                <td>${parseFloat(order.price).toFixed(2)}</td>
                                <td>${parseFloat(order.size).toFixed(4)}</td>
                                <td>${parseFloat(order.filledQty).toFixed(4)}</td>
                                <td>${order.status}</td>
                                <td>${new Date(parseInt(order.cTime)).toLocaleString()}</td>
                            </tr>
                            `;
                        });
                    }
                    $('#active-orders').html(ordersHtml || '<tr><td colspan="8">Keine aktiven Orders</td></tr>');
                })
                .fail(function (error) {
                    console.error('AJAX Error:', error);
                });
        }

        // Initial update
        updateMonitor();

        // Update alle 5 Sekunden
        setInterval(updateMonitor, 5000);
    </script>