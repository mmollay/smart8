
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
