<div id='content_trades'></div>

<script>
	$(document).ready(function () {
		loadListGenerator('lists/trades.php', {
			saveState: false,
			contentId: 'content_trades',
			sort: 'id',
			sortDir: 'DESC',
			reload: false

		});
	});
</script>