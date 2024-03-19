// TEILNEHMER
$(document).ready(function() {
	var max_fields      = 5; //maximum input boxes allowed
	var wrapper         = $(".input_fields_wrap"); //Fields wrapper
	var add_button      = $(".add_field_button"); //Add button ID
		   
	var x = 1; //initlal text box count
	$(add_button).click(function(e){ //on add input button click
		e.preventDefault();
		if(x < max_fields){ //max input box allowed
			x++; //text box increment
			$(wrapper).append('<div><table id="addmt" width="385px" "cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;"><tr><th colspan="2">&nbsp;</th></tr><tr><th colspan="2"><hr class="white-hr" /></th></tr><tr><th colspan="2">&nbsp;</th></tr></table><a href="#" class="remove_field"><table id="addmt" width="385px" "cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;"><tr><th align="left">Team entfernen</th><th align="right"><font color="#FFD700">[&ndash;]</font></th></tr></table></a><tr><th colspan="2"><hr /></th></tr></table><table id="addmt" width="385px" "cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;"><tr><td align="left">Zuordnung<font color="#8E6516">*</font></td><td align="right"><input name="mt_id[]" type="text" style="width: 66.5px; margin-right: 2px;" placeholder="#" required = "required" /><input name="mt_id[]" type="text" style="width: 66.5px;" placeholder="Klasse" required = "required" /></td></tr><tr><td colspan="2">&nbsp;</td></tr><tr><td align="left">Fahrzeug<font color="#8E6516">*</font></td><td align="right"><input name="mt_id[]" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="Fabrikat" required = "required" /></td></tr><tr><td align="left">&nbsp;</td><td align="right"><input name="mt_id[]" type="text" style="width: 66.5px; margin-right: 2px;" placeholder="Typ" required = "required" /><input name="mt_id[]" type="text" style="width: 66.5px;" placeholder="Baujahr" required = "required" /></td></tr><tr><td colspan="2">&nbsp;</td></tr><tr><td align="left">Fahrer/-in<font color="#8E6516">*</font></td></tr><tr><td align="left">&nbsp;</td><td align="right"><input name="mt_id[]" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="Nachname" required = "required" /></td></tr><tr><td colspan="2">&nbsp;</td></tr><tr><td align="left">Beifahrer/-in<font color="#8E6516">*</font></td><td align="right"><input name="mt_id[]" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="Vorname" required = "required" /></td></tr><tr><td align="left"&nbsp;</td>	<td align="right"><input name="mt_id[]" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="Nachname" required = "required" /></td></tr></table></div>'); //add input box
		}
	});
			   
	$(wrapper).on("click",".remove_field", function(e){ //user click on remove text
		e.preventDefault(); $(this).parent('div').remove(); x--;
	})
});

/* COPIED TO _ADDMZ.PHP DUE TO PHP / JS PARSE INSUFFICIENCE
// ZEITNEHMER
$(document).ready(function() {
    var mz_max_fields      = 15; //maximum input boxes allowed
    var mz_wrapper         = $(".mz_input_fields_wrap"); //Fields wrapper
    var mz_add_button      = $(".mz_add_field_button"); //Add button ID
                                    		   
    var mz_x = 1; //initlal text box count
    $(mz_add_button).click(function(e){ //on add input button click
        e.preventDefault();
        if(mz_x < mz_max_fields){ //max input box allowed
            mz_x++; //text box increment
            $(mz_wrapper).append("<div><table id='addmz' width='385px' cellspacing='5px' style='border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;'><tr><th colspan='2'>&nbsp;</th></tr><tr><th colspan='2'><hr class='white-hr' /></th></tr><tr><th colspan='2'>&nbsp;</th></tr></table><a href='#' class='mz_remove_field'><table id='addmz' width='385px' cellspacing='5px' style='border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;'><tr><th align='left'>Zeitnehmer entfernen</th><th align='right'><font color='#FFD700'>[&ndash;]</font></th></tr></table></a><tr><th colspan='2'><hr /></th></tr></table><table width='385px' cellspacing='5px' style='border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;'><tr><td align='left'>Position<font color='#8E6516'>*</font></td><td align='right'><select name='mz_id[]' class='input-block-level' placeholder='Bitte auswählen' style='background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;' required='required'><option selected='selected' disabled='disabled'>Bitte auswählen</option><option value='Start'>Start</option><option value='Ziel'>Ziel</option><option value='Omni'>Beide</option></select></td><tr><td align='left'>Prüfung<font color='#8E6516'>*</font></td><td align='right'><select name='mz_id[]' class='input-block-level' placeholder='Bitte auswählen' style='background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;' required='required'><option selected='selected' disabled='disabled'>Bitte auswählen</option>"+<?$select_option = "SELECT * FROM _main_wptable WHERE `eid` = '".$eid."'";$result_option	= mysqli_query($mysqli, $select_option);$anzahl_option	= mysqli_num_rows($result_option);while($row = mysqli_fetch_assoc($result_option)) {echo "<option value='".$row['rid']."'>".$row['rid_type'].$row['rid']."</option>";}?>+"</select></td></tr></tr></table></div>"); //add input box
        }
    });
                                        			   
    $(mz_wrapper).on("click",".mz_remove_field", function(e){ //user click on remove text
        e.preventDefault(); $(this).parent('div').remove(); mz_x--;
    })
});
*/

/* COPIED TO _ADDRD.PHP DUE TO DOM
// RUNDEN
$(document).ready(function() {
	var rd_max_fields      = 15; //maximum input boxes allowed
	var rd_wrapper         = $(".rd_input_fields_wrap"); //Fields wrapper
	var rd_add_button      = $(".rd_add_field_button"); //Add button ID
		   
	var rd_x = 1; //initlal text box count
	$(rd_add_button).click(function(e){ //on add input button click
		e.preventDefault();
		if(rd_x < rd_max_fields){ //max input box allowed
			rd_x++; //text box increment
			$(rd_wrapper).append('<div><table id="addrd" width="385px" "cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;"><tr><th colspan="2">&nbsp;</th></tr><tr><th colspan="2"><hr class="white-hr" /></th></tr><tr><th colspan="2">&nbsp;</th></tr></table><a href="#" class="rd_remove_field"><table id="addrd" width="385px" "cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;"><tr><th align="left">Prüfung entfernen</th><th align="right"><font color="#FFD700">[&ndash;]</font></th></tr></table></a><tr><th colspan="2"><hr /></th></tr></table><table width="385px" "cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;"><tr><td align="left">Prüfungsnummer<font color="#8E6516">*</font></td><td align="right"><input name="rid[]" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="' + rd_x + '" required="required" /></td></tr><tr><td align="left">Sollzeit<font color="#8E6516">*</font></td><td align="right"><input name="rid[]" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="Bsp. ' + Math.floor((Math.random() * 7) + 1) + ':' + Math.floor((Math.random() * 49) + 10) + '" required="required" /></td></tr><tr><td align="left">Zwischenzeit?</td><td align="right"><table width="135px" cellspacing="0" style="border: 0;"><tr><td align="left"><div class="checkboxOne"><input type="checkbox" value="yes" id="checkboxOneInput_"' + rd_x + '" name="rid[]"/><label for="checkboxOneInput_"' + rd_x + '"></label></div></td><td align="right"><select name="rid[]" id="rid_zz_"' + rd_x + '" class="input-block-level" placeholder="Anzahl" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 75px;" ><option selected=\'selected\' disabled=\'disabled\'>Zeiten</option><option value=\'1\'>1</option><option value=\'2\'>2</option><option value=\'3\'>3</option></select></td></tr></table></td></tr></tr></table></div>'); //add input box
		}
	});
			   
	$(rd_wrapper).on("click",".rd_remove_field", function(e){ //user click on remove text
		e.preventDefault(); $(this).parent('div').remove(); rd_x--;
	})
});
*/