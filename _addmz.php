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
		$select_event = "SELECT `id`, `eid` FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `active` = '1'";
		$result_event = mysqli_query($mysqli, $select_event);
		$numrow_event = mysqli_num_rows($result_event);
		
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
		
		<script>
		    $(document).ready(function() {
		        // SELECT OPTION CHECK
		        $('#form').submit(function (e) {
                    if ($('.option').val() == '') {
						// STOP FORM SUBMISSION
                        e.preventDefault();
                    }
                });
		        
		        // TIMEKEPPER
				// MAX AMOUNT OF BOX SETS
                var mz_max_fields      = 15;
				// FIELD WRAPPER
                var mz_wrapper         = $(".mz_input_fields_wrap");
				// ADD ID BUTTON
                var mz_add_button      = $(".mz_add_field_button");
                                
				// FIRST ITERATION
                var mz_x = 1;
				// CLICK ADD
                $(mz_add_button).click(function(e){
                    e.preventDefault();
					// IF BOX SET AMOUNT IS LOWER THAN EXPECTED
                    if(mz_x < mz_max_fields){
						// ITERATE COUNTE BY ONE
						mz_x++;
                        $(mz_wrapper).append("<div><table id='addmz' width='385px' cellspacing='5px' style='border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;'><tr><th colspan='2'>&nbsp;</th></tr><tr><th colspan='2'><hr class='white-hr' /></th></tr><tr><th colspan='2'>&nbsp;</th></tr></table><a href='#' class='mz_remove_field'><table id='addmz' width='385px' cellspacing='5px' style='border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;'><tr><th align='left'>Zeitnehmer entfernen</th><th align='right'><font color='#FFD700'>[&ndash;]</font></th></tr></table></a><tr><th colspan='2'><hr /></th></tr></table><table width='385px' cellspacing='5px' style='border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;'><tr><td align='left'>Prüfung<font color='#8E6516'>*</font></td><td align='right'><select name='mz_id[]' class='rid_" + mz_x + " option' style='background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;' required='required'><? $select_option	= "SELECT * FROM _main_wptable WHERE `eid` = '" . $eid . "' ORDER BY rid ASC"; $result_option = mysqli_query($mysqli, $select_option); $anzahl_option = mysqli_num_rows($result_option); if($anzahl_option > 0) { echo "<option value=''>Prüfung wählen</option>"; while($row = mysqli_fetch_assoc($result_option)) { echo "<option value='" . $row["rid_type"] . $row["rid"] . "'>" . $row["rid_type"] . $row["rid"] . "</option>"; } } elseif($anzahl_option == 0) { echo "<option>Keine Prüfungen</option>"; } ?></select></td></tr><tr><td align='left'>1. Position<font color='#8E6516'>*</font></td><td align='right'><select name='mz_id[]' class='rid_pos_" + mz_x + "_1' style='background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;' required='required'><option>Prüfung wählen</option></select></td></tr><tr><td align='left'>2. Position<font color='#8E6516'>*</font></td><td align='right'><select name='mz_id[]' class='rid_pos_" + mz_x + "_2' style='background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;'><option value=''>Prüfung wählen</option></select></td></tr><tr><td align='left'>3. Position<font color='#8E6516'>*</font></td><td align='right'><select name='mz_id[]' class='rid_pos_" + mz_x + "_3' style='background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;'><option value=''>Prüfung wählen</option></select></td></tr><tr><td align='left'>4. Position<font color='#8E6516'>*</font></td><td align='right'><select name='mz_id[]' class='rid_pos_" + mz_x + "_4' style='background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;'><option value=''>Prüfung wählen</option></select></td></tr><tr><td align='left'>5. Position<font color='#8E6516'>*</font></td><td align='right'><select name='mz_id[]' class='rid_pos_" + mz_x + "_5' style='background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;'><option value=''>Prüfung wählen</option></select></td></tr></tr></table></div>"); // add input box
                    }
                });
                                                    			   
				// USER CLICK ON REMOVE TEXT
                $(mz_wrapper).on("click",".mz_remove_field", function(e){
                    e.preventDefault(); 
					$(this).parent('div').remove(); 
                    mz_x--;
                });
				
				// RIGHT BEFORE FORM IS SUBMITTED THEY GET ENABLED FOR
				// SUBMITTING "EMPTY" VALUES (SCRIPT LOOPS EXACT AMOUNT)
				$('#form').submit(function() {
					$('.rid_pos_1_2').removeAttr('disabled');
					$('.rid_pos_1_3').removeAttr('disabled');
					$('.rid_pos_1_4').removeAttr('disabled');
					$('.rid_pos_1_5').removeAttr('disabled');
				});
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
					<h1><span style="position: absolute; right: 30px; top: 25px;">Z 3 : 1 T : 0 0 , 000</span></h1>
					<h3><span style="position: absolute; right: 30px; top: 64px;"><i>tempus fugit ..</i></span></h3>
				</div>
			
				<!-- 	COLUMN 1	-->
				<div id="modul_1" align="center">
					<h3>Mein Event</h3>
					<p>
						<form action="<? $_SERVER['PHP_SELF']; ?>" method="POST" id="form">
							<div class="mz_input_fields_wrap">								
								<a href="#" class="mz_add_field_button">
								<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 1px solid #FFFFFF; border-right: 1px solid #FFFFFF; border-bottom: 0;">
									<tr>
										<th align="left">Zeitnehmer hinzufügen</th>
										<th align="right"><font color="#FFD700"><!--[+]--></font></th>
									</tr>
									<tr>
										<th colspan="2"><hr /></th>
									</tr>
								</table>
								</a>
								
								<div>
									<table id="addmz" width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;">
										<tr>
										    <td colspan="2" align="justify">
										        Legen Sie hier Ihre Zeitnehmerdaten fest. Beachten Sie die Reihenfolge der Positionen. Sie benötigen Hilfe beim Erstellen? <a href="#" id="opener_tut_mz_anlegen" style="color: #FFD700;">Hier geht's zur Erklärung</a>!
										        
										        <div id="tut_mz_anlegen" class="modal_fix" title="Tutorial: Ein Zeitnehmer-Benutzerkonto erstellen" style="color: #FFFFFF; background: transparent; background-color: #A09A8E;">
										            <p>
														<table width="100%" cellspacing="5px" style="border: 1px solid #FFFFFF;">
															<tr>
															    <td align="justify">
    															    <font style="text-decoration: none; border-bottom: 1px dotted #fff; font-size: 17px;">Sie können für jeden Zeitnehmer <strong>bis zu 5 Positionen pro Prüfung</strong> vergeben.</font>
    															    <br /><br />
															        Nehmen wir einmal an, Sie sehen auf dem Zeitnahmeeinsatzplan, dass der Zeitnehmer <font color="#FFD700"><strong>Max Mustermann</strong></font> für die <font color="#FFD700"><strong>GP1</strong></font> auf <font color="#FFD700"><strong>Start</strong></font>, <font color="#FFD700"><strong>Zwischen-Zeitnahme 1</strong></font> und <font color="#FFD700"><strong>Ziel</strong></font> eingesetzt ist. Entsprechend des Einsatzplans nehmen Sie also folgende Einstellungen für diesen Zeitnehmer vor:
															    </td>
															</tr>
															<tr>
															    <td align="justify">&nbsp;</td>
															</tr>
															<tr>
															    <td align="justify"><hr class="white-hr" /></td>
															</tr>
															<tr>
															    <td align="center"><img src="images/tutorial_zeitnehmer_konto_2.jpg"></img></td>
															</tr>
															<tr>
															    <td align="justify"><hr class="white-hr" /></td>
															</tr>
														    <tr>
															    <td align="justify">&nbsp;</td>
															</tr>
															<tr>
															    <td align="justify">Sie müssen nicht zwingend alle Positionenfelder ausfüllen. Lediglich diese, die für Ihren Einsatzplan zutreffend sind. 
															    <br /><br />
															    Sobald Sie alle Angaben getätigt haben und fertig sind, klicken Sie auf speichern.</td>
															</tr>
														</table>
													</p>
													<p>
													    <br />  
													</p>
												    <p>
														<table width="100%" cellspacing="5px" style="border: 1px solid #FFFFFF;">
															<tr>
															    <td align="center">Ende des Tutorials. Sie können das Fenster rechts oben über <img src="images/close_modal.png"></img> schließen!</td>
															</tr>
														</table>
													</p>
													<p>
													    <br />  
													</p>
									        	</div>
										    </td>   
										</tr>
											<td>&nbsp;</td>
										</tr>
									    <tr>
							                 <td align="left">Bezeichnung</td>
							                 <td align="right">
							                     <input name="bezeichnung" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" />
							                 </td>
									    </td>
										<tr>
										    <td>&nbsp;</td>
										</tr>
										<tr>
											<td align="left">Prüfung<font color="#8E6516">*</font></td>
											<td align="right">
												<select name="mz_id[]" class="rid_1 option" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" required='required'>
													<?
														$select_option	= "SELECT * FROM _main_wptable WHERE `eid` = '".$eid."' ORDER BY rid ASC";
														$result_option	= mysqli_query($mysqli, $select_option);
														$anzahl_option	= mysqli_num_rows($result_option);
														
														if($anzahl_option > 0) {
														    echo "<option value=''>Prüfung wählen</option>";
    														while($row = mysqli_fetch_assoc($result_option)) {
																if($row["z_entry"] == 1) {
																	$what = " &mdash; Sprint";
																} else {
																	$what = "";
																}
																
    														    /*
																if($row["rid_attr"] > 0) {
    														        $what = " &mdash; Rundkurs";
    														    } elseif($row["rid_attr"] == 0) {
    														        $what = " &mdash; Sprint";
    														    }
																*/
    														    echo "<option value='" . $row["rid_type"] . $row["rid"] . "'>" . $row["rid_type"] . $row["rid"] . $what . "</option>";
    														}
														} elseif($anzahl_option == 0) {
														    echo "<option value=''>Keine Prüfungen</option>";
														}
													?>
												</select>
											</td>
										</tr>
										<tr>
											<td align="left">1. Position<font color="#8E6516">*</font></td>
											<td align="right">
												<select name="mz_id[]" class="rid_pos_1_1 listen_on_sprint" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" required='required'>
													<option value="">Prüfung wählen</option>
												</select>
											</td>
										</tr>
										<tr>
											<td align="left">2. Position<font color="#8E6516">*</font></td>
											<td align="right">
												<select name="mz_id[]" class="rid_pos_1_2 listen_on_sprint" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;">
													<option value="">Prüfung wählen</option>
												</select>
											</td>
										</tr>
										<tr>
											<td align="left">3. Position<font color="#8E6516">*</font></td>
											<td align="right">
												<select name="mz_id[]" class="rid_pos_1_3 listen_on_sprint" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;">
													<option value="">Prüfung wählen</option>
												</select>
											</td>
										</tr>
										<tr>
											<td align="left">4. Position<font color="#8E6516">*</font></td>
											<td align="right">
												<select name="mz_id[]" class="rid_pos_1_4 listen_on_sprint" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;">
													<option value="">Prüfung wählen</option>
												</select>
											</td>
										</tr>
										<tr>
											<td align="left">5. Position<font color="#8E6516">*</font></td>
											<td align="right">
												<select name="mz_id[]" class="rid_pos_1_5 listen_on_sprint" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;">
													<option value="">Prüfung wählen</option>
												</select>
											</td>
										</tr>
									</table>
								</div>
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
									<td align="right"><input type="submit" name="z_save" value="Speichern" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" /></td>
								</tr>
							</table>
							<!--
							<div id="slider">
								<div id="custom-handle" class="ui-slider-handle"></div>
							</div>
							-->
							<table width="385px" cellspacing="5px" style="border: 0;">
								<tr>
									<th colspan="2">&nbsp;</th>
								</tr>
							</table>
						</form>
						
						<?
							// DISPLAY ALL ERRORS
							error_reporting(E_ALL);
							
							if(isset($_POST['z_save'])) {
								// UNSET SUBMIT
								unset($_POST['z_save']);
								
								/*
								echo "Anzahl POST: " . count($_POST['mz_id']); 
								echo "<br /><br />";
								echo "<pre>";
								print_r($_POST['mz_id']);
								echo "</pre>";
								//exit;
								*/
								
								// ESTABLISH CONNECTION TO _OPTIO_ZMEMBERS
								// SELECT QUERY
								$select_zmem = "SELECT `id`, `eid` FROM `_optio_zmembers` WHERE `eid` = '" . $eid . "'";
								// QUERY AND FETCH RESULT
								$result_zmem = mysqli_query($mysqli, $select_zmem);
								$fetchr_zmem = mysqli_fetch_assoc($result_zmem);
								$numrow_zmem = mysqli_num_rows($result_zmem);
								
								// ESTABLISH CONNECTION TO _OPTIO_ZPOSITIONS
								// SELECT QUERY
								$select_zpos = "SELECT `id`, `zid`, `pos` FROM `_optio_zpositions` WHERE `zid` = '" . $fetchr_zmem['id'] . "'";
								// QUERY AND FETCH RESULT
								$result_zpos = mysqli_query($mysqli, $select_zpos);
								$fetchr_zpos = mysqli_fetch_assoc($result_zpos);
								
								// SET EVENT ID
								$eid  		= mysqli_real_escape_string($mysqli, utf8_encode($eid));
								
								//  Prüfe auf Bezeichnung
								if(isset($_POST['bezeichnung']) AND $_POST['bezeichnung'] != "") {
								    $bezeichnung = mysqli_real_escape_string($mysqli, $_POST['bezeichnung']);
								} else {
								    $bezeichnung = "ZN" . ($numrow_zmem + 1);
								}
								
								// LOOP THROUGH POST ARRAY
								for($i = 0; $i < count($_POST['mz_id']); $i++) {
									// CHECK IF EMPTY
									if($_POST['mz_id'][$i] != "" OR !empty($_POST['mz_id'][$i])) {
										// CHECK FOR VALUE TO BE GP, SP OR WP
										// SANITIZE AND EXPLODE TO rid_type (GP, SP, WP) AND rid (1, 2, n-1)
										if(strpos($_POST['mz_id'][$i], "WP") !== false) {
											$split = explode("WP", $_POST['mz_id'][$i]);
											$mz_id_type = $split[0];
											$mz_id_round = $split[1];
											$needle = "WP";
										} elseif(strpos($_POST['mz_id'][$i], "SP") !== false) {
											$split = explode("SP", $_POST['mz_id'][$i]);
											$mz_id_type = $split[0];
											$mz_id_round = $split[1];
											$needle = "SP";
										} elseif(strpos($_POST['mz_id'][$i], "GP") !== false) {
											$split = explode("GP", $_POST['mz_id'][$i]);
											$mz_id_type = $split[0];
											$mz_id_round = $split[1];
											$needle = "GP";
										}
										
										// IF ROUND ID FOUND
										if(strpos($_POST['mz_id'][$i], $needle) !== false) {
											//	Suche nach zugewiesener Prüfung, um Veranstaltungstag zu holen
											$select_eventdate = "SELECT * FROM `_main_wptable` WHERE `eid` = '" . $eid . "' AND `rid` = '" . $mz_id_round . "'";
											$result_eventdate = mysqli_query($mysqli, $select_eventdate);
											$numrow_eventdate = mysqli_num_rows($result_eventdate);
											
											//	Wenn Prüfung gefunden, trage Veranstaltungstag ein
											if($numrow_eventdate > 0) {
												$getrow_eventdate = mysqli_fetch_assoc($result_eventdate);
												$eventdate = $getrow_eventdate['execute'];
												
												// CREATE USERNAME
												$uname = rand(100, 999) . rand(100, 999);
														
												// CREATE PASSWORD
												$upass = rand(18273645, 51486237);
												
												// SEARCH FOR USERS WITH GENERATED CREDENTIALS TO AVOID DUPLICATES
												$select_dupe = "SELECT `uname`, `upass` FROM `_optio_zmembers` WHERE `uname` = '" . $uname . "' AND `upass` = '" . $upass . "'";
												$result_dupe = mysqli_query($mysqli, $select_dupe);
												$numrow_dupe = mysqli_num_rows($result_dupe);
												
												// CHECK WHETHER USER ALREADY EXISTS OR NOT
												if($numrow_dupe > 0) {
													// IF SO, THEN GENERATE NEW CREDENTIALS
													// CREATE USERNAME
													$uname = rand(100, 999) . rand(100, 999);
															
													// CREATE PASSWORD
													$upass = rand(18273645, 51486237);
												}
																
												// BUILD INSERT QUERY
												$query	= 	"INSERT INTO
																`_optio_zmembers`(
																	`id`,
																	`eid`,
																	`rid_type`,
																	`rid`,
																	`uname`,
																	`upass`,
																	`opt_whois`,
																	`active`,
																	`neutralized`,
																	`logintime`,
																	`eventdate`,
																	`ipv4`,
																	`ipv6`
																)
															VALUES(
																NULL,
																'" . $eid . "',
																'" . $needle . "',
																'" . $mz_id_round . "', 
																'" . $uname . "',
																'" . $upass . "',
																'" . $bezeichnung . "',
																'0',
																'0',
																'0',
																'" . $eventdate . "',
																'',
																''
															)";			 
												// EXCECUTE QUERY
												mysqli_query($mysqli, $query);
												
												// FETCH LAST INSERTED ID
												$prev_id = mysqli_insert_id($mysqli);
												
												// DEBUG
												// echo "<font size='2' color='#FFD700'>" . $query . "</font><br />";
											//	Prüfung nicht gefunden (gelöscht?)
											} else {
												$state      = 'Fehler:';
												$error_msg  = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Gewählte Prüfung nicht gefunden!</span><br />';
											}
											// ELSE POSITIONS FOUND
										} else {
											// GET POSITION
											$mz_pos = mysqli_real_escape_string($mysqli, utf8_encode($_POST['mz_id'][$i]));
											
											// BUILD INSERT QUERY
											$insert_zmem = "INSERT INTO 
																`_optio_zpositions`(
																	`id`, 
																	`zid`,
																	`rid`,																	
																	`pos`
																) 
															VALUES(
																NULL, 
																'" . $prev_id . "',
																'" . $mz_id_round . "',
																'" . $mz_pos . "'
															)";											
											// EXECUTE INSERTION
											mysqli_query($mysqli, $insert_zmem);
											
											// DEBUG
											// echo "<font size='2' color='#C0C0C0'>" . $insert_zmem . "</font><br />";
										}							
									// IS EMPTY OR HAS NO VALUE
									} else {
										// CONTINUE LOOP
										continue;
									}
								}
								
								// PREPARE WPTABLE_COUNT
								$count = count($_POST['mz_id']) / 6;
								$update_race_run_count_wptable = "UPDATE _race_run_events SET `count_zmembers` = `count_zmembers` + '" . $count . "' WHERE `eid` = '" . $eid . "' AND `active` = '1'";
											
								// EXCECUTE UPDATE COUNT
								mysqli_query($mysqli, $update_race_run_count_wptable);									
										
								// SET REDIRECT
								$redir = '<meta http-equiv="refresh" content="3; url=/msdn/_addmz.php">';
									
								// IF QUERY SUCCESSFUL
								if($query == true) {
									$state      = 'Erfolgreich!';
									$error_msg  = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Zeitnehmer-Benutzerkonto angelegt!</span><br />';
								} else {
									$state      = 'Fehler:';
									$error_msg  = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Zeitnehmer-Daten konnten nicht angelegt werden!</span><br />';
								}
							}
						?>
						
    					<?php
    						if(!empty($error_msg)) {
    							echo '<p class="error">';
    								echo '<span style="margin-left: 5px; border-bottom: 1px dotted #8E6516;"><font color="#FFD700">' . $state . '</font></span><br />';
    								echo $error_msg;
    							echo '</p>';
    						}							
							
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