$(document).ready(function() {
	// FADE ERROR MESSAGE
	$('.error').delay(5000).fadeOut(500);
	
	// INITIAL FETCH
	$('#special_hint').fadeIn(500).delay(1000).fadeOut(500).html('<table width="385px" cellspacing="5px" style="border: 1;"><tr><th align="center">Prüfe auf ausstehende Daten für Upload</th></tr><tr><td align="center"><font color="red">Browser nicht schließen!</font></td></tr></table>');
	$('#results').prop('disabled', true);
	$('#results').val('Bitte warten ...');
		
	setTimeout(function () {
		// FADE ERROR MESSAGE
		$('.error').delay(5000).fadeOut(500);
		
		$.ajax({
			type: 'POST',
			url: 'persistent_upload.php',
			success: function(data) {
				$('#special_hint').html('');
				$('#special_hint').fadeIn(500).delay(1000).fadeOut(500).append(data);
				$('#results').prop('disabled', false);
				$('#results').val('Ergebnis eintragen');
			}
		});
	}, 2500);
});	

// AUTO-FETCH EVERY 30 SECONDS
function persistentUpload() {
	$('#special_hint').fadeIn(500).delay(1000).fadeOut(500).html('<table width="385px" cellspacing="5px" style="border: 1;"><tr><th align="center">Prüfe auf ausstehende Daten für Upload</th></tr><tr><td align="center"><font color="red">Browser nicht schließen!</font></td></tr></table>');
	$('#results').prop('disabled', true);
	$('#results').val('Bitte warten ...');
		
	setTimeout(function () {
		// FADE ERROR MESSAGE
		$('.error').delay(5000).fadeOut(500);
		
		$.ajax({
			type: 'POST',
			url: 'persistent_upload.php',
			success: function(data) {
				$('#special_hint').html('');
				$('#special_hint').fadeIn(500).delay(1000).fadeOut(500).append(data);
				$('#results').prop('disabled', false);
				$('#results').val('Ergebnis eintragen');
			}
		});
	}, 2500);
}
	
// INTERVAL FOR FETCHING EVERY 30 SECONDS
setInterval(function() {
	persistentUpload();
}, 30000);
