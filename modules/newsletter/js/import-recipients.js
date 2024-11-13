// Namespace für die Import-Funktionalität
const ImportManager = {
    // UI Initialisierung
    init() {
        this.initUI();
        this.bindEvents();
        this.initValidation();
    },

    // UI Setup
    initUI() {
        $('.menu .item').tab({
            history: false,
            onVisible: (tabPath) => this.handleTabChange(tabPath)
        });
        $('.ui.dropdown').dropdown();
        $('.ui.checkbox').checkbox();
        $('.ui.modal').modal();
    },

    // Event Binding
    bindEvents() {
        // File Upload Events
        $('#fileInput').on('change', this.handleFileChange.bind(this));
        $('#fileLabel').hover(
            function () { $(this).addClass('hover'); },
            function () { $(this).removeClass('hover'); }
        );

        // Text Input Events
        $('#insertExample').click(() => $('#textInput').val(this.generateExampleData()));
        $('#clearText').click(() => $('#textInput').val(''));
        $('#validateFormat').click(() => this.validateFormat());

        // Form Events
        $('#importForm').on('submit', this.handleFormSubmit.bind(this));
        $('#importForm').on('reset', this.handleFormReset.bind(this));

        // Message Close Button
        $('.message .close').on('click', function () {
            $(this).closest('.message').hide();
        });
    },

    // Tab Management
    handleTabChange(tabPath) {
        if (tabPath === 'file') {
            $('#fileInput').val('');
            $('#fileLabel').val('Keine Datei ausgewählt');
        } else if (tabPath === 'text') {
            $('#textInput').val('');
        }
    },

    // File Upload Handling
    handleFileChange(event) {
        const file = event.target.files[0];
        const fileName = file?.name || 'Keine Datei ausgewählt';
        $('#fileLabel').val(fileName);

        if (file && !this.validateFile(file)) {
            event.target.value = '';
            $('#fileLabel').val('Keine Datei ausgewählt');
        }
    },

    validateFile(file) {
        const maxSize = 5 * 1024 * 1024; // 5MB
        const validTypes = ['.csv', '.txt'];
        const fileExtension = file.name.toLowerCase().slice(file.name.lastIndexOf('.'));

        if (file.size > maxSize) {
            this.showMessage('error', 'Fehler', 'Die Datei ist zu groß (Maximum: 5MB)');
            return false;
        }

        if (!validTypes.includes(fileExtension)) {
            this.showMessage('error', 'Fehler', 'Ungültiges Dateiformat. Bitte nur CSV oder TXT Dateien hochladen.');
            return false;
        }

        return true;
    },

    // Example Data Generation
    generateExampleData() {
        const headers = 'first_name,last_name,email,company,gender,title,comment';

        // Erste Zeile mit Platzhaltern
        const firstRow = `${placeholders.vorname},${placeholders.nachname},${placeholders.email},` +
            `${placeholders.firma},${placeholders.geschlecht},${placeholders.titel},` +
            `"Erstellt am ${placeholders.datum} um ${placeholders.uhrzeit}"`;

        // Weitere Beispielzeilen aus der Konfiguration
        const additionalRows = exampleRows.map(row => row.join(','));

        return [headers, firstRow, ...additionalRows].join('\n');
    },

    // Format Validation
    validateFormat() {
        const text = $('#textInput').val().trim();
        if (!text) {
            this.showMessage('warning', 'Warnung', 'Bitte geben Sie zuerst Daten ein.');
            return;
        }

        try {
            const lines = text.split('\n');
            const headers = lines[0].toLowerCase().split(',').map(h => h.trim());
            const required = columnDefinitions
                .filter(col => col.required)
                .map(col => col.name);

            const missing = required.filter(field => !headers.includes(field));
            if (missing.length > 0) {
                this.showMessage('error', 'Fehler', `Fehlende Pflichtfelder: ${missing.join(', ')}`);
                return;
            }

            this.validateEmailAddresses(lines, headers);
        } catch (e) {
            this.showMessage('error', 'Fehler', 'Fehler beim Validieren des Formats');
            console.error(e);
        }
    },

    validateEmailAddresses(lines, headers) {
        const emailIdx = headers.indexOf('email');
        let errors = [];

        lines.slice(1).forEach((line, idx) => {
            const fields = line.split(',');
            const email = fields[emailIdx]?.trim().replace(/"/g, '');
            if (!email || !this.isValidEmail(email)) {
                errors.push(`Zeile ${idx + 2}: Ungültige E-Mail-Adresse (${email || 'leer'})`);
            }
        });

        if (errors.length > 0) {
            this.showMessage('warning', 'Warnung', 'Fehler gefunden:<br>' + errors.join('<br>'));
        } else {
            this.showMessage('success', 'Erfolg', 'Format ist korrekt!');
        }
    },

    // Form Submission
    async handleFormSubmit(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const activeTab = $('.tab.active').data('tab');

        if (!this.validateFormData(activeTab, formData)) {
            return;
        }

        await this.submitForm(formData);
    },

    validateFormData(activeTab, formData) {
        if (activeTab === 'text') {
            const text = $('#textInput').val().trim();
            if (!text) {
                this.showMessage('warning', 'Warnung', 'Bitte geben Sie Daten ein.');
                return false;
            }
            formData.set('importFile', new Blob([text], { type: 'text/csv' }), 'import.csv');
        } else if (!$('#fileInput').val()) {
            this.showMessage('warning', 'Warnung', 'Bitte wählen Sie eine Datei aus.');
            return false;
        }
        return true;
    },

    async submitForm(formData) {
        const $progress = this.initProgressBar();
        const $buttons = $('#importForm button').addClass('disabled');

        try {
            const response = await $.ajax({
                url: 'ajax/process_import.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: () => this.createUploadXHR($progress)
            });

            this.handleSubmitResponse(response, $buttons, $progress);
        } catch (error) {
            this.handleSubmitError(error, $buttons, $progress);
        }
    },

    // Helper Methods
    initProgressBar() {
        return $('#importProgress').show().progress({
            total: 100,
            text: {
                active: 'Importiere: {percent}%',
                success: 'Import abgeschlossen!'
            }
        });
    },

    createUploadXHR($progress) {
        const xhr = new XMLHttpRequest();
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                $progress.progress('set progress', Math.round((e.loaded / e.total) * 100));
            }
        });
        return xhr;
    },

    handleSubmitResponse(response, $buttons, $progress) {
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

            this.showMessage('success', 'Import erfolgreich', message);

            if (typeof reloadTable === 'function') {
                reloadTable();
            }
        } else {
            this.showMessage('error', 'Fehler', response.message || 'Ein unbekannter Fehler ist aufgetreten');
        }
    },

    handleSubmitError(error, $buttons, $progress) {
        $buttons.removeClass('disabled');
        $progress.hide();
        this.showMessage('error', 'Fehler', 'Ein Serverfehler ist aufgetreten');
        console.error(error);
    },

    handleFormReset() {
        $('#importResults').hide();
        $('#fileInput').val('');
        $('#fileLabel').val('Keine Datei ausgewählt');
        $('#textInput').val('');
        $('.ui.dropdown').dropdown('clear');
        $('input[name="skipHeader"]').prop('checked', true).trigger('change');
        $('input[name="overwriteExisting"]').prop('checked', false).trigger('change');
        $('#importProgress').hide().progress('reset');
    },

    showMessage(type, title, message) {
        $('#importResults')
            .removeClass('success error warning')
            .addClass(type)
            .show()
            .find('.header').text(title)
            .siblings('.content').html(message);
    },

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
};

// Initialisierung beim Laden der Seite
$(document).ready(() => ImportManager.init());