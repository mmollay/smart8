<?php
include __DIR__ . '/../t_config.php';

// Parameter Models laden
$models_query = "SELECT id, name FROM trading_parameter_models ORDER BY name";
$models_result = $db->query($models_query);

// User laden
$users_query = "SELECT u.id, u.username, u.default_parameter_model_id 
                FROM users u 
                WHERE u.active = 1 
                ORDER BY u.username";
$users_result = $db->query($users_query);

// Aktuelle Marktempfehlung laden
$signal_query = "SELECT action, confidence, created_at, entry_price, tp_price, sl_price 
                FROM analysis_signals 
                WHERE symbol = 'ETHUSDT_UMCBL' 
                ORDER BY created_at DESC 
                LIMIT 1";
$signal_result = $db->query($signal_query);
$signal = $signal_result->fetch_assoc();
?>

<div class="ui grid">


    <div id="content">
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
                            <input type="hidden" name="symbol" value="ETHUSDT">
                            <div class="ui label">
                                ETHUSDT
                                <div class="detail price-value">Loading...</div>
                            </div>
                        </div>
                        <div class="field">
                            <label>
                                Direction
                                <?php if ($signal): ?>
                                    <div class="ui label <?= $signal['action'] === 'buy' ? 'green' : 'red' ?>">
                                        Empfehlung: <?= ucfirst($signal['action']) ?>
                                        (<?= number_format($signal['confidence'], 1) ?>%)
                                        <button type="button" class="ui mini button" id="useRecommendation">
                                            <i class="check icon"></i>
                                            Übernehmen
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </label>
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
                            <label>Stop Loss</label>
                            <input type="number" step="0.01" id="stopLoss" name="stop_loss">
                        </div>
                    </div>

                    <!-- Leverage -->
                    <div class="two fields">
                        <div class="field">
                            <label>Leverage</label>
                            <select class="ui dropdown" id="tradeLeverage" name="leverage">
                                <option value="">Leverage auswählen</option>
                                <?php
                                $leverages = [1, 2, 3, 5, 10, 20, 50, 100];
                                foreach ($leverages as $lev) {
                                    echo "<option value=\"$lev\">{$lev}x</option>";
                                }
                                ?>
                            </select>
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
    </div>

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

        // Empfehlung übernehmen
        $('#useRecommendation').click(function () {
            const recommendedAction = '<?= $signal['action'] ?? '' ?>';
            if (recommendedAction) {
                $('#tradeSide').dropdown('set selected', recommendedAction);
            }
        });

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

            // Order-Details sammeln
            const orderDetails = {
                user_id: $('#userId').val(),
                user_name: $('#userId option:selected').text(),
                parameter_model_id: $('#parameterModel').val(),
                parameter_model_name: $('#parameterModel option:selected').text(),
                symbol: $('[name="symbol"]').val(),
                side: $('#tradeSide').val(),
                position_size: $('#positionSize').val(),
                entry_price: $('#entryPrice').val(),
                take_profit: $('#takeProfit').val(),
                stop_loss: $('#stopLoss').val(),
                leverage: $('#tradeLeverage').val()
            };

            // Validierung
            if (!orderDetails.leverage) {
                $('<div class="ui error message">')
                    .html(`
                        <i class="close icon"></i>
                        <div class="header">Fehler</div>
                        <p>Bitte wählen Sie einen Leverage-Wert aus.</p>
                    `)
                    .prependTo('#tradeForm')
                    .transition('fade');
                return;
            }

            // Bestätigungsdialog erstellen
            const confirmModal = $(`
                <div class="ui modal">
                    <div class="header">
                        <i class="money bill alternate icon"></i>
                        Order Bestätigung
                    </div>
                    <div class="content">
                        <h4 class="ui dividing header">Bitte überprüfen Sie die Order-Details:</h4>
                        
                        <div class="ui list">
                            <div class="item">
                                <div class="header">User</div>
                                ${orderDetails.user_name}
                            </div>
                            <div class="item">
                                <div class="header">Parameter Model</div>
                                ${orderDetails.parameter_model_name}
                            </div>
                            <div class="item">
                                <div class="header">Symbol</div>
                                ${orderDetails.symbol}
                            </div>
                            <div class="item">
                                <div class="header">Direction</div>
                                <div class="ui ${orderDetails.side === 'buy' ? 'green' : 'red'} label">
                                    ${orderDetails.side === 'buy' ? 'Long' : 'Short'}
                                </div>
                            </div>
                            <div class="item">
                                <div class="header">Position Size</div>
                                ${orderDetails.position_size} ETH
                            </div>
                            <div class="item">
                                <div class="header">Entry Price</div>
                                ${orderDetails.entry_price} USDT
                            </div>
                            <div class="item">
                                <div class="header">Take Profit</div>
                                ${orderDetails.take_profit} USDT
                            </div>
                            <div class="item">
                                <div class="header">Stop Loss</div>
                                ${orderDetails.stop_loss} USDT
                            </div>
                            <div class="item">
                                <div class="header">Leverage</div>
                                ${orderDetails.leverage}x
                            </div>
                        </div>
                    </div>
                    <div class="actions">
                        <div class="ui approve primary button">
                            <i class="check icon"></i>
                            Order platzieren
                        </div>
                        <div class="ui cancel button">
                            <i class="cancel icon"></i>
                            Abbrechen
                        </div>
                    </div>
                </div>
            `).modal({
                closable: false,
                onApprove: function () {
                    // Order speichern
                    saveOrder(orderDetails);
                    return true;
                }
            }).modal('show');
        });

        function saveOrder(orderDetails) {
            $.ajax({
                url: 'ajax/save_order.php',
                type: 'POST',
                data: orderDetails,
                success: function (response) {
                    if (response.success) {
                        // Erfolgsmeldung
                        $('<div class="ui success message">')
                            .html(`
                                <i class="close icon"></i>
                                <div class="header">Order erfolgreich platziert</div>
                                <p>${response.message}</p>
                            `)
                            .prependTo('#tradeForm')
                            .transition('fade');

                        // Formular zurücksetzen
                        $('#tradeForm')[0].reset();
                        $('.ui.dropdown').dropdown('clear');
                    } else {
                        // Fehlermeldung
                        $('<div class="ui error message">')
                            .html(`
                                <i class="close icon"></i>
                                <div class="header">Fehler</div>
                                <p>${response.message}</p>
                            `)
                            .prependTo('#tradeForm')
                            .transition('fade');
                    }
                },
                error: function () {
                    // Systemfehler
                    $('<div class="ui error message">')
                        .html(`
                            <i class="close icon"></i>
                            <div class="header">Systemfehler</div>
                            <p>Die Order konnte aufgrund eines Systemfehlers nicht gespeichert werden.</p>
                        `)
                        .prependTo('#tradeForm')
                        .transition('fade');
                }
            });
        }

        // Message Close Button
        $(document).on('click', '.message .close', function () {
            $(this).closest('.message').transition('fade');
        });

        // Initial Load wenn Model vorausgewählt
        const initialModelId = $('#parameterModel').val();
        if (initialModelId) {
            loadModelParameters(initialModelId);
        }
    });
</script>

<script>
    // Lade initialen Content
    document.addEventListener('DOMContentLoaded', function () {
        loadContent('content', 'lists/trades.php');
    });

    // Content laden
    function loadContent(targetId, url) {
        fetch('/smart8/modules/trader2/' + url)
            .then(response => response.text())
            .then(html => {
                document.getElementById(targetId).innerHTML = html;
            })
            .catch(error => console.error('Error:', error));
    }

    // Sync-Funktion für alle Daten
    function syncAll() {
        const button = document.querySelector('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="sync loading icon"></i> Syncing...';
        button.disabled = true;

        const symbols = ['ETHUSDT'];
        const promises = symbols.map(symbol =>
            fetch(`/smart8/modules/trader2/ajax/sync_trades.php?symbol=${symbol}&reset=1`)
                .then(response => response.json())
                .then(data => {
                    console.log(`Sync ${symbol}:`, data);
                    return data;
                })
        );

        Promise.all(promises)
            .then(() => {
                // Aktualisiere die aktuelle Ansicht
                const content = document.getElementById('content');
                if (content.innerHTML.includes('trades.php')) {
                    loadContent('content', 'lists/trades.php');
                } else if (content.innerHTML.includes('pnl.php')) {
                    loadContent('content', 'lists/pnl.php');
                } else if (content.innerHTML.includes('positions.php')) {
                    loadContent('content', 'lists/positions.php');
                }

                // Button zurücksetzen
                button.innerHTML = '<i class="check icon"></i> Done!';
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, 2000);
            })
            .catch(error => {
                console.error('Sync error:', error);
                button.innerHTML = '<i class="times icon"></i> Error!';
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, 2000);
            });
    }
</script>