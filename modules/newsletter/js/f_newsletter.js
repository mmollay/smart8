// f_newsletter.js
window.NewsletterEditor = window.NewsletterEditor || (function () {
    return {
        insertPlaceholder(placeholder) {
            const editor = document.querySelector('.ck-editor__editable').ckeditorInstance;
            if (editor) {
                editor.model.change(writer => {
                    const selection = editor.model.document.selection;
                    const range = selection.getFirstRange();
                    editor.model.insertContent(writer.createText(placeholder), range.start);
                    const newPosition = range.start.getShiftedBy(placeholder.length);
                    writer.setSelection(newPosition);
                });
                editor.editing.view.focus();
            }
        },

        previewWithPlaceholders() {
            const editor = document.querySelector('.ck-editor__editable').ckeditorInstance;
            if (!editor) return;

            const content = editor.getData();
            const subject = $('#subject').val();

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

            $('#previewModal').modal({
                allowMultiple: true,
                closable: true,
                onHide: function () {
                    return true;
                }
            }).modal('show');
        }
    };
})();

// Modal-Initialisierung
$(document).ready(function () {
    $('#modal_form_n, #saveTemplateModal').modal({
        allowMultiple: true,
        closable: false
    });
});

function loadTemplate(templateId) {
    if (!templateId) return;

    $.ajax({
        url: 'ajax/template/get_template.php',
        method: 'POST',
        data: { template_id: templateId },
        dataType: 'json',
        success: function (response) {
            console.log('Template Response:', response); // Debug-Ausgabe

            if (response.success) {
                const editor = document.querySelector('.ck-editor__editable').ckeditorInstance;
                if (editor && response.data.html_content) {
                    editor.setData(response.data.html_content);
                }

                if (response.data.subject) {
                    $('#subject').val(response.data.subject);
                }

                showToast('Template wurde geladen', 'success');
            } else {
                console.error('Template Ladefehler:', response.message);
                showToast('Fehler beim Laden des Templates: ' + response.message, 'error');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', {
                status: status,
                error: error,
                response: xhr.responseText
            });
            showToast('Fehler beim Laden des Templates', 'error');
        }
    });
}

function saveAsTemplate() {
    // Template-Modal öffnen, ohne das Haupt-Modal zu schließen
    $('#saveTemplateModal')
        .modal({
            allowMultiple: true,
            onApprove: function () {
                return saveTemplate();
            }
        })
        .modal('show');
}

function saveTemplate() {
    const name = $('#templateName').val();
    const description = $('#templateDescription').val();
    const editor = document.querySelector('.ck-editor__editable').ckeditorInstance;
    // Hole den Betreff aus dem Hauptformular
    const subject = $('#subject').val();


    // Hole den Content aus dem Editor
    const content = editor ? editor.getData() : '';

    // Debug-Ausgaben
    console.log('Speichere Template mit folgenden Werten:', {
        name: name,
        description: description,
        html_content: content,
        subject: subject
    });

    if (!name) {
        showToast('Bitte geben Sie einen Template-Namen ein', 'error');
        return false;
    }

    // Modal in Loading-Zustand versetzen
    $('#saveTemplateModal').addClass('loading');

    // AJAX-Request
    $.ajax({
        url: 'ajax/template/save_template.php',
        method: 'POST',
        data: {
            name: name,
            description: description,
            html_content: content,
            subject: subject // Stelle sicher, dass der subject mitgesendet wird
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                $('#saveTemplateModal')
                    .modal('hide')
                    .removeClass('loading');

                showToast('Template wurde gespeichert', 'success');

                // Template Dropdown aktualisieren
                refreshTemplateDropdown();

                // Template-Modal-Felder zurücksetzen
                $('#templateName').val('');
                $('#templateDescription').val('');
            } else {
                $('#saveTemplateModal').removeClass('loading');
                showToast('Fehler beim Speichern des Templates: ' + response.message, 'error');
            }
        },
        error: function (xhr, status, error) {
            $('#saveTemplateModal').removeClass('loading');
            console.error('AJAX Error:', {
                status: status,
                error: error,
                response: xhr.responseText
            });
            showToast('Fehler beim Speichern des Templates: ' + error, 'error');
        }
    });

    return false;
}

// Funktion zum Aktualisieren der Template-Dropdown
function refreshTemplateDropdown() {
    $.ajax({
        url: 'ajax/template/get_templates.php',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                const $dropdown = $('select[name="template_id"]');
                const currentValue = $dropdown.val();

                $dropdown.empty();
                $dropdown.append('<option value="">--Template auswählen--</option>');

                response.templates.forEach(template => {
                    $dropdown.append(new Option(template.name, template.id));
                });

                // Dropdown aktualisieren
                $dropdown.dropdown('refresh');
            }
        }
    });
}

// function updateFileListInDatabase(fileList) {
//     const formData = new FormData();
//     formData.append('action', 'updateFileList');
//     formData.append('update_id', update_id);  // Verwendet die globale Variable von oben
//     formData.append('fileList', JSON.stringify(fileList));

//     fetch('ajax/template/update_file_list.php', {
//         method: 'POST',
//         body: formData
//     })
//         .then(response => response.json())
//         .then(data => {
//             if (data.success) {
//                 console.log('Dateiliste aktualisiert');
//             } else {
//                 console.error('Fehler beim Aktualisieren der Dateiliste:', data.error);
//             }
//         })
//         .catch(error => {
//             console.error('Fehler beim Senden der Anfrage:', error);
//         });
// }

function afterFormSubmit(response) {
    if (response.success) {
        showToast('Newsletter gespeichert', 'success');

        // Wenn reloadTable verfügbar ist
        if (typeof reloadTable === 'function') {
            reloadTable();

            // Modal nach 300ms schließen (nach Tabellen-Reload)
            setTimeout(() => {
                $('.ui.modal').modal('hide');
            }, 200);
        } else {
            // Wenn keine Tabelle vorhanden, Modal direkt schließen
            $('.ui.modal').modal('hide');
        }
    } else {
        showToast('Fehler beim Speichern: ' + response.message, 'error');
    }
}