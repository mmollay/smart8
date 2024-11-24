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
    <title>Smart 8 Login - SSI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .logo-container {
            text-align: center;
            padding: 2rem;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .logo-container img {
            max-width: 200px;
            height: auto;
        }

        .form-container {
            padding: 2rem;
        }

        .ui.button,
        .ui.input>input {
            width: 100%;
        }

        .ui.teal.button {
            background-color: #21ba45 !important;
            color: white !important;
            transition: background-color 0.3s ease;
        }

        .ui.teal.button:hover {
            background-color: #1ea83e !important;
        }

        .forgot-password {
            text-align: right;
            margin: -5px 0 15px;
        }

        .forgot-password a {
            color: #666;
            font-size: 0.9em;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #21ba45;
        }

        .ui.checkbox label {
            color: #666;
        }

        .ui.input>input {
            border-radius: 8px;
        }

        .ui.input>input:focus {
            border-color: #21ba45;
        }

        .ui.google.button {
            background-color: #db4437;
            color: white;
            margin-top: 1em;
            transition: background-color 0.3s ease;
        }

        .ui.google.button:hover {
            background-color: #c53929;
        }

        .version-info {
            text-align: center;
            color: #666;
            font-size: 0.8em;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
        }

        .field label {
            color: #495057 !important;
            font-weight: 500 !important;
            margin-bottom: 0.5rem !important;
        }

        .ui.message {
            margin-bottom: 1.5rem;
            border-radius: 8px;
            box-shadow: none;
        }

        .ui.horizontal.divider {
            margin: 2em 0;
            color: #666;
        }

        .loader-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
            }

            .form-container {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="logo-container">
            <!-- Ersetzen Sie den src mit dem tatsächlichen Pfad zu Ihrem Logo -->
            <img src="../img/logo.png" alt="SSI Logo" />
        </div>

        <div class="form-container">
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

            <div class="ui negative message error-message" style="display: none;">
                <i class="close icon"></i>
                <div class="header">Fehler</div>
                <p class="error-text"></p>
            </div>

            <form class="ui large form" id="loginForm">
                <div class="field">
                    <label>Benutzername</label>
                    <div class="ui left icon input">
                        <i class="user icon"></i>
                        <input type="text" name="username" id="username" placeholder="Benutzername oder E-Mail"
                            autofocus required>
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

                <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" name="remember" id="remember">
                        <label>Angemeldet bleiben</label>
                    </div>
                </div>

                <button class="ui fluid large teal submit button" type="submit">
                    <i class="sign-in icon"></i>
                    Anmelden
                </button>
            </form>

            <?php if ($googleAuthEnabled): ?>
                <div class="ui horizontal divider">oder</div>

                <a href="<?php echo htmlspecialchars($googleAuth->getAuthUrl()); ?>" class="ui fluid large google button">
                    <i class="google icon"></i>
                    Mit Google anmelden
                </a>
            <?php endif; ?>

            <div class="version-info">
                Smart 8 v8.1.0
            </div>
        </div>

        <div class="loader-container" style="display: none;">
            <div class="ui active massive loader"></div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.ui.checkbox').checkbox();

            $('.message .close').on('click', function () {
                $(this).closest('.message').transition('fade');
            });

            $('#loginForm').submit(function (e) {
                e.preventDefault();

                const $form = $(this);
                const $submitButton = $form.find('button[type="submit"]');
                const $loading = $('.loader-container');
                const $errorMessage = $('.error-message');

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
                        $submitButton.removeClass('loading disabled');
                        $loading.hide();
                    }
                });
            });

            $(document).keyup(function (e) {
                if (e.key === "Escape") {
                    $('.error-message').hide();
                }
            });

            $('.google.button').on('click', function () {
                $(this).addClass('loading disabled');
                $('.loader-container').css('display', 'flex');
            });

            setTimeout(function () {
                $('#username').focus();
            }, 100);
        });
    </script>
</body>

</html>