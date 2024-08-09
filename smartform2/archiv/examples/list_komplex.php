<?php
require_once '../ListGenerator.php';
require_once __DIR__ . '/mysql.php';

// ListGenerator Initialisierung
$listGenerator = new ListGenerator([
    'listId' => 'martin',
    'itemsPerPage' => 5,
    'sortColumn' => $_GET['sortColumn'] ?? 'id',
    'sortDirection' => $_GET['sortDirection'] ?? 'ASC',
    'search' => $_GET['search'] ?? '',
    'page' => intval($_GET['page'] ?? 1),
    // Nachrichten und Anzeige-Optionen
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Benutzer gefunden.',
    'showFooter' => true,
    'footerTemplate' => 'Gesamt: {totalRows} Benutzer | Seite {currentPage} von {totalPages}',
    'showPagination' => true,
    // Tabellen-Styling
    'tableClasses' => 'ui celled table',
    'headerClasses' => 'ui inverted blue table',
    'rowClasses' => '',
    'cellClasses' => '',
    // Fomantic UI spezifische Optionen
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'compact' => true,
    'color' => 'blue',
    'size' => 'small',
    'width' => '1200px',
]);

// Datenbank-Abfrage
$query = "
    SELECT 
        u.id, u.first_name, u.last_name, u.email, u.role, u.status, u.created_at,
        ud.phone, ud.address, ud.birth_date
    FROM 
        users u
    LEFT JOIN 
        user_details ud ON u.id = ud.user_id
";
$listGenerator->setDatabase($db, $query, true);

// Filter hinzufügen
$listGenerator->addFilter('status', 'Status', ['Aktiv' => 'Aktiv', 'Inaktiv' => 'Inaktiv']);
$listGenerator->addFilter('role', 'Rolle', ['Admin' => 'Admin', 'User' => 'User', 'Editor' => 'Editor']);

// Buttons hinzufügen
$listGenerator->addButton('view_user', [
    'label' => '',
    'callback' => 'viewUser',
    'icon' => 'eye',
    'class' => 'ui green tiny button',
    'position' => 'left',
    'group' => 'manage1',
    'params' => ['id']
]);

$listGenerator->addButton('edit', [
    'label' => '',
    'icon' => 'edit',
    'position' => 'left',
    'class' => 'ui blue tiny button',
    'modalId' => 'editUser',
    'group' => 'manage1',
    'params' => ['id', 'first_name', 'last_name', 'email']
]);

$listGenerator->addButton('delete', [
    'label' => '',
    'callback' => 'deleteUser',
    'icon' => 'trash',
    'class' => 'ui red tiny button',
    'position' => 'right',
    'group' => 'manage',
    'confirmMessage' => 'Sind Sie sicher, dass Sie diesen Benutzer löschen möchten?',
    'params' => ['id']
]);

$listGenerator->addButton('edit_right', [
    'label' => '',
    'callback' => 'editUser',
    'icon' => 'edit',
    'class' => 'ui blue tiny button',
    'position' => 'right',
    'group' => 'manage',
    'conditions' => [
        function ($row) {
            return $row['status'] === 'Aktiv';
        },
        function ($row) {
            return $row['role'] !== 'SuperAdmin';
        }
    ],
    'params' => ['id', 'email']
]);

// Button-Gruppen und Spalten-Titel setzen
$listGenerator->setButtonGroupPosition('manage', 'right');
$listGenerator->setButtonColumnTitle('left', 'Aktionen');
$listGenerator->setButtonColumnTitle('right', 'Verwaltung');

$listGenerator->addModal('editUser', [
    'title' => 'Benutzer bearbeiten',
    'content' => 'edit_user.php',
    'size' => 'small',
]);

// Spalten definieren
$listGenerator->addColumn('id', 'ID', ['width' => 2]);
$listGenerator->addColumn('first_name', 'Vorname', ['width' => 4]);
$listGenerator->addColumn('last_name', 'Nachname', ['width' => 4]);
$listGenerator->addColumn('email', 'E-Mail', ['width' => 'collapsing']);
$listGenerator->addColumn('role', 'Rolle', [
    'width' => 'collapsing',
    'formatter' => function ($value) {
        $color = $value == 'Admin' ? 'red' : ($value == 'Editor' ? 'green' : 'blue');
        return "<div class='ui {$color} label'>{$value}</div>";
    }
]);
$listGenerator->addColumn('status', 'Status', [
    'width' => 'collapsing',
    'formatter' => function ($value) {
        $icon = $value == 'Aktiv' ? 'check circle' : 'times circle';
        $color = $value == 'Aktiv' ? 'green' : 'red';
        return "<i class='{$icon} {$color} icon'></i> {$value}";
    }
]);
$listGenerator->addColumn('created_at', 'Erstellt am', [
    'width' => 'collapsing',
    'formatter' => function ($value) {
        return "<div class='ui label'><i class='calendar icon'></i> " . date('d.m.Y H:i', strtotime($value)) . "</div>";
    }
]);
$listGenerator->addColumn('phone', 'Telefon');
$listGenerator->addColumn('address', 'Adresse');
$listGenerator->addColumn('birth_date', 'Geburtsdatum', [
    'formatter' => function ($value) {
        return $value ? date('d.m.Y', strtotime($value)) : '-';
    }
]);

// AJAX-Anfrage Behandlung
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo $listGenerator->generate(true);
    exit;
}

// HTML-Ausgabe
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erweiterte Benutzerliste</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.2/dist/semantic.min.css">
</head>

<body>
    <div class="ui container" style="padding-top: 20px;">
        <h1 class="ui header">Erweiterte Benutzerliste</h1>
        <?php echo $listGenerator->generate(); ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.2/dist/semantic.min.js"></script>
    <script>
        function editUser(id, params) {
            var email = params[1];
            alert('Bearbeite Benutzer: ID ' + id + ', E-Mail: ' + email);
        }

        function deleteUser(id) {
            if (confirm('Möchten Sie den Benutzer mit ID ' + id + ' wirklich löschen?')) {
                alert('Benutzer mit ID ' + id + ' gelöscht');
            }
        }

        function viewUser(id, params) {
            var firstName = params[1];
            var lastName = params[2];
            var role = params[3];
            var email = params[4];
            alert('Zeige Details für Benutzer: ' + firstName + ' ' + lastName + ' (ID: ' + id + ', Rolle: ' + role + ', E-Mail: ' + email + ')');
        }
    </script>
</body>

</html>