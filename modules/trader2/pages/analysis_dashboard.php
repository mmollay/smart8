<?php include __DIR__ . '/../t_config.php'; ?>


<div class="ui container" style="padding-top: 2em;">
    <!-- Statistiken -->
    <div class="ui three small statistics">
        <div class="statistic" data-tooltip="Anzahl der Trading-Signale">
            <div class="value" id="totalSignals">-</div>
            <div class="label">Signale</div>
        </div>
        <div class="statistic" data-tooltip="Erfolgsquote der Signale">
            <div class="value" id="successRate">-%</div>
            <div class="label">Erfolgsrate</div>
        </div>
        <div class="statistic" data-tooltip="Durchschnittlicher Gewinn/Verlust">
            <div class="value" id="avgProfit">-</div>
            <div class="label">Ø Profit</div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="ui grid">
        <!-- Preis-Chart -->
        <div class="sixteen wide column">
            <div class="ui segment">
                <div class="ui horizontal label" data-tooltip="ETH/USDT Kursverlauf">
                    <i class="chart line icon"></i> Preis
                </div>
                <div style="position: relative; height: 400px;">
                    <canvas id="priceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Indikatoren -->
        <div class="eight wide column">
            <div class="ui segment">
                <div class="ui horizontal label" data-tooltip="RSI: >70 überkauft, <30 überverkauft">
                    <i class="chart bar icon"></i> RSI
                </div>
                <div style="position: relative; height: 300px;">
                    <canvas id="rsiChart"></canvas>
                </div>
            </div>
        </div>

        <div class="eight wide column">
            <div class="ui segment">
                <div class="ui horizontal label" data-tooltip="EMA20 (blau) und EMA50 (rot) zeigen Trends">
                    <i class="chart area icon"></i> EMA
                </div>
                <div style="position: relative; height: 300px;">
                    <canvas id="emaChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Signaltabelle -->
    <div class="ui segment">
        <div class="ui horizontal label">
            <i class="signal icon"></i> Trading Signale
        </div>
        <table class="ui compact celled table">
            <thead>
                <tr>
                    <th>Zeit</th>
                    <th>Signal</th>
                    <th data-tooltip="Stärke des Signals">Konfidenz</th>
                    <th data-tooltip="Einstiegspreis">Entry</th>
                    <th data-tooltip="Take-Profit">TP</th>
                    <th data-tooltip="Stop-Loss">SL</th>
                    <th>Ergebnis</th>
                </tr>
            </thead>
            <tbody id="signalsTableBody">
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function () {
        let charts = {};

        function initCharts() {
            // Preis-Chart
            const priceCtx = document.getElementById('priceChart');
            if (priceCtx) {
                charts.price = new Chart(priceCtx, {
                    type: 'line',
                    data: {
                        datasets: [{
                            label: 'ETH/USDT',
                            borderColor: '#2185d0',
                            borderWidth: 2,
                            data: [],
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                type: 'time',
                                time: {
                                    unit: 'minute',
                                    displayFormats: {
                                        minute: 'HH:mm'
                                    }
                                }
                            },
                            y: {
                                beginAtZero: false
                            }
                        }
                    }
                });
            }

            // RSI-Chart
            const rsiCtx = document.getElementById('rsiChart');
            if (rsiCtx) {
                charts.rsi = new Chart(rsiCtx, {
                    type: 'line',
                    data: {
                        datasets: [{
                            label: 'RSI',
                            borderColor: '#21ba45',
                            borderWidth: 2,
                            data: [],
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                type: 'time',
                                time: {
                                    unit: 'minute'
                                }
                            },
                            y: {
                                min: 0,
                                max: 100
                            }
                        }
                    }
                });
            }

            // EMA-Chart
            const emaCtx = document.getElementById('emaChart');
            if (emaCtx) {
                charts.ema = new Chart(emaCtx, {
                    type: 'line',
                    data: {
                        datasets: [
                            {
                                label: 'EMA20',
                                borderColor: '#2185d0',
                                borderWidth: 2,
                                data: [],
                                fill: false
                            },
                            {
                                label: 'EMA50',
                                borderColor: '#db2828',
                                borderWidth: 2,
                                data: [],
                                fill: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                type: 'time',
                                time: {
                                    unit: 'minute'
                                }
                            },
                            y: {
                                beginAtZero: false
                            }
                        }
                    }
                });
            }
        }

        function loadData() {
            $.ajax({
                url: 'api/get_analysis_data.php',
                type: 'GET',
                success: function (response) {
                    if (!response.success) return;

                    // Charts aktualisieren
                    if (response.marketData && response.marketData.length > 0) {
                        // Preis-Chart
                        if (charts.price) {
                            charts.price.data.datasets[0].data = response.marketData.map(d => ({
                                x: moment(parseInt(d.timestamp)),
                                y: parseFloat(d.price)
                            }));
                            charts.price.update('none');
                        }

                        // RSI-Chart
                        if (charts.rsi) {
                            charts.rsi.data.datasets[0].data = response.marketData.map(d => ({
                                x: moment(parseInt(d.timestamp)),
                                y: parseFloat(d.rsi)
                            }));
                            charts.rsi.update('none');
                        }

                        // EMA-Charts
                        if (charts.ema) {
                            charts.ema.data.datasets[0].data = response.marketData.map(d => ({
                                x: moment(parseInt(d.timestamp)),
                                y: parseFloat(d.ema20)
                            }));
                            charts.ema.data.datasets[1].data = response.marketData.map(d => ({
                                x: moment(parseInt(d.timestamp)),
                                y: parseFloat(d.ema50)
                            }));
                            charts.ema.update('none');
                        }
                    }

                    // Signaltabelle aktualisieren
                    if (response.recentSignals) {
                        const tbody = $('#signalsTableBody');
                        tbody.empty();

                        response.recentSignals.forEach(function (signal) {
                            const row = $('<tr>');
                            row.append(`
                            <td>${moment(parseInt(signal.timestamp)).format('DD.MM.YY HH:mm')}</td>
                            <td>
                                <div class="ui label ${signal.action === 'buy' ? 'green' : 'red'}">
                                    ${signal.action.toUpperCase()}
                                </div>
                            </td>
                            <td>${parseFloat(signal.confidence).toFixed(2)}%</td>
                            <td>$${parseFloat(signal.entry_price).toFixed(2)}</td>
                            <td>$${parseFloat(signal.tp_price).toFixed(2)}</td>
                            <td>$${parseFloat(signal.sl_price).toFixed(2)}</td>
                            <td>${signal.result ? parseFloat(signal.result).toFixed(2) + '%' : '-'}</td>
                        `);
                            tbody.append(row);
                        });
                    }

                    // Statistiken aktualisieren
                    if (response.stats) {
                        $('#totalSignals').text(response.stats.totalSignals);
                        $('#successRate').text(response.stats.successRate.toFixed(2) + '%');
                        $('#avgProfit').text(response.stats.avgProfit.toFixed(2) + '%');
                    }
                }
            });
        }

        // Start
        initCharts();
        loadData();
        setInterval(loadData, 60000);

        // UI
        $('[data-tooltip]').popup({
            position: 'top center'
        });
    });
</script>

<style>
    .ui.horizontal.label {
        margin-bottom: 1em;
    }

    .ui.statistics {
        margin-bottom: 2em;
    }

    .chart-container {
        position: relative;
        width: 100%;
    }
</style>