<!-- login.php -->
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
            color: white !important;
        }
    </style>
</head>

<body>
    <div class="ui raised very padded text container segment" style='width:100%'>
        <h2 class="ui header" style="color: #21ba45;">Smart 8 Login</h2>
        <form class="ui huge form" id="loginForm">
            <div class="field">
                <label>Benutzername</label>
                <div class="ui fluid input">
                    <input type="text" value="office@ssi.at" name="username" id="username" placeholder="Benutzername"
                        autofocus>
                </div>
            </div>
            <div class="field">
                <label>Passwort</label>
                <div class="ui fluid input">
                    <input type="text" name="password" id="password" placeholder="Passwort" value='MCmaster21;'>
                </div>
            </div>
            <button class="ui fluid huge teal button" type="submit">Login</button>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#loginForm').submit(function (e) {
                e.preventDefault();

                var username = $('#username').val();
                var password = $('#password').val();

                $.ajax({
                    type: "POST",
                    url: "login2.php",
                    data: {
                        username: username,
                        password: password
                    },
                    success: function (response) {
                        if (response.trim() === "Erfolg") {
                            window.location.href = 'modules/main/index.php';
                        } else {
                            $('body').toast({
                                class: 'error',
                                message: 'Ungültiger Benutzername oder Passwort'
                            });
                        }
                    },
                    error: function () {
                        $('body').toast({
                            class: 'error',
                            message: 'Ein Fehler ist aufgetreten.'
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>