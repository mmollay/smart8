// websocket/websocket-client.js
class BitgetWSClient {
    constructor() {
        this.ws = null;
        this.config = null;
        this.callbacks = new Map();
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 5000;
    }

    async init() {
        try {
            const response = await fetch('api/get_ws_config.php');
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error);
            }

            this.config = data.config;
            this.connect();
        } catch (e) {
            console.error('Failed to initialize WebSocket:', e);
        }
    }

    connect() {
        if (!this.config) {
            console.error('WebSocket not configured');
            return;
        }

        this.ws = new WebSocket(this.config.endpoint);

        this.ws.onopen = () => {
            console.log('Connected to Bitget WebSocket');
            this.reconnectAttempts = 0;

            // Login
            this.ws.send(JSON.stringify(this.config.auth));

            // Subscribe to channels
            setTimeout(() => {
                this.ws.send(JSON.stringify(this.config.subscriptions));
            }, 1000);

            this.executeCallbacks('connectionStatus', true);
        };

        this.ws.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                this.handleMessage(data);
            } catch (e) {
                console.error('Error parsing message:', e);
            }
        };

        this.ws.onclose = () => {
            console.log('WebSocket connection closed');
            this.executeCallbacks('connectionStatus', false);
            this.handleReconnect();
        };

        this.ws.onerror = (error) => {
            console.error('WebSocket error:', error);
            this.executeCallbacks('connectionStatus', false);
        };
    }

    handleMessage(data) {
        console.log('Received message:', data);

        if (data.event === 'login') {
            console.log(data.code === 0 ? 'Login successful' : 'Login failed');
            return;
        }

        // Debug Event für alle Nachrichten
        this.executeCallbacks('debug', data);

        // Account Updates
        if (data.arg?.channel === 'account') {
            this.executeCallbacks('account', data);
            this.executeCallbacks('lastUpdate', new Date().toLocaleTimeString());
        }

        // Position Updates
        if (data.arg?.channel === 'positions') {
            this.executeCallbacks('positions', data);
            this.executeCallbacks('lastUpdate', new Date().toLocaleTimeString());
        }

        // Ticker Updates
        if (data.arg?.channel === 'ticker') {
            this.executeCallbacks('ticker', data);
            this.executeCallbacks('lastUpdate', new Date().toLocaleTimeString());
        }
    }

    handleReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`Reconnecting... Attempt ${this.reconnectAttempts}`);
            setTimeout(() => this.connect(), this.reconnectDelay);
        } else {
            console.error('Max reconnection attempts reached');
        }
    }

    on(event, callback) {
        if (!this.callbacks.has(event)) {
            this.callbacks.set(event, []);
        }
        this.callbacks.get(event).push(callback);
    }

    executeCallbacks(event, data) {
        if (this.callbacks.has(event)) {
            this.callbacks.get(event).forEach(callback => {
                try {
                    callback(data);
                } catch (e) {
                    console.error(`Error executing ${event} callback:`, e);
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
window.wsClient = new BitgetWSClient();