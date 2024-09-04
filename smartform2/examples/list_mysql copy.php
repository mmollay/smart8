<?php
//Neue Version mit ajax
include __DIR__ . '/../ListGenerator.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$listGenerator = new ListGenerator([
    'listId' => 'userList',
    'contentId' => 'content1',
    'itemsPerPage' => 10,
    'sortColumn' => $_GET['sort'] ?? 'u.id',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'ASC'),
    'search' => $_GET['search'] ?? '',
    'page' => intval($_GET['page'] ?? 1),
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Daten gefunden.',
    'showFooter' => true,
    'footerTemplate' => 'Gesamt: {totalRows} Einträge | Seite {currentPage} von {totalPages}',
    'showPagination' => true,
    'tableClasses' => 'ui celled table',
    'headerClasses' => 'ui inverted blue table',
    'rowClasses' => '',
    'cellClasses' => '',
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'compact' => true,
    'color' => 'blue',
    'size' => 'small',
    'width' => '1200px',
    'filterClass' => 'ui message',
    'debug' => true,
]);

// Datenbank-Verbindung
$db = new mysqli('localhost', 'root', 'Jgewl21;', 'demo');

if ($db->connect_error) {
    die("Verbindung fehlgeschlagen: " . $db->connect_error);
}

// Vereinfachte SQL-Abfrage
$query = "
    SELECT
        u.id, u.first_name, u.last_name, u.email, u.role, u.status, u.created_at, u.hire_date,
        ud.city,
        d.name AS department_name,
        COUNT(DISTINCT up.project_id) AS project_count
    FROM
        users u
    LEFT JOIN
        user_details ud ON u.id = ud.user_id
    LEFT JOIN
        departments d ON u.department_id = d.id
    LEFT JOIN
        user_projects up ON u.id = up.user_id
    WHERE 1=1
    GROUP BY
        u.id
";

$listGenerator->setSearchableColumns(['first_name', 'last_name', 'email', 'role', 'status', 'name', 'city']);
$listGenerator->setDatabase($db, $query, true);

// Vereinfachte Gruppierungsoptionen
$listGenerator->addGroupByOption('department_name', 'Abteilung');
$listGenerator->addGroupByOption('role', 'Rolle');
$listGenerator->addGroupByOption('status', 'Status');

// Setzen Sie die aktuelle Gruppierung (z.B. basierend auf einem GET-Parameter)
if (isset($_GET['groupBy'])) {
    $listGenerator->setGroupBy($_GET['groupBy']);
}

// Vereinfachte Filter
$listGenerator->addFilter('u.status', 'Status', ['aktiv' => 'Aktiv', 'inaktiv' => 'Inaktiv']);
$listGenerator->addFilter('u.role', 'Rolle', ['admin' => 'Admin', 'user' => 'User', 'editor' => 'Editor']);
$listGenerator->addFilter('d.name', 'Abteilung', ['IT' => 'IT', 'HR' => 'HR', 'Marketing' => 'Marketing']);


// Neuer Filter für Einstellungsmonat
$listGenerator->addFilter('hire_date', 'Einstellungsmonat', [
    '01' => 'Januar',
    '02' => 'Februar',
    '03' => 'März',
    '04' => 'April',
    '05' => 'Mai',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'August',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Dezember'
], [
    'type' => 'dropdown',
    'multiple' => true,
    'placeholder' => 'Monate auswählen',
    'searchable' => true,
    'maxSelections' => 3,
    'fullTextSearch' => true,
    'allowAdditions' => false,
    'customClass' => 'my-custom-dropdown',
    'clearable' => true,
    'where' => 'DATE_FORMAT(hire_date, "%m") IN (?)'
]);


// Spalten definieren mit einfachen Formatierungen
$listGenerator->addColumn('id', 'ID');
$listGenerator->addColumn('first_name', 'Vorname');
$listGenerator->addColumn('last_name', 'Nachname');
$listGenerator->addColumn('email', 'E-Mail');
$listGenerator->addColumn('role', 'Rolle');
$listGenerator->addColumn('status', 'Status', [
    'formatter' => function ($value) {
        $color = $value === 'Aktiv' ? 'green' : 'red';
        return "<span class='ui {$color} label'>{$value}</span>";
    },
    'allowHtml' => true
]);
$listGenerator->addColumn('department_name', 'Abteilung');
$listGenerator->addColumn('project_count', 'Anzahl Projekte');
$listGenerator->addColumn('city', 'Stadt');
$listGenerator->addColumn('hire_date', 'Einstelldatum', [
    'formatter' => function ($value) {
        return $value ? date('d.m.Y', strtotime($value)) : '';
    }
]);

// Buttons definieren (unverändert)
$listGenerator->addButton('edit', [
    'label' => '',
    'icon' => 'edit',
    'position' => 'left',
    'class' => 'blue tiny',
    'modalId' => 'editUser',
    'group' => 'manage1',
    'params' => ['id'],
    'conditions' => [
        function ($row) {
            return $row['status'] === 'Aktiv';
        },
        function ($row) {
            return in_array($row['role'], ['Admin', 'Editor']);
        }
    ],
    'popup' => 'Benutzer bearbeiten'
]);

$listGenerator->addButton('deactivate', [
    'label' => '',
    'icon' => 'power off',
    'class' => 'orange tiny',
    'position' => 'left',
    'callback' => 'deactivateUser',
    'params' => ['id'],
    'conditions' => [
        function ($row) {
            return $row['status'] === 'Aktiv';
        },
        function ($row) {
            return $row['role'] !== 'Admin';
        }
    ],
    'popup' => 'Benutzer deaktivieren'
]);

$listGenerator->addButton('activate', [
    'label' => '',
    'icon' => 'power off',
    'class' => 'green tiny',
    'position' => 'left',
    'callback' => 'activateUser',
    'params' => ['id'],
    'conditions' => [
        function ($row) {
            return $row['status'] === 'Inaktiv';
        }
    ],
    'popup' => 'Benutzer aktivieren'
]);

$listGenerator->addButton('delete', [
    'label' => '',
    'icon' => 'trash',
    'class' => 'red tiny',
    'position' => 'right',
    'callback' => 'deleteUser',
    'params' => ['id'],
    'confirmMessage' => 'Sind Sie sicher, dass Sie diesen Benutzer löschen möchten?',
    'conditions' => [
        function ($row) {
            return $row['status'] === 'Inaktiv';
        },
        function ($row) {
            return $row['project_count'] == 0;
        }
    ],
    'popup' => 'Benutzer löschen'
]);

$listGenerator->addButton('viewProjects', [
    'label' => '',
    'icon' => 'folder open',
    'class' => 'teal tiny',
    'position' => 'right',
    'modalId' => 'viewUserProjects',
    'params' => ['id'],
    'conditions' => [
        function ($row) {
            return $row['project_count'] > 0;
        }
    ],
    'popup' => 'Projekte anzeigen'
]);

// Setzen der Spaltentitel für die Buttons
$listGenerator->setButtonColumnTitle('left', 'Aktionen', 'left');
$listGenerator->setButtonColumnTitle('right', 'Weitere Aktionen', 'center');

// Externe Buttons
$listGenerator->addExternalButton('new_entry', [
    'icon' => 'plus',
    'class' => 'primary',
    'position' => 'inline',
    'alignment' => 'right',
    'title' => 'Neuer Eintrag'
]);

$listGenerator->addExternalButton('export_csv', [
    'icon' => 'download',
    'class' => 'secondary',
    'position' => 'inline',
    'alignment' => 'right',
    'title' => 'CSV exportieren',
    'callback' => 'exportToCsv'
]);

// Modal-Definitionen
$listGenerator->addModal('editUser', [
    'title' => 'Benutzer bearbeiten',
    'content' => 'edit_user.php',
    'size' => 'small',
]);

$listGenerator->addModal('viewUserProjects', [
    'title' => 'Benutzerprojekte',
    'content' => 'view_user_projects.php',
    'size' => 'small',
]);

echo $listGenerator->generateList();