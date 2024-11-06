<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fomantic-UI Tabelle</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.8.8/semantic.min.css" >
</head>
<body>

<div class="ui container grid">
    <div class="ui row" style="margin-top: 20px;">
        <button class="ui green button" onclick="addRow()">Hinzufügen</button>
    </div>
    <?php
    // Erstellen eines Arrays mit einigen Namen
    $namen = array("Anna", "Bernd", "Carla", "Daniel", "Elena");

    // HTML-Ausgabe starten
    echo '<div class="ui row" style="margin-top: 20px;">
        <table class="ui celled striped padded sortable table" style="background-color: #f9fafb;">';
    echo '<thead style="background-color: #2185d0; color: #ffffff;"><tr><th class="center aligned">Aktion</th><th class="center aligned">Aktion</th><th class="center aligned" onclick="sortTable(0, this)">Name <i class="sort icon"></i></th><th class="center aligned" onclick="sortTable(1, this)">Alter <i class="sort icon"></i></th></tr></thead>';
    echo '<tbody class="center aligned" style="background-color: #ffffff;" id="tableBody">';

    // Durch die Namen iterieren und sie als Tabellenzeilen ausgeben
    foreach ($namen as $index => $name) {
        $alter = rand(18, 65); // Zufälliges Alter für jeden Namen
        echo '<tr style="background-color: #e8f0fe;">
              <td><button class="ui red button" onclick="deleteRow(this)">Löschen</button></td><td>' . htmlspecialchars($name) . '</td><td>' . $alter . '</td></tr>';
    }

    // HTML-Ausgabe beenden
    echo '</tbody>';
    echo '</table>
    </div>';
    ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" ></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.8.8/semantic.min.js" ></script>
<script>
function sortTable(columnIndex) {
    const table = document.querySelector('.sortable.table');
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    const sortedRows = rows.sort((a, b) => {
        const aText = a.children[columnIndex].textContent.trim().toLowerCase();
        const bText = b.children[columnIndex].textContent.trim().toLowerCase();
        if (aText < bText) return -1;
        if (aText > bText) return 1;
        return 0;
    });
    const tbody = table.querySelector('tbody');
    tbody.innerHTML = '';
    sortedRows.forEach(row => tbody.appendChild(row));
}
</script>
<script>
function sortTable(columnIndex, header) {
    const table = document.querySelector('.sortable.table');
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    const currentOrder = header.getAttribute('data-order') || 'asc';
    const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
    header.setAttribute('data-order', newOrder);
    
    const sortedRows = rows.sort((a, b) => {
        const aText = a.children[columnIndex].textContent.trim().toLowerCase();
        const bText = b.children[columnIndex].textContent.trim().toLowerCase();
        if (aText < bText) return newOrder === 'asc' ? -1 : 1;
        if (aText > bText) return newOrder === 'asc' ? 1 : -1;
        return 0;
    });
    const tbody = table.querySelector('tbody');
    tbody.innerHTML = '';
    sortedRows.forEach(row => tbody.appendChild(row));

    // Update sort icon
    table.querySelectorAll('th i.sort.icon').forEach(icon => icon.className = 'sort icon');
    header.querySelector('i.sort.icon').className = newOrder === 'asc' ? 'sort ascending icon' : 'sort descending icon';
}
</script>
<script>
function deleteRow(button) {
    const row = button.parentElement.parentElement;
    row.parentNode.removeChild(row);
}
</script>
<script>
function addRow() {
    const tableBody = document.getElementById('tableBody');
    const newRow = document.createElement('tr');
    newRow.style.backgroundColor = '#e8f0fe';
    const alter = Math.floor(Math.random() * (65 - 18 + 1)) + 18;
    newRow.innerHTML = `
        <td><button class="ui red button" onclick="deleteRow(this)">Löschen</button></td>
        <td>Neuer Name</td>
        <td>${alter}</td>
    `;
    tableBody.appendChild(newRow);
}
</script>
</body>
</html>