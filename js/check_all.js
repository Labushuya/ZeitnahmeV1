var marker = true;
function selectCheckboxes(form_mt) {
    if (marker == false) {
        document.getElementById('master_checker_mt').value = 'Alle demarkieren';
        for (var i = 0;i < document.forms[form_mt].elements.length;i++) {
            if(document.forms[form_mt].elements[i].type == 'checkbox') {
                document.forms[form_mt].elements[i].checked = true;
                marker = true;
            }
        }
    } else {
        document.getElementById('master_checker_mt').value = 'Alle markieren';
        for (var i = 0;i < document.forms[form_mt].elements.length;i++) {
            if(document.forms[form_mt].elements[i].type == 'checkbox') {
                document.forms[form_mt].elements[i].checked = false;
                marker = false;
            }
        }
    }
}