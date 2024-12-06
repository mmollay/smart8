<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formular Optionen</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.js"></script>
</head>

<body>
    <div id="content1"></div>
    <script src="<?php echo BASE_URL; ?>/js/listGenerator.js"></script>
    <script src="<?php echo BASE_URL; ?>/js/formGenerator.js"></script>
    <script src="<?php echo BASE_URL; ?>/js/fileUploader.js"></script>
    <script src="<?php echo BASE_URL; ?>/js/ckeditor-init.js"></script>
    <script>
        $(document).ready(function () {
            // Initial load of the MySQL Benutzerliste
            loadListGenerator('list_mysql.php', { saveState: false, contentId: 'content1' });
            //loadListGenerator('list_array.php', { contentId: 'content2' });
        });
    </script>
</body>

</html>