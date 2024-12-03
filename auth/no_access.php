<?php
// auth/no_access.php
require_once(__DIR__ . '/../config.php');
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Zugriff verweigert</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.css">
</head>

<body>
    <div class="ui container" style="padding-top: 50px;">
        <div class="ui negative message">
            <div class="header">
                Zugriff verweigert
            </div>
            <p>Sie haben keine Berechtigung, auf dieses Modul zuzugreifen. Bitte wenden Sie sich an Ihren Administrator.
            </p>
        </div>
        <a href="<?= WEB_ROOT ?>/modules/main/" class="ui button">
            Zurück zur Hauptseite
        </a>
    </div>
</body>

</html>