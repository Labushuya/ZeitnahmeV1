// AJAX EDIT rid
function saveToDatabase(editableObj,column,id) {
	$(editableObj).css("background","url(images/ripple12px-fts2.gif) no-repeat right");
	$.ajax({
		url: "update_rd.php",
		type: "POST",
		data:'column='+column+'&editval='+editableObj.innerHTML+'&id='+id,
		success: function(data){
			$(editableObj).css("background", "#A09A8E url(images/tick.png) no-repeat right");
		},
		error: function(data) {
			$(editableObj).css("background", "#A09A8E url(images/cross.png) no-repeat right");
		}
	});
}
			
// AJAX EDIT CHECKBOX ROUND
function saveToDatabaseCBRD(checkboxObj, column, id, eid, rid) {
	$.ajax({
		url: "update_rd.php",
		type: "POST",
		data: 'column=' + column + '&editval=' + checkboxObj + '&id=' + id + '&eid=' + eid + '&rid=' + rid,
		success: function(data) {
			$("#checkboxOneInput_" + id).attr('disabled', true);			
			$("#is_neutralized_" + id).animate({
				backgroundColor: "#ff0000"
			}, 1000);		
			$("#is_neutralized_" + id).css('color', '#ffffff !important');
		},
		error: function(data) {
		}
	});
}

// AJAX EDIT CHECKBOX ROUND
function saveToDatabaseGeheim(checkboxObj, column, id, eid, rid) {
	$.ajax({
		url: "update_rd_secret.php",
		type: "POST",
		data: 'column=' + column + '&editval=' + checkboxObj + '&id=' + id + '&eid=' + eid + '&rid=' + rid,
		success: function(data) {
			//	Weise Übergabeparameter neuen Wert zu
			if($('#geheimXP_' + id).val() == 0) {
				$('#geheimXP_' + id).val(1);
				$('#secret_state_' + id).html('verbergen');
			} else if($('#geheimXP_' + id).val() == 1) {
				$('#geheimXP_' + id).val(0);
				$('#secret_state_' + id).html('freigeben');
			}
		},
		error: function(data) {
		}
	});
}
			
// AJAX EDIT OPTIONAL TIMEBUDDY NAME
function saveToDatabaseMZ(editableObj,column,id) {
	$(editableObj).css("background","url(images/ripple12px-fts2.gif) no-repeat right");
	$.ajax({
		url: "update_mz.php",
		type: "POST",
		data:'column='+column+'&editval='+editableObj.innerHTML+'&id='+id,
		success: function(data){
			$(editableObj).css("background", "#A09A8E url(images/tick.png) no-repeat right");
		},
		error: function(data) {
			$(editableObj).css("background", "#A09A8E url(images/cross.png) no-repeat right");
		}
	});
}

// AJAX EDIT CHECKBOX RACER
function saveToDatabaseCBMT(checkboxObj, eid, sid) {
	$.ajax({
		url: "update_mt.php",
		type: "POST",
		data:	{
					editval: checkboxObj,
					eid: eid,					
					sid: sid
				},
		success: function(data) {
			$('#status_' + sid).attr('src','images/' + data + '.png');
			if(data == "cross") {
				$('#check_state_' + sid).val('yes');
			} else if(data == "tick") {
				$('#check_state_' + sid).val('no');
			}
		},
		error: function(data) {
		}
	});
}