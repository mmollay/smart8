<div id='content_newsletters'></div>
<!-- Wird im Dashboard geladen -->
<!-- <script src="../../smartform2/js/listGenerator.js"></script> -->

<script>
    $(document).ready(function () {
        loadListGenerator('lists/newsletters.php', {
            saveState: false,
            contentId: 'content_newsletters',
            sort: 'content_id',
            sortDir: 'DESC',
            //autoReloadInterval: 20000
        });
    });
</script>
<script src="js/newsletter.js"></script>