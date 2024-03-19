$(document).ready(function() {
	var easing = 1 //enable or disable easing | 0 or 1
	var easing_effect = 'easeInOutCubic';
	var animation_speed = 500 //ms
      
	var slider_width = $('#content').width(); // get width automaticly
	$('#btn').click(function() {
		// check if slider is collapsed
		var is_collapsed = $(this).css("margin-right") == slider_width+"px" && !$(this).is(':animated');
      
		// minus margin or positive margin
		var sign = (is_collapsed) ? '-' : '+'; 
    
		if(!$(this).is(':animated')) { // prevent double margin on double click
			if(easing) $('.willSlide').animate({"margin-right": sign+'='+slider_width},animation_speed,easing_effect);
			else $('.willSlide').animate({"margin-right": sign+'='+slider_width},animation_speed);
		}
		// if you need you can add class when expanded
		(is_collapsed) ? $('.willSlide').removeClass('expanded') : $('.willSlide').addClass('expanded');
	});
});