<?php error_reporting(E_ALL);
	date_default_timezone_set("Europe/Berlin");
	
	// GET CURRENT TIMESTAMP ...
	$timestamp = time();
		
	// ... AND CONVERT TO TODAY'S DATE
	$today_date = date('Y-m-d', $timestamp);
		
	/*
	$uhrzeit_hh = date("H", $timestamp);
	$uhrzeit_mm = date("i", $timestamp);
	*/
	
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
	
	// START SECURE SESSION
	session_start();
	
	// SET RESULT STORE AS ARRAY IF NOT SET
	if(!isset($_SESSION['holder'])) {
		$_SESSION['holder'] = array();
	}
	
	// SET RESULT HOLDER AS ARRAY IF NOT SET
	if(!isset($persistence)) {
		$persistence = array();
	}
	
	// CHECK LOGGED IN USER AND VALIDATE INFORMATION
	if(isset($_SESSION['user_id']) AND $_SESSION['user_id'] != "") {
		// RETURN SESSION TO LOCAL VARIABLES
		$uid	= $_SESSION['user_id'];
		$rid_type	= $_SESSION['rid_type'];
		$rid		= $_SESSION['rid'];
		$username	= $_SESSION['username'];
		$opt_whois	= $_SESSION['opt_whois'];
		
		// FETCH EVENT ID
		$select_event = "SELECT * FROM _optio_zmembers WHERE `id` = '" . $uid . "'";
		$result_event = mysqli_query($mysqli, $select_event);
		$getrow_event = mysqli_fetch_assoc($result_event);
		$zid = $getrow_event['id'];
		// DECLARE EVENT ID
		$eid = $getrow_event['eid'];
		
		// SEARCH FOR ZTYPE (REGULAR OR SPRINT?)
		$select_ztype = "SELECT * FROM _optio_zpositions WHERE `zid` = '" . $zid . "'";
		$result_ztype = mysqli_query($mysqli, $select_ztype);
		$numrow_ztype = mysqli_num_rows($result_ztype);
		
		// MAKE SURE ZMEMBER GOT ALLOCATED ROUND
		if($numrow_ztype > 0) {
			$getrow_ztype = mysqli_fetch_assoc($result_ztype);
			$ztype = $getrow_ztype['pos'];
		} else {
			header("Location: login_fail.php");
			$ztype = "";
		}
		
		// CHANGE INPUT MASK FOR RESULT BASED ON ZTYPE
		if($ztype != "" OR !empty($ztype)) {
			if($ztype == "Sprint") {
				$result_mask = '$("#t_start").mask("99:99,99",{placeholder:"MM:SS,00"});';
				$result_form = 8;
			} elseif($ztype != "Sprint") {
				$result_mask = '$("#t_start").mask("99:99:99,99",{placeholder:"HH:MM:SS,00"});';
				$result_form = 11;
			}
		}

		// OUTPUT NAVBAR AND LOGIN / LOGOUT PANEL
		$logged = file_get_contents("essentials/logout.html");
		$navbar = file_get_contents("essentials/mz_navbar_logged_in.html");
		include("essentials/chat_slider.php");
		
		// FORM SENT
		if(	isset($_POST['results']) 		AND 
			isset($_POST['sid']) 		AND
			isset($_POST['t_start'])		AND
			/*
			isset($_POST['t_start_hh']) 	AND 
			isset($_POST['t_start_mm']) 	AND 
			isset($_POST['t_start_ss']) 	AND 
			isset($_POST['t_start_ms']) 	AND 
			*/
			!empty($_POST['sid']) 		AND 
			!empty($_POST['t_start'])
			/*
			!empty($_POST['t_start_hh']) 	AND 
			!empty($_POST['t_start_mm']) 	AND 
			!empty($_POST['t_start_ss']) 	AND 
			!empty($_POST['t_start_ms'])
			*/
		) {
			if(strlen($_POST['t_start']) == $result_form) {
				// UNSET SUBMIT
				unset($_POST['results']);
				
				/*
				// DECLARE DATE PARTS (VIA EXPLODE)
				$date_parts = explode('-', $today_date);
				// GET YEAR
				$t_start_yy = $date_parts[0];
				// GET MONTH
				$t_start_mo = $date_parts[1];
				// GET DAY
				$t_start_dd = $date_parts[2];			
				*/
				
				// SANITIZE FORM VALUES
				$sid	= mysqli_real_escape_string($mysqli, $_POST['sid']);
				$t_start	= mysqli_real_escape_string($mysqli, $_POST['t_start']);
				
				/*
				$t_start_hh = mysqli_real_escape_string($mysqli, $_POST['t_start_hh']);
				$t_start_mm = mysqli_real_escape_string($mysqli, $_POST['t_start_mm']);
				$t_start_ss = mysqli_real_escape_string($mysqli, $_POST['t_start_ss']);
				$t_start_ms = mysqli_real_escape_string($mysqli, $_POST['t_start_ms']);
				*/
				
				// NOTICE FORMAT LENGTH
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
					$t_start_hh = 0;
					$t_start_mm = $time[0];
					$t_start_ss = $time[1];
					
					// GET CENTISECONDS AND SET SECONDS
					$t_centi = explode(",", $t_start_ss);
					$t_start_ss = $t_centi[0];
					$t_start_ms = $t_centi[1];
					
					// MAKE STRING TO INTEGER
					if($t_start_mm == "00") {
						$t_start_mm = str_replace('00', '0', $t_start_mm);
						$t_start_mm = intval($t_start_mm);
					}
					if($t_start_ss == "00") {
						$t_start_ss = str_replace('00', '0', $t_start_ss);
						$t_start_ss = intval($t_start_ss);
					}
					
					// CREATE SECONDS FROM INPUT TO SAVE FOR DB INSTEAD OF STRTOTIME
					$seconds = intval(($t_start_mm[0] * 60) + $t_start_ss);
				}
				
				// GET SINGLE, UNTOUCHED CENTISECONDS
				$ergHundertstel = $t_start_ms;
				
				// CHECK HH, MM, SS AND MS FOR LENGTH AND FILL WITH LEADING ZERO IF NECCESSARY
				/*
				if(strlen($t_start_yy) == 1) {
					$t_start_yy = "0" . $t_start_yy;
				}
				if(strlen($t_start_mo) == 1) {
					$t_start_mo = "0" . $t_start_mo;
				}
				if(strlen($t_start_dd) == 1) {
					$t_start_dd = "0" . $t_start_dd;
				}
				*/
				if(strlen($t_start_hh) == 1) {
					$t_start_hh = "0" . $t_start_hh;
				}
				if(strlen($t_start_mm) == 1) {
					$t_start_mm = "0" . $t_start_mm;
				}
				if(strlen($t_start_ss) == 1) {
					$t_start_ss = "0" . $t_start_ss;
				}
				if(strlen($t_start_ms) == 1) {
					$t_start_ms = $t_start_ms . "0";
				}
				
				/*
				// BUILD TIME
				$time = time();
				// TIME IN HOURS
				$t_start_hh = date("H", $time);
				// TIME IN MINUTES
				$t_start_mm = date("i", $time);
				// TIME IN SECONDS
				$t_start_ss = date("s", $time);
				// TIME IN MICROSECONDS
				$t_start_ms = date("u", $time);
				*/
				
				// MERGE TIME TO CONVERT TO TIMESTAMP (USING : FOR CENTISECONDS TO MAKE SPLIT EASIER, WHEN CALCULATING RESULTS)
				$time_merged = /*$t_start_yy . "-" . $t_start_mo . "-" . $t_start_dd . " " . */$t_start_hh . ":" . $t_start_mm . ":" . $t_start_ss . "." . $t_start_ms;
				// echo $time_merged;
				
				if($ztype != "Sprint") {
					// CREATE TIMESTAMP
					$ergSekunden = strtotime($time_merged);
				} elseif($ztype == "Sprint") {
					$ergSekunden = $seconds;
				}
				
				// CREATE TIMESTAMP FROM RESULT
				// $ms_total = (60 * $t_start_mm + $t_start_ss) * 1000 + $t_start_ms;
				
				// START OF GETTING POSITION STACK FOR LOGGED IN USER
				// FETCH POSITION STACK OF LOGGED IN USER
				$select_stack = "SELECT * FROM _optio_zpositions WHERE `zid` = '" . $uid . "'";
				$result_stack = mysqli_query($mysqli, $select_stack);
				$numrow_stack = mysqli_num_rows($result_stack);
				
				// ITERATION (FOR AVOIDING LAST SPACE IN STACK)
				$i = 1;
				
				// CREATE STACK VARIABLE
				$position_stack = "";
				
				// FETCH POSITION STACK
				while($getrow_stack = mysqli_fetch_assoc($result_stack)) {
					if($i < $numrow_stack) {
						$position_stack .= $getrow_stack['pos'] . " ";
						$i++;
					} elseif($i == $numrow_stack) {
						$position_stack .= $getrow_stack['pos'];
					}				
				}
				
				// EXPLODE POSITION STACK
				$stacks = explode(" ", $position_stack);
				// END OF GETTING POSITION STACK FOR LOGGED IN USER
				
				// START OF SEARCHING AND SAVING IN RESULT TABLE
				// EMPTY PLACEHOLDER VARIABLE
				$pos_placeholder = "";
				
				// DEFINE SEARCH QUERY
				$wpresult_select = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `sid` = '" . $sid . "' AND `position` = '" . $pos_placeholder . "'";
				
				// MAXIMUM REGISTERED POSITIONS
				$max_pos = count($stacks);
				
				// CURRENT STACK
				$select_stack_wpr = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `sid` = '" . $sid . "' AND `zid` = '" . $uid . "'";
				$result_stack_wpr = mysqli_query($mysqli, $select_stack_wpr);
				$numrow_stack_wpr = mysqli_num_rows($result_stack_wpr);
				$getrow_stack_wpr = mysqli_fetch_assoc($result_stack_wpr);
				
				// SEPARATE SEARCHING FOR POSITIONS
				$select_stack_spc = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `sid` = '" . $sid . "'";
				$result_stack_spc = mysqli_query($mysqli, $select_stack_spc);
				$getrow_stack_spc = mysqli_fetch_assoc($result_stack_spc);
				$numrow_stack_spc = mysqli_num_rows($result_stack_spc);
				
				// CHECK IF THERE ARE ALREADY RESULTS
				if($numrow_stack_spc > 0) {
					// INITIATE ARRAY AS CONTAINER FOR TIMESTAMPS
					$array_time = array();
					
					// LOAD EVERY ROW IN CONTAINER
					for($j = 0; $j < $numrow_stack_spc; $j++) {
						$array_time[$j] = $getrow_stack_spc['t_time'];
					}
					
					// INITIALIZE ERROR COUNTER
					$code_excecution = 2;
					
					// COMPARE TIMESTAMPS FROM DATABASE WITH USER INPUT
					// ALREADY SAVED VALUES CAN'T BE SMALLER THAN INPUT
					if($ergSekunden <= end($array_time)) {
						// SET CODE EXECUTION TO 0
						$code_excecution--;
					}/* elseif($ergSekunden > end($array_time)) {
						// SET CODE EXECUTION TO 1
						$code_excecution--;
					}*/
					
					// SEARCH FOR VALID TMEMBERS
					$select_tmembers_valid_list = "SELECT eid, sid FROM _optio_tmembers WHERE `eid` = '" . $eid . "' ORDER BY `sid` ASC";
					$result_tmembers_valid_list = mysqli_query($mysqli, $select_tmembers_valid_list);
					// INITIALIZE ARRAY AS TMEMBERS CONTAINER
					$array_tmembers_valid_list = array();
					// FETCH EVERY TMEMBER FOR THIS EVENT ID
					while($getrow_tmembers_valid_list = mysqli_fetch_assoc($result_tmembers_valid_list)) {
						$array_tmembers_valid_list[] = $getrow_tmembers_valid_list['sid'];
					}
					// COMPARE SUBMITTED START ID WITH TMEMBERS ARRAY
					if(!in_array($sid, $array_tmembers_valid_list, true)) {
						$code_excecution--;
					}
					
					// CHECK FOR CONTINUE CODE EXCECUTION
					if($code_excecution == 2) {
						// CHECK IF CURRENT POSITION IS EQUAL TO REGISTERED POSITION COUNT
						if($numrow_stack_wpr < $max_pos) {
							$cur_pos = $stacks[$numrow_stack_wpr];
						} elseif($numrow_stack_wpr >= $max_pos) {
							$cur_pos = "";
						}
						
						// FETCH INFORMATION TMEMBERS INFORMATION
						$select_tmembers = "SELECT eid, sid, ready FROM _optio_tmembers WHERE `eid` = '" . $eid . "' AND `sid` = '" . $sid . "'";
						$result_tmembers = mysqli_query($mysqli, $select_tmembers);
						$numrow_tmembers = mysqli_num_rows($result_tmembers);
						
						// SORT AND VALIDATE INCOMING RESULTS BASED ON MAXIMUM REGISTERED POSITIONS
						if(count($cur_pos) <= $max_pos AND ($cur_pos != "" OR !empty($cur_pos))) {
							// SET CURRENT POSITION STACK TO VALID
							$t_validation = 1;
							// INSERT RESULT FOR TMEMBER
							$insert_stack = "INSERT INTO 
												_main_wpresults(
													id,
													eid,
													rid,
													zid,
													sid,
													t_pos,
													t_time,
													t_centi,
													t_realtime,
													t_validation
												)
											VALUES(
												NULL,
												'" . $eid . "',
												'" . $rid . "',
												'" . $uid . "',
												'" . $sid . "',
												'" . $cur_pos . "',
												'" . $ergSekunden . "',
												'" . $ergHundertstel . "',
												'" . $time_merged . "',
												'" . $t_validation . "'
											)";
							mysqli_query($mysqli, $insert_stack);
						} elseif(count($cur_pos) > $max_pos OR ($cur_pos == "" OR empty($cur_pos))) {
							// SET CURRENT POSITION STACK TO INVALID
							$t_validation = 0;
							$cur_pos = "+";
							// INSERT RESULT FOR TMEMBER
							$insert_stack = "INSERT INTO 
												_main_wpresults(
													id,
													eid,
													rid,
													zid,
													sid,
													t_pos,
													t_time,
													t_centi,
													t_realtime,
													t_validation
												)
											VALUES(
												NULL,
												'" . $eid . "',
												'" . $rid . "',
												'" . $uid . "',
												'" . $sid . "',
												'" . $cur_pos . "',
												'" . $ergSekunden . "',
												'" . $ergHundertstel . "',
												'" . $time_merged . "',
												'" . $t_validation . "'
											)";
							mysqli_query($mysqli, $insert_stack);
							// MAKE ALERT AFTER REFRESH WHEN MORE RESULT THAN REGISTERED POSITIONS
							$_SESSION['validation'] =	'
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
															<div id="dialog-confirm" title="Mehr Zeiten als erlaubt!">
																<p align="justify"><span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>Für diesen Teilnehmer wurden mehr Zeiten eingeben, als von Ihrem zuständigen Auswerter / Veranstalter für Ihre Positionen der Prüfung festgelegt wurden. Diese und weitere Zeiten werden entsprechend markiert.</p>
															</div>
														';
						}
						// REDIRECT TO SAME PAGE WITH NO POST (TO PREVENT REFRESH)
						//echo '<meta http-equiv="refresh" content="0; url=/msdn/timebuddy.php">';
						
						// CHECK WHETHER QUERY WAS SUCCESSFUL OR NOT
						if(mysqli_affected_rows($mysqli) == 1) {
							$state		= 'Erfolgreich!';
							$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Zeit wurde gespeichert!</span><br />';
						} elseif(mysqli_affected_rows($mysqli) > 1) {
							// CREATE HINT MESSAGE
							$state		= 'Achtung &#9888';
							$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Es mehr als ein Datensatz betroffen! (Duplikat?)</span><br />';
						} elseif(mysqli_affected_rows($mysqli) <= 0) {
							// STORE IN HOLDER
							$persistence[] = $eid;
							$persistence[] = $rid;
							$persistence[] = $uid;
							$persistence[] = $sid;
							$persistence[] = $cur_pos;
							$persistence[] = $ergSekunden;
							$persistence[] = $ergHundertstel;
							$persistence[] = $time_merged;
							$persistence[] = $t_validation;
							
							// SAVE HOLDER IN STORE
							$_SESSION['holder'] = $persistence;
							
							// CREATE ERROR MESSAGE
							$state		= 'Hinweis!';
							$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Es konnte keine Verbindung hergestellt werden! </span><br />';
							$error_msg	.= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Daten wurden temporär gespeichert und hochgeladen, </span><br />';
							$error_msg	.= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>sobald eine Verbindung aufgebaut werden konnte. </span><br />';
							$error_msg	.= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Bitte schließen Sie den Browser nicht! </span><br />';
						}
						
						// echo "Betroffene DS: " . mysqli_affected_rows($mysqli);
					
					// STOP CODE EXCECUTION
					} elseif($code_excecution < 2) {
						if($code_excecution == 1) {
							// CREATE ERROR MESSAGE
							$state		= 'Achtung &#9888';
							$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Eingegebene Zeit darf bestehende Zeiten <strong>nicht</strong> </span><br />';
							$error_msg	.= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>unterschreiten! Zeit wurde <strong>nicht</strong> gespeichert! </span><br />';
						} elseif($code_excecution == 0) {
							// CREATE ERROR MESSAGE
							$state		= 'Achtung &#9888';
							$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Teilnehmer nicht vorhanden!</span><br />';
						}
					}
				} elseif($numrow_stack_wpr == 0) {
					// INITIATE ARRAY AS CONTAINER FOR TIMESTAMPS
					$array_time = array();
					
					// LOAD EVERY ROW IN CONTAINER
					for($j = 0; $j < $numrow_stack_spc; $j++) {
						$array_time[$j] = $getrow_stack_spc['t_time'];
					}
					
					// CHECK IF CURRENT POSITION IS EQUAL TO REGISTERED POSITION COUNT
					if($numrow_stack_wpr < $max_pos) {
						$cur_pos = $stacks[$numrow_stack_wpr];
					} elseif($numrow_stack_wpr >= $max_pos) {
						$cur_pos = "";
					}
						
					// FETCH INFORMATION TMEMBERS INFORMATION
					$select_tmembers = "SELECT eid, sid, ready FROM _optio_tmembers WHERE `eid` = '" . $eid . "' AND `sid` = '" . $sid . "'";
					$result_tmembers = mysqli_query($mysqli, $select_tmembers);
					$numrow_tmembers = mysqli_num_rows($result_tmembers);
					
					// INITIALIZE ERROR COUNTER
					$code_excecution = 2;
					
					// COMPARE TIMESTAMPS FROM DATABASE WITH USER INPUT
					// ALREADY SAVED VALUES CAN'T BE SMALLER THAN INPUT
					if($ergSekunden <= end($array_time)) {
						// SET CODE EXECUTION TO 0
						$code_excecution--;
					}/* elseif($ergSekunden > end($array_time)) {
						// SET CODE EXECUTION TO 0
						$code_excecution--;
					}*/
					
					// SEARCH FOR VALID TMEMBERS
					$select_tmembers_valid_list = "SELECT eid, sid FROM _optio_tmembers WHERE `eid` = '" . $eid . "' ORDER BY `sid` ASC";
					$result_tmembers_valid_list = mysqli_query($mysqli, $select_tmembers_valid_list);
					// INITIALIZE ARRAY AS TMEMBERS CONTAINER
					$array_tmembers_valid_list = array();
					// FETCH EVERY TMEMBER FOR THIS EVENT ID
					while($getrow_tmembers_valid_list = mysqli_fetch_assoc($result_tmembers_valid_list)) {
						$array_tmembers_valid_list[] = $getrow_tmembers_valid_list['sid'];
					}
					// COMPARE SUBMITTED START ID WITH TMEMBERS ARRAY
					if(!in_array($sid, $array_tmembers_valid_list, true)) {
						$code_excecution--;
					}
					
					// CHECK FOR CONTINUE CODE EXCECUTION
					if($code_excecution == 2) {						
						// SORT AND VALIDATE INCOMING RESULTS BASED ON MAXIMUM REGISTERED POSITIONS
						if(count($cur_pos) <= $max_pos AND ($cur_pos != "" OR !empty($cur_pos))) {
							// SET CURRENT POSITION STACK TO VALID
							$t_validation = 1;
							// INSERT RESULT FOR TMEMBER
							$insert_stack = "INSERT INTO 
												_main_wpresults(
													id,
													eid,
													rid,
													zid,
													sid,
													t_pos,
													t_time,
													t_centi,
													t_realtime,
													t_validation
												)
											VALUES(
												NULL,
												'" . $eid . "',
												'" . $rid . "',
												'" . $uid . "',
												'" . $sid . "',
												'" . $cur_pos . "',
												'" . $ergSekunden . "',
												'" . $ergHundertstel . "',
												'" . $time_merged . "',
												'" . $t_validation . "'
											)";
							mysqli_query($mysqli, $insert_stack);
						} elseif(count($cur_pos) > $max_pos OR ($cur_pos == "" OR empty($cur_pos))) {
							// SET CURRENT POSITION STACK TO INVALID
							$t_validation = 0;
							$cur_pos = "+";
							// INSERT RESULT FOR TMEMBER
							$insert_stack = "INSERT INTO 
												_main_wpresults(
													id,
													eid,
													rid,
													zid,
													sid,
													t_pos,
													t_time,
													t_centi,
													t_realtime,
													t_validation
												)
											VALUES(
												NULL,
												'" . $eid . "',
												'" . $rid . "',
												'" . $uid . "',
												'" . $sid . "',
												'" . $cur_pos . "',
												'" . $ergSekunden . "',
												'" . $ergHundertstel . "',
												'" . $time_merged . "',
												'" . $t_validation . "'
											)";
							mysqli_query($mysqli, $insert_stack);
							// MAKE ALERT AFTER REFRESH WHEN MORE RESULT THAN REGISTERED POSITIONS
							$_SESSION['validation'] =	'
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
															<div id="dialog-confirm" title="Mehr Zeiten als erlaubt!">
																<p align="justify"><span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>Für diesen Teilnehmer wurden mehr Zeiten eingeben, als von Ihrem zuständigen Auswerter / Veranstalter für Ihre Positionen der Prüfung festgelegt wurden. Diese und weitere Zeiten werden entsprechend markiert.</p>
															</div>
														';
						}
						
						// REDIRECT TO SAME PAGE WITH NO POST (TO PREVENT REFRESH)
						//echo '<meta http-equiv="refresh" content="0; url=/msdn/timebuddy.php">';
							
						// CHECK WHETHER QUERY WAS SUCCESSFUL OR NOT
						if(mysqli_affected_rows($mysqli) == 1) {
							$state		= 'Erfolgreich!';
							$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Zeit wurde gespeichert!</span><br />';
						} elseif(mysqli_affected_rows($mysqli) > 1) {
							// CREATE HINT MESSAGE
							$state		= 'Achtung &#9888';
							$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Es mehr als ein Datensatz betroffen! (Duplikat?)</span><br />';
						} elseif(mysqli_affected_rows($mysqli) <= 0) {
							// STORE IN HOLDER
							$persistence[] = $eid;
							$persistence[] = $rid;
							$persistence[] = $uid;
							$persistence[] = $sid;
							$persistence[] = $cur_pos;
							$persistence[] = $ergSekunden;
							$persistence[] = $ergHundertstel;
							$persistence[] = $time_merged;
							$persistence[] = $t_validation;
							
							// SAVE HOLDER IN STORE
							$_SESSION['holder'] = $persistence;
							
							// CREATE ERROR MESSAGE
							$state		= 'Hinweis!';
							$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Es konnte keine Verbindung hergestellt werden! </span><br />';
							$error_msg	.= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Daten wurden temporär gespeichert und hochgeladen, </span><br />';
							$error_msg	.= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>sobald eine Verbindung aufgebaut werden konnte. </span><br />';
							$error_msg	.= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Bitte schließen Sie den Browser nicht! </span><br />';
						}
						
						// echo "Betroffene DS: " . mysqli_affected_rows($mysqli);
					// STOP CODE EXCECUTION
					} elseif($code_excecution < 2) {
						if($code_excecution == 1) {
							// CREATE ERROR MESSAGE
							$state		= 'Achtung &#9888';
							$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Eingegebene Zeit darf bestehende Zeiten <strong>nicht</strong> </span><br />';
							$error_msg	.= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>unterschreiten! Zeit wurde <strong>nicht</strong> gespeichert! </span><br />';
						} elseif($code_excecution == 0) {
							// CREATE ERROR MESSAGE
							$state		= 'Achtung &#9888';
							$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Teilnehmer nicht vorhanden!</span><br />';
						}
					}
				}
			} else {
				// CREATE ERROR MESSAGE
				$state		= 'Hinweis!';
				$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Korrektes Format beachten! </span><br />';
			}
		} elseif(isset($_POST['results']) AND (($_POST['sid'] == "" OR $_POST['t_start'] == "") OR ($_POST['sid'] == "" AND $_POST['t_start'] == ""))) {
			// CREATE ERROR MESSAGE
			$state		= 'Hinweis!';
			$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Alle Felder sind korrekt auszufüllen! </span><br />';
		}
	} else {
		// REDIRECT 
		header('Location: /msdn/index.php');
		$logged = file_get_contents("essentials/login.html");
		$navbar = file_get_contents("essentials/navbar_logged_out.html");
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
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<!--	INCLUDING ICO	-->
		<link rel="shortcut icon" href="favicon.ico">
				
		<!--	INCLUDING LIB	-->
		<?php 
			include("lib/library.html");
			include("lib/library_int_timebuddy.html");			
		?>
		
		<script>
			jQuery(function($){
				/*
				$.mask.definitions['h'] = "([01]?[0-9]|2[0-3])";
				$.mask.definitions['m'] = "([0-5][0-9])";
				$.mask.definitions['s'] = "([0-5][0-9])";
				$.mask.definitions['u'] = "([0-9][0-9])";
				$("#t_start").mask("hh:mm:ss,uu",{placeholder:"HH:MM:SS,00"});
				*/
				<?
					echo $result_mask;
				?>
			});
		</script>
		
		<?php
			// ECHO VALIDATION IF SET
			if(isset($_SESSION['validation']) AND $_SESSION['validation'] != "" OR !empty($_SESSION['validation'])) {
				// WRITE TO LOCAL VARIABLE
				$validation = $_SESSION['validation'];
				// ECHO MODAL
				echo $validation;
				// RESET MODAL
				$validation = "";
				unset($_SESSION['validation']);
			}
		?>
		
		<noscript>
			<div style="z-index: 5000; left: 0; position: fixed; text-align: center; width: 100%; height: 100%; background-color: rgba(48, 48, 48, 0.75);">
				<h2 style="line-height: 100%; padding-top: 25%; color: #fff;"><span style="border: 1px dotted #fff; padding: 25px 50px 25px 50px; background-color: rgba(255, 0, 0, 0.25)">Bitte aktivieren Sie JavaScript!</span></h2>
			</div>
		</noscript>
	</head>
	
	<body>
		<div id="container" style="min-height: 350px !important;">
			<div id="linkList" style="top: 20px;">			
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
				<!--
				<div id="pageHeader">
					<h1><span style="position:absolute; right: 30px; top: 25px;">Z 3 : 1 T : 0 0 , 000</span></h1>
					<h3><span style="position:absolute; right: 30px; top: 64px;"><i>tempus fugit ..</i></span></h3>
				</div>
				-->
			
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
					
						<form action="<? $_SERVER['PHP_SELF']; ?>" method="POST">
							<table cellspacing="5px" cellpadding="0" style="border: 1px solid #FFFFFF;" width="385px">
								<tr>	 
									<td align="left">
										Prüfungsnummer
									</td>
									<td align="right">
										<!--
										<select name="p_select" id="p_select" class="input-block-level" placeholder="Bitte auswählen" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" required="required" />
											<option disabled='disabled' selected='selected'>Bitte auswählen</option>
											<?												
												/* QUERIES _OPTIO_TMEMBERS
												$select_main_wpt = "SELECT id, eid, rid_type, rid, sz_id, suspend FROM _main_wptable WHERE `eid` = '" . $eid . "' ORDER BY rid ASC";
												$result_main_wpt = mysqli_query($mysqli, $select_main_wpt);
												
												$disabled = "disabled = 'disabled'";
												
														// FETCH STATUS FROM DATABASE
														while($spalte_main_wpt = mysqli_fetch_assoc($result_main_wpt)) {
														// READY STATUS IN _OPTIO_TMEMBERS FOUND
														if($spalte_main_wpt['suspend'] == "yes") {
															echo "<option value='" . $spalte_main_wpt['rid'] . "' " . $disabled . ">" . $spalte_main_wpt['rid_type'] . $spalte_main_wpt['rid'] . " - deaktiviert</option>";
													} elseif($spalte_main_wpt['suspend'] == "no") {
															echo "<option value='" . $spalte_main_wpt['rid'] . "'>" . $spalte_main_wpt['rid_type'] . $spalte_main_wpt['rid'] . "</option>";
													} 
												}
												*/
											?>
										</select>
										-->
										<?												
											// QUERIES _OPTIO_TMEMBERS
											$select_optio_zm = "SELECT id, eid, rid_type, rid FROM _optio_zmembers WHERE `eid` = '" . $eid . "' AND `id` = '" . $uid . "' LIMIT 1";
											$result_optio_zm = mysqli_query($mysqli, $select_optio_zm);
											
											// FETCH STATUS FROM DATABASE
											$spalte_optio_zm = mysqli_fetch_assoc($result_optio_zm);
										?>
										
										<input type="text" id="p_select" name="p_diff" value="<? echo $spalte_optio_zm['rid_type'] . $spalte_optio_zm['rid']; ?>" readonly />
									</td>
								</tr>
								<tr>
									<td align="left">
										Teilnehmernummer
									</td>
									<td align="right">
										<!--
										<select name="t_select" id="t_select" class="input-block-level" placeholder="Bitte auswählen" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" required="required" />
											<option disabled='disabled' selected='selected'>Bitte auswählen</option>
											<?												
												// QUERIES _OPTIO_TMEMBERS
												/*
												$select_opt_mt = "SELECT id, eid, sid, class, vname_1, nname_1, vname_2, nname_2, ready FROM _optio_tmembers WHERE `eid` = '" . $eid . "' ORDER BY sid ASC";
												$result_opt_mt = mysqli_query($mysqli, $select_opt_mt);
												
												$disabled = "disabled = 'disabled'";
												
														// FETCH STATUS FROM DATABASE
														while($spalte_opt_mt = mysqli_fetch_assoc($result_opt_mt)) {
														// READY STATUS IN _OPTIO_TMEMBERS FOUND
														if($spalte_opt_mt['ready'] == "no" OR $spalte_opt_mt['ready'] == "fin") {
															echo "<option value='" . $spalte_opt_mt['sid'] . "' " . $disabled . ">" . $spalte_opt_mt['sid'] . " - deaktiviert</option>";
													} elseif($spalte_opt_mt['ready'] == "yes" OR $spalte_opt_mt['ready'] == "pen") {
															echo "<option value='" . $spalte_opt_mt['sid'] . "'>" . $spalte_opt_mt['sid'] . "</option>";
													} 
												}
												*/
											?>
										</select>
										-->
										
										<input type="text" id="t_select" name="sid" maxlength="4" pattern="^[1-9]{0,1}[0-9]{1,3}$" required="required" autofocus />
									</td>
								</tr>
								<tr>
									<th colspan="2">&nbsp;</th>
								</tr>
								<?
									// PREPARE QUERIES
									// _MAIN_AMEMBERS
									$select_main_amembers	= "SELECT id, position FROM _main_amembers WHERE id = '" . $eid . "' LIMIT 1";
									$result_main_amembers	= mysqli_query($mysqli, $select_main_amembers);
									$number_main_amembers	= mysqli_num_rows($result_main_amembers);
									$fetcha_main_amembers	= mysqli_fetch_assoc($result_main_amembers);
									// _OPTIO_ZMEMBERS
									$select_optio_zmembers	= "SELECT eid, rid FROM _optio_zmembers WHERE eid = '" . $eid . "' LIMIT 1";
									$result_optio_zmembers	= mysqli_query($mysqli, $select_optio_zmembers);
									$number_optio_zmembers	= mysqli_num_rows($result_optio_zmembers);
									$fetcha_optio_zmembers	= mysqli_fetch_assoc($result_optio_zmembers);
														
									if($number_main_amembers != 0) {
										if($fetcha_main_amembers['position'] == "Omni") {
											$t_start = "";
											$t_stop = "";
											$t_result = "<tr><td align='left'>Platzierung</td><td align='right'><input type='text' id='t_result' name='t_result' readonly /></td></tr>";
										} elseif($fetcha_main_amembers['position'] == "Start") {
											$t_start = "";
											$t_stop = "disabled = 'disabled'";
											$t_result = "";
										} elseif($fetcha_main_amembers['position'] == "Ziel") {
											$t_start = "disabled = 'disabled'";
											$t_stop = "";
											$t_result = "";
										}
									} elseif($number_optio_zmembers != 0) {		
										if($fetcha_optio_zmembers['rid'] == "Omni") {
											$t_start = "";
											$t_stop = "";
											$t_result = "<tr><td align='left'>Platzierung</td><td align='right'><input type='text' id='t_result' name='t_result' readonly /></td></tr>";
										} elseif($fetcha_optio_zmembers['rid'] == "Start") {
											$t_start = "";
											$t_stop = "disabled = 'disabled'";
											$t_result = "";
										} elseif($fetcha_optio_zmembers['rid'] == "Ziel") {
											$t_start = "disabled = 'disabled'";
											$t_stop = "";
											$t_result = "";
										}
									} elseif($number_optio_zmembers == 0 && $number_main_amembers == 0) {
										echo "ERROR";
									}
								?>
								<tr>
									<td align="left">Zeit</td>
									<td align="right">
										<table width="135px" cellspacing="0px">
											<!--
											<tr>
												<td align="left">
													<input type="text" id="t_start_hh" name="t_start_hh" style="width: 20px;" <? echo $t_start; ?> maxlength="2" pattern="^[0-9]{1,2}$" /> 
												</td>
												<td align="left">&nbsp;:&nbsp;</td>
												<td align="left">
													<input type="text" id="t_start_mm" name="t_start_mm" style="width: 20px;" <? echo $t_start; ?> maxlength="2" pattern="^[0-9]{1,2}$" />
												</td>
												<td align="left">&nbsp;:&nbsp;</td>
												<td align="left">
													<input type="text" id="t_start_ss" name="t_start_ss" style="width: 20px;" <? echo $t_start; ?> maxlength="2" pattern="^[0-9]{1,2}$" />
												</td>
												<td align="left">&nbsp;,&nbsp;</td>
												<td align="right">
													<input type="text" id="t_start_ms" name="t_start_ms" style="width: 20px;" <? echo $t_start; ?> maxlength="2" pattern="^[0-9]{1,2}$" />	
												</td>
											</tr>
											-->
											<tr>
												<td align="left">
													<input type="text" id="t_start" name="t_start" style="width: 135px;" maxlength="11" required="required" /> 
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<!--
								<tr>
									<td align="left">Zielzeit</td>
									<td align="right">
										<table width="135px" cellspacing="0px">
											<tr>
												<td align="left">
													<input type="text" id="t_stop_hh" name="t_stop_hh" value="<? echo $uhrzeit_hh; ?>" style="width: 20px;" <? echo $t_stop; ?> maxlength="2" /> 
												</td>
												<td align="left">&nbsp;&nbsp;</td>
												<td align="left">
													<input type="text" id="t_stop_mm" name="t_stop_mm" value="<? echo $uhrzeit_mm; ?>" style="width: 20px;" <? echo $t_stop; ?> maxlength="2" />
												</td>
												<td align="left">&nbsp;&nbsp;</td>
												<td align="left">
													<input type="text" id="t_stop_ss" name="t_stop_ss" style="width: 20px;" <? echo $t_stop; ?> maxlength="2" />
												</td>
												<td align="left">&nbsp;,&nbsp;</td>
												<td align="right">
													<input type="text" id="t_stop_ms" name="t_stop_ms" style="width: 20px;" <? echo $t_stop; ?> maxlength="2" />	
												</td>
											</tr>
										</table>
									</td>
								</tr>
								-->
								<tr>
									<th colspan="2">&nbsp;</th>
								</tr>
								<!--
								<tr>	
									<td align="left">
										Differenz	
									</td>
									<td align="right">
										<input type="text" id="t_diff" name="t_diff" readonly />
									</td>
								</tr>
								<? echo $t_result; ?>
								-->
								<tr>
									<th colspan="2">&nbsp;</th>
								</tr>
								<tr>
									<td align="left">
										<button id="time" name="time" style="height: 24px; border: 1px solid #FFFFFF; background: transparent; background-color: #A09A8E; color: #8E6516; width: 135px;" tabindex="-1" disabled readonly>
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
										<input type="submit" name="results" id="results" value="Ergebnis eintragen">
									</td>
								</tr>
							</table>
						</form>
						
						<div><br /></div>
						<!--
						<table cellspacing="5px" cellpadding="0" style="border: 1px solid #FFFFFF;" width="385px">
								<tr>
										<td align="left" style="font-size: small;">Zuletzt geändert</td>
										<td rowspan="5">
												<table width="100%">
														<tr>
																<td align="left" style="font-size: small;">1. Datensatz</td>
																<td align="right"><font onmouseover="this.style.color='#8E6516';" onmouseout="this.style.color='#FFFFFF';" style="font-size: xx-small;">editieren</font></td>
														</tr>
														<tr>
																<td align="left" style="font-size: small;">2. Datensatz</td>
																<td align="right"><font onmouseover="this.style.color='#8E6516';" onmouseout="this.style.color='#FFFFFF';" style="font-size: xx-small;">editieren</font></td>
														</tr>
														<tr>
																<td align="left" style="font-size: small;">3. Datensatz</td>
																<td align="right"><font onmouseover="this.style.color='#8E6516';" onmouseout="this.style.color='#FFFFFF';" style="font-size: xx-small;">editieren</font></td>
														</tr>
												</table>
										</td>
								</tr>
						</table>
						-->
						
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