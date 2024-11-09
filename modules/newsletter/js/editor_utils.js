// js/editor_utils.js
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
    }
};