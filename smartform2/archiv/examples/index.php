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
        <div class="ui four column stackable grid">
            <div class="column">
                <button class="ui primary button fluid" id="openModalBtn">Formular im Modal öffnen</button>
            </div>
            <div class="column">
                <a href="formular.php" class="ui secondary button fluid">Normales Formular öffnen</a>
            </div>
            <div class="column">
                <a href="formular_two.php" class="ui teal button fluid">Zwei Formulare anzeigen</a>
            </div>
            <div class="column">
                <button class="ui green button fluid" id="showSimpleFormBtn">Einfaches Formular anzeigen</button>
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
    </div>
    <!--Auswahl von Listen Übersicht zum anklicken list_mysql.php und list_array.php-->
    <div class="ui container" style="padding-top: 50px;">
        <h1 class="ui header">Listengenerator Demo</h1>
        <a href="list_komplex.php" class="ui primary button">Liste Komplex</a>
        <a href="list_minimal.php" class="ui secondary button">Liste Simple</a>
        <a href="list_double.php" class="ui teal button">Liste Doppelt</a>
        <a href="list_ajax.php" class="ui green button">Liste Ajax</a>
    </div>
    <hr>
    <div id="content1"></div><br><br>
    <div id="content2"></div>
    <script src="../js/listGenerator.js"></script>

    <script>
        $(document).ready(function () {
            // Example: Load MySQL Benutzerliste
            $('#mysqlUserListBtn').on('click', function () {
                loadListGenerator('list_mysql.php', {
                    saveState: true,
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
            loadListGenerator('list_mysql.php', { saveState: true, contentId: 'content1' });
            //loadListGenerator('list_array.php', { contentId: 'content2' });
        });
    </script>
    <script>
        $(document).ready(function () {
            $('#openModalBtn').click(function () {
                $('#modalContent').load('formular_modal.php', function () {
                    $('#myModal').modal('show');
                });
            });

            $('#showSimpleFormBtn').click(function () {
                $('#simpleFormContainer').show();
                $('#simpleFormContent').load('formular_simple.php');
            });
        });
    </script>
</body>

</html>