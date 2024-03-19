$(document).ready( function() {
	$('.trigger').not('.trigger_active').next('.toggle_container').hide();
	$('.trigger').click( function() {
		var trig = $(this);
		
		if(trig.hasClass('trigger_active')) {
			trig.next('.toggle_container').slideToggle(500);
			trig.removeClass('trigger_active');
			} else {
				$('.trigger_active').next('.toggle_container').slideToggle(500);
				$('.trigger_active').removeClass('trigger_active');
					trig.next('.toggle_container').slideToggle(500);
					trig.addClass('trigger_active');
			};
			return false;
	});
});