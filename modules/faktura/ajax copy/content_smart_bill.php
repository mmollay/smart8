<?php
$smart_head = 'KOPF<hr>';
$smart_title = 'Rechnung';
$smart_company_container = 'SSI-Martin Mollay, Hollenthon 33, 2812 Hollenthon';
$smart_user_container = 'Test User<br>Userstrasse 33<br>2700 Wiener Neustadt';
$smart_bill_right_container = 'Rechnungsnummer: 12323123123<br>Kundennummer: 12323123123';
$smart_footer_container = $smart_company_container;
?>
<style type="text/css" title="currentStyle">
@IMPORT "css/smart_bill.css";
</style>




<div class=smart_border>

	<div class=smart_head>
		<?=$smart_head?>
	</div>

	<div class=smart_company_container>
		<?=$smart_company_container?>
	</div>

	<div class=smart_bill_left_container>
		<div class=smart_user_container>
			<button class='button_smart_bill' id='new_user'>User bearbeiten</button>
			<br>
			<?=$smart_user_container?>
		</div>
	</div>
	<div class=smart_bill_right_container>
		<?=$smart_bill_right_container?>
	</div>
	<div style='clear: both'></div>
	<div class=smart_title>
		<?=$smart_title?>
	</div>


	<table class='smart_table' cellpadding=0 cellspacing=1>
		<tr>
			<th>Pos</th>
			<th>Artikel</th>
			<th>Menge</th>
			<th>Summe</th>
		</tr>

		<tr>
			<td colspan=4><button class='button_smart_bill' id='new_position'>Neue
					Position anlegen</button></td>
		</tr>




		<tr>
			<td colspan=3>Summe</td>
			<td align=right>0,00</td>
		</tr>


	</table>

	<div class=smart_footer_container>
		<?=$smart_footer_container?>
	</div>
</div>


