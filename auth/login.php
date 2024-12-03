<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Services/GoogleAuthService.php';
e
// OAuth Konfiguration laden
$config = require_once __DIR__ . '/../config/oauth_config.php';
$googleAuthEnabled = false;
$googleAuthUrl = '';

try {
    $googleAuth = new \Smart\Services\GoogleAuthService($db, $config['google']);
    $googleAuthEnabled = true;
    $googleAuthUrl = $googleAuth->getAuthUrl();
} catch (\Exception $e) {
    error_log("Google Auth nicht verfügbar: " . $e->getMessage());
}

// Prüfen ob bereits eingeloggt
if (isset($_SESSION['client_id']) and $_SESSION['client_id'] != '') {
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

        .ui.button {
            margin: 0.5em 0;
        }

        .ui.teal.button {
            background-color: #21ba45 !important;
            color: white !important;
        }

        .ui.google.button {
            background-color: #4285f4;
            color: white;
        }

        .password-requirements {
            font-size: 0.9em;
            margin-top: 1em;
            display: none;
        }

        .password-requirements.visible {
            display: block;
        }

        .requirement {
            margin: 0.3em 0;
            color: #666;
        }

        .requirement.met {
            color: #21ba45;
        }

        .requirement i {
            margin-right: 0.5em;
        }

        .password-strength {
            margin-top: 0.5em;
            height: 4px;
            background: #eee;
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
            background: #db2828;
        }

        .register-fields {
            display: none;
        }

        .register-fields.visible {
            display: block;
        }

        .divider-text {
            position: relative;
            text-align: center;
            margin: 1.5em 0;
        }

        .divider-text::before,
        .divider-text::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #e0e0e0;
        }

        .divider-text::before {
            left: 0;
        }

        .divider-text::after {
            right: 0;
        }

        .divider-text span {
            background: white;
            padding: 0 1em;
            color: #666;
            font-size: 0.9em;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
            }
        }

        .shake {
            animation: shake 0.6s cubic-bezier(.36, .07, .19, .97) both;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="logo-container">
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

            <form id="authForm" class="ui large form">
                <div class="field">
                    <label>E-Mail</label>
                    <div class="ui left icon input">
                        <i class="user icon"></i>
                        <input type="email" name="email" placeholder="E-Mail-Adresse" required>
                    </div>
                </div>

                <div class="field">
                    <label>Passwort</label>
                    <div class="ui left icon input">
                        <i class="lock icon"></i>
                        <input type="password" name="password" placeholder="Passwort" required>
                    </div>
                </div>

                <div class="register-fields">
                    <div class="field">
                        <label>Passwort bestätigen</label>
                        <div class="ui left icon input">
                            <i class="lock icon"></i>
                            <input type="password" name="confirm_password" placeholder="Passwort bestätigen">
                        </div>
                    </div>

                    <div class="password-requirements">
                        <div class="password-strength">
                            <div class="strength-bar"></div>
                        </div>
                        <div class="requirement" data-requirement="length">
                            <i class="circle outline icon"></i>
                            Mindestens 8 Zeichen
                        </div>
                        <div class="requirement" data-requirement="lowercase">
                            <i class="circle outline icon"></i>
                            Mindestens ein Kleinbuchstabe
                        </div>
                        <div class="requirement" data-requirement="uppercase">
                            <i class="circle outline icon"></i>
                            Mindestens ein Großbuchstabe
                        </div>
                        <div class="requirement" data-requirement="number">
                            <i class="circle outline icon"></i>
                            Mindestens eine Zahl
                        </div>
                        <div class="requirement" data-requirement="special">
                            <i class="circle outline icon"></i>
                            Mindestens ein Sonderzeichen
                        </div>
                    </div>
                </div>

                <div class="login-fields">
                    <div class="field">
                        <div class="ui checkbox">
                            <input type="checkbox" name="remember">
                            <label>Angemeldet bleiben</label>
                        </div>
                    </div>

                    <div style="text-align: right; margin-bottom: 1em;">
                        <a href="reset_password.php" class="ui link">Passwort vergessen?</a>
                    </div>
                </div>

                <button class="ui fluid large teal submit button" type="submit">
                    <i class="sign-in icon"></i>
                    <span class="button-text">Anmelden</span>
                </button>

                <button type="button" class="ui fluid large button toggle-auth-mode">
                    <span class="button-text">Neu hier? Jetzt registrieren</span>
                </button>

                <?php if ($googleAuthEnabled): ?>
                    <div class="divider-text">
                        <span>oder</span>
                    </div>

                    <a href="<?php echo htmlspecialchars($googleAuthUrl); ?>" class="ui fluid google button">
                        <i class="google icon"></i>
                        Mit Google anmelden
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="ui dimmer">
            <div class="ui loader"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.js"></script>
    <script>
        $(document).ready(function () {
            let isRegistering = false;
            const $form = $('#authForm');
            const $registerFields = $('.register-fields');
            const $loginFields = $('.login-fields');
            const $passwordInput = $('input[name="password"]');
            const $requirements = $('.requirement');
            const $strengthBar = $('.strength-bar');
            const $toggleButton = $('.toggle-auth-mode');
            const $submitButton = $('.submit.button');

            // UI Initialisierung
            $('.ui.checkbox').checkbox();

            $('.message .close').on('click', function () {
                $(this).closest('.message').transition('fade');
            });

            // Toggle zwischen Login und Registrierung
            $toggleButton.click(function () {
                isRegistering = !isRegistering;
                $form.trigger('reset');

                if (isRegistering) {
                    $registerFields.slideDown();
                    $loginFields.slideUp();
                    $('.password-requirements').slideDown();
                    $submitButton.find('.button-text').text('Registrieren');
                    $toggleButton.find('.button-text').text('Zurück zum Login');
                } else {
                    $registerFields.slideUp();
                    $loginFields.slideDown();
                    $('.password-requirements').slideUp();
                    $submitButton.find('.button-text').text('Anmelden');
                    $toggleButton.find('.button-text').text('Neu hier? Jetzt registrieren');
                }
            });

            // Passwort-Validierung
            function validatePassword(password) {
                const requirements = {
                    length: password.length >= 8,
                    lowercase: /[a-z]/.test(password),
                    uppercase: /[A-Z]/.test(password),
                    number: /[0-9]/.test(password),
                    special: /[^A-Za-z0-9]/.test(password)
                };

                let score = 0;
                Object.entries(requirements).forEach(([key, met]) => {
                    const $req = $(`.requirement[data-requirement="${key}"]`);
                    if (met) {
                        $req.addClass('met').find('i').removeClass('circle outline').addClass('check circle');
                        score++;
                    } else {
                        $req.removeClass('met').find('i').removeClass('check circle').addClass('circle outline');
                    }
                });

                // Update Stärkebalken
                const percentage = (score / 5) * 100;
                $strengthBar.css('width', `${percentage}%`);

                if (percentage <= 20) {
                    $strengthBar.css('background-color', '#db2828'); // Rot
                } else if (percentage <= 40) {
                    $strengthBar.css('background-color', '#f2711c'); // Orange
                } else if (percentage <= 60) {
                    $strengthBar.css('background-color', '#fbbd08'); // Gelb
                } else if (percentage <= 80) {
                    $strengthBar.css('background-color', '#b5cc18'); // Olivgrün
                } else {
                    $strengthBar.css('background-color', '#21ba45'); // Grün
                }

                return score >= 3; // Mindestens 3 Anforderungen müssen erfüllt sein
            }

            // Echtzeit-Passwort-Validierung
            $passwordInput.on('input', function () {
                if (isRegistering) {
                    const isValid = validatePassword($(this).val());
                    $submitButton.prop('disabled', !isValid);
                    if (!isValid) {
                        $submitButton.addClass('disabled');
                    } else {
                        $submitButton.removeClass('disabled');
                    }
                }
            });

            $form.on('submit', function (e) {
                e.preventDefault();
                const $submitButton = $(this).find('button[type="submit"]');
                const endpoint = isRegistering ? 'register.php' : 'login2.php';

                // Debug-Ausgabe
                console.log('Form data:', $(this).serialize());

                // UI-Feedback
                $submitButton.addClass('loading disabled');

                $.ajax({
                    type: 'POST',
                    url: endpoint,
                    data: {
                        email: $('input[name="email"]').val(),
                        password: $('input[name="password"]').val(),
                        confirm_password: $('input[name="confirm_password"]').val()
                    },
                    dataType: 'json',
                    success: function (response) {
                        console.log('Success:', response);
                        if (response.success) {
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            }
                        } else {
                            showError(response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.log('Error details:', {
                            status: xhr.status,
                            responseText: xhr.responseText,
                            error: error
                        });
                        showError('Ein unerwarteter Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.');
                    },
                    complete: function () {
                        $submitButton.removeClass('loading disabled');
                    }
                });
            });
            // Hilfsfunktion für Fehleranzeige
            function showError(message) {
                $('.error-message').remove();
                $form.prepend(`
            <div class="ui negative message error-message">
                <i class="close icon"></i>
                <div class="header">Fehler</div>
                <p>${message}</p>
            </div>
        `);

                // Shake-Animation für visuelles Feedback
                $form.addClass('shake');
                setTimeout(() => $form.removeClass('shake'), 600);

                // Schließen-Button für Fehlermeldung
                $('.error-message .close').on('click', function () {
                    $(this).closest('.message').transition('fade');
                });
            }

            // Google-Login-Button Handler
            $('.google.button').on('click', function () {
                $(this).addClass('loading disabled');
                $('.ui.dimmer').css('display', 'flex');
            });

            // Autofokus auf Username-Feld
            setTimeout(function () {
                $('input[name="email"]').focus();
            }, 100);

            // Escape Taste schließt Fehlermeldungen
            $(document).keyup(function (e) {
                if (e.key === "Escape") {
                    $('.error-message').transition('fade');
                }
            });

            // Password Sichtbarkeit Toggle
            $('.toggle-password').on('click', function () {
                const $input = $(this).prev('input');
                const type = $input.attr('type') === 'password' ? 'text' : 'password';
                $input.attr('type', type);
                $(this).find('i').toggleClass('eye slash outline');
            });

            // Enter-Taste Submit
            $form.find('input').keypress(function (e) {
                if (e.which == 13) {
                    $form.submit();
                    return false;
                }
            });

            // Form Reset bei Modus-Wechsel
            function resetForm() {
                $form[0].reset();
                $('.error-message').remove();
                $submitButton.removeClass('loading disabled');
                $('.strength-bar').css('width', '0');
                $('.requirement').removeClass('met')
                    .find('i')
                    .removeClass('check circle')
                    .addClass('circle outline');
            }

            // Registrierungs-Modus Toggle mit Animation
            function toggleMode() {
                resetForm();
                $form.transition('shake');

                if (isRegistering) {
                    $('.register-fields, .password-requirements').transition('slide down');
                    $('.login-fields').transition('slide up');
                } else {
                    $('.register-fields, .password-requirements').transition('slide up');
                    $('.login-fields').transition('slide down');
                }
            }
        });
    </script>
</body>

</html>