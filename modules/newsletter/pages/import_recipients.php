<?php
require_once(__DIR__ . '/../n_config.php');
$importConfig = require(__DIR__ . '/../config/import_export_config.php');

// Hole alle verfügbaren Gruppen für das Dropdown
$groups = getAllGroups($db);

// Beispieldaten basierend auf der Konfiguration generieren
$headers = implode(',', array_keys($importConfig['default_columns']));
$exampleRows = [
    ['max@example.com', 'Max', 'Mustermann', 'male', 'Dr.', 'Firma GmbH', 'Ein Kommentar'],
    ['anna.schmidt@technik.com', 'Anna', 'Schmidt', 'female', 'Prof.', 'Technik AG', 'Senior Entwicklerin'],
    ['t.weber@consulting.net', 'Thomas', 'Weber', 'male', 'Dipl.-Ing.', 'Consulting Partners', 'Projektleiter']
];
$exampleData = $headers . "\n" . implode("\n", array_map(function ($row) {
    return implode(',', array_map(function ($cell) {
        return strpos($cell, ' ') !== false ? '"' . $cell . '"' : $cell;
    }, $row));
}, $exampleRows));

?>
<div class="ui container">
    <div class="ui attached message">
        <h2 class="ui small header">
            <i class="users icon"></i>
            <div class="content">
                Empfänger importieren
                <div class="sub header">Importieren Sie Empfänger per Datei-Upload oder direkter Eingabe</div>
            </div>
        </h2>
    </div>

    <div class="ui form attached fluid segment">
        <form class="ui form" id="importForm" method="post" enctype="multipart/form-data">
            <!-- Tab Menu -->
            <div class="ui top attached tabular menu">
                <a class="item active" data-tab="text">
                    <i class="edit icon"></i> Direkte Eingabe
                </a>
                <a class="item" data-tab="file">
                    <i class="file icon"></i> Datei-Upload
                </a>

                <div class="right menu">
                    <a class="item" onclick="$('#helpModal').modal('show')">
                        <i class="question circle icon"></i> Hilfe
                    </a>
                </div>
            </div>

            <!-- File Upload Tab -->
            <div class="ui bottom attached tab segment" data-tab="file">
                <div class="field">
                    <label>CSV/TXT Datei auswählen</label>
                    <div class="ui action input">
                        <input type="file" name="importFile" accept=".csv,.txt" id="fileInput" style="display: none;">
                        <input type="text" readonly placeholder="Keine Datei ausgewählt" id="fileLabel"
                            onclick="$('#fileInput').click();">
                        <div class="ui primary labeled icon button" onclick="$('#fileInput').click();">
                            <i class="file icon"></i>
                            Durchsuchen
                        </div>
                    </div>
                    <div class="ui info message">
                        <div class="header">Unterstützte Formate</div>
                        <ul class="list">
                            <li>CSV-Dateien (.csv)</li>
                            <li>Text-Dateien (.txt)</li>
                            <li>UTF-8 Kodierung</li>
                            <li>Maximale Dateigröße: 5MB</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Text Input Tab -->
            <div class="ui bottom attached tab segment active" data-tab="text">
                <div class="field">
                    <label>Empfängerdaten direkt eingeben</label>
                    <textarea name="importText" id="textInput" rows="10"
                        style="font-family: monospace; min-height: 200px;"
                        placeholder="<?php echo htmlspecialchars($headers); ?>"></textarea>
                    <div class="ui tiny buttons" style="margin-top: 5px;">
                        <button type="button" class="ui labeled icon button" id="insertExample">
                            <i class="paste icon"></i> Beispieldaten
                        </button>
                        <button type="button" class="ui labeled icon button" id="clearText">
                            <i class="eraser icon"></i> Löschen
                        </button>
                        <button type="button" class="ui labeled icon button" id="validateFormat">
                            <i class="check icon"></i> Format prüfen
                        </button>
                    </div>
                </div>
            </div>

            <!-- Import Options -->
            <div class="ui secondary segment">
                <div class="field">
                    <label>
                        Gruppen auswählen
                        <button type="button" class="ui mini primary icon button" style="margin-left: 1em;"
                            onclick="$('#newGroupModal').modal('show')">
                            <i class="plus icon"></i> Neue Gruppe
                        </button>
                    </label>
                    <div class="ui fluid multiple search selection dropdown">
                        <input type="hidden" name="group_ids[]">
                        <i class="dropdown icon"></i>
                        <div class="default text">Gruppen wählen...</div>
                        <div class="menu" id="groupsDropdownMenu">
                            <?php foreach ($groups as $groupId => $groupHtml): ?>
                                <div class="item" data-value="<?= htmlspecialchars($groupId) ?>">
                                    <?= $groupHtml ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="two fields">
                    <div class="field">
                        <div class="ui checkbox">
                            <input type="checkbox" name="skipHeader" checked>
                            <label>Erste Zeile überspringen (Spaltenüberschriften)</label>
                        </div>
                    </div>
                    <div class="field">
                        <label>Trennzeichen</label>
                        <select class="ui dropdown" name="delimiter">
                            <option value=",">Komma (,)</option>
                            <option value=";">Semikolon (;)</option>
                            <option value="\t">Tab</option>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" name="overwriteExisting">
                        <label>Bestehende Empfänger aktualisieren</label>
                    </div>
                </div>
            </div>

            <button class="ui primary button" type="submit">
                <i class="upload icon"></i> Import starten
            </button>
            <button class="ui button" type="reset">
                <i class="undo icon"></i> Zurücksetzen
            </button>
        </form>

        <!-- Results Message -->
        <div id="importResults" style="display:none;" class="ui message">
            <i class="close icon"></i>
            <div class="header"></div>
            <div class="content"></div>
        </div>

        <!-- Progress Bar -->
        <div class="ui progress" id="importProgress" style="display:none;">
            <div class="bar">
                <div class="progress"></div>
            </div>
            <div class="label">Importiere...</div>
        </div>
    </div>
</div>

<!-- Help Modal -->
<div class="ui modal" id="helpModal">
    <i class="close icon"></i>
    <div class="header">
        <i class="question circle icon"></i> Import-Hilfe
    </div>
    <div class="content">
        <table class="ui celled table">
            <thead>
                <tr>
                    <th>Spalte</th>
                    <th>Beschreibung</th>
                    <th>Pflicht</th>
                    <th>Beispiel</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($importConfig['default_columns'] as $field => $label): ?>
                    <tr>
                        <td><?php echo $field; ?></td>
                        <td><?php echo $label; ?></td>
                        <td>
                            <?php if (in_array($field, $importConfig['required_fields'])): ?>
                                <i class="green checkmark icon"></i>
                            <?php else: ?>
                                <i class="red remove icon"></i>
                            <?php endif; ?>
                        </td>
                        <td><?php echo getExampleValue($field); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
function getExampleValue($field)
{
    $examples = [
        'email' => 'max@example.com',
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'company' => 'Firma GmbH',
        'gender' => 'male',
        'title' => 'Dr.',
        'comment' => 'Ein Kommentar'
    ];
    return $examples[$field] ?? '';
}
?>

<script>
    $(document).ready(function () {
        // Initialisierungen
        $('.menu .item').tab();
        $('.ui.dropdown').dropdown();
        $('.ui.checkbox').checkbox();
        $('.ui.modal').modal();

        // Konstanten
        const requiredFields = <?php echo json_encode($importConfig['required_fields']); ?>;
        const exampleData = <?php echo json_encode($exampleData); ?>;

        // File Input Handler
        $('#fileInput').on('change', function () {
            const fileName = this.files[0]?.name || 'Keine Datei ausgewählt';
            $('#fileLabel').val(fileName);

            if (this.files[0] && this.files[0].size > 5 * 1024 * 1024) {
                showMessage('error', 'Fehler', 'Die Datei ist zu groß (Maximum: 5MB)');
                this.value = '';
                $('#fileLabel').val('Keine Datei ausgewählt');
            }
        });

        // Button Handler
        $('#insertExample').click(() => $('#textInput').val(exampleData));
        $('#clearText').click(() => $('#textInput').val(''));

        $('#validateFormat').click(function () {
            const text = $('#textInput').val().trim();
            if (!text) {
                showMessage('warning', 'Warnung', 'Bitte geben Sie zuerst Daten ein.');
                return;
            }

            try {
                const lines = text.split('\n');
                const headers = lines[0].toLowerCase().split(',').map(h => h.trim());

                const missing = requiredFields.filter(field => !headers.includes(field));
                if (missing.length > 0) {
                    showMessage('error', 'Fehler', `Fehlende Pflichtfelder: ${missing.join(', ')}`);
                    return;
                }

                let errors = [];
                const emailIdx = headers.indexOf('email');

                lines.slice(1).forEach((line, idx) => {
                    const fields = parseCsvLine(line);
                    const email = fields[emailIdx]?.trim();
                    if (!email || !isValidEmail(email)) {
                        errors.push(`Zeile ${idx + 2}: Ungültige E-Mail-Adresse (${email || 'leer'})`);
                    }
                });

                if (errors.length > 0) {
                    showMessage('warning', 'Warnung', 'Fehler gefunden:<br>' + errors.join('<br>'));
                } else {
                    showMessage('success', 'Erfolg', 'Format ist korrekt!');
                }
            } catch (e) {
                showMessage('error', 'Fehler', 'Fehler beim Validieren des Formats');
                console.error(e);
            }
        });

        // Form Submit Handler
        $('#importForm').on('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const activeTab = $('.tab.active').data('tab');

            if (activeTab === 'text') {
                const text = $('#textInput').val().trim();
                if (!text) {
                    showMessage('warning', 'Warnung', 'Bitte geben Sie Daten ein.');
                    return;
                }
                formData.set('importFile', new Blob([text], { type: 'text/csv' }), 'import.csv');
            } else if (!$('#fileInput').val()) {
                showMessage('warning', 'Warnung', 'Bitte wählen Sie eine Datei aus.');
                return;
            }

            const $progress = $('#importProgress').show().progress({
                total: 100,
                text: {
                    active: 'Importiere: {percent}%',
                    success: 'Import abgeschlossen!'
                }
            });

            const $buttons = $('#importForm button').addClass('disabled');

            $.ajax({
                url: 'ajax/process_import.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function () {
                    const xhr = new XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            $progress.progress('set progress', Math.round((e.loaded / e.total) * 100));
                        }
                    });
                    return xhr;
                },
                success: function (response) {
                    $buttons.removeClass('disabled');
                    $progress.progress('complete');

                    if (response.success) {
                        let message = `Neue Datensätze: ${response.imported}<br>`;
                        if (response.updated > 0) {
                            message += `Aktualisierte Datensätze: ${response.updated}<br>`;
                        }
                        message += `Übersprungene Datensätze: ${response.skipped}`;

                        if (response.errors?.length > 0) {
                            message += '<br><br>Warnungen:<ul><li>' +
                                response.errors.join('</li><li>') +
                                '</li></ul>';
                        }

                        showMessage('success', 'Import erfolgreich', message);

                        if (typeof reloadTable === 'function') {
                            reloadTable();
                        }
                    } else {
                        showMessage('error', 'Fehler', response.message || 'Ein unbekannter Fehler ist aufgetreten');
                    }
                },
                error: function (xhr) {
                    $buttons.removeClass('disabled');
                    $progress.hide();
                    showMessage('error', 'Fehler', 'Ein Serverfehler ist aufgetreten');
                    console.error(xhr);
                }
            });
        });

        // Helper Functions
        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        function parseCsvLine(line) {
            const result = [];
            let current = '';
            let inQuotes = false;

            for (let char of line) {
                if (char === '"') {
                    inQuotes = !inQuotes;
                    continue;
                }
                if (char === ',' && !inQuotes) {
                    result.push(current);
                    current = '';
                    continue;
                }
                current += char;
            }
            result.push(current);
            return result;
        }

        function showMessage(type, title, message) {
            $('#importResults')
                .removeClass('success error warning')
                .addClass(type)
                .show()
                .find('.header').text(title)
                .siblings('.content').html(message);
        }

        // Form Reset Handler
        $('#importForm').on('reset', function () {
            $('#importResults').hide();
            $('#fileInput').val('');
            $('#fileLabel').val('Keine Datei ausgewählt');
            $('#textInput').val('');
            $('.ui.dropdown').dropdown('clear');
            $('input[name="skipHeader"]').prop('checked', true).trigger('change');
            $('input[name="overwriteExisting"]').prop('checked', false).trigger('change');
            $('#importProgress').hide().progress('reset');
        });

        // Close button für Messages
        $('.message .close').on('click', function () {
            $(this).closest('.message').hide();
        });
    });
</script>

<style>
    #fileLabel {
        cursor: pointer !important;
        background-color: #fff !important;
    }

    #fileLabel:hover {
        background-color: #f9f9f9 !important;
    }

    #fileLabel.hover {
        background-color: #f5f5f5 !important;
    }

    .ui.action.input .button {
        display: flex;
        align-items: center;
    }

    .ui.selection.dropdown .menu>.item {
        display: flex;
        align-items: center;
        padding: 0.5em 1em !important;
    }

    .ui.selection.dropdown .menu>.item i.icon {
        margin: 0 0.5em 0 0;
        font-size: 1em;
    }

    .field>label>.button {
        padding: 0.5em !important;
        font-size: 0.8em !important;
    }
</style>