$(document).ready(function() {
	// INITIAL FETCH
	$.ajax({
		type: 'POST',
		url: 'timebuddy_state.php',
		success: function(html) {
			$('#t_state').html(html);
		}
	});
	
	// AUTO-FETCH EVERY 10 SECONDS
	function auto_fetch() {
		$.ajax({
			type: 'POST',
			url: 'timebuddy_state.php',
			success: function(html) {
				$('#t_state').html(html);
			}
		});
	}
	
	// INTERVAL FOR FETCHING EVERY 10 SECONDS
	setInterval(function() {
		auto_fetch();
	}, 10000);
});