class BitgetWebSocket {
    constructor(symbol = 'ETHUSDT_UMCBL') {
        this.symbol = symbol;
        this.ws = null;
        this.pingInterval = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.lastPongTime = Date.now();
        this.subscriptions = new Set();
        
        this.connect();
    }

    connect() {
        this.ws = new WebSocket('wss://ws.bitget.com/mix/v1/stream');

        this.ws.onopen = () => {
            console.log('WebSocket Verbindung hergestellt');
            this.reconnectAttempts = 0;
            this.startPingInterval();
            this.resubscribe();
        };

        this.ws.onclose = () => {
            console.log('WebSocket Verbindung geschlossen');
            this.cleanup();
            this.handleReconnect();
        };

        this.ws.onerror = (error) => {
            console.error('WebSocket Fehler:', error);
            this.cleanup();
        };

        this.ws.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                
                // Pong Nachricht
                if (data.action === 'pong') {
                    this.lastPongTime = Date.now();
                    return;
                }

                // Verarbeite Preis-Updates
                if (data.data && data.data[0] && data.data[0].last) {
                    const price = parseFloat(data.data[0].last);
                    this.updatePrice(price);
                }

                // Verarbeite Kerzendaten
                if (data.data && data.data[0] && data.data[0].candle) {
                    this.updateCandle(data.data[0].candle);
                }

            } catch (error) {
                console.error('Fehler beim Verarbeiten der WebSocket Nachricht:', error);
            }
        };
    }

    cleanup() {
        if (this.pingInterval) {
            clearInterval(this.pingInterval);
            this.pingInterval = null;
        }
    }

    handleReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`Versuche Reconnect (${this.reconnectAttempts}/${this.maxReconnectAttempts})...`);
            setTimeout(() => this.connect(), 5000);
        } else {
            console.error('Maximale Anzahl an Reconnect-Versuchen erreicht');
        }
    }

    startPingInterval() {
        this.pingInterval = setInterval(() => {
            if (this.ws.readyState === WebSocket.OPEN) {
                // Sende Ping
                this.ws.send(JSON.stringify({
                    "action": "ping"
                }));

                // Prüfe ob letzter Pong zu lange her
                if (Date.now() - this.lastPongTime > 10000) {
                    console.warn('Keine Pong-Antwort erhalten, reconnecting...');
                    this.ws.close();
                }
            }
        }, 20000);
    }

    subscribe(channel) {
        if (this.ws.readyState === WebSocket.OPEN) {
            const subscribeMessage = {
                "action": "subscribe",
                "args": [
                    {
                        "instType": "mc",
                        "channel": channel,
                        "instId": this.symbol
                    }
                ]
            };
            this.ws.send(JSON.stringify(subscribeMessage));
            this.subscriptions.add(channel);
        }
    }

    resubscribe() {
        this.subscriptions.forEach(channel => {
            this.subscribe(channel);
        });
    }

    unsubscribe(channel) {
        if (this.ws.readyState === WebSocket.OPEN) {
            const unsubscribeMessage = {
                "action": "unsubscribe",
                "args": [
                    {
                        "instType": "mc",
                        "channel": channel,
                        "instId": this.symbol
                    }
                ]
            };
            this.ws.send(JSON.stringify(unsubscribeMessage));
            this.subscriptions.delete(channel);
        }
    }

    updatePrice(price) {
        // Aktualisiere Preis im UI
        const priceInput = document.querySelector('input[name="entry_price"]');
        if (priceInput) {
            priceInput.value = price.toFixed(2);
        }

        // Trigger Event für Preis-Update
        const event = new CustomEvent('priceUpdate', { detail: { price: price } });
        document.dispatchEvent(event);
    }

    updateCandle(candleData) {
        // Trigger Event für Kerzen-Update
        const event = new CustomEvent('candleUpdate', { detail: { candle: candleData } });
        document.dispatchEvent(event);
    }
}

// Initialisiere WebSocket und abonniere Kanäle
document.addEventListener('DOMContentLoaded', () => {
    const ws = new BitgetWebSocket();
    
    // Abonniere Ticker und Kerzendaten
    ws.subscribe('ticker');
    ws.subscribe('candle1m');
    
    // Symbol-Änderung Handler
    document.getElementById('trading_symbol').addEventListener('change', (event) => {
        const newSymbol = event.target.value;
        ws.unsubscribe('ticker');
        ws.unsubscribe('candle1m');
        ws.symbol = newSymbol;
        ws.subscribe('ticker');
        ws.subscribe('candle1m');
    });

    // Preis-Update Handler
    document.addEventListener('priceUpdate', async (event) => {
        const price = event.detail.price;
        
        // Aktualisiere Analyse wenn Preis sich signifikant ändert
        const currentPrice = parseFloat(document.querySelector('input[name="entry_price"]').value);
        const priceChange = Math.abs((price - currentPrice) / currentPrice * 100);
        
        if (priceChange >= 0.1) { // 0.1% Änderung
            await refreshAnalysis();
        }
    });

    // Kerzen-Update Handler
    document.addEventListener('candleUpdate', async () => {
        // Aktualisiere Analyse bei neuer Kerze
        await refreshAnalysis();
    });
});

// Fehlerbehandlung für die gesamte Seite
window.onerror = function(msg, url, line, col, error) {
    console.error('Globaler Fehler:', {
        message: msg,
        url: url,
        line: line,
        column: col,
        error: error
    });
    return false;
};

// Behandlung von unbehandelten Promise-Rejections
window.onunhandledrejection = function(event) {
    console.error('Unbehandelte Promise Rejection:', event.reason);
};
