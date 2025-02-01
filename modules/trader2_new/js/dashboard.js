class TradingDashboard {
    constructor(symbol = 'ETHUSDT_UMCBL') {
        this.symbol = symbol;
        this.webSocket = null;
        this.chart = null;
        this.candleData = [];
        this.currentInterval = '15m';
        this.debug = true;
        this.marketData = {
            price: 0,
            volume24h: 0,
            change24h: 0,
            high24h: 0,
            low24h: 0
        };
    }

    async initialize() {
        try {
            // Lade initiale Daten
            await this.loadInitialData();
            
            // Initialisiere Chart
            await this.initializeChart();
            
            // Initialisiere WebSocket
            await this.initializeWebSocket();
            
            // Initialisiere Event-Listener
            this.initializeEventListeners();
            
            // Lade Marktdaten
            await this.loadMarketData();
            
        } catch (error) {
            console.error('Fehler bei der Initialisierung:', error);
            this.showError('Fehler bei der Initialisierung des Dashboards');
        }
    }

    async initializeChart() {
        // Chart-Optionen
        const options = {
            series: [{
                name: 'Kerzen',
                data: this.candleData
            }],
            chart: {
                type: 'candlestick',
                height: 500,
                width: '100%',
                animations: {
                    enabled: false
                },
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        selection: true,
                        zoom: true,
                        zoomin: true,
                        zoomout: true,
                        pan: true,
                        reset: true
                    }
                }
            },
            title: {
                text: this.symbol,
                align: 'left'
            },
            xaxis: {
                type: 'datetime',
                labels: {
                    datetimeUTC: false,
                    formatter: function(value) {
                        return new Date(value).toLocaleString();
                    }
                }
            },
            yaxis: {
                tooltip: {
                    enabled: true
                },
                labels: {
                    formatter: function(value) {
                        return value.toFixed(2);
                    }
                }
            },
            plotOptions: {
                candlestick: {
                    colors: {
                        upward: '#26a69a',
                        downward: '#ef5350'
                    },
                    wick: {
                        useFillColor: true,
                    }
                }
            },
            tooltip: {
                enabled: true,
                theme: 'dark',
                x: {
                    format: 'dd MMM HH:mm'
                }
            }
        };

        // Erstelle Chart
        const chartElement = document.getElementById('priceChart');
        if (!chartElement) {
            throw new Error('Chart-Element nicht gefunden');
        }

        this.chart = new ApexCharts(chartElement, options);
        await this.chart.render();
    }

    async initializeWebSocket() {
        this.webSocket = new BitgetWebSocketClient(this.symbol);
        
        // WebSocket Event-Handler
        this.webSocket.on('price', (data) => {
            this.marketData.price = data.price;
            this.updateMarketDataDisplay();
        });
        
        this.webSocket.on('candle', (data) => {
            this.updateChart(data);
        });
        
        this.webSocket.on('ticker', (data) => {
            if (data) {
                this.marketData = {
                    ...this.marketData,
                    volume24h: parseFloat(data.volume24h || 0),
                    change24h: parseFloat(data.change24h || 0),
                    high24h: parseFloat(data.high24h || 0),
                    low24h: parseFloat(data.low24h || 0)
                };
                this.updateMarketDataDisplay();
            }
        });
        
        this.webSocket.on('error', (error) => {
            console.error('WebSocket Fehler:', error);
            this.showError('WebSocket Verbindungsfehler');
        });
        
        // Verbinde WebSocket
        await this.webSocket.connect();
        
        // Aktualisiere Marktdaten regelmäßig
        setInterval(() => this.loadMarketData(), 10000);
    }

    initializeEventListeners() {
        // Intervall-Buttons
        const intervalButtons = document.querySelectorAll('[data-interval]');
        intervalButtons.forEach(button => {
            button.addEventListener('click', () => {
                const interval = button.getAttribute('data-interval');
                this.changeInterval(interval);
                
                // Entferne 'active' Klasse von allen Buttons
                intervalButtons.forEach(btn => btn.classList.remove('active'));
                // Füge 'active' Klasse zum geklickten Button hinzu
                button.classList.add('active');
            });
        });
    }

    async loadInitialData() {
        try {
            // Lade historische Daten
            const response = await fetch(`../api/get_historical_data.php?symbol=${this.symbol}&interval=${this.currentInterval}`);
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Fehler beim Laden der historischen Daten');
            }
            
            // Aktualisiere Chart mit historischen Daten
            this.candleData = data.candles;
            
            if (this.debug) {
                console.log('Initiale Daten geladen:', this.candleData);
            }
            
        } catch (error) {
            console.error('Fehler beim Laden der initialen Daten:', error);
            this.showError('Fehler beim Laden der historischen Daten');
        }
    }

    async loadMarketData() {
        try {
            const response = await fetch(`https://api.bitget.com/api/mix/v1/market/ticker?symbol=${this.symbol}`);
            const data = await response.json();
            
            if (data && data.data) {
                const ticker = data.data;
                this.marketData = {
                    price: parseFloat(ticker.last),
                    volume24h: parseFloat(ticker.usdtVolume),
                    change24h: parseFloat(ticker.priceChangePercent),
                    high24h: parseFloat(ticker.high24h),
                    low24h: parseFloat(ticker.low24h)
                };
                
                this.updateMarketDataDisplay();
            }
        } catch (error) {
            console.error('Fehler beim Laden der Marktdaten:', error);
        }
    }

    updateMarketDataDisplay() {
        // Aktueller Preis
        const priceElement = document.getElementById('currentPrice');
        if (priceElement) {
            priceElement.textContent = this.marketData.price.toFixed(2);
        }

        // 24h Volumen
        const volumeElement = document.getElementById('volume24h');
        if (volumeElement) {
            volumeElement.textContent = this.formatVolume(this.marketData.volume24h);
        }

        // 24h Änderung
        const changeElement = document.getElementById('change24h');
        if (changeElement) {
            const change = this.marketData.change24h;
            const changeText = `${change >= 0 ? '+' : ''}${change.toFixed(2)}%`;
            changeElement.textContent = changeText;
            changeElement.className = change >= 0 ? 'text-success' : 'text-danger';
        }

        // Zusätzliche Marktdaten
        const high24hElement = document.getElementById('high24h');
        if (high24hElement) {
            high24hElement.textContent = this.marketData.high24h.toFixed(2);
        }

        const low24hElement = document.getElementById('low24h');
        if (low24hElement) {
            low24hElement.textContent = this.marketData.low24h.toFixed(2);
        }
    }

    formatVolume(volume) {
        if (volume >= 1000000) {
            return `${(volume / 1000000).toFixed(2)}M`;
        } else if (volume >= 1000) {
            return `${(volume / 1000).toFixed(2)}K`;
        }
        return volume.toFixed(2);
    }

    updatePrice(data) {
        const priceElement = document.getElementById('currentPrice');
        if (priceElement) {
            priceElement.textContent = data.price.toFixed(2);
        }
    }

    updateChart(data) {
        // Füge neue Kerze hinzu oder aktualisiere letzte Kerze
        const candle = {
            x: new Date(data.timestamp).getTime(),
            y: [data.open, data.high, data.low, data.close]
        };

        if (this.candleData.length > 0 && 
            this.candleData[this.candleData.length - 1].x === candle.x) {
            // Aktualisiere letzte Kerze
            this.candleData[this.candleData.length - 1] = candle;
        } else {
            // Füge neue Kerze hinzu
            this.candleData.push(candle);
            // Begrenze die Anzahl der Kerzen
            if (this.candleData.length > 500) {
                this.candleData.shift();
            }
        }

        // Berechne Indikatoren
        this.calculateIndicators();

        this.updateChartData();
    }

    calculateIndicators() {
        if (this.candleData.length < 14) return;

        const prices = this.candleData.map(candle => ({
            high: candle.y[1],
            low: candle.y[2],
            close: candle.y[3]
        }));

        // ADX Berechnung (14 Perioden)
        const adx = this.calculateADX(prices, 14);
        const lastADX = adx[adx.length - 1];
        
        // ATR Berechnung (14 Perioden)
        const atr = this.calculateATR(prices, 14);
        const lastATR = atr[atr.length - 1];
        const currentPrice = prices[prices.length - 1].close;
        const atrPercent = (lastATR / currentPrice) * 100;

        // ROC Berechnung (14 Perioden)
        const closes = prices.map(p => p.close);
        const roc = this.calculateROC(closes, 14);
        const lastROC = roc[roc.length - 1];

        // DI Werte aus ADX Berechnung
        const lastPlusDI = lastADX.plusDI;
        const lastMinusDI = lastADX.minusDI;

        // Aktualisiere Anzeige
        document.getElementById('adxValue').textContent = lastADX.adx.toFixed(2);
        document.getElementById('atrValue').textContent = atrPercent.toFixed(2);
        document.getElementById('rocValue').textContent = lastROC.toFixed(2);
        document.getElementById('plusDI').textContent = lastPlusDI.toFixed(2);
        document.getElementById('minusDI').textContent = lastMinusDI.toFixed(2);

        // Setze Farbklassen basierend auf den Werten
        const adxElement = document.getElementById('adxValue');
        adxElement.className = lastADX.adx >= 25 && lastADX.adx <= 50 ? 'text-success' : 'text-muted';

        const atrElement = document.getElementById('atrValue');
        atrElement.className = atrPercent <= 2 ? 'text-success' : 'text-danger';

        const rocElement = document.getElementById('rocValue');
        if (lastROC >= 0.5 && lastROC <= 10) {
            rocElement.className = 'text-success';
        } else if (lastROC >= -10 && lastROC <= -0.5) {
            rocElement.className = 'text-danger';
        } else {
            rocElement.className = 'text-muted';
        }

        const plusDIElement = document.getElementById('plusDI');
        const minusDIElement = document.getElementById('minusDI');
        if (lastPlusDI > lastMinusDI + 5) {
            plusDIElement.className = 'text-success';
            minusDIElement.className = 'text-muted';
        } else if (lastMinusDI > lastPlusDI + 5) {
            plusDIElement.className = 'text-muted';
            minusDIElement.className = 'text-danger';
        } else {
            plusDIElement.className = 'text-muted';
            minusDIElement.className = 'text-muted';
        }
    }

    calculateATR(prices, period) {
        const tr = prices.map((price, i) => {
            if (i === 0) return price.high - price.low;
            
            const previousClose = prices[i - 1].close;
            return Math.max(
                price.high - price.low,
                Math.abs(price.high - previousClose),
                Math.abs(price.low - previousClose)
            );
        });

        return this.calculateSMA(tr, period);
    }

    calculateROC(prices, period) {
        const roc = [];
        for (let i = 0; i < prices.length; i++) {
            if (i < period) {
                roc.push(0);
                continue;
            }
            const previousPrice = prices[i - period];
            const currentPrice = prices[i];
            const changePercent = ((currentPrice - previousPrice) / previousPrice) * 100;
            roc.push(changePercent);
        }
        return roc;
    }

    calculateADX(prices, period) {
        const smoothingPeriod = 14;
        const trueRanges = [];
        const plusDM = [];
        const minusDM = [];
        
        // Berechne True Range und Directional Movement
        for (let i = 1; i < prices.length; i++) {
            const high = prices[i].high;
            const low = prices[i].low;
            const prevHigh = prices[i - 1].high;
            const prevLow = prices[i - 1].low;
            
            const tr = Math.max(
                high - low,
                Math.abs(high - prices[i - 1].close),
                Math.abs(low - prices[i - 1].close)
            );
            
            const upMove = high - prevHigh;
            const downMove = prevLow - low;
            
            trueRanges.push(tr);
            
            if (upMove > downMove && upMove > 0) {
                plusDM.push(upMove);
                minusDM.push(0);
            } else if (downMove > upMove && downMove > 0) {
                plusDM.push(0);
                minusDM.push(downMove);
            } else {
                plusDM.push(0);
                minusDM.push(0);
            }
        }
        
        // Berechne geglättete Werte
        const smoothTR = this.calculateEMA(trueRanges, smoothingPeriod);
        const smoothPlusDM = this.calculateEMA(plusDM, smoothingPeriod);
        const smoothMinusDM = this.calculateEMA(minusDM, smoothingPeriod);
        
        // Berechne +DI und -DI
        const plusDI = smoothPlusDM.map((pdm, i) => (pdm / smoothTR[i]) * 100);
        const minusDI = smoothMinusDM.map((mdm, i) => (mdm / smoothTR[i]) * 100);
        
        // Berechne DX und ADX
        const dx = plusDI.map((pdi, i) => {
            const mdi = minusDI[i];
            return Math.abs(pdi - mdi) / (pdi + mdi) * 100;
        });
        
        const adx = this.calculateEMA(dx, period);
        
        return adx.map((value, i) => ({
            adx: value,
            plusDI: plusDI[i],
            minusDI: minusDI[i]
        }));
    }

    calculateSMA(data, period) {
        const sma = [];
        for (let i = 0; i < data.length; i++) {
            if (i < period - 1) {
                sma.push(0);
                continue;
            }
            
            let sum = 0;
            for (let j = 0; j < period; j++) {
                sum += data[i - j];
            }
            sma.push(sum / period);
        }
        return sma;
    }

    calculateEMA(data, period) {
        const k = 2 / (period + 1);
        const ema = [];
        
        // Erste EMA ist SMA
        let sum = 0;
        for (let i = 0; i < period; i++) {
            sum += data[i];
        }
        ema.push(sum / period);
        
        // Berechne restliche EMAs
        for (let i = period; i < data.length; i++) {
            ema.push(data[i] * k + ema[ema.length - 1] * (1 - k));
        }
        
        return ema;
    }

    updateChartData() {
        if (this.chart) {
            this.chart.updateSeries([{
                data: this.candleData
            }]);
        }
    }

    async changeInterval(interval) {
        this.currentInterval = interval;
        await this.loadInitialData();
        this.updateChartData();
    }

    showError(message) {
        // Zeige Fehlermeldung in einem Bootstrap Alert
        const alertContainer = document.createElement('div');
        alertContainer.className = 'alert alert-danger alert-dismissible fade show';
        alertContainer.setAttribute('role', 'alert');
        alertContainer.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Füge Alert zum Container hinzu
        const container = document.querySelector('.container-fluid');
        if (container) {
            container.insertBefore(alertContainer, container.firstChild);
        }
        
        // Automatisches Ausblenden nach 5 Sekunden
        setTimeout(() => {
            alertContainer.remove();
        }, 5000);
    }
}

// Initialisiere Dashboard wenn DOM geladen ist
document.addEventListener('DOMContentLoaded', () => {
    const dashboard = new TradingDashboard();
    dashboard.initialize();
});
