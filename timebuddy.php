<?php
	// SET ERROR REPORTING LEVEL
	error_reporting(E_ALL);
	
	// SET TIMEZONE
	date_default_timezone_set("Europe/Berlin");
	
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
	
	// FIRST CHECK FOR ANY LIVE ERROR
	if(isset($_GET['error'])) {
		// REWRITE ERROR
		$error = mysqli_real_escape_string($mysqli, $_GET['error']);
		switch($error) {
			case "0x1001":
				echo	'
						<script>
							$(document).ready(function(){
								$("#dialog-confirm").dialog({
									autoOpen: true,
									resizable: false,
									height: "auto",
									width: 400,
									modal: true,
									buttons: {
										"Verstanden": function(){
											$(this).dialog("close");
										}
									}
								}).bind("clickoutside", function(e) {
									$target = $(e.target);
									if (!$target.filter(".hint").length
											&& !$target.filter(".hintclickicon").length) {
										$field_hint.dialog("close");
									}
								});
								
								$("#results").submit(function(){
									$("#dialog-confirm").modal("show");
								});
							});
						</script>
						<div id="dialog-confirm" title="Fehlerhafter Zugang">
							<p align="justify">
								<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
								Für diesen Zeitnehmer-Zugang wurden keine Prüfung hinterlegt. Bitte wenden Sie sich an Ihren Auswerter / Veranstalter!<br /><br /><p style="text-align: center;">Sie werden weitergeleitet ... <img src="images/ripple12px-fts2.gif" /></p>
							</p>
						</div>
						';
			break;
		}
	}
	
	// START SESSION
	session_start();
	
	// CHECK LOGGED IN USER AND VALIDATE INFORMATION
	if(isset($_SESSION['user_id']) AND $_SESSION['user_id'] != "") {
		// SET RESULT STORE AS ARRAY IF NOT SET
		if(!isset($_SESSION['holder'])) {
			$_SESSION['holder'] = array();
		}
		
		// SET RESULT HOLDER AS ARRAY IF NOT SET
		if(!isset($persistence)) {
			$persistence = array();
		}
		
		// CREATE MISC VARIABLES
		$error = "";
		$disabled = "";
		
		// RETURN SESSION TO LOCAL VARIABLES
		$uid	= $_SESSION['user_id'];
		$rid_type	= $_SESSION['rid_type'];
		$rid		= $_SESSION['rid'];
		$username	= $_SESSION['username'];
		$opt_whois	= $_SESSION['opt_whois'];
		$logtype    = $_SESSION['logtype'];
		
		switch($logtype) {
		    //  Zeitkontrolle
		    case "zk":
		        header("Location: zcontrol.php");
	        break;
	        //  Stempelkontrolle
	        case "zs":
	            header("Location: zstamp.php");
            break;
            //  Bordkartenkontrolle
            case "bc":
                header("Location: boarding.php");
            break;
		}
		
		// FETCH EVENT ID
		$select_event = "SELECT * FROM `_optio_zmembers` WHERE `id` = '" . $uid . "'";
		$result_event = mysqli_query($mysqli, $select_event);
		$getrow_event = mysqli_fetch_assoc($result_event);
		$zid = $getrow_event['id'];
		$zdesc	= $getrow_event['rid_type'];
		$zmid	= $getrow_event['rid'];		
		
		// DECLARE EVENT ID
		$eid = $getrow_event['eid'];
		
		// SEARCH FOR ZTYPE (REGULAR OR SPRINT?)
		$select_ztype = "SELECT * FROM `_optio_zpositions` WHERE `zid` = '" . $zid . "'";
		$result_ztype = mysqli_query($mysqli, $select_ztype);
		$numrow_ztype = mysqli_num_rows($result_ztype);
		
		// MAKE SURE ZMEMBER GOT ROUND
		if($numrow_ztype > 0) {
			$getrow_ztype = mysqli_fetch_assoc($result_ztype);
			$ztype	= $getrow_ztype['pos'];
			$zrdid	= $getrow_ztype['rid'];			
			
			// CHECK FOR ALLOCATED ROUND (ID AND DESC)
			if($zdesc == "" OR $zmid == "" OR $zrdid == "") {
				$error =	'
							<script>
								$(document).ready(function(){
									$("#dialog-confirm").dialog({
										autoOpen: true,
										resizable: false,
										height: "auto",
										width: 400,
										modal: true,
										buttons: {
											"Verstanden": function(){
												$(this).dialog("close");
											}
										}
									}).bind("clickoutside", function(e) {
										$target = $(e.target);
										if (!$target.filter(".hint").length
												&& !$target.filter(".hintclickicon").length) {
											$field_hint.dialog("close");
										}
									});
									
									$("#results").submit(function(){
										$("#dialog-confirm").modal("show");
									});
								});
							</script>
							<div id="dialog-confirm" title="Fehlerhafter Zugang">
								<p align="justify">
									<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
									Für diesen Zeitnehmer-Zugang wurden keine Prüfung hinterlegt. Bitte wenden Sie sich an Ihren Auswerter / Veranstalter!<br /><br /><p style="text-align: center;">Sie werden weitergeleitet ... <img src="images/ripple12px-fts2.gif" /></p>
								</p>
							</div>
							';
				echo 	'<meta http-equiv="refresh" content="10; url=/msdn/timebuddy_fail.php?error=0x2000">';
				$ztype = "";
				
				// CREATE DISABLE VARIABLE TO MAKE SURE, NO INPUT IS POSSIBLE
				$disabled = "disabled = 'disabled'";
			} else {
				// INCLUDE RELEVANT LIBS
				include("lib/library_int_timebuddy2.html");
			}			
		// ZMEMBER HAS NO ROUND --> SAVE IN ERROR VARIABLE AND REDIRECT TO LOGIN FAIL
		} elseif($numrow_ztype == 0) {
			$error =	'
						<script>
							$(document).ready(function(){
								$("#dialog-confirm").dialog({
									autoOpen: true,
									resizable: false,
									height: "auto",
									width: 400,
									modal: true,
									buttons: {
										"Verstanden": function(){
											$(this).dialog("close");
										}
									}
								}).bind("clickoutside", function(e) {
									$target = $(e.target);
									if (!$target.filter(".hint").length
											&& !$target.filter(".hintclickicon").length) {
										$field_hint.dialog("close");
									}
								});
								
								$("#results").submit(function(){
									$("#dialog-confirm").modal("show");
								});
							});
						</script>
						<div id="dialog-confirm" title="Fehlerhafter Zugang">
							<p align="justify">
								<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
								Für diesen Zeitnehmer-Zugang wurden keine Positionen hinterlegt. Bitte wenden Sie sich an Ihren Auswerter / Veranstalter!<br /><br /><p style="text-align: center;">Sie werden weitergeleitet ... <img src="images/ripple12px-fts2.gif" /></p>
							</p>
						</div>
						';
			echo 	'<meta http-equiv="refresh" content="10; url=/msdn/timebuddy_fail.php?error=0x2001">';
			$ztype = "";
			
			// CREATE DISABLE VARIABLE TO MAKE SURE, NO INPUT IS POSSIBLE
			$disabled = "disabled = 'disabled'";
		}
		
		// CHANGE INPUT MASK FOR RESULT BASED ON ZTYPE
		if($ztype != "" OR !empty($ztype)) {
			if($ztype == "Sprint") {
				$result_mask = '$("#t_start").mask("99:99,99",{placeholder:"MM:SS,00"});';
				$inp_pattern = '([0-5][0-9]:[0-5][0-9],[0-9][0-9]){1}';
				$result_plho = 'MM:SS,00';
				$result_form = 8;
			} elseif($ztype != "Sprint") {
				$result_mask = '$("#t_start").mask("99:99:99,99",{placeholder:"HH:MM:SS,00"});';
				$inp_pattern = '(([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9],[0-9][0-9]){1}';
				$result_plho = 'HH:MM:SS,00';
				$result_form = 11;
			}
		}
		
		// FETCH POSITION STACK (TOTAL AMOUNT OF POSITIONS FOR LOGGED IN ZMEMBER)
		$select_zpositions = "SELECT `id`, `eid`, `rid` FROM `_optio_zmembers` WHERE `id` = '" . $uid . "' AND `eid` = '" . $eid . "' AND `rid` = '" . $rid . "'";
		
		$result_zpositions = mysqli_query($mysqli, $select_zpositions);
		$getrow_zpositions = mysqli_fetch_assoc($result_zpositions);
		$zid = $getrow_zpositions['id'];
		
		$zpositions_container = array();
		
		$select_zpos_stack = "SELECT `id`, `zid`, `rid`, `pos` FROM `_optio_zpositions` WHERE `zid` = '" . $zid . "' AND `rid` = '" . $rid . "'";
		
		$result_zpos_stack = mysqli_query($mysqli, $select_zpos_stack);
		while($getrow_zpos_stack = mysqli_fetch_assoc($result_zpos_stack)){
			$zpositions_container[] = $getrow_zpos_stack['pos'];
		}
		
		//	Bereite Positions Container für Übergabe per AJAX vor
		$zpositions = json_encode($zpositions_container);
		
		// OUTPUT NAVBAR AND LOGIN / LOGOUT PANEL
		$logged = file_get_contents("essentials/opt_logout_ft.html");
		$navbar = file_get_contents("essentials/mz_navbar_logged_in.html");
		include("essentials/chat_slider.php");
		
		// FORM HAS BEEN SENT
		// FIRST CHECK IF SUBMITTED TPOS IS ALREADY AVAILABLE
		// IF SO, THEN PROCEED WITH UPDATE FOR THIS TMEMBER AND TPOS
		// IF TPOS VALUE IS "NEW", THEN PROCEED WITH INSERT FOR TMEMBER
		if(	isset($_POST['results']) 		AND 
			isset($_POST['sid']) 		AND
			isset($_POST['t_start'])		AND
			isset($_POST['t_pos'])			AND
			!empty($_POST['sid']) 		AND 
			!empty($_POST['t_start'])		AND 
			!empty($_POST['t_pos'])
		) {	
			// SANITIZE FORM VALUES
			$sid	= mysqli_real_escape_string($mysqli, $_POST['sid']);
			$t_start	= mysqli_real_escape_string($mysqli, $_POST['t_start']);
			$position		= mysqli_real_escape_string($mysqli, $_POST['t_pos']);
		
			//	Prüfe, ob Teilnehmer existiert
			$select_tmember = "SELECT * FROM `_optio_tmembers` WHERE `eid` = '" . $eid . "' AND `sid` = '" . $sid . "'";
			$result_tmember = mysqli_query($mysqli, $select_tmember);
			$numrow_tmember = mysqli_num_rows($result_tmember);
			
			//	Teilnehmer existiert
			if($numrow_tmember == 1) {
				// CHECK IF LENGTH OF RESULT EQUALS PERMITTED LENGTH
				if(strlen($_POST['t_start']) == $result_form) {	
					// CHECK FORMAT LENGTH
					if(strlen($_POST['t_start']) == 11) {
						// GET TIME PARTS (HH:MM:SS,UU)
						$time = explode(":", $t_start);
						$t_start_hh = $time[0];
						$t_start_mm = $time[1];
						$t_start_ss = $time[2];
						
						// GET CENTISECONDS AND SET SECONDS
						$t_centi = explode(",", $t_start_ss);
						$t_start_ss = $t_centi[0];
						$t_start_ms = $t_centi[1];
					} elseif(strlen($_POST['t_start']) == 8) {
						// GET TIME PARTS (MM:SS,UU)
						$time = explode(":", $t_start);
						$t_start_hh = "00";
						$t_start_mm = $time[0];
						$t_start_ss = $time[1];
						
						// GET CENTISECONDS AND SET SECONDS
						$t_centi = explode(",", $t_start_ss);
						$t_start_ss = $t_centi[0];
						$t_start_ms = $t_centi[1];
					// NOTHING MATCHES --> EXIT AND REDIRECT WITH HINT
					} else {
						exit();
						echo 	'<meta http-equiv="refresh" content="0; url=/msdn/timebuddy.php?error=0x1001">';
					}
						
					// CREATE SECONDS FROM INPUT TO SAVE FOR DB INSTEAD OF STRTOTIME
					$seconds = intval(($t_start_mm * 60) + $t_start_ss);
						
					// GET SINGLE, UNTOUCHED CENTISECONDS
					$ergHundertstel = $t_start_ms;
					
					// MERGE TIME TO CONVERT TO TIMESTAMP (USING : FOR CENTISECONDS TO MAKE SPLIT EASIER, WHEN CALCULATING RESULTS)
					/*	
						THE VERSION BENEATH GIVES EXACT RESULT AS NEW ONE, EXCEPT THAT IT WON'T ACCOUNT THE CURRENT DATE IF RESULT
						GETS ENTERED AFTER DAYS, ETC. => E. G. RESULT ENTERED 1 DAY AFTER EVENT == CALCULATED ~45:MM:SS,UU
						THE NEW VERSION TAKES THE REGISTERED DATE FOR THIS ROUND INTO ACCOUNT, SO THE RESULT WILL ALWAYS BE
						FROM THE "CURRENT" DATE
					*/
					// $time_merged = $t_start_hh . ":" . $t_start_mm . ":" . $t_start_ss . "." . $t_start_ms;
					
					// FETCH REGISTERED DATE FOR THIS ROUND
					$select_registered_date = "SELECT `eid`, `rid_type`, `rid`, `execute` FROM `_main_wptable` WHERE `eid` = '" .  $eid. "' AND `rid` = '" . $rid . "'";
					$result_registered_date = mysqli_query($mysqli, $select_registered_date);
					$getrow_registered_date = mysqli_fetch_assoc($result_registered_date);
					
					$registered_date = $getrow_registered_date['execute'];
					
					$time_merged = $registered_date . " " . $t_start_hh . ":" . $t_start_mm . ":" . $t_start_ss . "." . $t_start_ms;
					
					// CONVERT FOR SPECIFIC ZTYPE
					if($ztype != "Sprint") {
						// CREATE TIMESTAMP
						$ergSekunden = strtotime($time_merged);
					} elseif($ztype == "Sprint") {
						$ergSekunden = $seconds;
					}
					
					// REWRITE MERGED TIME BECAUSE OF STORING AS DECIMAL
					$time_merged = str_replace('.', ',', $time_merged);
					
					$time_merged = explode(' ', $time_merged);
					$ergString = $time_merged[1];
					
					// CHECK FOR TPOS
					/*	NUMROW[START] == 1 => UPDATE
						NUMROW[START] == 0 => INSERT
					*/
					$select_whether = "SELECT * FROM `_main_wpresults` WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $sid . "' AND `position` = '" . $position . "'";
					$result_whether = mysqli_query($mysqli, $select_whether);
					$numrow_whether = mysqli_num_rows($result_whether);
					
					// CHECK FOR RESULT; IF NOT, THEN INSERT
					if($numrow_whether == 0) {
						// INSERT AS NEW RESULT
						$insert_whether = 	"INSERT INTO 
												`_main_wpresults`(
													`id`,
													`eid`,
													`rid`,
													`zid`,
													`sid`,
													`position`,
													`ergebnis_sekunden`,
													`ergebnis_hundertstel`,
													`ergebnis_string`,
													`duplicate`
												)
												VALUES(
													NULL,
													'" . $eid . "',
													'" . $rid . "',
													'" . $uid . "',
													'" . $sid . "',
													'" . $position . "',
													'" . $ergSekunden . "',
													'" . $ergHundertstel . "',
													'" . $ergString . "',
													'0'
												)
											";
						mysqli_query($mysqli, $insert_whether);
						
						// CHECK IF QUERY WAS SUCCESSFUL AND ECHO MESSAGE
						if(mysqli_affected_rows($mysqli) == 1) {
							$state		= 'Erfolgreich!';
							$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Zeit für Teilnehmernummer ' . $sid . ' wurde gespeichert!</span><br />';
						}
						
						//	Registriere Logeintrag
						$insert_log =	"
										INSERT INTO 
											`_optio_zmembers_log`(
												`id`,
												`zid`,
												`eid`,
												`rid`,
												`logtime`,
												`action`
											)
										VALUES(
											'NULL',
											'" . $uid . "',
											'" . $eid . "',
											'" . $rid . "',
											'" . time() . "',
											'Eintrag: #" . $sid . " | " . $position . "-Ergebnis mit " . $ergString . "'
										)";
						$result_log = mysqli_query($mysqli, $insert_log);
					// UPDATE EXISTING ROW
					} elseif($numrow_whether == 1) {
						$getrow_whether = mysqli_fetch_assoc($result_whether);
						$id = $getrow_whether['id'];
						$update_whether = 	"UPDATE
												`_main_wpresults`
											SET
												`id`			= '" . $id . "',
												`eid`		= '" . $eid . "',
												`rid`		= '" . $rid . "',
												`zid`			= '" . $uid . "',
												`sid`	= '" . $sid . "',
												`position`			= '" . $position . "',
												`ergebnis_sekunden`		= '" . $ergSekunden . "',
												`ergebnis_hundertstel`		= '" . $ergHundertstel . "',
												`ergebnis_string`	= '" . $ergString . "',
												`duplicate`	= '0'
											WHERE 
												`id`				= '" . $id . "'
											";
						mysqli_query($mysqli, $update_whether);
						
						// CHECK IF QUERY WAS SUCCESSFUL AND ECHO MESSAGE
						if(mysqli_affected_rows($mysqli) == 1) {
							$state		= 'Erfolgreich!';
							$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Zeit für Teilnehmernummer ' . $sid . ' wurde überschrieben!</span><br />';
						}
						
						//	Registriere Logeintrag
						$insert_log =	"
										INSERT INTO 
											`_optio_zmembers_log`(
												`id`,
												`zid`,
												`eid`,
												`rid`,
												`logtime`,
												`action`
											)
										VALUES(
											'NULL',
											'" . $uid . "',
											'" . $eid . "',
											'" . $rid . "',
											'" . time() . "',
											'Korrektur: #" . $sid . " | " . $position . "-Ergebnis mit " . $ergString . "'
										)";
						$result_log = mysqli_query($mysqli, $insert_log);
					// MORE THAN ONE IDENTICAL ROW FOUND
					} elseif($numrow_whether > 1) {
						// GET REFERENCE ID FOR ERROR MESSAGE
						$id = "";
						while($getrow_whether = mysqli_fetch_assoc($result_whether)) {
							$id .= $getrow_whether['id'] . ".";
						}	

						// DELETE LAST CHAR
						$id = substr($id, 0, -1);
						
						// DUPLICATE ENTRY, DISPLAY ERROR MESSAGE
						if(mysqli_affected_rows($mysqli) == 1) {
							$state		= 'Fehler!';
							$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>' . $numrow_whether . ' Zeiten für Teilnehmernummer ' . $sid . ' gefunden!</span><br />';
						}
						
						$error =	'
								<script>
									$(document).ready(function(){
										$("#dialog-confirm").dialog({
											autoOpen: true,
											resizable: false,
											height: "auto",
											width: 400,
											modal: true,
											buttons: {
												"Verstanden": function(){
													$(this).dialog("close");
												}
											}
										});
										
										$("#results").submit(function(){
											$("#dialog-confirm").modal("show");
										});
									});
								</script>
								<div id="dialog-confirm" title="Fehlerhafter Datensatz">
									<p align="justify">
										<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
										Es wurden mehrere identische Ergebnispositionen für diesen Teilnehmer entdeckt. Bitte wenden Sie sich an Ihren Auswerter / Veranstalter!<br /><br /><p style="text-align: center;">Sie werden weitergeleitet ... <img src="images/ripple12px-fts2.gif" /></p>
									</p>
								</div>
								';
					echo 	'<meta http-equiv="refresh" content="10; url=/msdn/timebuddy_fail.php?error=0x2002&id=' . $id . '">';
					$ztype = "";
					
					// CREATE DISABLE VARIABLE TO MAKE SURE, NO INPUT IS POSSIBLE
					$disabled = "disabled = 'disabled'";
					}
				} else {
					// CREATE ERROR MESSAGE
					$state		= 'Hinweis!';
					$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Korrektes Format beachten! </span><br />';
				}
			} elseif($numrow_tmember > 1) {	
				//	Teilnehmer existiert nicht!
				$state		= 'Fehler:';
				$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Teilnehmernummer ' . $sid . ' existiert mehrfach!</span><br />';
			} else {
				//	Teilnehmer existiert nicht!
				$state		= 'Fehler:';
				$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Teilnehmernummer ' . $sid . ' existiert nicht!</span><br />';
			}
		} elseif(	
			isset($_POST['results']) AND 
			(
				($_POST['sid'] == "" OR $_POST['t_start'] == "") OR 
				($_POST['sid'] == "" AND $_POST['t_start'] == "")
			)
		) {
			// CREATE ERROR MESSAGE
			$state		= 'Hinweis!';
			$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Alle Felder sind korrekt auszufüllen! </span><br />';
		}
	} elseif(isset($_SESSION['user_id']) AND $_SESSION['user_id'] != "" AND !isset($_SESSION['sid'])) {
		header('Location: /msdn/timebuddy.php');
	} elseif(isset($_SESSION['user_id']) AND $_SESSION['user_id'] != "" AND isset($_SESSION['sid'])) {
		header('Location: /msdn/racer.php');
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
				
				
		<!--	INCLUDING LIBS	-->
		<?php 
			include("lib/library.html");
			include("lib/library_int_timebuddy.html");
			
			// ECHO POSSIBLE ERRORS
			echo $error;
		?>
		
		<script>
			$(document).ready(function() {
				// INITIALLY BLOCK SUBMIT BUTTON
				$('#results').prop("disabled", true);
				
				// CHECK FOR CORRECT TIME PATTERN
				$("#t_start").keyup(function() {
					// GET VALUE
					var result_input = this.value;

					var time_format = new RegExp('^<? echo $inp_pattern; ?>$');

					// TEST IF CORRECT FORMAT
					if(time_format.test(result_input)) {
						$('#results').prop("disabled", false);
					} else {
						$('#results').prop("disabled", true);
					}
				});
				
				// INPUT MASK FOR RESULTS
				$(function($){
					<?
						echo $result_mask;
					?>
				});
				
				// INITIALLY DISABLE SELECT FIELD
				$('#t_pos').prop('disabled', true);
				
				// CHECK IF INPUT IS SELECT FIELD AND NOT INPUT
				if($('#t_pos').is("select")) {
					// PRE-SELECT TPOS BASED ON TMEMBER
					$('#t_select').change(function() {
						// DECLARE TMEMBER ID VARIABLE
						var sid = $('#t_select').val();
						
						// DISABLE EVERY OPTION FROM PREVIOUS INPUT
						$("#t_pos").val('none').prop("selected", true);
						$('#t_pos').val('none').find(':selected').nextAll().prop("disabled", true);
						
						// CHECK WHETHER INPUT IS NUMERIC OR NOT
						if(sid.length == 0 || sid == 0 || !$.isNumeric(sid)) {
							$('#t_pos').prop("disabled", true);
							$("#t_pos").val('none').prop("selected", true);
							$('#t_pos').val('none').find(':selected').nextAll().prop("disabled", true);
						// INPUT IS NUMERIC AND VALID --> PROCEED
						} else {						
							// DECLARE EVENT ID AND ROUND ID BASED ON PHP VARIABLES
							var eid = <? echo $eid; ?>;
							var zid = <? echo $zid; ?>;
							var rid = <? echo $rid; ?>;
							var pos = <? echo $zpositions; ?>;
								
							// AJAX REQUEST
							$.ajax({
								type: 'POST',
								url: 'timebuddy_fetch_tpos.php',
								data:	{
											rid: rid,
											eid: eid,
											zid: zid,
											sid: sid,
											pos: pos
										},
								success: function(data){
									if(data !== "incomplete") {
										if(data !== "no_result") {
											//	Aktiviere Eingabefeld
											$('#t_pos').prop("disabled", false);
											
											//	Verstecke Platzhalter Option
											$("#t_pos option[value='none']").hide();
											
											//	Aktiviere Auswahlfeld mit vorbelegtem Rückgabewert
											$("#t_pos option[value='" + data + "']").prop("disabled", false);
											$("#t_pos").val(data).prop("selected", true);
											$('#t_pos').val(data).find(':selected').prevAll().prop('disabled', false);
											$('#t_start').prop("disabled", false);
										} else if(data == "no_result") {
											// BLOCK SELECT AND TIME INPUT
											$('#t_pos').prop("disabled", true);
											$("#t_pos option[value='none']").text("Kein Teilnehmer");
											$('#t_start').prop("disabled", true);
										}
									} else {
										//	Übergabeparameter unvollständig
										//	Blocke alle Eingabemöglichkeiten
										$('#t_pos').prop("disabled", true);
										$("#t_pos option[value='none']").text("Kritischer Fehler");
										$('#t_start').prop("disabled", true);
									}
								}
							});
						}
					});
				}
				
				var eid = <?php echo $eid; ?>;
				var uid = <?php echo $uid; ?>;
				var rid = <?php echo $rid; ?>;
				
				var log = "";
				var href = "";
				
				setTimeout(function () {
					$.ajax({
						type: 'POST',
						url: 'mz_checklogin.php',
						data: 'eid=' + eid + '&uid=' + uid + '&rid=' + rid,
						success: 
							function(data) {
								if(data == "nopost") {
									alert("[E1] Es konnten keine POST Daten gesendet werden!");
									log =	'<div id="dialog-confirm" class="modal_fix" title="Parameterfehler">' + 
												'<p>' + 
													'<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>' + 
														'Bei der Übertragung der erforderlichen Parameter ist ein kritischer Fehler aufgetreten! Prüfung für Loginberechtigung ausgesetzt. Bitte Systembereiber kontaktieren!' + 
														'<br /><br />' + 
														'Seite wird neugeladen ... <img src="images/ripple12px-fts2.gif" />' + 
												'</p>' + 
											'</div>';
									href = window.location.href = "timebuddy.php";
								} else if(data == "multiuser") {
									alert("[E2] Zeitnehmer-Zugang mehrfach vorhanden!");
									log =	'<div id="dialog-confirm" class="modal_fix" title="Zeitnehmer-Zugang mehrfach vorhanden!' + 
												'<p>' + 
													'<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>' + 
														'Dieser Zeitnehmer-Zugang ist mehrfach vorhanden! Bitte wenden Sie sich an zuständigen den Auswerter' + 
														'<br /><br />' + 
														'Sie werden ausgeloggt ... <img src="images/ripple12px-fts2.gif" />' + 
												'</p>' + 
											'</div>';
									href = window.location.href = "includes/opt_logout_ft.php?error=0x2008";
								} else if(data == "nouser") {
									alert("[E3] Zeitnehmer-Zugang existiert nicht!");
									log =	'<div id="dialog-confirm" class="modal_fix" title="Zeitnehmer-Zugang existiert nicht (mehr)!">' + 
												'<p>' + 
													'<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>' + 
														'Dieser Zeitnehmer-Zugang ist nicht (mehr) existent! Bitte wenden Sie sich an zuständigen den Auswerter' + 
														'<br /><br />' + 
														'Sie werden ausgeloggt ... <img src="images/ripple12px-fts2.gif" />' + 
												'</p>' + 
											'</div>';
									href = window.location.href = "includes/opt_logout_ft.php?error=0x2005";
								} else if(data == "regular") {
									alert("[E4] <?php echo $rid_type . $rid; ?> neutralisiert");
									log =	'<div id="dialog-confirm" class="modal_fix" title="<?php echo $rid_type . $rid; ?> neutralisiert">' + 
												'<p>' + 
													'<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>' + 
														'Diese Prüfung wurde von Ihrem zuständigen Auswerter soeben neutralisiert' + 
														'<br /><br />' + 
														'Sie werden ausgeloggt ... <img src="images/ripple12px-fts2.gif" />' + 
												'</p>' + 
											'</div>';
									href = window.location.href = "includes/opt_logout_ft.php?error=0x2006";
								}
								
								if(data != "") {
									//	Hänge Dialog an Quellcode an
									$('#logged_state').html(log);
									
									$("#dialog-confirm").dialog({
										resizable: false,
										height: "auto",
										width: 400,
										modal: true,
										buttons: {
											"Verstanden": function() {
												href;
											}
										}
									});
									window.setTimeout(function() {
										href;
									}, 5000);
								}
							}
					});
				}, 2500);
			});	

			// AUTO-FETCH EVERY 30 SECONDS
			function checkLogin() {
				var eid = <?php echo $eid; ?>;
				var uid = <?php echo $uid; ?>;
				var rid = <?php echo $rid; ?>;
				
				var log = "";
				var href = "";
				
				setTimeout(function () {
					$.ajax({
						type: 'POST',
						url: 'mz_checklogin.php',
						data: 'eid=' + eid + '&uid=' + uid + '&rid=' + rid,
						success: 
							function(data) {
								if(data == "nopost") {
									alert("[E1] Es konnten keine POST Daten gesendet werden!");
									log =	'<div id="dialog-confirm" class="modal_fix" title="<?php echo $rid_type . $rid; ?> neutralisiert">' + 
												'<p>' + 
													'<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>' + 
														'Diese Prüfung wurde von Ihrem Auswerter soeben neutralisiert.' + 
														'<br /><br />' + 
														'Sie werden ausgeloggt ... <img src="images/ripple12px-fts2.gif" />' + 
												'</p>' + 
											'</div>';
									href = window.location.href = "includes/opt_logout_ft.php?error=0x2008";
								} else if(data == "multiuser") {
									alert("[E2] Zeitnehmer-Zugang mehrfach vorhanden!");
									log =	'<div id="dialog-confirm" class="modal_fix" title="Zeitnehmer-Zugang mehrfach vorhanden!' + 
												'<p>' + 
													'<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>' + 
														'Dieser Zeitnehmer-Zugang ist mehrfach vorhanden! Bitte wenden Sie sich an zuständigen den Auswerter' + 
														'<br /><br />' + 
														'Sie werden ausgeloggt ... <img src="images/ripple12px-fts2.gif" />' + 
												'</p>' + 
											'</div>';
									href = window.location.href = "includes/opt_logout_ft.php?error=0x2009";
								} else if(data == "nouser") {
									alert("[E3] Zeitnehmer-Zugang existiert nicht!");
									log =	'<div id="dialog-confirm" class="modal_fix" title="<?php echo $rid_type . $rid; ?> neutralisiert">' + 
												'<p>' + 
													'<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>' + 
														'Diese Prüfung wurde von Ihrem Auswerter soeben neutralisiert.' + 
														'<br /><br />' + 
														'Sie werden ausgeloggt ... <img src="images/ripple12px-fts2.gif" />' + 
												'</p>' + 
											'</div>';
									href = window.location.href = "includes/opt_logout_ft.php?error=0x2006";
								}
								
								if(data != "regular") {
									//	Hänge Dialog an Quellcode an
									$('#logged_state').html(log);
									
									$("#dialog-confirm").dialog({
										resizable: false,
										height: "auto",
										width: 400,
										modal: true,
										buttons: {
											"Verstanden": function() {
												href;
											}
										}
									});
									window.setTimeout(function() {
										href;
									}, 5000);
								}
							}
					});
				}, 2500);
			}
				
			// INTERVAL FOR FETCHING EVERY 30 SECONDS
			setInterval(function() {
				checkLogin();
			}, 60000);
		</script>
		
		<noscript>
			<div style="z-index: 5000; left: 0; position: fixed; text-align: center; width: 100%; height: 100%; background-color: rgba(48, 48, 48, 0.75);">
				<h2 style="line-height: 100%; padding-top: 25%; color: #fff;"><span style="border: 1px dotted #fff; padding: 25px 50px 25px 50px; background-color: rgba(255, 0, 0, 0.25)">Bitte aktivieren Sie JavaScript!</span></h2>
			</div>
		</noscript>
	</head>
	<body>
		<div id="container_tb">
			<div id="linkList_nobanner">			
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
				<!-- 	COLUMN 1	-->
				<div id="modul_1" align="center">
					<h3>Mein Event</h3>
					<p>	
						<a id="show_team_status" onclick="document.getElementById('spoiler_team_status').style.display=''; document.getElementById('show_team_status').style.display='none';" class="link">
						<table cellspacing="5px" cellpadding="5px" style="border: 1px solid #FFFFFF; font-size: small;" width="100%">
							<tr>
								<td onmouseover="this.style.color='#FFD700'" onmouseout="this.style.color='#FFFFFF'">Zeige Teamstatus</td>
								<td>&nbsp;</td>
							</tr>
						</table>
						</a>
						<span id="spoiler_team_status" style="display: none;">
						<a onclick="document.getElementById('spoiler_team_status').style.display='none'; document.getElementById('show_team_status').style.display='';" class="link">
							<table cellspacing="5px" cellpadding="5px" style="border-top: 1px solid #FFFFFF; border-left: 1px solid #FFFFFF; border-right: 1px solid #FFFFFF; border-bottom: 0; font-size: small;" width="100%">
								<tr>
									<td onmouseover="this.style.color='#FFFFFF'" onmouseout="this.style.color='#FFD700'">Verberge Teamstatus</td>
									<td>&nbsp;</td>
								</tr>
							</table>
						</a>
						<table cellspacing="5px" cellpadding="5px" style="border: 1px solid #FFFFFF; font-size: small;" width="100%" id="t_state"></table>
						</span>
					
						<form action="<? $_SERVER['PHP_SELF']; ?>" id="form" method="POST">
						<table cellspacing="5px" cellpadding="0" style="border: 1px solid #FFFFFF;" width="385px">
							<tr>	 
								<td align="left">
									Prüfungsnummer
								</td>
								<td align="right">
									<?											
										// QUERIES _OPTIO_TMEMBERS
										$select_optio_zm = "SELECT `id`, `eid`, `rid_type`, `rid` FROM `_optio_zmembers` WHERE `eid` = '" . $eid . "' AND `id` = '" . $uid . "' LIMIT 1";
										$result_optio_zm = mysqli_query($mysqli, $select_optio_zm);
										
										// FETCH STATUS FROM DATABASE
										$spalte_optio_zm = mysqli_fetch_assoc($result_optio_zm);
									?>
									
									<input type="text" id="p_select" name="p_diff" value="<? echo $spalte_optio_zm['rid_type'] . $spalte_optio_zm['rid']; ?>" disabled="disabled" />
								</td>
							</tr>
							<tr>
								<td align="left">
									Teilnehmernummer
								</td>
								<td align="right">
									<input type="tel" id="t_select" name="sid" maxlength="4" pattern="^[1-9]{1}[0-9]{0,3}$" placeholder="Erwarte Teilnehmer" required="required" <? echo $disabled; ?> autofocus />
								</td>
							</tr>
							<?											
								// MAKE SURE TO DISPLAY SELECT OPTION ONLY WHEN MORE THAN ONE ZPOSITION AND NOT SPRINT
								if($ztype != "Sprint" AND count($zpositions_container) > 1) {				
									echo	'
											<tr>	 
												<td align="left">
												Prüfungsposition
												</td>
												<td align="right">
												<select name="t_pos" id="t_pos" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" required="required">
													<option value="none">&#8987; Bitte warten</option>
											';
												
									// SEARCH FOR RESULTS IN REUSLT TABLE BASED ON ARRAY STACK POSITION
									for($i = 0; $i < count($zpositions_container); $i++) {
										echo	'
													<option value="' . $zpositions_container[$i] . '" disabled="disabled">' . $zpositions_container[$i] . '</option>
												';
									}
												
									echo	'
												</select>
												</td>
											</tr>
											';
								} elseif($ztype == "Sprint" OR count($zpositions_container) == 1) {	
									echo	'
											<tr>	 
												<td align="left">
												Prüfungsposition
												</td>
												<td align="right">
													<input type="text" name="t_pos" value="' . $zpositions_container[0] . '" required="required" readonly="readonly" />
												</td>
											</tr>
											';
								}
							?>	
							<tr>
								<th colspan="2">&nbsp;</th>
							</tr>
							<div id="result_hint"></div>
							<tr>
								<td align="left">Zeit</td>
								<td align="right">
									<table width="135px" cellspacing="0px">
										<tr>
											<td align="left">
												<input type="tel" id="t_start" name="t_start" style="width: 135px;" maxlength="11" required="required" placeholder="<? echo $result_plho; ?>" /> 
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<th colspan="2">&nbsp;</th>
							</tr>
							<tr>
								<td align="left">
									<button id="time" name="time" style="height: 24px; border: 1px solid #FFFFFF; background: transparent; background-color: #A09A8E; color: #8E6516; width: 135px;" tabindex="-1" disabled='disabled' disabled>
										<a style="text-decoration: none; border-style: none; color: #8E6516; font-family: Arial, sans-serif; font-size: 14px; pointer-events: none; cursor: default;" href="https://www.schnelle-online.info/Atomuhr-Uhrzeit.html" id="soitime220204983267" tabindex="-1">Uhrzeit</a>
										<script type="text/javascript">
											SOI = (typeof(SOI) != 'undefined') ? SOI : {};
										
											(SOI.ac21fs = SOI.ac21fs || []).push(function() {
												(new SOI.DateTimeService("220204983267", "DE")).start();
											});
													
											(function() {
												if (typeof(SOI.scrAc21) == "undefined") { 
												SOI.scrAc21=document.createElement('script');
												SOI.scrAc21.type='text/javascript'; 
												SOI.scrAc21.async=true;
												SOI.scrAc21.src=((document.location.protocol == 'https:') ? 'https://' : 'http://') + 'homepage-tools.schnelle-online.info/Homepage/atomicclock2_1.js';
												var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(SOI.scrAc21, s);
												}
											})();
										</script>
									</button>
								</td>
								<td align="right">
									<input type="submit" name="results" id="results" value="Ergebnis eintragen" <? echo $disabled; ?> />
								</td>
							</tr>
							<tr>
								<th colspan="2">&nbsp;</th>
							</tr>
							<?php
								//	Hole zuletzt eingegebene Zeiten
								$select_recent_results = "SELECT * FROM `_main_wpresults` WHERE `zid` = '" . $uid . "' ORDER BY `id` DESC LIMIT 3";
								$result_recent_results = mysqli_query($mysqli, $select_recent_results);
								$numrow_recent_results = mysqli_num_rows($result_recent_results);
							
								if($numrow_recent_results > 0) {
									echo	"
											<tr>
												<td colspan=\"2\" style=\"border-bottom: 1px solid white;\"><strong>Zuletzt getätigte Eingaben</strong></td>
											</tr>
											";
									
									while($getrow_recent_results = mysqli_fetch_assoc($result_recent_results)) {
										echo	"
												<tr>
													<td align=\"center\">Startnummer <strong>" . $getrow_recent_results['sid'] . "</strong></td>
													<td align=\"center\">Zeit <strong>" . $getrow_recent_results['t_realtime'] . "</strong></td>
												</tr>
												";
									}
								}
							?>
						</table>
						</form>
						
						<div><br /></div>
						
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
						
						<div id="special_hint"></div>
						
						<span id="logged_state"></span>
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