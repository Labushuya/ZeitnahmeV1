<?php error_reporting(E_ALL);
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
	
	// START SECURE SESSION
	sec_session_start();
	 
	// CUSTOM NAVBAR
	if(login_check($mysqli) == true) {
		// SEARCH FOR USER EVENTS
		// CREATE EVENT ID FROM ACTIVE SESSION
		$eid	= $_SESSION['user_id'];
		
		// SEARCH FOR EVENTS FROM LOGGED IN USER
		$select_event = "SELECT `id`, `eid`, `start`, `end`, `t_calc` FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `active` = '1'";
		$result_event = mysqli_query($mysqli, $select_event);
		$numrow_event = mysqli_num_rows($result_event);
		$getrow_event = mysqli_fetch_assoc($result_event);
		
		$start = $getrow_event['start'];
		$end = $getrow_event['end'];
		
		//	Hole Art der Berechnung
		//	1 - Ab Start (X zu Y und X zu Z)
		//	2 - Sequenziell (X zu Y und Y zu Z)
		//	3 - Seq. Differenz (Differenz von X zu Y plus / minus Differenz von Y zu Z)
		$calculation = $getrow_event['t_calc'];
		
		// SEARCH FOR EVENTS FROM LOGGED IN USER
		if($numrow_event == 1) {
			$logged = file_get_contents("essentials/logout.html");
			$navbar = file_get_contents("essentials/navbar_logged_in_active.html");
		// NO EVENT FOUND
		} elseif($numrow_event == 0) {
			$logged = file_get_contents("essentials/logout.html");
			$navbar = file_get_contents("essentials/navbar_logged_in.html");
		}
	} else {
		$logged = file_get_contents("essentials/login.html");
		$navbar = file_get_contents("essentials/navbar_logged_out.html");
		header("Location: index.php");
	}
	
	// CHECK FOR ERRORS
	if(isset($_GET['error'])) {
        echo "<p id=\"error\">Error Logging In!</p>";
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//DE" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" >

	<head>
		<!--	SET TITLE		-->
		<title>Z 3 : 1 T : 0 0 , 000</title>
		
		<!--	SET META		-->
		<meta name="description" content="TimeKeeper - Das Datenzentrum im Motorsport!">
		<meta name="author" content="Ultraviolent (www.mindsources.net)" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width">
		
		<!--	INCLUDING ICO	-->
		<link rel="shortcut icon" href="favicon.ico">
		<!--	INCLUDING LIB	-->
		<?php include("lib/library.html"); ?>
		
		<style>
		    .zz {
		        display: none;
		    }
		</style>
		
		<script>
			$(document).ready(function() {
				// INPUT MASK FOR RESULTS
				$(function($){
					$("#till").mask("99:99",{placeholder:"HH:MM"});
				});
				
				$.datepicker.regional['de'] = {
					showWeek: true,
					showButtonPanel: true,
					closeText: 'Fertig',
					prevText: '<<',
					nextText: '>>',
					currentText: 'heute',
					monthNames: [
						'Januar',
						'Februar',
						'März',
						'April',
						'Mai',
						'Juni',
						'Juli',
						'August',
						'September',
						'Oktober',
						'November',
						'Dezember'
					],
					monthNamesShort: [
						'Jan',
						'Feb',
						'Mär',
						'Apr',
						'Mai',
						'Jun',
						'Jul',
						'Aug',
						'Sep',
						'Okt',
						'Nov',
						'Dez'
					],
					dayNames: [
						'Sonntag',
						'Montag',
						'Dienstag',
						'Mittwoch',
						'Donnerstag',
						'Freitag',
						'Samstag'
					],
					dayNamesShort: [
						'So',
						'Mo',
						'Di',
						'Mi',
						'Do',
						'Fr',
						'Sa'
					],
					dayNamesMin: [
						'So',
						'Mo',
						'Di',
						'Mi',
						'Do',
						'Fr',
						'Sa'
					],
					weekHeader: 'KW',
					dateFormat: 'dd.mm.yy',
					firstDay: 0,
					isRTL: false,
					showMonthAfterYear: false,
					yearSuffix: ''
				};
				
				$.datepicker.setDefaults(
					$.datepicker.regional["de"], 
					$('#ends').datepicker('option', 'dateFormat', 'dd/mm/yy')
				);

				$("#ends").datepicker({
					minDate: new Date(<? echo "'" . $start . "'"; ?>),
					maxDate: new Date(<? echo "'" . $end . "'"; ?>),
					onSelect: function(selected) {
						$("#ends").datepicker("option","minDate", selected);
						$("#ends").datepicker("option","maxDate", selected);
					}
				});
				
				// INPUT MASK FOR RESULTS
				$(function($){
					$("#rd_id_sz").mask("99:99,99",{placeholder:"MM:SS,00"});
					$(".rd_id_sz").mask("99:99,99",{placeholder:"MM:SS,00"});
				});
				
				$("#rd_id_zz").change(function() {
					var htmlString = "";
					var len = $("#rd_id_zz").find("option:selected").val();
					
					//	Übergebe Art der Berechnung an JavaScript
					var calculation = <?php echo $calculation; ?>
					
					alert(calculation);
					
					//	Ab Start
					if(calculation == 1) {
						if(len == 0) {
							var array = ['Start / Ziel'];
						} else if(len == 1) {
							var array = ['Start / ZZ1', 'Start / Ziel'];
						} else if(len == 2) {
							var array = ['Start / ZZ1', 'Start / ZZ2', 'Start / Ziel'];
						} else if(len == 3) {
							var array = ['Start / ZZ1', 'Start / ZZ2', 'Start / ZZ3', 'Start / Ziel'];
						} else if(len == 4) {
							var array = ['Start / ZZ1', 'Start / ZZ2', 'Start / ZZ3', 'Start / ZZ4', 'Start / Ziel'];
						} else if(len == 5) {
							var array = ['Start / ZZ1', 'Start / ZZ2', 'Start / ZZ3', 'Start / ZZ4', 'Start / ZZ5', 'Start / Ziel'];
						}
					//	Sequenziell und sequenziell Differenz
					} else if(calculation == 2 || calculation == 3) {
						if(len == 0) {
							var array = ['Start / Ziel'];
						} else if(len == 1) {
							var array = ['Start / ZZ1', 'ZZ1 / Ziel'];
						} else if(len == 2) {
							var array = ['Start / ZZ1', 'ZZ1 / ZZ2', 'ZZ2 / Ziel'];
						} else if(len == 3) {
							var array = ['Start / ZZ1', 'ZZ1 / ZZ2', 'ZZ2 / ZZ3', 'ZZ3 / Ziel'];
						} else if(len == 4) {
							var array = ['Start / ZZ1', 'ZZ1 / ZZ2', 'ZZ2 / ZZ3', 'ZZ3 / ZZ4', 'ZZ4 / Ziel'];
						} else if(len == 5) {
							var array = ['Start / ZZ1', 'ZZ1 / ZZ2', 'ZZ2 / ZZ3', 'ZZ3 / ZZ4', 'ZZ4 / ZZ5', 'ZZ5 / Ziel'];
						}
					}
					
					$.each(array, function(i, val) {
						htmlString += '<tr class="appended">';
						htmlString += '<td align="left" class="hide_content">Sollzeit ' + val + '<font color="#8E6516">*</font></td>';
						htmlString += '<td align="right" class="hide_content"><input name="rd_id_sz[]" class="rd_id_sz" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="MM:SS,00" required="required" /></td>';
						htmlString += '</tr>';
					});
					$("#sub_sz").html(htmlString);
					$(".rd_id_sz").mask("99:99,99",{placeholder:"MM:SS,00"});					
				});
				
				// STATIC CHECKBOX
				//$("#rd_id_zz").hide();
				$('body').on('click','.checkboxOne input',function(){
					$(this).closest('tr').find(".zz").toggle(500, 'easeInOutCubic');
				});
				
				$(".checkboxOne").change(function() {
					if($(".checkboxOne").attr('checked', false)) {
						$("#sub_sz").html('');
						$("#sub_sz").html('<tr><td align="left" class="hide_content">Sollzeit Start / Ziel<font color="#8E6516">*</font></td><td align="right" class="hide_content"><input name="rd_id_sz[]" class="rd_id_sz" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="MM:SS,00" required="required" /></td><tr>');
						$('#rd_id_zz').prop('selectedIndex', 0);
						$(".rd_id_sz").mask("99:99,99",{placeholder:"MM:SS,00"});
					}
					
				});
				
				$("#z_entry").change(function() {
					if($("#z_entry").val() == "0") {
						$(".zwischenzeit").show();
						$(".appended").show();	
						
						var htmlString = "";
						var len = $("#rd_id_zz").find("option:selected").val();
						
						//	Übergebe Art der Berechnung an JavaScript
						var calculation = <?php echo $calculation; ?>
						
						//	Ab Start
						if(calculation == 1) {
							if(len == 0) {
								var array = ['Start / Ziel'];
							} else if(len == 1) {
								var array = ['Start / ZZ1', 'Start / Ziel'];
							} else if(len == 2) {
								var array = ['Start / ZZ1', 'Start / ZZ2', 'Start / Ziel'];
							} else if(len == 3) {
								var array = ['Start / ZZ1', 'Start / ZZ2', 'Start / ZZ3', 'Start / Ziel'];
							} else if(len == 4) {
								var array = ['Start / ZZ1', 'Start / ZZ2', 'Start / ZZ3', 'Start / ZZ4', 'Start / Ziel'];
							} else if(len == 5) {
								var array = ['Start / ZZ1', 'Start / ZZ2', 'Start / ZZ3', 'Start / ZZ4', 'Start / ZZ5', 'Start / Ziel'];
							}
						//	Sequenziell und sequenziell Differenz
						} else if(calculation == 2 || calculation == 3) {
							if(len == 0) {
								var array = ['Start / Ziel'];
							} else if(len == 1) {
								var array = ['Start / ZZ1', 'ZZ1 / Ziel'];
							} else if(len == 2) {
								var array = ['Start / ZZ1', 'ZZ1 / ZZ2', 'ZZ2 / Ziel'];
							} else if(len == 3) {
								var array = ['Start / ZZ1', 'ZZ1 / ZZ2', 'ZZ2 / ZZ3', 'ZZ3 / Ziel'];
							} else if(len == 4) {
								var array = ['Start / ZZ1', 'ZZ1 / ZZ2', 'ZZ2 / ZZ3', 'ZZ3 / ZZ4', 'ZZ4 / Ziel'];
							} else if(len == 5) {
								var array = ['Start / ZZ1', 'ZZ1 / ZZ2', 'ZZ2 / ZZ3', 'ZZ3 / ZZ4', 'ZZ4 / ZZ5', 'ZZ5 / Ziel'];
							}
						}
						
						$.each(array, function(i, val) {
							htmlString += '<tr class="appended">';
							htmlString += '<td align="left" class="hide_content">Sollzeit ' + val + '<font color="#8E6516">*</font></td>';
							htmlString += '<td align="right" class="hide_content"><input name="rd_id_sz[]" class="rd_id_sz" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="MM:SS,00" required="required" /></td>';
							htmlString += '</tr>';
						});
						$("#sub_sz").html(htmlString);
						$(".rd_id_sz").mask("99:99,99",{placeholder:"MM:SS,00"});	
					} else if($("#z_entry").val() == "1") {
						$(".zwischenzeit").hide();
						$(".appended").hide();	
						$("#sub_sz").html('<tr id="original"><td align="left">Sollzeit Start / Ziel<font color="#8E6516">*</font></td><td align="right"><input class="rd_id_sz" id="rd_id_sz" name="rd_id_sz[]" maxlength="8" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="MM:SS,00" required="required" /></td></tr>');
						$("#rd_id_sz").mask("99:99,99",{placeholder:"MM:SS,00"});
					}					
				});
				
				// FADE ERROR MESSAGE
				$('.error').delay(5000).fadeOut(500);
			});
		</script>
		
		<noscript>
			<div style="z-index: 5000; left: 0; position: fixed; text-align: center; width: 100%; height: 100%; background-color: rgba(48, 48, 48, 0.75);">
				<h2 style="line-height: 100%; padding-top: 25%; color: #fff;"><span style="border: 1px dotted #fff; padding: 25px 50px 25px 50px; background-color: rgba(255, 0, 0, 0.25)">Bitte aktivieren Sie JavaScript!</span></h2>
			</div>
		</noscript>
	</head>

	<body>
		<div id="container">
			<div id="linkList">			
				<!--	NAVIGATION	-->
				<?php
					echo $navbar;
				?>
				
				<!--	LOGIN 		-->
				<?php
					echo $logged;
				?>
			</div>
			
			<div id="intro">
				<div id="pageHeader">
					<h1><span style="position:absolute; right: 30px; top: 25px;">Z 3 : 1 T : 0 0 , 000</span></h1>
					<h3><span style="position:absolute; right: 30px; top: 64px;"><i>tempus fugit ..</i></span></h3>
				</div>
			
				<!-- 	COLUMN 1	-->
				<div id="modul_1" align="center">
					<h3>Mein Event</h3>
					<p>
						<form action="<? $_SERVER['PHP_SELF']; ?>" method="POST" id="rd_form">
							<div class="rd_input_fields_wrap" id="rd_input_fields_wrap">
								<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 1px solid #FFFFFF; border-right: 1px solid #FFFFFF; border-bottom: 0;">
									<tr>
										<!-- <th align="left" id="substitute_status">Prüfung(en) hinzufügen</th> -->
										<th align="left" colspan="2">Prüfung hinzufügen</th>
										<!-- <th align="right"><a href="#" class="rd_add_field_button" id="add_field"><font color="#FFD700">[+]</font></a></th> -->
									</tr>
									<tr>
										<th colspan="2"><hr /></th>
									</tr>
								</table>
								<table id="addrd" width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;">
									<tr>
										<td align="left">Bezeichnung<font color="#8E6516">*</font></td>
										<td align="right">
											<select name="rid_type" id="rid_type" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" required="required">
												<?php
													// QUERIES _RACE_RUN_EVENTS
													$select_race_run = "SELECT * FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `active` = '1'";
													$result_race_run = mysqli_query($mysqli, $select_race_run);
													$spalte_race_run = mysqli_fetch_assoc($result_race_run);

													// QUERIES _MAIN_WPTABLE
													$select_main_wpt = "SELECT * FROM `_main_wptable` WHERE `eid` = '" . $eid . "'";
													$result_main_wpt = mysqli_query($mysqli, $select_main_wpt);
													$spalte_main_wpt = mysqli_fetch_assoc($result_main_wpt);
													$numrow_main_wpt = mysqli_num_rows($result_main_wpt);
													
													// CHECK IF THERE ARE ROWS. IF SO, THEN FILL HIDDEN FIELD
													// JS WILL CHECK FOR VALUE AND ONLY EXEC WHEN NOT EMPTY
													// PREVENTS INITIAL ADDING OF ROUNDS. OTHERWISE ONCHANGE
													// "NA"-ERROR WILL BE SHOWN
													
													if($numrow_main_wpt > 0) {
														$secure_val = 1;
													} elseif($numrow_main_wpt == 0) {
														$secure_val = 0;
													}
														
													// FETCH RD_TYPE AND INFOR FROM DATABASE
													// RD_TYPE IN _RACE_RUN_EVENTS FOUND
													if($spalte_race_run['master_rid_type'] == "WP" OR $spalte_race_run['master_rid_type'] == "GP" OR $spalte_race_run['master_rid_type'] == "SP") {
														echo "<option value='" . $spalte_race_run['master_rid_type'] . "' selected='selected'>" . $spalte_race_run['master_rid_type'] . "</option>";
														echo "<optgroup label='ändern zu ..' style='color: #8E6516;'>";
														// SHOW INDIVIDUAL OPTIONS BASED ON DATABASE ENTRY
														if($spalte_race_run['master_rid_type'] == "GP") {
															echo "<option value='changeto_WP'>WP</option>";
															echo "<option value='changeto_SP'>SP</option>";
														} elseif($spalte_race_run['master_rid_type'] == "WP") {
															echo "<option value='changeto_GP'>GP</option>";
															echo "<option value='changeto_SP'>SP</option>";
														} elseif($spalte_race_run['master_rid_type'] == "SP") {
															echo "<option value='changeto_GP'>GP</option>";
															echo "<option value='changeto_WP'>WP</option>";
														}
														echo "</optgroup>";
													} else {
														echo "<option selected='selected' disabled='disabled'>Bitte auswählen</option>";
														echo "<option value='GP'>GP</option>";
														echo "<option value='WP'>WP</option>";
														echo "<option value='SP'>SP</option>";
													}
												?>
											</select>
										</td>
									</tr>
									<tr>
									    <td align="left">Ort / Position</td>
									    <td align="right"><input name="title" id="title" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="Bsp. Opel Arena" /></td>
									</tr>
									<tr>
										<td align="left">Zeiteneingabe<font color="#8E6516">*</font></td>
										<td align="right">
											<select name="z_entry" id="z_entry" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;">
                								<option value='0' selected='selected'>Regulär</option>
                								<option value='1'>Fahrtzeit</option>
                							</select>
										</td>
									</tr>
									<tr>
										<?
											// FETCH ID OF LAST ADDED ROUND
											$select_last_rd_id = "SELECT * FROM _main_wptable WHERE `eid` = '" . $eid . "' ORDER BY `rid` DESC";
											$result_last_rd_id = mysqli_query($mysqli, $select_last_rd_id);
											$numrow_last_rd_id = mysqli_num_rows($result_last_rd_id);
											$getrow_last_rd_id = mysqli_fetch_assoc($result_last_rd_id);
													
											if($numrow_last_rd_id == 0) {
												$last_added_rd_id = 1;
											} elseif($numrow_last_rd_id > 0) {
												$last_added_rd_id = $numrow_last_rd_id + 1;
											}
										?>
										<td align="left" class="hide_content">Prüfungsnummer<font color="#8E6516">*</font></td>
										<td align="right" class="hide_content"><input id="rd_id_pn" name="rd_id_pn" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" value="<? echo $last_added_rd_id; ?>" readonly="readonly" required="required" /></td>
									</tr>
								</table>
								<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;">			<tr>
										<td align="left">Stichtag<font color="#8E6516">*</font></td>
										<td align="right"><input name="ends" id="ends" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="TT.MM.JJJJ" required="required" pattern="^((0[1-9]|[1-2][0-9]|3[0-1]).(0[1-9]|1[0-2]).([1-9]{1}[0-9]{3}))$" /></td>
									</tr>
									<tr>
										<td align="left">Endet um<font color="#8E6516">*</font></td>
										<td align="right"><input name="till" id="till" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="HH:MM" required="required" pattern="^(([01]?[0-9]|2[0-3]):[0-5][0-9]){1}$" /></td>
									</tr>
									<tr>
										<td align="left">Geheim?</td>
										<td align="right">
											<select name="secret" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" >
												<option value='0' selected='selected'>Nein</option>
												<option value='1'>Ja</option>
											</select>
										</td>
									</tr>
								</table>
								<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;">
									<tr>
									    <td align="left" class="hide_content zwischenzeit">Zwischenzeit?</td>
									    <td align="right" class="hide_content zwischenzeit">
									        <table width="135px" cellspacing="0" style="border: 0;">
									            <tr>
									                <td align="left">
        									            <div class="checkboxOne">
                                               			    <input type="checkbox" id="checkboxOneInput" />
                                               				<label for="checkboxOneInput"></label>
                                                   		</div>
                                                   	</td>
                                                   	<td align="right">
                                                   		<select name="rd_id_zz" id="rd_id_zz" class="zz" placeholder="Anzahl" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 75px;" >
                											<option value="0" selected='selected'>Zeiten</option>
                											<option value='1'>1</option>
                											<option value='2'>2</option>
                											<option value='3'>3</option>
                											<option value='4'>4</option>
                											<option value='5'>5</option>
                										</select>
                									</td>
                								</tr>
                							</table>
									    </td>
								    </tr>
								</table>
								<table id="sub_sz" width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;">
									<tr id="original">
										<td align="left">Sollzeit Start / Ziel<font color="#8E6516">*</font></td>
										<td align="right"><input class="rd_id_sz" id="rd_id_sz" name="rd_id_sz[]" maxlength="8" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="MM:SS,00" required="required" /></td>
									</tr>
								</table>
							</div>
							<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF;">
								<tr>
									<th colspan="2">&nbsp;</th>
								</tr>
								<tr>
									<th colspan="2">Eingaben speichern</th>
								</tr>
								<tr>
									<th colspan="2"><hr /></th>
								</tr>
								<tr>
									<td align="left"><input type="button" value="<<" onclick="window.location='my_event.php';" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;"/></td>
									<td align="right">
										<input type="submit" name="r_save" id="save" value="Speichern" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" />
										<input type="hidden" name="secure_val" id="secure_val" value="<? echo $secure_val; ?>" />										
									</td>
								</tr>
							</table>
							<table width="385px" cellspacing="5px" style="border: 0;">
								<tr>
									<th colspan="2">&nbsp;</th>
								</tr>
							</table>
							<!--
							<div id="slider">
								<div id="custom-handle" class="ui-slider-handle"></div>
							</div>
							-->
						</form>
						
						<? error_reporting(E_ALL);
							if(isset($_POST['r_save'])) {
								// SET INITIAL SUCCESS COUNT TO ZERO
								$pts = 0;
								
							    // REDECLARE POST
								$rid_type			= mysqli_real_escape_string($mysqli, utf8_encode($_POST['rid_type']));
								$secret				= mysqli_real_escape_string($mysqli, utf8_encode($_POST['secret']));
								$rid_pn			    = mysqli_real_escape_string($mysqli, utf8_encode($_POST['rd_id_pn']));
								$title			    = mysqli_real_escape_string($mysqli, utf8_encode(titleCase($_POST['title'])));
								
								if(isset($_POST['rd_id_zz'])) {
									$rid_zz		    = mysqli_real_escape_string($mysqli, utf8_encode($_POST['rd_id_zz']));
								} else {
									$rid_zz		    = 0;
								}
								
								$z_entry			= mysqli_real_escape_string($mysqli, utf8_encode($_POST['z_entry']));
								$ends				= mysqli_real_escape_string($mysqli, utf8_encode($_POST['ends']));
								$execute			= mysqli_real_escape_string($mysqli, utf8_encode($_POST['till']));
								$_POST['rd_id_sz']	= array_map(array($mysqli, 'real_escape_string'), $_POST['rd_id_sz']);
								
								// CONVERT DATE TO TIME AND CONNECT WITH TIME POST
								$date = convert_to_db($ends);
								$execute = strtotime($date . " " . $execute . ":00.00");
								
								// UNSET POST
								unset($_POST['r_save']);
								unset($_POST['rid_type']);
								unset($_POST['rd_id_pn']);
								unset($_POST['rd_id_zz']);
								unset($_POST['secure_val']);
								unset($_POST['ends']);
								unset($_POST['till']);
								unset($_POST['secret']);
								unset($_POST['title']);
								
								//	Wenn Prüfung geheim, passe Toggle an
								if($secret == 0) {
									$toggle = 0;
								} elseif($secret == 1) {
									$toggle = 1;
								}
								
								// FIRST CHECK FOR MANIPULATED ROUND NUMBER AND AUTO ALLOCATE IF NECCESSARY
								$select_round_number = "SELECT `eid`, `rid` FROM `_main_wptable` WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid_pn . "'";
								$result_round_number = mysqli_query($mysqli, $select_round_number);
								$numrow_round_number = mysqli_num_rows($result_round_number);
								
								// ROUND ID HAS ALREADY BEEN FOUND
								if($numrow_round_number > 0) {
									// GIVE NEW VALUE THROUGH CHECKING NUM_ROWS + 1 FROM SEARCH W/O RD_ID
									$select_round_number_new = "SELECT `eid`, `rid` FROM `_main_wptable` WHERE `eid` = '" . $eid . "'";
									$result_round_number_new = mysqli_query($mysqli, $select_round_number_new);
									$numrow_round_number_new = mysqli_num_rows($result_round_number_new);
									$rid_pn = $numrow_round_number_new + 1;
								}
								
								// SET TOTAL POSITIONS IN RELATION TO Z_ENTRY CHOICE
								if($z_entry == 0) {
									$total_pos = intval($rid_zz) + 2;
								} elseif($z_entry == 1) {
									$total_pos = 1;
								}
								
								// BUILD INSERT QUERY
								$insert_rd_id	= 	"INSERT INTO
														`_main_wptable`(
															`id`,
															`eid`,
															`rid_type`,
															`rid`,
															`rid_attr`,
															`suspend`,
															`secret`,
															`toggle_secret`,
															`execute`,
															`finished`,
															`title`,
															`total_pos`,
															`ref_rd`,
															`z_entry`
														)
													VALUES(
														NULL,
														'" . $eid . "',
														'" . $rid_type . "',
														'" . $rid_pn . "', 
														'" . $rid_zz . "',
														'0',
														'" . $secret . "',
														'" . $toggle . "',
														'" . $date . "',
														'" . $execute . "',
														'" . $title . "',
														'" . $total_pos . "',
														'0',
														'" . $z_entry . "'
													)";			 
								// EXCECUTE QUERY
								mysqli_query($mysqli, $insert_rd_id);
								
								if(mysqli_affected_rows($mysqli) > 0) {
									$pts++;
								}
											
								// CHECK FOR MORE THAN ONE ITEM IN ARRAY
								if(count($_POST['rd_id_sz']) > 1) {
									// LOOP THROUGH SZ POST
									for($i = 0; $i < count($_POST['rd_id_sz']); $i++) {
										// INITIAL COUNT FOR ALLOCATION
										$j = $i + 1;
										
										// BUILD INSERT QUERY
										$insert_sz_id	= 	"INSERT INTO
																`_main_wptable_sz`(
																	`id`,
																	`eid`,
																	`rid`,
																	`sz_cid`,
																	`sz`
																)
															VALUES(
																NULL,
																'" . $eid . "',
																'" . $rid_pn . "',
																'" . $j . "',
																'00:" . $_POST['rd_id_sz'][$i] . "'
															)";			 
										// EXCECUTE QUERY
										mysqli_query($mysqli, $insert_sz_id);
										
										if(mysqli_affected_rows($mysqli) > 0) {
											$pts++;
										}
									}
								} elseif(count($_POST['rd_id_sz']) == 1) {
									// BUILD INSERT QUERY
									$insert_sz_id	= 	"INSERT INTO
																`_main_wptable_sz`(
																	`id`,
																	`eid`,
																	`rid`,
																	`sz_cid`,
																	`sz`
																)
															VALUES(
																NULL,
																'" . $eid . "',
																'" . $rid_pn . "',
																'1',
																'00:" . $_POST['rd_id_sz'][0] . "'
															)";		 
									// EXCECUTE QUERY
									mysqli_query($mysqli, $insert_sz_id);
									
									if(mysqli_affected_rows($mysqli) > 0) {
										$pts++;
									}
								}
								
								// UPDATE EVERY EMPTY RD_TYPE
								// SET UPDATE QUERIES
								$update_race_run = "UPDATE _race_run_events SET `master_rid_type` = '" . $rid_type . "' WHERE `eid` = '" . $eid . "' AND `active` = '1'";
								mysqli_query($mysqli, $update_race_run);
								$update_main_wpt = "UPDATE _main_wptable SET `rid_type` = '" . $rid_type . "' WHERE `eid` = '" . $eid . "'";
								mysqli_query($mysqli, $update_main_wpt);
								$update_optiozme = "UPDATE _optio_zmembers SET `rid_type` = '" . $rid_type . "' WHERE `eid` = '" . $eid . "'";
								mysqli_query($mysqli, $update_optiozme);
								
								// PREPARE WPTABLE_COUNT
								$count = 1;
								$update_race_run_count_wptable = "UPDATE _race_run_events SET `count_wptable` = `count_wptable` + '" . $count . "' WHERE `eid` = '" . $eid . "' AND `active` = '1'";
								    	
								// EXCECUTE UPDATE COUNT
								mysqli_query($mysqli, $update_race_run_count_wptable);
										
								// SET REDIRECT
								$redir = '<meta http-equiv="refresh" content="1; url=/msdn/_addrd.php">';
								
								// IF QUERY SUCCESSFUL
								if($pts >= 2) {
									$state      = 'Erfolgreich!';
									$error_msg  = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Rundendaten wurden erfolgreich angelegt!</span><br />';
								} else {
									$state      = 'Fehler:';
									$error_msg  = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Rundendaten konnten nicht angelegt werden!</span><br />';
								}
							} else {
								$state       = 'Hinweis:';
								$error_msg   = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Zeitnehmer müssen Prüfungen zugewiesen werden</span><br />';
							}
    					?>
    						
        				<?php
        					if(!empty($error_msg)) {
        						echo '<p class="error">';
        							echo '<span style="margin-left: 5px; border-bottom: 1px dotted #8E6516;"><font color="#FFD700">' . $state . '</font></span><br />';
        							echo $error_msg;
        						echo '</p>';
        					}
							
							echo '<p class="error2">';
        					echo '</p>';
							
							if(isset($redir)) {
								echo $redir;
							}
        				?>
						
						<table width="385px" cellspacing="5px" style="border: 0;">
							<tr>
								<th colspan="2">&nbsp;</th>
							</tr>
						</table>
					</p>
				</div>
		
				<div id="supportingText">
					<!-- 	COLUMN 2	-->
					<!--
					<div id="modul_2" align="center">
						<h3><span>ÜBERSCHRIFT 2</span></h3>
						<p>
							Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentialsly unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
						</p>
					</div>
					-->
				</div>

				<!-- 	FOOTER 		-->
				<?php
					include("essentials/footer.php");
				?>
			</div>
		</div>
	</body>
</html>
