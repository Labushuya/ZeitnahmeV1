function disable() {
	if (document.getElementById("rid_type").value == "entfernen") {
		document.getElementById("rid_pn").disabled = "disabled";
		document.getElementById("rid_sz").disabled = "disabled";
    } else {
		document.getElementById("rid_pn").disabled = "";
		document.getElementById("rid_sz").disabled = "";
    }
    return true;
}