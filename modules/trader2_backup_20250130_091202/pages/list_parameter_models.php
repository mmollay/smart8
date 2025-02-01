<?php
require_once(__DIR__ . '/../t_config.php');
?>

<div id='content_parameter_models'></div>

<script>
    $(document).ready(function () {
        loadListGenerator('lists/trading_parameter_models.php', {
            saveState: false,
            contentId: 'content_parameter_models'
        });
    });
</script>