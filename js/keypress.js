$(document).ready(function(){
	$('#ajaxForm').keypress(function(e){
		if(e.keyCode==13)
		$('#btnSend').click();
	});
});