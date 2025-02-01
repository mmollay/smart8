<?php
require_once(__DIR__ . '/../t_config.php');
?>
<!DOCTYPE html>
<html>

<head>
    <title>Auto Trade Test</title>
    <?php require_once(__DIR__ . '/../includes/header.php'); ?>
    <style>
        .price-display {
            font-size: 1.5em;
            font-weight: bold;
            margin: 1em 0;
        }

        .calculation-box {
            background: #f9f9f9;
            padding: 1em;
            border-radius: 4px;
            margin: 1em 0;
        }

        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .status-indicator.active {
            background: #21ba45;
        }

        .status-indicator.inactive {
            background: #db2828;
        }
    </style>
</head>

<body>
    <div class="ui container">
        <h2 class="ui header">Auto Trade Test</h2>

        <!-- Status Box -->
        <div class="ui segment">
            <h3>System Status</h3>
            <div class="ui grid">
                <div class="four wide column">
                    <div>
                        <span class="status-indicator active"></span>
                        WebSocket: <span id="wsStatus">Verbinden...</span>
                    </div>
                </div>
                <div class="four wide column">
                    <div>Current Price: <span id="currentPrice">-</span></div>
                </div>
                <div class="four wide column">
                    <div>Last Signal: <span id="lastSignal">-</span></div>
                </div>
                <div class="four wide column">
                    <div>Auto Mode:
                        <div class="ui toggle checkbox">
                            <input type="checkbox" id="autoMode">
                            <label></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration -->
        <div class="ui form segment">
            <h3>Trading Configuration</h3>

            <div class="two fields">
                <!-- User Selection -->
                <div class="field">
                    <label>User</label>
                    <select class="ui dropdown" id="userId" required>
                        <option value="">User auswählen</option>
                        <?php
                        $stmt = $db->prepare("
                            SELECT 
                                u.id,
                                u.username,
                                u.company,
                                m.name as model_name
                            FROM users u
                            LEFT JOIN trading_parameter_models m ON m.id = u.default_parameter_model_id
                            WHERE u.active = 1
                            ORDER BY u.company ASC
                        ");
                        $stmt->execute();
                        $result = $stmt->get_result();

                        while ($row = $result->fetch_assoc()) {
                            $modelInfo = $row['model_name'] ? " (Modell: {$row['model_name']})" : "";
                            echo "<option value=\"{$row['id']}\">{$row['company']}{$modelInfo}</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Trading Pair -->
                <div class="field">
                    <label>Symbol</label>
                    <select class="ui dropdown" id="symbol" required>
                        <option value="ETHUSDT" selected>ETH/USDT</option>
                    </select>
                </div>
            </div>

            <!-- Model Parameters Display -->
            <div class="ui segment calculation-box">
                <h4>Model Parameters</h4>
                <div class="ui grid">
                    <div class="four wide column">
                        <div class="field">
                            <label>Leverage</label>
                            <div id="modelLeverage">-</div>
                        </div>
                    </div>
                    <div class="four wide column">
                        <div class="field">
                            <label>Position Size %</label>
                            <div id="modelPositionSize">-</div>
                        </div>
                    </div>
                    <div class="four wide column">
                        <div class="field">
                            <label>Take Profit %</label>
                            <div id="modelTakeProfit">-</div>
                        </div>
                    </div>
                    <div class="four wide column">
                        <div class="field">
                            <label>Stop Loss %</label>
                            <div id="modelStopLoss">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price Calculations -->
            <div class="ui segment calculation-box">
                <h4>Order Preview</h4>
                <div class="ui grid">
                    <div class="four wide column">
                        <div class="field">
                            <label>Side</label>
                            <div id="previewSide">-</div>
                        </div>
                    </div>
                    <div class="four wide column">
                        <div class="field">
                            <label>Entry Price</label>
                            <div id="previewEntry">-</div>
                        </div>
                    </div>
                    <div class="four wide column">
                        <div class="field">
                            <label>Take Profit</label>
                            <div id="previewTakeProfit">-</div>
                        </div>
                    </div>
                    <div class="four wide column">
                        <div class="field">
                            <label>Stop Loss</label>
                            <div id="previewStopLoss">-</div>
                        </div>
                    </div>
                </div>
                <div class="ui divider"></div>
                <div class="ui grid">
                    <div class="eight wide column">
                        <div class="field">
                            <label>Position Size</label>
                            <div id="previewPositionSize">-</div>
                        </div>
                    </div>
                    <div class="eight wide column">
                        <div class="field">
                            <label>Account Risk</label>
                            <div id="previewRisk">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Controls -->
            <div class="ui segment">
                <h4>Manual Test Controls</h4>
                <div class="ui buttons">
                    <button class="ui positive button" id="testBuy">Test Buy Signal</button>
                    <div class="or"></div>
                    <button class="ui negative button" id="testSell">Test Sell Signal</button>
                </div>
            </div>
        </div>

        <!-- Trade History -->
        <div class="ui segment">
            <h3>Test Trade History</h3>
            <table class="ui celled table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Side</th>
                        <th>Entry</th>
                        <th>TP</th>
                        <th>SL</th>
                        <th>Size</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="tradeHistory">
                </tbody>
            </table>
        </div>
    </div>

    <script src="../assets/js/auto_trade.js"></script>
</body>

</html>