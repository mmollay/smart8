<!DOCTYPE html>
<html>
<head>
    <title>Drag & Drop System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.2/dist/semantic.min.css">
    <style>
        .draggable { cursor: move; margin: 5px; }
        .droppable { min-height: 50px; margin: 10px 0; }
        .drag-hover { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <div class="ui container" style="padding-top: 20px;">
        <h2 class="ui header">Drag & Drop System</h2>  
        <div class="ui grid">
            <div class="eight wide column">
                <div class="ui segment source-container">
                    <h3 class="ui header">Elemente</h3>
                    <?php
                    require_once 'config.php';
                    $items = $db->query("SELECT * FROM drag_items WHERE position = 0")->fetchAll(PDO::FETCH_ASSOC);
                    foreach($items as $item) {
                        echo "<div class='ui card draggable' data-id='{$item['id']}'>";
                        echo "<div class='content'>";
                        echo "<div class='header'>{$item['title']}</div>";
                        echo "<div class='description'>{$item['description']}</div>";
                        echo "</div></div>";
                    }
                    ?>
                </div>
            </div>
            
            <div class="eight wide column">
                <div class="ui segment droppable">
                    <h3 class="ui header">Zielbereich</h3>
                    <?php
                    $sorted_items = $db->query("SELECT * FROM drag_items WHERE position > 0 ORDER BY position")->fetchAll(PDO::FETCH_ASSOC);
                    foreach($sorted_items as $item) {
                        echo "<div class='ui card draggable' data-id='{$item['id']}'>";
                        echo "<div class='content'>";
                        echo "<div class='header'>{$item['title']}</div>";
                        echo "<div class='description'>{$item['description']}</div>";
                        echo "</div></div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.2/dist/semantic.min.js"></script>
    <script src="drag-drop.js"></script>
</body>
</html>

// config.php
<?php
try {
    $db = new PDO(
        'mysql:host=localhost;dbname=dragdrop_db;charset=utf8',
        'username',
        'password',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch(PDOException $e) {
    die('Verbindungsfehler: ' . $e->getMessage());
}

// update-position.php
<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = $_POST['itemId'] ?? 0;
    $newPosition = $_POST['position'] ?? 0;
    
    try {
        $stmt = $db->prepare("UPDATE drag_items SET position = ? WHERE id = ?");
        $stmt->execute([$newPosition, $itemId]);
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// drag-drop.js
$(document).ready(function() {
    $('.draggable').draggable({
        revert: 'invalid',
        zIndex: 100,
        cursor: 'move',
        start: function(event, ui) {
            $(this).addClass('ui raised card');
        },
        stop: function(event, ui) {
            $(this).removeClass('ui raised card');
        }
    });

    $('.droppable').droppable({
        accept: '.draggable',
        hoverClass: 'drag-hover',
        drop: function(event, ui) {
            const droppedItem = ui.draggable;
            const itemId = droppedItem.data('id');
            const position = $(this).children('.draggable').length + 1;

            $.ajax({
                url: 'update-position.php',
                method: 'POST',
                data: {
                    itemId: itemId,
                    position: position
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        droppedItem.appendTo($(this)).css({
                            top: 0,
                            left: 0
                        });
                    } else {
                        ui.draggable.draggable('option', 'revert', true);
                        console.error('Fehler beim Aktualisieren der Position');
                    }
                }.bind(this),
                error: function() {
                    ui.draggable.draggable('option', 'revert', true);
                    console.error('AJAX-Fehler');
                }
            });
        }
    });
});
