<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Services/GoogleAuthService.php';

// OAuth Konfiguration laden
$config = require_once __DIR__ . '/../config/oauth_config.php';
$googleAuthEnabled = false;

try {
    $googleAuth = new \Smart\Services\GoogleAuthService($db, $config['google']);
    $googleAuthEnabled = true;
} catch (\Exception $e) {
    error_log("Google Auth nicht verfügbar: " . $e->getMessage());
}

// Prüfen ob bereits eingeloggt
if (isset($_SESSION['client_id'])) {
    header('Location: ../modules/main/index.php');
    exit;
}

// Error Message aus URL
$error = $_GET['error'] ?? '';
$errorMessage = '';
$successMessage = '';

switch ($error) {
    case 'google_auth_failed':
        $errorMessage = 'Google Anmeldung fehlgeschlagen. Bitte versuchen Sie es erneut.';
        break;
    case 'logout':
        $successMessage = 'Sie wurden erfolgreich abgemeldet.';
        break;
}
?>
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

        .ui.google.button {
            background-color: #db4437;
            color: white;
            margin-top: 1em;
        }

        .ui.google.button:hover {
            background-color: #c53929;
        }

        .ui.horizontal.divider {
            margin: 2em 0;
            color: rgba(0, 0, 0, .6);
        }

        .success-message {
            margin-bottom: 1em;
        }

        .ui.segment {
            position: relative;
        }

        .loader-container {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
    </style>
</head>

<body>
    <div class="ui raised very padded text container segment" style="width:100%; max-width:450px;">
        <h2 class="ui header" style="color: #21ba45;">
            <i class="sign-in icon"></i>
            <div class="content">
                Smart 8 Login
                <div class="sub header">Bitte melden Sie sich an</div>
            </div>
        </h2>

        <?php if (!empty($errorMessage)): ?>
            <div class="ui negative message">
                <i class="close icon"></i>
                <div class="header">Fehler</div>
                <p><?php echo htmlspecialchars($errorMessage); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($successMessage)): ?>
            <div class="ui success message">
                <i class="close icon"></i>
                <p><?php echo htmlspecialchars($successMessage); ?></p>
            </div>
        <?php endif; ?>

        <div class="ui negative message error-message">
            <i class="close icon"></i>
            <div class="header">Fehler</div>
            <p class="error-text"></p>
        </div>

        <form class="ui huge form" id="loginForm">
            <div class="field">
                <label>Benutzername</label>
                <div class="ui left icon input">
                    <i class="user icon"></i>
                    <input type="text" name="username" id="username" placeholder="Benutzername oder E-Mail" autofocus
                        required>
                </div>
            </div>

            <div class="field">
                <label>Passwort</label>
                <div class="ui left icon input">
                    <i class="lock icon"></i>
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

            <button class="ui fluid huge teal submit button" type="submit">
                <i class="sign-in icon"></i>
                Anmelden
            </button>
        </form>

        <?php if ($googleAuthEnabled): ?>
            <div class="ui horizontal divider">
                oder
            </div>

            <a href="<?php echo htmlspecialchars($googleAuth->getAuthUrl()); ?>" class="ui fluid huge google button">
                <i class="google icon"></i>
                Mit Google anmelden
            </a>
        <?php endif; ?>

        <div class="loader-container">
            <div class="ui active massive loader"></div>
        </div>
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
                            window.location.href = response.redirect || '../modules/main/index.php';
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

            // Google Button Loading State
            $('.google.button').on('click', function () {
                $(this).addClass('loading disabled');
                $('.loader-container').css('display', 'flex');
            });

            // Automatischer Fokus auf Username-Feld
            setTimeout(function () {
                $('#username').focus();
            }, 100);
        });
    </script>
</body>

</html>