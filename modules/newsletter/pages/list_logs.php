<div id='content_logs'></div>
<?php
$content_id = isset($_GET['content_id']) ? intval($_GET['content_id']) :
    (isset($_POST['content_id']) ? intval($_POST['content_id']) : 0);
?>

<script>
    $(document).ready(function () {
        loadListGenerator('lists/logs.php?content_id=' + <?= $content_id ?>, {
            saveState: false,
            contentId: 'content_logs',
            reload: true
        });
    });
</script>