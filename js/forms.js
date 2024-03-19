function formhash(form, password) {
    // Erstelle ein neues Feld für das gehashte Passwort. 
    var p = document.createElement("input");
 
    // Füge es dem Formular hinzu. 
    form.appendChild(p);
    p.name = "p";
    p.type = "hidden";
    p.value = hex_sha512(password.value);
 
    // Sorge dafür, dass kein Text-Passwort geschickt wird. 
    password.value = "";
 
    // Reiche das Formular ein. 
    form.submit();
}

function regformhash(form, uid, email, password, conf, anrede, vname, nname, str, nr, plz, ort, agb, abo) {
    // Überprüfe, ob jedes Feld einen Wert hat
    if(	uid.value 		== ''					|| 
		email.value 	== ''					|| 
        password.value 	== ''					|| 
        conf.value 		== ''					||
		anrede.value 	== ''					||
		anrede.value 	== 'Bitte auswählen'	||
		vname.value 	== ''					||
		nname.value 	== ''					||
		str.value 		== ''					||
		nr.value 		== ''					||
		plz.value 		== ''					||
		ort.value 		== ''					||
		agb.value 		== ''					||
		abo.value 		== ''					) {
 
        alert('Bitte füllen Sie alle Felder aus');
        return false;
    }
	
    // Überprüfe den Benutzernamen
    re_user = /^\w+$/; 
    if(!re_user.test(form.username.value)) { 
        alert("Benutzername darf ausschließlich aus Buchstaben, Zahlen, sowie Unterstrichen bestehen"); 
        form.username.focus();
        return false; 
    }
 
    // Überprüfe, dass Passwort lang genug ist (min 6 Zeichen)
    // Die Überprüfung wird unten noch einmal wiederholt, aber so kann man dem 
    // Benutzer mehr Anleitung geben
    if(password.value.length < 6) {
        alert('Passwort muss mindestens 6 Zeichen lang sein');
        form.password.focus();
        return false;
    }
 
    // Mindestens eine Ziffer, ein Kleinbuchstabe und ein Großbuchstabe
    // Mindestens sechs Zeichen 
    var re_pass = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}/; 
    if(!re_pass.test(password.value)) {
        alert('Passwort muss mindestens eine Zahl, einen Klein- und einen Großbuchstaben enthalten');
        return false;
    }
 
    // Überprüfe die Passwörter und bestätige, dass sie gleich sind
    if(password.value != conf.value) {
        alert('Passwörter stimmen nicht überein');
        form.password.focus();
        return false;
    }
	
	// Überprüfe die Email-Adresse
    re_mail = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/igm;
    if(!re_mail.test(form.email.value)) { 
        alert("Bitte geben Sie eine gültige Email-Adresse an"); 
        form.email.focus();
        return false; 
    }
 
    // Erstelle ein neues Feld für das gehashte Passwort.
    var p = document.createElement("input");
 
    // Füge es dem Formular hinzu. 
    form.appendChild(p);
    p.name = "p";
    p.type = "hidden";
    p.value = hex_sha512(password.value);
 
    // Sorge dafür, dass kein Text-Passwort geschickt wird. 
    password.value = "";
    conf.value = "";
 
    // Reiche das Formular ein. 
    form.submit();
    return true;
}