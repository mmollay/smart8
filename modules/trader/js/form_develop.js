$(document).ready(function () {

});

function after_post_request(json) {
	json = JSON.parse(json);
	//Alles json in console.log anzeigen
	console.log(json);

	//json.message show in toast
	if (json.message) {

		$('#message').toast({
			class: 'info',
			title: json.message,
			position: 'top center'
		});
	}
	else if (json.error) {
		$('#message').toast({
			class: 'red',
			title: json.error,
			position: 'top center'
		});
	}
	else if (!json) {
		$('#message').toast({
			class: 'red',
			title: 'Keine Daten erhalten',
			position: 'top center'
		});
	}

	if (json.buy_sell) {
		$('#dropdown_buy_sell_stop').dropdown('set selected', json.buy_sell);
		$('#price_trade').val(json.price);

		if (json.buy_sell === 'buy') {
			var neuerPreis = parseFloat(json.price) + 5; // Füge 5 zum Preis hinzu
			//wenn kein json.price ist dann toast "Kein Preis erhalten"
			if (!json.price) {
				$('#message').toast({
					class: 'red',
					title: 'Kein Preis erhalten',
					position: 'top center'
				});
				return;
			}

			$('#price').val(neuerPreis); // Setze den neuen Preis
			$('#dropdown_buy_sell_stop').dropdown('set selected', 'buyStop');
		} else if (json.buy_sell === 'sell') {
			var neuerPreis = parseFloat(json.price) - 5; // Subtrahiere 5 vom Preis
			$('#price').val(neuerPreis); // Setze den neuen Preis
			$('#dropdown_buy_sell_stop').dropdown('set selected', 'sellStop');
		}
	}

}

function submit_hedging(id) {
	if (id = 'ok') {
		$('#message').toast({ class: 'info', title: 'Demo: Send Strategy', position: 'top center' });
	}
	else {
		$('#message').toast({
			class: 'red',
			title: id,
			position: 'top center'
		});
	}
}
