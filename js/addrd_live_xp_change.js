$(document).ready(function() {
	// GET VALUE OF HIDDEN FIELD FIRST
	// IF 0 THEN NO EXECUTE, BECAUSE INITIAL
	// ADDING OF ROUNDS
	
	// VAR FOR SECURE_VAL
	var secure_val = $("#secure_val").val();
	
	// CHECK FOR SECURE_VAL VALUE
	if(secure_val > 0) {
		// AJAX FOR CHANGING XP NAME
		$("#rid_type").change(function() {
			if($("#rid_type").find("option:selected").val() == "changeto_GP") {
				// GET VALUE OF XP NAME TO CHANGE
				var changeXP = $("#rid_type").find("option:selected").val();
			} else if($("#rid_type").find("option:selected").val() == "changeto_SP") {
				// GET VALUE OF XP NAME TO CHANGE
				var changeXP = $("#rid_type").find("option:selected").val();
			} else if($("#rid_type").find("option:selected").val() == "changeto_WP") {
				// GET VALUE OF XP NAME TO CHANGE
				var changeXP = $("#rid_type").find("option:selected").val();
			}
			
			$.ajax({
				type: 'POST',
				url: 'addrd_live_xp_change.php',
				data: 'changeXP=' + changeXP,
				success: function(html){
					$('.error2').fadeIn(500).delay(5000).fadeOut(500).html(html);
					var rid_type = $("#rid_type").find("option:selected").val();
					rid_type.replace("_changeto", "");
					
					$("#rid_type").html();
					
					$.ajax({
					type: 'POST',
					data: 'rid_type=' + rid_type,
					url: 'fetch_current_xp_choice.php',
					success: function(data) {
						$("#rid_type").html(data);
						$("select#rid_type").selectmenu('refresh', true);
					}
				});
				}
			}); 
		});
	}
});