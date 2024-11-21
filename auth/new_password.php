<?php
// auth/new_password.php

require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/password_helper.php');

// Token aus URL holen
$token = $_GET['token'] ?? '';
$error = null;
$success = false;

// PasswordHelper initialisieren
$passwordHelper = new PasswordHelper($db);

// Token überprüfen
$user = $passwordHelper->validateToken($token);

if (!$user) {
    $error = "Ungültiger oder abgelaufener Link. Bitte fordern Sie einen neuen Link an.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validierung
    if ($password !== $confirmPassword) {
        $error = "Die Passwörter stimmen nicht überein.";
    } else if (strlen($password) < 8) {
        $error = "Das Passwort muss mindestens 8 Zeichen lang sein.";
    } else {
        // Passwort aktualisieren
        if ($passwordHelper->updatePassword($user['user_id'], $password)) {
            $success = true;
        } else {
            $error = "Fehler beim Aktualisieren des Passworts.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neues Passwort setzen - Smart System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }

        .ui.segment {
            max-width: 450px;
            width: 100%;
            margin: 20px;
        }

        .ui.teal.header,
        .ui.teal.button {
            background-color: #21ba45 !important;
            color: white !important;
        }

        .password-requirements {
            margin-top: 1em;
            font-size: 0.9em;
            color: #666;
        }

        .requirement {
            margin: 0.3em 0;
        }

        .requirement.met {
            color: #21ba45;
        }

        .requirement i {
            margin-right: 0.5em;
        }

        #passwordStrength {
            height: 5px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }

        .strength-text {
            font-size: 0.9em;
            margin-top: 0.3em;
        }
    </style>
</head>

<body>
    <div class="ui raised very padded text container segment">
        <h2 class="ui header" style="color: #21ba45;">
            <i class="key icon"></i>
            <div class="content">
                Neues Passwort setzen
                <?php if (!$error && !$success && $user): ?>
                    <div class="sub header">Für Account: <?php echo htmlspecialchars($user['user_name']); ?></div>
                <?php endif; ?>
            </div>
        </h2>

        <?php if ($error): ?>
            <div class="ui negative message">
                <i class="close icon"></i>
                <div class="header">Fehler</div>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
            <div class="ui center aligned container" style="margin-top: 2em;">
                <a href="auth/login.php" class="ui button">
                    <i class="arrow left icon"></i>
                    Zurück zum Login
                </a>
            </div>
        <?php elseif ($success): ?>
            <div class="ui positive message">
                <i class="check circle icon"></i>
                <div class="header">Passwort aktualisiert</div>
                <p>Ihr Passwort wurde erfolgreich geändert. Sie können sich jetzt mit Ihrem neuen Passwort anmelden.</p>
            </div>
            <div class="ui center aligned container">
                <a href="../login.php" class="ui teal button">
                    <i class="sign-in icon"></i>
                    Zum Login
                </a>
            </div>
        <?php elseif ($user): ?>
            <form class="ui large form" id="newPasswordForm" method="post">
                <div class="field">
                    <label>Neues Passwort</label>
                    <div class="ui input">
                        <input type="password" name="password" id="password" required minlength="8">
                    </div>
                    <div id="passwordStrength" class="ui progress">
                        <div class="bar"></div>
                    </div>
                    <div class="strength-text"></div>
                </div>

                <div class="field">
                    <label>Passwort bestätigen</label>
                    <div class="ui input">
                        <input type="password" name="confirm_password" id="confirmPassword" required minlength="8">
                    </div>
                </div>

                <div class="password-requirements">
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
                    <div class="requirement" data-requirement="match">
                        <i class="circle outline icon"></i>
                        Passwörter stimmen überein
                    </div>
                </div>

                <button class="ui fluid large teal submit button" type="submit" id="submitButton" disabled>
                    <i class="save icon"></i>
                    Passwort speichern
                </button>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.js"></script>
    <script>
        $(document).ready(function () {
            const $password = $('#password');
            const $confirmPassword = $('#confirmPassword');
            const $submitButton = $('#submitButton');
            const $requirements = $('.requirement');
            const $strengthBar = $('#passwordStrength .bar');
            const $strengthText = $('.strength-text');

            function checkPasswordStrength(password) {
                let strength = 0;
                const requirements = {
                    length: password.length >= 8,
                    lowercase: /[a-z]/.test(password),
                    uppercase: /[A-Z]/.test(password),
                    number: /[0-9]/.test(password),
                    special: /[^A-Za-z0-9]/.test(password)
                };

                // Update requirement indicators
                Object.keys(requirements).forEach(req => {
                    const $req = $(`.requirement[data-requirement="${req}"]`);
                    if (requirements[req]) {
                        $req.addClass('met').find('i').removeClass('circle outline').addClass('check circle');
                        strength++;
                    } else {
                        $req.removeClass('met').find('i').removeClass('check circle').addClass('circle outline');
                    }
                });

                // Check if passwords match
                const match = password === $confirmPassword.val() && password.length > 0;
                const $matchReq = $('.requirement[data-requirement="match"]');
                if (match) {
                    $matchReq.addClass('met').find('i').removeClass('circle outline').addClass('check circle');
                    strength++;
                } else {
                    $matchReq.removeClass('met').find('i').removeClass('check circle').addClass('circle outline');
                }

                // Update strength indicator
                const percentage = (strength / 6) * 100;
                $strengthBar.css('width', `${percentage}%`);

                if (percentage <= 33) {
                    $strengthBar.css('background-color', '#DB2828');
                    $strengthText.text('Schwach').css('color', '#DB2828');
                } else if (percentage <= 66) {
                    $strengthBar.css('background-color', '#FBBD08');
                    $strengthText.text('Mittel').css('color', '#FBBD08');
                } else {
                    $strengthBar.css('background-color', '#21BA45');
                    $strengthText.text('Stark').css('color', '#21BA45');
                }

                // Enable/disable submit button
                $submitButton.prop('disabled', strength < 6);
                if (strength < 6) {
                    $submitButton.addClass('disabled');
                } else {
                    $submitButton.removeClass('disabled');
                }
            }

            $password.on('input', function () {
                checkPasswordStrength($(this).val());
            });

            $confirmPassword.on('input', function () {
                checkPasswordStrength($password.val());
            });

            // Initialize messages
            $('.message .close').on('click', function () {
                $(this).closest('.message').transition('fade');
            });

            // Form validation
            $('#newPasswordForm').on('submit', function (e) {
                const password = $password.val();
                const confirmPassword = $confirmPassword.val();

                if (password !== confirmPassword) {
                    e.preventDefault();
                    $('.ui.message').remove();
                    $(this).prepend(`
                        <div class="ui negative message">
                            <i class="close icon"></i>
                            <div class="header">Fehler</div>
                            <p>Die Passwörter stimmen nicht überein.</p>
                        </div>
                    `);
                    return false;
                }

                if (password.length < 8) {
                    e.preventDefault();
                    $('.ui.message').remove();
                    $(this).prepend(`
                        <div class="ui negative message">
                            <i class="close icon"></i>
                            <div class="header">Fehler</div>
                            <p>Das Passwort muss mindestens 8 Zeichen lang sein.</p>
                        </div>
                    `);
                    return false;
                }
            });
        });
    </script>
</body>

</html>