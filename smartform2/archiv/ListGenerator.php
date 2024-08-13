<?php
//Filter mit Manory und Clearable und Multi-Select
//Button ausserhalb der Tabelle mit Tooltip
error_reporting(E_ALL);
ini_set('display_errors', 1);

class ListGenerator
{
    private $data = [];
    private $columns = [];
    public $options = [];
    private $db = null;
    private $table = '';
    private $totalRows = 0;
    private $totalPages = 1;
    private $tableClasses = '';
    private $headerClasses = '';
    private $rowClasses = '';
    private $cellClasses = '';
    private $buttons = [];
    private $buttonPositions = [
        'left' => [],
        'right' => [],
        'columns' => [
            'column_name' => [
                'before' => [],
                'after' => []
            ]
        ]
    ];
    private $buttonColumnTitles = [];
    private $listId;
    private $filters = [];
    private $buttonGroups = [];
    private $groupAlignments = [];
    private $externalButtons = [];
    private $isFullQuery = false;
    private $fullQuery = '';
    private $modals = [];
    private $width;



    private function getBasePath()
    {
        $classDir = dirname((new ReflectionClass($this))->getFileName());
        $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $classDir);
        $relativePath = '/' . trim($relativePath, '/');
        return $relativePath;
    }

    public function addModal($identifier, $options = [])
    {
        $this->modals[$identifier] = array_merge([
            'title' => '',
            'content' => '',
            'size' => 'small',
            'actions' => []
        ], $options);

        if (isset($options['formGenerator'])) {
            $formData = $options['formGenerator']->getFormData();
            if (isset($formData['modalActions'])) {
                $this->modals[$identifier]['actions'] = $formData['modalActions'];
            }
        }
    }

    public function addExternalButton($identifier, $options = [])
    {
        $this->externalButtons[$identifier] = array_merge([
            'label' => '',
            'callback' => '',
            'icon' => '',
            'class' => 'ui button',
            'confirmMessage' => '',
            'title' => '',
            'position' => 'top', // 'top' oder 'bottom'
            'alignment' => 'left', // 'left' oder 'right'
            'params' => [],
            'visible' => true,
            'popup' => null, // Neue Option für Popup
        ], $options);
    }
    public function addFilter($key, $label, $options, $config = [])
    {
        $defaultConfig = [
            'type' => 'dropdown',
            'multiple' => false,
            'placeholder' => 'Bitte auswählen',
            'searchable' => false,
            'maxSelections' => null,
            'fullTextSearch' => false,
            'allowAdditions' => false,
            'customClass' => '',
            'clearable' => false,
            'where' => "{$key} = ?"
        ];

        $this->filters[$key] = [
            'label' => $label,
            'options' => $options,
            'config' => array_merge($defaultConfig, $config)
        ];
    }

    private function renderFilters()
    {
        if (empty($this->filters)) {
            return '';
        }

        $html = "<div class='{$this->options['filterContainerClass']}' style='{$this->options['filterContainerStyle']}'>";
        $html .= "<div class='ui form'>";
        $html .= "<div class='ui stackable grid'>";

        foreach ($this->filters as $key => $filter) {
            $html .= $this->renderFilterField($key, $filter);
        }

        $html .= "</div></div></div>";

        return $html;
    }

    private function renderFilterField($key, $filter)
    {
        $filterId = "filter_{$this->listId}_{$key}";
        $html = "<div class='four wide column'>";
        $html .= "<div class='{$this->options['filterFieldClass']}' style='{$this->options['filterFieldStyle']}'>";
        $html .= "<label style='{$this->options['filterLabelStyle']}'>{$filter['label']}</label>";

        $dropdownClass = $this->getFilterDropdownClass($filter['config']);

        $html .= "<select class='{$dropdownClass}' name='{$filterId}' id='{$filterId}' style='{$this->options['filterSelectStyle']}'";
        $html .= $this->getFilterAttributes($filter['config']);
        $html .= ">";

        $html .= "<option value=''>{$filter['config']['placeholder']}</option>";
        foreach ($filter['options'] as $value => $label) {
            $selected = (isset($_GET['filters'][$key]) && $_GET['filters'][$key] == $value) ? 'selected' : '';
            $html .= "<option value='{$value}' {$selected}>{$label}</option>";
        }
        $html .= "</select>";
        $html .= "</div></div>";

        return $html;
    }

    private function getFilterDropdownClass($config)
    {
        $class = 'ui fluid dropdown';
        if ($config['searchable'])
            $class .= ' search';
        if ($config['multiple'])
            $class .= ' multiple';
        if ($config['clearable'])
            $class .= ' clearable';
        return $class . ' ' . $config['customClass'];
    }

    private function getFilterAttributes($config)
    {
        $attrs = '';
        if ($config['multiple'])
            $attrs .= " multiple='multiple'";
        if ($config['maxSelections'])
            $attrs .= " data-max-selections='{$config['maxSelections']}'";
        if ($config['fullTextSearch'])
            $attrs .= " data-full-text-search='true'";
        if ($config['allowAdditions'])
            $attrs .= " data-allow-additions='true'";
        return $attrs;
    }

    public function setButtonGroupPosition($groupName, $alignment = 'center')
    {
        if (!in_array($alignment, ['left', 'center', 'right'])) {
            throw new InvalidArgumentException("Ungültige Ausrichtung für Buttongruppe. Erlaubt sind 'left', 'center' oder 'right'.");
        }
        $this->groupAlignments[$groupName] = $alignment;
    }

    public function addButton($identifier, $options = [])
    {
        $this->buttons[$identifier] = array_merge([
            'label' => '',
            'callback' => '',
            'icon' => '',
            'class' => 'ui button',
            'confirmMessage' => '',
            'title' => '',
            'group' => null,
            'position' => 'right',
            'reference' => null,
            'alignment' => 'center',
            'params' => [],
            'condition' => null,
            'conditions' => [],
            'dynamicClass' => null,
            'visible' => true,
            'rawTitle' => false,
            'modalId' => null
        ], $options);

        if (isset($options['group'])) {
            $groupName = $options['group'];
            if (!isset($this->buttonGroups[$groupName])) {
                $this->buttonGroups[$groupName] = [];
            }
            $this->buttonGroups[$groupName][] = $identifier;

            // Wenn die Gruppe bereits eine Ausrichtung hat, verwende diese
            if (isset($this->groupAlignments[$groupName])) {
                $this->buttons[$identifier]['alignment'] = $this->groupAlignments[$groupName];
            }
        }

        $this->setButtonPosition($identifier, $this->buttons[$identifier]['position'], $this->buttons[$identifier]['reference']);
    }

    private function setButtonPosition($buttonName, $position, $reference = null)
    {
        if (!isset($this->buttons[$buttonName])) {
            throw new InvalidArgumentException("Button '$buttonName' existiert nicht.");
        }

        // Entferne den Button von seiner aktuellen Position
        foreach ($this->buttonPositions as $pos => &$buttons) {
            if (is_array($buttons)) {
                $buttons = array_filter($buttons, function ($b) use ($buttonName) {
                    return $b !== $buttonName;
                });
            }
        }

        // Setze die neue Position
        if ($position === 'left' || $position === 'right') {
            $this->buttonPositions[$position][] = $buttonName;
        } elseif ($position === 'before' || $position === 'after') {
            if ($reference === null) {
                throw new InvalidArgumentException("Für 'before' und 'after' Positionen wird eine Referenzspalte benötigt.");
            }
            if (!isset($this->buttonPositions['columns'][$reference])) {
                $this->buttonPositions['columns'][$reference] = ['before' => [], 'after' => []];
            }
            $this->buttonPositions['columns'][$reference][$position][] = $buttonName;
        } else {
            throw new InvalidArgumentException("Ungültige Position für Button. Erlaubt sind 'left', 'right', 'before' oder 'after'.");
        }
    }

    public function setButtonColumnTitle($position, $title)
    {
        $this->buttonColumnTitles[$position] = $title;
    }

    private function renderModals()
    {
        $html = '';
        foreach ($this->modals as $identifier => $modal) {
            $modalId = $this->listId . '_modal_' . $identifier;

            $html .= "<div class='ui modal' id='$modalId' data-content='{$modal['content']}'>";
            $html .= "<i class='close icon'></i>";
            $html .= "<div class='header'>{$modal['title']}</div>";
            $html .= "<div class='content'>";
            $html .= "<div class='ui active loader'></div>";
            $html .= "</div>";

            if (!empty($modal['actions'])) {
                $html .= "<div class='actions'>";
                foreach ($modal['actions'] as $actionKey => $action) {
                    $html .= "<div class='{$action['class']}' data-action='$actionKey'>{$action['label']}</div>";
                }
                $html .= "</div>";
            }

            $html .= "</div>";
        }
        return $html;
    }

    private function renderButton($button, $row = null)
    {
        if (!isset($button['visible']) || !$button['visible']) {
            return '';
        }

        $icon = isset($button['icon']) && $button['icon'] ? "<i class='{$button['icon']} icon'></i>" : '';
        $confirm = isset($button['confirmMessage']) && $button['confirmMessage'] ? "data-confirm=\"" . htmlspecialchars($button['confirmMessage']) . "\"" : '';

        $params = [];
        if (isset($button['params']) && is_array($button['params'])) {
            foreach ($button['params'] as $param) {
                $params[$param] = $row && isset($row[$param]) ? $row[$param] : '';
            }
        }
        $paramsJson = htmlspecialchars(json_encode($params));

        $label = isset($button['label']) ? htmlspecialchars($button['label']) : '';
        $callback = isset($button['callback']) ? htmlspecialchars($button['callback']) : '';
        $title = isset($button['title']) && $button['title'] ? "title=\"" . htmlspecialchars($button['title']) . "\"" : '';

        $buttonClass = $button['class'] ?? 'ui button';
        if (isset($button['dynamicClass']) && is_callable($button['dynamicClass'])) {
            $buttonClass = call_user_func($button['dynamicClass'], $row);
        }
        if (empty($label) && !empty($icon)) {
            $buttonClass .= ' icon';
        }

        $modalAttribute = isset($button['modalId']) ? "data-modal-id=\"{$button['modalId']}\"" : '';
        $paramsAttribute = "data-params='" . $paramsJson . "'";

        return "<button class='$buttonClass' $modalAttribute $paramsAttribute onclick='$callback($paramsJson)' $confirm $title>$icon$label</button>";
    }

    private function shouldShowButton($button, $row)
    {
        // Prüfe einzelne Bedingung
        if (isset($button['condition']) && is_callable($button['condition'])) {
            if (!call_user_func($button['condition'], $row)) {
                return false;
            }
        }

        // Prüfe mehrere Bedingungen
        if (!empty($button['conditions']) && is_array($button['conditions'])) {
            foreach ($button['conditions'] as $condition) {
                if (is_callable($condition) && !call_user_func($condition, $row)) {
                    return false;
                }
            }
        }

        // Wenn keine Bedingungen nicht erfüllt sind, zeige den Button
        return true;
    }
    private function renderSingleButton($identifier, $row)
    {
        if (!isset($this->buttons[$identifier])) {
            error_log("Button mit Identifier '$identifier' nicht gefunden.");
            return '';
        }

        $button = $this->buttons[$identifier];

        if (!$this->shouldShowButton($button, $row)) {
            return '';
        }

        return $this->renderButton($button, $row);
    }

    // Hilfsmethode zum Ersetzen von Platzhaltern
    private function replacePlaceholders($text, $row)
    {
        return preg_replace_callback('/\{(\w+)\}/', function ($matches) use ($row) {
            return $row[$matches[1]] ?? $matches[0];
        }, $text);
    }
    public function __construct($options = [])
    {
        $this->options = array_merge($this->getDefaultOptions(), $options);
        $this->width = $this->options['width'];
        $this->listId = $options['listId'] ?? 'listGenerator_' . uniqid();
        $this->applyTableStyles();
    }

    private function getDefaultOptions()
    {
        return [
            'itemsPerPage' => 10,
            'sortColumn' => 'id',
            'sortDirection' => 'ASC',
            'search' => '',
            'page' => 1,
            'showNoDataMessage' => true,
            'noDataMessage' => 'Keine Daten verfügbar.',
            'showFooter' => true,
            'footerText' => 'Gesamt: {totalRows} Einträge | Seite {currentPage} von {totalPages}',
            'showPagination' => true,
            'tableClasses' => 'ui table',
            'headerClasses' => '',
            'rowClasses' => '',
            'cellClasses' => '',
            'celled' => false,
            'basic' => false,
            'striped' => false,
            'selectable' => false,
            'attached' => null,
            'definition' => false,
            'collapsing' => false,
            'stackable' => false,
            'unstackable' => false,
            'padded' => false,
            'compact' => false,
            'size' => null,
            'color' => null,
            'inverted' => false,
            'singleLine' => false,
            'fixed' => false,
            'structured' => false,
            'columnCount' => null,
            'filterLayout' => 'inline',
            'filterContainerClass' => 'ui message form',
            'filterContainerStyle' => 'margin-bottom: 1em;',
            'filterFieldClass' => 'field',
            'filterFieldStyle' => 'margin-right: 1em; margin-bottom: 1em;',
            'filterLabelStyle' => 'margin-bottom: 0.5em; display: block;',
            'filterSelectStyle' => 'width: 100%;',
            'filterMinWidth' => '200px',
            'width' => '100%',
        ];
    }

    private function applyTableStyles()
    {
        $this->tableClasses = 'ui table';

        $booleanClasses = [
            'celled',
            'basic',
            'striped',
            'selectable',
            'definition',
            'collapsing',
            'stackable',
            'unstackable',
            'padded',
            'compact',
            'inverted',
            'singleLine',
            'fixed',
            'structured'
        ];

        foreach ($booleanClasses as $class) {
            if ($this->options[$class]) {
                $this->tableClasses .= " $class";
            }
        }

        if ($this->options['attached']) {
            $this->tableClasses .= " {$this->options['attached']} attached";
        }

        if ($this->options['size']) {
            $this->tableClasses .= " {$this->options['size']}";
        }

        if ($this->options['color']) {
            $this->tableClasses .= " {$this->options['color']}";
        }

        if ($this->options['columnCount']) {
            $columnCount = min(16, max(1, intval($this->options['columnCount'])));
            $this->tableClasses .= " $columnCount column";
        }

        $this->tableClasses .= " " . $this->options['tableClasses'];
        $this->headerClasses = $this->options['headerClasses'];
        $this->rowClasses = $this->options['rowClasses'];
        $this->cellClasses = $this->options['cellClasses'];
    }

    public function setDatabase($db, $tableOrQuery, $isFullQuery = false)
    {
        $this->db = $db;
        $this->isFullQuery = $isFullQuery;

        if ($isFullQuery) {
            $this->fullQuery = $tableOrQuery;
            // Extrahieren Sie die Spaltennamen aus der Abfrage
            $this->extractColumnsFromQuery();
        } else {
            $this->table = $tableOrQuery;
        }
    }


    private function extractColumnsFromQuery()
    {
        // Führen Sie die Abfrage aus, um die Spaltennamen zu erhalten
        $result = $this->db->query($this->fullQuery . " LIMIT 0");
        if ($result) {
            $this->columns = [];
            while ($field = $result->fetch_field()) {
                $this->addColumn($field->name, $field->name);
            }
        }
    }

    public function setData($data)
    {
        $this->data = $data;
        $this->totalRows = count($data);
        $this->totalPages = ceil($this->totalRows / $this->options['itemsPerPage']);
    }

    public function addColumn($key, $label, $options = [])
    {
        $this->columns[$key] = array_merge([
            'name' => $key,
            'label' => $label,
            'sortable' => true,
            'searchable' => true,
            'formatter' => null,
            'width' => null // Neue Option für die Spaltenbreite
        ], $options);
    }

    public function generateJSON()
    {
        if ($this->db) {
            $this->loadFromDatabase();
        } else {
            $this->processArrayData();
        }

        return json_encode([
            'tableBody' => $this->renderTableBody(),
            'pagination' => $this->renderPagination(),
            'footer' => $this->renderFooter(),
            'totalRows' => $this->totalRows,
            'externalButtons' => $this->renderExternalButtons()
        ]);
    }

sddsf
    public function generate($returnJson = false)
    {
        if ($this->db) {
            $this->loadFromDatabase();
        } else {

        }

        if ($returnJson) {
            header('Content-Type: application/json');
            return json_encode([
                'tableBody' => $this->renderTableBody(),
                'pagination' => $this->renderPagination(),
                'footer' => $this->renderFooter(),
                'sortColumn' => $this->options['sortColumn'],
                'sortDirection' => $this->options['sortDirection'],
                'totalRows' => $this->totalRows,
                'externalButtons' => $this->renderExternalButtons()
            ]);
        }

        return $this->renderHTML();
    }

    private function renderExternalButtons()
    {
        $positions = [
            'topLeft' => '',
            'topRight' => '',
            'bottomLeft' => '',
            'bottomRight' => ''
        ];

        foreach ($this->externalButtons as $identifier => $button) {
            $buttonHtml = $this->renderButton($button);

            $position = ($button['position'] === 'top' ? 'top' : 'bottom') .
                ($button['alignment'] === 'right' ? 'Right' : 'Left');

            $positions[$position] .= $buttonHtml;
        }

        return $positions;
    }

    private function applyFilters(&$query, &$params)
    {
        foreach ($this->filters as $key => $filter) {
            if (isset($_GET['filters'][$key]) && $_GET['filters'][$key] !== '') {
                $filterValue = $_GET['filters'][$key];
                $whereClause = $filter['config']['where'];

                if (strpos($whereClause, '{value}') !== false) {
                    // Wenn {value} in der WHERE-Klausel vorhanden ist, ersetzen wir es direkt
                    $whereClause = str_replace('{value}', $filterValue, $whereClause);
                    $query .= " AND ($whereClause)";
                } elseif (strpos($whereClause, '?') !== false) {
                    // Wenn ein Fragezeichen vorhanden ist, fügen wir den Wert als Parameter hinzu
                    $query .= " AND ($whereClause)";
                    $params[] = $filterValue;
                } else {
                    // Wenn weder {value} noch ? vorhanden sind, nehmen wir an, dass es sich um eine direkte Bedingung handelt
                    $query .= " AND ($whereClause)";
                }
            }
        }
    }


    private function processArrayData()
    {
        // Suche anwenden
        if ($this->options['search']) {
            $this->data = array_filter($this->data, function ($row) {
                foreach ($this->columns as $key => $col) {
                    if ($col['searchable'] && stripos($row[$key], $this->options['search']) !== false) {
                        return true;
                    }
                }
                return false;
            });
        }

        // Filter anwenden
        foreach ($this->filters as $key => $filter) {
            if (isset($_GET['filter_' . $key]) && $_GET['filter_' . $key] !== '') {
                $filterValues = is_array($_GET['filter_' . $key]) ? $_GET['filter_' . $key] : explode(',', $_GET['filter_' . $key]);
                $this->data = array_filter($this->data, function ($row) use ($key, $filterValues) {
                    if ($key === 'year') {
                        $rowYear = date('Y', strtotime($row['created_at']));
                        return in_array($rowYear, $filterValues);
                    }
                    return in_array((string) $row[$key], $filterValues);
                });
            }
        }

        // Sortierung anwenden
        if (isset($this->columns[$this->options['sortColumn']]) && $this->columns[$this->options['sortColumn']]['sortable']) {
            usort($this->data, function ($a, $b) {
                $column = $this->options['sortColumn'];
                $result = $a[$column] <=> $b[$column];
                return $this->options['sortDirection'] === 'ASC' ? $result : -$result;
            });
        }

        $this->totalRows = count($this->data);
        $this->totalPages = ceil($this->totalRows / $this->options['itemsPerPage']);
        $offset = ($this->options['page'] - 1) * $this->options['itemsPerPage'];
        $this->data = array_slice($this->data, $offset, $this->options['itemsPerPage']);
    }

    private function renderHTML()
    {
        $html = '<div id="' . $this->listId . '" class="list-generator-container" style="width: ' . $this->width . ';">';


        $externalButtons = $this->renderExternalButtons();

        // Top section with search and buttons
        $html .= '<div class="ui grid">';
        $html .= '<div class="left floated eight wide column">';
        $html .= $externalButtons['topLeft'];
        $html .= $this->renderSearch();
        $html .= '</div>';
        $html .= '<div class="right floated eight wide column right aligned">';
        $html .= $externalButtons['topRight'];
        $html .= '</div>';
        $html .= '</div>';

        $filters = $this->renderFilters();
        if (!empty($filters)) {
            $html .= $filters;
        }

        $html .= '<table class="' . $this->tableClasses . '">';
        $html .= $this->renderTableHeader();
        $html .= '<tbody class="listBody">';
        $html .= $this->renderTableBody();
        $html .= '</tbody>';
        if ($this->options['showFooter']) {
            $html .= '<tfoot class="listFooter"><tr><td colspan="' . $this->getTotalColumns() . '">' . $this->renderFooter() . '</td></tr></tfoot>';
        }
        $html .= '</table>';
        if ($this->options['showPagination'] && $this->totalPages > 1) {
            $html .= '<div class="listPagination">' . $this->renderPagination() . '</div>';
        }

        // Bottom buttons
        $html .= '<div class="ui grid">';
        $html .= '<div class="left floated eight wide column">';
        $html .= $externalButtons['bottomLeft'];
        $html .= '</div>';
        $html .= '<div class="right floated eight wide column right aligned">';
        $html .= $externalButtons['bottomRight'];
        $html .= '</div>';
        $html .= '</div>';

        $html .= '</div>';
        $html .= $this->renderModals();
        $html .= $this->renderJavaScript();
        return $html;
    }

    private function renderSearch()
    {
        return '
        <div class="ui search" style="display: inline-block; margin-right: 1em;">
            <div class="ui icon input">
                <input class="prompt" type="text" placeholder="Suche..." value="' . htmlspecialchars($this->options['search']) . '">
                <i class="search icon"></i>
            </div>
        </div>';
    }

    private function renderTableHeader()
    {
        $html = '<thead><tr class="' . $this->headerClasses . '">';

        // Render left buttons header
        if (!empty($this->buttonPositions['left'])) {
            $title = $this->getButtonColumnTitle('left');
            $html .= "<th class='" . $this->cellClasses . "'>$title</th>";
        }

        // Render column headers and their associated button headers
        foreach ($this->columns as $key => $col) {
            // Render button header before the column
            if (!empty($this->buttonPositions['columns'][$key]['before'])) {
                $title = $this->getButtonColumnTitle("before_$key");
                $html .= "<th class='" . $this->cellClasses . "'>$title</th>";
            }

            // Render the main column header
            $sortClass = $key === $this->options['sortColumn']
                ? ($this->options['sortDirection'] === 'ASC' ? 'sorted ascending' : 'sorted descending')
                : '';

            $widthClass = isset($col['width']) ? $this->getWidthClass($col['width']) : '';

            $html .= "<th class='$sortClass $widthClass " . $this->cellClasses . "' data-sort='$key'>";

            // Hier verwenden wir rawTitle
            if (isset($col['rawTitle']) && $col['rawTitle']) {
                $html .= $col['label'];
            } else {
                $html .= htmlspecialchars($col['label']);
            }

            if ($key === $this->options['sortColumn']) {
                $sortIcon = $this->options['sortDirection'] === 'ASC' ? ' ▲' : ' ▼';
                $html .= "<span class='sort-icon'>$sortIcon</span>";
            }

            $html .= "</th>";

            // Render button header after the column
            if (!empty($this->buttonPositions['columns'][$key]['after'])) {
                $title = $this->getButtonColumnTitle("after_$key");
                $html .= "<th class='" . $this->cellClasses . "'>$title</th>";
            }
        }

        // Render right buttons header
        if (!empty($this->buttonPositions['right'])) {
            $title = $this->getButtonColumnTitle('right');
            $html .= "<th class='" . $this->cellClasses . "'>$title</th>";
        }

        $html .= '</tr></thead>';
        return $html;
    }

    private function getWidthClass($width)
    {
        $widthMap = [
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen'
        ];

        if (is_numeric($width) && isset($widthMap[$width])) {
            return $widthMap[$width] . ' wide';
        } elseif (is_string($width) && in_array($width, ['wide', 'collapsing'])) {
            return $width;
        }

        return '';
    }


    private function getButtonColumnTitle($position)
    {
        if (isset($this->buttonColumnTitles[$position])) {
            return $this->buttonColumnTitles[$position];
        }

        $buttons = $position === 'left' || $position === 'right'
            ? $this->buttonPositions[$position]
            : ($this->buttonPositions['columns'][substr($position, 6)][substr($position, 0, 5)] ?? []);

        if (!empty($buttons)) {
            $firstButtonName = $buttons[0];
            return $this->buttons[$firstButtonName]['title'] ?? 'Aktionen';
        }

        return 'Aktionen';
    }



    private function renderTableBody()
    {
        if ($this->totalRows > 0) {
            $html = '';
            foreach ($this->data as $row) {
                $html .= '<tr class="' . $this->rowClasses . '">';

                // Render left buttons
                if (!empty($this->buttonPositions['left'])) {
                    $html .= $this->renderButtons($row, 'left');
                }

                // Render columns and their associated buttons
                foreach ($this->columns as $key => $col) {
                    // Render buttons before the column
                    if (!empty($this->buttonPositions['columns'][$key]['before'])) {
                        $html .= $this->renderButtons($row, 'columns', $key, 'before');
                    }

                    // Render the column data
                    $value = $row[$key] ?? '';
                    if ($col['formatter']) {
                        $value = $col['formatter']($value, $row);
                    }

                    $widthClass = isset($col['width']) ? $this->getWidthClass($col['width']) : '';
                    $html .= "<td class='" . $this->cellClasses . " $widthClass'>$value</td>";

                    // Render buttons after the column
                    if (!empty($this->buttonPositions['columns'][$key]['after'])) {
                        $html .= $this->renderButtons($row, 'columns', $key, 'after');
                    }
                }

                // Render right buttons
                if (!empty($this->buttonPositions['right'])) {
                    $html .= $this->renderButtons($row, 'right');
                }

                $html .= '</tr>';
            }
        } else if ($this->options['showNoDataMessage']) {
            $html = '<tr><td colspan="' . $this->getTotalColumns() . '" class="center aligned ' . $this->cellClasses . '">' . $this->options['noDataMessage'] . '</td></tr>';
        } else {
            $html = '';
        }
        return $html;
    }


    private function renderButtons($row, $position, $reference = null, $placement = null)
    {
        $html = "<td class='center aligned'>";
        $buttons = [];
        $groupedButtons = [];
        $ungroupedButtons = [];

        if ($position === 'left' || $position === 'right') {
            $buttons = $this->buttonPositions[$position] ?? [];
        } elseif ($position === 'columns' && $reference !== null && $placement !== null) {
            $buttons = $this->buttonPositions['columns'][$reference][$placement] ?? [];
        }

        foreach ($buttons as $identifier) {
            if (isset($this->buttons[$identifier])) {
                $button = $this->buttons[$identifier];
                if ($this->shouldShowButton($button, $row)) {
                    if (isset($button['group'])) {
                        if (!isset($groupedButtons[$button['group']])) {
                            $groupedButtons[$button['group']] = [];
                        }
                        $groupedButtons[$button['group']][] = $identifier;
                    } else {
                        $ungroupedButtons[] = $identifier;
                    }
                }
            }
        }

        // Render grouped buttons
        foreach ($groupedButtons as $group => $buttonIds) {
            $alignment = $this->groupAlignments[$group] ?? 'center';
            $html .= "<div class='ui {$alignment} aligned'>";
            $html .= "<div class='ui tiny buttons'>";
            foreach ($buttonIds as $id) {
                $html .= $this->renderSingleButton($id, $row);
            }
            $html .= "</div></div>";
        }

        // Render ungrouped buttons
        foreach ($ungroupedButtons as $id) {
            $alignment = $this->buttons[$id]['alignment'] ?? 'center';
            $html .= "<div class='ui {$alignment} aligned'>";
            $html .= $this->renderSingleButton($id, $row);
            $html .= "</div>";
        }

        $html .= "</td>";
        return $html;
    }


    private function renderPagination()
    {
        if ($this->totalRows == 0 || $this->totalPages <= 1) {
            return ''; // Keine Pagination anzeigen, wenn keine Daten oder nur eine Seite
        }

        $html = '<div class="ui pagination menu">';
        for ($i = 1; $i <= $this->totalPages; $i++) {
            $activeClass = $i === $this->options['page'] ? 'active' : '';
            $html .= "<a class='item $activeClass' data-page='$i'>$i</a>";
        }
        $html .= '</div>';
        return $html;
    }


    private function renderFooter()
    {
        return str_replace(
            ['{totalRows}', '{currentPage}', '{totalPages}'],
            [$this->totalRows, $this->options['page'], $this->totalPages],
            $this->options['footerText']
        );
    }


    private function getTotalColumns()
    {
        $totalColumns = count($this->columns);

        // Zähle Buttons in der linken Spalte
        $totalColumns += !empty($this->buttonPositions['left']) ? 1 : 0;

        // Zähle Buttons in der rechten Spalte
        $totalColumns += !empty($this->buttonPositions['right']) ? 1 : 0;

        // Zähle Buttons, die vor oder nach bestimmten Spalten platziert sind
        foreach ($this->buttonPositions['columns'] as $columnButtons) {
            $totalColumns += count($columnButtons['before'] ?? []) > 0 ? 1 : 0;
            $totalColumns += count($columnButtons['after'] ?? []) > 0 ? 1 : 0;
        }

        return $totalColumns;
    }
    private static $jsRendered = false;

    private function renderJavaScript()
    {
        $js = "";
        if (!self::$jsRendered) {
            $basePath = $this->getBasePath();
            $js = "<script src='{$basePath}/js/listGenerator.js'></script>\n";
            $js .= "<script>var filterSettings = " . json_encode($this->filters) . ";</script>";
            $js .= "<style>
            .list-generator-container table,
            .list-generator-container .ui.grid,
            .list-generator-container .listPagination {
                width: 100%;
            }
        </style>";
            self::$jsRendered = true;
        }
        $js .= "
<script>
function initializeListGeneratorWrapper() {
    if (typeof window.initializeListGenerator === 'function') {
        console.log('initializeListGenerator wird aufgerufen für: " . $this->listId . "');
        window.initializeListGenerator('" . $this->listId . "', {
            sortColumn: '" . $this->options['sortColumn'] . "',
            sortDirection: '" . $this->options['sortDirection'] . "',
            showFooter: " . ($this->options['showFooter'] ? 'true' : 'false') . "
        });
    } else {
        console.warn('initializeListGenerator ist noch nicht definiert, versuche es in 100ms erneut.');
        setTimeout(initializeListGeneratorWrapper, 100);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeListGeneratorWrapper);
} else {
    initializeListGeneratorWrapper();
}
</script>
";

        return $js;
    }

}