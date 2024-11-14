<?
// In config.php oder einer ähnlichen Konfigurationsdatei
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    define('BASE_URL', '/smart8/smartform2');
} else {
    define('BASE_URL', '/smartform2');
}
?>


<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formular Optionen</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/29.0.0/decoupled-document/ckeditor.js"></script>
</head>

<body>
    <div class="ui container" style="padding-top: 50px;">
        <h1 class="ui header">Formular Optionen</h1>
        <div class="ui five column stackable grid">
            <div class="column">
                <button class="ui primary button fluid" id="openModalBtn">Formular im Modal</button>
            </div>
            <div class="column">
                <a href="formular.php" class="ui secondary button fluid">Normales Formular</a>
            </div>
            <div class="column">
                <a href="formular_two.php" class="ui teal button fluid">Zwei Formulare anzeigen</a>
            </div>
            <div class="column">
                <button class="ui green button fluid" id="showSimpleFormBtn">Ajax Formular</button>
            </div>
            <div class="column">
                <a href="ckeditor.php" class="ui button fluid">Ckeditor</a>
            </div>
        </div>
        <div class="ui modal" id="myModal">
            <i class="close icon"></i>
            <div class="header">Formular im Modal</div>
            <div class="content" id="modalContent">
                <!-- Der Formularinhalt wird hier dynamisch geladen -->
            </div>
        </div>
        <div id="simpleFormContainer" style="display: none; margin-top: 20px;">
            <h2 class="ui header">Einfaches Formular</h2>
            <div id="simpleFormContent">
                <!-- Der Inhalt des einfachen Formulars wird hier geladen -->
            </div>
        </div>
        <br>

        <button id="mysqlUserListBtn" class="ui button">MySQL Benutzerliste</button>
        <button id="arrayOrderListBtn" class="ui primary button">Array Bestellungsliste</button>
        <!-- //button für eigene Seite list_direct.php aufrufen -->
        <a href="list_direct.php" class="ui teal button">Liste direkt</a>


        <hr><br>
        <div id="content1"></div>
        <div id="content2"></div>
    </div>
    <hr>

    <script>
        $(document).ready(function () {
            $('#openModalBtn').click(function () {
                const $modal = $('#myModal');
                const $modalContent = $('#modalContent');

                // Lade den Formularinhalt
                $modalContent.load('formular_modal.php', function () {
                    // Modal-Konfiguration
                    $modal.modal({
                        autofocus: false, // Verhindert automatischen Fokus
                        allowMultiple: false, // Verhindert mehrfache Modal-Instanzen
                        closable: true, // Erlaubt Schließen durch Klick außerhalb/ESC
                        observeChanges: true,
                        onHide: function () {
                            // Bereinigung beim Schließen
                            // cleanupModal($modal);
                            return true;
                        },

                    }).modal('show');
                });
            });
        });

        // Funktion zur Bereinigung des Modals
        function cleanupModal($modal) {
            const $modalContent = $modal.find('.content');

            // Entferne alle Event-Listener
            $modalContent.find('*').off();

            // Entferne alle CKEditor-Instanzen falls vorhanden
            if (window.editorInstances) {
                Object.keys(window.editorInstances).forEach(formId => {
                    Object.keys(window.editorInstances[formId]).forEach(editorId => {
                        if (window.editorInstances[formId][editorId]) {
                            window.editorInstances[formId][editorId].destroy();
                            delete window.editorInstances[formId][editorId];
                        }
                    });
                });
            }

            // Entferne Semantic UI Komponenten
            $modalContent.find('.dropdown').dropdown('destroy');
            $modalContent.find('.checkbox').checkbox('destroy');
            $modalContent.find('.calendar').calendar('destroy');

            // Leere den Modal-Inhalt
            $modalContent.empty();

            // Entferne dynamisch hinzugefügte Skripte und Styles
            $('script[data-modal-script]').remove();
            $('style[data-modal-style]').remove();

            // Garbage Collection forcieren
            if (window.gc) {
                window.gc();
            }
        }

    </script>

    <script src="<?php echo BASE_URL; ?>/js/listGenerator.js"></script>
    <script src="<?php echo BASE_URL; ?>/js/formGenerator.js"></script>
    <script src="<?php echo BASE_URL; ?>/js/fileUploader.js"></script>
    <script src="<?php echo BASE_URL; ?>/js/ckeditor-init.js"></script>



    <script>
        $(document).ready(function () {
            // Example: Load MySQL Benutzerliste
            $('#mysqlUserListBtn').on('click', function () {
                loadListGenerator('list_mysql.php', {
                    saveState: false,
                    sort: 'last_name',
                    sortDir: 'ASC',
                    filters: { role: 'user' },
                    contentId: 'content1'
                });
            });

            // Example: Load Array Bestellungsliste
            $('#arrayOrderListBtn').on('click', function () {
                loadListGenerator('list_array.php', {
                    saveState: true,
                    sort: 'order_date',
                    sortDir: 'DESC',
                    search: 'Hoffmann',
                    contentId: 'content2'
                });
            });

            // Initial load of the MySQL Benutzerliste
            loadListGenerator('list_mysql.php', { saveState: false, contentId: 'content1' });
            //loadListGenerator('list_array.php', { contentId: 'content2' });
        });
    </script>
</body>

</html>