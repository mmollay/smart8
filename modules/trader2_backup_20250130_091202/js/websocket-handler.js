// websocket-handler.js
class WebSocketHandler {
    static instance = null;

    constructor() {
        if (WebSocketHandler.instance) {
            return WebSocketHandler.instance;
        }

        this.ws = null;
        this.callbacks = new Map();
        this.lastData = null;
        WebSocketHandler.instance = this;
    }

    connect() {
        if (this.ws) {
            this.ws.close();
        }

        this.ws = new WebSocket('ws://localhost:8080');

        this.ws.onopen = () => {
            console.log('WebSocket Verbindung hergestellt');
            this.executeCallbacks('connectionStatus', true);
        };

        this.ws.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                console.log('Rohdaten vom Server:', data);
                this.lastData = data;

                // Preis-Update
                if (data.currentPrice !== undefined) {
                    this.executeCallbacks('price', data.currentPrice);
                }

                // Balance-Update
                if (data.crossWalletBalance !== undefined ||
                    data.availableBalance !== undefined ||
                    data.crossUnPnl !== undefined) {
                    this.executeCallbacks('balance', {
                        crossWalletBalance: data.crossWalletBalance,
                        availableBalance: data.availableBalance,
                        crossUnPnl: data.crossUnPnl
                    });
                }

                // Zeit-Update
                this.executeCallbacks('lastUpdate', new Date().toLocaleTimeString());

            } catch (e) {
                console.error('Fehler beim Verarbeiten der WebSocket Daten:', e);
            }
        };

        this.ws.onclose = () => {
            console.log('WebSocket Verbindung geschlossen');
            this.executeCallbacks('connectionStatus', false);
            setTimeout(() => this.connect(), 5000);
        };

        this.ws.onerror = (error) => {
            console.error('WebSocket Fehler:', error);
            this.executeCallbacks('connectionStatus', false);
        };
    }

    on(event, callback) {
        if (!this.callbacks.has(event)) {
            this.callbacks.set(event, []);
        }
        this.callbacks.get(event).push(callback);

        // Sofort letzte Daten senden, falls vorhanden
        if (this.lastData && event === 'balance') {
            callback({
                crossWalletBalance: this.lastData.crossWalletBalance,
                availableBalance: this.lastData.availableBalance,
                crossUnPnl: this.lastData.crossUnPnl
            });
        }
    }

    executeCallbacks(event, data) {
        if (this.callbacks.has(event)) {
            this.callbacks.get(event).forEach(callback => {
                try {
                    callback(data);
                } catch (e) {
                    console.error(`Fehler beim Ausführen des ${event} Callbacks:`, e);
                }
            });
        }
    }

    disconnect() {
        if (this.ws) {
            this.ws.close();
            this.ws = null;
        }
    }
}

// Globale Instanz
window.wsHandler = new WebSocketHandler();