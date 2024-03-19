<?
	if(isset($_POST['eid']) AND isset($_POST['sid'])) {
		// INCLUDE FUNCTIONS
		include_once 'includes/functions.php';
		
		// INCLUDE DB_CONNECT
		include_once 'includes/db_connect.php';
		
		// SANITIZE AND REWIRTE POST
		$eid = htmlspecialchars(stripslashes(trim($_POST['eid'])));
		$sid = htmlspecialchars(stripslashes(trim($_POST['sid'])));
		$eid = mysqli_real_escape_string($mysqli, utf8_encode($eid));
		$sid = mysqli_real_escape_string($mysqli, utf8_encode($sid));
		
		// GET TODAY'S DATE (Ymd) AND COMPARE WITH EVENT ENDING
		$today = date('Y-m-d', time());
		
		$select_event = "SELECT `eid`, `end` FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `active` = '1'";
		$result_event = mysqli_query($mysqli, $select_event);
		$numrow_event = mysqli_num_rows($result_event);
		$getrow_event = mysqli_fetch_assoc($result_event);
		
		$end = $getrow_event['end'];

		// TABLE HEADER SECTION
		// GET TOTAL AMOUNT OF ROUNDS
		$select_rounds = "SELECT `eid`, `rid_type`, `rid`, `total_pos`, `toggle_secret`, `secret` FROM `_main_wptable` WHERE `eid` = '" . $eid . "'";
		$result_rounds = mysqli_query($mysqli, $select_rounds);
		$numrow_rounds = mysqli_num_rows($result_rounds);
			
		// CREATE TABLE BUILD VARIABLE
		$tb = "";
		
		// CHECK IF THERE ARE ROUNDS TO BE DISPLAYED
		if($numrow_rounds > 0) {
			if($numrow_rounds == 1) {
				$exams = "Prüfung";
			} elseif($numrow_rounds > 1) {
				$exams = "Prüfungen";
			}
			
			// HEADER SECTION
			$tb =	"
						<table cellspacing=\"5px\" cellpadding=\"0\" style=\"border: 1px solid #FFFFFF;\" width=\"100%\">
							<tr>
								<td colspan=\"5\" style=\"border-bottom: 1px solid #FFF;\"><font size=\"4\"><strong>" . $exams . "</strong></font></td>
							</tr>
							<tr>
					";
			//	Manuelle Zählervariable
			$i = 1;
			
			// LOOP THROUGH EACH ROUND
			while($getrow_rounds = mysqli_fetch_assoc($result_rounds)) {
				// FETCH ROUND TYPE
				$rid_type = $getrow_rounds['rid_type'];
				$rd_scrt = $getrow_rounds['secret'];
				$rd_scrt_toggle = $getrow_rounds['toggle_secret'];
			
				// RESET STATUS COLOR
				$status = "";
				
				// CHECK FOR SPECIAL STATUS (E. G. FROM EVENT HANDLER --> DISABLED)
				$select_special = "SELECT * FROM _optio_tmembers_lock WHERE `eid` = '" . $eid . "' AND `sid` = '" . $sid . "' AND `rid` = '" . $i . "'";
				$result_special = mysqli_query($mysqli, $select_special);
				$numrow_special = mysqli_num_rows($result_special);
				
			   	if($numrow_special == 0) {
					$special_status = "";
				} elseif($numrow_special == 1) {
					$special_status = "no";
				}
			
				// GET TOTAL AMOUNT OF ROUNDS
				$select_rndpos = "SELECT `eid`, `rid`, `total_pos`, `z_entry` FROM `_main_wptable` WHERE `eid` = '" . $eid . "' AND `rid` = '" . $i . "'";
				$result_rndpos = mysqli_query($mysqli, $select_rndpos);
				$getrow_rndpos = mysqli_fetch_assoc($result_rndpos);
				$getrow_totpos = $getrow_rndpos['total_pos'];
				$getrow_postyp = $getrow_rndpos['z_entry'];
				
				// GET TOTAL AMOUNT OF TMEMBER RESULTS
				$select_result = "SELECT `eid`, `rid`, `sid` FROM `_main_wpresults` WHERE `eid` = '" . $eid . "' AND `rid` = '" . $i . "' AND `sid` = '" . $sid . "'";
				$result_result = mysqli_query($mysqli, $select_result);
				$numrow_result = mysqli_num_rows($result_result);
				
				// NO SPECIAL STATUS, PROCEED WITH RESULT AMOUNT BASED STATUS CHECK
				if($special_status == "" OR empty($special_status)) {			
					// CHECK STATUS
					// NO START
					if($numrow_result == 0) {
						$ready_status = "yes";
					// PENDING
					} elseif($numrow_result > 0 AND $numrow_result <= ($getrow_totpos - 1)) {
						$ready_status = "pen";
					// FINISHED
					} elseif($numrow_result == $getrow_totpos) {
						$ready_status = "fin";
					}
				// SPECIAL STATUS FOUND
				} elseif($special_status != "" OR !empty($special_status)) {					
					switch($special_status) {
						case "no":
							$ready_status = "out";
							break;
						default:
							$ready_status = "out";
							break;
					}
				}
				
				// DECLARE STATUS COLORS
				// STATUS OKAY
				if($ready_status == "yes") {
					$status = "#A09A8E";
					$symbol = "<span style='font-size: x-small;'>&#10008;</span>";
				// STATUS PENDING
				} elseif($ready_status == "pen") {
					$status = "#FFFF00";
					$symbol = "<span style='font-size: x-small;'>&#8987;</span>";
				// STATUS FINISHED
				} elseif($ready_status == "fin") {
					$status = "#00FF00";
					$symbol = "<span style='font-size: x-small;'>&#10004;</span>";
				// STATUS ERROR
				} elseif($ready_status == "out") {
					$status = "#FF0000";					
					$symbol = "<span style='font-size: x-small;'>&#9888;</span>";
				}
				
				// BEGINN WITH CALCULATIONS IF ROUND HAS BEEN FINISHED
				if($ready_status == "fin" || $status == "#00FF00") {
					// CHECK FOR REGULAR OR SPRINT ROUND
					// ROUND IS REGUALR
					if($getrow_postyp == 0) {
						// CHECK FOR TOTAL POSITION
						// ONLY START AND GOAL
						if($getrow_totpos == 2) {
							// Hole Sollzeit für aktuelle Prüfung
							$select_sollzeit = "SELECT * FROM _main_wptable_sz WHERE `eid` = '" . $eid . "' AND `rid` = '" . $i . "'";
							$result_sollzeit = mysqli_query($mysqli, $select_sollzeit);
							$getrow_sollzeit = mysqli_fetch_assoc($result_sollzeit);
								
							// Weise Spaltennamen der entsprechenden Variable zu
							$sollzeit = $getrow_sollzeit['sz'];
								
							// Konvertiere in Echtzeit gespeicherte Sollzeit in Sekunden
							$sollzeit_split_1 = explode(":", $sollzeit);
							/*
							[0] => hh
							[1] => mm
							[2] => ss,uu
							*/
							$sollzeit_split_2 = explode(",", $sollzeit_split_1[2]);
							/*
							[0] => ss
							[1] => uu
							*/
							
							// Dezimales Format => ss,uu => Bsp. 00:02:45,00 => 165,00s
							$sollzeit_sekunden = ($sollzeit_split_1[1] * 60) + $sollzeit_split_2[0] . "." . $sollzeit_split_2[1];
							
							// Suche nach Startzeit für aktuellen Teilnehmer
							$select_pre = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $i . "' AND `sid` = '" . $sid . "' AND `position` = 'Start'";
							$result_pre = mysqli_query($mysqli, $select_pre);
							$numrow_pre = mysqli_num_rows($result_pre);
							$getrow_pre = mysqli_fetch_assoc($result_pre);
							
							// Startzeit wurde gefunden (darf maximal 1 sein!)
							if($numrow_pre == 1) {
								// Speichere als dezimale Sekunden in jeweiligen Arrays
								$zeit_als_dezimal["pre"] = $getrow_pre['t_time'] . "." . $getrow_pre['t_centi'];
												
								/*
								Zweidimensionales Array [x][y]
								pre = Start
								seq = Ziel
								Array	[
											[1] => Array	[
																["Start"]	=> Dezimal Zeit
																["Ziel"]	=> Dezimal Zeit
															]
										]
								*/
							// Es wurde keine Startzeit gefunden
							} elseif($numrow_pre == 0) {
								// Speichere als dezimale Sekunden in jeweiligen Arrays
								$zeit_als_dezimal["pre"] = 0;
							}
							
							// Suche nach Zielzeit für aktuellen Teilnehmer
							$select_seq = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $i . "' AND `sid` = '" . $sid . "' AND `position` = 'Ziel'";
							$result_seq = mysqli_query($mysqli, $select_seq);
							$numrow_seq = mysqli_num_rows($result_seq);
							$getrow_seq = mysqli_fetch_assoc($result_seq);
								
							// Zielzeit wurde gefunden (darf maximal 1 sein!)
							if($numrow_seq == 1) {
								// Speichere als dezimale Sekunden in jeweiligen Arrays
								$zeit_als_dezimal["seq"] = $getrow_seq['t_time'] . "." . $getrow_seq['t_centi'];
				
								/*
								Zweidimensionales Array [x][y]
								Pre = Start
								Seq = Ziel
								Array	[
											[1] => Array	[
																["Start"]	=> Dezimal Zeit
																["Ziel"]	=> Dezimal Zeit
															]
										]
								*/
							// Es wurde keine Zielzeit gefunden
							} elseif($numrow_seq == 0) {
								// Speichere als dezimale Sekunden in jeweiligen Arrays
								$zeit_als_dezimal["seq"] = 0;
							}
							
							// Starte mit der Berechnung, vorausgesetzt es handelt sich nicht um Leerwerte
							if($zeit_als_dezimal["pre"] != 0 AND $zeit_als_dezimal["seq"] != 0) {
								// Berechne Fahrtzeit
								$fahrtzeit_dezimal = abs((float)$zeit_als_dezimal["seq"] - (float)$zeit_als_dezimal["pre"]);
								$fahrtzeit_dezimal = number_format((float)$fahrtzeit_dezimal, 2, '.', '');
								
								// Übergebe Fahrtzeit an Funktion zur Konvertierung
								$fahrtzeit_konvert = convertTimeRacer($fahrtzeit_dezimal);
								
								// Berechne Abweichung
								$abweichung_dezimal = abs((float)$fahrtzeit_dezimal - (float)$sollzeit_sekunden);
								$abweichung_dezimal = number_format((float)$abweichung_dezimal, 2, '.', '');
								
								// Übergebe Fahrtzeit an Funktion zur Konvertierung
								$abweichung_konvert = convertTimeRacer($abweichung_dezimal);
								$content = $abweichung_konvert;
							// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
							} else {
								// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
								$content	= "&mdash;&mdash;&mdash;";
							}
						// ROUND HAS ADDITIONAL POSITIONS 
						} elseif($getrow_totpos > 2) {
							// Erstelle zusätzliche Array zum Speichern der Gesamtfahrtzeit und -abweichung
							$fahrtzeit_dezimal_gesamt = array();
							$abweichung_dezimal_gesamt = array();
							
							// Hole Sollzeiten und speichere diese in Array
							$select_sollzeit = "SELECT * FROM _main_wptable_sz WHERE `eid` = '" . $eid . "' AND `rid` = '" . $i . "' ORDER BY `sz_cid` ASC";
							$result_sollzeit = mysqli_query($mysqli, $select_sollzeit);
							
							// Erstelle Arrays zum Speichern von Sollzeiten
							$sollzeit_konvert = array();
							$sollzeit_sekunde = array();
							
							while($getrow_sollzeit = mysqli_fetch_assoc($result_sollzeit)) {
								// Weise Sollzeiten zu
								$sollzeit_konvert[] = $getrow_sollzeit['sz'];
							}
							
							// Wandel konvertierte Sollzeiten in Sekunden um
							for($x = 0; $x < count($sollzeit_konvert); $x++) {
								// START CONVERTING TARGET TIME IN SECONDS
								$target_split_1 = explode(":", $sollzeit_konvert[$x]);
								/*
								[0] => hh
								[1] => mm
								[2] => ss,uu
								*/
								$target_split_2 = explode(",", $target_split_1[2]);
								/*
								[0] => ss
								[1] => uu
								*/
								$sollzeit_sekunde[$x] = ($target_split_1[1] * 60) + $target_split_2[0] . "." . $target_split_2[1];
							}
							
							$select_calc = "SELECT `eid`, `t_calc` FROM _race_run_events WHERE `eid` = '" . $eid . "' AND `active` = '1'";
							$result_calc = mysqli_query($mysqli, $select_calc);
							$getrow_calc = mysqli_fetch_assoc($result_calc);
								
							// Weise Berechnungsart zu
							$rd_calc = $getrow_calc['t_calc'];
							
							// Hole Ergebnis für Start für aktuellen Teilnehmer
							$select_pre = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $i . "' AND `sid` = '" . $sid . "' AND `position` = 'Start'";
							$result_pre = mysqli_query($mysqli, $select_pre);
							$numrow_pre = mysqli_num_rows($result_pre);
							$getrow_pre = mysqli_fetch_assoc($result_pre);
							
							// Startzeit wurde gefunden (darf maximal 1 sein!)
							if($numrow_pre == 1) {
								// Speichere als dezimale Sekunden in jeweiligen Arrays
								$zeit_als_dezimal["pre"] = $getrow_pre['t_time'] . "." . $getrow_pre['t_centi'];
							// Startzeit wurde nicht gefunden
							} elseif($numrow_pre == 0) {
								// Speichere als dezimale Sekunden in jeweiligen Arrays
								$zeit_als_dezimal["pre"] = 0;
							}
							
							// Hole Ergebnis für jede Zwischenzeit für aktuellen Teilnehmer
							for($c = 0; $c < ($getrow_totpos - 2); $c++) {
								$select_opt = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $i . "' AND `sid` = '" . $sid . "' AND `position` = 'ZZ" . ($c + 1) . "'";
								$result_opt = mysqli_query($mysqli, $select_opt);
								$numrow_opt = mysqli_num_rows($result_opt);
								$getrow_opt = mysqli_fetch_assoc($result_opt);
															
								// Zwischenzeit X wurde gefunden
								if($numrow_opt == 1) {
									// Speichere als dezimale Sekunden in jeweiligen Arrays
									$zeit_als_dezimal["zz" . ($c + 1)] = $getrow_opt['t_time'] . "." . $getrow_opt['t_centi'];
								// Zwischenzeit X wurde nicht gefunden
								} elseif($numrow_opt == 0) {
									// Speichere als dezimale Sekunden in jeweiligen Arrays
									$zeit_als_dezimal["zz" . ($c + 1)] = 0;
								}
							}
							
							// Hole Ergebnis für Ziel für aktuellen Teilnehmer
							$select_seq = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $i . "' AND `sid` = '" . $sid . "' AND `position` = 'Ziel'";
							$result_seq = mysqli_query($mysqli, $select_seq);
							$numrow_seq = mysqli_num_rows($result_seq);
							$getrow_seq = mysqli_fetch_assoc($result_seq);
							
							// Zielzeit wurde gefunden (darf maximal 1 sein!)
							if($numrow_seq == 1) {
								// Speichere als dezimale Sekunden in jeweiligen Arrays
								$zeit_als_dezimal["seq"] = $getrow_seq['t_time'] . "." . $getrow_seq['t_centi'];
							// Zielzeit wurde nicht gefunden
							} elseif($numrow_seq == 0) {
								// Speichere als dezimale Sekunden in jeweiligen Arrays
								$zeit_als_dezimal["seq"] = 0;
							}
							
							// Beginne vollständige Berechnungen unter Berücksichtigung der Berechnungsart
							// Minus 1, da Aus 2 Zeiten 1 Differenz wird
							for($d = 0; $d < ($getrow_totpos - 1); $d++) {
								// Prüfe auf Berechnungstyp der Runden-ID
								/*
									1 =>	Ab Start 
											(ZZ1 - Start)
											( + (ZZX - Start))
											+ (Ziel - Start)
									2 =>	Einzeln
											(ZZ1 - Start)
											( + (ZZ2 - ZZ1))
											( + (ZZX - ZZX-1))
											+ (Ziel - ZZX bzw ZZ1)
									3 =>	Einzeln Differenz verrechnet (n. n. implementiert)
											(ZZ1 - Start)
											( +/- (ZZ2 - ZZ1))
											( +/- (ZZX - ZZX-1))
											+/- (Ziel - ZZX bzw ZZ1)
								*/								
								if($rd_calc == 1) {
									// Beginne bei erster Zwischenzeit abzgl. Startzeit
									if($d >= 0 AND $d < (($getrow_totpos - 1) - 1)) {
										// Starte mit der Berechnung, vorausgesetzt es handelt sich nicht um Leerwerte
										if($zeit_als_dezimal["pre"] != 0 AND $zeit_als_dezimal["zz" . ($d + 1)] != 0) {
											// Berechne Fahrtzeit
											$fahrtzeit_dezimal = abs((float)$zeit_als_dezimal["zz" . ($d + 1)] - (float)$zeit_als_dezimal["pre"]);
											$fahrtzeit_dezimal = number_format((float)$fahrtzeit_dezimal, 2, '.', '');
												
											// Addiere Fahrtzeit Dezimal zur Gesamtfahrtzeit
											@$fahrtzeit_dezimal_gesamt = abs((float)$fahrtzeit_dezimal_gesamt + (float)$fahrtzeit_dezimal);
											$fahrtzeit_dezimal_gesamt = number_format((float)$fahrtzeit_dezimal_gesamt, 2, '.', '');
											
											// Berechne Abweichung
											$abweichung_dezimal = abs((float)$fahrtzeit_dezimal - (float)$sollzeit_sekunde[$d]);
											$abweichung_dezimal = number_format((float)$abweichung_dezimal, 2, '.', '');
												
											// Addiere Abweichung Dezimal zur Gesamtabweichung
											@$abweichung_dezimal_gesamt = abs((float)$abweichung_dezimal_gesamt + (float)$abweichung_dezimal);
											$abweichung_dezimal_gesamt = number_format((float)$abweichung_dezimal_gesamt, 2, '.', '');
											
											// Konvertiere Gesamtabweichung
											$abweichung_konvert_gesamt = convertTimeRacer($abweichung_dezimal_gesamt);
											$content = $abweichung_konvert_gesamt;
										// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
										} else {
											$content = "&mdash;&mdash;&mdash;";
										}								
									// Ende mit Differenz aus Zielzeit abzgl. Startzeit
									} elseif($d >= 0 AND $d == (($getrow_totpos - 1) - 1)) {
										// Starte mit der Berechnung, vorausgesetzt es handelt sich nicht um Leerwerte
										if($zeit_als_dezimal["pre"] != 0 AND $zeit_als_dezimal["seq"] != 0) {
											// Berechne Fahrtzeit
											$fahrtzeit_dezimal = abs((float)$zeit_als_dezimal["seq"] - (float)$zeit_als_dezimal["pre"]);
											$fahrtzeit_dezimal = number_format((float)$fahrtzeit_dezimal, 2, '.', '');
												
											// Addiere Fahrtzeit Dezimal zur Gesamtfahrtzeit
											$fahrtzeit_dezimal_gesamt = abs((float)$fahrtzeit_dezimal_gesamt + (float)$fahrtzeit_dezimal);
											$fahrtzeit_dezimal_gesamt = number_format((float)$fahrtzeit_dezimal_gesamt, 2, '.', '');
											
											// Berechne Abweichung
											$abweichung_dezimal = abs((float)$fahrtzeit_dezimal - (float)$sollzeit_sekunde[$d]);
											$abweichung_dezimal = number_format((float)$abweichung_dezimal, 2, '.', '');
												
											// Addiere Abweichung Dezimal zur Gesamtabweichung
											$abweichung_dezimal_gesamt = abs((float)$abweichung_dezimal_gesamt + (float)$abweichung_dezimal);
											$abweichung_dezimal_gesamt = number_format((float)$abweichung_dezimal_gesamt, 2, '.', '');	
											
											// Konvertiere Gesamtabweichung
											$abweichung_konvert_gesamt = convertTimeRacer($abweichung_dezimal_gesamt);
											$content = $abweichung_konvert_gesamt;
										// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
										} else {
											$content = "&mdash;&mdash;&mdash;";
										}
									}
								} elseif($rd_calc == 2) {
									// Beginne bei erster Zwischenzeit abzgl. Startzeit
									if($d == 0) {
										// Starte mit der Berechnung, vorausgesetzt es handelt sich nicht um Leerwerte
										if($zeit_als_dezimal["pre"] != 0 AND $zeit_als_dezimal["zz" . ($d + 1)] != 0) {
											// Berechne Fahrtzeit
											$fahrtzeit_dezimal = abs((float)$zeit_als_dezimal["zz" . ($d + 1)] - (float)$zeit_als_dezimal["pre"]);
											$fahrtzeit_dezimal = number_format((float)$fahrtzeit_dezimal, 2, '.', '');
												
											// Addiere Fahrtzeit Dezimal zur Gesamtfahrtzeit
											@$fahrtzeit_dezimal_gesamt = abs((float)$fahrtzeit_dezimal_gesamt + (float)$fahrtzeit_dezimal);
											$fahrtzeit_dezimal_gesamt = number_format((float)$fahrtzeit_dezimal_gesamt, 2, '.', '');
											
											// Berechne Abweichung
											$abweichung_dezimal = abs((float)$fahrtzeit_dezimal - (float)$sollzeit_sekunde[$d]);
											$abweichung_dezimal = number_format((float)$abweichung_dezimal, 2, '.', '');
												
											// Addiere Abweichung Dezimal zur Gesamtabweichung
											@$abweichung_dezimal_gesamt = abs((float)$abweichung_dezimal_gesamt + (float)$abweichung_dezimal);
											$abweichung_dezimal_gesamt = number_format((float)$abweichung_dezimal_gesamt, 2, '.', '');
											
											// Konvertiere Gesamtabweichung
											$abweichung_konvert_gesamt = convertTimeRacer($abweichung_dezimal_gesamt);
											$content = $abweichung_konvert_gesamt;
										// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
										} else {
											$content = "&mdash;&mdash;&mdash;";
										}
									// Verrechnene Zwischenzeiten miteinander (ZZX - ZZX-1)
									} elseif($d >= 1 AND $d < (($getrow_totpos - 1) - 1)) {
										// Starte mit der Berechnung, vorausgesetzt es handelt sich nicht um Leerwerte
										if($zeit_als_dezimal["zz" . $d] != 0 AND $zeit_als_dezimal["zz" . ($d + 1)] != 0) {
											// Berechne Fahrtzeit
											$fahrtzeit_dezimal = abs((float)$zeit_als_dezimal["zz" . ($d + 1)] - (float)$zeit_als_dezimal["zz" . $d]);
											$fahrtzeit_dezimal = number_format((float)$fahrtzeit_dezimal, 2, '.', '');
												
											// Addiere Fahrtzeit Dezimal zur Gesamtfahrtzeit
											$fahrtzeit_dezimal_gesamt = abs((float)$fahrtzeit_dezimal_gesamt + (float)$fahrtzeit_dezimal);
											$fahrtzeit_dezimal_gesamt = number_format((float)$fahrtzeit_dezimal_gesamt, 2, '.', '');
											
											// Berechne Abweichung
											$abweichung_dezimal = abs((float)$fahrtzeit_dezimal - (float)$sollzeit_sekunde[$d]);
											$abweichung_dezimal = number_format((float)$abweichung_dezimal, 2, '.', '');
												
											// Addiere Abweichung Dezimal zur Gesamtabweichung
											$abweichung_dezimal_gesamt = abs((float)$abweichung_dezimal_gesamt + (float)$abweichung_dezimal);
											$abweichung_dezimal_gesamt = number_format((float)$abweichung_dezimal_gesamt, 2, '.', '');	
											
											// Konvertiere Gesamtabweichung
											$abweichung_konvert_gesamt = convertTimeRacer($abweichung_dezimal_gesamt);
											$content = $abweichung_konvert_gesamt;
										// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
										} else {
											$content = "&mdash;&mdash;&mdash;";
										}										
									// Ende mit Differenz aus Zielzeit abzgl. vorangegangener Zwischenzeit
									} elseif($d > 0 AND $d == (($getrow_totpos - 1) - 1)) {
										// Starte mit der Berechnung, vorausgesetzt es handelt sich nicht um Leerwerte
										if($zeit_als_dezimal["zz" . $d] != 0 AND $zeit_als_dezimal["seq"] != 0) {
											// Berechne Fahrtzeit
											$fahrtzeit_dezimal = abs((float)$zeit_als_dezimal["seq"] - (float)$zeit_als_dezimal["zz" . $d]);
											$fahrtzeit_dezimal = number_format((float)$fahrtzeit_dezimal, 2, '.', '');
												
											// Addiere Fahrtzeit Dezimal zur Gesamtfahrtzeit
											$fahrtzeit_dezimal_gesamt = abs((float)$fahrtzeit_dezimal_gesamt + (float)$fahrtzeit_dezimal);
											$fahrtzeit_dezimal_gesamt = number_format((float)$fahrtzeit_dezimal_gesamt, 2, '.', '');
											
											// Berechne Abweichung
											$abweichung_dezimal = abs((float)$fahrtzeit_dezimal - (float)$sollzeit_sekunde[$d]);
											$abweichung_dezimal = number_format((float)$abweichung_dezimal, 2, '.', '');
												
											// Addiere Abweichung Dezimal zur Gesamtabweichung
											$abweichung_dezimal_gesamt = abs((float)$abweichung_dezimal_gesamt + (float)$abweichung_dezimal);
											$abweichung_dezimal_gesamt = number_format((float)$abweichung_dezimal_gesamt, 2, '.', '');	
											
											// Konvertiere Gesamtabweichung
											$abweichung_konvert_gesamt = convertTimeRacer($abweichung_dezimal_gesamt);
											$content = $abweichung_konvert_gesamt;
										// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
										} else {
											$content = "&mdash;&mdash;&mdash;";
										}
									}
								} elseif($rd_calc == 3) {
									
								}
							}
						}
					// ROUND IS SPRINT
					} elseif($getrow_postyp == 1) {
						// Hole Sollzeit für aktuelle Prüfung
						$select_sollzeit = "SELECT * FROM _main_wptable_sz WHERE `eid` = '" . $eid . "' AND `rid` = '" . $i . "'";
						$result_sollzeit = mysqli_query($mysqli, $select_sollzeit);
						$getrow_sollzeit = mysqli_fetch_assoc($result_sollzeit);
								
						// Weise Spaltennamen der entsprechenden Variable zu
						$sollzeit = $getrow_sollzeit['sz'];
								
						// Konvertiere in Echtzeit gespeicherte Sollzeit in Sekunden
						$sollzeit_split_1 = explode(":", $sollzeit);
						/*
						[0] => hh
						[1] => mm
						[2] => ss,uu
						*/
						$sollzeit_split_2 = explode(",", $sollzeit_split_1[2]);
						/*
						[0] => ss
						[1] => uu
						*/
						
						// Dezimales Format => ss,uu => Bsp. 00:02:45,00 => 165,00s
						$sollzeit_sekunden = ($sollzeit_split_1[1] * 60) + $sollzeit_split_2[0] . "." . $sollzeit_split_2[1];
						
						// Suche nach Startzeit für aktuellen Teilnehmer
						$select_spr = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $i . "' AND `sid` = '" . $sid . "' AND `position` = 'Sprint'";
						$result_spr = mysqli_query($mysqli, $select_spr);
						$numrow_spr = mysqli_num_rows($result_spr);
						$getrow_spr = mysqli_fetch_assoc($result_spr);
						
						// Startzeit wurde gefunden (darf maximal 1 sein!)
						if($numrow_spr == 1) {
							// Speichere als dezimale Sekunden in jeweiligen Arrays
							$fahrtzeit_dezimal = $getrow_spr['t_time'] . "," . $getrow_spr['t_centi'];
											
							/*
							Eindimensionales Array [x]
							Array	[
										[1] => Dezimal Zeit
									]
							*/
							// Es wurde keine Startzeit gefunden
						} elseif($numrow_spr == 0) {
							// Speichere als dezimale Sekunden in jeweiligen Arrays
							$fahrtzeit_dezimal = 0;
						}
						
						// Starte mit der Berechnung, vorausgesetzt es handelt sich nicht um Leerwerte
						if($fahrtzeit_dezimal != 0) {
							// Ersetze Komma durch Punkt, da Berechnung nur mit Dezimal-Punktuation erfolgen kann
							$fahrtzeit_dezimal = str_replace(',', '.', $fahrtzeit_dezimal);
							
							// Übergebe Fahrtzeit an Funktion zur Konvertierung
							$fahrtzeit_konvert = convertTimeRacer($fahrtzeit_dezimal);
							
							// Berechne Abweichung
							$abweichung_dezimal = abs($fahrtzeit_dezimal - $sollzeit_sekunden);
							$abweichung_dezimal = number_format((float)$abweichung_dezimal, 2, '.', '');
							
							// Übergebe Fahrtzeit an Funktion zur Konvertierung
							$abweichung_konvert = convertTimeRacer($abweichung_dezimal);
							$content = $abweichung_konvert;
						// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
						} else {
							// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
							$content	= "&mdash;&mdash;&mdash;";
						}
					}
				} else {
					$content = "&ndash;&ndash;&ndash;&ndash;&ndash;";
				}
				
				// CHECK AMOUNT OF TOTAL ROUNDS AND HIDE
				//	if($numrow_rounds > 0 AND ($today < $end)) {
				if($numrow_rounds > 0) {
					// EXACT CHECK FOR AMOUNT AND ROUNDS TO HIDE
					if($rd_scrt == 1 AND $rd_scrt_toggle == 1) {
							// BUILD TABLE CONTENT WITH HIDDEN RESULT
							$tb .=	"
									<td align=\"center\" style=\"font-size: small; vertical-align: middle;\">
										<table width='100%' cellspacing='0' cellpadding='0'>
											<tr>
												<td align='center' width='25%'>
													<div style=\"border: 1px solid #FFFFFF; background: transparent; background-color: " . $status . "; color: #000000; width: 100%; text-decoration: none;\">
														" . $rid_type . $i . " 
													</div>
												</td>
												<td align='center' width='50%'>
													<div class='tooltip' style=\"border: 1px solid #FFFFFF; border-left: 0; background: transparent; background-color: " . $status . "; color: #000000; width: 100%; text-decoration: none;\">
														<font color='#000000'>&ndash;&ndash;?&ndash;&ndash;</font>
														<span class='tooltiptext'>Diese Prüfung wurde als geheim eingestuft und zeigt bis zur Freigabe des Auswerters / Veranstalters kein Ergebnis an.</span>
													</div>
												</td>
												<td align='center' width='25%'>
													<div style=\"border: 1px solid #FFFFFF; border-left: 0; background: transparent; background-color: " . $status . "; color: #000000; width: 100%; text-decoration: none;\">
														" . $symbol . " 
													</div>
												</td>
											</tr>
										</table>												
									</td>
									";
					} elseif(
						($rd_scrt == 1 AND $rd_scrt_toggle == 0) XOR
						($rd_scrt == 0)
					) {
						// BUILD TABLE CONTENT
						$tb .=	"
								<td align=\"center\" style=\"font-size: small; vertical-align: middle;\">
									<table width='100%' cellspacing='0' cellpadding='0'>
										<tr>
											<td align='center' width='25%'>
												<div style=\"border: 1px solid #FFFFFF; background: transparent; background-color: " . $status . "; color: #000000; width: 100%; text-decoration: none;\">
													" . $rid_type . $i . " 
												</div>
											</td>
											<td align='center' width='50%'>
												<div style=\"border: 1px solid #FFFFFF; border-left: 0; background: transparent; background-color: " . $status . "; color: #000000; width: 100%; text-decoration: none;\">
													" . $content . "
												</div>
											</td>
											<td align='center' width='25%'>
												<div style=\"border: 1px solid #FFFFFF; border-left: 0; background: transparent; background-color: " . $status . "; color: #000000; width: 100%; text-decoration: none;\">
													" . $symbol . " 
												</div>
											</td>
										</tr>
									</table>												
								</td>
								";
					}
				} else {
					// BUILD TABLE CONTENT
					$tb .=	"
							<td align=\"center\" style=\"font-size: small; vertical-align: middle;\">
								<table width='100%' cellspacing='0' cellpadding='0'>
									<tr>
										<td align='center' width='25%'>
											<div style=\"border: 1px solid #FFFFFF; background: transparent; background-color: " . $status . "; color: #000000; width: 100%; text-decoration: none;\">
												" . $rid_type . $i . " 
											</div>
										</td>
										<td align='center' width='50%'>
											<div style=\"border: 1px solid #FFFFFF; border-left: 0; background: transparent; background-color: " . $status . "; color: #000000; width: 100%; text-decoration: none;\">
												" . $content . "
											</div>
										</td>
										<td align='center' width='25%'>
											<div style=\"border: 1px solid #FFFFFF; border-left: 0; background: transparent; background-color: " . $status . "; color: #000000; width: 100%; text-decoration: none;\">
												" . $symbol . " 
											</div>
										</td>
									</tr>
								</table>												
							</td>
							";
				}			
				
				if($i > 0 AND $i % 3 == 0 AND !($i % 3)) {
					$tb .=	"
							</tr>
							<tr>
							";
				}
				
				// RESET CONTENT
				$content = "&ndash;&ndash;&ndash;&ndash;&ndash;";
				
				$i++;
			}
			
			$tb .=	"
							</tr>
						</table>
					";
		// NO ROUNDS AVAILABLE [ERROR]
		} elseif($numrow_rounds == 0) {
			$tb .=	"
						<table width=\"100%\" cellspacing=\"5px\" style=\"border: 0;\">
							<tr>
								<th align=\"center\">Keine Prüfungen gefunden</th>
							</tr>
						</table>
					";
		}
			
		// OUTPUT FINAL TABLE
		echo $tb;
	}
?>