<?php
include (__DIR__ . '/../config.php');
include (__DIR__ . '/../functions.php');

$clientId = $_SESSION['client_id'];
$array = getBrokerUserByClientId($db, $clientId);
$accountId = $array['account'];
?>

<select id="timeFrameSelect" class="ui dropdown">
    <!-- <option value="hours" selected>24 Stunden</option> -->
    <option value="days" selected>7 Tage</option>
    <option value="weeks">4 Wochen</option>
    <option value="months">6 Monate</option>
</select>

<select id="chartTypeSelect" class="ui dropdown">
    <option value="bar" selected>Balkendiagramm</option>
    <option value="line">Liniendiagramm</option>
</select>

<canvas id="chartCanvas"></canvas>

<script>
    $(document).ready(function () {
        var currentChart;

        var savedTimeFrame = localStorage.getItem('timeFrame') || 'days';
        var savedChartType = localStorage.getItem('chartType') || 'bar';

        $('#timeFrameSelect').val(savedTimeFrame);
        $('#chartTypeSelect').val(savedChartType);

        function getTimeFrameLabel(timeFrame) {
            switch (timeFrame) {
                case 'hours':
                    return '24 Stunden';
                case 'days':
                    return '14 Tage';
                case 'weeks':
                    return '4 Wochen';
                case 'months':
                    return '6 Monate';
                default:
                    return '';
            }
        }

        function updateChartData(timeFrame, chartType) {
            $.ajax({
                url: 'pages/chart_data.php',
                type: 'POST',
                data: {
                    timeFrame: timeFrame,
                    accountId: '<?= $accountId; ?>'
                },
                success: function (data) {
                    const parsedData = JSON.parse(data);
                    const labels = parsedData.map(item => item.label);
                    const profits = parsedData.map(item => item.profit);
                    drawChart(labels, profits, chartType, timeFrame);
                }
            });
        }

        function drawChart(labels, data, chartType, timeFrame) {
            const ctx = $('#chartCanvas')[0].getContext('2d');
            const timeFrameLabel = getTimeFrameLabel(timeFrame);

            if (currentChart) {
                currentChart.destroy();
            }

            currentChart = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Gewinn',
                        data: data,
                        backgroundColor: data.map(value => value >= 0 ? 'rgba(54, 162, 235, 0.2)' : 'rgba(255, 99, 132, 0.2)'),
                        borderColor: data.map(value => value >= 0 ? 'rgba(54, 162, 235, 1)' : 'rgba(255, 99, 132, 1)'),
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Gewinn/Verlust (€)'
                            },
                            grid: {
                                drawBorder: true,
                                color: function (context) {
                                    if (context.tick.value === 0) {
                                        return 'black';
                                    }
                                    return 'rgba(0, 0, 0, 0.1)';
                                },
                                lineWidth: function (context) {
                                    if (context.tick.value === 0) {
                                        return 2;
                                    }
                                    return 1;
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: getTimeFrameLabel(timeFrame)
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)',
                                lineWidth: 1
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
                            formatter: (value, context) => {
                                if (value !== 0 && context.chart.data.labels.length < 30) {
                                    return Number(value).toFixed(2) + '€';
                                } else {
                                    return null;
                                }
                            },
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
                },
                plugins: [ChartDataLabels]
            });
        }

        $('#timeFrameSelect, #chartTypeSelect').change(function () {
            var timeFrame = $('#timeFrameSelect').val();
            var chartType = $('#chartTypeSelect').val();
            updateChartData(timeFrame, chartType);
            localStorage.setItem('timeFrame', timeFrame);
            localStorage.setItem('chartType', chartType);
        });

        updateChartData(savedTimeFrame, savedChartType);
    });
</script>