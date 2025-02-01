// analysis-dashboard.js
$(document).ready(function () {
    let charts = {};

    // Charts initialisieren
    function initCharts() {
        // Preis-Chart
        const priceChart = $('#priceChart');
        if (priceChart.length) {
            charts.price = new Chart(priceChart, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'ETH/USDT',
                        data: [],
                        borderColor: '#2185d0',
                        borderWidth: 2,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        // RSI-Chart
        const rsiChart = $('#rsiChart');
        if (rsiChart.length) {
            charts.rsi = new Chart(rsiChart, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'RSI',
                        data: [],
                        borderColor: '#21ba45',
                        borderWidth: 2,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            min: 0,
                            max: 100
                        }
                    }
                }
            });
        }

        // EMA-Chart
        const emaChart = $('#emaChart');
        if (emaChart.length) {
            charts.ema = new Chart(emaChart, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'EMA20',
                            data: [],
                            borderColor: '#2185d0',
                            borderWidth: 2,
                            pointRadius: 0
                        },
                        {
                            label: 'EMA50',
                            data: [],
                            borderColor: '#db2828',
                            borderWidth: 2,
                            pointRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    }

    // Daten laden und Charts aktualisieren
    function loadData() {
        $.ajax({
            url: 'api/get_analysis_data.php',
            method: 'GET',
            success: function (response) {
                if (response.success && response.marketData) {
                    updateCharts(response.marketData);
                    updateStats(response.stats);
                }
            },
            error: function (xhr, status, error) {
                console.error('Fehler beim Laden der Daten:', error);
                $('.ui.error.message').show()
                    .find('.content')
                    .text('Fehler beim Laden der Daten: ' + error);
            }
        });
    }

    // Charts aktualisieren
    function updateCharts(marketData) {
        // Daten für Charts vorbereiten
        const timestamps = marketData.map(d => new Date(parseInt(d.timestamp)).toLocaleTimeString());
        const prices = marketData.map(d => parseFloat(d.price));
        const rsiValues = marketData.map(d => parseFloat(d.rsi));
        const ema20Values = marketData.map(d => parseFloat(d.ema20));
        const ema50Values = marketData.map(d => parseFloat(d.ema50));

        // Preis-Chart aktualisieren
        if (charts.price) {
            charts.price.data.labels = timestamps;
            charts.price.data.datasets[0].data = prices;
            charts.price.update();
        }

        // RSI-Chart aktualisieren
        if (charts.rsi) {
            charts.rsi.data.labels = timestamps;
            charts.rsi.data.datasets[0].data = rsiValues;
            charts.rsi.update();
        }

        // EMA-Chart aktualisieren
        if (charts.ema) {
            charts.ema.data.labels = timestamps;
            charts.ema.data.datasets[0].data = ema20Values;
            charts.ema.data.datasets[1].data = ema50Values;
            charts.ema.update();
        }
    }

    // Statistiken aktualisieren
    function updateStats(stats) {
        if (stats) {
            $('#totalSignals').text(stats.totalSignals || 0);
            $('#successRate').text((stats.successRate || 0).toFixed(2) + '%');
            $('#avgProfit').text((stats.avgProfit || 0).toFixed(2) + '%');
        }
    }

    // Error Message schließen
    $('.message .close').on('click', function () {
        $(this).closest('.message').hide();
    });

    // Initialisierung und Start
    initCharts();
    loadData();

    // Automatisches Update alle 60 Sekunden
    setInterval(loadData, 30000);
});