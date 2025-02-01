<div id='content_account_transactions'></div>

<script>
    $(document).ready(function () {
        loadListGenerator('lists/account_transactions.php', {
            saveState: false,
            contentId: 'content_account_transactions',
            sort: 'bitget_timestamp',
            sortDir: 'DESC',
            reload: false
        });
    });
</script>
