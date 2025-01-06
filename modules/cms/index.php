<!DOCTYPE html>
<html>

<head>
    <title>CMS Builder</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.0/dist/semantic.min.css">
    <style>
        #builder-area {
            min-height: 600px;
            border: 2px dashed #ccc;
            padding: 20px;
        }

        .element-palette {
            position: fixed;
            right: 0;
            top: 0;
            width: 250px;
            height: 100%;
            background: #f5f5f5;
            padding: 20px;
            border-left: 1px solid #ddd;
        }

        .grid-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 10px 0;
            min-height: 100px;
            border: 1px solid #eee;
            padding: 10px;
        }

        .draggable {
            cursor: move;
            margin: 5px;
            padding: 10px;
            background: #fff;
            border: 1px solid #ddd;
        }

        .dropzone {
            min-height: 50px;
            border: 1px dashed #aaa;
            margin: 5px 0;
            padding: 10px;
        }
    </style>
</head>

<body>
    <div class="ui container" style="margin-right: 270px !important;">
        <div class="ui segment" id="builder-area">
            <!-- Hier werden die Elemente platziert -->
        </div>
    </div>

    <div class="element-palette">
        <h3 class="ui header">Elemente</h3>
        <div class="ui segment draggable" data-type="grid">
            Grid Container
        </div>
        <div class="ui segment draggable" data-type="text">
            Text Element
        </div>
        <div class="ui segment draggable" data-type="image">
            Bild Element
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.0/dist/semantic.min.js"></script>
    <script>
        $(document).ready(function () {
            // Draggable-Elemente initialisieren
            $('.draggable').draggable({
                helper: 'clone',
                revert: 'invalid'
            });

            // Builder-Bereich als Dropzone initialisieren
            $('#builder-area').droppable({
                accept: '.draggable',
                drop: function (event, ui) {
                    handleDrop(event, ui, $(this));
                }
            });

            // Funktion zum Erstellen neuer Elemente
            function handleDrop(event, ui, target) {
                const type = ui.draggable.data('type');
                let newElement;

                switch (type) {
                    case 'grid':
                        newElement = $('<div class="grid-container ui grid">' +
                            '<div class="eight wide column dropzone">Bereich 1</div>' +
                            '<div class="eight wide column dropzone">Bereich 2</div>' +
                            '</div>');
                        break;
                    case 'text':
                        newElement = $('<div class="ui segment">' +
                            '<div class="ui form">' +
                            '<textarea rows="3">Textinhalt</textarea>' +
                            '</div></div>');
                        break;
                    case 'image':
                        newElement = $('<div class="ui segment">' +
                            '<div class="ui placeholder">' +
                            '<div class="image"></div>' +
                            '</div></div>');
                        break;
                }

                // Neue Dropzonen initialisieren
                newElement.find('.dropzone').droppable({
                    accept: '.draggable',
                    drop: function (event, ui) {
                        handleDrop(event, ui, $(this));
                    }
                });

                // Element zur Zielposition hinzufügen
                target.append(newElement);

                // Speichern der Struktur
                saveStructure();
            }

            // Funktion zum Speichern der Struktur
            function saveStructure() {
                const structure = [];

                // Rekursive Funktion zum Sammeln der Elementstruktur
                function collectElements(container) {
                    const elements = [];
                    container.children().each(function () {
                        const element = {
                            type: $(this).hasClass('grid-container') ? 'grid' :
                                $(this).find('textarea').length ? 'text' : 'image',
                            content: $(this).find('textarea').val() || '',
                            width: $(this).hasClass('column') ?
                                $(this).attr('class').match(/(\d+)\s+wide/)[1] : 'full',
                            children: []
                        };

                        if (element.type === 'grid') {
                            element.children = collectElements($(this));
                        }
                        elements.push(element);
                    });
                    return elements;
                }

                const pageStructure = collectElements($('#builder-area'));

                // AJAX-Aufruf zum Speichern
                $.ajax({
                    url: 'save_structure.php',
                    method: 'POST',
                    data: {
                        structure: JSON.stringify(pageStructure)
                    },
                    success: function (response) {
                        console.log('Struktur gespeichert', response);
                    }
                });
            }
        });
    </script>
</body>

</html>