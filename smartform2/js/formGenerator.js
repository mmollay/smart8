// form-generator.js

function initializeForm(formId, formRules, responseType, successFunction) {
    var form = $('#' + formId);

    if (form.data('initialized')) {
        return;
    }

    var formValidationRules = {};
    var isSubmitting = false;

    $.each(formRules, function (fieldName, rules) {
        formValidationRules[fieldName] = { identifier: fieldName, rules: rules };
    });

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

    form.data('initialized', true);
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
                console.log('JSON response:', response);
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

function showFieldError(fieldId, errorMessage) {
    var field = $('#' + fieldId).closest('.field');
    field.addClass('error');
    var errorElement = $('#' + fieldId + '-error');
    if (errorElement.length === 0) {
        $('<div id="' + fieldId + '-error" class="ui basic red pointing prompt label transition visible">' + errorMessage + '</div>').insertAfter($('#' + fieldId));
    } else {
        errorElement.text(errorMessage);
    }
}

function clearFieldError(fieldId) {
    var field = $('#' + fieldId).closest('.field');
    field.removeClass('error');
    $('#' + fieldId + '-error').remove();
}

function showToast(message, type) {
    // Create toast element
    var toast = document.createElement('div');
    toast.classList.add('ui', 'toast-container');

    // Create message element
    var messageEl = document.createElement('div');
    messageEl.classList.add('ui', 'message', type, 'toast');
    messageEl.innerHTML = '<i class="close icon"></i><div class="content"><div class="header">' + message + '</div></div>';

    // Append message to toast
    toast.appendChild(messageEl);

    // Append toast to body
    document.body.appendChild(toast);

    // Initialize Fomantic UI toast
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

    // Remove the toast container after the toast has faded
    setTimeout(function () {
        $(toast).remove();
    }, 3500);  // 3000ms display time + 500ms for fade out
}

// Initialize UI components
$(document).ready(function () {
    // Initialisiere Radio-Buttons
    $('.ui.radio.checkbox').checkbox();

    // Initialisiere gruppierte Checkboxen
    $('.list .master.checkbox')
        .checkbox({
            // check all children
            onChecked: function () {
                var $childCheckbox = $(this).closest('.checkbox').siblings('.list').find('.checkbox');
                $childCheckbox.checkbox('check');
            },
            // uncheck all children
            onUnchecked: function () {
                var $childCheckbox = $(this).closest('.checkbox').siblings('.list').find('.checkbox');
                $childCheckbox.checkbox('uncheck');
            }
        });

    $('.list .child.checkbox')
        .checkbox({
            // Fire on load to set parent value
            fireOnInit: true,
            // Change parent state on each child checkbox change
            onChange: function () {
                var $listGroup = $(this).closest('.list'),
                    $parentCheckbox = $listGroup.closest('.item').children('.checkbox'),
                    $checkbox = $listGroup.find('.checkbox'),
                    allChecked = true,
                    allUnchecked = true;

                // check to see if all other siblings are checked or unchecked
                $checkbox.each(function () {
                    if ($(this).checkbox('is checked')) {
                        allUnchecked = false;
                    }
                    else {
                        allChecked = false;
                    }
                });

                // set parent checkbox state, but don't trigger its onChange callback
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
    $('.ui.calendar').each(function () {
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

    initializeDropdowns();
});

// In form-generator.js, am Ende der Datei hinzufügen:

function initializeDropdowns() {
    $('.ui.dropdown').each(function () {
        var $dropdown = $(this);
        var settingsAttr = $dropdown.attr('data-settings');
        var settings = settingsAttr ? JSON.parse(settingsAttr) : {};

        // Stelle sicher, dass fullTextSearch und clearable korrekt gesetzt sind
        settings.fullTextSearch = settings.fullTextSearch !== false;
        settings.clearable = settings.clearable !== false;

        console.log('Dropdown ID:', $dropdown.attr('id'));
        console.log('Final Settings:', settings);
        $dropdown.dropdown(settings);

        $dropdown.on('change', function (event, data) {
            console.log('Dropdown changed:', {
                id: $dropdown.attr('id'),
                value: $dropdown.dropdown('get value'),
                text: $dropdown.dropdown('get text')
            });
        });
    });
}
