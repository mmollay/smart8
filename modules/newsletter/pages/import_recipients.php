<?php
require_once(__DIR__ . '/../n_config.php');

// Hole alle verfügbaren Gruppen für das Dropdown
$stmt = $db->prepare("SELECT id, name, color FROM groups ORDER BY name");
$stmt->execute();
$groups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
        <br>
        <form class="ui form" id="importForm" method="post" enctype="multipart/form-data">
            <!-- Tab Menu -->
            <div class="ui top attached tabular menu">
                <a class="item active" data-tab="file">
                    <i class="file icon"></i> Datei-Upload
                </a>
                <a class="item" data-tab="text">
                    <i class="edit icon"></i> Direkte Eingabe
                </a>
                <div class="right menu">
                    <a class="item" onclick="$('#helpModal').modal('show')">
                        <i class="question circle icon"></i> Hilfe
                    </a>
                </div>
            </div>

            <div class="ui bottom attached tab segment active" data-tab="file">
                <div class="field">
                    <label>CSV/TXT Datei auswählen</label>
                    <div class="ui action input">
                        <input type="file" name="importFile" accept=".csv,.txt" id="fileInput" style="display: none;">
                        <input type="text" readonly placeholder="Keine Datei ausgewählt" id="fileLabel"
                            style="cursor: pointer;" onclick="$('#fileInput').click();">
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

            <div class="ui bottom attached tab segment" data-tab="text">
                <div class="field">
                    <label>Empfängerdaten direkt eingeben</label>
                    <textarea name="importText" id="textInput" rows="10"
                        style="font-family: monospace; min-height: 200px;"
                        placeholder="first_name,last_name,email,company,gender,title,comment"></textarea>
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
                <!-- Gruppen Auswahl mit "Neue Gruppe" Button -->
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
                            <?php
                            $groups = getAllGroups($db);
                            foreach ($groups as $groupId => $groupHtml):
                                ?>
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

        <!-- Modal für neue Gruppe -->
        <div class="ui small modal" id="newGroupModal">
            <i class="close icon"></i>
            <div class="header">
                <i class="tags icon"></i> Neue Gruppe erstellen
            </div>
            <div class="content">
                <form class="ui form" id="newGroupForm">
                    <div class="required field">
                        <label>Gruppenname</label>
                        <input type="text" name="name" placeholder="Name der neuen Gruppe" required>
                    </div>
                    <div class="required field">
                        <label>Farbe</label>
                        <div class="ui fluid selection dropdown" id="colorDropdown">
                            <input type="hidden" name="color" required>
                            <i class="dropdown icon"></i>
                            <div class="default text">Farbe wählen</div>
                            <div class="menu">
                                <div class="item" data-value="red"><i class="circle red icon"></i>Rot</div>
                                <div class="item" data-value="orange"><i class="circle orange icon"></i>Orange</div>
                                <div class="item" data-value="yellow"><i class="circle yellow icon"></i>Gelb</div>
                                <div class="item" data-value="olive"><i class="circle olive icon"></i>Olive</div>
                                <div class="item" data-value="green"><i class="circle green icon"></i>Grün</div>
                                <div class="item" data-value="teal"><i class="circle teal icon"></i>Türkis</div>
                                <div class="item" data-value="blue"><i class="circle blue icon"></i>Blau</div>
                                <div class="item" data-value="violet"><i class="circle violet icon"></i>Violett
                                </div>
                                <div class="item" data-value="purple"><i class="circle purple icon"></i>Lila</div>
                                <div class="item" data-value="pink"><i class="circle pink icon"></i>Pink</div>
                                <div class="item" data-value="brown"><i class="circle brown icon"></i>Braun</div>
                                <div class="item" data-value="grey"><i class="circle grey icon"></i>Grau</div>
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <label>Beschreibung</label>
                        <textarea name="description" rows="2"
                            placeholder="Optionale Beschreibung der Gruppe"></textarea>
                    </div>
                </form>
            </div>
            <div class="actions">
                <div class="ui black deny button">
                    Abbrechen
                </div>
                <div class="ui positive right labeled icon button" onclick="createNewGroup()">
                    Gruppe erstellen
                    <i class="check icon"></i>
                </div>
            </div>
        </div>

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
                <tr>
                    <td>first_name</td>
                    <td>Vorname</td>
                    <td><i class="green checkmark icon"></i></td>
                    <td>Max</td>
                </tr>
                <tr>
                    <td>last_name</td>
                    <td>Nachname</td>
                    <td><i class="green checkmark icon"></i></td>
                    <td>Mustermann</td>
                </tr>
                <tr>
                    <td>email</td>
                    <td>E-Mail-Adresse</td>
                    <td><i class="green checkmark icon"></i></td>
                    <td>max@example.com</td>
                </tr>
                <tr>
                    <td>company</td>
                    <td>Firma</td>
                    <td><i class="red remove icon"></i></td>
                    <td>Firma GmbH</td>
                </tr>
                <tr>
                    <td>gender</td>
                    <td>Geschlecht (male/female/other)</td>
                    <td><i class="red remove icon"></i></td>
                    <td>male</td>
                </tr>
                <tr>
                    <td>title</td>
                    <td>Titel</td>
                    <td><i class="red remove icon"></i></td>
                    <td>Dr.</td>
                </tr>
                <tr>
                    <td>comment</td>
                    <td>Kommentar</td>
                    <td><i class="red remove icon"></i></td>
                    <td>Ein Kommentar</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<!-- Allgeinenes JS -->
<script>
    $(document).ready(function () {
        // Initialisierungen
        $('.menu .item').tab({
            history: false,
            onVisible: function (tabPath) {
                // Optional: Zusätzliche Aktionen beim Tab-Wechsel
                if (tabPath === 'file') {
                    $('#fileInput').val('');
                    $('#fileLabel').val('Keine Datei ausgewählt');
                } else if (tabPath === 'text') {
                    $('#textInput').val('');
                }
            }
        });
        $('.ui.dropdown').dropdown();
        $('.ui.checkbox').checkbox();
        $('.ui.modal').modal();

        // Beispieldaten
        const exampleData = `first_name,last_name,email,company,gender,title,comment
Max,Mustermann,max@example.com,Firma GmbH,male,Dr.,"Ein Kommentar"
Anna,Schmidt,anna.schmidt@technik.com,Technik AG,female,Prof.,"Senior Entwicklerin"
Thomas,Weber,t.weber@consulting.net,Consulting Partners,male,Dipl.-Ing.,"Projektleiter"
Marie,Bauer,m.bauer@design-studio.de,Creative Design Studio,female,,"UI/UX Spezialistin"
Klaus,Fischer,klaus.fischer@handel.de,Handel & Co. KG,male,,"Vertrieb Region Süd"`;

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

        // Button Handlers
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
                const required = ['first_name', 'last_name', 'email'];

                const missing = required.filter(field => !headers.includes(field));
                if (missing.length > 0) {
                    showMessage('error', 'Fehler', `Fehlende Pflichtfelder: ${missing.join(', ')}`);
                    return;
                }

                let errors = [];
                const emailIdx = headers.indexOf('email');

                lines.slice(1).forEach((line, idx) => {
                    const fields = line.split(',');
                    const email = fields[emailIdx]?.trim().replace(/"/g, '');
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

            // Show and reset progress bar
            const $progress = $('#importProgress').show().progress({
                total: 100,
                text: {
                    active: 'Importiere: {percent}%',
                    success: 'Import abgeschlossen!'
                }
            });

            // Disable form buttons
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

        // Form Reset Handler
        $('#importForm').on('reset', function (e) {
            // Verstecke die Nachricht
            $('#importResults').hide();

            // Setze Datei-Input zurück
            $('#fileInput').val('');
            $('#fileLabel').val('Keine Datei ausgewählt');

            // Setze Text-Input zurück
            $('#textInput').val('');

            // Setze Dropdowns zurück
            $('.ui.dropdown').dropdown('clear');

            // Setze Checkboxen auf Standardwerte zurück
            $('input[name="skipHeader"]').prop('checked', true).trigger('change');
            $('input[name="overwriteExisting"]').prop('checked', false).trigger('change');

            // Verstecke Progress Bar
            $('#importProgress').hide().progress('reset');
        });

        // Helper Functions
        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        function showMessage(type, title, message) {
            $('#importResults')
                .removeClass('success error warning')
                .addClass(type)
                .show()
                .find('.header').text(title)
                .siblings('.content').html(message);
        }

        // Close button for messages
        $('.message .close').on('click', function () {
            $(this).closest('.message').hide();
        });
    });
</script>

<!-- Hochladen von Files -->
<script>
    // Verbesserte File Input Handhabung
    $(document).ready(function () {
        const $fileInput = $('#fileInput');
        const $fileLabel = $('#fileLabel');

        // Style für Hover-Effekt
        $fileLabel.hover(
            function () { $(this).addClass('hover'); },
            function () { $(this).removeClass('hover'); }
        );

        // File Input Handler
        $fileInput.on('change', function () {
            const file = this.files[0];
            if (file) {
                if (validateFile(file)) {
                    $fileLabel.val(file.name);
                } else {
                    this.value = '';
                    $fileLabel.val('Keine Datei ausgewählt');
                }
            }
        });

        // Datei-Validierung
        function validateFile(file) {
            const maxSize = 5 * 1024 * 1024; // 5MB
            const validTypes = ['.csv', '.txt'];
            const fileExtension = file.name.toLowerCase().slice(file.name.lastIndexOf('.'));

            if (file.size > maxSize) {
                showMessage('error', 'Fehler', 'Die Datei ist zu groß (Maximum: 5MB)');
                return false;
            }

            if (!validTypes.includes(fileExtension)) {
                showMessage('error', 'Fehler', 'Ungültiges Dateiformat. Bitte nur CSV oder TXT Dateien hochladen.');
                return false;
            }

            return true;
        }
    });
</script>

<!-- Neue Gruppen erstellen -->
<script>
    $(document).ready(function () {
        // Initialisiere Modal mit angepassten Optionen
        $('#newGroupModal').modal({
            closable: true,
            onDeny: function () {
                $('#newGroupForm').form('clear');
                return true;
            },
            onApprove: false // Verhindert automatisches Schließen bei Approve
        });

        // Erweiterte Form-Validierung
        $('#newGroupForm').form({
            fields: {
                name: {
                    identifier: 'name',
                    rules: [
                        {
                            type: 'empty',
                            prompt: 'Bitte geben Sie einen Gruppennamen ein'
                        },
                        {
                            type: 'minLength[2]',
                            prompt: 'Der Gruppenname muss mindestens 2 Zeichen lang sein'
                        }
                    ]
                },
                color: {
                    identifier: 'color',
                    rules: [
                        {
                            type: 'empty',
                            prompt: 'Bitte wählen Sie eine Farbe'
                        }
                    ]
                }
            }
        });
    });

    function createNewGroup() {
        const $form = $('#newGroupForm');
        const $submitButton = $('.ui.positive.button');

        // Prüfe Form-Validierung
        if (!$form.form('validate form')) {
            return false;
        }

        // Deaktiviere Submit Button
        $submitButton.addClass('loading disabled');

        const formData = new FormData($form[0]);

        // Prüfe zuerst, ob der Name bereits existiert
        const groupName = formData.get('name');

        $.ajax({
            url: 'ajax/check_group_name.php',
            type: 'POST',
            data: { name: groupName },
            success: function (checkResponse) {
                if (checkResponse.exists) {
                    showErrorToast('Der Gruppenname existiert bereits');
                    // Name existiert bereits
                    $form.form('add errors', ['Der Gruppenname existiert bereits']);
                    $submitButton.removeClass('loading disabled');
                    return;
                }

                // Name ist verfügbar, erstelle Gruppe
                createGroup(formData);
            },
            error: function () {
                showErrorToast('Fehler bei der Überprüfung des Gruppennamens');
                $submitButton.removeClass('loading disabled');
            }
        });
    }

    function createGroup(formData) {
        const $submitButton = $('.ui.positive.button');
        const $modal = $('#newGroupModal');

        $.ajax({
            url: 'ajax/create_group.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    const $groupDropdown = $('.ui.fluid.multiple.search.selection.dropdown');

                    // Füge neue Gruppe zum Dropdown hinzu
                    const newOption = `
                    <div class="item" data-value="${response.id}">
                        <i class="circle ${response.color} icon"></i>
                        ${response.name}
                    </div>
                `;
                    $('#groupsDropdownMenu').append(newOption);

                    // Erfolgsmeldung
                    showSuccessToast(`Gruppe "${response.name}" wurde erfolgreich erstellt`);

                    // Schließe Modal
                    $modal.modal('hide');
                    $('#newGroupForm').form('clear');

                    // Längerer Timeout und getrennte Aktionen
                    setTimeout(() => {
                        // Zuerst Wert setzen
                        $groupDropdown.dropdown('set selected', response.id);
                    }, 300);
                } else {
                    showErrorToast(response.message || 'Fehler beim Erstellen der Gruppe');
                }
            },
            error: function (xhr, status, error) {
                showErrorToast('Serverfehler beim Erstellen der Gruppe');
                console.error('Error creating group:', error);
            },
            complete: function () {
                $submitButton.removeClass('loading disabled');
            }
        });
    }

    // Hilfsfunktionen für Toast-Nachrichten
    function showSuccessToast(message) {
        $('body').toast({
            class: 'success',
            message: message,
            showProgress: 'bottom',
            displayTime: 3000
        });
    }

    function showErrorToast(message) {
        $('body').toast({
            class: 'error',
            message: message,
            showProgress: 'bottom',
            displayTime: 4000
        });
    }
</script>


<style>
    /* Styling für das Textfeld */
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

    /* Verbessertes Button Styling */
    .ui.action.input .button {
        display: flex;
        align-items: center;
    }
</style>

<style>
    /* Styling für das Gruppen-Dropdown */
    .ui.selection.dropdown .menu>.item {
        display: flex;
        align-items: center;
        padding: 0.5em 1em !important;
    }

    .ui.selection.dropdown .menu>.item i.icon {
        margin: 0 0.5em 0 0;
        font-size: 1em;
    }

    /* Styling für den "Neue Gruppe" Button */
    .field>label>.button {
        padding: 0.5em !important;
        font-size: 0.8em !important;
    }

    /* Styling für das Modal */
    #newGroupModal .content {
        padding: 1.5em !important;
    }

    #newGroupModal .ui.form .field {
        margin-bottom: 1em;
    }

    /* Farb-Dropdown Styling */
    #colorDropdown .menu>.item {
        display: flex;
        align-items: center;
    }

    #colorDropdown .menu>.item i.icon {
        margin-right: 0.5em;
    }
</style>