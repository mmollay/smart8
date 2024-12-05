/**
 * Optimierte ListGenerator.js
 */
class ListGenerator {
    static instances = new Map();

    constructor(options = {}) {
        this.state = {
            page: 1,
            sort: 'id',
            sortDir: 'ASC',
            search: '',
            filters: {},
            saveState: true,
            contentId: options.contentId || 'content',
            autoReloadInterval: options.autoReloadInterval || 0
        };

        this.currentUrl = null;
        this.reloadTimeout = null;
        this.autoReloadTimer = null;
        this.isSearchFocused = false;

        ListGenerator.instances.set(this.state.contentId, this);
    }

    static getInstance(contentId) {
        return ListGenerator.instances.get(contentId);
    }

    async load(url, customState = {}) {
        this.currentUrl = url;

        // Lade gespeicherten Zustand
        if (this.state.saveState) {
            const savedState = this.loadSavedState(url);
            Object.assign(this.state, savedState);
        }

        // Überschreibe mit customState
        Object.assign(this.state, customState);

        try {
            const response = await this.fetchData(url);
            this.render(response);
            this.setupEventHandlers();
            this.restoreFilters();
            this.setupAutoReload();

            if (this.state.saveState) {
                this.saveState();
            }
        } catch (error) {
            this.handleError(error);
        }
    }

    async fetchData(url) {
        const response = await $.ajax({
            url,
            method: "GET",
            data: this.state
        });
        return response;
    }

    render(content) {
        const $container = $(`#${this.state.contentId}`);
        $container.html(content);

        if (this.isSearchFocused) {
            this.focusSearch();
        }
    }

    setupEventHandlers() {
        this.setupSortHandlers();
        this.setupSearchHandler();
        this.setupPaginationHandler();
        this.setupFilterHandlers();
        this.setupModalHandlers();
    }

    setupSortHandlers() {
        $(`#${this.state.contentId} .sortable`).on('click', (e) => {
            const column = $(e.currentTarget).data('column');

            if (this.state.sort === column) {
                this.state.sortDir = this.state.sortDir === 'ASC' ? 'DESC' : 'ASC';
            } else {
                this.state.sort = column;
                this.state.sortDir = 'ASC';
            }

            this.state.page = 1;
            this.reload();
        });
    }

    setupSearchHandler() {
        const searchInput = $(`#search_${this.state.contentId}`);

        searchInput.on({
            'input': (e) => {
                this.state.search = e.target.value;
                this.state.page = 1;
                this.scheduleReload();
            },
            'focus': () => {
                this.isSearchFocused = true;
                $(`#${this.state.contentId} .ui.dropdown`).dropdown('hide');
            },
            'blur': () => {
                this.isSearchFocused = false;
            }
        });
    }

    scheduleReload() {
        clearTimeout(this.reloadTimeout);
        this.reloadTimeout = setTimeout(() => this.reload(), 300);
    }

    reload() {
        this.load(this.currentUrl, this.state);
    }

    setupAutoReload() {
        if (this.autoReloadTimer) {
            clearInterval(this.autoReloadTimer);
        }

        if (this.state.autoReloadInterval > 0) {
            this.autoReloadTimer = setInterval(() => {
                if (!document.hidden) {
                    this.reload();
                }
            }, this.state.autoReloadInterval);
        }
    }

    setupModalHandlers() {
        const $content = $(`#${this.state.contentId}`);

        $content.find('[data-modal]').on('click', async (e) => {
            const $trigger = $(e.currentTarget);
            const modalId = $trigger.data('modal');
            const $modal = $(`#${modalId}`);

            try {
                await this.loadModalContent($modal, this.collectModalData($trigger));
                this.initializeModal($modal);
            } catch (error) {
                this.handleModalError($modal, error);
            }
        });
    }

    async loadModalContent($modal, data) {
        const contentUrl = $modal.data('content-url');
        const method = $modal.data('method') || 'POST';

        const response = await $.ajax({
            url: contentUrl,
            method,
            data
        });

        $modal.find('.content').html(response);
        return response;
    }

    initializeModal($modal) {
        $modal.modal({
            closable: false,
            observeChanges: true,
            onApprove: () => this.handleModalSubmit($modal),
            onHidden: () => {
                $modal.modal('refresh').find('.content').html('');
            }
        }).modal('show');

        // Initialisiere Semantic UI Komponenten
        $modal.find('.ui.dropdown').dropdown();
        $modal.find('.ui.checkbox').checkbox();
    }

    async handleModalSubmit($modal) {
        const $form = $modal.find('form');
        if (!$form.length) return true;

        try {
            const response = await $.ajax({
                url: $form.attr('action'),
                method: $form.attr('method'),
                data: new FormData($form[0]),
                processData: false,
                contentType: false
            });

            if (response.success) {
                this.showToast(response.message, 'success');
                $modal.modal('hide');
                this.reload();
            } else {
                this.showToast(response.message, 'error');
            }
        } catch (error) {
            this.showToast('Fehler beim Speichern der Daten.', 'error');
            console.error('Formular-Übermittlungsfehler:', error);
        }

        return false;
    }

    showToast(message, type) {
        $('body').toast({
            message,
            class: type,
            showProgress: 'bottom',
            classProgress: type === 'success' ? 'green' : 'red'
        });
    }

    // Methode zum Laden des gespeicherten Zustands
    loadSavedState(url) {
        try {
            const savedState = localStorage.getItem(`${url}_${this.state.contentId}`);
            return savedState ? JSON.parse(savedState) : {};
        } catch (error) {
            console.error('Fehler beim Laden des gespeicherten Zustands:', error);
            return {};
        }
    }

    // Methode zum Speichern des Zustands
    saveState() {
        try {
            localStorage.setItem(
                `${this.currentUrl}_${this.state.contentId}`,
                JSON.stringify(this.state)
            );
        } catch (error) {
            console.error('Fehler beim Speichern des Zustands:', error);
        }
    }

    // Methode zum Wiederherstellen der Filter
    restoreFilters() {
        for (let filterName in this.state.filters) {
            const $filter = $(`#filter_${this.state.contentId}_${filterName}`);
            if ($filter.length) {
                const value = this.state.filters[filterName];
                if (value !== undefined && value !== '') {
                    $filter.dropdown('set selected', value);
                }
            }
        }
    }

    // Methode zur Fehlerbehandlung
    handleError(error) {
        console.error('ListGenerator Fehler:', error);
        $(`#${this.state.contentId}`).html(
            '<div class="ui negative message">Fehler beim Laden der Daten.</div>'
        );
        this.showToast('Fehler beim Laden der Daten', 'error');
    }

    // Methode zum Fokussieren der Suche
    focusSearch() {
        const $search = $(`#search_${this.state.contentId}`);
        $search.focus();
        const searchInput = $search[0];
        if (searchInput) {
            searchInput.selectionStart = searchInput.selectionEnd = searchInput.value.length;
        }
    }

    // Hilfsmethode zum Sammeln von Modal-Daten
    collectModalData($trigger) {
        const data = {};
        $.each($trigger[0].attributes, function () {
            if (this.name.startsWith('data-') &&
                !['data-modal', 'data-content', 'data-variation'].includes(this.name)) {
                const key = this.name.slice(5); // Entfernt das 'data-' Präfix
                data[key] = this.value;
            }
        });
        return data;
    }

    // Methode zur Modal-Fehlerbehandlung
    handleModalError($modal, error) {
        console.error('Modal Fehler:', error);
        $modal.find('.content').html(
            '<div class="ui negative message">Fehler beim Laden des Modal-Inhalts.</div>'
        );
        $modal.modal('show');
    }

    // Hilfsmethode zum Überprüfen des Status
    isLoading() {
        return this.reloadTimeout !== null;
    }

    setupPaginationHandler() {
        $(`#pagination_${this.state.contentId} .item`).off('click').on('click', (e) => {
            const $item = $(e.currentTarget);
            if (!$item.hasClass('disabled')) {
                this.state.page = $item.data('page');
                this.reload();
            }
        });
    }

    setupFilterHandlers() {
        $(`#${this.state.contentId} .ui.dropdown[id^="filter_${this.state.contentId}_"]`).each((_, element) => {
            const $dropdown = $(element);
            const fullFilterName = $dropdown.attr('id').replace(`filter_${this.state.contentId}_`, '');
            const isMultiple = $dropdown.hasClass('multiple');

            $dropdown.dropdown({
                fullTextSearch: $dropdown.data('full-text-search') || false,
                allowAdditions: $dropdown.data('allow-additions') || false,
                maxSelections: $dropdown.data('max-selections') || null,
                onChange: (value) => {
                    this.state.filters[fullFilterName] = value;
                    this.state.page = 1;
                    this.scheduleReload();
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
            if (this.state.filters[fullFilterName]) {
                $dropdown.dropdown('set selected', this.state.filters[fullFilterName]);
            }
        });
    }

    // Zusätzliche Hilfsmethoden
    updatePaginationUI() {
        const $pagination = $(`#pagination_${this.state.contentId}`);
        $pagination.find('.item').removeClass('active');
        $pagination.find(`.item[data-page="${this.state.page}"]`).addClass('active');
    }

    setupGroupByDropdown() {
        $('#groupBySelect').dropdown({
            onChange: (value) => {
                this.state.groupBy = value;
                this.state.page = 1;
                this.reload();
            }
        });
    }

    clearFilters() {
        this.state.filters = {};
        this.state.page = 1;
        $(`#${this.state.contentId} .ui.dropdown`).dropdown('clear');
        this.reload();
    }

    resetSearch() {
        this.state.search = '';
        $(`#search_${this.state.contentId}`).val('');
        this.state.page = 1;
        this.reload();
    }
}

// Globale Initialisierung
$(document).ready(() => {
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            ListGenerator.instances.forEach(instance => instance.setupAutoReload());
        }
    });
});