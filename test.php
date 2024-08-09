<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flexibles seitliches Klappmenü</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.8.8/semantic.min.css">
    <style>
        #toggle-button {
            background-color: #2185d0;
            color: white;
            border-radius: 0 5px 5px 0;
            width: 25px;
            text-align: center;
            position: absolute;
            right: -30px;
            top: 60px;
            padding: 10px 0;
            cursor: pointer;
            font-size: 16px;
        }

        #side-menu {
            background: #f4f4f4;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            width: 200px;
            height: 100vh;
            position: fixed;
            left: -180px;
            top: 0;
            transition: left 0.5s;
        }

        #side-menu .item:hover {
            background: #e0e0e0;
        }

        #main-content {
            margin-left: 20px;
            padding: 20px;
            transition: margin-left 0.5s;
        }
    </style>
</head>

<body>
    <div class="ui vertical menu" id="side-menu">
        <button class="ui button" id="toggle-button">&#9776;</button>
        <a class="item">Link 1</a>
        <a class="item">Link 2</a>
        <a class="item">Link 3</a>
    </div>

    <div id="main-content">
        <!-- Hauptinhalt der Seite -->
        Hauptinhalt der Seite hier...
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.8.8/semantic.min.js"></script>
    <script>
        $(document).ready(function () {
            var menuVisible = false;
            $('#toggle-button').click(function () {
                if (menuVisible) {
                    $('#side-menu').css('left', '-180px');
                    $('#main-content').css('margin-left', '20px');
                } else {
                    $('#side-menu').css('left', '0');
                    $('#main-content').css('margin-left', '220px');
                }
                menuVisible = !menuVisible;
            });
        });
    </script>
</body>

</html>