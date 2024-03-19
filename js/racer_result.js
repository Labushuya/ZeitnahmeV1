// AJAX VARIABLES DECLARED IN RACER.PHP
$(document).ready(function() {
	$('#racer_status').fadeIn(500).html('<table width="100%" cellspacing="5px" style="border: 0;"><tr><th align="center">Lade ... <img src="images/ripple.gif"></img></th></tr></table>');
	
	// AJAX REQUEST
	$.ajax({
		type: 'POST',
		url: 'racer_result.php',
		data: 	{
					eid: eid,
					sid: sid
				},
		success: function(data) {
			$('#racer_status').html(data);
		}
	});
});

function fetch_racer_status() {
	$('#racer_status').fadeIn(500).html('<table width="100%" cellspacing="5px" style="border: 0;"><tr><th align="center">Aktualisiere ... <img src="images/ripple.gif"></img></th></tr></table>');	
	
	// AJAX REQUEST
	$.ajax({
		type: 'POST',
		url: 'racer_result.php',
		data: 	{
					eid: eid,
					sid: sid
				},
		success: function(data) {
			$('#racer_status').html(data);
		}
	});
}

// INTERVAL FOR FETCHING EVERY 10 SECONDS
setInterval(function() {
	fetch_racer_status();
}, 60000);