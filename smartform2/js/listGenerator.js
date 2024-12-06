// Globale Variablen
let listInstances = {};
let autoReloadTimers = {};

function loadListGenerator(url, customState = {}) {

    alert('Tst');
    const contentId = customState.contentId || 'content';
    currentContentId = contentId; // Speichern der aktuellen ContentID

    if (!listInstances[contentId]) {
        listInstances[contentId] = {
            currentUrl: url,
            state: {
                page: 1,
                sort: 'id',
                sortDir: 'ASC',
                search: '',
                filters: {},
                saveState: true,
                contentId: contentId,
                autoReloadInterval: customState.autoReloadInterval || 0 // Fügen Sie dies hinzu
            }
        };
    }

    let instance = listInstances[contentId];
    instance.currentUrl = url;

    // Lade gespeicherten Zustand aus localStorage nur wenn saveState true ist
    const savedState = (customState.saveState !== false) ? JSON.parse(localStorage.getItem(`${url}_${contentId}`)) || {} : {};

    // Verschmelze den Standard-Zustand, gespeicherten Zustand und benutzerdefinierten Zustand
    instance.state = {
        ...instance.state,
        ...savedState,
        ...customState
    };

    console.log(`Sende AJAX-Anfrage für ${contentId} mit Zustand:`, instance.state);

    $.ajax({
        url: url,
        method: "GET",
        data: {
            ...instance.state,
            filters: instance.state.filters
        },
        success: function (response) {
            $(`#${contentId}`).html(response);
            setupEventHandlers(contentId);
            console.log(`AJAX-Anfrage erfolgreich für ${contentId}`);
            if (instance.isSearchFocused) {
                $(`#search_${contentId}`).focus();
                var searchInput = $(`#search_${contentId}`)[0];
                if (searchInput) {
                    searchInput.selectionStart = searchInput.selectionEnd = searchInput.value.length;
                }
            }

            // Stelle Suchwert wieder her
            $(`#search_${contentId}`).val(instance.state.search);

            // Stelle Filterwerte wieder her
            restoreFilters(contentId);

            // Speichere Zustand im localStorage nur wenn saveState true ist
            if (instance.state.saveState) {
                localStorage.setItem(`${url}_${contentId}`, JSON.stringify(instance.state));
            }

            // Fügen Sie diese Zeile hinzu, um den Auto-Reload einzurichten
            setupAutoReload(contentId);
        },
        error: function (xhr, status, error) {
            $(`#${contentId}`).html("<div class='ui negative message'>Fehler beim Laden der Daten.</div>");
            console.error(`AJAX-Anfrage fehlgeschlagen für ${contentId}:`, status, error);
        }
    });
}

function reloadTable(contentId = null) {
    const targetContentId = contentId || currentContentId;
    if (!targetContentId) {
        console.error('Keine ContentID verfügbar. Bitte geben Sie eine an oder laden Sie zuerst eine Tabelle.');
        return;
    }
    let instance = listInstances[targetContentId];
    loadListGenerator(instance.currentUrl, instance.state);

    // Setze den Auto-Reload-Timer zurück
    setupAutoReload(targetContentId);
}

function setupEventHandlers(contentId) {
    let instance = listInstances[contentId];
    let reloadTimeout;
    let previousState = JSON.stringify(instance.state);

    function scheduleReload() {
        clearTimeout(reloadTimeout);
        reloadTimeout = setTimeout(() => {
            const currentState = JSON.stringify(instance.state);
            if (currentState !== previousState) {
                console.log('Zustand hat sich geändert, lade neu...');
                reloadTable(contentId);
                previousState = currentState;
            } else {
                console.log('Kein Neuladen nötig, Zustand unverändert');
            }
        }, 100);
    }

    initializeGroupByDropdown();

    // Externe Buttons einrichten
    $(`#${contentId} button[data-modal]`).off('click').on('click', function () {
        const modalId = $(this).data('modal');
        const $modal = $('#' + modalId);
    });

    // Popups für externe Buttons initialisieren
    $(`#${contentId} button[data-content]`).popup({
        position: 'top center',
        variation: 'basic'
    });

    // Sortierbare Spalten einrichten
    $(`#${contentId} .sortable`).off('click').on('click', function () {
        const column = $(this).data('column');
        console.log(`Angeklickte Spalte für ${contentId}:`, column);

        if (instance.state.sort === column) {
            instance.state.sortDir = instance.state.sortDir === 'ASC' ? 'DESC' : 'ASC';
        } else {
            instance.state.sort = column;
            instance.state.sortDir = 'ASC';
        }
        instance.state.page = 1;
        console.log(`Aktualisierter Zustand für ${contentId}:`, instance.state);
        reloadTable(contentId);
    });

    // Suchfeld einrichten
    // Suchfeld einrichten
    $(`#search_${contentId}`).off('input focus blur').on({
        'input': function () {
            instance.state.search = $(this).val();
            instance.state.page = 1;
            scheduleReload();
        },
        'focus': function () {
            instance.isSearchFocused = true;
            // Schließe alle offenen Dropdowns
            $(`#${contentId} .ui.dropdown`).dropdown('hide');
        },
        'blur': function () {
            instance.isSearchFocused = false;
        }
    });

    // Paginierung einrichten
    $(`#pagination_${contentId} .item`).off('click').on('click', function () {
        if (!$(this).hasClass('disabled')) {
            instance.state.page = $(this).data('page');
            reloadTable(contentId);
        }
    });

    // Filter-Dropdowns einrichten
    $(`#${contentId} .ui.dropdown[id^="filter_${contentId}_"]`).each(function () {
        const $dropdown = $(this);
        // Extrahiere den Filter-Namen aus der ID und entferne eventuelle Tabellen-Präfixe
        const fullFilterName = $dropdown.attr('id').replace(`filter_${contentId}_`, '');
        const filterName = fullFilterName.includes('.') ?
            fullFilterName.split('.').pop() :
            fullFilterName;

        const isMultiple = $dropdown.hasClass('multiple');

        $dropdown.dropdown({
            fullTextSearch: $dropdown.data('full-text-search') || false,
            allowAdditions: $dropdown.data('allow-additions') || false,
            maxSelections: $dropdown.data('max-selections') || null,
            onChange: function (value, text, $selectedItem) {
                console.log(`Dropdown geändert: ${filterName}, Wert: ${value}, Original Filter: ${fullFilterName}`);

                // Speichere sowohl den Original-Filternamen als auch den Wert
                instance.state.filters[fullFilterName] = value;
                instance.state.page = 1;
                scheduleReload();
            },
            onShow: function () {
                if (isMultiple) {
                    $dropdown.addClass('keep-open');
                }
            },
            onHide: function () {
                if (isMultiple && $dropdown.hasClass('keep-open')) {
                    $dropdown.removeClass('keep-open');
                    return false;
                }
                return true;
            }
        });

        // Setze initial gespeicherte Werte
        if (instance.state.filters[fullFilterName]) {
            $dropdown.dropdown('set selected', instance.state.filters[fullFilterName]);
        }
    });


    // Modal-Trigger einrichten
    $(`#${contentId} [data-modal]`).off('click').on('click', function () {
        const modalId = $(this).data('modal');
        const $modal = $('#' + modalId);

        // Sammle nur die relevanten data-* Attribute
        const data = {};
        $.each(this.attributes, function () {
            if (this.name.startsWith('data-') &&
                !['data-modal', 'data-content', 'data-variation'].includes(this.name)) {
                const key = this.name.slice(5); // Entfernt das 'data-' Präfix
                data[key] = this.value;
            }
        });

        console.log('Collected modal data:', data); // Debugging

        // Lade den Inhalt dynamisch
        const contentUrl = $modal.data('content-url');
        const method = $modal.data('method') || 'POST'; // Standard ist POST, falls nicht anders angegeben

        $.ajax({
            url: contentUrl,
            method: method,
            data: data,
            success: function (response) {
                $modal.find('.content').html(response);

                // Fülle das Modal-Formular mit den Daten
                for (let key in data) {
                    const $field = $modal.find(`[name="${key}"]`);
                    if ($field.length) {
                        $field.val(data[key]);
                        console.log(`Feld gefüllt: ${key} = ${data[key]}`); // Debugging
                    } else {
                        console.log(`Feld nicht gefunden: ${key}`); // Debugging
                    }
                }

                // Initialisiere Formularelemente falls nötig
                $modal.find('.ui.dropdown').dropdown();
                $modal.find('.ui.checkbox').checkbox();

                // Öffne das Modal
                $modal.modal({
                    closable: false,
                    observeChanges: true,
                    closeIcon: false,
                    onApprove: function () {
                        return submitModalForm($modal, contentId);
                    },
                    onHidden: function () {
                        //Modalinhalt leeren auch Größe zurücksetzen
                        $modal.modal('refresh').find('.content').html('');
                    }
                }).modal('show');
            },
            error: function () {
                $modal.find('.content').html("<div class='ui negative message'>Fehler beim Laden des Inhalts.</div>");
                $modal.modal('show');
            }
        });
    });

    // Initialisiere Modals
    // $('.ui.modal').modal({
    //     closable: false,
    //     onApprove: function () {
    //         console.log('Modal bestätigt');
    //         return false; // Verhindert automatisches Schließen
    //     }
    // });

    // Aktualisiere den Speicherzustand-Toggle-Handler
    $(`#saveStateToggle_${contentId}`).off('change').on('change', function () {
        instance.state.saveState = $(this).is(':checked');
        if (!instance.state.saveState) {
            localStorage.removeItem(`${instance.currentUrl}_${contentId}`);
        }
        reloadTable(contentId);
    });

    // Initialisiere Popups
    $(`#${contentId} .ui.button[data-content]`).popup({
        position: 'top center',
        variation: 'tiny'
    });
}

// Funktion zum Wiederherstellen der Filter anpassen
function restoreFilters(contentId) {
    let instance = listInstances[contentId];
    for (let filterName in instance.state.filters) {
        // Berücksichtige den vollständigen Filternamen (mit Tabellenprefix)
        const $filter = $(`#filter_${contentId}_${filterName}`);
        if ($filter.length) {
            const value = instance.state.filters[filterName];
            if (value !== undefined && value !== '') {
                $filter.dropdown('set selected', value);
            }
        }
    }
}

function submitModalForm($modal, contentId) {
    const $form = $modal.find('form');
    if ($form.length) {
        $.ajax({
            url: $form.attr('action'),
            method: $form.attr('method'),
            data: new FormData($form[0]),
            processData: false,
            contentType: false,
            success: function (response) {
                console.log('Form submission response:', response); // Debugging
                if (response.success) {
                    $modal.modal('hide');
                    reloadTable(contentId);
                    showToast(response.message, 'success');
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function (xhr, status, error) {
                console.error('Form submission error:', status, error); // Debugging
                showToast('Fehler beim Speichern der Daten.', 'error');
            }
        });
    }
    return false; // Verhindert das Schließen des Modals
}

function reloadTable(contentId = null) {
    const targetContentId = contentId || currentContentId;
    if (!targetContentId) {
        console.error('Keine ContentID verfügbar. Bitte geben Sie eine an oder laden Sie zuerst eine Tabelle.');
        return;
    }
    let instance = listInstances[targetContentId];
    loadListGenerator(instance.currentUrl, instance.state);
    // Setze den Auto-Reload-Timer zurück
    setupAutoReload(targetContentId);

    // Gruppieren-Dropdown nach dem Neuladen der Tabelle erneut initialisieren
    initializeGroupByDropdown();
}

function showToast(message, type) {
    $('body').toast({
        message: message,
        class: type,
        showProgress: 'bottom',
        classProgress: type === 'success' ? 'green' : 'red'
    });
}

// Modifizierte setupAutoReload Funktion
function setupAutoReload(contentId) {
    const instance = listInstances[contentId];
    const interval = instance.state.autoReloadInterval;

    if (autoReloadTimers[contentId]) {
        clearInterval(autoReloadTimers[contentId]);
    }

    if (interval > 0) {
        console.log(`Auto-Reload für ${contentId} mit Intervall ${interval}ms eingerichtet`);
        autoReloadTimers[contentId] = setInterval(() => {
            if (!document.hidden) {
                console.log(`Auto-Reload für ${contentId} ausgeführt`);
                reloadTable(contentId);
            }
        }, interval);
    }
}

function initializeGroupByDropdown() {
    $('#groupBySelect').dropdown({
        onChange: function (value) {
            let instance = listInstances[currentContentId];
            instance.state.groupBy = value;
            instance.state.page = 1;
            reloadTable();
        }
    });
}

function setupListGenerator(contentId) {
    let instance = {
        state: {
            page: 1,
            sort: 'id',
            sortDir: 'ASC',
            search: '',
            filters: {},
            contentId: contentId
        }
    };

    // Funktion zum Neuladen der Tabelle
    function reloadTable() {
        $.ajax({
            url: window.location.href,
            method: 'GET',
            data: instance.state,
            success: function (response) {
                $(`#${contentId}`).html($(response).find(`#${contentId}`).html());
                setupListGenerator(contentId);
            },
            error: function (xhr, status, error) {
                console.error('Fehler beim Neuladen der Tabelle:', error);
            }
        });
    }

    // Setup sortierbare Spalten
    $(`#${contentId} .sortable`).off('click').on('click', function () {
        const column = $(this).data('column');
        if (instance.state.sort === column) {
            instance.state.sortDir = instance.state.sortDir === 'ASC' ? 'DESC' : 'ASC';
        } else {
            instance.state.sort = column;
            instance.state.sortDir = 'ASC';
        }
        instance.state.page = 1;
        reloadTable();
    });

    // Setup Suchfeld
    $(`#search_${contentId}`).off('input').on('input', function () {
        instance.state.search = $(this).val();
        instance.state.page = 1;
        reloadTable();
    });

    // Setup Paginierung
    $(`#pagination_${contentId} .item`).off('click').on('click', function () {
        if (!$(this).hasClass('disabled')) {
            instance.state.page = $(this).data('page');
            reloadTable();
        }
    });

    // Setup Filter-Dropdowns
    $(`#${contentId} .ui.dropdown[id^="filter_${contentId}_"]`).dropdown({
        onChange: function (value, text, $choice) {
            const filterName = $(this).attr('id').replace(`filter_${contentId}_`, '');
            instance.state.filters[filterName] = value;
            instance.state.page = 1;
            reloadTable();
        }
    });

    // Initialisiere alle Semantic UI Komponenten
    $(`#${contentId} .ui.dropdown`).dropdown();
    $(`#${contentId} .ui.checkbox`).checkbox();
}


// Initialisiere bei Document Ready
$(document).ready(function () {
    $('.ui.dropdown').dropdown();
    // Initiales Laden der ListGenerators wird von der Seite selbst aufgerufen

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            for (let contentId in listInstances) {
                setupAutoReload(contentId);
            }
        }
    });

    // Gruppieren-Dropdown initialisieren
    initializeGroupByDropdown();

});