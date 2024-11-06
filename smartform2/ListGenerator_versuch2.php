
<?php

/**
 * ListGenerator - Optimierte Version
 * 
 * Hauptoptimierungen:
 * - Einführung von Dependency Injection
 * - Verbesserte Cache-Strategie mit Interface
 * - Optimierte Datenbankabfragen
 * - Reduzierte Komplexität
 * - Verbesserte Performance
 */

interface CacheInterface
{



    public function get(string $key): ?array;
    public function set(string $key, array $value, int $ttl = 300): void;
    public function delete(string $key): void;
    public function clear(): void;
}


class ListGenerator
{



    // Ergänze diese Properties am Anfang der Klasse
    private string $query = '';
    private array $data = [];
    private array $queryParams = [];
    private array $customWhere = [];

    private CacheInterface $cache;
    private array $config;
    private ?PDO $db;
    private array $columns = [];
    private array $filters = [];
    private array $searchableColumns = [];
    private ?string $groupBy = null;
    private array $totals = [];
    private array $errors = [];
    private array $eventHandlers = [];
    private array $buttons = [];
    private array $modals = [];
    private int $totalRows = 0;

    private array $totalTypes = [];
    private bool $debug = false;

    private array $totalLabels = [];
    private array $groupByOptions = [];

    /**
     * Debug Log Methode
     */
    private function debugLog(string $message, array $data = []): void
    {
        if (!$this->config['debug']) {
            return;
        }

        $logEntry = [
            'time' => date('Y-m-d H:i:s'),
            'message' => $message,
            'data' => $data
        ];

        error_log(print_r($logEntry, true));
    }

    /**
     * Berechnet die Summe/Durchschnitt/etc. für eine Spalte
     */
    private function calculateTotal(array $values, string $type): float|int
    {
        if (empty($values)) {
            return 0;
        }

        // Nur numerische Werte verwenden
        $values = array_filter($values, 'is_numeric');

        if (empty($values)) {
            return 0;
        }

        switch ($type) {
            case 'sum':
                return array_sum($values);

            case 'avg':
                return array_sum($values) / count($values);

            case 'count':
                return count($values);

            case 'min':
                return min($values);

            case 'max':
                return max($values);

            default:
                return 0;
        }
    }

    /**
     * Total-Wert formatieren
     */
    private function formatTotalValue($value, string $type): string
    {
        switch ($type) {
            case 'sum':
            case 'avg':
                return number_format($value, 2, ',', '.');
            case 'count':
                return number_format($value, 0, ',', '.');
            default:
                return (string) $value;
        }
    }

    /**
     * Select-Filter rendern
     */
    private function renderSelectFilter(string $key, array $filter): string
    {
        $output = new StringBuilder();

        $currentValue = $_GET['filters'][$key] ?? '';

        $output->append(sprintf(
            '<select name="filters[%s]" class="ui dropdown">',
            $this->escapeHtml($key)
        ));

        // Placeholder Option
        $output->append(sprintf(
            '<option value="">%s</option>',
            $this->escapeHtml($filter['placeholder'])
        ));

        // Filter Optionen
        foreach ($filter['options'] as $value => $label) {
            $selected = $value == $currentValue ? ' selected' : '';
            $output->append(sprintf(
                '<option value="%s"%s>%s</option>',
                $this->escapeHtml($value),
                $selected,
                $this->escapeHtml($label)
            ));
        }

        $output->append('</select>');
        return $output->toString();
    }

    /**
     * Text-Filter rendern
     */
    private function renderTextFilter(string $key, array $filter): string
    {
        $currentValue = $_GET['filters'][$key] ?? '';

        return sprintf(
            '<input type="text" name="filters[%s]" value="%s" placeholder="%s" class="ui input">',
            $this->escapeHtml($key),
            $this->escapeHtml($currentValue),
            $this->escapeHtml($filter['placeholder'])
        );
    }

    /**
     * Datums-Filter rendern
     */
    private function renderDateFilter(string $key, array $filter): string
    {
        $currentValue = $_GET['filters'][$key] ?? '';

        return sprintf(
            '<input type="date" name="filters[%s]" value="%s" class="ui input">',
            $this->escapeHtml($key),
            $this->escapeHtml($currentValue)
        );
    }

    /**
     * Summen-Spalte hinzufügen
     */
    public function addTotal(string $column, string $type = 'sum', string $label = ''): void
    {
        $this->totals[$column] = 0;
        $this->totalTypes[$column] = $type;
        if ($label) {
            $this->totalLabels[$column] = $label;
        }
    }

    /**
     * Gruppierung setzen
     */
    public function setGroupBy(string $column, array $options = []): void
    {
        $this->groupBy = $column;
        $this->groupByOptions = $options;
    }

    /**
     * Custom WHERE-Bedingung hinzufügen
     */
    public function addCustomWhere(string $condition, array $params = []): void
    {
        $this->customWhere[] = [
            'condition' => $condition,
            'params' => $params
        ];
    }

    public function __construct(
        array $config,
        CacheInterface $cache,
        ?PDO $db = null
    ) {
        $this->cache = $cache;
        $this->db = $db;
        $this->config = $this->getDefaultConfig() + $config;

        if ($this->config['enableCSRF']) {
            $this->initializeCSRF();
        }
    }

    /**
     * Basis-Query setzen
     */
    public function setQuery(string $query): void
    {
        $this->query = $query;
    }

    /**
     * Array-Daten setzen
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Offset für Paginierung berechnen
     */
    private function calculateOffset(): int
    {
        $page = max(1, (int) ($this->config['page'] ?? 1));
        return ($page - 1) * $this->config['itemsPerPage'];
    }

    /**
     * CSS-Klassen für Tabelle zusammenbauen
     */
    private function getTableClasses(): string
    {
        $classes = [
            $this->config['tableClasses'],
            $this->config['celled'] ? 'celled' : '',
            $this->config['color'] ?? '',
            $this->config['size'] ?? '',
        ];
        return implode(' ', array_filter($classes));
    }

    /**
     * Prüfen ob Summenzeile benötigt wird
     */
    private function hasTotals(): bool
    {
        return !empty($this->totals);
    }

    /**
     * Summenzeile rendern
     */
    private function renderTotalsRow(array $data): string
    {
        if (!$this->hasTotals()) {
            return '';
        }

        $output = new StringBuilder();
        $output->append('<tfoot><tr>');

        foreach ($this->columns as $key => $column) {
            $value = '';
            if (isset($this->totals[$key])) {
                $type = $this->totalTypes[$key] ?? 'sum';
                $value = $this->calculateTotal(
                    array_column($data, $key),
                    $type
                );
                $value = $this->formatTotalValue($value, $type);
            }
            $output->append("<td>$value</td>");
        }

        $output->append('</tr></tfoot>');
        return $output->toString();
    }

    /**
     * CSS-Klassen für Sortierung
     */
    private function getSortClass(string $column): string
    {
        if (!($this->columns[$column]['sortable'] ?? false)) {
            return '';
        }

        $classes = ['sortable'];
        if ($this->config['sortColumn'] === $column) {
            $classes[] = strtolower($this->config['sortDirection']);
            $classes[] = 'sorted';
        }

        return implode(' ', $classes);
    }

    /**
     * Paginierung rendern
     */
    private function renderPagination(): string
    {
        if (!$this->config['showPagination']) {
            return '';
        }

        $totalPages = ceil($this->totalRows / $this->config['itemsPerPage']);
        if ($totalPages <= 1) {
            return '';
        }

        $currentPage = max(1, min($this->config['page'], $totalPages));

        $output = new StringBuilder();
        $output->append('<div class="ui pagination menu">');

        // Erste & Vorherige Seite
        if ($currentPage > 1) {
            $output->append($this->renderPaginationLink(1, '«', 'First'))
                ->append($this->renderPaginationLink($currentPage - 1, '‹', 'Previous'));
        }

        // Seitenzahlen
        for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++) {
            $active = $i === $currentPage ? ' active' : '';
            $output->append($this->renderPaginationLink($i, $i, '', $active));
        }

        // Nächste & Letzte Seite
        if ($currentPage < $totalPages) {
            $output->append($this->renderPaginationLink($currentPage + 1, '›', 'Next'))
                ->append($this->renderPaginationLink($totalPages, '»', 'Last'));
        }

        $output->append('</div>');
        return $output->toString();
    }

    /**
     * Einzelnen Pagination-Link rendern
     */
    private function renderPaginationLink(int $page, string $text, string $title = '', string $class = ''): string
    {
        $url = $this->buildUrl(['page' => $page]);
        return sprintf(
            '<a class="item%s" href="%s" title="%s">%s</a>',
            $class,
            $url,
            $title,
            $text
        );
    }

    /**
     * Filter-Bereich rendern
     */
    private function renderFilters(): string
    {
        if (empty($this->filters)) {
            return '';
        }

        $output = new StringBuilder();
        $output->append('<div class="ui form filter-form">');
        $output->append('<div class="fields">');

        foreach ($this->filters as $key => $filter) {
            $output->append($this->renderFilter($key, $filter));
        }

        $output->append('</div></div>');
        return $output->toString();
    }

    /**
     * Einzelnes Filter-Element rendern
     */
    private function renderFilter(string $key, array $filter): string
    {
        $output = new StringBuilder();
        $output->append('<div class="field">');

        // Label
        if (!empty($filter['label'])) {
            $output->append(sprintf(
                '<label>%s</label>',
                $this->escapeHtml($filter['label'])
            ));
        }

        // Filter-Element basierend auf Typ
        switch ($filter['type']) {
            case 'select':
                $output->append($this->renderSelectFilter($key, $filter));
                break;
            case 'text':
                $output->append($this->renderTextFilter($key, $filter));
                break;
            case 'date':
                $output->append($this->renderDateFilter($key, $filter));
                break;
        }

        $output->append('</div>');
        return $output->toString();
    }

    /**
     * Prüft ob ein Wert dem Filter entspricht
     */
    private function matchesFilter($item, $key, $value, $filterConfig): bool
    {
        if (!isset($item[$key])) {
            return false;
        }

        $itemValue = $item[$key];
        $operator = $filterConfig['operator'] ?? '=';

        switch ($operator) {
            case '=':
                return $itemValue == $value;
            case '>':
                return $itemValue > $value;
            case '<':
                return $itemValue < $value;
            case '>=':
                return $itemValue >= $value;
            case '<=':
                return $itemValue <= $value;
            case 'LIKE':
                return stripos($itemValue, $value) !== false;
            case 'IN':
                return in_array($itemValue, (array) $value);
            case 'BETWEEN':
                if (!is_array($value) || count($value) !== 2) {
                    return false;
                }
                return $itemValue >= $value[0] && $itemValue <= $value[1];
            default:
                return false;
        }
    }

    /**
     * URL mit Parametern erstellen
     */
    private function buildUrl(array $params = []): string
    {
        $currentParams = $_GET;
        unset($currentParams['page']); // Seite zurücksetzen bei neuen Parametern

        $urlParams = array_merge($currentParams, $params);
        return '?' . http_build_query($urlParams);
    }

    private function getDefaultConfig(): array
    {
        return [
            'debug' => false,
            'listId' => 'defaultList',
            'itemsPerPage' => 10,
            'sortColumn' => 'id',
            'sortDirection' => 'ASC',
            'cacheEnabled' => true,
            'cacheTTL' => 300,
            'enableCSRF' => true,
            'enableXSSProtection' => true,
            'tableClasses' => 'ui celled table',
            'headerClasses' => 'ui table',
            'rowClasses' => '',
            'showNoDataMessage' => true,
            'noDataMessage' => 'Keine Daten gefunden.',
            'showFooter' => true
        ];
    }

    /**
     * Hauptmethode zur Listengenerierung
     */
    public function generateList(): string
    {
        try {
            $cacheKey = $this->generateCacheKey();

            if ($this->config['cacheEnabled']) {
                $cachedResult = $this->cache->get($cacheKey);
                if ($cachedResult !== null) {
                    return $cachedResult['html'];
                }
            }

            $data = $this->fetchData();
            $html = $this->renderList($data);

            if ($this->config['cacheEnabled']) {
                $this->cache->set($cacheKey, ['html' => $html], $this->config['cacheTTL']);
            }

            return $html;
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Daten abrufen
     */
    private function fetchData(): array
    {
        if ($this->db) {
            return $this->fetchFromDatabase();
        }
        return $this->fetchFromArray();
    }

    /**
     * Daten aus der Datenbank abrufen
     */
    private function fetchFromDatabase(): array
    {
        try {
            $query = $this->buildQuery();
            $stmt = $this->db->prepare($query);

            // Parameter binden
            foreach ($this->queryParams as $key => $value) {
                $stmt->bindValue(":$key", $value, $this->getParamType($value));
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            throw new ListGeneratorException(
                "Datenbankfehler: " . $e->getMessage(),
                ['query' => $query ?? null, 'params' => $this->queryParams]
            );
        }
    }


    /**
     * Daten aus Array abrufen
     */
    private function fetchFromArray(): array
    {
        $data = $this->applyFilters($this->data);
        $data = $this->applySearch($data);
        $data = $this->applySort($data);

        $this->totalRows = count($data);

        return array_slice(
            $data,
            $this->calculateOffset(),
            $this->config['itemsPerPage']
        );
    }

    /**
     * Filter anwenden
     */
    private function applyFilters(array $data): array
    {
        if (empty($_GET['filters'])) {
            return $data;
        }

        return array_filter($data, function ($item) {
            foreach ($_GET['filters'] as $key => $value) {
                if (empty($value) || !isset($this->filters[$key])) {
                    continue;
                }

                $filterConfig = $this->filters[$key];
                if (!$this->matchesFilter($item, $key, $value, $filterConfig)) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Query Parameter abrufen und validieren
     */
    private function getQueryParams(): array
    {
        try {
            $params = [];

            // Filter-Parameter
            if (!empty($_GET['filters'])) {
                $params = array_merge($params, $this->getFilterParams());
            }

            // Such-Parameter
            if (!empty($this->config['search'])) {
                $params = array_merge($params, $this->getSearchParams());
            }

            // Custom WHERE Parameter
            if (!empty($this->customWhere)) {
                $params = array_merge($params, $this->getCustomWhereParams());
            }

            // Validierung aller Parameter
            return $this->validateQueryParams($params);

        } catch (Exception $e) {
            throw new ListGeneratorException(
                "Fehler bei der Parameter-Verarbeitung: " . $e->getMessage(),
                ['params' => $params ?? []]
            );
        }
    }

    /**
     * Parameter für Filter extrahieren
     */
    private function getFilterParams(): array
    {
        $params = [];

        foreach ($_GET['filters'] as $column => $value) {
            if (!isset($this->filters[$column]) || $value === '') {
                continue;
            }

            $filter = $this->filters[$column];
            $operator = $filter['operator'] ?? '=';

            switch ($operator) {
                case 'LIKE':
                    $params["filter_$column"] = '%' . $value . '%';
                    break;

                case 'IN':
                    if (!is_array($value)) {
                        $value = explode(',', $value);
                    }
                    foreach ($value as $i => $v) {
                        $params["filter_{$column}_{$i}"] = $this->sanitizeValue($v);
                    }
                    break;

                case 'BETWEEN':
                    if (is_array($value) && count($value) === 2) {
                        $params["filter_{$column}_start"] = $this->sanitizeValue($value[0]);
                        $params["filter_{$column}_end"] = $this->sanitizeValue($value[1]);
                    }
                    break;

                case 'IS NULL':
                case 'IS NOT NULL':
                    // Keine Parameter nötig
                    break;

                default:
                    $params["filter_$column"] = $this->sanitizeValue($value);
            }
        }

        return $params;
    }

    /**
     * Parameter für Suche extrahieren
     */
    private function getSearchParams(): array
    {
        $params = [];
        $searchValue = $this->config['search'];

        foreach ($this->searchableColumns as $column) {
            $paramName = "search_" . str_replace('.', '_', $column);
            $params[$paramName] = '%' . $this->sanitizeValue($searchValue) . '%';
        }

        return $params;
    }

    /**
     * Parameter für Custom WHERE Conditions extrahieren
     */
    private function getCustomWhereParams(): array
    {
        $params = [];

        foreach ($this->customWhere as $condition) {
            if (!empty($condition['params']) && is_array($condition['params'])) {
                foreach ($condition['params'] as $key => $value) {
                    $params[$key] = $this->sanitizeValue($value);
                }
            }
        }

        return $params;
    }

    /**
     * Wert für Query-Parameter aufbereiten und typisieren
     */
    private function sanitizeValue($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'sanitizeValue'], $value);
        }

        if (is_bool($value)) {
            return (int) $value;
        }

        if ($value === null) {
            return null;
        }

        // Datumsformate erkennen und konvertieren
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            $date = DateTime::createFromFormat('Y-m-d', substr($value, 0, 10));
            if ($date) {
                return $date->format('Y-m-d');
            }
        }

        // Numerische Werte konvertieren
        if (is_numeric($value)) {
            // Integer
            if (ctype_digit($value)) {
                return (int) $value;
            }
            // Float
            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        // String-Werte bereinigen
        if (is_string($value)) {
            // Basic XSS Prevention
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            // SQL Injection Prevention
            $value = str_replace(
                ['\\', "\0", "\n", "\r", "'", '"', "\x1a"],
                ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'],
                $value
            );
        }

        return $value;
    }

    /**
     * Parameter-Typ für PDO bestimmen
     */
    private function getParamType($value): int
    {
        if (is_int($value)) {
            return PDO::PARAM_INT;
        }
        if (is_bool($value)) {
            return PDO::PARAM_BOOL;
        }
        if ($value === null) {
            return PDO::PARAM_NULL;
        }
        return PDO::PARAM_STR;
    }

    /**
     * Query-Parameter validieren und sichern
     */
    private function validateQueryParams(array $params): array
    {
        $validatedParams = [];

        foreach ($params as $key => $value) {
            // Parameter-Name validieren
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
                throw new ListGeneratorException(
                    "Ungültiger Parameter-Name: $key",
                    ['params' => $params]
                );
            }

            // Wert validieren und typisieren
            $validatedValue = $this->sanitizeValue($value);

            // SQL-Injection Prevention für Strings
            if (is_string($validatedValue)) {
                $validatedValue = str_replace(
                    ['\\', "\0", "\n", "\r", "'", '"', "\x1a"],
                    ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'],
                    $validatedValue
                );
            }

            $validatedParams[$key] = $validatedValue;
        }

        return $validatedParams;
    }

    /**
     * Debug-Informationen für Query und Parameter
     */
    private function debugQueryParams(string $query, array $params): void
    {
        if (!$this->config['debug']) {
            return;
        }

        $this->debugLog('Query Parameters', [
            'query' => $query,
            'params' => $params,
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)
        ]);
    }

    /**
     * Query bauen
     */
    private function buildQuery(): string
    {
        try {
            $baseQuery = $this->query;
            $conditions = [];

            // WHERE-Bedingungen sammeln
            if (!empty($_GET['filters'])) {
                $filterConditions = $this->buildFilterConditions();
                if (!empty($filterConditions)) {
                    $conditions[] = '(' . implode(' AND ', $filterConditions) . ')';
                }
            }

            // Suchbedingungen hinzufügen
            if (!empty($this->config['search']) && !empty($this->searchableColumns)) {
                $searchConditions = [];
                foreach ($this->searchableColumns as $column) {
                    $searchConditions[] = sprintf(
                        "%s LIKE :search",
                        $this->quoteIdentifier($column)
                    );
                }
                if (!empty($searchConditions)) {
                    $conditions[] = '(' . implode(' OR ', $searchConditions) . ')';
                    $this->queryParams['search'] = '%' . $this->config['search'] . '%';
                }
            }

            // WHERE-Klausel zusammenbauen
            if (!empty($conditions)) {
                $whereOrAnd = stripos($baseQuery, 'WHERE') !== false ? 'AND' : 'WHERE';
                $baseQuery .= " $whereOrAnd " . implode(' AND ', $conditions);
            }

            // Gruppierung hinzufügen
            if ($this->groupBy) {
                $baseQuery .= ' GROUP BY ' . $this->quoteIdentifier($this->groupBy);
            }

            // Sortierung hinzufügen
            $baseQuery .= $this->buildOrderByClause();

            // Limit und Offset für Paginierung
            $baseQuery .= $this->buildLimitClause();

            if ($this->config['debug']) {
                $this->debugLog('Built Query', [
                    'query' => $baseQuery,
                    'params' => $this->queryParams
                ]);
            }

            return $baseQuery;

        } catch (Exception $e) {
            throw new ListGeneratorException(
                "Fehler beim Erstellen der Query: " . $e->getMessage(),
                ['query' => $baseQuery ?? null, 'conditions' => $conditions ?? []]
            );
        }
    }

    /**
     * Suchbare Spalten setzen
     */
    public function setSearchableColumns(array $columns): void
    {
        $this->searchableColumns = $columns;
    }


    /**
     * Filter-Bedingungen erstellen
     */
    private function buildFilterConditions(): array
    {
        $conditions = [];
        $allowedOperators = ['=', '>', '<', '>=', '<=', 'LIKE', 'IN', 'BETWEEN', 'IS NULL', 'IS NOT NULL'];

        foreach ($_GET['filters'] as $column => $value) {
            if (!isset($this->filters[$column]) || $value === '') {
                continue;
            }

            $filter = $this->filters[$column];
            $operator = $filter['operator'] ?? '=';

            if (!in_array($operator, $allowedOperators)) {
                continue;
            }

            $quotedColumn = $this->quoteIdentifier($column);

            switch ($operator) {
                case 'LIKE':
                    $conditions[] = "$quotedColumn LIKE :filter_$column";
                    $this->queryParams["filter_$column"] = '%' . $value . '%';
                    break;

                case 'IN':
                    if (!is_array($value)) {
                        $value = explode(',', $value);
                    }
                    $placeholders = array_map(
                        function ($i) use ($column) {
                            return ":filter_{$column}_{$i}";
                        },
                        array_keys($value)
                    );
                    $conditions[] = "$quotedColumn IN (" . implode(', ', $placeholders) . ")";
                    foreach ($value as $i => $v) {
                        $this->queryParams["filter_{$column}_{$i}"] = $v;
                    }
                    break;

                case 'BETWEEN':
                    if (is_array($value) && count($value) === 2) {
                        $conditions[] = "$quotedColumn BETWEEN :filter_{$column}_start AND :filter_{$column}_end";
                        $this->queryParams["filter_{$column}_start"] = $value[0];
                        $this->queryParams["filter_{$column}_end"] = $value[1];
                    }
                    break;

                case 'IS NULL':
                    $conditions[] = "$quotedColumn IS NULL";
                    break;

                case 'IS NOT NULL':
                    $conditions[] = "$quotedColumn IS NOT NULL";
                    break;

                default:
                    $conditions[] = "$quotedColumn $operator :filter_$column";
                    $this->queryParams["filter_$column"] = $value;
            }
        }

        return $conditions;
    }

    /**
     * Suchbedingungen erstellen
     */
    private function buildSearchConditions(): string
    {
        $searchValue = $this->config['search'];
        $conditions = [];

        foreach ($this->searchableColumns as $column) {
            $quotedColumn = $this->quoteIdentifier($column);
            $paramName = "search_" . str_replace('.', '_', $column);
            $conditions[] = "$quotedColumn LIKE :$paramName";
            $this->queryParams[$paramName] = '%' . $searchValue . '%';
        }

        return implode(' OR ', $conditions);
    }

    /**
     * ORDER BY Klausel erstellen
     */
    private function buildOrderByClause(): string
    {
        $column = $this->config['sortColumn'];
        $direction = $this->config['sortDirection'];

        if (!isset($this->columns[$column])) {
            return '';
        }

        // Sicherheitscheck für Sort-Direction
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        return ' ORDER BY ' . $this->quoteIdentifier($column) . ' ' . $direction;
    }

    /**
     * LIMIT Klausel für Paginierung erstellen
     */
    private function buildLimitClause(): string
    {
        $page = max(1, (int) ($this->config['page'] ?? 1));
        $itemsPerPage = (int) $this->config['itemsPerPage'];
        $offset = ($page - 1) * $itemsPerPage;

        return sprintf(' LIMIT %d OFFSET %d', $itemsPerPage, $offset);
    }

    /**
     * Count-Query für Gesamtanzahl erstellen
     */
    private function buildCountQuery(): string
    {
        // Basis-Query extrahieren
        $baseQuery = preg_replace('/SELECT .* FROM/', 'SELECT COUNT(*) FROM', $this->query, 1);

        // ORDER BY und LIMIT entfernen
        $baseQuery = preg_replace('/ORDER BY .*$/i', '', $baseQuery);
        $baseQuery = preg_replace('/LIMIT .*$/i', '', $baseQuery);

        // WHERE-Bedingungen hinzufügen
        $conditions = [];

        if (!empty($_GET['filters'])) {
            $filterConditions = $this->buildFilterConditions();
            if (!empty($filterConditions)) {
                $conditions[] = '(' . implode(' AND ', $filterConditions) . ')';
            }
        }

        if (!empty($this->config['search'])) {
            $searchConditions = $this->buildSearchConditions();
            if (!empty($searchConditions)) {
                $conditions[] = '(' . $searchConditions . ')';
            }
        }

        if (!empty($conditions)) {
            $whereOrAnd = stripos($baseQuery, 'WHERE') !== false ? 'AND' : 'WHERE';
            $baseQuery .= " $whereOrAnd " . implode(' AND ', $conditions);
        }

        return $baseQuery;
    }

    /**
     * SQL Identifier quoten
     */
    private function quoteIdentifier(string $identifier): string
    {
        // Behandlung von Tabel.Spalte Notation
        if (strpos($identifier, '.') !== false) {
            return implode('.', array_map(
                function ($part) {
                    return '`' . str_replace('`', '``', trim($part)) . '`';
                },
                explode('.', $identifier)
            ));
        }

        return '`' . str_replace('`', '``', trim($identifier)) . '`';
    }

    private function applySearch(array $data): array
    {
        if (empty($this->config['search']) || empty($this->searchableColumns)) {
            return $data;
        }

        $searchTerm = mb_strtolower($this->config['search']);

        return array_filter($data, function ($row) use ($searchTerm) {
            foreach ($this->searchableColumns as $column) {
                if (!isset($row[$column])) {
                    continue;
                }
                $value = mb_strtolower((string) $row[$column]);
                if (mb_strpos($value, $searchTerm) !== false) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Sortierung anwenden
     */
    private function applySort(array $data): array
    {
        $column = $this->config['sortColumn'];
        $direction = $this->config['sortDirection'];

        usort($data, function ($a, $b) use ($column, $direction) {
            $valueA = $a[$column] ?? '';
            $valueB = $b[$column] ?? '';

            $result = $this->compareValues($valueA, $valueB);
            return $direction === 'DESC' ? -$result : $result;
        });

        return $data;
    }

    /**
     * Werte vergleichen für Sortierung
     */
    private function compareValues($a, $b): int
    {
        if (is_numeric($a) && is_numeric($b)) {
            return $a <=> $b;
        }

        if ($a instanceof DateTime && $b instanceof DateTime) {
            return $a <=> $b;
        }

        return strcmp((string) $a, (string) $b);
    }

    private function renderFooter(int $totalPages, int $currentPage): string
    {
        if (!$this->config['showFooter']) {
            return '';
        }

        $output = new StringBuilder();

        $template = $this->config['footerTemplate'] ?? 'Gesamt: {totalRows} Einträge | Seite {currentPage} von {totalPages}';

        // Platzhalter ersetzen
        $footerText = str_replace(
            ['{totalRows}', '{currentPage}', '{totalPages}'],
            [$this->totalRows, $currentPage, $totalPages],
            $template
        );

        $output->append('<div class="ui right aligned container footer-stats">');
        $output->append($this->escapeHtml($footerText));
        $output->append('</div>');

        return $output->toString();
    }

    /**
     * Liste rendern
     */
    private function renderList(array $data): string
    {
        $totalPages = ceil($this->totalRows / $this->config['itemsPerPage']);
        $currentPage = max(1, min($this->config['page'], $totalPages));

        $output = new StringBuilder();

        // Container für Suchfeld und Filter
        $output->append('<div class="ui form">');
        $output->append('<div class="fields">');

        // Suchfeld in einer Spalte
        $output->append('<div class="twelve wide field">');
        $output->append($this->renderSearch());
        $output->append('</div>');

        // Optional: Zusätzliche Buttons/Controls in einer weiteren Spalte
        $output->append('<div class="four wide field">');
        // Hier könnten zusätzliche Buttons/Controls eingefügt werden
        $output->append('</div>');

        $output->append('</div>'); // Ende .fields
        $output->append('</div>'); // Ende .ui.form

        // Restliche Elemente
        $output
            ->append($this->renderFilters())
            ->append($this->renderTable($data))
            ->append($this->renderPagination())
            ->append($this->renderFooter($totalPages, $currentPage));

        return $output->toString();
    }

    private function renderSearch(): string
    {
        if (empty($this->searchableColumns)) {
            return '';
        }

        $output = new StringBuilder();
        $currentSearch = $this->config['search'] ?? '';
        $listId = $this->config['listId'];
        $contentId = $this->config['contentId'];

        $output->append('<div class="ui fluid icon input">');
        $output->append(sprintf(
            '<input type="text" 
                   id="search_%s" 
                   name="search" 
                   value="%s" 
                   placeholder="Suchen..." 
                   data-content-id="%s"
                   class="search-input">',
            $contentId,
            $this->escapeHtml($currentSearch),
            $contentId
        ));
        $output->append('<i class="search icon"></i>');

        // Clear-Button nur anzeigen wenn Suchbegriff vorhanden
        if (!empty($currentSearch)) {
            $output->append(sprintf(
                '<button class="ui icon button clear-search" data-content-id="%s">
                    <i class="close icon"></i>
                </button>',
                $contentId
            ));
        }

        $output->append('</div>');

        // Optional: Zeige an, in welchen Spalten gesucht wird
        if (!empty($this->searchableColumns)) {
            $searchableLabels = array_map(function ($column) {
                return $this->columns[$column]['label'] ?? $column;
            }, $this->searchableColumns);

            $output->append(sprintf(
                '<div class="ui tiny text" style="margin-top: 0.3em; color: #666;">
                    Suche in: %s
                </div>',
                implode(', ', $searchableLabels)
            ));
        }

        return $output->toString();
    }

    /**
     * Tabelle rendern
     */
    private function renderTable(array $data): string
    {
        $output = new StringBuilder();

        $output
            ->append('<table class="' . $this->getTableClasses() . '">')
            ->append($this->renderTableHeader())
            ->append($this->renderTableBody($data));

        if ($this->hasTotals()) {
            $output->append($this->renderTotalsRow($data));
        }

        $output->append('</table>');

        return $output->toString();
    }

    /**
     * Tabellenkopf rendern
     */
    private function renderTableHeader(): string
    {
        $output = new StringBuilder();

        $output->append('<thead><tr>');

        foreach ($this->columns as $key => $column) {
            $sortClass = $this->getSortClass($key);
            $output->append(
                sprintf(
                    '<th class="%s">%s</th>',
                    $sortClass,
                    $this->escapeHtml($column['label'])
                )
            );
        }

        $output->append('</tr></thead>');

        return $output->toString();
    }

    /**
     * Tabellenkörper rendern
     */
    private function renderTableBody(array $data): string
    {
        $output = new StringBuilder();

        $output->append('<tbody>');

        if (empty($data) && $this->config['showNoDataMessage']) {
            $output->append(
                sprintf(
                    '<tr><td colspan="%d">%s</td></tr>',
                    count($this->columns),
                    $this->config['noDataMessage']
                )
            );
        } else {
            foreach ($data as $row) {
                $output->append($this->renderTableRow($row));
            }
        }

        $output->append('</tbody>');

        return $output->toString();
    }

    /**
     * Tabellenzeile rendern
     */
    private function renderTableRow(array $row): string
    {
        $output = new StringBuilder();

        $output->append(
            sprintf('<tr class="%s">', $this->config['rowClasses'])
        );

        foreach ($this->columns as $key => $column) {
            $value = $row[$key] ?? '';
            $formatter = $column['formatter'] ?? null;

            if ($formatter && is_callable($formatter)) {
                $value = $formatter($value, $row);
            }

            $output->append(
                sprintf('<td>%s</td>', $this->escapeHtml($value))
            );
        }

        $output->append('</tr>');

        return $output->toString();
    }

    /**
     * Button hinzufügen
     */
    public function addButton(string $id, array $options): void
    {
        $defaultOptions = [
            'label' => '',
            'icon' => '',
            'class' => 'ui button',
            'position' => 'left',
            'visible' => true
        ];

        $this->buttons[$id] = array_merge($defaultOptions, $options);
    }

    /**
     * Modal hinzufügen
     */
    public function addModal(string $id, array $options): void
    {
        $defaultOptions = [
            'title' => '',
            'content' => '',
            'size' => 'small'
        ];

        $this->modals[$id] = array_merge($defaultOptions, $options);
    }

    /**
     * Spalte hinzufügen mit Suchbarkeit
     */
    public function addColumn(string $key, array $options = []): void
    {
        $defaultOptions = [
            'label' => $key,
            'sortable' => true,
            'searchable' => false,
            'formatter' => null,
            'options' => []
        ];

        $options = array_merge($defaultOptions, $options);

        // Formatter verarbeiten
        if (is_string($options['formatter'])) {
            // Vordefinierten Formatter laden
            $predefinedFormatter = $this->getPredefinedFormatter(
                $options['formatter'],
                $options['options'] ?? []
            );
            if ($predefinedFormatter) {
                $options['formatter'] = $predefinedFormatter;
            }
        }

        $this->columns[$key] = $options;

        if ($options['searchable']) {
            $this->searchableColumns[] = $key;
        }
    }

    /**
     * Filter hinzufügen
     */
    public function addFilter(string $key, array $options): void
    {
        $defaultOptions = [
            'label' => $key,
            'type' => 'select',
            'options' => [],
            'placeholder' => '- Bitte wählen -'
        ];

        $this->filters[$key] = array_merge($defaultOptions, $options);
    }

    /**
     * Sicherheitsfunktionen
     */
    private function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function initializeCSRF(): void
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Cache Key generieren
     */
    private function generateCacheKey(): string
    {
        return md5(serialize([
            'filters' => $_GET['filters'] ?? [],
            'search' => $this->config['search'],
            'sort' => $this->config['sortColumn'],
            'page' => $this->config['page'],
            'columns' => array_keys($this->columns)
        ]));
    }

    /**
     * Event Handling
     */
    public function on(string $event, callable $handler): void
    {
        $this->eventHandlers[$event][] = $handler;
    }

    private function trigger(string $event, array $data = []): void
    {
        foreach ($this->eventHandlers[$event] ?? [] as $handler) {
            $handler($data);
        }
    }

    /**
     * Fehlerbehandlung
     */
    private function handleError(Exception $e): string
    {
        $this->errors[] = [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'time' => date('Y-m-d H:i:s')
        ];

        $this->trigger('error', ['exception' => $e]);

        if ($this->config['debug']) {
            return sprintf(
                '<div class="error">Error: %s</div>',
                $this->escapeHtml($e->getMessage())
            );
        }

        return '<div class="error">Ein Fehler ist aufgetreten</div>';
    }
}

/**
 * Query Builder für optimierte Datenbankabfragen
 */
class QueryBuilder
{
    private PDO $db;
    private array $parts = [];
    private array $params = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function select(array $columns): self
    {
        $this->parts['select'] = sprintf(
            'SELECT %s',
            implode(', ', array_map([$this, 'quoteIdentifier'], $columns))
        );
        return $this;
    }

    public function where(array $conditions): self
    {
        if (!empty($conditions)) {
            $whereParts = [];
            foreach ($conditions as $column => $value) {
                $param = ':' . str_replace('.', '_', $column);
                $whereParts[] = sprintf(
                    '%s = %s',
                    $this->quoteIdentifier($column),
                    $param
                );
                $this->params[$param] = $value;
            }

            $this->parts['where'] = 'WHERE ' . implode(' AND ', $whereParts);
        }
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->parts['orderBy'] = sprintf(
            'ORDER BY %s %s',
            $this->quoteIdentifier($column),
            $direction
        );
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->parts['limit'] = 'LIMIT ' . $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->parts['offset'] = 'OFFSET ' . $offset;
        return $this;
    }

    public function groupBy(string $column): self
    {
        if ($column) {
            $this->parts['groupBy'] = 'GROUP BY ' . $this->quoteIdentifier($column);
        }
        return $this;
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function build(): string
    {
        return implode(' ', array_filter($this->parts));
    }
}

/**
 * Optimierter StringBuilder für effiziente String-Konkatenation
 */
class StringBuilder
{
    private array $parts = [];
    private int $totalLength = 0;

    public function append(string $str): self
    {
        $this->parts[] = $str;
        $this->totalLength += strlen($str);
        return $this;
    }

    public function toString(): string
    {
        $result = implode('', $this->parts);
        // Reset nach Verwendung für besseres Memory Management
        $this->parts = [];
        $this->totalLength = 0;
        return $result;
    }

    public function clear(): void
    {
        $this->parts = [];
        $this->totalLength = 0;
    }

    public function length(): int
    {
        return $this->totalLength;
    }
}

/**
 * FileCache Implementation
 */
class FileCache implements CacheInterface
{
    private string $cacheDir;
    private int $defaultTtl;

    public function __construct(string $cacheDir, int $defaultTtl = 3600)
    {
        $this->cacheDir = rtrim($cacheDir, '/');
        $this->defaultTtl = $defaultTtl;

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    public function get(string $key): ?array
    {
        $filename = $this->getCacheFilename($key);

        if (!file_exists($filename)) {
            return null;
        }

        $data = @unserialize(file_get_contents($filename));
        if (!$data || !isset($data['expiry']) || $data['expiry'] < time()) {
            @unlink($filename);
            return null;
        }

        return $data['value'];
    }

    public function set(string $key, array $value, int $ttl = null): void
    {
        $filename = $this->getCacheFilename($key);
        $data = [
            'value' => $value,
            'expiry' => time() + ($ttl ?? $this->defaultTtl)
        ];

        file_put_contents($filename, serialize($data), LOCK_EX);
    }

    public function delete(string $key): void
    {
        $filename = $this->getCacheFilename($key);
        if (file_exists($filename)) {
            @unlink($filename);
        }
    }

    public function clear(): void
    {
        $files = glob($this->cacheDir . '/*.cache');
        foreach ($files as $file) {
            @unlink($file);
        }
    }

    private function getCacheFilename(string $key): string
    {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }
}

/**
 * Button Generator
 */
class ButtonGenerator
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function generate(array $button): string
    {
        $builder = new StringBuilder();

        $classes = array_filter([
            'ui',
            $button['size'] ?? null,
            $button['color'] ?? null,
            $button['class'] ?? null,
            'button'
        ]);

        $attributes = [
            'class' => implode(' ', $classes),
            'id' => $button['id'] ?? '',
            'type' => $button['type'] ?? 'button',
            'data-action' => $button['action'] ?? '',
            'disabled' => $button['disabled'] ?? false ? 'disabled' : null,
        ];

        $builder->append('<button ' . $this->buildAttributes($attributes) . '>');

        if (!empty($button['icon'])) {
            $builder->append('<i class="' . $button['icon'] . ' icon"></i>');
        }

        $builder->append(htmlspecialchars($button['label']));
        $builder->append('</button>');

        return $builder->toString();
    }

    private function buildAttributes(array $attributes): string
    {
        $parts = [];
        foreach ($attributes as $key => $value) {
            if ($value === null) {
                continue;
            }
            if ($value === true) {
                $parts[] = $key;
            } else {
                $parts[] = $key . '="' . htmlspecialchars($value) . '"';
            }
        }
        return implode(' ', $parts);
    }


}

/**
 * Modal Generator
 */
class ModalGenerator
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }



    public function generate(array $modal): string
    {
        $builder = new StringBuilder();

        $classes = array_filter([
            'ui',
            $modal['size'] ?? 'small',
            'modal'
        ]);

        $builder
            ->append('<div class="' . implode(' ', $classes) . '" id="' . $modal['id'] . '">')
            ->append('<div class="header">' . htmlspecialchars($modal['title']) . '</div>')
            ->append('<div class="content">' . $modal['content'] . '</div>')
            ->append('<div class="actions">');

        if (isset($modal['buttons'])) {
            $buttonGenerator = new ButtonGenerator($this->config);
            foreach ($modal['buttons'] as $button) {
                $builder->append($buttonGenerator->generate($button));
            }
        }

        $builder->append('</div></div>');

        return $builder->toString();
    }

    public function renderSearch(): string
    {
        if (empty($this->searchableColumns)) {
            return '';
        }

        $output = new StringBuilder();
        $currentSearch = $this->config['search'] ?? '';
        $listId = $this->config['listId'];
        $contentId = $this->config['contentId'];

        $output->append('<div class="ui fluid icon input">');
        $output->append(sprintf(
            '<input type="text" 
                   id="search_%s" 
                   name="search" 
                   value="%s" 
                   placeholder="Suchen..." 
                   data-content-id="%s"
                   class="search-input">',
            $contentId,
            $this->escapeHtml($currentSearch),
            $contentId
        ));
        $output->append('<i class="search icon"></i>');

        // Clear-Button nur anzeigen wenn Suchbegriff vorhanden
        if (!empty($currentSearch)) {
            $output->append(sprintf(
                '<button class="ui icon button clear-search" data-content-id="%s">
                    <i class="close icon"></i>
                </button>',
                $contentId
            ));
        }

        $output->append('</div>');

        // Optional: Zeige an, in welchen Spalten gesucht wird
        if (!empty($this->searchableColumns)) {
            $searchableLabels = array_map(function ($column) {
                return $this->columns[$column]['label'] ?? $column;
            }, $this->searchableColumns);

            $output->append(sprintf(
                '<div class="ui tiny text" style="margin-top: 0.3em; color: #666;">
                    Suche in: %s
                </div>',
                implode(', ', $searchableLabels)
            ));
        }

        return $output->toString();
    }

    public function getPredefinedFormatter($formatterName, $options = [])
    {
        $predefinedFormatters = [
            // Währungen
            'euro' => function ($value) use ($options) {
                if ($value === null || $value === '')
                    return $options['empty_value'] ?? '';
                $formatted = number_format(
                    (float) $value,
                    $options['decimals'] ?? 2,
                    $options['dec_point'] ?? ',',
                    $options['thousands_sep'] ?? '.'
                ) . ' €';
                return $value < 0 ? "<span class='negative'>{$formatted}</span>" : $formatted;
            },

            'dollar' => function ($value) use ($options) {
                if ($value === null || $value === '')
                    return $options['empty_value'] ?? '';
                $formatted = '$' . number_format(
                    (float) $value,
                    $options['decimals'] ?? 2,
                    $options['dec_point'] ?? '.',
                    $options['thousands_sep'] ?? ','
                );
                return $value < 0 ? "<span class='negative'>{$formatted}</span>" : $formatted;
            },

            // Prozente
            'percent' => function ($value) use ($options) {
                if ($value === null || $value === '')
                    return $options['empty_value'] ?? '';
                $formatted = number_format(
                    (float) $value,
                    $options['decimals'] ?? 2,
                    $options['dec_point'] ?? ',',
                    $options['thousands_sep'] ?? '.'
                ) . ' %';
                return $value < 0 ? "<span class='negative'>{$formatted}</span>" : $formatted;
            },

            // Datum & Zeit
            'date' => function ($value) use ($options) {
                if (!$value)
                    return $options['empty_value'] ?? '';
                try {
                    $date = $value instanceof DateTime ? $value : new DateTime($value);
                    return $date->format($options['format'] ?? 'd.m.Y');
                } catch (Exception $e) {
                    return $options['empty_value'] ?? '';
                }
            },

            'datetime' => function ($value) use ($options) {
                if (!$value)
                    return $options['empty_value'] ?? '';
                try {
                    $date = $value instanceof DateTime ? $value : new DateTime($value);
                    return $date->format($options['format'] ?? 'd.m.Y H:i');
                } catch (Exception $e) {
                    return $options['empty_value'] ?? '';
                }
            },

            'time' => function ($value) use ($options) {
                if (!$value)
                    return $options['empty_value'] ?? '';
                try {
                    $date = $value instanceof DateTime ? $value : new DateTime($value);
                    return $date->format($options['format'] ?? 'H:i');
                } catch (Exception $e) {
                    return $options['empty_value'] ?? '';
                }
            },

            // Boolean
            'boolean' => function ($value) use ($options) {
                $trueText = $options['true_text'] ?? 'Ja';
                $falseText = $options['false_text'] ?? 'Nein';
                $trueClass = $options['true_class'] ?? 'positive';
                $falseClass = $options['false_class'] ?? 'negative';

                return $value ?
                    "<span class='{$trueClass}'>{$trueText}</span>" :
                    "<span class='{$falseClass}'>{$falseText}</span>";
            },

            // Zahlen
            'number' => function ($value) use ($options) {
                if ($value === null || $value === '')
                    return $options['empty_value'] ?? '';
                $formatted = number_format(
                    (float) $value,
                    $options['decimals'] ?? 0,
                    $options['dec_point'] ?? ',',
                    $options['thousands_sep'] ?? '.'
                );
                return $value < 0 ? "<span class='negative'>{$formatted}</span>" : $formatted;
            },

            'number_color' => function ($value) use ($options) {
                if ($value === null || $value === '')
                    return $options['empty_value'] ?? '';
                $formatted = number_format(
                    (float) $value,
                    $options['decimals'] ?? 2,
                    $options['dec_point'] ?? ',',
                    $options['thousands_sep'] ?? '.'
                );
                $color = $value > 0 ? 'positive' : ($value < 0 ? 'negative' : 'neutral');
                return "<span class='{$color}'>{$formatted}</span>";
            },

            // Dateigröße
            'filesize' => function ($bytes) use ($options) {
                if ($bytes === null || $bytes === '')
                    return $options['empty_value'] ?? '';
                $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                $bytes = max($bytes, 0);
                $pow = floor((($bytes ? log($bytes) : 0) / log(1024)));
                $pow = min($pow, count($units) - 1);
                $bytes /= pow(1024, $pow);
                return sprintf(
                    '%.' . ($options['decimals'] ?? 2) . 'f %s',
                    $bytes,
                    $units[$pow]
                );
            },

            // Zeitdauer
            'duration' => function ($seconds) use ($options) {
                if ($seconds === null || $seconds === '')
                    return $options['empty_value'] ?? '';
                if ($seconds < 60)
                    return $seconds . ' Sek';
                if ($seconds < 3600) {
                    return floor($seconds / 60) . ' Min ' . ($seconds % 60) . ' Sek';
                }
                return floor($seconds / 3600) . ' Std ' . floor(($seconds % 3600) / 60) . ' Min';
            },

            // Telefon
            'phone' => function ($number) use ($options) {
                if (!$number)
                    return $options['empty_value'] ?? '';
                $format = $options['format'] ?? '(\1) \2-\3';
                $pattern = $options['pattern'] ?? '/(\d{3})(\d{3})(\d{4})/';
                return preg_replace($pattern, $format, $number);
            },

            // Text
            'truncate' => function ($string) use ($options) {
                if (!$string)
                    return $options['empty_value'] ?? '';
                $length = $options['length'] ?? 50;
                $append = $options['append'] ?? "...";
                return (mb_strlen($string) > $length)
                    ? mb_substr($string, 0, $length - mb_strlen($append)) . $append
                    : $string;
            },

            // Status
            'status' => function ($value) use ($options) {
                if (!$value)
                    return $options['empty_value'] ?? '';
                $colors = $options['colors'] ?? [
                    'active' => 'positive',
                    'inactive' => 'negative',
                    'pending' => 'warning',
                    'default' => 'basic'
                ];
                $color = $colors[strtolower($value)] ?? $colors['default'];
                return "<span class='ui {$color} label'>{$value}</span>";
            },

            // Progress
            'progress' => function ($value) use ($options) {
                if ($value === null || $value === '')
                    return $options['empty_value'] ?? '';
                $max = $options['max'] ?? 100;
                $min = $options['min'] ?? 0;
                $percentage = min(100, max(0, ($value - $min) / ($max - $min) * 100));
                return sprintf(
                    '<div class="ui progress" data-percent="%d">
                        <div class="bar" style="width: %d%%">
                            <div class="progress">%d%%</div>
                        </div>
                    </div>',
                    $percentage,
                    $percentage,
                    $percentage
                );
            }
        ];

        return $predefinedFormatters[$formatterName] ?? null;
    }

    /**
     * CSS für die Formatter
     */
    public function getFormatterStyles(): string
    {
        return '
        <style>
            .negative { color: #db2828; }
            .positive { color: #21ba45; }
            .warning { color: #f2711c; }
            .neutral { color: #767676; }
            
            .ui.progress {
                margin: 0;
                background: #f3f4f5;
                border-radius: 0.28571429rem;
                height: 1.2em;
            }
            .ui.progress .bar {
                min-width: 0;
                border-radius: 0.28571429rem;
                background: #2185d0;
                transition: width 0.3s ease;
            }
            .ui.progress .progress {
                color: rgba(255,255,255,.9);
                font-size: 0.8em;
                padding: 0 0.5em;
            }
        </style>';
    }

    /**
     * Werte formatieren beim Rendern der Tabelle
     */
    private function formatValue($value, $column, $row): string
    {
        $formatter = $this->columns[$column]['formatter'] ?? null;

        if ($formatter) {
            if (is_callable($formatter)) {
                // Formatter als Funktion ausführen
                return $formatter($value, $row);
            }
        }

        // Standardformatierung falls kein Formatter definiert
        return $this->escapeHtml((string) $value);
    }

    /**
     * Tabellenzeile rendern
     */
    public function renderTableRow(array $row): string
    {
        $output = new StringBuilder();
        $output->append(sprintf('<tr class="%s">', $this->config['rowClasses']));

        foreach ($this->columns as $key => $column) {
            $value = $row[$key] ?? '';
            $formattedValue = $this->formatValue($value, $key, $row);

            $cellClass = $column['cellClass'] ?? $this->config['cellClasses'];
            $output->append(sprintf('<td class="%s">%s</td>', $cellClass, $formattedValue));
        }

        $output->append('</tr>');
        return $output->toString();
    }

}

/**
 * Custom Exception Klasse
 */
class ListGeneratorException extends Exception
{
    private array $context;

    public function __construct(string $message, array $context = [], int $code = 0)
    {
        parent::__construct($message, $code);
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }


}

/**
 * Beispiel-Verwendung:
 */
// $config = [
//     'debug' => true,
//     'listId' => 'userList',
//     'itemsPerPage' => 20
// ];
// 
// $cache = new FileCache(__DIR__ . '/cache');
// $db = new PDO('mysql:host=localhost;dbname=mydb', 'user', 'pass');
// 
// $list = new ListGenerator($config, $cache, $db);
// 
// $list->addColumn('id', ['label' => 'ID']);
// $list->addColumn('name', ['label' => 'Name', 'searchable' => true]);
// $list->addColumn('email', ['label' => 'E-Mail', 'searchable' => true]);
// 
// $list->addButton('add', [
//     'label' => 'Neu',
//     'icon' => 'plus',
//     'class' => 'primary'
// ]);
// 
// echo $list->generateList();