$(document).ready(function() {
	var rid_type = $("#rid_type").find("option:selected").val();
	
	setTimeout(function() {
		$.ajax({
			type: 'POST',
			data: 'rid_type=' + rid_type,
			url: 'fetch_current_xp_choice.php',
			success: function(data) {
				$("#rid_type").html(data);
				$("#rid_type").selectmenu('refresh');
			}
		});
	}, 2500);
)};

// AUTO-FETCH EVERY 2.5 SECONDS
function persistentSelectionFetch() {
	var rid_type = $("#rid_type").find("option:selected").val();
	
	setTimeout(function() {
		$.ajax({
			type: 'POST',
			data: 'rid_type=' + rid_type,
			url: 'fetch_current_xp_choice.php',
			success: function(data) {
				$("#rid_type").html(data);
				$("#rid_type").selectmenu('refresh');
			}
		});
	}, 2500);
}
	
// INTERVAL FOR FETCHING EVERY 30 SECONDS
setInterval(function() {
	persistentSelectionFetch();
}, 2500);