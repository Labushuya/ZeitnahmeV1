// SET LASTTIMEID
var lastTimeID = 0;

// FUNCTION SCROLLDOWN
function scrolldown() {
	$("#view_ajax").animate({
		//scrollTop: $('#view_ajax').get(0).scrollHeight
		scrollTop: $('#view_ajax').height() + 5 * ($('#view_ajax').height())
	}, (10000));
	$('#view_ajax').bind('scroll mousedown wheel DOMMouseScroll mousewheel keyup', function(e){
		if ( e.which > 0 || e.type == "mousedown" || e.type == "mousewheel"){
			$("#view_ajax").stop();
		}
	});
}

// TRIGGER INTERVAL SCROLL DOWN
$(document).ready(function() {
	scrolldown();
});

// SEND MESSAGE AND TRIGGER INTERVAL
$(document).ready(function() {
	$('#btnSend').click(function(){
		sendChatText();
		$('#chatInput').val("");
	});
	startChat();
});

// FUNCTION INTERVAL TRIGGER FETCH MESSAGE
function startChat(){
	setInterval(function(){
		getChatText();		
	}, 5000);
}

// FUNCTION FETCH MESSAGE
function getChatText(){
	$.ajax({
		type: "GET",
		url: "/msdn/messenger/refresh.php?lastTimeID="+lastTimeID
	}).done(function( data )
	{
		var jsonData = JSON.parse(data);
		var jsonLength = jsonData.results.length;
		var html = "";
		for (var i = 0; i < jsonLength; i++) {
			var result = jsonData.results[i];
			html += '<div style="font-size: x-small; color:#'+result.color+'">(' + result.chattime+ ') <b>' + result.username +'</b>: '+result.chattext+ '</div>';
			lastTimeID = result.id;
		}
		$('#view_ajax').append(html);
	});
}

// FUNCTION SEND MESSAGE
function sendChatText(){
	var chatInput = $('#chatInput').val();
	if(chatInput != ""){
		$.ajax({
			type: "GET",
			url: "/msdn/messenger/submit.php?chattext=" + encodeURIComponent( chatInput )
		});
		scrolldown();
	}
}