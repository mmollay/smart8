// Globales Objekt zur Verwaltung aller ListGenerator-Instanzen
const ListGeneratorManager = {
    instances: {},

    createInstance: function (contentId, initialState = {}) {
        this.instances[contentId] = {
            state: {
                page: 1,
                sort: 'id',
                sortDir: 'ASC',
                search: '',
                filters: {},
                ...initialState
            },
            reloadTable: function () {
                const data = { ...this.state, contentId: contentId };
                $.ajax({
                    url: window.location.href,
                    method: 'GET',
                    data: data,
                    beforeSend: function () {
                        $(`#${contentId}`).addClass('loading');
                    },
                    success: function (response) {
                        $(`#${contentId}`).html($(response).find(`#${contentId}`).html());
                        ListGeneratorManager.setupEventHandlers(contentId);
                    },
                    error: function (xhr, status, error) {
                        console.error('Fehler beim Laden der Tabelle:', error);
                        $(`#${contentId}`).html('<div class="ui error message">Fehler beim Laden der Daten.</div>');
                    },
                    complete: function () {
                        $(`#${contentId}`).removeClass('loading');
                    }
                });
            }
        };
        return this.instances[contentId];
    },

    setupEventHandlers: function (contentId) {
        const instance = this.instances[contentId];

        // Sortierung
        $(`#${contentId} .sortable`).off('click').on('click', function () {
            const column = $(this).data('column');
            instance.state.sortDir = instance.state.sort === column && instance.state.sortDir === 'ASC' ? 'DESC' : 'ASC';
            instance.state.sort = column;
            instance.state.page = 1;
            instance.reloadTable();
        });

        // Suche
        const searchInput = $(`#search_${contentId}`);
        searchInput.off('input').on('input', _.debounce(function () {
            instance.state.search = $(this).val();
            instance.state.page = 1;
            instance.reloadTable();
        }, 300));

        // Paginierung
        $(`#pagination_${contentId} .item`).off('click').on('click', function () {
            if (!$(this).hasClass('disabled')) {
                instance.state.page = $(this).data('page');
                instance.reloadTable();
            }
        });

        // Filter
        $(`#${contentId} .ui.dropdown[id^="filter_${contentId}_"]`).dropdown({
            onChange: function (value, text, $choice) {
                const filterName = $(this).attr('id').replace(`filter_${contentId}_`, '');
                instance.state.filters[filterName] = value;
                instance.state.page = 1;
                instance.reloadTable();
            }
        });

        // Initialisiere Semantic UI Komponenten
        $(`#${contentId} .ui.dropdown`).dropdown();
        $(`#${contentId} .ui.checkbox`).checkbox();
    },

    init: function (contentId, initialState = {}) {
        const instance = this.createInstance(contentId, initialState);
        this.setupEventHandlers(contentId);
        return instance;
    }
};