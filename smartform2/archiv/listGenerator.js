(function () {

    var listGenerators = {};

    function loadJQuery(callback) {
        if (typeof jQuery === 'undefined') {
            alert('test');
            var script = document.createElement('script');
            script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
            script.onload = function () {
                if (typeof $.fn.popup === 'undefined') {
                    var semanticScript = document.createElement('script');
                    semanticScript.src = 'https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.2/dist/semantic.min.js';
                    semanticScript.onload = callback;
                    document.head.appendChild(semanticScript);
                } else {
                    callback();
                }
            };
            document.head.appendChild(script);
        } else if (typeof $.fn.popup === 'undefined') {
            var semanticScript = document.createElement('script');
            semanticScript.src = 'https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.2/dist/semantic.min.js';
            semanticScript.onload = callback;
            document.head.appendChild(semanticScript);
        } else {
            callback();
        }
    }

    function initListGenerator(listId, options) {
        var searchTimer;


        initializeModals(listId);

        listGenerators[listId] = {
            currentSortColumn: options.sortColumn,
            currentSortDirection: options.sortDirection,
            showFooter: options.showFooter
        };

        $('#' + listId + ' .ui.search input').off('input').on('input', function () {
            var searchInput = $(this);
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                updateList(listId, { search: searchInput.val(), page: 1 });
            }, 300);
        });

        $('#' + listId + ' th[data-sort]').off('click').on('click', function () {
            var sortColumn = $(this).data('sort');
            var sortDirection = (sortColumn === listGenerators[listId].currentSortColumn && listGenerators[listId].currentSortDirection === 'ASC') ? 'DESC' : 'ASC';
            updateList(listId, { sortColumn: sortColumn, sortDirection: sortDirection, page: 1 });
        });

        $('#' + listId + ' .ui.dropdown').each(function () {
            var $dropdown = $(this);
            var filterName = $dropdown.data('filter');
            var settings = filterSettings[filterName].settings;

            $dropdown.dropdown({
                clearable: true,
                fullTextSearch: settings.fullTextSearch,
                allowAdditions: settings.allowAdditions,
                maxSelections: settings.maxSelections,
                onChange: function (value, text) {
                    updateList(listId, { ['filter_' + filterName]: value, page: 1 });
                    setTimeout(function () {
                        $dropdown.find('input.search').val('');
                    }, 50);
                },
                onHide: function () {
                    $dropdown.find('input.search').val('');
                }
            });
        });

        initPagination(listId);
        updateSortIndicators(listId);
        initializeButtons(listId);
        initializePopups(listId);
        initializeModals(listId);

        restoreState(listId);
        clearDropdownSearchInputs(listId);
    }

    function restoreState(listId) {
        var urlParams = new URLSearchParams(window.location.search);

        $('#' + listId + ' .ui.dropdown').each(function () {
            var $dropdown = $(this);
            var filterName = $dropdown.data('filter');
            var savedValue = urlParams.get('filter_' + filterName);
            if (savedValue) {
                var values = savedValue.split(',');
                setTimeout(function () {
                    $dropdown.dropdown('clear');
                    values.forEach(function (value) {
                        $dropdown.dropdown('set selected', value);
                    });
                    $dropdown.find('input.search').val('');
                }, 0);
            }
        });

        var savedSearch = urlParams.get('search');
        if (savedSearch) {
            $('#' + listId + ' .ui.search input').val(savedSearch);
        }

        var savedSortColumn = urlParams.get('sortColumn');
        var savedSortDirection = urlParams.get('sortDirection');
        if (savedSortColumn && savedSortDirection) {
            listGenerators[listId].currentSortColumn = savedSortColumn;
            listGenerators[listId].currentSortDirection = savedSortDirection;
        }

        updateList(listId, {});
    }

    function updateList(listId, newOptions) {
        var currentUrl = new URL(window.location.href);
        var params = new URLSearchParams(currentUrl.search);

        $('#' + listId + ' .ui.dropdown').each(function () {
            var filterName = $(this).data('filter');
            var filterValue = $(this).dropdown('get value');
            if (filterValue && filterValue.length > 0) {
                params.set('filter_' + filterName, Array.isArray(filterValue) ? filterValue.join(',') : filterValue);
            } else {
                params.delete('filter_' + filterName);
            }
        });

        var searchValue = $('#' + listId + ' .ui.search input').val();
        if (searchValue) {
            params.set('search', searchValue);
        } else {
            params.delete('search');
        }

        for (let key in newOptions) {
            if (newOptions.hasOwnProperty(key)) {
                if (newOptions[key] === '' || newOptions[key] === null) {
                    params.delete(key);
                } else {
                    params.set(key, newOptions[key]);
                }
            }
        }

        if (!params.has('sortColumn')) {
            params.set('sortColumn', listGenerators[listId].currentSortColumn);
        }
        if (!params.has('sortDirection')) {
            params.set('sortDirection', listGenerators[listId].currentSortDirection);
        }

        params.set('listId', listId);

        window.history.pushState({}, '', '?' + params.toString());

        $.ajax({
            url: '?' + params.toString(),
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                $('#' + listId + ' .listBody').html(data.tableBody);
                $('#' + listId + ' .listPagination').html(data.pagination);

                if (listGenerators[listId].showFooter) {
                    var footerElement = $('#' + listId + ' .listFooter');
                    if (footerElement.length > 0) {
                        footerElement.find('td').html(data.footer);
                    } else {
                        $('#' + listId + ' table').append('<tfoot class="listFooter"><tr><td colspan="' + data.totalColumns + '">' + data.footer + '</td></tr></tfoot>');
                    }
                } else {
                    $('#' + listId + ' .listFooter').remove();
                }

                listGenerators[listId].currentSortColumn = data.sortColumn;
                listGenerators[listId].currentSortDirection = data.sortDirection;
                updateSortIndicators(listId);
                initializeButtons(listId);
                initPagination(listId);
                initializePopups(listId);
                initializeModals(listId);
                clearDropdownSearchInputs(listId);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Error updating list:', textStatus, errorThrown);
            }
        });
    }

    function updateSortIndicators(listId) {
        $('#' + listId + ' th[data-sort]').each(function () {
            var th = $(this);
            th.removeClass('sorted ascending descending');
            th.find('.sort-icon').remove();

            if (th.data('sort') === listGenerators[listId].currentSortColumn) {
                var isAscending = listGenerators[listId].currentSortDirection.toLowerCase() === 'asc';
                th.addClass('sorted ' + (isAscending ? 'ascending' : 'descending'));
                th.append('<span class="sort-icon">' + (isAscending ? ' ▲' : ' ▼') + '</span>');
            }
        });
    }

    function initializeButtons(listId) {
        $('#' + listId + ' .listBody button').each(function () {
            var button = $(this);
            var confirm = button.data('confirm');

            button.off('click').on('click', function (e) {
                e.preventDefault();
                if (confirm && !window.confirm(confirm)) {
                    return;
                }

                var callback = button.attr('onclick');
                if (callback) {
                    new Function(callback)();
                }
            });
        });
    }

    function initPagination(listId) {
        $('#' + listId + ' .pagination .item').off('click').on('click', function (e) {
            e.preventDefault();
            updateList(listId, { page: $(this).data('page') });
        });
    }

    function initializePopups(listId) {
        $('#' + listId + ' [data-content]').each(function () {
            var el = $(this);
            var options = {
                content: el.data('content'),
                position: el.data('position') || 'top center',
                variation: el.data('variation') || 'inverted',
                hoverable: el.data('hoverable') || false,
                inline: el.data('inline') || false,
                on: el.data('on') || 'hover',
                boundary: el.data('boundary') || 'window',
                delay: el.data('delay') || { show: 50, hide: 0 }
            };
            if (el.data('class')) {
                options.className = {
                    popup: el.data('class')
                };
            }
            el.popup(options);
        });
    }

    function initializeModals(listId) {
        console.log('Initializing modals for', listId);

        $('#' + listId + ' button[data-modal-id]').off('click').on('click', function (e) {
            e.preventDefault();
            var modalId = listId + '_modal_' + $(this).data('modal-id');
            var modal = $('#' + modalId);
            var contentUrl = modal.data('content');
            var params = $(this).data('params');

            console.log('Modal button clicked', modalId, contentUrl, params);
            console.log('Button params:', params);

            if (contentUrl) {
                var queryParams = $.param(params);
                var fullUrl = contentUrl + '?' + queryParams;

                console.log('Full URL:', fullUrl);

                modal.find('.content').html("<div class='ui active loader'></div>");
                modal.modal('show');

                $.ajax({
                    url: fullUrl,
                    method: 'GET',
                    success: function (response) {
                        modal.find('.content').html(response);
                        console.log('Modal content loaded successfully');
                    },
                    error: function (xhr, status, error) {
                        console.error("Error loading modal content:", xhr.status, error);
                        modal.find('.content').html("<div class='ui negative message'><div class='header'>Fehler</div><p>Beim Laden des Inhalts ist ein Fehler aufgetreten.</p></div>");
                    }
                });
            } else {
                console.log('No content URL, showing modal directly');
                modal.modal('show');
            }
        });

        $('.ui.modal .actions .button').off('click').on('click', function () {
            var action = $(this).data('action');
            var modal = $(this).closest('.ui.modal');

            console.log('Modal action clicked', action);

            if (action === 'close') {
                modal.modal('hide');
            } else if (action === 'save') {
                console.log('Save action triggered');
                modal.modal('hide');
            }
        });

        $(document).on('click', '.ui.message .close', function () {
            $(this).closest('.ui.message').transition('fade');
        });
    }

    function clearDropdownSearchInputs(listId) {
        $('#' + listId + ' .ui.dropdown').each(function () {
            $(this).find('input.search').val('');
        });
    }

    function initializeAllListGenerators() {
        $('.list-generator').each(function () {
            var listId = $(this).attr('id');
            var options = $(this).data('options');
            initListGenerator(listId, options);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadJQuery(function () {
            initializeAllListGenerators();

        });
    });

    // Öffentliche Funktionen
    window.initializeListGenerator = function (listId, options) {
        initListGenerator(listId, options);
    };

    window.reloadListGenerator = function (listId, options = {}) {
        if (listGenerators.hasOwnProperty(listId)) {
            updateList(listId, options);
        } else {
            console.error('ListGenerator with ID ' + listId + ' not found.');
        }
    };
})();