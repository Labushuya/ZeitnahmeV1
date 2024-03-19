$(document).ready(function () {
	$('#nav > li > a > li > a').click(function(){
		if ($(this).attr('class') != 'active') {
			$('#nav li ul').slideUp();
			$(this).next().slideToggle();
			$('#nav li a').removeClass('active');
			$(this).addClass('active');
		}
	});
});