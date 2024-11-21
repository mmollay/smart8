<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart 8 Login</title>
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
            color: white !important;
        }

        .forgot-password {
            text-align: right;
            margin-top: -10px;
            margin-bottom: 15px;
        }

        .forgot-password a {
            color: #666;
            font-size: 0.9em;
        }

        .forgot-password a:hover {
            color: #21ba45;
        }

        .remember-me {
            margin-bottom: 15px;
        }

        .ui.modal {
            max-width: 500px;
        }

        #loading {
            display: none;
            margin-top: 1em;
        }

        .error-message {
            display: none;
            margin-bottom: 1em;
        }
    </style>
</head>

<body>
    <div class="ui raised very padded text container segment" style="width:100%; max-width:450px;">
        <h2 class="ui header" style="color: #21ba45;">Smart 8 Login</h2>

        <div class="ui negative message error-message">
            <i class="close icon"></i>
            <div class="header">Fehler</div>
            <p class="error-text"></p>
        </div>

        <form class="ui huge form" id="loginForm">
            <div class="field">
                <label>Benutzername</label>
                <div class="ui fluid input">
                    <input type="text" name="username" id="username" placeholder="Benutzername" autofocus required>
                </div>
            </div>
            <div class="field">
                <label>Passwort</label>
                <div class="ui fluid input">
                    <input type="password" name="password" id="password" placeholder="Passwort" required>
                </div>
            </div>
            <div class="forgot-password">
                <a href="reset_password.php">Passwort vergessen?</a>
            </div>
            <div class="field remember-me">
                <div class="ui checkbox">
                    <input type="checkbox" name="remember" id="remember">
                    <label>Angemeldet bleiben</label>
                </div>
            </div>
            <div class="ui active centered inline loader" id="loading"></div>
            <button class="ui fluid huge teal button" type="submit">
                <i class="sign-in icon"></i>
                Login
            </button>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.js"></script>
    <script>
        $(document).ready(function () {
            // UI-Elemente initialisieren
            $('.ui.checkbox').checkbox();
            $('.message .close').on('click', function () {
                $(this).closest('.message').transition('fade');
            });

            // Login-Formular Handler
            $('#loginForm').submit(function (e) {
                e.preventDefault();

                const $form = $(this);
                const $submitButton = $form.find('button[type="submit"]');
                const $loading = $('#loading');
                const $errorMessage = $('.error-message');

                // Button deaktivieren und Loading anzeigen
                $submitButton.addClass('loading disabled');
                $loading.show();
                $errorMessage.hide();

                var formData = {
                    username: $('#username').val().trim(),
                    password: $('#password').val(),
                    remember: $('#remember').is(':checked')
                };

                $.ajax({
                    type: "POST",
                    url: "login2.php",
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            window.location.href = response.redirect || 'modules/main/index.php';
                        } else {
                            $('.error-text').text(response.message || 'Ungültige Anmeldedaten');
                            $errorMessage.show();
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Login error:', error);
                        $('.error-text').text('Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.');
                        $errorMessage.show();
                    },
                    complete: function () {
                        // Button reaktivieren und Loading ausblenden
                        $submitButton.removeClass('loading disabled');
                        $loading.hide();
                    }
                });
            });

            // Fehlermeldung bei Escape-Taste ausblenden
            $(document).keyup(function (e) {
                if (e.key === "Escape") {
                    $('.error-message').hide();
                }
            });
        });
    </script>
</body>

</html>