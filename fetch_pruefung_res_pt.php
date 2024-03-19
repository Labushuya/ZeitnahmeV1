<? error_reporting(E_ALL);
	date_default_timezone_set("Europe/Berlin");

    // INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
	
	// START SECURE SESSION
	sec_session_start();
	
	// CREATE EVENT ID FROM ACTIVE SESSION
	$eid	= $_SESSION['user_id'];
	
	// GET SELECT OPTIONS FOR rid
	if(isset($_POST["rid"]) && !empty($_POST["rid"])) {
	    // GET ROUND DESIGNATION
		$rid = mysqli_real_escape_string($mysqli, utf8_encode($_POST['rid']));
		
		// SEARCH FOR SPECIFIC ROUND TYPES (E. G. "Sprint")
		$select_sprint = "SELECT eid, rid, z_entry FROM _main_wptable WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `z_entry` = '1'";
		$result_sprint = mysqli_query($mysqli, $select_sprint);
		$numrow_sprint = mysqli_num_rows($result_sprint);
		$getrow_sprint = mysqli_fetch_assoc($result_sprint);
		
		// INITIALIZE ADDITIONAL POSITIONS ARRAY (FOR LATER USE)
		$additional_pos_stack = array();
			
		// INITIALIZE ARRAY
		$tmem_container = array();
			
		// INITIALIZE ARRAY AS PACKAGE
		$total_information = array();
			
		// INITIALIZE ARRAY FOR CENTISECONDS
		$total_info_centis = array();
			
		// INITIALIZE ARRAY FOR TARGET TIMES
		$target_time_container = array();
		
		// INITIALIZE ARRAY FOR REALTIME
		$real_time_container = array();
					
		// GRAB EVERY TARGET TIME FOR THIS ROUND ID WITH EVENT ID FROM LOGGED IN USER
		$select_target_time = "SELECT * FROM _main_wptable_sz WHERE `rid` = '" . $rid . "' AND `eid` = '" . $eid . "'";
		$result_target_time = mysqli_query($mysqli, $select_target_time);
		$numrow_target_time = mysqli_num_rows($result_target_time);
			
		// MAKE SURE TARGET TIMES ARE VALID, OTHERWISE SHOW 
		if($numrow_target_time > 0) {
			while($getrow_target_time = mysqli_fetch_assoc($result_target_time)) {
				$target_time_container[] = $getrow_target_time["sz"];
			}
		} else {
			echo 	'
						<table width="100%" cellspacing="5px" style="border: 1px solid #9E9482;">
							<tr>
								<td align="center" colspan="2">Sollzeit(en) nicht gesetzt!</td>
							</tr>
						</table>
					';
			exit();
		}
			
		// GRAB TYPE OF CALCULATION FROM LOGGED IN USER (AUSWERTER)
		$select_tcalc = "SELECT eid, t_calc FROM _race_run_events WHERE `eid` = '" . $eid . "' AND `active` = '1'";
		$result_tcalc = mysqli_query($mysqli, $select_tcalc);
		$getrow_tcalc = mysqli_fetch_assoc($result_tcalc);
										
		// GRAB POST AND SEARCH ROUND
		$select_rd = "SELECT * FROM _main_wpresults WHERE `rid` = '" . $rid . "' AND `eid` = '" . $eid . "' ORDER BY `sid` ASC";
		$result_rd = mysqli_query($mysqli, $select_rd);
		$numrow_rd = mysqli_num_rows($result_rd);
		
		// CHECK WHETHER SPECIAL rid_type OR NOT
		// IF NOT, THEN PROCEED AS USUAL ORHERWISE, PROCEED WITH SPECIAL TABLE LAYOUT
		if($numrow_sprint == 0) {
			// START BUILDING TABLE IF RESULTS HAVE BEEN FOUND
			if($numrow_rd > 0) {
				// BUILD TABLE HEADER
				echo	'
							<table width="100%" cellpadding="5px" cellspacing="0" style="border: 1px solid #FFFFFF;">
								<tr id="table_status">
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1"><strong>#</strong></font></td>
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1"><strong>Start</strong></font></td>
						';
						
				// CHECK FOR ADDITIONAL POSITIONS (E. G. ZZ1 - ZZN)
				$select_wpt = "SELECT * FROM _main_wptable WHERE `rid` = '" . $rid . "' AND `eid` = '" . $eid . "'";
				$result_wpt = mysqli_query($mysqli, $select_wpt);
				$numrow_wpt = mysqli_num_rows($result_wpt);
				$getrow_wpt = mysqli_fetch_assoc($result_wpt);
				
				// DECLARE SUM OF ADDITIONAL POSITIONS
				$sum_additional_pos = $getrow_wpt['total_pos'] - 2;
				
				// IF SUM OF ADDITIONAL POSITIONS IS GREATER THAN ZERO ...
				if($sum_additional_pos > 0) {	
					// ... LOOP THROUGH AMOUNT OF ADDITIONAL POSITIONS
					for($a = 0; $a < $sum_additional_pos; $a++) {
						// STORE ADDITIONAL POSITIONS IN ARRAY
						$additional_pos_stack[] = "ZZ" . ($a + 1);
						
						// BUILD TABLE HEADER FOR ADDITIONAL POSITIONS
						echo	'
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1"><strong>ZZ' . ($a + 1) . '</strong></font></td>
								';
					}
				}
									
				echo	'				
									
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1"><strong>Ziel</strong></font></td>
						';
				
				// IF THERE ARE ADDITIONAL POSITIONS, THERE ARE ALSO ADDITIONAL DRIVING TIMES
				if($sum_additional_pos > 0) {
					// ... LOOP THROUGH AMOUNT OF ADDITIONAL POSITIONS FOR DRIVING TIMES
					for($d = 0; $d < ($getrow_wpt['total_pos'] - 1); $d++) {
						// BUILD TABLE HEADER FOR DRIVING TIMES BASED ON ADDITIONAL POSITIONS
						echo	'
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1"><strong>Fahrtzeit ' . ($d + 1) . '</strong></font></td>
								';
					}	
					// APPEND TOTAL DRIVING TIME AFTER LOOP
					echo	'		
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1"><strong>Fahrtzeit ges.</strong></font></td>
							';
				// THERE ARE NO ADDITIONAL POSITIONS
				} else {
					echo	'		
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1"><strong>Fahrtzeit</strong></font></td>
							';
				}
				
				// IF THERE ARE ADDITIONAL POSITIONS, THERE ARE ALSO ADDITIONAL RESULTS
				if($sum_additional_pos > 0) {
					// ... LOOP THROUGH AMOUNT OF ADDITIONAL POSITIONS FOR RESULTS
					for($f = 0; $f < ($getrow_wpt['total_pos'] - 1); $f++) {
						// BUILD TABLE HEADER FOR RESULTS BASED ON ADDITIONAL POSITIONS
						echo	'
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1"><strong>Abweichung ' . ($f + 1) . '</strong></font></td>
								';
					}	
					// APPEND TOTAL RESULTS AFTER LOOP
					echo	'		
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1"><strong>Abweichung ges.</strong></font></td>
							';
				// THERE ARE NO ADDITIONAL POSITIONS
				} else {
					echo	'		
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1"><strong>Abweichung</strong></font></td>
							';
				}
				
				echo	'		
								</tr>
						';
				// END OF BUILDING TABLE HEADER
				
				// START BUILDING TABLE CONTAINER FOR RESULTS
				echo	'
								<tr>
						';
				
				// GET TMEMBERS LIST AND SAVE AS ARRAY
				$select_tmem = "SELECT * FROM _optio_tmembers WHERE `eid` = '" . $eid . "' ORDER BY `sid` ASC";
				$result_tmem = mysqli_query($mysqli, $select_tmem);
				$numrow_tmem = mysqli_num_rows($result_tmem);
							
				// WHILE LOOP FOR STORING EVERY TMEMBER IN ARRAY
				while($getrow_tmem = mysqli_fetch_assoc($result_tmem)) {
					$tmem_container[] = $getrow_tmem['sid'];
				}
				
				// MAIN LOOP FOR BUILDING TABLE CONTAINER RESULTS
				for($b = 0; $b < count($tmem_container); $b++) {			
					// TABLE ROW START ID
					echo	'
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1">' . $tmem_container[$b] . '</font></td>
							';
							
					// SEARCH FOR START BASED ON CURRENT TMEMBER
					$select_start = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $tmem_container[$b] . "' AND `position` = 'Start'";
					$result_start = mysqli_query($mysqli, $select_start);
					$numrow_start = mysqli_num_rows($result_start);
					$getrow_start = mysqli_fetch_assoc($result_start);
					
					// CHECK IF RESULT HAS BEEN FOUND (MUST BE ONE ROW AT MAX!)
					// START RESULT HAS BEEN FOUND
					if($numrow_start == 1) {
						echo	'
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1">' . $getrow_start['t_realtime'] . '</font></td>
								';
								
						// STORE IN ARRAY AS PACKAGE
						$total_information[$tmem_container[$b]]["Start"] = $getrow_start['t_time'];
						$total_info_centis[$tmem_container[$b]]["Start"] = $getrow_start['t_centi'];
					// START RESULT HAS NOT BEEN FOUND
					} elseif($numrow_start == 0) {
						echo	'
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1">&mdash;&mdash;&mdash;</font></td>
								';
								
						// STORE IN ARRAY AS PACKAGE
						$total_information[$tmem_container[$b]]["Start"] = 0;
						$total_info_centis[$tmem_container[$b]]["Start"] = 0;
					}
					
					if($sum_additional_pos > 0) {
						// TABLE ROW ADDITIONAL POSITIONS (E. G. ZZ1 - ZZN)
						// SEARCH FOR EACH ADDITIONAL POSITION BASED ON CURRENT TMEMBER
						for($c = 0; $c < count($additional_pos_stack); $c++) {
							// SEARCH FOR ADDITIONAL RESULTS BASED ON CURRENT TMEMBER
							$select_add = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $tmem_container[$b] . "' AND `position` = '" . $additional_pos_stack[$c] . "'";
							$result_add = mysqli_query($mysqli, $select_add);
							$numrow_add = mysqli_num_rows($result_add);
							$getrow_add = mysqli_fetch_assoc($result_add);
							
							// CHECK IF RESULT HAS BEEN FOUND (MUST BE ONE ROW AT MAX!)
							// GOAL RESULT HAS BEEN FOUND
							if($numrow_add == 1) {
								echo	'
										<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1">' . $getrow_add['t_realtime'] . '</font></td>
										';
										
								// STORE IN ARRAY AS PACKAGE
								$total_information[$tmem_container[$b]][$additional_pos_stack[$c]] = $getrow_add['t_time'];
								$total_info_centis[$tmem_container[$b]][$additional_pos_stack[$c]] = $getrow_add['t_centi'];
							// GOAL RESULT HAS NOT BEEN FOUND
							} elseif($numrow_add == 0) {
								echo	'
										<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1">&mdash;&mdash;&mdash;</font></td>
										';
										
								// STORE IN ARRAY AS PACKAGE
								$total_information[$tmem_container[$b]][$additional_pos_stack[$c]] = 0;
								$total_info_centis[$tmem_container[$b]][$additional_pos_stack[$c]] = 0;
							}
						}
					}
					
					// SEARCH FOR GOAL BASED ON CURRENT TMEMBER
					$select_goal = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $tmem_container[$b] . "' AND `position` = 'Ziel'";
					$result_goal = mysqli_query($mysqli, $select_goal);
					$numrow_goal = mysqli_num_rows($result_goal);
					$getrow_goal = mysqli_fetch_assoc($result_goal);
					
					// CHECK IF RESULT HAS BEEN FOUND (MUST BE ONE ROW AT MAX!)
					// GOAL RESULT HAS BEEN FOUND
					if($numrow_goal == 1) {
						echo	'
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1">' . $getrow_goal['t_realtime'] . '</font></td>
								';
								
						// STORE IN ARRAY AS PACKAGE
						$total_information[$tmem_container[$b]]["Ziel"] = $getrow_goal['t_time'];
						$total_info_centis[$tmem_container[$b]]["Ziel"] = $getrow_goal['t_centi'];
					// GOAL RESULT HAS NOT BEEN FOUND
					} elseif($numrow_goal == 0) {
						echo	'
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1">&mdash;&mdash;&mdash;</font></td>
								';
						
						// STORE IN ARRAY AS PACKAGE
						$total_information[$tmem_container[$b]]["Ziel"] = 0;
						$total_info_centis[$tmem_container[$b]]["Ziel"] = 0;
						
						/*
						EXAMPLE FOR AN ROUND WITH ONLY TWO POSITIONS - START AND GOAL
						---- ARRAY CONTAINER WITH START ID --- POSITION
						$total_information[$tmem_container[$b]]["Ziel"]
						Array								- CONTAINER WITH START ID AS INDIZES AND RESULTS BASED ON TOTAL POSITIONS
							(
								[1] => Array				- START ID 1
									(
										[Start] => 48		- RESULT NUMBER ONE		
										[Ziel] => 62		- RESULT NUMBER TWO
									)
							)
						*/
					}
					
					// GET RESULTS FROM CURRENT TMEMBER
					$select_tresult = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $tmem_container[$b] . "'";
					$result_tresult = mysqli_query($mysqli, $select_tresult);
					$getrow_tresult = mysqli_fetch_assoc($result_tresult);
					
					// INITIATE ARRAY FOR HOLDING FINAL RESULTS
					$result_array_crudes = array();
					$result_array_centis = array();
					$result_array_refine = array();
					$result_array_tartim = array();
					
					// ... LOOP THROUGH AMOUNT OF TOTAL POSITIONS MINUS ONE BECAUSE OF THE DIFFERENCE
					for($e = 0; $e < ($getrow_wpt['total_pos'] - 1); $e++) {
					// for($e = 0; $e < ($tmem_container); $e++) {
						// CALCULATE EACH STAGE FROM START
						// if($getrow_tcalc['t_calc'] == 1) {
						if($getrow_tcalc['t_calc'] > 0) {
							// CHECK IF START RESULT IS SET
							if($total_information[$tmem_container[$b]]["Start"] != "" OR !empty($total_information[$tmem_container[$b]]["Start"])) {
								// CHECK WHETHER TOTAL POSITIONS IS GREATER THAN TWO
								if($getrow_wpt['total_pos'] > 2) {
									if($e < count($additional_pos_stack)) {
										// CHECK FOR EMPTY VALUES TO PREVENT CALCULATION WITH ONLY ONE VALUE
										if($total_information[$tmem_container[$b]]["Ziel"] != 0 OR $total_information[$tmem_container[$b]][$additional_pos_stack[$e]] != 0) {
											// SUBTRACT FIRST RESULT AFTER START FROM START (ADDITIONAL POSITION)
											// CHECK CENTISECONDS VALUE FOR CORRECT CALCULATION (IF 0 THEN SUBTRACT CENTISECONDS FROM 100 AND SUBTRACT ONE SECOND FROM TOTAL SECONDS)
											// IF START CENTISECONDS ARE 0 AND ADDITIONAL POSITION CENTISECONDS ARE 0 --> SUBTRACT NORMALLY
											if($total_info_centis[$tmem_container[$b]]["Start"] == 0 AND $total_info_centis[$tmem_container[$b]][$additional_pos_stack[$e]] == 0) {
												$result_array_centis[$e] = $total_info_centis[$tmem_container[$b]][$additional_pos_stack[$e]] - $total_info_centis[$tmem_container[$b]]["Start"];
												$result_array_crudes[$e] = $total_information[$tmem_container[$b]][$additional_pos_stack[$e]] - $total_information[$tmem_container[$b]]["Start"];
											// ELSEIF START CENTISECONDS ARE 0 AND ADDITIONAL POSITION CENTISECONDS ARE GREATER THAN 0 --> SUBTRACT NORMALLY
											} elseif($total_info_centis[$tmem_container[$b]]["Start"] == 0 AND $total_info_centis[$tmem_container[$b]][$additional_pos_stack[$e]] > 0) {
												$result_array_centis[$e] = $total_info_centis[$tmem_container[$b]][$additional_pos_stack[$e]] - $total_info_centis[$tmem_container[$b]]["Start"];
												$result_array_crudes[$e] = $total_information[$tmem_container[$b]][$additional_pos_stack[$e]] - $total_information[$tmem_container[$b]]["Start"];
											// ELSEIF START CENTISECONDS ARE GREATER THAN 0 AND ADDITIONAL POSITION CENTISECONDS ARE 0 --> SUBTRACT ADDITIONAL POSITION CENTISECONDS FROM HUNDRED AND USE DIFFERENCE
											} elseif($total_info_centis[$tmem_container[$b]]["Start"] > 0 AND $total_info_centis[$tmem_container[$b]][$additional_pos_stack[$e]] == 0) {
												$result_array_centis[$e] = 100 - $total_info_centis[$tmem_container[$b]][$additional_pos_stack[$e]];
												$result_array_crudes[$e] = ($total_information[$tmem_container[$b]][$additional_pos_stack[$e]] - 1) - $total_information[$tmem_container[$b]]["Start"];
											// ELSEIF START CENTISECONDS ARE GREATER THAN 0 AND ADDITIONAL POSITION CENTISECONDS ARE GREATER THAN 0 --> COMPARE WHICH ONE IS HIGHER (BECAUSE START NEEDS TO BE HIGHER)
											} elseif($total_info_centis[$tmem_container[$b]]["Start"] > 0 AND $total_info_centis[$tmem_container[$b]][$additional_pos_stack[$e]] > 0) {
												if($total_info_centis[$tmem_container[$b]]["Start"] > $total_info_centis[$tmem_container[$b]][$additional_pos_stack[$e]]) {
													// GET ABSOLUTE DIFFERENCE NO MATTER IF RESULT IS NEGATIVE
													$result_array_centis[$e] = 100 - abs($total_info_centis[$tmem_container[$b]][$additional_pos_stack[$e]] - $total_info_centis[$tmem_container[$b]]["Start"]);
													$result_array_crudes[$e] = ($total_information[$tmem_container[$b]][$additional_pos_stack[$e]] - 1) - $total_information[$tmem_container[$b]]["Start"];
												} elseif($total_info_centis[$tmem_container[$b]]["Start"] < $total_info_centis[$tmem_container[$b]][$additional_pos_stack[$e]]) {
													$result_array_centis[$e] = $total_info_centis[$tmem_container[$b]][$additional_pos_stack[$e]] - $total_info_centis[$tmem_container[$b]]["Start"];
													$result_array_crudes[$e] = $total_information[$tmem_container[$b]][$additional_pos_stack[$e]] - $total_information[$tmem_container[$b]]["Start"];
												}
											}
													
											// CONVERT TIMESTAMP TO VALID TIME FORMAT
											$result_array_refine[$e] = formatSeconds($result_array_crudes[$e], $result_array_centis[$e]);
										} else {
											// COVER ARRAY POSITION WITH HINT - NO TIME AVAILABLE
											$result_array_refine[$e] = "Keine Zeit";
										}
									// ITERATION VARIABLE HAS REACHED MAXIMUM ADDITIONAL POSITIONS
									// START GETTING GOAL RESULT AND SUBTRACT
									} elseif($e == count($additional_pos_stack)) {
										// CHECK FOR EMPTY VALUES TO PREVENT CALCULATION WITH ONLY ONE VALUE
										if($total_information[$tmem_container[$b]]["Ziel"] != 0 OR $total_information[$tmem_container[$b]][$additional_pos_stack[$e]] != 0) {
											// SUBTRACT GOAL RESULT AFTER START FROM START
											// CHECK CENTISECONDS VALUE FOR CORRECT CALCULATION (IF 0 THEN SUBTRACT CENTISECONDS FROM 100 AND SUBTRACT ONE SECOND FROM TOTAL SECONDS)
											// IF START CENTISECONDS ARE 0 AND ADDITIONAL POSITION CENTISECONDS ARE 0 --> SUBTRACT NORMALLY
											if($total_info_centis[$tmem_container[$b]]["Start"] == 0 AND $total_info_centis[$tmem_container[$b]]["Ziel"] == 0) {
												$result_array_centis[$e] = $total_info_centis[$tmem_container[$b]]["Ziel"] - $total_info_centis[$tmem_container[$b]]["Start"];
												$result_array_crudes[$e] = $total_information[$tmem_container[$b]]["Ziel"] - $total_information[$tmem_container[$b]]["Start"];
											// ELSEIF START CENTISECONDS ARE 0 AND ADDITIONAL POSITION CENTISECONDS ARE GREATER THAN 0 --> SUBTRACT NORMALLY
											} elseif($total_info_centis[$tmem_container[$b]]["Start"] == 0 AND $total_info_centis[$tmem_container[$b]]["Ziel"] > 0) {
												$result_array_centis[$e] = $total_info_centis[$tmem_container[$b]]["Ziel"] - $total_info_centis[$tmem_container[$b]]["Start"];
												$result_array_crudes[$e] = $total_information[$tmem_container[$b]]["Ziel"] - $total_information[$tmem_container[$b]]["Start"];
											// ELSEIF START CENTISECONDS ARE GREATER THAN 0 AND ADDITIONAL POSITION CENTISECONDS ARE 0 --> SUBTRACT ADDITIONAL POSITION CENTISECONDS FROM HUNDRED AND USE DIFFERENCE
											} elseif($total_info_centis[$tmem_container[$b]]["Start"] > 0 AND $total_info_centis[$tmem_container[$b]]["Ziel"] == 0) {
												$result_array_centis[$e] = 100 - $total_info_centis[$tmem_container[$b]]["Start"];
												$result_array_crudes[$e] = ($total_information[$tmem_container[$b]]["Ziel"] - 1) - $total_information[$tmem_container[$b]]["Start"];
											// ELSEIF START CENTISECONDS ARE GREATER THAN 0 AND ADDITIONAL POSITION CENTISECONDS ARE GREATER THAN 0 --> COMPARE WHICH ONE IS HIGHER (BECAUSE START NEEDS TO BE HIGHER)
											} elseif($total_info_centis[$tmem_container[$b]]["Start"] > 0 AND $total_info_centis[$tmem_container[$b]]["Ziel"] > 0) {
												if($total_info_centis[$tmem_container[$b]]["Start"] > $total_info_centis[$tmem_container[$b]]["Ziel"]) {
													// GET ABSOLUTE DIFFERENCE NO MATTER IF RESULT IS NEGATIVE
													$result_array_centis[$e] = 100 - abs($total_info_centis[$tmem_container[$b]]["Ziel"] - $total_info_centis[$tmem_container[$b]]["Start"]);
													$result_array_crudes[$e] = ($total_information[$tmem_container[$b]]["Ziel"] - 1) - $total_information[$tmem_container[$b]]["Start"];
												} elseif($total_info_centis[$tmem_container[$b]]["Start"] < $total_info_centis[$tmem_container[$b]]["Ziel"]) {
													$result_array_centis[$e] = $total_info_centis[$tmem_container[$b]]["Ziel"] - $total_info_centis[$tmem_container[$b]]["Start"];
													$result_array_crudes[$e] = $total_information[$tmem_container[$b]]["Ziel"] - $total_information[$tmem_container[$b]]["Start"];
												}
											}
											
											// CONVERT TIMESTAMP TO VALID TIME FORMAT
											$result_array_refine[$e] = formatSeconds($result_array_crudes[$e], $result_array_centis[$e]);
										} else {
											// COVER ARRAY POSITION WITH HINT - NO TIME AVAILABLE
											$result_array_refine[$e] = "Keine Zeit";
										}
									}
								} elseif($getrow_wpt['total_pos'] == 2) {
									// CHECK FOR EMPTY VALUES TO PREVENT CALCULATION WITH ONLY ONE VALUE
									if($total_information[$tmem_container[$b]]["Ziel"] != 0) {
										// SUBTRACT GOAL RESULT AFTER START FROM START
										// CHECK CENTISECONDS VALUE FOR CORRECT CALCULATION (IF 0 THEN SUBTRACT CENTISECONDS FROM 100 AND SUBTRACT ONE SECOND FROM TOTAL SECONDS)
										// IF START CENTISECONDS ARE 0 AND ADDITIONAL POSITION CENTISECONDS ARE 0 --> SUBTRACT NORMALLY
										if($total_info_centis[$tmem_container[$b]]["Start"] == 0 AND $total_info_centis[$tmem_container[$b]]["Ziel"] == 0) {
											$result_array_centis[$e] = $total_info_centis[$tmem_container[$b]]["Ziel"] - $total_info_centis[$tmem_container[$b]]["Start"];
											$result_array_crudes[$e] = $total_information[$tmem_container[$b]]["Ziel"] - $total_information[$tmem_container[$b]]["Start"];
										// ELSEIF START CENTISECONDS ARE 0 AND ADDITIONAL POSITION CENTISECONDS ARE GREATER THAN 0 --> SUBTRACT NORMALLY
										} elseif($total_info_centis[$tmem_container[$b]]["Start"] == 0 AND $total_info_centis[$tmem_container[$b]]["Ziel"] > 0) {
											$result_array_centis[$e] = $total_info_centis[$tmem_container[$b]]["Ziel"] - $total_info_centis[$tmem_container[$b]]["Start"];
											$result_array_crudes[$e] = $total_information[$tmem_container[$b]]["Ziel"] - $total_information[$tmem_container[$b]]["Start"];
										// ELSEIF START CENTISECONDS ARE GREATER THAN 0 AND ADDITIONAL POSITION CENTISECONDS ARE 0 --> SUBTRACT ADDITIONAL POSITION CENTISECONDS FROM HUNDRED AND USE DIFFERENCE
										} elseif($total_info_centis[$tmem_container[$b]]["Start"] > 0 AND $total_info_centis[$tmem_container[$b]]["Ziel"] == 0) {
											$result_array_centis[$e] = 100 - $total_info_centis[$tmem_container[$b]]["Start"];
											$result_array_crudes[$e] = ($total_information[$tmem_container[$b]]["Ziel"] - 1) - $total_information[$tmem_container[$b]]["Start"];
										// ELSEIF START CENTISECONDS ARE GREATER THAN 0 AND ADDITIONAL POSITION CENTISECONDS ARE GREATER THAN 0 --> COMPARE WHICH ONE IS HIGHER (BECAUSE START NEEDS TO BE HIGHER)
										} elseif($total_info_centis[$tmem_container[$b]]["Start"] > 0 AND $total_info_centis[$tmem_container[$b]]["Ziel"] > 0) {
											if($total_info_centis[$tmem_container[$b]]["Start"] > $total_info_centis[$tmem_container[$b]]["Ziel"]) {
												// GET ABSOLUTE DIFFERENCE NO MATTER IF RESULT IS NEGATIVE
												$result_array_centis[$e] = 100 - abs($total_info_centis[$tmem_container[$b]]["Ziel"] - $total_info_centis[$tmem_container[$b]]["Start"]);
												$result_array_crudes[$e] = ($total_information[$tmem_container[$b]]["Ziel"] - 1) - $total_information[$tmem_container[$b]]["Start"];
											} elseif($total_info_centis[$tmem_container[$b]]["Start"] < $total_info_centis[$tmem_container[$b]]["Ziel"]) {
												$result_array_centis[$e] = $total_info_centis[$tmem_container[$b]]["Ziel"] - $total_info_centis[$tmem_container[$b]]["Start"];
												$result_array_crudes[$e] = $total_information[$tmem_container[$b]]["Ziel"] - $total_information[$tmem_container[$b]]["Start"];
											}
										}
											
										// CONVERT TIMESTAMP TO VALID TIME FORMAT
										@$result_array_refine[$e] = formatSeconds($result_array_crudes[$e], $result_array_centis[$e]);	
									} else {
										// COVER ARRAY POSITION WITH HINT - NO TIME AVAILABLE
										$result_array_refine[$e] = "Keine Zeit";
									}
								}																
							// NO START RESULT SET
							} elseif($total_information[$tmem_container[$b]]["Start"] == "" OR empty($total_information[$tmem_container[$b]]["Start"])) {
								// COVER ARRAY POSITION WITH HINT - NO TIME AVAILABLE
								$result_array_refine[$e] = "Keine Zeit";
							}
						}
							
						// BUILD TABLE ROW						
						echo	'
										<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1">' . $result_array_refine[$e] . '</font></td>
								';
						
						/*
							CALCULATES ONLY START AND GOAL /!\
						*/
						
						// GET CALCULATED TIME
						// MAKE SURE, VALUE IS NOT "KEINE ZEIT"
						if($result_array_refine[$e] != "Keine Zeit") {
							if(
								($total_information[$tmem_container[$b]]["Start"] != "" OR !empty($total_information[$tmem_container[$b]]["Start"])) 	AND 
								($total_information[$tmem_container[$b]]["Ziel"] != "" OR !empty($total_information[$tmem_container[$b]]["Ziel"]))
							) {
								// CONVERT TARGET TIME TO SECONDS
								$target_split = explode(":", $target_time_container[$e]);
								$target_seconds = intval(($target_split[0] * 60) + $target_split[1]);
									
								/*
								// GET CONVERTED RESULT AND SPLIT TO GET MINUTE, SECONDS AND CENTISECONDS
								$result_split_1 = explode(":", $result_array_refine[$e]);
								$result_split_2 = explode(".", $result_split_1[2]);
								$result_seconds = (intval($result_split_2[0]) * 60) + intval($result_split_2[1]);
								
								@$result_second = abs($result_seconds - $target_seconds);
								@$result_centi = $result_array_centis[$e];
								*/
								if($target_seconds > $result_array_crudes[$e]) {
									@$result_second = abs($result_array_crudes[$e] - $target_seconds) - 1;
									@$result_centi = 100 - abs($result_array_centis[$e]);
								} elseif($target_seconds < $result_array_crudes[$e]) {
									@$result_second = abs($result_array_crudes[$e] - $target_seconds);
									@$result_centi = abs($result_array_centis[$e]);
								} elseif($target_seconds == $result_array_crudes[$e]) {
									@$result_second = abs($result_array_crudes[$e] - $target_seconds);
									@$result_centi = abs($result_array_centis[$e]);
								}
								
								$result_array_tartim[$e] = formatSeconds($result_second, $result_centi);
								
								// BUILD TABLE ROW						
								echo	'
												<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1">' . $result_array_tartim[$e] . '</font></td>
										';
							} else {
								// BUILD TABLE ROW						
								echo	'
											<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1">&mdash;&mdash;&mdash;</font></td>
										';
							}
						} else {
							// BUILD TABLE ROW						
							echo	'
											<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1">&mdash;&mdash;&mdash;</font></td>
									';
						}
					}
					
					// ONLY SHOW TOTAL DIFF IF THERE ARE ADDITIONAL POSITIONS
					if($getrow_wpt['total_pos'] > 2) {
						echo	'
										<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1">00:00:00,00</font></td>									
										<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1">ges.</font></td>
								';
					}
					// END BUILDING ROW FOR CURRENT TMEMBER
					echo	'
								</tr>
							';
				}
				
				// FINALIZE TABLE
				echo	'
							</table>
						';			
			} else {
				echo 	'
							<table width="100%" cellspacing="5px" style="border: 1px solid #9E9482;">
								<tr id="table_status">
									<td align="center" colspan="2">Keine Rennergebnisse verfügbar</td>
								</tr>
							</table>
						';
			}
		} elseif($numrow_sprint == 1) {
			// START BUILDING TABLE IF RESULTS HAVE BEEN FOUND
			if($numrow_rd > 0) {
				// BUILD TABLE HEADER
				echo	'
							<table width="100%" cellpadding="5px" cellspacing="0" style="border: 1px solid #FFFFFF;">
								<tr id="table_status">
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1"><strong>#</strong></font></td>
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1"><strong>Fahrtzeit</strong></font></td>
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1"><strong>Abweichung</strong></font></td>
								</tr>
						';
						
				// INITIALIZE ARRAY CONTAINER
				$result_array_centis = array();		
				
				// GET TMEMBERS LIST AND SAVE AS ARRAY
				$select_tmem = "SELECT * FROM _optio_tmembers WHERE `eid` = '" . $eid . "' ORDER BY `sid` ASC";
				$result_tmem = mysqli_query($mysqli, $select_tmem);
				$numrow_tmem = mysqli_num_rows($result_tmem);
							
				// WHILE LOOP FOR STORING EVERY TMEMBER IN ARRAY
				while($getrow_tmem = mysqli_fetch_assoc($result_tmem)) {
					$tmem_container[] = $getrow_tmem['sid'];
				}
				
				// MAIN LOOP FOR BUILDING TABLE CONTAINER RESULTS
				for($b = 0; $b < count($tmem_container); $b++) {
					// GET EVERY REALTIME FOR TMEMBERS
					$select_realtime = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $tmem_container[$b] . "'";
					$result_realtime = mysqli_query($mysqli, $select_realtime);
					$getrow_realtime = mysqli_fetch_assoc($result_realtime);
					$real_time_container[$b] = $getrow_realtime['t_realtime'];	
					$result_array_centis[$b] = $getrow_realtime['t_centi'];
					$total_information[$b] = $getrow_realtime['t_time'];

					// MAKE SURE, TIMES ARE VALID
					if($real_time_container[$b] != 0 OR !empty($real_time_container[$b])) {
						// CONVERT TARGET TIME TO SECONDS
						$target_split = explode(":", $target_time_container[0]);
						$target_seconds = intval(($target_split[0] * 60) + $target_split[1]);
						
						if($target_seconds > @$total_information[$b]) {
							@$result_second = abs($total_information[$b] - $target_seconds) - 1;
							@$result_centi = 100 - abs($result_array_centis[$b]);
							$result_array_tartim[$b] = formatSeconds($result_second, $result_centi);
						} elseif($target_seconds < $total_information[$b]) {
							@$result_second = abs($total_information[$b] - $target_seconds);
							@$result_centi = abs($result_array_centis[$b]);
							$result_array_tartim[$b] = formatSeconds($result_second, $result_centi);
						} elseif($target_seconds == $total_information[$b]) {
							@$result_second = abs($total_information[$b] - $target_seconds);
							@$result_centi = abs($result_array_centis[$b]);
							$result_array_tartim[$b] = formatSeconds($result_second, $result_centi);
						}					
					} else {
						$real_time_container[$b] = "Keine Zeit";
						$result_array_tartim[$b] = "&mdash;&mdash;&mdash;";						
					}
					
								
					// TABLE ROW START ID
					echo	'
									<tr>
										<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1">' . $tmem_container[$b] . '</font></td>
										<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1">' . $real_time_container[$b] . '</font></td>
										<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482;"><font size="1">' . $result_array_tartim[$b] . '</font></td>
									</tr>
							';
				}
						
				echo	'
								</tr>
							</table>
						';
			} else {
				echo 	'
							<table width="100%" cellspacing="5px" style="border: 1px solid #9E9482;">
								<tr id="table_status">
									<td align="center" colspan="2">Keine Rennergebnisse verfügbar</td>
								</tr>
							</table>
						';
			}
		}
    }
?>