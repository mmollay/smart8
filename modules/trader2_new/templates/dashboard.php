<?php
// Absolute Pfade für Includes
$moduleRoot = realpath(__DIR__ . '/..');
require_once($moduleRoot . '/config/t_config.php');
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trading Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    
    <!-- ApexCharts CSS -->
    <link href="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../css/dashboard.css" rel="stylesheet">
    
    <style>
        #priceChart {
            width: 100%;
            min-height: 400px;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
        
        .market-data {
            font-size: 0.9rem;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Linke Seitenleiste -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" data-view="overview">
                                <i class="bi bi-house"></i> Übersicht
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-view="positions">
                                <i class="bi bi-graph-up"></i> Positionen
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-view="orders">
                                <i class="bi bi-list-task"></i> Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-view="signals">
                                <i class="bi bi-bell"></i> Signale
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-view="settings">
                                <i class="bi bi-gear"></i> Einstellungen
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Hauptbereich -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Übersichts-Karten -->
                <div class="row mt-4" id="overview-cards">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Kontostand</h6>
                                <h4 class="card-title" id="account-balance">-</h4>
                                <p class="card-text text-success" id="account-pnl">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Offene Positionen</h6>
                                <h4 class="card-title" id="open-positions">-</h4>
                                <p class="card-text" id="positions-pnl">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Aktive Orders</h6>
                                <h4 class="card-title" id="active-orders">-</h4>
                                <p class="card-text" id="orders-info">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Trading Signale</h6>
                                <h4 class="card-title" id="active-signals">-</h4>
                                <p class="card-text" id="signals-info">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart-Bereich -->
                <div class="row mt-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title">Chart</h5>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary active" data-interval="1m">1m</button>
                                        <button type="button" class="btn btn-outline-primary" data-interval="5m">5m</button>
                                        <button type="button" class="btn btn-outline-primary" data-interval="15m">15m</button>
                                        <button type="button" class="btn btn-outline-primary" data-interval="1h">1h</button>
                                        <button type="button" class="btn btn-outline-primary" data-interval="4h">4h</button>
                                        <button type="button" class="btn btn-outline-primary" data-interval="1d">1d</button>
                                    </div>
                                </div>
                                <!-- Chart Container -->
                                <div id="priceChart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Marktdaten</h5>
                                <div class="market-data">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Aktueller Preis:</span>
                                        <span id="currentPrice" class="fw-bold">--.--</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>24h Hoch:</span>
                                        <span id="high24h" class="text-success">--.--</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>24h Tief:</span>
                                        <span id="low24h" class="text-danger">--.--</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>24h Volumen:</span>
                                        <span id="volume24h">--.--</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>24h Änderung:</span>
                                        <span id="change24h">--.--</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>ADX:</span>
                                        <span id="adxValue">--.--</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>ATR (%):</span>
                                        <span id="atrValue">--.--</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>ROC (%):</span>
                                        <span id="rocValue">--.--</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>+DI:</span>
                                        <span id="plusDI">--.--</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>-DI:</span>
                                        <span id="minusDI">--.--</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap Bundle mit Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.min.js"></script>
    
    <!-- Custom Scripts -->
    <script src="../js/websocket.js"></script>
    <script src="../js/dashboard.js"></script>
</body>
</html>
