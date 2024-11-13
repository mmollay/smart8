const EditorUtils = {
    insertPlaceholder(placeholder) {
        const editor = document.querySelector('.ck-editor__editable').ckeditorInstance;
        if (editor) {
            editor.model.change(writer => {
                const selection = editor.model.document.selection;
                const range = selection.getFirstRange();
                // Platzhalter einfügen
                editor.model.insertContent(writer.createText(placeholder), range.start);
                // Cursor direkt nach dem Platzhalter positionieren
                const newPosition = range.start.getShiftedBy(placeholder.length);
                writer.setSelection(newPosition);
            });
            // Fokus auf den Editor setzen
            editor.editing.view.focus();
        }
    },

    previewWithPlaceholders() {
        const editor = document.querySelector('.ck-editor__editable').ckeditorInstance;
        if (!editor) return;

        const content = editor.getData();
        const subject = $('#subject').val();

        // Example placeholder values
        const placeholders = {
            'anrede': 'Sehr geehrter Herr',
            'titel': 'Dr.',
            'vorname': 'Max',
            'nachname': 'Mustermann',
            'firma': 'Musterfirma GmbH',
            'email': 'max.mustermann@example.com',
            'datum': new Date().toLocaleDateString('de-DE'),
            'uhrzeit': new Date().toLocaleTimeString('de-DE')
        };

        let previewContent = content;
        let previewSubject = subject;

        // Replace placeholders
        Object.entries(placeholders).forEach(([key, value]) => {
            const regex = new RegExp(`{{${key}}}`, 'g');
            previewContent = previewContent.replace(regex, value);
            previewSubject = previewSubject.replace(regex, value);
        });

        $('#previewContent').html(`
            <div class="ui raised segment">
                <h3>${previewSubject}</h3>
                <div class="ui divider"></div>
                ${previewContent}
            </div>
        `);

        // Korrekte Modal-Konfiguration
        $('#previewModal').modal({
            allowMultiple: true,
            closable: true,
            onHide: function () {
                return true; // Erlaubt das Schließen des Preview-Modals
            }
        }).modal('show');
    }
};