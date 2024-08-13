<?php
include (__DIR__ . "/config.php");

function getUserDetails($userId, $db)
{
    // Die SQL-Abfrage vorbereiten
    $stmt = $db->prepare("SELECT first_name, last_name FROM clients WHERE client_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Gibt "first_name last_name" als einen String zurück
        return $user['first_name'] . ' ' . $user['last_name'];
    } else {
        return null; // Kein Nutzer gefunden
    }
}

?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSI Trader Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

    <script>var smart_form_wp = 'smartform/'</script>
    <script src="smartform/js/smart_list.js"></script>
    <script src="../../smartform2/js/listGenerator.js"></script>


    <style>
        body {
            //display: flex;
            //flex-direction: column;
            //min-height: 100vh;
            background-color: #E8F5E9;
            /* Ein sehr helles Grün als Hintergrund */
        }

        .pusher {
            flex: 1;
            padding-top: 5em;
        }

        .ui.container {
            padding: 1em 0.5em;
        }

        .ui.vertical.menu .header.item {
            background-color: #A5D6A7;
            /* Ein mittleres, zartes Grün */
            color: white !important;
            font-size: 1.2em;
            padding: 1em !important;
        }

        .ui.green.top.massive.fixed.menu {
            background-color: #81C784;
            /* Ein kräftigeres Grün */
        }

        .ui.sidebar.menu .item {
            color: #4CAF50;
            /* Ein lebhaftes Grün */
        }

        .ui.button,
        .ui.buttons .button {
            background-color: #66BB6A;
            /* Ein angenehmes Grün für Buttons */
            color: white;
        }

        /* Ändert die Farbe des Menü-Icons */
        #toggleMenu .sidebar.icon {
            color: #4CAF50;
            /* Passend zum restlichen Design */
        }
    </style>
</head>

<body>

    <!-- Sidebar Menu -->
    <div class="ui vertical labeled icon sidebar menu">
        <!-- SSI-Trader als Button mit Verknüpfung zu home.php -->
        <a class="item" href="#" data-page="home">
            <div class="header">SSI-Trader</div>
        </a>
        <a class="item" href="#" data-page="home"><i class="home icon"></i>Home</a>
        <a class="item" href="#" data-page="trades"><i class="chart bar icon"></i>Trades</a>
        <a class="item" href="#" data-page="trades2"><i class="chart bar icon"></i>Trades2</a>
        <a class="item" href="#" data-page="charts"><i class="chartline icon"></i>Charts</a>
        <a class="item" href="#" data-page="settings"><i class="settings icon"></i>Einstellungen</a>
        <a class="item" href="#" id="logout"><i class="sign out icon"></i>Logout</a>
    </div>

    <!-- Pusher für Inhalte -->
    <div class="pusher">
        <!-- Menu bar -->
        <div class="ui green top massive fixed menu">
            <a class="item" id="toggleMenu">
                <i class="sidebar icon"></i>
                Menü
            </a>
            <div class="item">
                <? echo getUserDetails($_SESSION['client_id'], $db) ?>
            </div>
        </div>

        <!-- Dashboard Inhalte -->
        <div class="ui container">
            <div id="pageContent">
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {

            $('.ui.sidebar').sidebar({
                context: $('.bottom.segment'),
                transition: 'overlay'
            });

            // Prüfen, ob eine Seite im Local Storage gespeichert ist
            var lastPage = localStorage.getItem('lastPage') || 'trades';
            loadPageContent(lastPage); // Laden der zuletzt gespeicherten oder der Home-Seite

            // Menü-Links Klick-Handler
            $('.ui.sidebar.menu .item').not('.header').click(function (e) {
                e.preventDefault(); // Verhindert die Standardaktion des Links
                var page = $(this).data('page'); // Holt den Wert des data-page Attributs
                localStorage.setItem('lastPage', page); // Speichert die aktuelle Seite im Local Storage
                loadPageContent(page);
            });

            // Menu Toggle
            $('#toggleMenu').click(function () {
                $('.ui.sidebar').sidebar('toggle');
            });

            // Logout Handler
            $('#logout').click(function (e) {
                e.preventDefault();
                $.ajax({
                    url: 'logout.php', // Pfad zur Logout-Logik
                    method: 'POST',
                    success: function () {
                        localStorage.removeItem('lastPage'); // Beim Logout den gespeicherten Wert löschen
                        window.location.href = 'login.php'; // Umleitung zur Login-Seite
                    }
                });
            });

            // Funktion zum Laden der Seiteninhalte
            function loadPageContent(page) {
                $.ajax({
                    url: "pages/" + page + '.php', // Der Pfad zur PHP-Datei basierend auf dem data-page Wert
                    method: "GET",
                    success: function (response) {
                        $('#pageContent').html(response);
                    },
                    error: function () {
                        alert('Seite konnte nicht geladen werden.');
                    }
                });
            }
        });
    </script>

</body>

</html>