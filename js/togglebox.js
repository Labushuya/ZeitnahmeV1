$(document).ready(function() {
	var next = true;

	function displayOtherScreen(elem, direction) {
		if(next && !$(elem).hasClass("trigger2_active")) {
			next = false;
			$(elem).addClass("trigger2_active").slideToggle(500);
		} else if($(elem).hasClass("trigger2_active")) {
			next = true;
			$(elem).removeClass("trigger2_active").slideToggle(500);
		}
	}

			
	$('.trigger2').click(function() {
		// click on "(click to expand)" link or "Next" links
		if($(this).hasClass("trigger2-next")) {
			$('.toggle2_container').each(function(elem){ 
				displayOtherScreen(this, "next");
			});
		}
		// click on "Back" links
		else {
		 // read ".toggle_container" elements backwards
			$($(".toggle2_container").get().reverse()).each(function(){ 
				displayOtherScreen(this, "back");
			}); 
		}
	});
});