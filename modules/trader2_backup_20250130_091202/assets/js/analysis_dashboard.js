// Chart-Objekte
let charts = {};

// Chart-Konfigurationen
const chartConfigs = {
    price: {
        type: 'line',
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
    },
    rsi: {
        type: 'line',
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
    },
    ema: {
        type: 'line',
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
    }
};

// Chart-Initialisierung
function initCharts() {
    initPriceChart();
    initRsiChart();
    initEmaChart();
}

function initPriceChart() {
    const ctx = document.getElementById('priceChart');
    if (!ctx) return;

    charts.price = new Chart(ctx, {
        ...chartConfigs.price,
        data: {
            datasets: [{
                label: 'ETH/USDT',
                borderColor: '#2185d0',
                borderWidth: 2,
                data: [],
                fill: false
            }]
        }
    });
}

function initRsiChart() {
    const ctx = document.getElementById('rsiChart');
    if (!ctx) return;

    charts.rsi = new Chart(ctx, {
        ...chartConfigs.rsi,
        data: {
            datasets: [{
                label: 'RSI',
                borderColor: '#21ba45',
                borderWidth: 2,
                data: [],
                fill: false
            }]
        }
    });
}

function initEmaChart() {
    const ctx = document.getElementById('emaChart');
    if (!ctx) return;

    charts.ema = new Chart(ctx, {
        ...chartConfigs.ema,
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
        }
    });
}

// Daten laden und aktualisieren
function loadData() {
    $.ajax({
        url: 'api/get_analysis_data.php',
        type: 'GET',
        success: function(response) {
            try {
                const data = JSON.parse(response);
                updateCharts(data);
                updateStatistics(data);
                updateSignalsTable(data.signals);
            } catch (error) {
                console.error('Fehler beim Verarbeiten der Daten:', error);
                showError('Fehler beim Laden der Daten');
            }
        },
        error: function(xhr, status, error) {
            console.error('API-Fehler:', error);
            showError('Verbindungsfehler');
        }
    });
}

function updateCharts(data) {
    if (charts.price) {
        charts.price.data.datasets[0].data = data.prices;
        charts.price.update();
    }
    if (charts.rsi) {
        charts.rsi.data.datasets[0].data = data.rsi;
        charts.rsi.update();
    }
    if (charts.ema) {
        charts.ema.data.datasets[0].data = data.ema20;
        charts.ema.data.datasets[1].data = data.ema50;
        charts.ema.update();
    }
}

function updateStatistics(data) {
    $('#totalSignals').text(data.stats.total || '-');
    $('#successRate').text(data.stats.successRate ? data.stats.successRate + '%' : '-%');
    $('#avgProfit').text(data.stats.avgProfit ? data.stats.avgProfit + '%' : '-%');
}

function updateSignalsTable(signals) {
    const tbody = $('#signalsTableBody');
    tbody.empty();

    signals.forEach(signal => {
        tbody.append(`
            <tr>
                <td>${formatDateTime(signal.time)}</td>
                <td>${formatSignal(signal.type)}</td>
                <td>${signal.confidence}%</td>
                <td>${signal.entry}</td>
                <td>${signal.takeProfit}</td>
                <td>${signal.stopLoss}</td>
                <td>${formatResult(signal.result)}</td>
            </tr>
        `);
    });
}

// Hilfsfunktionen
function formatDateTime(timestamp) {
    return new Date(timestamp).toLocaleString();
}

function formatSignal(type) {
    return type === 'buy' ? 
        '<span class="ui green label">LONG</span>' : 
        '<span class="ui red label">SHORT</span>';
}

function formatResult(result) {
    if (!result) return '-';
    const isPositive = result > 0;
    return `<span class="ui ${isPositive ? 'green' : 'red'} label">
        ${isPositive ? '+' : ''}${result}%
    </span>`;
}

function showError(message) {
    $('.ui.error.message').remove();
    $('.ui.container').prepend(`
        <div class="ui error message">
            <i class="close icon"></i>
            <div class="header">Fehler</div>
            <p>${message}</p>
        </div>
    `);
}

// Event Listener
$(document).ready(function() {
    initCharts();
    loadData();
    
    // Automatische Aktualisierung alle 60 Sekunden
    setInterval(loadData, 60000);
    
    // Error Message schließen
    $(document).on('click', '.message .close', function() {
        $(this).closest('.message').fadeOut();
    });
});
