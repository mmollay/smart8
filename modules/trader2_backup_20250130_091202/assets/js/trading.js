// Trading App Namespace
const TradingApp = {
    // State Management
    state: {
        lastSignalCheck: 0,
        currentModelId: null,
        currentUserId: null,
        parameters: {
            take_profit_percent: 2.0,
            stop_loss_percent: 1.0,
            leverage: 10,
            position_size_percent: 10.0
        },
        lastPrice: 0,
        ws: null,
        currentSignal: null,
        tradingParameters: null,
        parameterModel: null
    },

    // Initialization
    init() {
        this.ui.initialize();
        this.startSignalChecking();
        this.setupEventListeners();
        this.initializeWebSocket();
        
        // Initial parameter load
        const modelId = $('#parameterModel').val();
        if (modelId) {
            this.state.currentModelId = modelId;
            this.loadModelParameters(modelId);
        }

        // Initial user model load
        const userId = $('#userId').val();
        if (userId) {
            this.state.currentUserId = userId;
            this.loadUserModel(userId);
        }
    },

    startSignalChecking() {
        // Initial Check
        this.checkForSignals();

        // Regelmäßiger Check alle 10 Sekunden
        setInterval(() => {
            this.checkForSignals();
        }, 10000);
    },

    async checkForSignals() {
        try {
            // Prüfen ob 10 Sekunden seit dem letzten Check vergangen sind
            const now = Date.now();
            if (now - this.state.lastSignalCheck < 10000) {
                return;
            }

            this.state.lastSignalCheck = now;

            const response = await $.ajax({
                url: '../api/get_latest_signal.php',
                type: 'GET'
            });

            if (response.success && response.signal) {
                this.state.currentSignal = response.signal;
                this.ui.updateSignalDisplay(response.signal);
            } else {
                this.state.currentSignal = null;
                this.ui.updateSignalDisplay(null);
            }
        } catch (error) {
            console.error('Error checking signals:', error);
            this.ui.showMessage('error', 'Fehler', 'Signale konnten nicht abgerufen werden');
        }
    },

    // UI Initialization
    initializeUI() {
        $('.ui.dropdown').dropdown();
        this.ui.updateLoadingState(true);
    },

    // WebSocket Management
    initializeWebSocket() {
        const wsUrl = 'wss://stream.binance.com:9443/ws/ethusdt@trade';

        this.state.ws = new WebSocket(wsUrl);

        this.state.ws.onopen = () => {
            console.log('WebSocket verbunden');
            $('#connection-status').html('<i class="circle icon green"></i> Verbunden');
        };

        this.state.ws.onclose = () => {
            console.log('WebSocket getrennt');
            $('#connection-status').html('<i class="circle icon red"></i> Getrennt');
            // Reconnect nach 5 Sekunden
            setTimeout(() => this.initializeWebSocket(), 5000);
        };

        this.state.ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.handleWebSocketMessage(data);
        };

        this.state.ws.onerror = (error) => {
            console.error('WebSocket error:', error);
            this.ui.showMessage('error', 'Verbindungsfehler', 'Die Verbindung zum Preis-Stream konnte nicht hergestellt werden');
        };
    },

    handleWebSocketMessage(data) {
        // Binance Ticker Format
        const price = parseFloat(data.p); // Current price
        const symbol = 'ETHUSDT';

        // Update price display
        const priceData = {
            type: 'price_update',
            symbol: symbol,
            price: price
        };

        this.updatePrice(priceData);

        // Optional: Trigger signal check if price moves significantly
        if (Math.abs(price - this.state.lastPrice) > 10) {
            this.checkForSignals();
        }
    },

    updatePrice(data) {
        const priceElement = $(`#price_${data.symbol}`);
        const oldPrice = parseFloat(priceElement.text());
        const newPrice = parseFloat(data.price);

        priceElement
            .text(newPrice.toFixed(2))
            .removeClass('price-up price-down')
            .addClass(newPrice > oldPrice ? 'price-up' : 'price-down');
    },

    updateSignal(signal) {
        if (!signal) {
            $('#signalContainer').html(`
                <div class="ui info message">
                    <div class="header">Kein aktives Signal</div>
                    <p>Momentan liegt kein Trading-Signal vor.</p>
                </div>
            `);
            return;
        }

        const signalHtml = `
            <div class="ui grid">
                <div class="eight wide column">
                    <div class="ui list">
                        <div class="item">
                            <div class="header">Signal</div>
                            <span class="${signal.side === 'buy' ? 'price-up' : 'price-down'}">
                                ${signal.side === 'buy' ? 'Long' : 'Short'}
                            </span>
                        </div>
                        <div class="item">
                            <div class="header">Entry</div>
                            ${signal.entry_price.toFixed(2)} USDT
                        </div>
                        <div class="item">
                            <div class="header">Confidence</div>
                            <div class="ui progress" data-percent="${signal.confidence}">
                                <div class="bar" style="width: ${signal.confidence}%">
                                    <div class="progress">${signal.confidence}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="eight wide column">
                    <div class="ui list">
                        <div class="item">
                            <div class="header">Take Profit</div>
                            ${signal.take_profit.toFixed(2)} USDT
                        </div>
                        <div class="item">
                            <div class="header">Stop Loss</div>
                            ${signal.stop_loss.toFixed(2)} USDT
                        </div>
                        <div class="item">
                            <div class="header">RSI</div>
                            ${signal.rsi.toFixed(2)}
                        </div>
                    </div>
                </div>
            </div>`;

        $('#signalContainer').html(signalHtml);

        // Initialize progress bars
        $('.ui.progress').progress();
    },

    async loadModelParameters(modelId) {
        try {
            const response = await $.ajax({
                url: '../api/get_model_parameters.php',
                type: 'GET',
                data: { model_id: modelId }
            });

            if (response.success) {
                this.state.parameters = response.parameters;
                
                // Formularfelder aktualisieren
                if (response.parameters.leverage) {
                    $('#leverage').val(response.parameters.leverage);
                }
                if (response.parameters.position_size_percent) {
                    $('#positionSize').val(response.parameters.position_size_percent);
                }
                if (response.parameters.tp_percentage_long) {
                    $('#takeProfitPercent').val(response.parameters.tp_percentage_long);
                }
                if (response.parameters.sl_percentage_long) {
                    $('#stopLossPercent').val(response.parameters.sl_percentage_long);
                }
            } else {
                this.ui.showMessage('error', 'Fehler', response.error || 'Parameter konnten nicht geladen werden');
            }
        } catch (error) {
            console.error('Error loading model parameters:', error);
            this.ui.showMessage('error', 'Fehler', 'Parameter konnten nicht geladen werden');
        }
    },

    async loadUserModel(userId) {
        try {
            const response = await $.ajax({
                url: '../api/get_user_model.php',
                type: 'GET',
                data: { user_id: userId }
            });

            if (response.success && response.model_id) {
                this.state.currentModelId = response.model_id;
                $('#parameterModel').val(response.model_id).trigger('change');
            }
        } catch (error) {
            console.error('Error loading user model:', error);
            this.ui.showMessage('error', 'Fehler', 'User-Modell konnte nicht geladen werden');
        }
    },

    async submitOrder() {
        const formData = new FormData($('#tradeForm')[0]);
        const orderData = Object.fromEntries(formData.entries());
        
        try {
            const response = await $.ajax({
                url: '../api/submit_order.php',
                type: 'POST',
                data: orderData
            });

            if (response.success) {
                this.ui.showMessage('success', 'Erfolg', 'Order wurde erfolgreich platziert');
                $('#tradeForm')[0].reset();
            } else {
                this.ui.showMessage('error', 'Fehler', response.error || 'Order konnte nicht platziert werden');
            }
        } catch (error) {
            console.error('Error submitting order:', error);
            this.ui.showMessage('error', 'Fehler', 'Order konnte nicht platziert werden');
        }
    },

    setupEventListeners() {
        // User Change Handler
        $('#userId').on('change', async (e) => {
            const userId = $(e.target).val();
            if (userId) {
                this.state.currentUserId = userId;
                this.loadUserModel(userId);
            }
        });

        // Parameter Model Change Handler
        $('#parameterModel').on('change', (e) => {
            const modelId = $(e.target).val();
            if (modelId) {
                this.state.currentModelId = modelId;
                this.loadModelParameters(modelId);
            }
        });

        // Form submission handler
        $('#tradeForm').on('submit', (e) => {
            e.preventDefault();
            this.submitOrder();
        });

        // Price update handler
        $('#entryPrice').on('change', (e) => {
            const price = parseFloat($(e.target).val());
            if (!isNaN(price)) {
                this.updatePriceCalculations(price);
            }
        });

        // Trade side change handler
        $('#tradeSide').on('change', () => {
            const price = parseFloat($('#entryPrice').val());
            if (!isNaN(price)) {
                this.updatePriceCalculations(price);
            }
        });
    },

    updatePriceCalculations(price) {
        if (!price || isNaN(price)) return;
        
        const side = $('#tradeSide').val();
        const leverage = parseFloat($('#leverage').val()) || this.state.parameters.leverage;
        const positionSize = parseFloat($('#positionSize').val()) || this.state.parameters.position_size_percent;
        
        let tpPercent = side === 'buy' ? 
            (this.state.parameters.tp_percentage_long || this.state.parameters.take_profit_percent) : 
            (this.state.parameters.tp_percentage_short || this.state.parameters.take_profit_percent);
            
        let slPercent = side === 'buy' ? 
            (this.state.parameters.sl_percentage_long || this.state.parameters.stop_loss_percent) : 
            (this.state.parameters.sl_percentage_short || this.state.parameters.stop_loss_percent);

        if (side === 'buy') {
            $('#takeProfit').val((price * (1 + tpPercent/100)).toFixed(2));
            $('#stopLoss').val((price * (1 - slPercent/100)).toFixed(2));
        } else {
            $('#takeProfit').val((price * (1 - tpPercent/100)).toFixed(2));
            $('#stopLoss').val((price * (1 + slPercent/100)).toFixed(2));
        }

        // Position size calculations
        const accountBalance = 1000; // TODO: Get from API
        const positionSizeUSD = (accountBalance * positionSize/100 * leverage).toFixed(2);
        const positionSizeETH = (positionSizeUSD / price).toFixed(4);
        
        $('#positionSizeUSD').text(positionSizeUSD + ' USDT');
        $('#positionSizeETH').text(positionSizeETH + ' ETH');
    },

    // UI Management
    ui: {
        initialize() {
            this.setupMessageClosing();
            this.setupDropdowns();
        },

        setupDropdowns() {
            $('.ui.dropdown').dropdown();
        },

        setupMessageClosing() {
            $(document).on('click', '.message .close', function () {
                $(this).closest('.message').transition('fade');
            });
        },

        showMessage(type, title, message) {
            const messageHtml = `
                <div class="ui ${type} message">
                    <i class="close icon"></i>
                    <div class="header">${title}</div>
                    <p>${message}</p>
                </div>`;

            // Remove existing messages
            $('.ui.message').remove();

            // Add new message at the top of the container
            $('.ui.container').prepend(messageHtml);
        },

        updateSignalDisplay(signal) {
            if (!signal) {
                $('#signalContainer').html(`
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="ui tiny basic label">Kein aktives Signal</span>
                    </div>
                `);
                return;
            }

            const getRsiClass = (rsi) => {
                if (rsi >= 70) return 'negative';
                if (rsi <= 30) return 'positive';
                return '';
            };

            const getRsiText = (rsi) => {
                if (rsi >= 70) return '↓';
                if (rsi <= 30) return '↑';
                return '→';
            };

            const signalHtml = `
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="ui tiny label ${signal.action === 'buy' ? 'green' : 'red'}" style="min-width: 44px; text-align: center;">
                        ${signal.action === 'buy' ? 'Long' : 'Short'}
                    </span>
                    <span class="ui tiny basic label" title="Entry">
                        ${signal.entry_price.toFixed(2)}
                    </span>
                    <span class="ui tiny basic label" title="Take Profit">
                        TP: ${signal.take_profit.toFixed(2)}
                    </span>
                    <span class="ui tiny basic label" title="Stop Loss">
                        SL: ${signal.stop_loss.toFixed(2)}
                    </span>
                    <span class="ui tiny basic label ${getRsiClass(signal.rsi)}" title="RSI">
                        RSI: ${signal.rsi ? signal.rsi.toFixed(1) : 'N/A'} ${getRsiText(signal.rsi)}
                    </span>
                    <span class="ui tiny basic label" title="Konfidenz">
                        ${signal.confidence}%
                    </span>
                    <button type="button" id="applySignalBtn" class="ui tiny compact button">
                        <i class="copy icon"></i>
                        Signal übernehmen
                    </button>
                </div>`;

            $('#signalContainer').html(signalHtml);

            $('#applySignalBtn').on('click', () => {
                $('#tradeSide').val(signal.action).trigger('change');
                $('#entryPrice').val(signal.entry_price).trigger('change');
                $('#takeProfit').val(signal.take_profit);
                $('#stopLoss').val(signal.stop_loss);

                $('html, body').animate({
                    scrollTop: $('#tradeForm').offset().top - 20
                }, 500);

                $('#tradeForm').addClass('success');
                setTimeout(() => {
                    $('#tradeForm').removeClass('success');
                }, 1000);

                this.showMessage('info', 'Signal übernommen', 'Die Empfehlung wurde in das Formular übernommen.');
            });
        },

        updateParameterFields() {
            const model = TradingApp.state.parameterModel;
            if (!model) return;

            const side = $('#tradeSide').val();
            const entryPrice = parseFloat($('#entryPrice').val());
            if (!entryPrice) return;

            model.forEach(param => {
                const value = parseFloat(param.value);
                switch (param.parameter_name) {
                    case 'tp_percentage_long':
                    case 'tp_percentage_short':
                        if ((side === 'buy' && param.parameter_name === 'tp_percentage_long') ||
                            (side === 'sell' && param.parameter_name === 'tp_percentage_short')) {
                            const tp = side === 'buy'
                                ? entryPrice * (1 + value / 100)
                                : entryPrice * (1 - value / 100);
                            $('#takeProfit').val(tp.toFixed(2));
                        }
                        break;
                    case 'sl_percentage_long':
                    case 'sl_percentage_short':
                        if ((side === 'buy' && param.parameter_name === 'sl_percentage_long') ||
                            (side === 'sell' && param.parameter_name === 'sl_percentage_short')) {
                            const sl = side === 'buy'
                                ? entryPrice * (1 - value / 100)
                                : entryPrice * (1 + value / 100);
                            $('#stopLoss').val(sl.toFixed(2));
                        }
                        break;
                }
            });
        },

        showConfirmDialog(orderDetails) {
            return new Promise((resolve) => {
                const modal = $(`
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
                    onApprove: () => resolve(true),
                    onDeny: () => resolve(false)
                }).modal('show');
            });
        },

        resetForm() {
            $('#tradeForm')[0].reset();
            $('.ui.dropdown').dropdown('clear');
        }
    },

    // Initial Data Loading
    loadInitialData() {
        const modelId = $('#parameterModel').val();
        if (modelId) {
            this.loadModelParameters(modelId);
        }
    }
};

// Initialize on document ready
$(document).ready(() => TradingApp.init());
