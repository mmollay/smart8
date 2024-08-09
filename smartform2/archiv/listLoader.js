const ListLoader = {
    loadList: function (listId, options = {}) {
        const defaultOptions = {
            page: 1,
            sortColumn: 'id',
            sortDirection: 'ASC',
            search: ''
        };
        const finalOptions = { ...defaultOptions, ...options };

        $.ajax({
            url: 'load_list.php',
            method: 'GET',
            data: {
                listId: listId,
                ...finalOptions
            },
            dataType: 'json',
            success: function (response) {
                $(`#${listId} .listBody`).html(response.tableBody);
                $(`#${listId} .listPagination`).html(response.pagination);
                $(`#${listId} .listFooter`).html(response.footer);

                // Aktualisiere externe Buttons
                Object.keys(response.externalButtons).forEach(position => {
                    $(`#${listId}_${position}Buttons`).html(response.externalButtons[position]);
                });

                // Initialisiere Popups und andere UI-Elemente
                $(`#${listId} [data-content]`).popup();
                $(`#${listId} .ui.dropdown`).dropdown();
            },
            error: function (xhr, status, error) {
                console.error('Fehler beim Laden der Liste:', error);
            }
        });
    },

    initializeList: function (listId) {
        // Suchfunktion
        $(`#${listId} .ui.search input`).on('input', function () {
            clearTimeout($(this).data('timeout'));
            $(this).data('timeout', setTimeout(() => {
                ListLoader.loadList(listId, { search: $(this).val(), page: 1 });
            }, 300));
        });

        // Sortierung
        $(`#${listId} th[data-sort]`).on('click', function () {
            const sortColumn = $(this).data('sort');
            const currentDirection = $(this).hasClass('ascending') ? 'ASC' : 'DESC';
            const newDirection = currentDirection === 'ASC' ? 'DESC' : 'ASC';
            ListLoader.loadList(listId, { sortColumn: sortColumn, sortDirection: newDirection, page: 1 });
        });

        // Paginierung
        $(document).on('click', `#${listId} .pagination .item`, function (e) {
            e.preventDefault();
            ListLoader.loadList(listId, { page: $(this).data('page') });
        });

        // Initiale Ladung
        ListLoader.loadList(listId);
    }
};

// Initialisierung für jede Liste
$(document).ready(function () {
    ListLoader.initializeList('senders');
    // Fügen Sie hier weitere Listen hinzu, wenn nötig
});