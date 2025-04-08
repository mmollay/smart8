<?
if ($text) {
    $output .= "<div class='marquee' id='marquee$layer_id'>$text</div>";

    //$output .= "\n<script type='text/javascript' src='gadgets/marquee/jquery.marquee.min.js'></script>";

    // Ruft über Ajax das Eingabefeld
    $output .= "
<script>
		$(document).ready(function() {
			$('#marquee$layer_id').marquee({
			    duration: 20000,
			    gap: 200,
				duplicated: true,
			    delayBeforeStart: 0,
			    direction: 'left',
			});
		});
</script>
";
}