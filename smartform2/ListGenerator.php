<?php
//Aurichtung der Buttons in den Spalten
//Externe Buttons positionierbar auch inline
class ListGenerator
{
    private $config;
    private $data;
    private $columns = [];
    private $filters = [];
    private $db;
    private $query;
    private $useDatabase = false;
    private $totalRows = 0;

    private $buttons = [];
    private $buttonGroups = [];
    private $buttonColumnTitles = ['left' => '', 'right' => ''];

    private $modals = [];

    private $sessionKey;

    private $externalButtons = [];

    private $buttonColumnAlignments = ['left' => 'left', 'right' => 'right'];

    private $searchableColumns = [];

    public function __construct($config)
    {
        $defaultConfig = [
            'debug' => false,
            'listId' => 'defaultList',
            'contentId' => 'content2',
            'itemsPerPage' => 10,
            'sortColumn' => 'id',
            'sortDirection' => 'ASC',
            'search' => '',
            'page' => 1,
            'showNoDataMessage' => true,
            'noDataMessage' => 'Keine Daten gefunden.',
            'showFooter' => true,
            'footerTemplate' => 'Gesamt: {totalRows} Einträge | Seite {currentPage} von {totalPages}',
            'showPagination' => true,
            'tableClasses' => 'ui celled table',
            'headerClasses' => 'ui table',
            'rowClasses' => '',
            'cellClasses' => '',
            'selectable' => true,
            'celled' => true,
            'color' => '',
            'size' => 'small',
            'width' => '100%',
            'filterClass' => 'ui message',
            'rememberFilters' => true,
        ];

        $this->config = array_merge($defaultConfig, $config);
        $this->sessionKey = 'listGenerator_' . $this->config['listId'];

        if ($this->config['rememberFilters']) {
            $this->loadFiltersFromSession();
        } else {
            $this->clearFiltersFromSession();
        }

    }

    private function loadFiltersFromSession()
    {
        if (isset($_SESSION[$this->sessionKey])) {
            $savedFilters = $_SESSION[$this->sessionKey];
            $_GET['filters'] = $savedFilters['filters'] ?? [];
            $_GET['search'] = $savedFilters['search'] ?? '';
            $_GET['sort'] = $savedFilters['sort'] ?? $this->config['sortColumn'];
            $_GET['sortDir'] = $savedFilters['sortDir'] ?? $this->config['sortDirection'];
            $_GET['page'] = $savedFilters['page'] ?? 1;
        }
    }

    private function clearFiltersFromSession()
    {

        unset($_SESSION[$this->sessionKey]);
        $_GET['filters'] = [];
        $_GET['search'] = '';
        $_GET['sort'] = $this->config['sortColumn'];
        $_GET['sortDir'] = $this->config['sortDirection'];
        $_GET['page'] = 1;
    }

    private function saveFiltersToSession()
    {
        if ($this->config['rememberFilters']) {
            $dataToSave = [
                'filters' => $_GET['filters'] ?? [],
                'search' => $_GET['search'] ?? '',
                'sort' => $_GET['sort'] ?? $this->config['sortColumn'],
                'sortDir' => $_GET['sortDir'] ?? $this->config['sortDirection'],
                'page' => $_GET['page'] ?? 1
            ];
            $_SESSION[$this->sessionKey] = $dataToSave;
            error_log("Saved to session: " . print_r($dataToSave, true));
        }
    }


    public function getConfig()
    {
        return $this->config;
    }

    public function setData($data)
    {
        $this->data = $data;
        $this->useDatabase = false;
    }

    public function setDatabase($db, $query, $useDatabase = true)
    {
        $this->db = $db;
        $this->query = $query;
        $this->useDatabase = $useDatabase;

        $this->debugLog("Database set", [
            'query' => $query,
            'useDatabase' => $useDatabase
        ]);
    }

    public function setSearchableColumns(array $columns)
    {
        $this->searchableColumns = $columns;
    }

    public function addColumn($key, $label, $options = [])
    {
        $defaultOptions = [
            'allowHtml' => false,
            'width' => '',  // Neue Option für die Spaltenbreite
            'formatter' => null
        ];

        $this->columns[$key] = [
            'label' => $label,
            'options' => array_merge($defaultOptions, $options)
        ];
    }

    public function addFilter($key, $label, $options, $config = [])
    {
        $defaultConfig = [
            'placeholder' => 'Alle',
            'clearable' => false,
            'where' => "$key = ?"
        ];

        // Wenn $config ein String ist, nehmen wir an, es ist die WHERE-Bedingung
        if (is_string($config)) {
            $config = ['where' => $config];
        }

        $finalConfig = array_merge($defaultConfig, $config);

        $this->filters[$key] = [
            'label' => $label,
            'options' => $options,
            'config' => $finalConfig
        ];
    }

    private function fetchData()
    {
        $this->debugLog("Fetching data", [
            'config' => $this->config,
            'GET' => $_GET
        ]);

        if (!$this->useDatabase) {
            // Logic for non-database data
            $data = $this->data;

            // Apply filters
            foreach ($this->filters as $key => $filter) {
                if (isset($_GET['filters'][$key]) && $_GET['filters'][$key] !== '') {
                    $filterValue = $_GET['filters'][$key];
                    $innerWhereConditions[] = $filter['config']['where'];
                    $params[] = $filterValue;
                    $this->debugLog("Applied filter", ["key" => $key, "value" => $filterValue]);
                }
            }

            // Apply search
            if (!empty($this->config['search']) && !empty($this->searchableColumns)) {
                $searchTerm = $this->config['search'];
                $data = array_filter($data, function ($item) use ($searchTerm) {
                    foreach ($this->searchableColumns as $column) {
                        if (isset($item[$column]) && stripos($item[$column], $searchTerm) !== false) {
                            return true;
                        }
                    }
                    return false;
                });
            }

            // Apply sorting
            $sortColumn = $this->config['sortColumn'];
            $sortDirection = $this->config['sortDirection'];
            usort($data, function ($a, $b) use ($sortColumn, $sortDirection) {
                $result = $a[$sortColumn] <=> $b[$sortColumn];
                return $sortDirection === 'DESC' ? -$result : $result;
            });

            $this->totalRows = count($data);

            // Apply pagination
            $offset = ($this->config['page'] - 1) * $this->config['itemsPerPage'];
            $data = array_slice($data, $offset, $this->config['itemsPerPage']);

            return $data;
        } else {
            // Database logic
            $innerWhereConditions = [];
            $params = [];
            $hasGroupBy = stripos($this->query, 'GROUP BY') !== false;

            // Build filter conditions
            foreach ($this->filters as $key => $filter) {
                if (isset($_GET['filters'][$key]) && $_GET['filters'][$key] !== '') {
                    $filterValue = $_GET['filters'][$key];
                    $innerWhereConditions[] = $filter['config']['where'];
                    $params[] = $filterValue;
                    $this->debugLog("Applied filter", ["key" => $key, "value" => $filterValue]);
                }
            }

            // Build search condition
            if (!empty($this->config['search']) && !empty($this->searchableColumns)) {
                $searchConditions = [];
                foreach ($this->searchableColumns as $col) {
                    $searchConditions[] = "$col LIKE ?";
                    $params[] = "%{$this->config['search']}%";
                }
                $innerWhereConditions[] = "(" . implode(' OR ', $searchConditions) . ")";
                $this->debugLog("Applied search condition", $searchConditions);
            }

            $innerWhereClause = $innerWhereConditions ? "WHERE " . implode(' AND ', $innerWhereConditions) : "";

            // Modify the query based on whether it has a GROUP BY clause or not
            if ($hasGroupBy) {
                $modifiedQuery = preg_replace(
                    '/GROUP BY/i',
                    $innerWhereClause . ' GROUP BY',
                    $this->query
                );
            } else {
                $modifiedQuery = $this->query . ' ' . $innerWhereClause;
            }

            // Count total rows
            if ($hasGroupBy) {
                $countQuery = "SELECT COUNT(*) as total FROM ({$modifiedQuery}) as subquery";
            } else {
                $countQuery = "SELECT COUNT(*) as total FROM ({$this->query}) as subquery {$innerWhereClause}";
            }

            $this->debugLog("Count query", [
                'query' => $countQuery,
                'params' => $params
            ]);

            $stmt = $this->db->prepare($countQuery);
            if ($stmt) {
                if (!empty($params)) {
                    $types = str_repeat('s', count($params));
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $this->totalRows = $row['total'];
                $stmt->close();
            }

            $this->debugLog("Total rows", ['count' => $this->totalRows]);

            // Validate the sort column
            $validColumns = array_keys($this->columns);
            $sortColumn = in_array($this->config['sortColumn'], $validColumns)
                ? $this->config['sortColumn']
                : reset($validColumns);

            $sortDirection = $this->config['sortDirection'] === 'DESC' ? 'DESC' : 'ASC';

            // Fetch data
            $offset = ($this->config['page'] - 1) * $this->config['itemsPerPage'];

            // Main data query
            $query = $modifiedQuery;
            $query .= " ORDER BY " . $this->db->real_escape_string($sortColumn) . " " . $sortDirection;
            $query .= " LIMIT ? OFFSET ?";

            $this->debugLog("Final SQL Query", [
                'query' => $query,
                'params' => $params
            ]);

            $stmt = $this->db->prepare($query);
            if ($stmt) {
                $params[] = intval($this->config['itemsPerPage']);
                $params[] = $offset;
                $types = str_repeat('s', count($params) - 2) . 'ii';
                $stmt->bind_param($types, ...$params);

                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                $this->debugLog("Data fetched", [
                    'totalRows' => $this->totalRows,
                    'returnedRows' => count($data)
                ]);
                return $data;
            }
            $this->debugLog("Failed to prepare SQL statement", ['query' => $query]);
            return [];
        }
    }
    public function addExternalButton($id, $options)
    {
        $defaultOptions = [
            'icon' => '',
            'class' => 'ui button',
            'position' => 'top', // 'top', 'bottom', oder 'inline'
            'alignment' => 'left',
            'title' => '',
            'modalId' => null,
            'callback' => null,
            'params' => [],
            'popup' => null
        ];

        $this->externalButtons[$id] = array_merge($defaultOptions, $options);
    }

    private function renderExternalButtons($position)
    {
        $html = '';
        $leftButtons = '';
        $rightButtons = '';
        $inlineButtons = '';

        foreach ($this->externalButtons as $id => $button) {
            if ($button['position'] !== $position) {
                continue;
            }

            $buttonHtml = $this->renderButton($id, $button);

            if ($position === 'inline') {
                $inlineButtons .= $buttonHtml;
            } elseif ($button['alignment'] === 'right') {
                $rightButtons .= $buttonHtml;
            } else {
                $leftButtons .= $buttonHtml;
            }
        }

        if ($position === 'inline') {
            return $inlineButtons;
        }

        if ($leftButtons || $rightButtons) {
            $html .= "<div class='ui grid'>";
            $html .= "<div class='eight wide column left aligned'>{$leftButtons}</div>";
            $html .= "<div class='eight wide column right aligned'>{$rightButtons}</div>";
            $html .= "</div>";
        }

        return $html;
    }

    private function renderButton($id, $button)
    {
        $icon = $button['icon'] ? "<i class='{$button['icon']} icon'></i>" : '';
        $attributes = $this->getButtonAttributes($button);

        $html = "<button id='{$id}' class='{$button['class']}' {$attributes}>";
        $html .= "{$icon}{$button['title']}</button>";

        return $html;
    }

    private function getButtonAttributes($button, $params = [])
    {
        $attributes = '';

        if (!empty($button['modalId'])) {
            $attributes .= " data-modal='" . htmlspecialchars($button['modalId'], ENT_QUOTES, 'UTF-8') . "'";
        }

        if (!empty($button['callback'])) {
            $attributes .= " onclick='" . htmlspecialchars($button['callback'], ENT_QUOTES, 'UTF-8') . "(" . htmlspecialchars(json_encode($params), ENT_QUOTES, 'UTF-8') . ")'";
        }

        if (!empty($button['popup']) && is_array($button['popup'])) {
            $attributes .= " data-content='" . htmlspecialchars($button['popup']['content'] ?? '', ENT_QUOTES, 'UTF-8') . "'";
            $attributes .= " data-position='" . htmlspecialchars($button['popup']['position'] ?? 'top center', ENT_QUOTES, 'UTF-8') . "'";
            $attributes .= " data-variation='" . htmlspecialchars($button['popup']['variation'] ?? '', ENT_QUOTES, 'UTF-8') . "'";
        }

        foreach ($params as $key => $value) {
            $attributes .= " data-" . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . "='" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "'";
        }

        return $attributes;
    }

    public function addModal($id, $options)
    {
        $defaultOptions = [
            'title' => '',
            'content' => '',
            'size' => 'small',
            'method' => 'POST'
        ];

        $this->modals[$id] = array_merge($defaultOptions, $options);
    }

    public function addButton($id, $options)
    {
        $defaultOptions = [
            'label' => '',
            'callback' => null,
            'icon' => '',
            'class' => 'ui button',
            'position' => 'left',
            'group' => null,
            'modalId' => null,
            'confirmMessage' => null,
            'popup' => null,
            'conditions' => [],
            'params' => [],
            'visible' => true,
            'disabled' => false,
            'dynamicLabel' => null,
            'dynamicClass' => null,
            'tooltip' => null,
            'permission' => null,
            'sortOrder' => 0,
            'hotkey' => null
        ];

        $options = array_merge($defaultOptions, $options);

        // Füge listId zu den Parametern hinzu
        if (!in_array('listId', $options['params']) && !isset($options['params']['listId'])) {
            $options['params']['listid'] = $this->config['listId'];
        }

        // Verarbeite die params, um Aliase zu ermöglichen
        $processedParams = [];
        foreach ($options['params'] as $key => $value) {
            if (is_numeric($key)) {
                $processedParams[$value] = $value;
            } else {
                $processedParams[$key] = $value;
            }
        }
        $options['params'] = $processedParams;

        $this->buttons[$id] = $options;
    }

    public function setButtonGroupPosition($group, $position)
    {
        $this->buttonGroups[$group] = $position;
    }

    public function setButtonColumnTitle($position, $title, $alignment = 'left')
    {
        $this->buttonColumnTitles[$position] = $title;

        // Überprüfe, ob die Ausrichtung gültig ist
        $validAlignments = ['left', 'center', 'right'];
        if (!in_array($alignment, $validAlignments)) {
            $alignment = 'left'; // Standardausrichtung, falls ungültig
        }

        $this->buttonColumnAlignments[$position] = $alignment;
    }

    private function renderButtons($item, $position)
    {
        $html = '';
        foreach ($this->buttons as $id => $button) {
            if ($button['position'] !== $position) {
                continue;
            }

            if (!$this->checkButtonConditions($button, $item)) {
                continue;
            }

            $params = $this->getButtonParams($button, $item);
            $attributes = $this->getButtonAttributes($button, $params);

            $isIconButton = empty($button['label']) && !empty($button['icon']);
            $buttonClass = $button['class'];
            if ($isIconButton && strpos($buttonClass, 'icon') === false) {
                $buttonClass .= ' icon';
            }

            $html .= "<button id='{$id}' {$attributes} class='" . htmlspecialchars($buttonClass, ENT_QUOTES, 'UTF-8') . "'>";
            if (!empty($button['icon'])) {
                $html .= "<i class='" . htmlspecialchars($button['icon'], ENT_QUOTES, 'UTF-8') . " icon'></i>";
            }
            if (!empty($button['label'])) {
                $html .= htmlspecialchars($button['label'], ENT_QUOTES, 'UTF-8');
            }
            $html .= "</button>";
        }
        return $html;
    }

    private function getButtonParams($button, $item)
    {
        $params = ['listId' => $this->config['listId'] ?? 'defaultListId'];
        foreach ($button['params'] as $alias => $originalKey) {
            if (is_numeric($alias)) {
                $params[$originalKey] = $item[$originalKey] ?? '';
            } else {
                $params[$alias] = $item[$originalKey] ?? '';
            }
        }
        error_log('Button params: ' . print_r($params, true)); // Debugging
        return $params;
    }

    private function checkButtonConditions($button, $item)
    {
        foreach ($button['conditions'] as $condition) {
            if (!$condition($item)) {
                return false;
            }
        }
        return true;
    }


    public function generateList()
    {

        $this->saveFiltersToSession();

        $data = $this->fetchData();
        $totalRows = $this->totalRows;
        $totalPages = ceil($totalRows / $this->config['itemsPerPage']);
        $currentPage = max(1, min($this->config['page'], $totalPages));

        $tableClasses = $this->buildTableClasses();

        $html = "<div id='{$this->config['contentId']}' class='ui container' style='width: {$this->config['width']};'>";

        // Render top external buttons
        $html .= $this->renderExternalButtons('top');

        $html .= "<div class='ui grid'>";
        $html .= "<div class='six wide column'>";
        $html .= $this->generateSearchField();
        $html .= "</div>";
        $html .= "<div class='ten wide column right aligned'>";
        $html .= $this->renderExternalButtons('inline');
        $html .= "</div>";
        $html .= "</div>";

        $html .= $this->generateFilters();

        $html .= "<table class='{$tableClasses}'>";
        $html .= $this->generateTableHeader();
        $html .= "<tbody>";

        if (empty($data) && $this->config['showNoDataMessage']) {
            $html .= $this->generateNoDataMessage();
        } else {
            foreach ($data as $item) {
                $html .= $this->generateTableRow($item);
            }
        }

        $html .= "</tbody>";

        if ($this->config['showFooter']) {
            $html .= $this->generateTableFooter($totalRows, $currentPage, $totalPages);
        }

        $html .= "</table>";

        if ($this->config['showPagination']) {
            $html .= $this->generatePagination($currentPage, $totalPages);
        }

        // Render bottom external buttons
        $html .= $this->renderExternalButtons('bottom');

        $html .= "</div>";
        $html .= $this->renderModals();

        return $html;
    }

    private function buildTableClasses()
    {
        $classes = [$this->config['tableClasses']];
        if ($this->config['striped'])
            $classes[] = 'striped';
        if ($this->config['selectable'])
            $classes[] = 'selectable';
        if ($this->config['celled'])
            $classes[] = 'celled';
        if ($this->config['compact'])
            $classes[] = 'compact';
        if ($this->config['color'])
            $classes[] = $this->config['color'];
        if ($this->config['size'])
            $classes[] = $this->config['size'];
        return implode(' ', array_filter($classes));
    }

    private function generateTableHeader()
    {
        $html = "<thead class='{$this->config['headerClasses']}'><tr>";

        if (isset($this->buttonColumnTitles['left'])) {
            $html .= "<th>{$this->buttonColumnTitles['left']}</th>";
        }

        foreach ($this->columns as $key => $column) {
            $sortClass = $this->getSortClass($key);
            $sortIcon = $this->getSortIcon($key);
            $width = $column['options']['width'] ? "width: {$column['options']['width']};" : "";
            $html .= "<th class='sortable {$sortClass}' data-column='{$key}' style='{$width}'>{$column['label']} {$sortIcon}</th>";
        }

        if (isset($this->buttonColumnTitles['right'])) {
            $html .= "<th>{$this->buttonColumnTitles['right']}</th>";
        }

        $html .= "</tr></thead>";
        return $html;
    }

    private function getSortClass($key)
    {
        return $key === $this->config['sortColumn']
            ? ($this->config['sortDirection'] === 'ASC' ? 'sorted ascending' : 'sorted descending')
            : '';
    }

    private function getSortIcon($key)
    {
        return $key === $this->config['sortColumn']
            ? ($this->config['sortDirection'] === 'ASC' ? '▲' : '▼')
            : '';
    }

    private function generateTableRow($item)
    {
        $html = "<tr class='{$this->config['rowClasses']}'>";

        if (isset($this->buttonColumnTitles['left'])) {
            $alignment = $this->buttonColumnAlignments['left'];
            $html .= "<td class='button-column {$alignment} aligned'>" . $this->renderButtons($item, 'left') . "</td>";
        }

        foreach ($this->columns as $key => $column) {
            $value = $item[$key] ?? '';
            $value = $this->formatColumnValue($value, $column, $item);
            $width = $column['options']['width'] ? "width: {$column['options']['width']};" : "";
            $html .= "<td class='{$this->config['cellClasses']}' style='{$width}'>{$value}</td>";
        }

        if (isset($this->buttonColumnTitles['right'])) {
            $alignment = $this->buttonColumnAlignments['right'];
            $html .= "<td class='button-column {$alignment} aligned'>" . $this->renderButtons($item, 'right') . "</td>";
        }

        $html .= "</tr>";
        return $html;
    }

    private function formatColumnValue($value, $column, $item)
    {
        if (isset($column['options']['formatter'])) {
            $value = $column['options']['formatter']($value, $item);
        }

        return $column['options']['allowHtml']
            ? $value
            : htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function generateNoDataMessage()
    {
        $colspan = count($this->columns) +
            (isset($this->buttonColumnTitles['left']) ? 1 : 0) +
            (isset($this->buttonColumnTitles['right']) ? 1 : 0);

        return "
    <tr>
        <td colspan='{$colspan}' style='text-align: center; padding: 40px;'>
            <div class='ui message' style='display: inline-block;'>
                <p>{$this->config['noDataMessage']}</p>
            </div>
        </td>
    </tr>";
    }

    private function generateTableFooter($totalRows, $currentPage, $totalPages)
    {
        $colspan = count($this->columns) +
            (isset($this->buttonColumnTitles['left']) ? 1 : 0) +
            (isset($this->buttonColumnTitles['right']) ? 1 : 0);
        $footerText = str_replace(
            ['{totalRows}', '{currentPage}', '{totalPages}'],
            [$totalRows, $currentPage, $totalPages],
            $this->config['footerTemplate']
        );
        return "<tfoot><tr><td colspan='{$colspan}'>{$footerText}</td></tr></tfoot>";
    }
    private function renderModals()
    {
        $html = '';
        foreach ($this->modals as $id => $modal) {
            $sizeClass = $this->getModalSizeClass($modal['size']);
            $method = $modal['method'];
            $html .= "<div class='ui modal {$sizeClass}' id='{$id}' data-content-url='{$modal['content']}' data-method='{$method}'>";
            $html .= "<i class='close icon'></i>";
            $html .= "<div class='header'>{$modal['title']}</div>";
            $html .= "<div class='content'>";
            $html .= "<div class='ui active inverted dimmer'>";
            $html .= "<div class='ui text loader'>Laden...</div>";
            $html .= "</div>";
            $html .= "</div>";
            $html .= "</div>";
        }
        return $html;
    }

    private function getModalSizeClass($size)
    {
        $validSizes = ['mini', 'tiny', 'small', 'large', 'fullscreen'];
        return in_array($size, $validSizes) ? $size : '';
    }

    private function generateSearchField()
    {
        $searchInputId = "search_{$this->config['contentId']}";
        return "
        <div class='ui search' style='margin-bottom: 10px;'>
            <div class='ui fluid icon input'>
                <input class='prompt' type='text' placeholder='Suchen...' id='{$searchInputId}' value='" . htmlspecialchars($this->config['search'], ENT_QUOTES, 'UTF-8') . "'>
                <i class='search icon'></i>
            </div>
        </div>";
    }

    private function generateFilters()
    {
        if (empty($this->filters)) {
            return '';
        }

        $filterClass = $this->config['filterClass'] ?? 'ui segment';

        $html = "<div class='{$filterClass}' style='margin-bottom: 20px;'>";
        $html .= "<div class='ui form'>";
        $html .= "<div class='ui stackable grid'>";
        foreach ($this->filters as $key => $filter) {
            $filterId = "filter_{$this->config['contentId']}_{$key}";
            $html .= "<div class='four wide column'>";
            $html .= "<div class='field'>";
            $html .= "<label>{$filter['label']}</label>";
            $html .= "<select class='ui fluid dropdown' name='{$filterId}' id='{$filterId}'>";
            $html .= "<option value=''>{$filter['config']['placeholder']}</option>";
            foreach ($filter['options'] as $value => $label) {
                $selected = (isset($_GET['filters'][$key]) && $_GET['filters'][$key] == $value) ? 'selected' : '';
                $html .= "<option value='{$value}' {$selected}>{$label}</option>";
            }
            $html .= "</select>";
            $html .= "</div>";
            $html .= "</div>";
        }

        $html .= "</div>"; // Ende der Grid
        $html .= "</div>"; // Ende der Form
        $html .= "</div>"; // Ende des Segments

        return $html;
    }
    private function generatePagination($currentPage, $totalPages)
    {
        $html = "<div class='ui pagination menu'>";

        // Previous page
        $paginationId = "pagination_{$this->config['contentId']}";
        $html = "<div id='{$paginationId}' class='ui pagination menu'>";

        // Previous page
        $prevDisabled = ($currentPage == 1) ? 'disabled' : '';
        $html .= "<a class='item {$prevDisabled}' data-page='" . ($currentPage - 1) . "'>Vorherige</a>";


        // Page numbers
        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $startPage + 4);

        if ($startPage > 1) {
            $html .= "<a class='item' data-page='1'>1</a>";
            if ($startPage > 2) {
                $html .= "<span class='item disabled'>...</span>";
            }
        }

        for ($i = $startPage; $i <= $endPage; $i++) {
            $activeClass = ($i == $currentPage) ? 'active' : '';
            $html .= "<a class='item {$activeClass}' data-page='{$i}'>{$i}</a>";
        }

        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                $html .= "<span class='item disabled'>...</span>";
            }
            $html .= "<a class='item' data-page='{$totalPages}'>{$totalPages}</a>";
        }

        // Next page
        $nextDisabled = ($currentPage == $totalPages) ? 'disabled' : '';
        $html .= "<a class='item {$nextDisabled}' data-page='" . ($currentPage + 1) . "'>Nächste</a>";



        $html .= "</div>";
        return $html;
    }

    private function debugLog($message, $data = null)
    {
        if ($this->config['debug']) {
            $logMessage = date('[Y-m-d H:i:s] ') . $message;
            if ($data !== null) {
                $logMessage .= "\nData: " . print_r($data, true);
            }
            error_log($logMessage . "\n", 3, __DIR__ . '/listgenerator_debug.log');
        }
    }
}

?>