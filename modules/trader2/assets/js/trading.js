// Globale Variablen
let lastPrice = 0;
let ws;
let currentSignal = null;
let tradingParameters = null;

// WebSocket-Verbindung
function initWebSocket() {
    ws = new WebSocket('ws://localhost:8080');

    ws.onopen = () => updateConnectionStatus(true);
    ws.onclose = () => updateConnectionStatus(false);
    ws.onmessage = handleWebSocketMessage;

    // Cleanup beim Verlassen der Seite
    window.addEventListener('beforeunload', () => ws.close());
}

// Signal Handling
async function fetchLatestSignal() {
    try {
        console.log('Fetching latest signal...');
        const response = await fetch('api/get_latest_signal.php');
        console.log('Response status:', response.status);
        const data = await response.json();
        console.log('Raw Signal Data:', data);
        console.log('Signal Data Structure:', {
            success: data.success,
            hasSignal: !!data.signal,
            signalKeys: data.signal ? Object.keys(data.signal) : [],
            signalValues: data.signal ? Object.values(data.signal) : []
        });

        if (data.success && data.signal) {
            console.log('Received valid signal:', data.signal);
            currentSignal = data.signal;
            updateSignalDisplay(data.signal);
        } else {
            console.log('No valid signal in response:', data);
            $('#signalIndicator').html(`
                <div class="ui message">
                    <div class="content">
                        <div class="header">Kein aktives Signal</div>
                        <p>Warte auf das nächste Trading Signal...</p>
                    </div>
                </div>
            `);
        }
    } catch (error) {
        console.error('Fehler beim Abrufen des Signals:', error);
        showMessage('negative', 'Fehler beim Abrufen des Trading Signals');
    }
}

function formatNumber(num) {
    return Number(num).toFixed(2).replace('.', ',');
}

function updateSignalDisplay(signal) {
    console.log('Updating signal display with data:', signal);
    console.log('Available signal properties:', Object.keys(signal));

    console.log('Signal side:', signal.side);
    console.log('Signal entry_price:', formatNumber(signal.entry_price));
    console.log('Signal take_profit:', signal.take_profit ? formatNumber(signal.take_profit) : undefined);
    console.log('Signal stop_loss:', signal.stop_loss ? formatNumber(signal.stop_loss) : undefined);

    const timeDiff = Date.now() - signal.timestamp;

    $('#tradeSide').val(signal.side).trigger('change');
    console.log('Trade side after set:', $('#tradeSide').val());

    $('#entryPrice').val(formatNumber(signal.entry_price));
    console.log('Entry price after set:', $('#entryPrice').val());

    if (signal.take_profit) {
        $('#takeProfit').val(formatNumber(signal.take_profit));
        console.log('Take profit after set:', $('#takeProfit').val());
    }
    if (signal.stop_loss) {
        $('#stopLoss').val(formatNumber(signal.stop_loss));
        console.log('Stop loss after set:', $('#stopLoss').val());
    }

    $('#signalIndicator').html(`
        <div class="ui ${getConfidenceColor(signal.confidence)} message">
            <div class="content">
                <div class="header">
                    ${formatSignalType(signal.side)} Signal
                    <span class="ui ${getConfidenceColor(signal.confidence)} label">
                        ${Math.round(signal.confidence * 100)}% Confidence
                    </span>
                </div>
                <p>
                    Price: ${formatNumber(signal.entry_price)} USDT<br>
                    Time: ${formatTimeDiff(timeDiff)} ago
                </p>
            </div>
        </div>
    `);
}

function formatSignalType(side) {
    return side === 'buy' ?
        '<span class="ui green label">LONG</span>' :
        '<span class="ui red label">SHORT</span>';
}

function getConfidenceColor(confidence) {
    if (confidence >= 80) return 'green';
    if (confidence >= 60) return 'yellow';
    return 'orange';
}

function formatTimeDiff(diff) {
    const minutes = Math.floor(diff / 60000);
    if (minutes < 60) return `${minutes}m`;
    const hours = Math.floor(minutes / 60);
    return `${hours}h ${minutes % 60}m`;
}

function applySignal() {
    if (!currentSignal) return;

    console.log('Applying signal:', currentSignal); // Debug logging

    $('#tradeSide').val(currentSignal.side).trigger('change');
    $('#entryPrice').val(formatNumber(currentSignal.entry_price));
    $('#takeProfit').val(currentSignal.take_profit ? formatNumber(currentSignal.take_profit) : '');
    $('#stopLoss').val(currentSignal.stop_loss ? formatNumber(currentSignal.stop_loss) : '');
}

// Load trading parameters
async function loadTradingParameters() {
    try {
        const response = await fetch('api/get_trading_parameters.php');
        tradingParameters = await response.json();
        console.log('Loaded trading parameters:', tradingParameters);
        
        // Set default values
        $('input[name="size"]').val(formatNumber(tradingParameters.default_trade_size));
        $('select[name="leverage"]').val(tradingParameters.default_leverage);
    } catch (error) {
        console.error('Error loading trading parameters:', error);
    }
}

// Calculate TP/SL based on parameters
function updateTPSL() {
    if (!tradingParameters) return;

    const price = parseFloat($('#entryPrice').val().replace(',', '.'));
    const side = $('#tradeSide').val();
    
    if (!price || !side) return;

    const tpPercentage = side === 'buy' ? 
        tradingParameters.tp_percentage_long : 
        tradingParameters.tp_percentage_short;
    
    const slPercentage = side === 'buy' ? 
        tradingParameters.sl_percentage_long : 
        tradingParameters.sl_percentage_short;

    const tp = side === 'buy' ? 
        price * (1 + tpPercentage / 100) : 
        price * (1 - tpPercentage / 100);

    const sl = side === 'buy' ? 
        price * (1 - slPercentage / 100) : 
        price * (1 + slPercentage / 100);

    if (!$('#takeProfit').is(':focus')) {
        $('#takeProfit').val(formatNumber(tp));
    }
    if (!$('#stopLoss').is(':focus')) {
        $('#stopLoss').val(formatNumber(sl));
    }
}

// UI Updates
function updateConnectionStatus(isConnected) {
    $('#connection-status').html(isConnected ?
        '<i class="circle icon green"></i> Connected' :
        '<i class="circle icon red"></i> Disconnected'
    );
}

function updatePriceDisplay(price) {
    const priceElement = $('.price-value');
    const formattedPrice = formatNumber(price);

    priceElement.text(`$${formattedPrice}`);
    if (lastPrice > 0) {
        priceElement.removeClass('green red')
            .addClass(price > lastPrice ? 'green' : 'red');
        setTimeout(() => priceElement.removeClass('green red'), 1000);
    }
    lastPrice = price;

    const entryPrice = $('#entryPrice');
    if (!entryPrice.is(':focus')) {
        entryPrice.val(formattedPrice);
        updateTPSL();
    }
}

function updateWalletInfo(data) {
    if (data.crossWalletBalance) {
        $('#balance').text(formatNumber(data.crossWalletBalance));
    }
    if (data.availableBalance) {
        $('#available').text(formatNumber(data.availableBalance));
    }
    if (data.crossUnPnl) {
        const pnl = parseFloat(data.crossUnPnl);
        const pnlElement = $('#pnl');
        pnlElement.text(formatNumber(pnl))
            .css('color', pnl >= 0 ? '#21ba45' : '#db2828');
    }

    $('#time').text(new Date().toLocaleTimeString());
}

// WebSocket Message Handler
function handleWebSocketMessage(event) {
    try {
        const data = JSON.parse(event.data);
        updateWalletInfo(data);
        if (data.price) {
            updatePriceDisplay(data.price);
        }
    } catch (error) {
        console.error('Fehler beim Verarbeiten der WebSocket-Daten:', error);
    }
}

// Trading Funktionen
async function submitTrade(formData) {
    try {
        // Show loading state
        $('#tradeForm').addClass('loading');
        
        // Convert FormData to a plain object
        const formObject = {};
        for (let [key, value] of formData.entries()) {
            // Convert string numbers back to actual numbers
            if (key === 'price' || key === 'size' || key === 'leverage' || key === 'takeProfit' || key === 'stopLoss') {
                value = value.replace(',', '.'); // Convert German number format back to standard
                value = parseFloat(value);
            }
            formObject[key] = value;
        }

        console.log('Sending trade data:', formObject);

        const response = await fetch('api/place_future_trade.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formObject)
        });

        const result = await response.json();
        console.log('Trade response:', result);

        if (result.success) {
            // Format the trade details
            const trade = result.trade;
            const tradeDetails = `
                <div class="ui success message">
                    <div class="header">Trade erfolgreich ausgeführt</div>
                    <ul class="list">
                        <li>Symbol: ${trade.symbol}</li>
                        <li>Seite: ${trade.side === 'buy' ? 'Long' : 'Short'}</li>
                        <li>Größe: ${formatNumber(trade.size)} ETH</li>
                        <li>Preis: ${formatNumber(trade.price)} USDT</li>
                        <li>Hebel: ${trade.leverage}x</li>
                        ${trade.takeProfit ? `<li>Take Profit: ${formatNumber(trade.takeProfit)} USDT</li>` : ''}
                        ${trade.stopLoss ? `<li>Stop Loss: ${formatNumber(trade.stopLoss)} USDT</li>` : ''}
                    </ul>
                </div>
            `;
            
            // Show success message with details
            $('#resultMessage').html(tradeDetails).show();
            
            // Reset form after successful trade
            $('#tradeForm').trigger('reset');
            
            // Refresh wallet info if function exists
            if (typeof updateWalletInfo === 'function') {
                setTimeout(updateWalletInfo, 1000); // Update after 1 second
            }
        } else {
            showMessage('negative', `Fehler: ${result.error || 'Unbekannter Fehler'}`);
        }
    } catch (error) {
        console.error('Trade submission error:', error);
        showMessage('negative', 'Fehler beim Senden des Trades');
    } finally {
        $('#tradeForm').removeClass('loading');
    }
}

// UI Helpers
function showMessage(type, message) {
    const messageEl = $('#resultMessage');
    messageEl.removeClass().addClass(`ui ${type} message`)
        .html(`
            <i class="close icon"></i>
            <div class="content">${message}</div>
        `)
        .show();

    setTimeout(() => messageEl.fadeOut(), 5000);
}

// Event Listeners
$(document).ready(function() {
    console.log('Document ready, initializing...');
    
    // Load trading parameters
    loadTradingParameters();
    
    // Initialize WebSocket
    initWebSocket();
    
    // Fetch initial signal
    fetchLatestSignal();

    // Periodically update signal
    setInterval(fetchLatestSignal, 60000); // Alle 60 Sekunden

    // Form event handlers
    $('#tradeSide, #entryPrice').on('change input', updateTPSL);
    
    // Trade form submission
    $('#tradeForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        submitTrade(formData);
    });

    // Apply Signal Button
    $('#applySignalButton').on('click', applySignal);

    // Message Close
    $(document).on('click', '#resultMessage .close', function() {
        $(this).closest('.message').fadeOut();
    });
});
