<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SSI-Trader</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }

        .ui.button,
        .ui.input>input {
            width: 100%;
        }

        .ui.teal.header,
        .ui.teal.button {
            background-color: #21ba45 !important;
            /* Grünlicher Farbton */
            color: white !important;
        }
    </style>
</head>

<body>
    <div class="ui raised very padded text container segment" style='width:100%'>
        <h2 class="ui header" style="color: #21ba45;">SSI-Trader Login</h2> <!-- Anpassung der Überschrift -->
        <form class="ui huge form" id="loginForm">
            <div class="field">
                <label>Benutzername</label>
                <div class="ui fluid input"> <!-- Fluid Input für volle Breite -->
                    <input type="text" name="username" placeholder="Benutzername" autofocus
                        value='<?= $_GET['username'] ?>'>
                </div>
            </div>
            <div class="field">
                <label>Passwort</label>
                <div class="ui fluid input"> <!-- Fluid Input für volle Breite -->
                    <input type="password" name="password" placeholder="Passwort">
                </div>
            </div>
            <button class="ui fluid huge teal button" type="submit">Login</button>
            <!-- Fluid Button für volle Breite -->
        </form>
    </div>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.js'></script>
    <script>
        $(document).ready(function () {

            // Überprüfen, ob der URL-Parameter 'error' den Wert 'not_logged_in' hat
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');

            if (error === 'not_logged_in') {
                // Toast-Nachricht anzeigen
                $('body')
                    .toast({
                        class: 'error',
                        message: 'Sie sind nicht eingeloggt. Bitte loggen Sie sich ein.'
                    });
            }

            $('#loginForm').submit(function (e) {
                e.preventDefault();
                // Hier AJAX-Anfrage einfügen
                $.ajax({
                    type: "POST",
                    url: "login2.php", // Pfad zu Ihrer PHP-Datei
                    data: $(this).serialize(), // Daten des Formulars serialisieren und senden
                    success: function (response) {
                        // Überprüfen Sie die Antwort, die Ihr Server sendet
                        // Angenommen, Ihr Server sendet einfach "Erfolg" bei erfolgreicher Anmeldung
                        if (response.trim() === "Erfolg") {
                            window.location.href = 'index.php'; // Weiterleitung zur sicheren Seite
                        } else {
                            alert("Ungültiger Benutzername oder Passwort");
                        }
                    },
                    error: function () {
                        // Behandlung bei Fehler
                        alert("Ein Fehler ist aufgetreten.");
                    }
                });
            });
        });
    </script>
</body>

</html>