<?php

//json wert ausgeben
//echo json_encode($_POST);

// Sicherstellen, dass die Anfrage per POST erfolgt
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    exit("Direkter Zugriff nicht erlaubt.");
}

// Funktion zur Bereinigung der Eingabedaten
function sanitizeInput($data)
{
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Sammeln und Bereinigen der Formulardaten
$formData = array_map('sanitizeInput', $_POST);


// HTML für die Ausgabeseite
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formular Übersicht</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.css">
    <style>
        .color-preview {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 10px;
            vertical-align: middle;
            border: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <div class="ui container" style="padding-top: 50px;">
        <h1 class="ui header">Formular Übersicht</h1>
        <table class="ui celled table">
            <thead>
                <tr>
                    <th>Feld</th>
                    <th>Wert</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($formData as $field => $value): ?>
                    <tr>
                        <td><?= ucfirst(str_replace('_', ' ', $field)) ?></td>
                        <td>
                            <?php if ($field === 'favorite_color'): ?>
                                <span class="color-preview" style="background-color: <?= $value ?>"></span>
                            <?php endif; ?>
                            <?php
                            if (is_array($value)) {
                                echo implode(', ', $value);
                            } else {
                                echo $value;
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="javascript:history.back()" class="ui button">Zurück zum Formular</a>
    </div>
</body>

</html>