<? error_reporting(E_ALL);
	// BUFFER OUTPUT
	ob_start();

	date_default_timezone_set("Europe/Berlin");
	
	$timestamp = time();

    // INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
	
	// INCLUDE SPREADSHEET FUNCIONS
	require 'classes/spreadsheet/vendor/autoload.php';
	
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xls;

	// START SECURE SESSION
	sec_session_start();
	
	// CREATE EVENT ID FROM ACTIVE SESSION
	$eid	= $_SESSION['user_id'];
	
	// Prüfe, ob korrekte POST übergeben wurde
	if(isset($_GET['rid']) && !empty($_GET['rid'])) {
		// Bereinige übergebene POST und baue Runden-ID
		$rid = mysqli_real_escape_string($mysqli, utf8_encode($_GET['rid']));
		
		// Suche nach Prüfungstyp basierend auf Runden-ID
		$select_rd_info = "SELECT * FROM _main_wptable WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' LIMIT 1";
		$result_rd_info = mysqli_query($mysqli, $select_rd_info);
		$numrow_rd_info = mysqli_num_rows($result_rd_info);
		$getrow_rd_info = mysqli_fetch_assoc($result_rd_info);
		
		// Erstelle Dateiname basieren auf Prüfung
		// Prüfungstyp war Sprint
		if($getrow_rd_info['z_entry'] == 1) {
		    $document_desc = date("YmdHis", $timestamp) . "_" . $getrow_rd_info['rid_type'] . $rid . "_Sprint";   
		// Prüfungstyp war Normal
		} elseif($getrow_rd_info['z_entry'] == 0) {
		    $document_desc = date("YmdHis", $timestamp) . "_" . $getrow_rd_info['rid_type'] . $rid . "_Normal";		    
		}
		
		// ERSTELLE NEUES SPREADSHEET
		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		
		// ERSTELLE CONTAINER MIT ALPHABET
		$alphabet = 	array(
							1 	=> 'A',
							2 	=> 'B',
							3 	=> 'C',
							4 	=> 'D',
							5 	=> 'E',
							6 	=> 'F',
							7 	=> 'G',
							8 	=> 'H',
							9 	=> 'I',
							10 	=> 'J',
							11 	=> 'K',
							12 	=> 'L',
							13 	=> 'M',
							14 	=> 'N',
							15 	=> 'O',
							16 	=> 'P',
							17 	=> 'Q',
							18 	=> 'R',
							19 	=> 'S',
							20 	=> 'T',
							21 	=> 'U',
							22 	=> 'V',
							23 	=> 'W',
							24 	=> 'X',
							25 	=> 'Y',
							26 	=> 'Z'
						);
		
		// CONTAINER FÜR BEREINIGTE DATEN
		$ods_sanitized_info = array();
		
		// ODS Export Tabellenkopf
		$ods_header = array();
		
		// Ist die gesuchte Prüfung vorhanden
		if($numrow_rd_info == 1) {
			// Suche nach Ergebnissen basierend auf aktiver User-Session und übergebener Prüfungs-ID
			$select_rd = "SELECT * FROM _main_wpresults WHERE `rid` = '" . $rid . "' AND `eid` = '" . $eid . "' ORDER BY `sid` ASC";
			$result_rd = mysqli_query($mysqli, $select_rd);
			$numrow_rd = mysqli_num_rows($result_rd);
		
			// Es wurden Ergebnisse gefunden
			if($numrow_rd > 0) {
				// Beginne damit, Arrays zur Berechnung und Speicherung der Zeiten zu erzeugen
				// Aus Datenbank geholt
				$array_zeit_als_dezimal		= array();
				
				// Berechnetes Format [Differenzrechnung Start- und Zielzeit]
				$array_fahrtzeit_dezimal	= array();
				$array_fahrtzeit_konvert 	= array();
				
				// Berechnetes Format [Differenzrechnung Fahrtzeit- mit Sollzeit]
				$array_abweichung_dezimal	= array();
				$array_abweichung_konvert	= array();				
				
				// Erstelle "manuelle" Zählervariable
				$tcount = 1;
				
				// Hole Teilnehmerliste
				$select_tmember = "SELECT `eid`, `sid` FROM _optio_tmembers WHERE `eid` = '" . $eid . "' ORDER BY `sid` ASC";
				$result_tmember = mysqli_query($mysqli, $select_tmember);
				
				while($getrow_tmember = mysqli_fetch_assoc($result_tmember)) {
					// Speichere aktuellen Durchlauf in Index mir aktuellem Zählerstand
					$con_tmember[$tcount] = $getrow_tmember['sid'];
					
					// Erhöhe danach Zählerstand um eins
					$tcount++;
				}
				
				// Erstelle Tabellenkopf
				$ods_header[1] = "#";
				
				$rid_type	= $getrow_rd_info['rid_type'];
				$rd_state	= $getrow_rd_info['suspend'];
				$rd_tpos	= $getrow_rd_info['total_pos'];
				$rd_zentry	= $getrow_rd_info['z_entry'];
				
				// Prüfe, ob es sich um eine Prüfung vom Typ "Sprint" handelt
				// Keine Prüfung vom Typ "Sprint"
				if($rd_zentry == 0) {
					// Erstlle Tabellenkopf
					$ods_header[2] = "Start";
					
					// Prüfe auf Gesamtpositionen (Standard sind 2 --> Start und Ziel)
					// Standardprüfung mit Start und Ziel
					if($rd_tpos == 2) {
						// Hole Sollzeit für aktuelle Prüfung
						$select_sollzeit = "SELECT * FROM _main_wptable_sz WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "'";
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
						
						// Erstelle Tabellenkopf
						$ods_header[3] = "Ziel"; 
						$ods_header[4] = "Fahrtzeit"; 
						$ods_header[5] = "Abweichung";
						$ods_header[6] = "Sollzeit";
						
						// SETZE HEADER STYLE
						$header_range = ($alphabet[1] . '1:' . $alphabet[count($ods_header)] . '1');
						$content_range = ($alphabet[1] . '1:' . $alphabet[count($ods_header)] . count($con_tmember));
						
						// PACKE HEADER IN SPREADSHEET
						for($i = 1; $i < (count($ods_header) + 1); $i++) {
							$sheet->setCellValue(($alphabet[$i] . '1'), $ods_header[$i])->getStyle(($alphabet[$i] . '1'))->getFont()->setBold(true);
						}
						
						// SETZE STYLE FÜR HEADER
						$spreadsheet->getActiveSheet()->getStyle($header_range)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
						
						// Beginne mit der Berechnung und Ausgabe der Ergebnisse
						for($a = 1; $a < (count($con_tmember) + 1); $a++) {
							// Erstelle Tabelleninhalt
							$ods_sanitized_info[$con_tmember[$a]][0] = $con_tmember[$a];
							
							// Suche nach Startzeit für aktuellen Teilnehmer
							$select_pre = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $con_tmember[$a] . "' AND `position` = 'Start'";
							$result_pre = mysqli_query($mysqli, $select_pre);
							$numrow_pre = mysqli_num_rows($result_pre);
							$getrow_pre = mysqli_fetch_assoc($result_pre);
							
							// Startzeit wurde gefunden (darf maximal 1 sein!)
							if($numrow_pre == 1) {
								// Erstelle Tablleninhalt
								$ods_sanitized_info[$con_tmember[$a]][1] = $getrow_pre['t_realtime'];
								
								// Speichere als dezimale Sekunden in jeweiligen Arrays
								$array_zeit_als_dezimal[$con_tmember[$a]]["pre"] = $getrow_pre['t_time'] . "." . $getrow_pre['t_centi'];
												
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
								// Erstelle Tablleninhalt
								$ods_sanitized_info[$con_tmember[$a]][1] = "---";
								
								// Speichere als dezimale Sekunden in jeweiligen Arrays
								$array_zeit_als_dezimal[$con_tmember[$a]]["pre"] = 0;
							}
							
							// Suche nach Zielzeit für aktuellen Teilnehmer
							$select_seq = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $con_tmember[$a] . "' AND `position` = 'Ziel'";
							$result_seq = mysqli_query($mysqli, $select_seq);
							$numrow_seq = mysqli_num_rows($result_seq);
							$getrow_seq = mysqli_fetch_assoc($result_seq);
								
							// Zielzeit wurde gefunden (darf maximal 1 sein!)
							if($numrow_seq == 1) {
								// Erstelle Tablleninhalt
								$ods_sanitized_info[$con_tmember[$a]][2] = $getrow_seq['t_realtime'];
								
								// Speichere als dezimale Sekunden in jeweiligen Arrays
								$array_zeit_als_dezimal[$con_tmember[$a]]["seq"] = $getrow_seq['t_time'] . "." . $getrow_seq['t_centi'];
				
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
								// Erstelle Tablleninhalt
								$ods_sanitized_info[$con_tmember[$a]][2] = "---";
								
								// Speichere als dezimale Sekunden in jeweiligen Arrays
								$array_zeit_als_dezimal[$con_tmember[$a]]["seq"] = 0;
							}
							
							// Starte mit der Berechnung, vorausgesetzt es handelt sich nicht um Leerwerte
							if($array_zeit_als_dezimal[$con_tmember[$a]]["pre"] != 0 AND $array_zeit_als_dezimal[$con_tmember[$a]]["seq"] != 0) {
								// Berechne Fahrtzeit
								$array_fahrtzeit_dezimal[$a] = abs((float)$array_zeit_als_dezimal[$con_tmember[$a]]["seq"] - (float)$array_zeit_als_dezimal[$con_tmember[$a]]["pre"]);
								$array_fahrtzeit_dezimal[$a] = number_format((float)$array_fahrtzeit_dezimal[$a], 2, '.', '');
								
								// Übergebe Fahrtzeit an Funktion zur Konvertierung
								$array_fahrtzeit_konvert[$a] = convertTime($array_fahrtzeit_dezimal[$a]);
								
								// Berechne Abweichung
								$array_abweichung_dezimal[$a] = abs((float)$array_fahrtzeit_dezimal[$a] - (float)$sollzeit_sekunden);
								$array_abweichung_dezimal[$a] = number_format((float)$array_abweichung_dezimal[$a], 2, '.', '');
								
								// Übergebe Fahrtzeit an Funktion zur Konvertierung
								$array_abweichung_konvert[$a] = convertTime($array_abweichung_dezimal[$a]);
							// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
							} else {
								// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
								$array_fahrtzeit_konvert[$a]	= "---";
								$array_abweichung_konvert[$a]	= "---";
							}
							
							// Erstelle Tabelleninhalt
							$ods_sanitized_info[$con_tmember[$a]][3] = $array_fahrtzeit_konvert[$a];
							$ods_sanitized_info[$con_tmember[$a]][4] = $array_abweichung_konvert[$a];
							$ods_sanitized_info[$con_tmember[$a]][5] = $sollzeit;
							
							// Packe Tabelleninhalt in ODS
							for($x = 0; $x < count($ods_header); $x++) {
								$sheet->setCellValue(($alphabet[($x + 1)] . ($con_tmember[$a] + 1)), $ods_sanitized_info[$con_tmember[$a]][$x]);
								$spreadsheet->getActiveSheet()->getColumnDimension($alphabet[($x + 1)])->setAutoSize(true);
							}
							
							// Stelle ODS Datei fertig
							if($a == count($con_tmember)) {								
								$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Ods');
								$writer->save($document_desc . '.ods');
								
								$file = $document_desc . ".ods";
								header('Content-Description: File Transfer');
								header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
								header('Content-Disposition: attachment; filename=' . $file);
								header('Content-Transfer-Encoding: binary');
								header('Expires: 0');
								header('Cache-Control: must-revalidate');
								header('Pragma: public');
								header('Content-Length: ' . filesize($file));
								readfile($file);
								
								// WORKAROUND: DELETE FILE AFTER DOWNLOAD
								unlink($file);
								
								exit();
									
								// Wenn Datei nicht per AJAX angefordert wird, erzwinge Download
								/*
								if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
									header("Content-Disposition: attachment; filename=" . $document_desc . ".ods");
								}
								*/
							}
						}
					// Prüfung mit variabler Anzahl an Zwischenzeiten
					} elseif($rd_tpos > 2) {
						// Erstelle zusätzliche Array zum Speichern der Gesamtfahrtzeit und -abweichung
						$array_fahrtzeit_dezimal_gesamt = array();
						$array_fahrtzeit_konvert_gesamt = array();
						$array_abweichung_dezimal_gesamt = array();
						$array_abweichung_konvert_gesamt = array();
						
						// Hole Sollzeiten und speichere diese in Array
						$select_sollzeit = "SELECT * FROM _main_wptable_sz WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' ORDER BY `sz_cid` ASC";
						$result_sollzeit = mysqli_query($mysqli, $select_sollzeit);
						
						// Erstelle Arrays zum Speichern von Sollzeiten
						$array_sollzeit_konvert = array();
						$array_sollzeit_sekunde = array();
						
						while($getrow_sollzeit = mysqli_fetch_assoc($result_sollzeit)) {
							// Weise Sollzeiten zu
							$array_sollzeit_konvert[] = $getrow_sollzeit['sz'];
						}
						
						// Wandel konvertierte Sollzeiten in Sekunden um
						for($x = 0; $x < count($array_sollzeit_konvert); $x++) {
							// START CONVERTING TARGET TIME IN SECONDS
							$target_split_1 = explode(":", $array_sollzeit_konvert[$x]);
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
							$array_sollzeit_sekunde[$x] = ($target_split_1[1] * 60) + $target_split_2[0] . "." . $target_split_2[1];
						}
						
						// Erstelle ODS Header
						// Hole Anzahl der Zwischenzeiten
						// Minus 1 für die Berechnung
						for($a = 0; $a < ($rd_tpos - 2); $a++) {
							$ods_header[] = "ZZ " . ($a + 1);
						}
						
						// Erstelle ODS Header
						$ods_header[] = "Ziel";
							
						// Erstelle ODS Header
						for($a = 0; $a < ($rd_tpos - 1); $a++) {
							$ods_header[] = "Fahrtzeit " . ($a + 1);
						}
						
						// Erstelle ODS Header
						$ods_header[] = "Fahrtzeit gesamt";
							
						for($a = 0; $a < ($rd_tpos - 1); $a++) {
							// Erstelle ODS Header
							$ods_header[] = "Abweichung " . ($a + 1);
						}
						
						// Erstelle ODS Header
						$ods_header[] = "Abweichung gesamt";
							
						for($a = 0; $a < ($rd_tpos - 1); $a++) {
							// Erstelle ODS Header
							$ods_header[] = "Sollzeit " . ($a + 1);
						}
						
						// SETZE HEADER STYLE
						$header_range = ($alphabet[1] . '1:' . $alphabet[count($ods_header)] . '1');
						$content_range = ($alphabet[1] . '1:' . $alphabet[count($ods_header)] . count($con_tmember));
						
						// PACKE HEADER IN SPREADSHEET
						for($i = 1; $i < (count($ods_header) + 1); $i++) {
							$sheet->setCellValue(($alphabet[$i] . '1'), $ods_header[$i])->getStyle(($alphabet[$i] . '1'))->getFont()->setBold(true);
						}
						
						// SETZE STYLE FÜR HEADER
						$spreadsheet->getActiveSheet()->getStyle($header_range)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
						
						$select_calc = "SELECT `eid`, `t_calc` FROM _race_run_events WHERE `eid` = '" . $eid . "' AND `active` = '1'";
						$result_calc = mysqli_query($mysqli, $select_calc);
						$getrow_calc = mysqli_fetch_assoc($result_calc);
							
						// Weise Berechnungsart zu
						$rd_calc = $getrow_calc['t_calc'];
						
						// Führe Berechnungen und Tabellenaufbau in Hauptschleife durch
						for($b = 1; $b < (count($con_tmember) + 1); $b++) {
							// BUILD TABLE CONTENT
							$ods_sanitized_info[$con_tmember[$b]][0] = $con_tmember[$b];
							
							// Hole Ergebnis für Start für aktuellen Teilnehmer
							$select_pre = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $con_tmember[$b] . "' AND `position` = 'Start'";
							$result_pre = mysqli_query($mysqli, $select_pre);
							$numrow_pre = mysqli_num_rows($result_pre);
							$getrow_pre = mysqli_fetch_assoc($result_pre);
							
							// Startzeit wurde gefunden (darf maximal 1 sein!)
							if($numrow_pre == 1) {
								// Erstelle Tabelleninhalt
								$ods_sanitized_info[$con_tmember[$b]][1] = $getrow_pre['t_realtime'];
								
								// Speichere als dezimale Sekunden in jeweiligen Arrays
								$array_zeit_als_dezimal[$con_tmember[$b]]["pre"] = $getrow_pre['t_time'] . "." . $getrow_pre['t_centi'];
							// Startzeit wurde nicht gefunden
							} elseif($numrow_pre == 0) {
								// Erstelle Tabelleninhalt
								$ods_sanitized_info[$con_tmember[$b]][1] = "'---";
								
								// Speichere als dezimale Sekunden in jeweiligen Arrays
								$array_zeit_als_dezimal[$con_tmember[$b]]["pre"] = 0;
							}
							
							// Hole nächsten Index durch Zählen der Elemente
							$next_index = count($ods_sanitized_info[$con_tmember[$b]]);
							
							// Hole Ergebnis für jede Zwischenzeit für aktuellen Teilnehmer
							for($c = 0; $c < ($rd_tpos - 2); $c++) {
								$select_opt = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $con_tmember[$b] . "' AND `position` = 'ZZ" . ($c + 1) . "'";
								$result_opt = mysqli_query($mysqli, $select_opt);
								$numrow_opt = mysqli_num_rows($result_opt);
								$getrow_opt = mysqli_fetch_assoc($result_opt);
															
								// Zwischenzeit X wurde gefunden
								if($numrow_opt == 1) {
									// Erstelle Tablleninhalt
									$ods_sanitized_info[$con_tmember[$b]][(2 + $c)] = $getrow_opt['t_realtime'];
									
									// Speichere als dezimale Sekunden in jeweiligen Arrays
									$array_zeit_als_dezimal[$con_tmember[$b]]["zz" . ($c + 1)] = $getrow_opt['t_time'] . "." . $getrow_opt['t_centi'];
								// Zwischenzeit X wurde nicht gefunden
								} elseif($numrow_opt == 0) {
									// Erstelle Tablleninhalt
									$ods_sanitized_info[$con_tmember[$b]][(2 + $c)] = "'---";
									
									// Speichere als dezimale Sekunden in jeweiligen Arrays
									$array_zeit_als_dezimal[$con_tmember[$b]]["zz" . ($c + 1)] = 0;
								}
							}
							
							// Hole Ergebnis für Ziel für aktuellen Teilnehmer
							$select_seq = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $con_tmember[$b] . "' AND `position` = 'Ziel'";
							$result_seq = mysqli_query($mysqli, $select_seq);
							$numrow_seq = mysqli_num_rows($result_seq);
							$getrow_seq = mysqli_fetch_assoc($result_seq);
							
							// Zielzeit wurde gefunden (darf maximal 1 sein!)
							if($numrow_seq == 1) {
								// Hole nächsten Index durch Zählen der Elemente
								$next_index = count($ods_sanitized_info[$con_tmember[$b]]);
								
								// BUILD TABLE CONTENT
								$ods_sanitized_info[$con_tmember[$b]][$next_index] = $getrow_seq['t_realtime'];
								
								// Speichere als dezimale Sekunden in jeweiligen Arrays
								$array_zeit_als_dezimal[$con_tmember[$b]]["seq"] = $getrow_seq['t_time'] . "." . $getrow_seq['t_centi'];
							// Zielzeit wurde nicht gefunden
							} elseif($numrow_seq == 0) {
								// Hole nächsten Index durch Zählen der Elemente
								$next_index = count($ods_sanitized_info[$con_tmember[$b]]);
								
								// BUILD TABLE CONTENT
								$ods_sanitized_info[$con_tmember[$b]][$next_index] = "'---";
								
								// Speichere als dezimale Sekunden in jeweiligen Arrays
								$array_zeit_als_dezimal[$con_tmember[$b]]["seq"] = 0;
							}
							
							// Beginne vollständige Berechnungen unter Berücksichtigung der Berechnungsart
							// Minus 1, da Aus 2 Zeiten 1 Differenz wird
							for($d = 0; $d < ($rd_tpos - 1); $d++) {
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
									if($d >= 0 AND $d < (($rd_tpos - 1) - 1)) {
										// Starte mit der Berechnung, vorausgesetzt es handelt sich nicht um Leerwerte
										if($array_zeit_als_dezimal[$con_tmember[$b]]["pre"] != 0 AND $array_zeit_als_dezimal[$con_tmember[$b]]["zz" . ($d + 1)] != 0) {
											// Berechne Fahrtzeit
											$array_fahrtzeit_dezimal[$con_tmember[$b]] = abs((float)$array_zeit_als_dezimal[$con_tmember[$b]]["zz" . ($d + 1)] - (float)$array_zeit_als_dezimal[$con_tmember[$b]]["pre"]);
											$array_fahrtzeit_dezimal[$con_tmember[$b]] = number_format((float)$array_fahrtzeit_dezimal[$con_tmember[$b]], 2, '.', '');
												
											// Übergebe Fahrtzeit an Funktion zur Konvertierung
											$array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . $d] = convertTime($array_fahrtzeit_dezimal[$con_tmember[$b]]);
											
											// Addiere Fahrtzeit Dezimal zur Gesamtfahrtzeit
											@$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]] = abs((float)$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]] + (float)$array_fahrtzeit_dezimal[$con_tmember[$b]]);
											$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]] = number_format((float)$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]], 2, '.', '');
											
											// Berechne Abweichung
											$array_abweichung_dezimal[$con_tmember[$b]] = abs((float)$array_fahrtzeit_dezimal[$con_tmember[$b]] - (float)$array_sollzeit_sekunde[$d]);
											$array_abweichung_dezimal[$con_tmember[$b]] = number_format((float)$array_abweichung_dezimal[$con_tmember[$b]], 2, '.', '');
												
											// Übergebe Abweichung an Funktion zur Konvertierung
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] = convertTime($array_abweichung_dezimal[$con_tmember[$b]]);
											
											// Addiere Abweichung Dezimal zur Gesamtabweichung
											@$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = abs((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]] + (float)$array_abweichung_dezimal[$con_tmember[$b]]);
											$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = number_format((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]], 2, '.', '');

											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = convertTime($array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]]);
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = convertTime($array_abweichung_dezimal_gesamt[$con_tmember[$b]]);
										// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
										} else {
											// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
											$array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . $d]		= "---";
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d]		= "---";
											
											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = "---";
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = "---";
										}								
									// Ende mit Differenz aus Zielzeit abzgl. Startzeit
									} elseif($d >= 0 AND $d == (($rd_tpos - 1) - 1)) {
										// Starte mit der Berechnung, vorausgesetzt es handelt sich nicht um Leerwerte
										if($array_zeit_als_dezimal[$con_tmember[$b]]["pre"] != 0 AND $array_zeit_als_dezimal[$con_tmember[$b]]["seq"] != 0) {
											// Berechne Fahrtzeit
											$array_fahrtzeit_dezimal[$con_tmember[$b]] = abs((float)$array_zeit_als_dezimal[$con_tmember[$b]]["seq"] - (float)$array_zeit_als_dezimal[$con_tmember[$b]]["pre"]);
											$array_fahrtzeit_dezimal[$con_tmember[$b]] = number_format((float)$array_fahrtzeit_dezimal[$con_tmember[$b]], 2, '.', '');
												
											// Übergebe Fahrtzeit an Funktion zur Konvertierung
											$array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . $d] = convertTime($array_fahrtzeit_dezimal[$con_tmember[$b]]);
											
											// Addiere Fahrtzeit Dezimal zur Gesamtfahrtzeit
											$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]] = abs((float)$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]] + (float)$array_fahrtzeit_dezimal[$con_tmember[$b]]);
											$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]] = number_format((float)$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]], 2, '.', '');
											
											// Berechne Abweichung
											$array_abweichung_dezimal[$con_tmember[$b]] = abs((float)$array_fahrtzeit_dezimal[$con_tmember[$b]] - (float)$array_sollzeit_sekunde[$d]);
											$array_abweichung_dezimal[$con_tmember[$b]] = number_format((float)$array_abweichung_dezimal[$con_tmember[$b]], 2, '.', '');
												
											// Übergebe Abweichung an Funktion zur Konvertierung
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] = convertTime($array_abweichung_dezimal[$con_tmember[$b]]);
											
											// Addiere Abweichung Dezimal zur Gesamtabweichung
											$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = abs((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]] + (float)$array_abweichung_dezimal[$con_tmember[$b]]);
											$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = number_format((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]], 2, '.', '');
											
											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = convertTime($array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]]);
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = convertTime($array_abweichung_dezimal_gesamt[$con_tmember[$b]]);
										// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
										} else {
											// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
											$array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . $d]		= "---";
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d]		= "---";
											
											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = "---";
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = "---";
										}
									}
								} elseif($rd_calc == 2) {
									// Beginne bei erster Zwischenzeit abzgl. Startzeit
									if($d == 0) {
										// Starte mit der Berechnung, vorausgesetzt es handelt sich nicht um Leerwerte
										if($array_zeit_als_dezimal[$con_tmember[$b]]["pre"] != 0 AND $array_zeit_als_dezimal[$con_tmember[$b]]["zz" . ($d + 1)] != 0) {
											// Berechne Fahrtzeit
											$array_fahrtzeit_dezimal[$con_tmember[$b]]["fz" . $d] = abs((float)$array_zeit_als_dezimal[$con_tmember[$b]]["zz" . ($d + 1)] - (float)$array_zeit_als_dezimal[$con_tmember[$b]]["pre"]);
											$array_fahrtzeit_dezimal[$con_tmember[$b]]["fz" . $d] = number_format((float)$array_fahrtzeit_dezimal[$con_tmember[$b]]["fz" . $d], 2, '.', '');
												
											// Übergebe Fahrtzeit an Funktion zur Konvertierung
											$array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . $d] = convertTime($array_fahrtzeit_dezimal[$con_tmember[$b]]["fz" . $d]);
											
											// Addiere Fahrtzeit Dezimal zur Gesamtfahrtzeit
											@$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]] = abs((float)$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]] + (float)$array_fahrtzeit_dezimal[$con_tmember[$b]]["fz" . $d]);
											$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]] = number_format((float)$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]], 2, '.', '');
											
											// Berechne Abweichung
											$array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d] = abs((float)$array_fahrtzeit_dezimal[$con_tmember[$b]]["fz" . $d] - (float)$array_sollzeit_sekunde[$d]);
											$array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d] = number_format((float)$array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d], 2, '.', '');
												
											// Übergebe Abweichung an Funktion zur Konvertierung
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] = convertTime($array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d]);
											
											// Addiere Abweichung Dezimal zur Gesamtabweichung
											@$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = abs((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]] + (float)$array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d]);
											$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = number_format((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]], 2, '.', '');
											
											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = convertTime($array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]]);
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = convertTime($array_abweichung_dezimal_gesamt[$con_tmember[$b]]);
										// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
										} else {
											// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
											$array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . $d]		= "---";
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d]		= "---";
											
											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = "---";
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = "---";
										}
									// Verrechnene Zwischenzeiten miteinander (ZZX - ZZX-1)
									} elseif($d >= 1 AND $d < (($rd_tpos - 1) - 1)) {
										// Starte mit der Berechnung, vorausgesetzt es handelt sich nicht um Leerwerte
										if($array_zeit_als_dezimal[$con_tmember[$b]]["zz" . $d] != 0 AND $array_zeit_als_dezimal[$con_tmember[$b]]["zz" . ($d + 1)] != 0) {
											// Berechne Fahrtzeit
											$array_fahrtzeit_dezimal[$con_tmember[$b]]["fz" . $d] = abs((float)$array_zeit_als_dezimal[$con_tmember[$b]]["zz" . ($d + 1)] - (float)$array_zeit_als_dezimal[$con_tmember[$b]]["zz" . $d]);
											$array_fahrtzeit_dezimal[$con_tmember[$b]]["fz" . $d] = number_format((float)$array_fahrtzeit_dezimal[$con_tmember[$b]]["fz" . $d], 2, '.', '');
												
											// Übergebe Fahrtzeit an Funktion zur Konvertierung
											$array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . $d] = convertTime($array_fahrtzeit_dezimal[$con_tmember[$b]]["fz" . $d]);
											
											// Addiere Fahrtzeit Dezimal zur Gesamtfahrtzeit
											$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]] = abs((float)$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]] + (float)$array_fahrtzeit_dezimal[$con_tmember[$b]]["fz" . $d]);
											$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]] = number_format((float)$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]], 2, '.', '');
											
											// Berechne Abweichung
											$array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d] = abs((float)$array_fahrtzeit_dezimal[$con_tmember[$b]]["fz" . $d] - (float)$array_sollzeit_sekunde[$d]);
											$array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d] = number_format((float)$array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d], 2, '.', '');
												
											// Übergebe Abweichung an Funktion zur Konvertierung
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] = convertTime($array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d]);
											
											// Addiere Abweichung Dezimal zur Gesamtabweichung
											$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = abs((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]] + (float)$array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d]);
											$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = number_format((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]], 2, '.', '');	
											
											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = convertTime($array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]]);
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = convertTime($array_abweichung_dezimal_gesamt[$con_tmember[$b]]);
										// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
										} else {
											// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
											$array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . $d]		= "---";
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d]		= "---";
											
											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = "---";
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = "---";
										}										
									// Ende mit Differenz aus Zielzeit abzgl. vorangegangener Zwischenzeit
									} elseif($d > 0 AND $d == (($rd_tpos - 1) - 1)) {
										// Starte mit der Berechnung, vorausgesetzt es handelt sich nicht um Leerwerte
										if($array_zeit_als_dezimal[$con_tmember[$b]]["zz" . $d] != 0 AND $array_zeit_als_dezimal[$con_tmember[$b]]["seq"] != 0) {
											// Berechne Fahrtzeit
											$array_fahrtzeit_dezimal[$con_tmember[$b]]["fz" . $d] = abs((float)$array_zeit_als_dezimal[$con_tmember[$b]]["seq"] - (float)$array_zeit_als_dezimal[$con_tmember[$b]]["zz" . $d]);
											$array_fahrtzeit_dezimal[$con_tmember[$b]]["fz" . $d] = number_format((float)$array_fahrtzeit_dezimal[$con_tmember[$b]]["fz" . $d], 2, '.', '');
												
											// Übergebe Fahrtzeit an Funktion zur Konvertierung
											$array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . $d] = convertTime($array_fahrtzeit_dezimal[$con_tmember[$b]]["fz" . $d]);
											
											// Addiere Fahrtzeit Dezimal zur Gesamtfahrtzeit
											$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]] = abs((float)$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]] + (float)$array_fahrtzeit_dezimal[$con_tmember[$b]]["fz" . $d]);
											$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]] = number_format((float)$array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]], 2, '.', '');
											
											// Berechne Abweichung
											$array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d] = abs((float)$array_fahrtzeit_dezimal[$con_tmember[$b]]["fz" . $d] - (float)$array_sollzeit_sekunde[$d]);
											$array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d] = number_format((float)$array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d], 2, '.', '');
												
											// Übergebe Abweichung an Funktion zur Konvertierung
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] = convertTime($array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d]);
											
											// Addiere Abweichung Dezimal zur Gesamtabweichung
											$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = abs((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]] + (float)$array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d]);
											$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = number_format((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]], 2, '.', '');	
											
											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = convertTime($array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]]);
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = convertTime($array_abweichung_dezimal_gesamt[$con_tmember[$b]]);
										// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
										} else {
											// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
											$array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . $d]		= "---";
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d]		= "---";
											
											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = "---";
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = "---";
										}
									}
								} elseif($rd_calc == 3) {
									
								}
								
								// Hole nächsten Index durch Zählen der Elemente
								$next_index = count($ods_sanitized_info[$con_tmember[$b]]);
								
								// Prüfe letztmals auf Berechnungsmodus und gebe individuellen Wert aus
								// Bei Modus 1 (Ab Start) fällt die Gesamtfahrtzeit weg. An Ihre Stelle
								// tritt die Differenz zwischen Start und Ziel (da nicht überschreitbar)
								if($rd_calc == 1) {
									if($d >= 0 AND $d < (($rd_tpos - 1) - 1)) {
										// Erstelle Tabelleninhalt				
										$ods_sanitized_info[$con_tmember[$b]][$next_index] = $array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . $d];
									} elseif($d >= 0 AND $d == (($rd_tpos - 1) - 1)) {
										// Erstelle Tabelleninhalt			
										$ods_sanitized_info[$con_tmember[$b]][$next_index] = '┗━►';
									}										
								} elseif($rd_calc == 2) {
									// Erstelle Tabelleninhalt			
									$ods_sanitized_info[$con_tmember[$b]][$next_index] = $array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . $d];
								}
							}
							
							// Hole nächsten Index durch Zählen der Elemente
							$next_index = count($ods_sanitized_info[$con_tmember[$b]]);

							// Prüfe letztmals auf Berechnungsmodus und gebe individuellen Wert aus
							if($rd_calc == 1) {
								// Erstelle Tabelleninhalt			
								$ods_sanitized_info[$con_tmember[$b]][$next_index] = $array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . (($rd_tpos - 1) - 1)];
							} elseif($rd_calc == 2) {
								// Erstelle Tabelleninhalt			
								$ods_sanitized_info[$con_tmember[$b]][$next_index] = $array_fahrtzeit_konvert_gesamt[$con_tmember[$b]];
							}
							
							// Hole nächsten Index durch Zählen der Elemente
							$next_index++;
							
							// Gebe jede Abweichung basierend auf den Gesamtpositionen minus 2 (Start und Ziel) aus
							for($e = 0; $e < ($rd_tpos - 1); $e++) {
								if($e < ($rd_tpos - 1)) {
									// Erstelle Tabelleninhalt			
									$ods_sanitized_info[$con_tmember[$b]][$next_index] = $array_abweichung_konvert[$con_tmember[$b]]["ab" . $e];
								}
								
								// Hole nächsten Index durch Zählen der Elemente
								$next_index++;
							}
								
							// Erstelle Tabelleninhalt			
							$ods_sanitized_info[$con_tmember[$b]][$next_index] = $array_abweichung_konvert_gesamt[$con_tmember[$b]];
							
							// Hole nächsten Index durch Zählen der Elemente
							$next_index++;
							
							// Gebe jede Sollzeit aus
							for($f = 0; $f < ($rd_tpos - 1); $f++) {
								if($f < ($rd_tpos - 1)) {
									// Erstelle Tabelleninhalt			
									$ods_sanitized_info[$con_tmember[$b]][$next_index] = $array_sollzeit_konvert[$f];
								}
								
								// Hole nächsten Index durch Zählen der Elemente
								$next_index++;
							}
							
							// Packe Tabelleninhalt in ODS
							for($x = 0; $x < count($ods_header); $x++) {
								$sheet->setCellValue(($alphabet[($x + 1)] . ($con_tmember[$b] + 1)), $ods_sanitized_info[$con_tmember[$b]][$x]);
								$spreadsheet->getActiveSheet()->getColumnDimension($alphabet[($x + 1)])->setAutoSize(true);
							}
							
							// Stelle ODS Datei fertig
							if($b == count($con_tmember)) {								
								$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Ods');
								$writer->save($document_desc . '.ods');
								
								$file = $document_desc . ".ods";
								header('Content-Description: File Transfer');
								header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
								header('Content-Disposition: attachment; filename=' . $file);
								header('Content-Transfer-Encoding: binary');
								header('Expires: 0');
								header('Cache-Control: must-revalidate');
								header('Pragma: public');
								header('Content-Length: ' . filesize($file));
								readfile($file);
								
								// WORKAROUND: DELETE FILE AFTER DOWNLOAD
								unlink($file);
								
								exit();
									
								// Wenn Datei nicht per AJAX angefordert wird, erzwinge Download
								/*
								if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
									header("Content-Disposition: attachment; filename=" . $document_desc . ".ods");
								}
								*/
							}
						}
					}					
				// Eine Prüfung vom Typ "Sprint"	
				} elseif($rd_zentry == 1) {
					// Hole Sollzeit für aktuelle Prüfung
					$select_sollzeit = "SELECT * FROM _main_wptable_sz WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "'";
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
						
					// Erstelle ODS Header
					$ods_header[2] = "Fahrtzeit";
					$ods_header[3] = "Abweichung";
					$ods_header[4] = "Sollzeit";
			
					// SETZE HEADER STYLE
					$header_range = ($alphabet[1] . '1:' . $alphabet[count($ods_header)] . '1');
					$content_range = ($alphabet[1] . '1:' . $alphabet[count($ods_header)] . count($con_tmember));
						
					// PACKE HEADER IN SPREADSHEET
					for($i = 1; $i < (count($ods_header) + 1); $i++) {
						$sheet->setCellValue(($alphabet[$i] . '1'), $ods_header[$i])->getStyle(($alphabet[$i] . '1'))->getFont()->setBold(true);
					}
						
					// SETZE STYLE FÜR HEADER
					$spreadsheet->getActiveSheet()->getStyle($header_range)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
						
					// Beginne mit der Berechnung und Ausgabe der Ergebnisse
					for($a = 1; $a < (count($con_tmember) + 1); $a++) {
						// Suche nach Startzeit für aktuellen Teilnehmer
						$select_spr = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $con_tmember[$a] . "' AND `position` = 'Sprint'";
						$result_spr = mysqli_query($mysqli, $select_spr);
						$numrow_spr = mysqli_num_rows($result_spr);
						$getrow_spr = mysqli_fetch_assoc($result_spr);
						
						// Startzeit wurde gefunden (darf maximal 1 sein!)
						if($numrow_spr == 1) {
							// Speichere als dezimale Sekunden in jeweiligen Arrays
							$array_fahrtzeit_dezimal[$a] = $getrow_spr['t_time'] . "," . $getrow_spr['t_centi'];
											
							/*
							Eindimensionales Array [x]
							Array	[
										[1] => Dezimal Zeit
									]
							*/
							// Es wurde keine Startzeit gefunden
						} elseif($numrow_spr == 0) {
							// Speichere als dezimale Sekunden in jeweiligen Arrays
							$array_fahrtzeit_dezimal[$a] = 0;
						}
						
						// Starte mit der Berechnung, vorausgesetzt es handelt sich nicht um Leerwerte
						if($array_fahrtzeit_dezimal[$a] != 0) {
							// Ersetze Komma durch Punkt, da Berechnung nur mit Dezimal-Punktuation erfolgen kann
							$array_fahrtzeit_dezimal[$a] = str_replace(',', '.', $array_fahrtzeit_dezimal[$a]);
							
							// Übergebe Fahrtzeit an Funktion zur Konvertierung
							$array_fahrtzeit_konvert[$a] = convertTime($array_fahrtzeit_dezimal[$a]);
							
							// Berechne Abweichung
							$array_abweichung_dezimal[$a] = abs($array_fahrtzeit_dezimal[$a] - $sollzeit_sekunden);
							$array_abweichung_dezimal[$a] = number_format((float)$array_abweichung_dezimal[$a], 2, '.', '');
							
							// Übergebe Fahrtzeit an Funktion zur Konvertierung
							$array_abweichung_konvert[$a] = convertTime($array_abweichung_dezimal[$a]);
						// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
						} else {
							// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
							$array_fahrtzeit_konvert[$a]	= "---";
							$array_abweichung_konvert[$a]	= "---";
						}
						
						// Erstelle Tabelleninhalt
						$ods_sanitized_info[$con_tmember[$a]][0] = $con_tmember[$a];
						$ods_sanitized_info[$con_tmember[$a]][1] = $array_fahrtzeit_konvert[$a];
						$ods_sanitized_info[$con_tmember[$a]][2] = $array_abweichung_konvert[$a];
						$ods_sanitized_info[$con_tmember[$a]][3] = $sollzeit;
							
						// Packe Tabelleninhalt in ODS
						for($x = 0; $x < count($ods_header); $x++) {
							$sheet->setCellValue(($alphabet[($x + 1)] . ($con_tmember[$a] + 1)), $ods_sanitized_info[$con_tmember[$a]][$x]);
							$spreadsheet->getActiveSheet()->getColumnDimension($alphabet[($x + 1)])->setAutoSize(true);
						}
							
						// Stelle ODS Datei fertig
						if($a == count($con_tmember)) {								
							$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Ods');
							$writer->save($document_desc . '.ods');
								
							$file = $document_desc . ".ods";
							header('Content-Description: File Transfer');
							header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
							header('Content-Disposition: attachment; filename=' . $file);
							header('Content-Transfer-Encoding: binary');
							header('Expires: 0');
							header('Cache-Control: must-revalidate');
							header('Pragma: public');
							header('Content-Length: ' . filesize($file));
							readfile($file);
								
							// WORKAROUND: DELETE FILE AFTER DOWNLOAD
							unlink($file);
								
							exit();
									
							// Wenn Datei nicht per AJAX angefordert wird, erzwinge Download
							/*
							if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
								header("Content-Disposition: attachment; filename=" . $document_desc . ".ods");
							}
							*/
						}
					}				
				}
			} else {
				echo "Keine Ergebnisse";
			}
		} else {
			echo "Prüfung nicht vorhanden";
		}
	} else {
			echo "Keine übergebenen Parameter";
	}
?>