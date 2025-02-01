class BitgetWebSocketClient {
    constructor(symbol = 'ETHUSDT_UMCBL') {
        this.symbol = symbol;
        this.ws = null;
        this.pingInterval = null;
        this.reconnectInterval = null;
        this.callbacks = {
            onPrice: [],
            onCandle: [],
            onError: [],
            onConnect: [],
            onDisconnect: []
        };
        this.isAuthenticated = false;
        this.config = null;
        this.debug = true; // Debug-Modus aktivieren
    }
    
    async connect() {
        try {
            // Hole WebSocket-Konfiguration vom Server
            const response = await fetch(`../api/get_ws_config.php?symbol=${this.symbol}`);
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Fehler beim Laden der WebSocket-Konfiguration');
            }
            
            if (this.debug) {
                console.log('WebSocket Konfiguration:', data);
            }
            
            this.config = data.config;
            
            // Erstelle WebSocket-Verbindung
            this.ws = new WebSocket(this.config.url);
            
            // Event-Handler
            this.ws.onopen = () => this._handleOpen();
            this.ws.onmessage = (event) => this._handleMessage(event);
            this.ws.onerror = (error) => this._handleError(error);
            this.ws.onclose = () => this._handleClose();
            
            // Setze Ping-Interval
            this.pingInterval = setInterval(() => this._ping(), this.config.pingInterval);
            
        } catch (error) {
            console.error('WebSocket Verbindungsfehler:', error);
            this._triggerCallbacks('onError', error);
            
            // Versuche Reconnect
            setTimeout(() => this.connect(), this.config?.reconnectInterval || 5000);
        }
    }
    
    disconnect() {
        if (this.ws) {
            clearInterval(this.pingInterval);
            clearInterval(this.reconnectInterval);
            this.ws.close();
            this.isAuthenticated = false;
        }
    }
    
    on(event, callback) {
        if (this.callbacks[event]) {
            this.callbacks[event].push(callback);
        }
    }
    
    _handleOpen() {
        console.log('WebSocket verbunden');
        
        // Authentifiziere zuerst
        if (this.config.auth) {
            if (this.debug) {
                console.log('Sende Auth:', JSON.stringify(this.config.auth, null, 2));
            }
            this.ws.send(JSON.stringify(this.config.auth));
        } else {
            this._subscribe();
        }
    }
    
    _handleMessage(event) {
        if (this.debug) {
            console.log('WebSocket Nachricht erhalten:', event.data);
        }
        
        try {
            // Behandle einfache String-Antworten
            if (event.data === 'pong') {
                if (this.debug) {
                    console.log('Pong erhalten');
                }
                return;
            }
            
            // Versuche JSON zu parsen
            const data = JSON.parse(event.data);
            
            if (this.debug) {
                console.log('Verarbeitete Nachricht:', data);
            }
            
            // Prüfe auf Login-Bestätigung
            if (data.event === 'login') {
                if (data.code === 0 || data.code === '0') {
                    console.log('Login erfolgreich');
                    this.isAuthenticated = true;
                    this._subscribe();
                } else {
                    console.error('Login fehlgeschlagen:', data);
                    this._triggerCallbacks('onError', new Error('Login fehlgeschlagen: ' + JSON.stringify(data)));
                }
                return;
            }
            
            // Verarbeite verschiedene Nachrichtentypen
            if (data.data && data.arg) {
                switch (data.arg.channel) {
                    case 'ticker':
                        const price = parseFloat(data.data[0].last);
                        this._triggerCallbacks('onPrice', {
                            symbol: this.symbol,
                            price: price,
                            timestamp: data.data[0].ts
                        });
                        break;
                        
                    case 'candle1m':
                        this._triggerCallbacks('onCandle', {
                            symbol: this.symbol,
                            open: parseFloat(data.data[0][1]),
                            high: parseFloat(data.data[0][2]),
                            low: parseFloat(data.data[0][3]),
                            close: parseFloat(data.data[0][4]),
                            volume: parseFloat(data.data[0][5]),
                            timestamp: data.data[0][0]
                        });
                        break;
                        
                    default:
                        if (this.debug) {
                            console.log('Unbekannter Channel:', data.arg.channel);
                        }
                }
            }
            
        } catch (error) {
            // Ignoriere JSON.parse Fehler für einfache String-Antworten
            if (!(error instanceof SyntaxError)) {
                console.error('Fehler beim Verarbeiten der WebSocket-Nachricht:', error);
                this._triggerCallbacks('onError', error);
            }
        }
    }
    
    _handleError(error) {
        console.error('WebSocket Fehler:', error);
        this._triggerCallbacks('onError', error);
    }
    
    _handleClose() {
        console.log('WebSocket geschlossen');
        this._triggerCallbacks('onDisconnect');
        
        // Versuche Reconnect
        setTimeout(() => this.connect(), this.config?.reconnectInterval || 5000);
    }
    
    _subscribe() {
        // Sende Subscription
        if (this.config.subscription) {
            if (this.debug) {
                console.log('Sende Subscription:', JSON.stringify(this.config.subscription, null, 2));
            }
            this.ws.send(JSON.stringify(this.config.subscription));
        }
        
        this._triggerCallbacks('onConnect');
    }
    
    _ping() {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            // BitGet WebSocket Ping-Format
            const pingMessage = {
                "op": "ping",
                "args": [{ "instType": "UMCBL" }]
            };
            
            if (this.debug) {
                console.log('Sende Ping:', pingMessage);
            }
            this.ws.send(JSON.stringify(pingMessage));
        }
    }
    
    _triggerCallbacks(event, data) {
        if (this.callbacks[event]) {
            this.callbacks[event].forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error(`Fehler im ${event} Callback:`, error);
                }
            });
        }
    }
}
