<?php
//mit Summenbildung
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

    private $groupBy = null;
    private $groupByOptions = [];

    private $allowedOperators = ['=', '>', '<', '>=', '<=', 'LIKE', 'IN', 'BETWEEN', 'IS NULL', 'IS NOT NULL'];
    private $allowedFunctions = ['DATE', 'YEAR', 'MONTH', 'DAY', 'CONCAT', 'UPPER', 'LOWER'];

    private $totals = [];
    private $totalTypes = [];
    private $totalLabels = [];

    private $hasWhereClause = false;

    public function setGroupBy($column)
    {
        $this->groupBy = $column;
    }

    public function addGroupByOption($column, $label)
    {
        $this->groupByOptions[$column] = $label;
    }

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

        // Prüfen, ob die Abfrage bereits eine WHERE-Klausel enthält
        $this->hasWhereClause = (stripos($query, 'WHERE') !== false);

        $this->debugLog("Database set", [
            'query' => $query,
            'useDatabase' => $useDatabase,
            'hasWhereClause' => $this->hasWhereClause
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
            'width' => '',
            'formatter' => null,
            'align' => 'left',
            'showTotal' => false,
            'totalType' => 'sum', // 'sum', 'avg', 'count', 'min', 'max'
            'totalLabel' => '',
        ];

        $options = array_merge($defaultOptions, $options);

        if (is_string($options['formatter'])) {
            $options['formatter'] = $this->getPredefinedFormatter($options['formatter']);
        }

        $this->columns[$key] = [
            'label' => $label,
            'options' => $options
        ];

        if ($options['showTotal']) {
            $this->totals[$key] = 0;
            $this->totalTypes[$key] = $options['totalType'];
            $this->totalLabels[$key] = $options['totalLabel'];
        }
    }



    private function getPredefinedFormatter($formatterName)
    {
        $predefinedFormatters = [
            'euro' => function ($value) {
                if ($value === null || $value === '')
                    return '';
                return number_format((float) $value, 2, ',', '.') . ' €';
            },
            'dollar' => function ($value) {
                if ($value === null || $value === '')
                    return '';
                return '$' . number_format((float) $value, 2, '.', ',');
            },
            'percent' => function ($value) {
                if ($value === null || $value === '')
                    return '';
                return number_format((float) $value, 2, ',', '.') . ' %';
            },
            'date' => function ($value) {
                return $value ? date('d.m.Y', strtotime($value)) : '';
            },
            'datetime' => function ($value) {
                return $value ? date('d.m.Y H:i', strtotime($value)) : '';
            },
            'boolean' => function ($value) {
                return $value ? 'Ja' : 'Nein';
            },
            'number' => function ($value) {
                if ($value === null || $value === '')
                    return '';
                return number_format((float) $value, 0, ',', '.');
            },
        ];

        return $predefinedFormatters[$formatterName] ?? null;
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
            'clearable' => true,
            'where' => null,
            'parameterized' => false,
            'filterType' => 'simple'  // Neuer Parameter zur Unterscheidung zwischen einfachen und komplexen Filtern
        ];

        $finalConfig = array_merge($defaultConfig, $config);

        // Wenn 'where' nicht gesetzt ist und es sich um einen einfachen Filter handelt
        if ($finalConfig['where'] === null && $finalConfig['filterType'] === 'simple') {
            $finalConfig['where'] = "$key = ?";
        }

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
            // Logik für nicht-Datenbank-Daten
            $data = $this->data;
            $innerWhereConditions = [];
            $params = [];

            // Filter anwenden
            foreach ($this->filters as $key => $filter) {
                if (isset($_GET['filters'][$key]) && $_GET['filters'][$key] !== '') {
                    $filterValue = $_GET['filters'][$key];

                    if (is_array($filterValue)) {
                        // Behandlung von Mehrfachauswahlen
                        $placeholders = implode(',', array_fill(0, count($filterValue), '?'));
                        $whereCondition = str_replace('IN (?)', "IN ($placeholders)", $filter['config']['where']);
                        $innerWhereConditions[] = $whereCondition;
                        $params = array_merge($params, $filterValue);
                    } else {
                        if (isset($filter['options'][$filterValue]) && $filter['config']['where'] === null) {
                            // Komplexe Abfrage
                            if ($filter['config']['parameterized']) {
                                list($condition, $queryParams) = $this->parseParameterizedQuery($filterValue, $_GET['filter_params'][$key] ?? []);
                                $innerWhereConditions[] = $condition;
                                $params = array_merge($params, $queryParams);
                            } else {
                                $innerWhereConditions[] = $this->validateComplexQuery($filterValue);
                            }
                        } else {
                            // Einfache Abfrage
                            $whereCondition = $filter['config']['where'] ?? "$key = ?";
                            $innerWhereConditions[] = $whereCondition;
                            $params[] = $filterValue;
                        }
                    }

                    $this->debugLog("Applied filter", ["key" => $key, "value" => $filterValue]);
                }
            }

            // Suche anwenden
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

            // Gruppierung anwenden
            if ($this->groupBy) {
                $data = $this->groupNonDatabaseData($data, $this->groupBy);
            }

            // Sortierung anwenden
            $sortColumn = $this->config['sortColumn'];
            $sortDirection = $this->config['sortDirection'];
            if (!$this->groupBy) {
                usort($data, function ($a, $b) use ($sortColumn, $sortDirection) {
                    $result = $a[$sortColumn] <=> $b[$sortColumn];
                    return $sortDirection === 'DESC' ? -$result : $result;
                });
            } else {
                // Sortiere jede Gruppe separat
                foreach ($data as &$group) {
                    usort($group, function ($a, $b) use ($sortColumn, $sortDirection) {
                        $result = $a[$sortColumn] <=> $b[$sortColumn];
                        return $sortDirection === 'DESC' ? -$result : $result;
                    });
                }
            }

            $this->totalRows = $this->groupBy ? array_sum(array_map('count', $data)) : count($data);

            // Summen berechnen
            $this->calculateTotals($data);

            // Paginierung anwenden
            if (!$this->groupBy) {
                $offset = ($this->config['page'] - 1) * $this->config['itemsPerPage'];
                $data = array_slice($data, $offset, $this->config['itemsPerPage']);
            } else {
                // Paginierung für gruppierte Daten
                $paginatedData = [];
                $itemCount = 0;
                $offset = ($this->config['page'] - 1) * $this->config['itemsPerPage'];
                foreach ($data as $groupKey => $groupItems) {
                    if ($itemCount >= $offset && $itemCount < ($offset + $this->config['itemsPerPage'])) {
                        $paginatedData[$groupKey] = array_slice($groupItems, 0, $this->config['itemsPerPage'] - count($paginatedData));
                    }
                    $itemCount += count($groupItems);
                    if (count($paginatedData) >= $this->config['itemsPerPage'])
                        break;
                }
                $data = $paginatedData;
            }

            return $data;
        } else {
            $this->debugLog("Verwende Datenbankabfrage", ['query' => $this->query]);

            // Datenbanklogik
            $innerWhereConditions = [];
            $params = [];
            $hasGroupBy = stripos($this->query, 'GROUP BY') !== false;

            // Filter-Bedingungen erstellen
            foreach ($this->filters as $key => $filter) {
                if (isset($_GET['filters'][$key]) && $_GET['filters'][$key] !== '') {
                    $filterValue = $_GET['filters'][$key];

                    if (is_array($filterValue)) {
                        // Behandlung von Mehrfachauswahlen
                        $placeholders = implode(',', array_fill(0, count($filterValue), '?'));
                        $whereCondition = str_replace('IN (?)', "IN ($placeholders)", $filter['config']['where']);
                        $innerWhereConditions[] = $whereCondition;
                        $params = array_merge($params, $filterValue);
                    } else {
                        if ($filter['config']['filterType'] === 'simple') {
                            // Einfache Abfrage
                            $whereCondition = $filter['config']['where'];
                            $innerWhereConditions[] = $whereCondition;
                            $params[] = $filterValue;
                        } else {
                            // Komplexe Abfrage
                            if ($filter['config']['parameterized']) {
                                list($condition, $queryParams) = $this->parseParameterizedQuery($filterValue, $_GET['filter_params'][$key] ?? []);
                                $innerWhereConditions[] = $condition;
                                $params = array_merge($params, $queryParams);
                            } else {
                                $innerWhereConditions[] = $this->validateComplexQuery($filterValue);
                            }
                        }
                    }

                    $this->debugLog("Applied filter", ["key" => $key, "value" => $filterValue, "type" => $filter['config']['filterType']]);
                }
            }

            // Suchbedingung erstellen
            if (!empty($this->config['search']) && !empty($this->searchableColumns)) {
                $searchConditions = [];
                foreach ($this->searchableColumns as $col) {
                    $searchConditions[] = "$col LIKE ?";
                    $params[] = "%{$this->config['search']}%";
                }
                $innerWhereConditions[] = "(" . implode(' OR ', $searchConditions) . ")";
                $this->debugLog("Applied search condition", $searchConditions);
            }

            $whereOrAnd = $this->hasWhereClause ? 'AND' : 'WHERE';
            $innerWhereClause = $innerWhereConditions ? "$whereOrAnd " . implode(' AND ', $innerWhereConditions) : "";

            // Abfrage basierend auf GROUP BY Klausel modifizieren
            if ($hasGroupBy) {
                $modifiedQuery = preg_replace(
                    '/GROUP BY/i',
                    $innerWhereClause . ' GROUP BY',
                    $this->query
                );
            } else {
                $modifiedQuery = $this->query . ' ' . $innerWhereClause;
            }

            // Gruppierung hinzufügen, falls erforderlich
            if ($this->groupBy) {
                $modifiedQuery = $this->addGroupByToQuery($modifiedQuery, $this->groupBy);
            }

            // Gesamtzahl der Zeilen zählen
            if ($hasGroupBy || $this->groupBy) {
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

            // Sortierspalte validieren
            $validColumns = array_keys($this->columns);
            $sortColumn = in_array($this->config['sortColumn'], $validColumns)
                ? $this->config['sortColumn']
                : reset($validColumns);

            $sortDirection = $this->config['sortDirection'] === 'DESC' ? 'DESC' : 'ASC';

            // Summen berechnen
            $totalColumns = [];
            foreach ($this->totals as $key => $total) {
                switch ($this->totalTypes[$key]) {
                    case 'sum':
                        $totalColumns[] = "SUM({$key}) as total_{$key}";
                        break;
                    case 'avg':
                        $totalColumns[] = "AVG({$key}) as total_{$key}";
                        break;
                    case 'count':
                        $totalColumns[] = "COUNT({$key}) as total_{$key}";
                        break;
                    case 'min':
                        $totalColumns[] = "MIN({$key}) as total_{$key}";
                        break;
                    case 'max':
                        $totalColumns[] = "MAX({$key}) as total_{$key}";
                        break;
                }
            }

            if (!empty($totalColumns)) {
                $totalQuery = "SELECT " . implode(", ", $totalColumns) . " FROM ({$modifiedQuery}) as subquery";
                $stmt = $this->db->prepare($totalQuery);
                if ($stmt) {
                    if (!empty($params)) {
                        $types = str_repeat('s', count($params));
                        $stmt->bind_param($types, ...$params);
                    }
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $totalRow = $result->fetch_assoc();
                    foreach ($this->totals as $key => $total) {
                        $this->totals[$key] = $totalRow["total_{$key}"] ?? 0;
                    }
                    $stmt->close();
                }
            }

            // Daten abrufen
            $offset = ($this->config['page'] - 1) * $this->config['itemsPerPage'];

            // Hauptdatenabfrage
            $query = $modifiedQuery;
            if (!$this->groupBy) {
                $query .= " ORDER BY " . $this->db->real_escape_string($sortColumn) . " " . $sortDirection;
                $query .= " LIMIT ? OFFSET ?";
            }

            $this->debugLog("Final SQL Query", [
                'query' => $query,
                'params' => $params
            ]);

            $stmt = $this->db->prepare($query);
            if ($stmt) {
                if (!$this->groupBy) {
                    $params[] = intval($this->config['itemsPerPage']);
                    $params[] = $offset;
                    $types = str_repeat('s', count($params) - 2) . 'ii';
                } else {
                    $types = str_repeat('s', count($params));
                }

                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }

                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                if ($this->groupBy) {
                    $groupedData = [];
                    foreach ($data as $row) {
                        $groupValue = $row[$this->groupBy];
                        if (!isset($groupedData[$groupValue])) {
                            $groupedData[$groupValue] = [];
                        }
                        $groupedData[$groupValue][] = $row;
                    }
                    $data = $groupedData;

                    // Sortiere jede Gruppe
                    foreach ($data as &$group) {
                        usort($group, function ($a, $b) use ($sortColumn, $sortDirection) {
                            $result = $a[$sortColumn] <=> $b[$sortColumn];
                            return $sortDirection === 'DESC' ? -$result : $result;
                        });
                    }

                    // Paginierung für gruppierte Daten
                    $paginatedData = [];
                    $itemCount = 0;
                    foreach ($data as $groupKey => $groupItems) {
                        if ($itemCount >= $offset && $itemCount < ($offset + $this->config['itemsPerPage'])) {
                            $paginatedData[$groupKey] = array_slice($groupItems, 0, $this->config['itemsPerPage'] - count($paginatedData));
                        }
                        $itemCount += count($groupItems);
                        if (count($paginatedData) >= $this->config['itemsPerPage'])
                            break;
                    }
                    $data = $paginatedData;
                }

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
    private function validateComplexQuery($query)
    {
        // Entfernen von Mehrfach-Leerzeichen und Trimmen
        $query = preg_replace('/\s+/', ' ', trim($query));

        // Überprüfen auf unerlaubte SQL-Schlüsselwörter
        $disallowedKeywords = ['DELETE', 'DROP', 'TRUNCATE', 'INSERT', 'UPDATE', 'ALTER', '--'];
        foreach ($disallowedKeywords as $keyword) {
            if (stripos($query, $keyword) !== false) {
                throw new Exception("Unerlaubtes SQL-Schlüsselwort gefunden: $keyword");
            }
        }

        // Überprüfen der Operatoren und Funktionen
        $tokens = preg_split('/\s+/', $query);
        foreach ($tokens as $token) {
            if (
                in_array(strtoupper($token), $this->allowedOperators) ||
                in_array(strtoupper($token), $this->allowedFunctions)
            ) {
                continue;
            }
            // Hier könnten weitere Überprüfungen hinzugefügt werden
        }

        return $query;
    }

    private function parseParameterizedQuery($query, $params)
    {
        $processedQuery = $query;
        $queryParams = [];

        // Ersetzen Sie Platzhalter wie :param mit ? und sammeln Sie die Parameterwerte
        preg_match_all('/:(\w+)/', $query, $matches);
        foreach ($matches[1] as $param) {
            if (!isset($params[$param])) {
                throw new Exception("Fehlender Parameter: $param");
            }
            $processedQuery = str_replace(":$param", '?', $processedQuery);
            $queryParams[] = $params[$param];
        }

        return [$this->validateComplexQuery($processedQuery), $queryParams];
    }

    private function addGroupByToQuery($query, $groupBy)
    {
        // Überprüfen, ob die Abfrage bereits ein GROUP BY enthält
        if (stripos($query, 'GROUP BY') === false) {
            // Wenn nicht, füge GROUP BY hinzu
            $query .= " GROUP BY $groupBy";
        } else {
            // Wenn ja, erweitere das bestehende GROUP BY
            $query = preg_replace('/GROUP BY (.*)/i', "GROUP BY $1, $groupBy", $query);
        }
        return $query;
    }

    private function groupNonDatabaseData($data, $groupBy)
    {
        $groupedData = [];
        foreach ($data as $item) {
            $groupValue = $item[$groupBy] ?? 'Andere';
            if (!isset($groupedData[$groupValue])) {
                $groupedData[$groupValue] = [];
            }
            $groupedData[$groupValue][] = $item;
        }
        return $groupedData;
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

        $html = "<button id='{$id}' class='ui {$button['class']} button' {$attributes}>";
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

            $html .= "<button id='{$id}' {$attributes} class='ui " . htmlspecialchars($buttonClass, ENT_QUOTES, 'UTF-8') . " button'>";
            if (!empty($button['icon'])) {
                $html .= "<i class='" . htmlspecialchars($button['icon'], ENT_QUOTES, 'UTF-8') . " icon'></i>";
            }
            if (!empty($button['label'])) {
                $html .= htmlspecialchars($button['label'], ENT_QUOTES, 'UTF-8');
            }
            $html .= "</button>";
        }

        return "<div class='ui buttons'>$html</div>";
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

    private function calculateTotals($data)
    {
        foreach ($this->totals as $key => $total) {
            $values = array_column($data, $key);
            switch ($this->totalTypes[$key]) {
                case 'sum':
                    $this->totals[$key] = array_sum($values);
                    break;
                case 'avg':
                    $this->totals[$key] = count($values) > 0 ? array_sum($values) / count($values) : 0;
                    break;
                case 'count':
                    $this->totals[$key] = count($values);
                    break;
                case 'min':
                    $this->totals[$key] = min($values);
                    break;
                case 'max':
                    $this->totals[$key] = max($values);
                    break;
            }
        }
    }



    private function generateTotalRow($position = 'header')
    {
        $html = "<tr class='active'>";
        $isFirst = true;

        // Berücksichtigung der linken Button-Spalte
        if (isset($this->buttonColumnTitles['left'])) {
            $html .= "<td></td>";
        }

        foreach ($this->columns as $key => $column) {
            $align = $column['options']['align'];
            if (isset($this->totals[$key])) {
                $value = $this->formatColumnValue($this->totals[$key], $column, null);
                $label = $this->totalLabels[$key];
                $totalType = ucfirst($this->totalTypes[$key]);
                if ($isFirst) {
                    $html .= "<td class='{$align} aligned'><strong>{$label}</strong> {$value}</td>";
                    $isFirst = false;
                } else {
                    $html .= "<td class='{$align} aligned'>{$value}</td>";
                }
            } else {
                $html .= "<td></td>";
            }
        }

        // Berücksichtigung der rechten Button-Spalte
        if (isset($this->buttonColumnTitles['right'])) {
            $html .= "<td></td>";
        }

        $html .= "</tr>";
        return $html;
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

        // Gruppieren-Dropdown hinzufügen
        $html .= $this->generateGroupByDropdown();

        if ($this->groupBy) {
            $html .= $this->generateGroupedTable($data);
        } else {
            $html .= "<table class='{$tableClasses}'>";
            $html .= $this->generateTableHeader();

            // Summenzeile am Anfang der Tabelle
            // if ($this->hasTotals()) {
            //     $html .= "<thead>";
            //     $html .= $this->generateTotalRow('header');
            //     $html .= "</thead>";
            // }

            $html .= "<tbody>";

            if (empty($data) && $this->config['showNoDataMessage']) {
                $html .= $this->generateNoDataMessage();
            } else {
                foreach ($data as $item) {
                    $html .= $this->generateTableRow($item);
                }
            }

            $html .= "</tbody>";

            // Summenzeile am Ende der Tabelle
            if ($this->hasTotals()) {

                $html .= $this->generateTotalRow('footer');

            }
            $html .= "<tfoot>";
            if ($this->config['showFooter']) {
                $html .= $this->generateTableFooter($totalRows, $currentPage, $totalPages);
            }

            $html .= "</tfoot>";
            $html .= "</table>";
        }

        if ($this->config['showPagination']) {
            $html .= $this->generatePagination($currentPage, $totalPages);
        }

        // Render bottom external buttons
        $html .= $this->renderExternalButtons('bottom');

        $html .= "</div>";
        $html .= $this->renderModals();

        return $html;
    }

    private function generateGroupedTable($data)
    {
        $html = '';
        foreach ($data as $groupValue => $groupItems) {
            $html .= "<h3 class='ui header'>{$groupValue}</h3>";
            $html .= "<table class='{$this->buildTableClasses()}'>";
            $html .= $this->generateTableHeader();

            if ($this->hasTotals()) {
                $html .= "<thead>";
                $html .= $this->generateTotalRow('header');
                $html .= "</thead>";
            }

            $html .= "<tbody>";

            foreach ($groupItems as $item) {
                $html .= $this->generateTableRow($item);
            }

            $html .= "</tbody>";

            if ($this->hasTotals()) {
                $html .= "<tfoot>";
                $html .= $this->generateTotalRow('footer');
                $html .= "</tfoot>";
            }

            $html .= "</table>";
        }
        return $html;
    }

    private function hasTotals()
    {
        return !empty($this->totals);
    }




    private function generateGroupByDropdown()
    {
        if (empty($this->groupByOptions)) {
            return '';
        }

        $html = "<div class='ui form' style='margin-bottom: 20px;'>";
        $html .= "<div class='field'>";
        $html .= "<label>Gruppieren nach:</label>";
        $html .= "<select id='groupBySelect' class='ui clearable dropdown'>";
        $html .= "<option value=''>Keine Gruppierung</option>";

        foreach ($this->groupByOptions as $column => $label) {
            $selected = ($this->groupBy == $column) ? 'selected' : '';
            $html .= "<option value='{$column}' {$selected}>{$label}</option>";
        }

        $html .= "</select>";
        $html .= "</div>";
        $html .= "</div>";

        return $html;
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

            $dropdownClass = 'ui fluid dropdown';
            if ($filter['config']['searchable']) {
                $dropdownClass .= ' search';
            }
            if ($filter['config']['multiple']) {
                $dropdownClass .= ' multiple';
            }
            if ($filter['config']['clearable']) {
                $dropdownClass .= ' clearable';
            }
            $dropdownClass .= ' ' . $filter['config']['customClass'];

            $html .= "<select class='{$dropdownClass}' name='{$filterId}' id='{$filterId}'";
            if ($filter['config']['multiple']) {
                $html .= " multiple='multiple'";
            }
            if ($filter['config']['maxSelections']) {
                $html .= " data-max-selections='{$filter['config']['maxSelections']}'";
            }
            if ($filter['config']['fullTextSearch']) {
                $html .= " data-full-text-search='true'";
            }
            if ($filter['config']['allowAdditions']) {
                $html .= " data-allow-additions='true'";
            }
            $html .= ">";

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


    private function buildTableClasses()
    {
        $classes = [$this->config['tableClasses']];
        if ($this->config['striped'])
            $classes[] = 'striped';
        if ($this->config['selectable'])
            $classes[] = 'selectable';
        if ($this->config['celled'])
            $classes[] = 'celled';
        if ($this->config['color'])
            $classes[] = $this->config['color'];
        if ($this->config['size'])
            $classes[] = $this->config['size'];
        return implode(' ', array_filter($classes));
    }

    private function generateTableHeader()
    {
        $html = "<thead class='{$this->config['headerClasses']}'><tr>";

        // Linke Button-Spalte
        if (isset($this->buttonColumnTitles['left'])) {
            $html .= "<th>{$this->buttonColumnTitles['left']}</th>";
        }

        // Datenspalten
        foreach ($this->columns as $key => $column) {
            $sortClass = $this->getSortClass($key);
            $sortIcon = $this->getSortIcon($key);
            $width = $column['options']['width'] ? "width: {$column['options']['width']};" : "";
            $html .= "<th class='sortable {$sortClass}' data-column='{$key}' style='{$width}'>{$column['label']} {$sortIcon}</th>";
        }

        // Rechte Button-Spalte
        if (isset($this->buttonColumnTitles['right'])) {
            $html .= "<th>{$this->buttonColumnTitles['right']}</th>";
        }

        $html .= "</tr>";

        $html .= "</thead>";
        // Summenzeile in der Kopfzeile, falls erforderlich
        if ($this->hasTotals()) {
            $html .= $this->generateTotalRow('header');
        }


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
            $align = $column['options']['align'];
            $html .= "<td class='{$this->config['cellClasses']} {$align} aligned' style='{$width}'>{$value}</td>";
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
        return "<tr><td colspan='{$colspan}'>{$footerText}</td></tr>";
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



    private function debugLog($message, $data = null)
    {
        if ($this->config['debug']) {
            $logMessage = date('[Y-m-d H:i:s] ') . $message;
            if ($data !== null) {
                $logMessage .= "\nData: " . print_r($data, true);
            }
            file_put_contents(__DIR__ . '/listgenerator_debug.log', $logMessage . "\n", FILE_APPEND);
        }
    }
}

?>