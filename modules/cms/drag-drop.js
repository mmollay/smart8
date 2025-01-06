$(document).ready(function () {
    $('.draggable').draggable({
        revert: 'invalid',
        zIndex: 100,
        cursor: 'move',
        start: function (event, ui) {
            $(this).addClass('ui raised card');
        },
        stop: function (event, ui) {
            $(this).removeClass('ui raised card');
        }
    });

    $('.droppable').droppable({
        accept: '.draggable',
        hoverClass: 'drag-hover',
        drop: function (event, ui) {
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
                success: function (response) {
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
                error: function () {
                    ui.draggable.draggable('option', 'revert', true);
                    console.error('AJAX-Fehler');
                }
            });
        }
    });
});