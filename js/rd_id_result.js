$(document).ready(function() {
	$('#rd_fetch').on('change', function() {
		var rid = $('#rd_fetch').val();		
		
		if(rid > 0) {
			$('#fetch_rd').fadeIn(500).html('<table width="100%" cellspacing="5px" style="border: 0;"><tr><th align="center">Lade ... <img src="images/ripple.gif"></img></th></tr></table>');
			
			$.ajax({
				type: 'POST',
				url: 'fetch_pruefung_res.php',
				data: 'rid=' + rid,
				success: function(html) {
					$('#fetch_rd').html(html);
					
					if(html.indexOf("Keine Rennergebnisse verfügbar") > -1) {
						$("#export_type").css('visibility', 'hidden');
					} else {
						$("#export_type").css('visibility', 'visible');
						$("#titles").html('Rohzeiten exportieren als:');
					}
					
					$("#reload").css('visibility', 'visible');
				}
			}); 
		} else if(rid == 0) {
			$('#fetch_rd').fadeIn(500).html('<table width="100%" cellspacing="5px" style="border: 0;"><tr><th align="center">Bitte Prüfung wählen</th></tr></table>');
		}
	});
	
	// MAKE RESULT FETCH ON CLICK
	$('#reload').click(function() {
		var rid = $('#rd_fetch').val();		
		
		if(rid > 0) {
			$('#fetch_rd').fadeIn(500).html('<table width="100%" cellspacing="5px" style="border: 0;"><tr><th align="center">Lade ... <img src="images/ripple.gif"></img></th></tr></table>');
			
			$.ajax({
				type: 'POST',
				url: 'fetch_pruefung_res.php',
				data: 'rid=' + rid,
				success: function(html) {
					$('#fetch_rd').html(html);
					
					if(html.indexOf("Keine Rennergebnisse verfügbar") > -1) {
						$("#export_type").css('visibility', 'hidden');
					} else {
						$("#export_type").css('visibility', 'visible');
						$("#titles").html('Ergebnis(se) exportieren als:');
					}
					
					$("#reload").css('visibility', 'visible');
				}
			}); 
		} else if(rid == 0) {
			$('#fetch_rd').fadeIn(500).html('<table width="100%" cellspacing="5px" style="border: 0;"><tr><th align="center">Bitte Prüfung wählen</th></tr></table>');
			$("#export_type").css('visibility', 'hidden');
			$("#reload").css('visibility', 'hidden');
		}
	});
});

// AUTO-FETCH EVERY 30 SECONDS
function fetchResult() {
	var rid = $("#rd_fetch").val();
	
	if(rid > 0) {
		$('#fetch_rd').fadeIn(500).html('<table width="100%" cellspacing="5px" style="border: 0;"><tr><th align="center">Aktualisiere ... <img src="images/ripple.gif"></img></th></tr></table>');
				
		$.ajax({
			type: 'POST',
			url: 'fetch_pruefung_res.php',
			data: 'rid=' + rid,
			success: function(html){
				$('#fetch_rd').html(html);
					
				if(html.indexOf("Keine Rennergebnisse verfügbar") > -1) {
					$("#export_type").css('visibility', 'hidden');
				} else {
					$("#export_type").css('visibility', 'visible');
					$("#titles").html('Ergebnis(se) exportieren als:');
				}
				
				$("#reload").css('visibility', 'visible');
			}
		});
	} else if(rid == 0) {
		$('#fetch_rd').fadeIn(500).html('<table width="100%" cellspacing="5px" style="border: 0;"><tr><th align="center">Bitte Prüfung wählen</th></tr></table>');
	}
}
	
// INTERVAL FOR FETCHING EVERY 30 SECONDS
var interval =	setInterval(function() {
					fetchResult();
				}, 60000);
