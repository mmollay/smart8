<?php
require_once(__DIR__ . '/../n_config.php');
$importConfig = require(__DIR__ . '/../config/import_export_config.php');
$groups = getAllGroups($db);

// Beispielwerte-Funktion hinzufügen
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

// Beispieldaten generieren
$headers = implode(',', array_keys($importConfig['default_columns']));
$exampleRows = [
    ['max@example.com', 'Max', 'Mustermann', 'male', 'Dr.', 'Firma GmbH', 'Ein Kommentar'],
    ['anna.schmidt@technik.com', 'Anna', 'Schmidt', 'female', 'Prof.', 'Technik AG', 'Senior Entwicklerin'],
];
$exampleData = $headers . "\n" . implode("\n", array_map(fn($row) => implode(',', array_map(fn($cell) =>
    strpos($cell, ' ') !== false ? '"' . $cell . '"' : $cell, $row)), $exampleRows));
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
        <form class="ui form" id="importForm">
            <!-- Tabs -->
            <div class="ui top attached tabular menu">
                <a class="item active" data-tab="text"><i class="edit icon"></i> Direkte Eingabe</a>
                <a class="item" data-tab="file"><i class="file icon"></i> Datei-Upload</a>
                <div class="right menu">
                    <a class="item" onclick="$('#helpModal').modal('show')">
                        <i class="question circle icon"></i> Hilfe
                    </a>
                </div>
            </div>

            <!-- File Upload -->
            <div class="ui bottom attached tab segment" data-tab="file">
                <div class="field">
                    <label>CSV/TXT Datei auswählen</label>
                    <div class="ui action input">
                        <input type="file" name="importFile" accept=".csv,.txt" id="fileInput" style="display: none;">
                        <input type="text" readonly placeholder="Keine Datei ausgewählt" id="fileLabel">
                        <button type="button" class="ui primary labeled icon button" onclick="$('#fileInput').click();">
                            <i class="file icon"></i>Durchsuchen
                        </button>
                    </div>
                </div>
            </div>

            <!-- Text Input -->
            <div class="ui bottom attached tab segment active" data-tab="text">
                <div class="field">
                    <label>Empfängerdaten direkt eingeben</label>
                    <textarea name="importText" id="textInput" rows="10"
                        data-example="<?= htmlspecialchars($exampleData) ?>"
                        placeholder="<?= htmlspecialchars($headers) ?>">
</textarea>
                    <div class="ui tiny buttons" style="margin-top: 5px;">
                        <button type="button" class="ui button" id="insertExample"><i class="paste icon"></i>
                            Beispiel</button>
                        <button type="button" class="ui button" id="clearText"><i class="eraser icon"></i>
                            Löschen</button>
                        <button type="button" class="ui button" id="validateFormat"><i class="check icon"></i>
                            Prüfen</button>
                    </div>
                </div>
            </div>

            <!-- Import Options -->
            <div class="ui secondary segment">
                <!-- Gruppen-Auswahl -->
                <div class="field">
                    <label>Gruppen auswählen</label>
                    <div class="ui grid">
                        <div class="fourteen wide column">
                            <div class="ui fluid multiple search selection dropdown">
                                <input type="hidden" name="group_ids[]">
                                <i class="dropdown icon"></i>
                                <div class="default text">Gruppen wählen...</div>
                                <div class="menu">
                                    <?php foreach ($groups as $id => $html): ?>
                                        <div class="item" data-value="<?= $id ?>"><?= $html ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="two wide column">
                            <button type="button" class="ui icon button primary" id="addGroupButton">
                                <i class="plus icon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Import-Optionen -->
                <div class="two fields">
                    <div class="field">
                        <div class="ui checkbox">
                            <input type="checkbox" name="skipHeader" checked>
                            <label>Erste Zeile überspringen</label>
                        </div>
                    </div>
                    <div class="field">
                        <label>Trennzeichen</label>
                        <select class="ui dropdown" name="delimiter">
                            <option value="\t">Tab</option>
                            <option value=",">Komma (,)</option>
                            <option value=";">Semikolon (;)</option>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" name="overwriteExisting">
                        <label>Bestehende Empfänger aktualisieren</label>
                    </div>
                </div>

                <!-- <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" name="autoGender">
                        <label>Geschlecht automatisch zuweisen</label>
                    </div>
                </div> -->



            </div>

            <div id="loadingIndicator" class="ui dimmer" style="display:none;">
                <div class="content">
                    <div class="ui active inline loader"></div>
                    <div class="ui text" style="margin-top: 1em;">
                        Importiere Daten...
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <button class="ui primary button" type="submit">
                <i class="upload icon"></i> Import starten
            </button>
            <button class="ui button" type="reset">
                <i class="undo icon"></i> Zurücksetzen
            </button>
        </form>

        <!-- Fehlermeldungen -->
        <div id="errorList" class="ui error message" style="display:none;">
            <div class="header">Import-Fehler</div>
            <ul class="list"></ul>
        </div>
    </div>
</div>
<!-- Nach dem bestehenden HTML, vor dem schließenden </div> -->

<!-- Help Modal -->
<div class="ui modal" id="helpModal">
    <i class="close icon"></i>
    <div class="header">
        <i class="question circle icon"></i> Import-Hilfe
    </div>
    <div class="content">
        <div class="ui info message">
            <div class="header">Wichtige Hinweise</div>
            <ul class="list">
                <li>Dateien müssen im CSV-Format vorliegen</li>
                <li>Kodierung sollte UTF-8 sein</li>
                <li>E-Mail ist ein Pflichtfeld</li>
                <li>Felder mit Kommas müssen in Anführungszeichen gesetzt werden</li>
            </ul>
        </div>

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
                        <td><?= htmlspecialchars($field) ?></td>
                        <td><?= htmlspecialchars($label) ?></td>
                        <td class="center aligned">
                            <?php if (in_array($field, $importConfig['required_fields'])): ?>
                                <i class="green checkmark icon"></i>
                            <?php else: ?>
                                <i class="grey minus icon"></i>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars(getExampleValue($field)) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="actions">
        <div class="ui positive button">Verstanden</div>
    </div>
</div>

<!-- Am Ende der Datei vor dem Script -->
<div class="ui tiny modal" id="addGroupModal">
    <div class="header">Neue Gruppe erstellen</div>
    <div class="content">
        <div class="ui form">
            <div class="field">
                <label>Gruppenname</label>
                <input type="text" name="name" placeholder="Name der neuen Gruppe">
            </div>
            <div class="field">
                <label>Beschreibung</label>
                <input type="text" name="description" placeholder="Optionale Beschreibung">
            </div>
            <div class="field">
                <label>Farbe</label>
                <div class="ui selection dropdown" id="colorDropdown">
                    <input type="hidden" name="color">
                    <i class="dropdown icon"></i>
                    <div class="default text">Farbe wählen</div>
                    <div class="menu">
                        <div class="item" data-value="red">
                            <div class="ui red empty circular label"></div>Rot
                        </div>
                        <div class="item" data-value="orange">
                            <div class="ui orange empty circular label"></div>Orange
                        </div>
                        <div class="item" data-value="yellow">
                            <div class="ui yellow empty circular label"></div>Gelb
                        </div>
                        <div class="item" data-value="olive">
                            <div class="ui olive empty circular label"></div>Olive
                        </div>
                        <div class="item" data-value="green">
                            <div class="ui green empty circular label"></div>Grün
                        </div>
                        <div class="item" data-value="teal">
                            <div class="ui teal empty circular label"></div>Türkis
                        </div>
                        <div class="item" data-value="blue">
                            <div class="ui blue empty circular label"></div>Blau
                        </div>
                        <div class="item" data-value="violet">
                            <div class="ui violet empty circular label"></div>Violett
                        </div>
                        <div class="item" data-value="purple">
                            <div class="ui purple empty circular label"></div>Lila
                        </div>
                        <div class="item" data-value="pink">
                            <div class="ui pink empty circular label"></div>Pink
                        </div>
                        <div class="item" data-value="brown">
                            <div class="ui brown empty circular label"></div>Braun
                        </div>
                        <div class="item" data-value="grey">
                            <div class="ui grey empty circular label"></div>Grau
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="actions">
        <div class="ui cancel button">Abbrechen</div>
        <div class="ui positive button">Erstellen</div>
    </div>
</div>

<script src="js/import.js"></script>