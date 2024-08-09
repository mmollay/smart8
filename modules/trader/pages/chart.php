<?php
// Der PHP-Teil ist hauptsächlich serverseitig und für die Einrichtung der initialen Seite zuständig.
// Stellen Sie sicher, dass Ihre AJAX-Endpunkte korrekt konfiguriert sind, um Daten für alle Accounts abzurufen.
$accountColors = [
    "#1f77b4",
    "#ff7f0e",
    "#9467bd",
    "#8c564b",
    "#e377c2",
    "#7f7f7f",
    "#bcbd22",
    "#17becf",
    "#1a55FF",
    "#FF5733",
    "#33FF57",
    "#8E44AD",
    "#3498DB",
    "#F1C40F",
    "#E67E22",
    "#E74C3C",
    "#2ECC71",
    "#16A085"
];
?>

<select id="timeFrameSelect" class="ui dropdown">
    <option value="hours">24 Stunden</option>
    <option value="days" selected>7 Tage</option>
    <option value="weeks">4 Wochen</option>
    <option value="months">6 Monate</option>
</select>

<select id="chartTypeSelect" class="ui dropdown">
    <option value="bar" selected>Balkendiagramm</option>
    <option value="line">Liniendiagramm</option>
</select>

<select id="accountTypeSelect" class="ui dropdown">
    <option value="0">Alle Accounts</option>
    <option value="1" selected>Real Accounts</option>
    <option value="2">Demo Accounts</option>
</select>

<select id="profitFilter" class="ui dropdown">
    <option value="all">Alle Gewinne/Verluste</option>
    <option value="positive" selected>Nur positive Gewinne</option>
    <option value="negative">Nur negative Gewinne</option>
</select>

<canvas id="chartCanvas"></canvas>

<script>
    $(document).ready(function () {
        const accountColors = <?php echo json_encode($accountColors); ?>;
        var currentChart;

        // Lese Werte aus dem Local Storage oder setze Standardwerte
        var timeFrame = localStorage.getItem('timeFrame') || 'days';
        var chartType = localStorage.getItem('chartType') || 'bar';
        var accountType = localStorage.getItem('accountType') || '0';
        var profitFilter = localStorage.getItem('profitFilter') || 'all';

        // Setze die Werte im Dropdown entsprechend den geladenen oder Standardwerten
        $('#timeFrameSelect').val(timeFrame);
        $('#chartTypeSelect').val(chartType);
        $('#accountTypeSelect').val(accountType);
        $('#profitFilter').val(profitFilter);

        // Funktion zur Rückgabe des Labels basierend auf dem gewählten Zeitrahmen
        function getTimeFrameLabel(timeFrame) {
            switch (timeFrame) {
                case 'hours':
                    return '24 Stunden';
                case 'days':
                    return '7 Tage';
                case 'weeks':
                    return '4 Wochen';
                case 'months':
                    return '6 Monate';
                default:
                    return ''; // Rückgabe eines leeren Strings bei Fehlern
            }
        }

        // Funktion zum Aktualisieren und Zeichnen des Diagramms
        function updateChartData(tf, ct, at, pf) {
            timeFrame = tf;
            chartType = ct;
            accountType = at;
            profitFilter = pf;

            $.ajax({
                url: 'ajax/content_chart_data.php',
                type: 'POST',
                data: {
                    timeFrame: timeFrame,
                    chartType: chartType,
                    accountType: accountType
                },
                success: function (data) {
                    const responseData = JSON.parse(data);

                    drawChart(responseData);
                }
            });
        }

        // Funktion zum Zeichnen des Diagramms
        function drawChart(accountsData) {
            const ctx = $('#chartCanvas')[0].getContext('2d');
            if (currentChart) {
                currentChart.destroy(); // Zerstöre das vorherige Diagramm, falls vorhanden
            }

            const datasets = accountsData.map((account, index) => ({
                label: account.title,
                data: account.data.map(item => +item.profit),
                backgroundColor: accountColors[index % accountColors.length],
                borderColor: accountColors[index % accountColors.length],
                borderWidth: 1
            }));

            // Filtere die Datensätze basierend auf dem Profit-Filter
            const filteredDatasets = datasets.map(dataset => {
                if (profitFilter === 'positive') {
                    dataset.data = dataset.data.filter(value => value >= 0);
                } else if (profitFilter === 'negative') {
                    dataset.data = dataset.data.filter(value => value < 0);
                }
                return dataset;
            });

            const labels = accountsData[0].data.map(item => item.label);
            const timeFrameLabel = getTimeFrameLabel(timeFrame);

            currentChart = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: filteredDatasets
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Gewinn/Verlust (€)'
                            },
                            ticks: {
                                callback: (value) => '€' + value.toFixed(2)
                            },
                            grid: {
                                drawBorder: true,
                                color: context => context.tick.value === 0 ? 'black' : 'rgba(0, 0, 0, 0.1)',
                                lineWidth: context => context.tick.value === 0 ? 2 : 1
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: timeFrameLabel
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)',
                                lineWidth: 1
                            },
                            ticks: {
                                callback: function (value, index, ticks) {
                                    return this.getLabelForValue(value);
                                }
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Umsatzentwicklung ' + timeFrameLabel
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            formatter: (value, context) => value !== 0 && context.chart.data.labels.length < 10 ? Number(value).toFixed(2) + '€' : null,
                            color: '#444',
                            font: {
                                weight: 'bold'
                            }
                        }
                    },
                    elements: {
                        point: {
                            radius: 5,
                            pointStyle: 'circle'
                        }
                    }
                }
            });
        }

        function getTimeFrameLabel(timeFrame) {
            switch (timeFrame) {
                case 'hours':
                    return '24 Stunden';
                case 'days':
                    return '7 Tage';
                case 'weeks':
                    return '4 Wochen';
                case 'months':
                    return '6 Monate';
                default:
                    return ''; // Rückgabe eines leeren Strings bei Fehlern
            }
        }
        // Initialisiere das Diagramm mit den gespeicherten oder Standardwerten
        updateChartData(timeFrame, chartType, accountType, profitFilter);

        // Event-Listener für Dropdown-Änderungen und Speicherung der neuen Werte im Local Storage
        $('#timeFrameSelect').on('change', function () {
            localStorage.setItem('timeFrame', $(this).val());
            updateChartData($(this).val(), chartType, accountType, profitFilter);
        });

        $('#chartTypeSelect').on('change', function () {
            localStorage.setItem('chartType', $(this).val());
            updateChartData(timeFrame, $(this).val(), accountType, profitFilter);
        });

        $('#accountTypeSelect').on('change', function () {
            localStorage.setItem('accountType', $(this).val());
            updateChartData(timeFrame, chartType, $(this).val(), profitFilter);
        });

        $('#profitFilter').on('change', function () {
            localStorage.setItem('profitFilter', $(this).val());
            updateChartData(timeFrame, chartType, accountType, $(this).val());
        });
    });
</script>