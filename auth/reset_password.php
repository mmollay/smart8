<?php
// auth/reset_password.php

require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../services/MailService.php');
require_once(__DIR__ . '/password_helper.php');

use Smart\Services\MailService;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Überprüfen ob der Benutzer existiert
    $stmt = $db->prepare("SELECT user_id, firstname, secondname FROM user2company WHERE user_name = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();
        $passwordHelper = new PasswordHelper($db);

        // Token erstellen
        if ($token = $passwordHelper->createResetToken($user['user_id'])) {
            // Reset-Link erstellen
            $resetLink = "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'])
                . "/new_password.php?token=" . $token;

            // E-Mail-Inhalt
            $emailContent = "
                <h2>Passwort zurücksetzen</h2>
                <p>Sehr geehrte(r) {$user['firstname']} {$user['secondname']},</p>
                <p>Sie haben eine Anfrage zum Zurücksetzen Ihres Passworts gestellt.</p>
                <p>Klicken Sie auf den folgenden Link, um ein neues Passwort zu erstellen:</p>
                <p><a href='{$resetLink}'>{$resetLink}</a></p>
                <p>Dieser Link ist 60 Minuten gültig.</p>
                <p>Falls Sie keine Passwort-Zurücksetzung angefordert haben, ignorieren Sie diese E-Mail.</p>
                <br>
                <p>Mit freundlichen Grüßen</p>
                <p>Ihr Smart-Team</p>
            ";

            // E-Mail senden
            $mailService = MailService::getInstance();
            $mailResult = $mailService->sendMail($email, 'Passwort zurücksetzen', $emailContent);
        }
    }

    // Aus Sicherheitsgründen immer die gleiche Erfolgsmeldung anzeigen
    echo json_encode([
        'success' => true,
        'message' => 'Falls ein Konto mit dieser E-Mail-Adresse existiert, wurde eine E-Mail mit Anweisungen zum Zurücksetzen des Passworts gesendet.'
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passwort zurücksetzen - Smart System</title>
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

        .ui.form .field {
            margin-bottom: 1.5em;
        }

        .message-box {
            display: none;
            margin-top: 1em;
        }

        .back-to-login {
            margin-top: 1em;
            text-align: center;
        }

        #loading {
            display: none;
            margin-top: 1em;
        }
    </style>
</head>

<body>
    <div class="ui raised very padded text container segment">
        <h2 class="ui header" style="color: #21ba45;">
            <i class="lock icon"></i>
            <div class="content">
                Passwort zurücksetzen
                <div class="sub header">Geben Sie Ihre E-Mail-Adresse ein, um Ihr Passwort zurückzusetzen</div>
            </div>
        </h2>

        <!-- Formular -->
        <form class="ui large form" id="resetForm">
            <div class="field">
                <label>E-Mail-Adresse</label>
                <div class="ui input">
                    <input type="email" name="email" placeholder="Ihre E-Mail-Adresse" required
                        pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                </div>
            </div>

            <!-- Loading Indicator -->
            <div class="ui active centered inline loader" id="loading"></div>

            <!-- Buttons -->
            <button class="ui fluid large teal submit button" type="submit">
                <i class="paper plane outline icon"></i>
                Zurücksetzen-Link anfordern
            </button>

            <!-- Message Box -->
            <div class="ui message message-box">
                <i class="close icon"></i>
                <div class="content"></div>
            </div>

            <!-- Back to Login Link -->
            <div class="back-to-login">
                <a href="auth/login.php" class="ui basic button">
                    <i class="arrow left icon"></i>
                    Zurück zum Login
                </a>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize UI components
            $('.message .close').on('click', function () {
                $(this).closest('.message').transition('fade');
            });

            $('#resetForm').on('submit', function (e) {
                e.preventDefault();
                const $form = $(this);
                const $submitButton = $form.find('button[type="submit"]');
                const $loading = $('#loading');
                const $messageBox = $('.message-box');

                // Disable button and show loading
                $submitButton.addClass('loading disabled');
                $loading.show();
                $messageBox.hide();

                $.ajax({
                    type: 'POST',
                    url: 'reset_password.php',
                    data: $form.serialize(),
                    dataType: 'json',
                    success: function (response) {
                        $messageBox
                            .removeClass('negative')
                            .addClass('positive')
                            .find('.content')
                            .html('<i class="check circle icon"></i> ' + response.message);

                        // Reset form
                        $form[0].reset();
                    },
                    error: function () {
                        $messageBox
                            .removeClass('positive')
                            .addClass('negative')
                            .find('.content')
                            .html('<i class="exclamation triangle icon"></i> Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.');
                    },
                    complete: function () {
                        // Re-enable button and hide loading
                        $submitButton.removeClass('loading disabled');
                        $loading.hide();
                        $messageBox.show();
                    }
                });
            });
        });
    </script>
</body>

</html>