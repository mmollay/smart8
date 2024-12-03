// form-generator.js

function initializeForm(formId, formRules, responseType, successFunction) {
    var form = $('#' + formId);

    // Prüfe, ob das Formular bereits initialisiert wurde
    if (form.data('initialized')) {
        return;
    }

    var formValidationRules = {};
    var isSubmitting = false;

    $.each(formRules, function (fieldName, rules) {
        formValidationRules[fieldName] = { identifier: fieldName, rules: rules };
    });

    // Entferne vorherige Event-Listener
    form.off('submit');

    form.form({
        fields: formValidationRules,
        inline: true,
        on: 'blur',
        onSuccess: function (event, fields) {
            event.preventDefault();
            if (!isSubmitting) {
                if (typeof updateCKEditorData === 'function') {
                    updateCKEditorData(formId);
                }
                var isValid = typeof validateCKEditors === 'function' ? validateCKEditors(formId, formValidationRules) : true;
                if (isValid) {
                    isSubmitting = true;
                    if (responseType === 'redirect') {
                        form[0].submit();
                        return;
                    }
                    submitForm(formId, responseType, successFunction, function () { isSubmitting = false; });
                }
            }
        }
    });

    // Initialisiere alle UI-Komponenten
    initializeFormComponents(form);

    form.data('initialized', true);
}

function initializeFormComponents(form) {
    // Initialisiere Radio-Buttons
    form.find('.ui.radio.checkbox').checkbox();

    // Initialisiere gruppierte Checkboxen
    form.find('.list .master.checkbox').checkbox({
        onChecked: function () {
            var $childCheckbox = $(this)
                .closest('.checkbox')
                .siblings('.list')
                .find('.checkbox');
            $childCheckbox.checkbox('check');
        },
        onUnchecked: function () {
            var $childCheckbox = $(this)
                .closest('.checkbox')
                .siblings('.list')
                .find('.checkbox');
            $childCheckbox.checkbox('uncheck');
        }
    });

    form.find('.list .child.checkbox').checkbox({
        fireOnInit: true,
        onChange: function () {
            var $listGroup = $(this).closest('.list'),
                $parentCheckbox = $listGroup.closest('.item').children('.checkbox'),
                $checkbox = $listGroup.find('.checkbox'),
                allChecked = true,
                allUnchecked = true;

            $checkbox.each(function () {
                if ($(this).checkbox('is checked')) {
                    allUnchecked = false;
                } else {
                    allChecked = false;
                }
            });

            if (allChecked) {
                $parentCheckbox.checkbox('set checked');
            }
            else if (allUnchecked) {
                $parentCheckbox.checkbox('set unchecked');
            }
            else {
                $parentCheckbox.checkbox('set indeterminate');
            }
        }
    });

    // Initialisiere Kalender
    form.find('.ui.calendar').each(function () {
        $(this).calendar({
            type: $(this).data('type'),
            formatter: {
                date: function (date, settings) {
                    if (!date) return '';
                    var day = date.getDate();
                    var month = date.getMonth() + 1;
                    var year = date.getFullYear();
                    return day + '.' + month + '.' + year;
                }
            }
        });
    });

    // Initialisiere Dropdowns
    initializeDropdowns(form);
}

// Der initializeDropdowns-Funktion eine Überprüfung hinzufügen
function initializeDropdowns(form) {
    // Überprüfe, ob form ein gültiges jQuery-Objekt ist
    if (!form || !form.length) {
        console.warn('Kein gültiges Formular für Dropdown-Initialisierung gefunden');
        return;
    }

    form.find('.ui.dropdown').each(function () {
        var $dropdown = $(this);
        var settingsAttr = $dropdown.attr('data-settings');
        var onChangeAttr = $dropdown.attr('data-onchange');
        var settings = settingsAttr ? JSON.parse(settingsAttr) : {};

        settings.fullTextSearch = settings.fullTextSearch !== false;
        settings.clearable = settings.clearable !== false;

        if (onChangeAttr) {
            settings.onChange = function (value, text, $selected) {
                try {
                    eval('(' + onChangeAttr + ')(value, text, $selected)');
                } catch (error) {
                    console.error('Fehler in onChange Funktion:', error);
                }
            };
        }

        $dropdown.dropdown(settings);
    });
}

function submitForm(formId, responseType, successFunction, completeCallback) {
    var formElement = $('#' + formId)[0];
    var formData = new FormData(formElement);

    if (window.editorInstances && window.editorInstances[formId]) {
        Object.keys(window.editorInstances[formId]).forEach(function (editorId) {
            var editor = window.editorInstances[formId][editorId];
            formData.set(editorId, editor.getData());
        });
    }

    var ajaxSettings = {
        url: formElement.action,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            if (responseType === 'json') {
                console.log('JSON Antwort:', response);
            }
            successFunction(response);
        },
        error: function (xhr, status, error) {
            showToast('Fehler bei der Übermittlung des Formulars', 'error');
        },
        complete: completeCallback
    };

    if (responseType === 'json') {
        ajaxSettings.dataType = 'json';
    }

    $.ajax(ajaxSettings);
}

function showToast(message, type) {
    var toast = document.createElement('div');
    toast.classList.add('ui', 'toast-container');

    var messageEl = document.createElement('div');
    messageEl.classList.add('ui', 'message', type, 'toast');
    messageEl.innerHTML = '<i class="close icon"></i><div class="content"><div class="header">' + message + '</div></div>';

    toast.appendChild(messageEl);
    document.body.appendChild(toast);

    $(messageEl).toast({
        closeIcon: true,
        showIcon: type === 'success' ? 'check circle' : 'exclamation circle',
        position: 'top right',
        displayTime: 3000,
        closeOnClick: false,
        className: {
            toast: 'ui message'
        }
    });

    setTimeout(function () {
        $(toast).remove();
    }, 3500);
}

$(document).ready(function () {
    // Initialisiere Formularkomponenten für alle vorhandenen Formulare
    $('form').each(function () {
        initializeFormComponents($(this));
    });
});