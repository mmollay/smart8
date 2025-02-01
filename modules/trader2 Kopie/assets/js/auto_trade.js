const AutoTrader = {
    state: {
        ws: null,
        currentPrice: 0,
        lastSignal: null,
        autoMode: false,
        userId: null,
        symbol: 'ETHUSDT',
        modelParameters: null
    },

    init() {
        this.setupWebSocket();
        this.setupEventListeners();
        $('.ui.checkbox').checkbox();
        $('.ui.dropdown').dropdown();
    },

    setupWebSocket() {
        this.state.ws = new WebSocket('wss://stream.binance.com:9443/ws/ethusdt@trade');

        this.state.ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.state.currentPrice = parseFloat(data.p);
            this.updatePriceDisplay();

            if (this.state.autoMode && this.state.lastSignal) {
                this.processAutoTrade();
            }
        };

        this.state.ws.onopen = () => {
            $('#wsStatus').text('Verbunden');
            $('.status-indicator').first().addClass('active').removeClass('inactive');
        };

        this.state.ws.onclose = () => {
            $('#wsStatus').text('Getrennt');
            $('.status-indicator').first().addClass('inactive').removeClass('active');
            setTimeout(() => this.setupWebSocket(), 5000);
        };
    },

    setupEventListeners() {
        // Auto Mode Toggle
        $('#autoMode').on('change', (e) => {
            this.state.autoMode = $(e.target).is(':checked');
        });

        // User Selection
        $('#userId').on('change', (e) => {
            const userId = $(e.target).val();
            if (userId) {
                this.state.userId = userId;
                this.loadUserModel(userId);
            }
        });

        // Test Buttons
        $('#testBuy').on('click', () => this.simulateSignal('buy'));
        $('#testSell').on('click', () => this.simulateSignal('sell'));
    },

    async loadUserModel(userId) {
        try {
            const response = await $.ajax({
                url: '../api/get_user_model.php',
                type: 'GET',
                data: { user_id: userId }
            });

            if (response.success && response.model_id) {
                await this.loadModelParameters(response.model_id);
            }
        } catch (error) {
            console.error('Error loading user model:', error);
            this.showError('User-Modell konnte nicht geladen werden');
        }
    },

    loadModelParameters(modelId) {
        if (!modelId) return;
        
        $.ajax({
            url: '../ajax/get_model_parameters.php',
            method: 'GET',
            data: { model_id: modelId },
            success: (response) => {
                if (response.success) {
                    this.state.modelParameters = response.data;
                    this.updateModelDisplay();
                    this.updateOrderPreview();
                } else {
                    this.showError('Failed to load model parameters');
                }
            },
            error: () => {
                this.showError('Error loading model parameters');
            }
        });
    },

    updateModelDisplay() {
        if (!this.state.modelParameters) return;
        
        const params = this.state.modelParameters;
        $('#tradeSizeInput').val(params.default_trade_size);
        $('#leverageInput').val(params.default_leverage);
        $('#tpLongInput').val(params.tp_percentage_long);
        $('#slLongInput').val(params.sl_percentage_long);
        $('#tpShortInput').val(params.tp_percentage_short);
        $('#slShortInput').val(params.sl_percentage_short);
        
        // Update the UI to show active model
        $('.model-indicator').text(params.name || 'No Model Selected');
    },

    updatePriceDisplay() {
        $('#currentPrice').text(this.state.currentPrice.toFixed(2));
        if (this.state.lastSignal) {
            this.updateOrderPreview();
        }
    },

    updateOrderPreview() {
        if (!this.state.modelParameters || !this.state.lastSignal) return;

        const side = this.state.lastSignal;
        const currentPrice = this.state.currentPrice;
        const params = this.state.modelParameters;

        // Entry price calculation (current +/- 1)
        const entryPrice = side === 'buy' ?
            currentPrice + 1 :
            currentPrice - 1;

        // TP/SL calculations
        const takeProfit = side === 'buy' ?
            entryPrice * (1 + params.take_profit_percent / 100) :
            entryPrice * (1 - params.take_profit_percent / 100);

        const stopLoss = side === 'buy' ?
            entryPrice * (1 - params.stop_loss_percent / 100) :
            entryPrice * (1 + params.stop_loss_percent / 100);

        // Position size calculation
        const accountBalance = 1000; // TODO: Get from API
        const positionSizeUSD = accountBalance * params.position_size_percent / 100 * params.leverage;
        const positionSizeETH = positionSizeUSD / entryPrice;

        // Update display
        $('#previewSide').text(side === 'buy' ? 'Long' : 'Short')
            .removeClass('red green')
            .addClass(side === 'buy' ? 'green' : 'red');
        $('#previewEntry').text(entryPrice.toFixed(2));
        $('#previewTakeProfit').text(takeProfit.toFixed(2));
        $('#previewStopLoss').text(stopLoss.toFixed(2));
        $('#previewPositionSize').text(
            positionSizeETH.toFixed(4) + ' ETH' +
            ' (' + positionSizeUSD.toFixed(2) + ' USDT)'
        );
        $('#previewRisk').text(
            (positionSizeUSD * params.stop_loss_percent / 100).toFixed(2) + ' USDT'
        );
    },

    simulateSignal(side) {
        this.state.lastSignal = side;
        $('#lastSignal').text(side.toUpperCase())
            .removeClass('red green')
            .addClass(side === 'buy' ? 'green' : 'red');
        this.updateOrderPreview();

        if (this.state.autoMode) {
            this.processAutoTrade();
        }
    },

    async processAutoTrade() {
        if (!this.state.userId || !this.state.modelParameters || !this.state.lastSignal) return;

        const side = this.state.lastSignal;
        const currentPrice = this.state.currentPrice;
        const params = this.state.modelParameters;

        // Prepare order
        const orderData = {
            user_id: this.state.userId,
            symbol: this.state.symbol,
            side: side,
            price: side === 'buy' ? currentPrice + 1 : currentPrice - 1,
            leverage: params.leverage,
            position_size: (1000 * params.position_size_percent / 100 * params.leverage / currentPrice).toFixed(4),
            take_profit: side === 'buy' ?
                currentPrice * (1 + params.take_profit_percent / 100) :
                currentPrice * (1 - params.take_profit_percent / 100),
            stop_loss: side === 'buy' ?
                currentPrice * (1 - params.stop_loss_percent / 100) :
                currentPrice * (1 + params.stop_loss_percent / 100)
        };

        try {
            const response = await $.ajax({
                url: '../api/place_order.php',
                type: 'POST',
                data: orderData
            });

            if (response.success) {
                this.addTradeToHistory({
                    ...orderData,
                    time: new Date(),
                    status: 'Placed'
                });
            } else {
                this.showError('Order konnte nicht platziert werden: ' + response.error);
            }
        } catch (error) {
            console.error('Error placing order:', error);
            this.showError('Order konnte nicht platziert werden');
        }
    },

    addTradeToHistory(trade) {
        const row = `
            <tr>
                <td>${trade.time.toLocaleTimeString()}</td>
                <td class="${trade.side === 'buy' ? 'positive' : 'negative'}">
                    ${trade.side === 'buy' ? 'Long' : 'Short'}
                </td>
                <td>${parseFloat(trade.price).toFixed(2)}</td>
                <td>${parseFloat(trade.take_profit).toFixed(2)}</td>
                <td>${parseFloat(trade.stop_loss).toFixed(2)}</td>
                <td>${parseFloat(trade.position_size).toFixed(4)}</td>
                <td>${trade.status}</td>
            </tr>
        `;
        $('#tradeHistory tbody').prepend(row);
    },

    showError(message) {
        // TODO: Implement error display
        console.error(message);
    }
};

$(document).ready(() => {
    AutoTrader.init();
});
