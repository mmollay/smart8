// Global variables
let listInstances = {};

function loadListGenerator(url, customState = {}) {
    const contentId = customState.contentId || 'content';

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
                contentId: contentId
            }
        };
    }

    let instance = listInstances[contentId];
    instance.currentUrl = url;

    // Load saved state from localStorage only if saveState is true in customState or not specified
    const savedState = (customState.saveState !== false) ? JSON.parse(localStorage.getItem(`${url}_${contentId}`)) || {} : {};

    // Merge the default state, saved state, and custom state
    instance.state = {
        ...instance.state,
        ...savedState,
        ...customState
    };

    console.log(`Sending AJAX request for ${contentId} with state:`, instance.state);

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
            console.log(`AJAX request successful for ${contentId}`);
            if (instance.isSearchFocused) {
                $(`#search_${contentId}`).focus();
                var searchInput = $(`#search_${contentId}`)[0];
                if (searchInput) {
                    searchInput.selectionStart = searchInput.selectionEnd = searchInput.value.length;
                }
            }

            // Restore search input value
            $(`#search_${contentId}`).val(instance.state.search);

            // Restore filter values
            restoreFilters(contentId);

            // Save state to localStorage only if saveState is true
            if (instance.state.saveState) {
                localStorage.setItem(`${url}_${contentId}`, JSON.stringify(instance.state));
            }
        },
        error: function (xhr, status, error) {
            $(`#${contentId}`).html("<div class='ui negative message'>Fehler beim Laden der Daten.</div>");
            console.error(`AJAX request failed for ${contentId}:`, status, error);
        }
    });
}

function reloadTable(contentId) {
    let instance = listInstances[contentId];
    loadListGenerator(instance.currentUrl, instance.state);
}

function setupEventHandlers(contentId) {
    let instance = listInstances[contentId];

    // Setup sortable columns
    $(`#${contentId} .sortable`).off('click').on('click', function () {
        const column = $(this).data('column');
        console.log(`Clicked column for ${contentId}:`, column);

        if (instance.state.sort === column) {
            instance.state.sortDir = instance.state.sortDir === 'ASC' ? 'DESC' : 'ASC';
        } else {
            instance.state.sort = column;
            instance.state.sortDir = 'ASC';
        }
        instance.state.page = 1;
        console.log(`Updated state for ${contentId}:`, instance.state);
        reloadTable(contentId);
    });

    // Setup search field
    $(`#search_${contentId}`).off('input focus blur').on({
        'input': function () {
            instance.state.search = $(this).val();
            instance.state.page = 1;
            reloadTable(contentId);
        },
        'focus': function () {
            instance.isSearchFocused = true;
        },
        'blur': function () {
            instance.isSearchFocused = false;
        }
    });

    // Setup pagination
    $(`#pagination_${contentId} .item`).off('click').on('click', function () {
        if (!$(this).hasClass('disabled')) {
            instance.state.page = $(this).data('page');
            reloadTable(contentId);
        }
    });

    // Setup filter dropdowns
    let filterTimeout;
    $(`#${contentId} .ui.dropdown[id^="filter_${contentId}_"]`).dropdown({
        clearable: true,
        onChange: function (value, text, $choice) {
            const filterName = $(this).attr('id').replace(`filter_${contentId}_`, '');
            if (instance.state.filters[filterName] !== value) {
                instance.state.filters[filterName] = value;
                instance.state.page = 1;

                // Clear any existing timeout
                clearTimeout(filterTimeout);

                // Set a new timeout
                filterTimeout = setTimeout(() => {
                    reloadTable(contentId);
                }, 300); // 300ms delay
            }
        }
    });

    // Setup modal triggers
    $(`#${contentId} [data-modal]`).off('click').on('click', function () {
        const modalId = $(this).data('modal');
        const $modal = $('#' + modalId);

        // Collect all data-* attributes
        const data = $(this).data();
        delete data.modal; // Remove the 'modal' attribute from the data

        // Load the content dynamically
        const contentUrl = $modal.data('content-url');
        const method = $modal.data('method') || 'POST'; // Default to POST if not specified

        $.ajax({
            url: contentUrl,
            method: method,
            data: method === 'GET' ? data : JSON.stringify(data),
            contentType: method === 'GET' ? 'application/x-www-form-urlencoded; charset=UTF-8' : 'application/json',
            success: function (response) {
                $modal.find('.content').html(response);

                // Fill the modal form with the data
                for (let key in data) {
                    $modal.find(`[name="${key}"]`).val(data[key]);
                }

                // Initialize form elements if necessary
                $modal.find('.ui.dropdown').dropdown();
                $modal.find('.ui.checkbox').checkbox();

                // Open the modal
                $modal.modal({
                    closable: false,
                    observeChanges: true,
                    onApprove: function () {
                        return submitModalForm($modal, contentId);
                    }
                }).modal('show');
            },
            error: function () {
                $modal.find('.content').html("<div class='ui negative message'>Fehler beim Laden des Inhalts.</div>");
                $modal.modal('show');
            }
        });
    });

    // Initialize modals
    $('.ui.modal').modal({
        closable: false,
        onApprove: function () {
            console.log('Modal approved');
            return false; // Prevents automatic closing
        }
    });

    // Update the save state toggle handler
    $(`#saveStateToggle_${contentId}`).off('change').on('change', function () {
        instance.state.saveState = $(this).is(':checked');
        if (!instance.state.saveState) {
            localStorage.removeItem(`${instance.currentUrl}_${contentId}`);
        }
        reloadTable(contentId);
    });
}

function restoreFilters(contentId) {
    let instance = listInstances[contentId];
    for (let filterName in instance.state.filters) {
        const $filter = $(`#filter_${contentId}_${filterName}`);
        if ($filter.length) {
            $filter.dropdown('set selected', instance.state.filters[filterName]);
        }
    }
}

function submitModalForm($modal, contentId) {
    const $form = $modal.find('form');
    if ($form.length) {
        $.ajax({
            url: $form.attr('action'),
            method: $form.attr('method'),
            data: $form.serialize(),
            success: function (response) {
                if (response.success) {
                    $modal.modal('hide');
                    reloadTable(contentId);
                } else {
                    // Show error messages
                    $modal.find('.content').html(response);
                }
            },
            error: function () {
                $modal.find('.content').html("<div class='ui negative message'>Fehler beim Speichern der Daten.</div>");
            }
        });
    }
    return false; // Prevents the modal from closing
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

    // Setup sortable columns
    $(`#${contentId} .sortable`).on('click', function () {
        const column = $(this).data('column');
        if (instance.state.sort === column) {
            instance.state.sortDir = instance.state.sortDir === 'ASC' ? 'DESC' : 'ASC';
        } else {
            instance.state.sort = column;
            instance.state.sortDir = 'ASC';
        }
        instance.state.page = 1;
        reloadTable(contentId);
    });

    // Setup search field
    $(`#search_${contentId}`).on('input', function () {
        instance.state.search = $(this).val();
        instance.state.page = 1;
        reloadTable(contentId);
    });

    // Setup pagination
    $(`#pagination_${contentId} .item`).on('click', function () {
        if (!$(this).hasClass('disabled')) {
            instance.state.page = $(this).data('page');
            reloadTable(contentId);
        }
    });

    // Setup filter dropdowns
    $(`#${contentId} .ui.dropdown[id^="filter_${contentId}_"]`).dropdown({
        onChange: function (value, text, $choice) {
            const filterName = $(this).attr('id').replace(`filter_${contentId}_`, '');
            instance.state.filters[filterName] = value;
            instance.state.page = 1;
            reloadTable(contentId);
        }
    });

    function reloadTable(contentId) {
        $.ajax({
            url: window.location.href,
            method: 'GET',
            data: instance.state,
            success: function (response) {
                $(`#${contentId}`).html($(response).find(`#${contentId}`).html());
                setupListGenerator(contentId);
            }
        });
    }
}

// Initialize on document ready
$(document).ready(function () {
    $('.ui.dropdown').dropdown();
    // Initial load of the list generators will be called from the page itself
});