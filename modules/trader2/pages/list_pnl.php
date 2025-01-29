<?php require_once(__DIR__ . '/../t_config.php'); ?>

<div id="content_pnl"></div>

<script>
    $(document).ready(function () {
        loadListGenerator('lists/pnl.php', {
            saveState: false,
            contentId: 'content_pnl',
            sort: 'bitget_timestamp',
            sortDir: 'DESC',
            pageSize: 25
        });
    });
</script>