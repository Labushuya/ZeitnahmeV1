//	Zeitnehmer Auto-Logout
//	Vergleiche Log auf letzte Aktivität mit aktuellem Zeitstempel
$(document).ready(function() {
	$.ajax({
		url:	'cronjob/mz_autologout.php'
	});
});

function mz_autologout() {
	$.ajax({
		url:	'cronjob/mz_autologout.php'
	});
}

//	Wiederhole Auto-Logout alle 30 Sekunden
setInterval(function() {
	mz_autologout();
}, 30000);