<?php
if (!isset($_POST) || empty($_POST)) {
    header('Location: formular.php');
    exit;
}

function formatValue($value)
{
    if (is_array($value)) {
        return implode(', ', array_map('htmlspecialchars', $value));
    } elseif (is_bool($value)) {
        return $value ? 'Ja' : 'Nein';
    } else {
        return htmlspecialchars($value);
    }
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formular-Ergebnis</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.css">
    <style>
        .ui.container {
            padding: 2em 0;
        }

        .data-section {
            margin-bottom: 2em;
        }

        .timestamp {
            color: #666;
            font-size: 0.9em;
            margin-top: 1em;
        }

        .parameter-table {
            margin-top: 2em !important;
        }

        .long-content {
            max-height: 100px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-word;
        }
    </style>
</head>

<body>
    <div class="ui container">
        <div class="ui segment">
            <h2 class="ui header">
                <i class="check circle icon green"></i>
                <div class="content">
                    Formular erfolgreich übermittelt
                    <div class="sub header">Übersicht aller übermittelten Parameter</div>
                </div>
            </h2>

            <div class="ui divider"></div>

            <!-- Parameter Table -->
            <table class="ui celled striped table parameter-table">
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Wert</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_POST as $key => $value): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($key) ?></strong>
                            </td>
                            <td>
                                <?php
                                $formattedValue = formatValue($value);
                                if (strlen($formattedValue) > 100) {
                                    echo '<div class="long-content">' . $formattedValue . '</div>';
                                } else {
                                    echo $formattedValue;
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (!empty($_FILES)): ?>
                <h3 class="ui header">Hochgeladene Dateien</h3>
                <table class="ui celled striped table">
                    <thead>
                        <tr>
                            <th>Dateiname</th>
                            <th>Typ</th>
                            <th>Größe</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_FILES as $input => $fileInfo): ?>
                            <?php if (is_array($fileInfo['name'])): ?>
                                <?php foreach ($fileInfo['name'] as $key => $filename): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($filename) ?></td>
                                        <td><?= htmlspecialchars($fileInfo['type'][$key]) ?></td>
                                        <td><?= number_format($fileInfo['size'][$key] / 1024, 2) ?> KB</td>
                                        <td>
                                            <?php if ($fileInfo['error'][$key] === 0): ?>
                                                <i class="check circle icon green"></i> Erfolgreich
                                            <?php else: ?>
                                                <i class="times circle icon red"></i> Fehler
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="ui divider"></div>

            <!-- Action Buttons -->
            <div class="ui buttons">
                <a href="formular.php" class="ui labeled icon button">
                    <i class="left arrow icon"></i>
                    Zurück zum Formular
                </a>
                <a href="index.php" class="ui right labeled icon button primary">
                    <i class="home icon"></i>
                    Zur Startseite
                </a>
            </div>

            <!-- Timestamp -->
            <div class="timestamp">
                Übermittelt am: <?= date('d.m.Y H:i:s') ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.js"></script>
</body>

</html>