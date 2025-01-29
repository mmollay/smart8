<?php
include __DIR__ . '/../t_config.php';

// Parameter Models laden
$models_query = "SELECT id, name FROM trading_parameter_models WHERE is_active = 1 ORDER BY name";
$models_result = $db->query($models_query);

// User laden
$users_query = "SELECT u.id, u.username, u.default_parameter_model_id 
                FROM users u 
                WHERE u.active = 1 
                ORDER BY u.username";
$users_result = $db->query($users_query);
?>

<div class="ui container">
    <!-- Trading Form -->
    <div class="ui segment">
        <h2 class="ui dividing header">
            <i class="money bill alternate icon"></i>
            Manual Trading
        </h2>

        <form class="ui form" id="tradeForm" method="post">
            <!-- User & Parameter Model -->
            <div class="two fields">
                <div class="field">
                    <div class="ui labeled input">
                        <div class="ui label fixed-width-label">User</div>
                        <select class="ui dropdown" name="user_id" id="userId" required>
                            <option value="">--User wählen--</option>
                            <?php while ($row = $users_result->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>"
                                    data-model="<?= $row['default_parameter_model_id'] ?? '' ?>">
                                    <?= htmlspecialchars($row['username']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="field">
                    <div class="ui labeled input">
                        <div class="ui label fixed-width-label">Parameter Model</div>
                        <select class="ui dropdown" name="parameter_model_id" id="parameterModel">
                            <option value="">--Model wählen--</option>
                            <?php while ($row = $models_result->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>">
                                    <?= htmlspecialchars($row['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Symbol & Direction -->
            <div class="two fields">
                <div class="field">
                    <label>Symbol</label>
                    <input type="text" name="symbol" value="ETHUSDT_UMCBL" readonly>
                </div>
                <div class="field">
                    <label>Direction</label>
                    <select class="ui dropdown" name="side" id="tradeSide" required>
                        <option value="">Select Direction</option>
                        <option value="buy">Buy/Long</option>
                        <option value="sell">Sell/Short</option>
                    </select>
                </div>
            </div>

            <!-- Size & Entry Price -->
            <div class="two fields">
                <div class="field">
                    <label>Position Size (ETH)</label>
                    <input type="number" name="position_size" id="positionSize" step="0.001" required>
                </div>
                <div class="field">
                    <label>Entry Price (USDT)</label>
                    <div class="ui right labeled input">
                        <input type="number" name="entry_price" id="entryPrice" step="0.01" required>
                        <div class="ui basic label">
                            Current: <span class="price-value">-</span>
                            <button type="button" class="ui mini button" id="useCurrentPrice">Use</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Take Profit & Stop Loss -->
            <div class="two fields">
                <div class="field">
                    <label>Take Profit (USDT)</label>
                    <input type="number" name="take_profit" id="takeProfit" step="0.01">
                </div>
                <div class="field">
                    <label>Stop Loss (USDT)</label>
                    <input type="number" name="stop_loss" id="stopLoss" step="0.01">
                </div>
            </div>

            <!-- Submit Button -->
            <button class="ui primary button" type="submit">
                <i class="money bill alternate icon"></i>
                Place Trade
            </button>
        </form>
    </div>

    <!-- Market Analysis wird später hinzugefügt -->
</div>

<style>
    .fixed-width-label {
        width: 150px !important;
    }
</style>

<!-- Basic JavaScript -->
<script>
    $(document).ready(function () {
        // Initialize dropdowns
        $('.ui.dropdown').dropdown();

        // WebSocket für aktuelle Preise
        const ws = new WebSocket('wss://stream.binance.com:9443/ws/ethusdt@trade');
        let currentPrice = null;

        ws.onmessage = function (event) {
            const data = JSON.parse(event.data);
            currentPrice = parseFloat(data.p).toFixed(2);
            $('.price-value').text(currentPrice);
        };

        // Use Current Price Button
        $('#useCurrentPrice').click(function (e) {
            e.preventDefault();
            if (currentPrice) {
                $('#entryPrice').val(currentPrice);
                calculateTPSL();
            }
        });

        // Parameter Model Funktionen
        function loadModelParameters(modelId) {
            if (!modelId) return;

            $.ajax({
                url: 'ajax/get_model_parameters.php',
                type: 'POST',
                data: { model_id: modelId },
                success: function (response) {
                    console.log('Model Parameters:', response);
                    if (response.success) {
                        applyModelParameters(response.parameters);
                    }
                }
            });
        }

        function applyModelParameters(parameters) {
            console.log('Applying parameters:', parameters);

            parameters.forEach(param => {
                const value = parseFloat(param.parameter_value);

                switch (param.parameter_name) {
                    // Standard Werte
                    case 'leverage':
                        $('#tradeLeverage').val(value.toString()).trigger('change');
                        break;
                    case 'position_size':
                        $('#positionSize').val(value);
                        break;

                    // Take Profit
                    case 'tp_percentage_long':
                    case 'tp_percentage_short':
                        if ($('#tradeSide').val() === 'buy' && param.parameter_name === 'tp_percentage_long' ||
                            $('#tradeSide').val() === 'sell' && param.parameter_name === 'tp_percentage_short') {
                            const entryPrice = parseFloat($('#entryPrice').val());
                            if (entryPrice) {
                                const tp = $('#tradeSide').val() === 'buy'
                                    ? entryPrice * (1 + value / 100)
                                    : entryPrice * (1 - value / 100);
                                $('#takeProfit').val(tp.toFixed(2));
                            }
                        }
                        break;

                    // Stop Loss
                    case 'sl_percentage_long':
                    case 'sl_percentage_short':
                        if ($('#tradeSide').val() === 'buy' && param.parameter_name === 'sl_percentage_long' ||
                            $('#tradeSide').val() === 'sell' && param.parameter_name === 'sl_percentage_short') {
                            const entryPrice = parseFloat($('#entryPrice').val());
                            if (entryPrice) {
                                const sl = $('#tradeSide').val() === 'buy'
                                    ? entryPrice * (1 - value / 100)
                                    : entryPrice * (1 + value / 100);
                                $('#stopLoss').val(sl.toFixed(2));
                            }
                        }
                        break;
                }
            });
        }

        function calculateTPSL() {
            const modelId = $('#parameterModel').val();
            if (modelId) {
                loadModelParameters(modelId);
            }
        }

        // Event Listener für Parameter Model
        $('#parameterModel').change(function () {
            const modelId = $(this).val();
            if (modelId) {
                loadModelParameters(modelId);
            }
        });

        // Event Listener für Entry Price und Side
        $('#entryPrice, #tradeSide').change(function () {
            const modelId = $('#parameterModel').val();
            if (modelId) {
                loadModelParameters(modelId);
            }
        });

        // User Select Event
        $('#userId').change(function () {
            const selectedOption = $(this).find('option:selected');
            const modelId = selectedOption.data('model');

            if (modelId) {
                $('#parameterModel').val(modelId).trigger('change');
            }
        });

        // Model Select Event
        $('#parameterModel').change(function () {
            const modelId = $(this).val();
            if (modelId) {
                loadModelParameters(modelId);
            }
        });

        // Form Submission
        $('#tradeForm').on('submit', function (e) {
            e.preventDefault();
            console.log('Trade Form Submitted', {
                symbol: $('[name="symbol"]').val(),
                side: $('#tradeSide').val(),
                size: $('#positionSize').val(),
                entry_price: $('#entryPrice').val(),
                take_profit: $('#takeProfit').val(),
                stop_loss: $('#stopLoss').val(),
                leverage: $('#tradeLeverage').val(),
                parameter_model: $('#parameterModel').val()
            });
        });

        // Initial Load wenn Model vorausgewählt
        const initialModelId = $('#parameterModel').val();
        if (initialModelId) {
            loadModelParameters(initialModelId);
        }
    });
</script>