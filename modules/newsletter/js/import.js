window.ImportModule = {
    // Konfiguration
    config: {
        urls: {
            import: 'ajax/process_import.php',
            status: 'ajax/import_status.php'
        },
        pollInterval: 1000,
        maxFileSize: 5 * 1024 * 1024, // 5MB
        progressTexts: {
            active: 'Import läuft...',
            success: 'Import abgeschlossen',
            error: 'Import fehlgeschlagen'
        }
    },
    // Status-Verwaltung
    state: {
        importId: null,
        pollTimer: null,
        isImporting: false
    },

    // Initialisierung
    init() {
        this.initializeComponents();
        this.bindEvents();

        $('<style>')
            .prop('type', 'text/css')
            .html(`
        #importStatus {
            margin: 1em 0;
            padding: 0.5em;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        #importProgress {
            margin-bottom: 0 !important;
        }
        #importProgress .bar {
            transition: width 0.3s ease;
            min-width: 2em;
        }
        .ui.mini.statistic {
            margin: 0;
        }
        .ui.mini.statistic .value {
            font-size: 1.2em !important;
            font-weight: bold;
        }
        .ui.mini.statistic .label {
            font-size: 0.85em !important;
            color: #666;
            margin-top: 0.2em;
        }
        .ui.grid>.column {
            padding: 0.5em !important;
        }
    `)
            .appendTo('head');
    },

    // UI-Komponenten initialisieren
    initializeComponents() {
        $('.menu .item').tab();
        $('.ui.dropdown').dropdown();
        $('.ui.checkbox').checkbox();
        $('.ui.modal').modal();

        $('#importProgress').progress({
            total: 100,
            showActivity: true,
            text: this.config.progressTexts
        });

        $('#helpModal').modal({
            closable: true,
            onShow: function () {
                $(this).find('.positive.button').focus();
            }
        });

    },

    // Event-Binding
    bindEvents() {
        // Bestehende Event-Bindings
        $('#fileInput').on('change', e => this.handleFileSelection(e));
        $('#insertExample').on('click', () => this.insertExampleData());
        $('#clearText').on('click', () => $('#textInput').val(''));
        $('#validateFormat').on('click', () => this.validateFormat());
        $('#importForm').on('submit', (e) => {
            e.preventDefault();
            this.handleSubmit(e);
        });
        $('#importForm').on('reset', (e) => {
            e.preventDefault();
            this.resetForm();
        });
        $(document).on('click', '.message .close', function () {
            $(this).closest('.message').fadeOut();
        });

        // Neue Gruppen Event-Bindings
        $('#addGroupModal .positive.button').on('click', () => {
            const $form = $('#addGroupModal .form');
            const data = {
                name: $form.find('input[name="name"]').val().trim(),
                color: $form.find('input[name="color"]').val() || 'grey', // Default-Farbe wenn leer
                description: $form.find('input[name="description"]').val().trim()
            };

            // Nur noch Name prüfen
            if (!data.name) {
                alert('Bitte einen Gruppennamen eingeben');
                return;
            }

            $.ajax({
                url: 'ajax/create_group.php',
                method: 'POST',
                data: data,
                success: (response) => {
                    console.log('Response:', response);
                    if (response.success) {
                        const $dropdown = $('input[name="group_ids[]"]').closest('.ui.dropdown');
                        console.log('Dropdown gefunden:', $dropdown.length);

                        const newOption = `
                            <div class="item" data-value="${response.id}">
                                <div class="ui empty circular label ${response.color}"></div>${response.name}
                            </div>
                        `;
                        console.log('Neue Option:', newOption);

                        $dropdown.find('.menu').append(newOption);
                        $dropdown.dropdown('refresh');
                        $dropdown.dropdown('set selected', response.id);

                        console.log('Aktuelle Werte:', $dropdown.dropdown('get value'));

                        $('#addGroupModal').modal('hide');
                        $('#addGroupModal input').val('');
                        $('#colorDropdown').dropdown('clear');
                    } else {
                        alert(response.message);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Ajax Fehler:', error);
                    alert('Fehler beim Speichern der Gruppe');
                }
            });


        });

        $('#addGroupButton').on('click', () => {
            $('#addGroupModal')
                .modal('show')
                .find('input').val('');
            $('#colorDropdown').dropdown('clear');
        });
    },

    // Datei-Auswahl Handler
    handleFileSelection(e) {
        const file = e.target.files[0];
        const fileName = file ? file.name : 'Keine Datei ausgewählt';
        $('#fileLabel').val(fileName);

        if (file && file.size > this.config.maxFileSize) {
            this.showMessage('error', 'Datei zu groß',
                `Maximale Dateigröße: ${this.formatBytes(this.config.maxFileSize)}`);
            e.target.value = '';
            $('#fileLabel').val('Keine Datei ausgewählt');
        }
    },

    // Format-Validierung
    validateFormat() {
        const text = $('#textInput').val().trim();
        if (!text) {
            this.showMessage('warning', 'Keine Daten', 'Bitte geben Sie zuerst Daten ein.');
            return;
        }

        const validation = this.validateImportData(text);
        if (validation.isValid) {
            this.showMessage('success', 'Validierung erfolgreich',
                `${validation.rowCount} Datensätze wurden validiert.`);
        } else {
            this.showMessage('error', 'Validierungsfehler',
                validation.errors.join('<br>'));
        }
    },

    // Import-Daten validieren
    validateImportData(text) {
        const lines = text.split('\n');
        const errors = [];
        let rowCount = 0;

        try {
            if (lines.length < 2) {
                return { isValid: false, errors: ['Keine Daten gefunden'] };
            }

            const headers = lines[0].toLowerCase().split(',').map(h => h.trim());
            const emailIndex = headers.indexOf('email');

            if (emailIndex === -1) {
                return { isValid: false, errors: ['Keine E-Mail-Spalte gefunden'] };
            }

            lines.slice(1).forEach((line, index) => {
                if (!line.trim()) return;
                rowCount++;

                const fields = this.parseCsvLine(line);
                const email = fields[emailIndex]?.trim();

                if (!email || !this.isValidEmail(email)) {
                    errors.push(`Zeile ${index + 2}: Ungültige E-Mail (${email || 'leer'})`);
                }
            });
        } catch (e) {
            return { isValid: false, errors: ['Fehler beim Validieren: ' + e.message] };
        }

        return {
            isValid: errors.length === 0,
            errors,
            rowCount
        };
    },

    // Form-Submit Handler
    async handleSubmit(e) {
        e.preventDefault();
        e.stopPropagation();  // Wichtig: Verhindert Bubble-Up

        if (this.state.isImporting) return;

        const formData = new FormData(e.target);
        if (!this.validateSubmitData(formData)) return;

        this.state.isImporting = true;
        this.updateUI(true);

        try {
            const response = await this.startImport(formData);
            if (response.success) {
                this.state.importId = response.import_id;
                this.startProgressPolling();
            } else {
                throw new Error(response.message || 'Import fehlgeschlagen');
            }
        } catch (error) {
            this.handleImportError(error);
        }
    },

    // Import starten
    async startImport(formData) {
        const response = await fetch(this.config.urls.import, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    },

    // Fortschritts-Polling
    startProgressPolling() {
        this.state.pollTimer = setInterval(async () => {
            try {
                const status = await this.fetchImportStatus();
                this.updateProgress(status);

                if (status.status === 'completed' || status.status === 'error') {
                    this.stopPolling();
                    this.handleImportCompletion(status);
                }
            } catch (error) {
                this.stopPolling();
                this.handleImportError(error);
            }
        }, this.config.pollInterval);
    },

    // Status abrufen
    async fetchImportStatus() {
        const response = await fetch(
            `${this.config.urls.status}?id=${this.state.importId}`
        );
        return await response.json();
    },

    updateProgress(status) {
        const statusText = `Verarbeitet: ${status.processed_records} von ${status.total_records}`;
        $('#importStatus').text(statusText);

        $('#importStatus').parent().find('.content').text(
            status.processed_records === status.total_records ?
                'Import abgeschlossen' :
                'Importiere Daten...'
        );
    },

    // UI-Status aktualisieren
    updateUI(importing) {
        if (importing) {
            $('#loadingIndicator').dimmer('show');
        } else {
            $('#loadingIndicator').dimmer('hide');
        }
        $('#importForm button').toggleClass('disabled', importing);
        $('input, select, textarea').prop('disabled', importing);
        $('.ui.tab').toggleClass('disabled', importing);
        $('.tabular.menu .item').toggleClass('disabled', importing);
    },

    // Polling stoppen
    stopPolling() {
        if (this.state.pollTimer) {
            clearInterval(this.state.pollTimer);
            this.state.pollTimer = null;
        }
        this.state.isImporting = false;

        // Verzögerte UI-Aktualisierung
        setTimeout(() => {
            this.updateUI(false);
        }, 100); // 3 Sekunden warten bevor UI aktualisiert wird
    },

    // Import abschließen
    handleImportCompletion(status) {
        this.stopPolling();

        if (status.status === 'completed') {
            $('#importProgress').progress('set success');

            // Prüfen ob wirklich Datensätze verarbeitet wurden
            if (status.imported === 0 && status.updated === 0 && status.skipped > 0) {
                // Wenn nur übersprungene Datensätze, dann als Warnung anzeigen
                this.showMessage('warning', 'Import abgeschlossen', `
                    <div class="ui list">
                        <div class="item"><i class="ban icon"></i> ${status.skipped} Datensätze übersprungen</div>
                        <div class="item"><i class="info circle icon"></i> Keine Datensätze importiert oder aktualisiert</div>
                    </div>
                `);
            } else {
                // Normaler Erfolgsfall
                this.showMessage('success', 'Import erfolgreich', `
                    <div class="ui list">
                        <div class="item"><i class="plus circle icon"></i> ${status.imported} neue Datensätze importiert</div>
                        <div class="item"><i class="sync icon"></i> ${status.updated} Datensätze aktualisiert</div>
                        ${status.skipped > 0 ? `<div class="item"><i class="ban icon"></i> ${status.skipped} Datensätze übersprungen</div>` : ''}
                    </div>
                `);
            }

            if (typeof reloadTable === 'function') {
                reloadTable();
            }
        } else {
            // Nur Fehler anzeigen wenn kein erfolgreicher Import
            this.handleImportError(status.error_message || 'Import fehlgeschlagen');
        }
    },

    // Fehlerbehebung
    handleImportError(error) {
        this.stopPolling();
        $('#importProgress').progress('set error');

        // Konvertiere error in String, egal ob es ein Objekt oder String ist
        const errorMessage = typeof error === 'string' ? error :
            (error.message || 'Ein unerwarteter Fehler ist aufgetreten');

        // Prüfen ob es sich um einen currentContentId Fehler handelt
        if (errorMessage.includes('currentContentId')) {
            return; // Fehler ignorieren da er nicht relevant ist
        }

        this.showMessage('error', 'Import fehlgeschlagen', `
            <div class="ui negative message">
                <p><i class="warning sign icon"></i> ${errorMessage}</p>
            </div>
        `);
    },

    // Hilfsfunktionen
    formatCompletionMessage(status) {
        return `
            Importierte Datensätze: ${status.imported}<br>
            Aktualisierte Datensätze: ${status.updated}<br>
            Übersprungene Datensätze: ${status.skipped}
        `;
    },

    showMessage(type, title, message, stay = true) {
        // IMMER erst alle alten Nachrichten entfernen
        $('.ui.message').not('#errorList, #importStatus').remove();

        const $message = $('<div>')
            .addClass(`ui message ${type} visible`)
            .css('display', 'block !important')
            .append(
                $('<i>').addClass('close icon'),
                $('<div>').addClass('header').text(title),
                $('<p>').html(message)
            );

        // Neue Nachricht einfügen
        $('#importForm').before($message);

        // Click-Handler für Close-Button
        $message.find('.close').on('click', function () {
            $(this).closest('.message').remove();
        });
    },

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    parseCsvLine(line) {
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
    },

    formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(decimals)) + ' ' + sizes[i];
    },

    // Nach handleSubmit und vor startImport einfügen
    validateSubmitData(formData) {
        // Prüfe aktiven Tab
        const activeTab = $('.tab.active').data('tab');

        if (activeTab === 'text') {
            const text = $('#textInput').val().trim();
            if (!text) {
                this.showMessage('warning', 'Keine Daten', 'Bitte geben Sie Daten ein.');
                return false;
            }
            formData.set('importFile', new Blob([text], { type: 'text/csv' }), 'import.csv');
        } else if (!$('#fileInput').val()) {
            this.showMessage('warning', 'Keine Datei', 'Bitte wählen Sie eine Datei aus.');
            return false;
        }

        // Prüfe Gruppen-Auswahl
        const groupIds = formData.getAll('group_ids[]');
        if (groupIds.length === 0) {
            this.showMessage('warning', 'Keine Gruppe', 'Bitte wählen Sie mindestens eine Gruppe aus.');
            return false;
        }

        return true;
    },

    insertExampleData() {
        const exampleData = $('#textInput').data('example') || `<?php echo $exampleData; ?>`;
        $('#textInput').val(exampleData);
    },

    resetForm() {
        // Progress und Status zurücksetzen
        $('#importProgress').progress('reset');
        $('#importStatus').hide();

        // Statistiken zurücksetzen
        $('#stat-total, #stat-processed, #stat-success, #stat-errors').text('0');

        // Formular-Elemente zurücksetzen
        $('#fileInput').val('');
        $('#fileLabel').val('Keine Datei ausgewählt');
        $('#textInput').val('');
        $('.ui.dropdown').dropdown('clear');

        // Checkboxen zurücksetzen
        $('input[name="skipHeader"]').prop('checked', true).trigger('change');
        $('input[name="overwriteExisting"]').prop('checked', false).trigger('change');

        // Fehlermeldungen entfernen
        $('.ui.message').not('#errorList, #importStatus').remove();
        $('#errorList').hide();

        // Import-Status zurücksetzen
        this.state.importId = null;
        this.state.isImporting = false;

        // Polling stoppen falls aktiv
        this.stopPolling();

        // UI-Elemente aktivieren
        this.updateUI(false);
    },
};

// Modul initialisieren
$(document).ready(() => ImportModule.init());

