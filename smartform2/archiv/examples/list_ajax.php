<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benutzerliste mit AJAX</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.2/dist/semantic.min.css">
</head>

<body>
    <div class="ui container" style="padding-top: 20px;">
        <h1 class="ui header">Benutzerliste mit AJAX</h1>

        <div id="listContainer"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.2/dist/semantic.min.js"></script>
    <script>
        var listOptions = {
            sortColumn: 'id',
            sortDirection: 'ASC',
            search: '',
            page: 1
        };

        function loadList() {
            $.ajax({
                url: 'get_list_data.php',
                method: 'GET',
                data: listOptions,
                success: function (response) {
                    $('#listContainer').html(response);
                    initializeListEvents();
                },
                error: function (xhr, status, error) {
                    console.error('Fehler beim Laden der Liste:', error);
                }
            });
        }

        function initializeListEvents() {
            $('.pagination .item').on('click', function (e) {
                e.preventDefault();
                listOptions.page = $(this).data('page');
                loadList();
            });

            $('th[data-sort]').on('click', function () {
                listOptions.sortColumn = $(this).data('sort');
                listOptions.sortDirection = listOptions.sortDirection === 'ASC' ? 'DESC' : 'ASC';
                listOptions.page = 1;
                loadList();
            });

            $('.ui.search input').off('input').on('input', function () {
                listOptions.search = $(this).val();
                listOptions.page = 1;
                loadList();
            });
        }

        $(document).ready(function () {
            loadList();
        });

        function editUser(id) {
            alert('Bearbeite Benutzer mit ID: ' + id);
        }
    </script>
</body>

</html>