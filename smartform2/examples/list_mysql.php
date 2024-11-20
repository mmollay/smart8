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

// Optimierte SQL-Abfrage
$query = "
    SELECT
        u.id, u.first_name, u.last_name, u.email, u.role, u.status, u.created_at, u.hire_date, u.salary,
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
    GROUP BY
        u.id
";

$listGenerator->setSearchableColumns(['u.first_name', 'u.last_name', 'u.email', 'u.role', 'u.status', 'd.name', 'ud.city']);
$listGenerator->setDatabase($db, $query, true);

// Gruppierungsoptionen
$listGenerator->addGroupByOption('department_name', 'Abteilung');
$listGenerator->addGroupByOption('role', 'Rolle');
$listGenerator->addGroupByOption('status', 'Status');

// Setzen Sie die aktuelle Gruppierung (z.B. basierend auf einem GET-Parameter)
if (isset($_GET['groupBy'])) {
    $listGenerator->setGroupBy($_GET['groupBy']);
}

// Filter
$listGenerator->addFilter('u.status', 'Status', ['Aktiv' => 'Aktiv', 'Inaktiv' => 'Inaktiv']);
$listGenerator->addFilter('u.role', 'Rolle', ['Admin' => 'Admin', 'User' => 'User', 'Editor' => 'Editor']);
$listGenerator->addFilter('d.name', 'Abteilung', ['IT' => 'IT', 'HR' => 'HR', 'Marketing' => 'Marketing', 'Finance' => 'Finance']);

// Komplexe Filter
$listGenerator->addFilter('salary_range', 'Gehaltsbereich', [
    'salary < 50000' => 'Unter 50.000',
    'salary >= 50000 AND salary < 70000' => '50.000 - 69.999',
    'salary >= 70000' => '70.000 und mehr'
], ['filterType' => 'complex']);

$listGenerator->addFilter('hire_date_range', 'Einstellungszeitraum', [
    'YEAR(hire_date) = YEAR(CURDATE())' => 'Dieses Jahr',
    'YEAR(hire_date) = YEAR(CURDATE()) - 1' => 'Letztes Jahr',
    'hire_date <= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)' => 'Vor mehr als 2 Jahren'
], ['filterType' => 'complex']);

// Spalten definieren
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
$listGenerator->addColumn('project_count', 'Anzahl Projekte', [
    'formatter' => 'number',
    'align' => 'right',
    'showTotal' => true,
    // 'totalType' => 'sum', //avg
    'totalLabel' => ''
]);
$listGenerator->addColumn('city', 'Stadt');
$listGenerator->addColumn('hire_date', 'Einstelldatum', [
    'formatter' => function ($value) {
        return $value ? date('d.m.Y', strtotime($value)) : '';
    }
]);
$listGenerator->addColumn('salary', 'Gehalt', [
    'formatter' => 'euro',
    'align' => 'right',
    'showTotal' => true,
    'totalType' => 'sum', //avg
    'totalLabel' => 'Durchschnitt:'
]);

// Buttons definieren
$listGenerator->addButton('edit', [
    'icon' => 'edit',
    'position' => 'left',
    'class' => 'blue mini',
    'modalId' => 'modal_form_edit',
    'popup' => 'Bearbeiten',
    'params' => ['update_id' => 'id']
]);

$listGenerator->addButton('delete', [
    'icon' => 'trash',
    'position' => 'right',
    'class' => 'red mini',
    'modalId' => 'modal_form_delete',
    'popup' => 'Löschen',
    'params' => ['delete_id' => 'id'],
    'conditions' => [
        function ($row) {
            return $row['role'] !== 'Admin'; // Admins können nicht gelöscht werden
        }
    ]
]);

$listGenerator->addButton('activate', [
    'icon' => 'check',
    'position' => 'right',
    'class' => 'green mini',
    'popup' => 'Aktivieren',
    'params' => ['user_id' => 'id'],
    'conditions' => [
        function ($row) {
            return $row['status'] === 'Inaktiv';
        }
    ],
    'callback' => 'activateUser'
]);

$listGenerator->addButton('deactivate', [
    'icon' => 'ban',
    'position' => 'right',
    'class' => 'orange mini',
    'popup' => 'Deaktivieren',
    'params' => ['user_id' => 'id'],
    'conditions' => [
        function ($row) {
            return $row['status'] === 'Aktiv' && $row['role'] !== 'Admin';
        }
    ],
    'callback' => 'deactivateUser'
]);

$listGenerator->addButton('reset_password', [
    'icon' => 'key',
    'position' => 'right',
    'class' => 'teal mini',
    'popup' => 'Passwort zurücksetzen',
    'params' => ['user_id' => 'id'],
    'callback' => 'resetPassword'
]);

$listGenerator->addButton('edit_permissions', [
    'icon' => 'lock',
    'position' => 'right',
    'class' => 'purple mini',
    'popup' => 'Berechtigungen bearbeiten',
    'params' => ['user_id' => 'id'],
    'modalId' => 'modal_edit_permissions'
]);

// Modals definieren
$listGenerator->addModal('modal_form_edit', [
    'title' => "<i class='icon edit'></i> Benutzer bearbeiten",
    'class' => 'long',
    'url' => 'form_edit.php'
]);

$listGenerator->addModal('modal_form_delete', [
    'title' => "Benutzer entfernen",
    'class' => 'small',
    'url' => 'form_delete.php'
]);

$listGenerator->addModal('modal_edit_permissions', [
    'title' => "<i class='icon lock'></i> Benutzerberechtigungen bearbeiten",
    'class' => 'small',
    'url' => 'form_edit_permissions.php'
]);

// Externe Buttons
$listGenerator->addExternalButton('new_user', [
    'icon' => 'plus',
    'class' => 'ui blue circular button',
    'position' => 'top',
    'alignment' => 'left',
    'title' => "Neuen Benutzer erstellen",
    'modalId' => 'modal_form_new_user',
]);



// Export-Button hinzufügen
$listGenerator->addExternalButton('export', [
    'icon' => 'download',
    'class' => 'ui green circular button',
    'position' => 'top',
    'alignment' => 'right',
    'title' => "Als CSV exportieren",
    'onclick' => "window.location.href='export.php?format=csv&listId=" . $listGenerator->getConfig()['listId'] . "'",
]);

// Export-Button hinzufügen
$listGenerator->addExternalButton('export2', [
    'icon' => 'download',
    'class' => 'ui green circular button',
    'position' => 'top',
    'alignment' => 'right',
    'title' => "Als CSV exportieren",
    'onclick' => "window.location.href='export.php?format=csv&listId=" . $listGenerator->getConfig()['listId'] . "'",
]);


$listGenerator->addModal('modal_form_new_user', [
    'title' => "<i class='icon plus'></i> Neuen Benutzer erstellen",
    'class' => 'long',
    'url' => 'form_new_user.php'
]);

// Liste generieren und ausgeben
echo $listGenerator->generateList();

// Datenbankverbindung schließen
if (isset($db)) {
    $db->close();
}
?>

<script>
    function activateUser(params) {
        $.ajax({
            url: 'activate_user.php',
            method: 'POST',
            data: params,
            success: function (response) {
                alert('Benutzer wurde aktiviert');
                location.reload(); // Seite neu laden, um Änderungen zu sehen
            },
            error: function () {
                alert('Fehler beim Aktivieren des Benutzers');
            }
        });
    }

    function deactivateUser(params) {
        $.ajax({
            url: 'deactivate_user.php',
            method: 'POST',
            data: params,
            success: function (response) {
                alert('Benutzer wurde deaktiviert');
                location.reload(); // Seite neu laden, um Änderungen zu sehen
            },
            error: function () {
                alert('Fehler beim Deaktivieren des Benutzers');
            }
        });
    }

    function resetPassword(params) {
        $.ajax({
            url: 'reset_password.php',
            method: 'POST',
            data: params,
            success: function (response) {
                alert('Passwort wurde zurückgesetzt');
            },
            error: function () {
                alert('Fehler beim Zurücksetzen des Passworts');
            }
        });
    }
</script>