<? error_reporting(E_ALL);
	date_default_timezone_set("Europe/Berlin");

    // Binde Funktionen ein
	include_once 'includes/functions.php';
	
	// Binde die DB-Connect ein
	include_once 'includes/db_connect.php';
	
	// Starte sichere Session
	sec_session_start();
	
	//	Gelber Hintergrund bei mehr als +10 Sekunden Abweichung
	$critical_abw_bcolor = "";
	$critical_abw_fcolor = "color: #fff;";
	
	// Prüfe, ob Zugang aktiv, ansonsten leite weiter
	if(isset($_SESSION['user_id'])) {
		// Baue Event ID aus aktiver User-Session
		$eid	= $_SESSION['user_id'];
	} elseif(!isset($_SESSION['user_id']) OR $_SESSION['user_id'] == "") {
		// Simuliere HTML Ausgabe
		echo	"
				<head>
				";
		include("lib/library.html");
		echo 	"
					<script>
						$('#dialog_res').hide();
					</script>
				";
		echo	"
					<meta http-equiv='refresh' content='0; url=/msdn/index.php'>
					<noscript>
						<div style=\"z-index: 5000; left: 0; position: fixed; text-align: center; width: 100%; height: 100%; background-color: rgba(48, 48, 48, 0.75);\">
							<h2 style=\"line-height: 100%; padding-top: 25%; color: #fff;\"><span style=\"border: 1px dotted #fff; padding: 25px 50px 25px 50px; background-color: rgba(255, 0, 0, 0.25)\">Bitte aktivieren Sie JavaScript!</span></h2>
						</div>
					</noscript>
				</head>
				";
	}
	
	// Prüfe, ob korrekte POST übergeben wurde
	if(isset($_POST['rid']) && !empty($_POST['rid'])) {
		// Bereinige übergebene POST und baue Runden-ID
		$rid = mysqli_real_escape_string($mysqli, utf8_encode($_POST['rid']));
		
		// Suche nach Prüfungstyp basierend auf Runden-ID
		$select_rd_info = "SELECT * FROM _main_wptable WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' LIMIT 1";
		$result_rd_info = mysqli_query($mysqli, $select_rd_info);
		$numrow_rd_info = mysqli_num_rows($result_rd_info);
		
		// Ist die gesuchte Prüfuing vorhanden
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
				
				// Beginne mit Aufbau der Tabelle
				// Tabellen Header
				echo	'
						<table width="100%" cellpadding="5px" cellspacing="0" style="border: 1px solid #FFFFFF;">
							<tr id="table_status">
								<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1"><strong>#</strong></font></td>
						';
						
				// Weise Spaltennamen den entsprechenden Variablen zu
				$getrow_rd_info = mysqli_fetch_assoc($result_rd_info);
					
				$rid_type	= $getrow_rd_info['rid_type'];
				$rd_state	= $getrow_rd_info['suspend'];
				$rd_tpos	= $getrow_rd_info['total_pos'];
				$rd_zentry	= $getrow_rd_info['z_entry'];
				
				// Prüfe, ob es sich um eine Prüfung vom Typ "Sprint" handelt
				// Keine Prüfung vom Typ "Sprint"
				if($rd_zentry == 0) {
					// Führe Tabellen Header weiter
					echo	'
								<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1"><strong>Start</strong></font></td>
							';
					
					// Prüfe auf Gesamtpositionen (Standard sind 2 --> Start und Ziel)
					// Standardprüfung mit Start und Ziel
					if($rd_tpos == 2) {
						// Führe Tabellen Header weiter und schließe diesen direkt
						echo	'
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1"><strong>Ziel</strong></font></td>
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1"><strong>Fahrtzeit</strong></font></td>
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1"><strong>Abweichung</strong></font></td>
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1"><strong>Sollzeit</strong></font></td>
								<tr>
								';
						
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
						
						// Beginne mit der Berechnung und Ausgabe der Ergebnisse
						for($a = 1; $a < (count($con_tmember) + 1); $a++) {
							// Öffne erste Tabellenzeile für Inhalt
							// Spalte: Startnummer
							echo	'
									<tr>
										<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1">' . $con_tmember[$a] . '</font></td>
									';
									
							// Suche nach Startzeit für aktuellen Teilnehmer
							$select_pre = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $con_tmember[$a] . "' AND `position` = 'Start'";
							$result_pre = mysqli_query($mysqli, $select_pre);
							$numrow_pre = mysqli_num_rows($result_pre);
							$getrow_pre = mysqli_fetch_assoc($result_pre);
							
							// Startzeit wurde gefunden (darf maximal 1 sein!)
							if($numrow_pre == 1) {
								// Spalte: Startzeit in konvertiertem Format (HH:MM:SS,UU)
								echo	'
										<td id="scrollto_' . $getrow_pre['id'] . '" align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><input type="text" class="edit_' . $getrow_pre['id'] . '" id="' . $rid . '_' . $getrow_pre['id'] . '" value="' . $getrow_pre['t_realtime'] . '" style="border: 0; background-color: transparent; font-size: x-small;" disabled /><span id="substitute_' . $getrow_pre['id'] . '"><img src="images/edit.png" title="Ergebnis editieren" alt="Ergebnis editieren" id="edit_' . $getrow_pre['id'] . '" class="edit"></img></span></td>
										';
										
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
								// Spalte: Startzeit in konvertiertem Format (HH:MM:SS,UU)
								echo	'
										<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1">&mdash;&mdash;&mdash;</font></td>
										';
										
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
								// Spalte: Zielzeit in konvertiertem Format (HH:MM:SS,UU)
								echo	'
										<td id="scrollto_' . $getrow_seq['id'] . '" align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><input type="text" class="edit_' . $getrow_seq['id'] . '" id="' . $rid . '_' . $getrow_seq['id'] . '" value="' . $getrow_seq['t_realtime'] . '" style="border: 0; background-color: transparent; font-size: x-small;" disabled /><span id="substitute_' . $getrow_seq['id'] . '"><img src="images/edit.png" title="Ergebnis editieren" alt="Ergebnis editieren" id="edit_' . $getrow_seq['id'] . '" class="edit"></img></span></td>
										';
										
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
								// Spalte: Zielzeit in konvertiertem Format (HH:MM:SS,UU)
								echo	'
										<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1">&mdash;&mdash;&mdash;</font></td>
										';
								
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
								$array_abweichung_dezimal[$con_tmember[$a]] = abs((float)$array_fahrtzeit_dezimal[$a] - (float)$sollzeit_sekunden);
								$array_abweichung_dezimal[$con_tmember[$a]] = number_format((float)$array_abweichung_dezimal[$con_tmember[$a]], 2, '.', '');
								
								//	Abweichung größer gleich 10 Sekunden
								if($array_abweichung_dezimal[$con_tmember[$a]] >= 10) {
									$critical_abw_bcolor = "background-color: #ffff00;";
									$critical_abw_fcolor = "color: #000;";
								} else {
									$critical_abw_bcolor = "";
									$critical_abw_fcolor = "";
								}
								
								// Übergebe Fahrtzeit an Funktion zur Konvertierung
								$array_abweichung_konvert[$a] = convertTime($array_abweichung_dezimal[$con_tmember[$a]]);
								
								// Setze Farbakzente für Unter- bzw. Überschreiten der Sollzeit
								if($array_fahrtzeit_dezimal[$a] >= $sollzeit_sekunden AND $array_fahrtzeit_konvert[$a] != "&mdash;&mdash;&mdash;") {
									$color = "";
								} elseif($array_fahrtzeit_dezimal[$a] < $sollzeit_sekunden AND $array_fahrtzeit_konvert[$a] != "&mdash;&mdash;&mdash;") {
									$color = "style='color: #F30000;'";
									$critical_abw_bcolor = "";
								} 
							// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
							} else {
								// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
								$array_fahrtzeit_konvert[$a]	= "&mdash;&mdash;&mdash;";
								$array_abweichung_konvert[$a]	= "&mdash;&mdash;&mdash;";
								
								// Setze Farbakzente auf leer
								$color = "";
								$critical_abw_bcolor = "";
								$critical_abw_fcolor = "color: #fff;";
							}
							
							// Spalten: Fahrtzeit, Abweichung und Sollzeit in konvertiertem Format, danach schließe Tabellenzeile		
							echo	'
										<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1">' . $array_fahrtzeit_konvert[$a] . '</font></td>
										<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; ' . $critical_abw_fcolor . ' ' . $critical_abw_bcolor . '"><font size="1" ' . $color . '><strong>' . $array_abweichung_konvert[$a] . '</strong></font></td>
										<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1">' . $sollzeit . '</font></td>
									</tr>
									';
									
							// Setze Farbakzente auf leer
							$color = ""; 
							$critical_abw_bcolor = "";
							$critical_abw_fcolor = "color: #fff;";
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
						
						// Prüfe Anzahl der Zwischenzeiten (abzgl. 2 wegen Start und Ziel [Standard])
						for($a = 0; $a < ($rd_tpos - 2); $a++) {
							echo	'
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1"><strong>ZZ ' . ($a + 1) . ' </strong></font></td>
									';
						}
						
						echo	'
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1"><strong>Ziel</strong></font></td>
								';
								
						for($a = 0; $a < ($rd_tpos - 1); $a++) {
							echo	'
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1"><strong>FZ ' . ($a + 1) . ' </strong></font></td>
									';
						}
						
						echo	'
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1"><strong>FZ &sum;</strong></font></td>
								';
								
						for($a = 0; $a < ($rd_tpos - 1); $a++) {
							echo	'
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1"><strong>ABW ' . ($a + 1) . ' </strong></font></td>
									';
						}
						
						echo	'
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1"><strong>ABW &sum;</strong></font></td>
								';
								
						for($a = 0; $a < ($rd_tpos - 1); $a++) {
							echo	'
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1"><strong>SZ ' . ($a + 1) . ' </strong></font></td>
									';
						}
						
						// Schließe Tabellen Header
						echo	'
								</tr>
								';
						
						$select_calc = "SELECT `eid`, `t_calc` FROM _race_run_events WHERE `eid` = '" . $eid . "' AND `active` = '1'";
						$result_calc = mysqli_query($mysqli, $select_calc);
						$getrow_calc = mysqli_fetch_assoc($result_calc);
							
						// Weise Berechnungsart zu
						$rd_calc = $getrow_calc['t_calc'];
						
						// Führe Berechnungen und Tabellenaufbau in Hauptschleife durch
						for($b = 1; $b < (count($con_tmember) + 1); $b++) {
							// Öffne Tabellenzeile
							echo	'
									<tr>
									';
									
							// Spalte: Startnummer
							echo	'
										<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1">' . $con_tmember[$b] . '</font></td>
									';
							
							// Hole Ergebnis für Start für aktuellen Teilnehmer
							$select_pre = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $con_tmember[$b] . "' AND `position` = 'Start'";
							$result_pre = mysqli_query($mysqli, $select_pre);
							$numrow_pre = mysqli_num_rows($result_pre);
							$getrow_pre = mysqli_fetch_assoc($result_pre);
							
							// Startzeit wurde gefunden (darf maximal 1 sein!)
							if($numrow_pre == 1) {
								// Spalte: Startzeit in konvertiertem Format (HH:MM:SS,UU)
								echo	'
											<td id="scrollto_' . $getrow_pre['id'] . '" align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><input type="text" class="edit_' . $getrow_pre['id'] . '" id="' . $rid . '_' . $getrow_pre['id'] . '" value="' . $getrow_pre['t_realtime'] . '" style="border: 0; background-color: transparent; font-size: x-small;" disabled /><span id="substitute_' . $getrow_pre['id'] . '"><img src="images/edit.png" title="Ergebnis editieren" alt="Ergebnis editieren" id="edit_' . $getrow_pre['id'] . '" class="edit"></img></span></td>
										';
										
								// Speichere als dezimale Sekunden in jeweiligen Arrays
								$array_zeit_als_dezimal[$con_tmember[$b]]["pre"] = $getrow_pre['t_time'] . "." . $getrow_pre['t_centi'];
							// Startzeit wurde nicht gefunden
							} elseif($numrow_pre == 0) {
								// Spalte: Startzeit in konvertiertem Format (HH:MM:SS,UU)
								echo	'
											<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1">&mdash;&mdash;&mdash;</font></td>
										';
										
								// Speichere als dezimale Sekunden in jeweiligen Arrays
								$array_zeit_als_dezimal[$con_tmember[$b]]["pre"] = 0;
							}
							
							// Hole Ergebnis für jede Zwischenzeit für aktuellen Teilnehmer
							for($c = 0; $c < ($rd_tpos - 2); $c++) {
								$select_opt = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $con_tmember[$b] . "' AND `position` = 'ZZ" . ($c + 1) . "'";
								$result_opt = mysqli_query($mysqli, $select_opt);
								$numrow_opt = mysqli_num_rows($result_opt);
								$getrow_opt = mysqli_fetch_assoc($result_opt);
															
								// Zwischenzeit X wurde gefunden
								if($numrow_opt == 1) {
									// Spalte: Zwischenzeit X in konvertiertem Format (HH:MM:SS,UU)
									echo	'
												<td id="scrollto_' . $getrow_opt['id'] . '" align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><input type="text" class="edit_' . $getrow_opt['id'] . '" id="' . $rid . '_' . $getrow_opt['id'] . '" value="' . $getrow_opt['t_realtime'] . '" style="border: 0; background-color: transparent; font-size: x-small;" disabled /><span id="substitute_' . $getrow_opt['id'] . '"><img src="images/edit.png" title="Ergebnis editieren" alt="Ergebnis editieren" id="edit_' . $getrow_opt['id'] . '" class="edit"></img></span></td>
											';
											
									// Speichere als dezimale Sekunden in jeweiligen Arrays
									$array_zeit_als_dezimal[$con_tmember[$b]]["zz" . ($c + 1)] = $getrow_opt['t_time'] . "." . $getrow_opt['t_centi'];
								// Zwischenzeit X wurde nicht gefunden
								} elseif($numrow_opt == 0) {
									// Spalte: Zwischenzeit X in konvertiertem Format (HH:MM:SS,UU)
									echo	'
												<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1">&mdash;&mdash;&mdash;</font></td>
											';
											
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
								// Spalte: Zielzeit in konvertiertem Format (HH:MM:SS,UU)
								echo	'
											<td id="scrollto_' . $getrow_seq['id'] . '" align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><input type="text" class="edit_' . $getrow_seq['id'] . '" id="' . $rid . '_' . $getrow_seq['id'] . '" value="' . $getrow_seq['t_realtime'] . '" style="border: 0; background-color: transparent; font-size: x-small;" disabled /><span id="substitute_' . $getrow_seq['id'] . '"><img src="images/edit.png" title="Ergebnis editieren" alt="Ergebnis editieren" id="edit_' . $getrow_seq['id'] . '" class="edit"></img></span></td>
										';
										
								// Speichere als dezimale Sekunden in jeweiligen Arrays
								$array_zeit_als_dezimal[$con_tmember[$b]]["seq"] = $getrow_seq['t_time'] . "." . $getrow_seq['t_centi'];
							// Zielzeit wurde nicht gefunden
							} elseif($numrow_seq == 0) {
								// Spalte: Zielzeit in konvertiertem Format (HH:MM:SS,UU)
								echo	'
											<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1">&mdash;&mdash;&mdash;</font></td>
										';
										
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
											
											//	Abweichung größer gleich 10 Sekunden
											if($array_abweichung_dezimal[$con_tmember[$b]] >= 10) {
												$critical_abw_bcolor = "background-color: #ffff00;";
												$critical_abw_fcolor = "color: #000;";
											} else {
												$critical_abw_bcolor = "";
												$critical_abw_fcolor = "";
											}
											
											// Übergebe Abweichung an Funktion zur Konvertierung
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] = convertTime($array_abweichung_dezimal[$con_tmember[$b]]);
											
											// Addiere Abweichung Dezimal zur Gesamtabweichung
											@$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = abs((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]] + (float)$array_abweichung_dezimal[$con_tmember[$b]]);
											$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = number_format((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]], 2, '.', '');

											// Setze Farbakzente für Unter- bzw. Überschreiten der Sollzeit
											if($array_abweichung_dezimal[$con_tmember[$b]] >= $array_sollzeit_sekunde[$d] AND $array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] != "&mdash;&mdash;&mdash;") {
												$color = "";
											} elseif($array_abweichung_dezimal[$con_tmember[$b]] < $array_sollzeit_sekunde[$d] AND $array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] != "&mdash;&mdash;&mdash;") {
												$color = "style='color: #F30000;'";
												$critical_abw_bcolor = "";
											} 
											
											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = convertTime($array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]]);
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = convertTime($array_abweichung_dezimal_gesamt[$con_tmember[$b]]);
										// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
										} else {
											// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
											$array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . $d]		= "&mdash;&mdash;&mdash;";
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d]		= "&mdash;&mdash;&mdash;";
											
											// Setze Farbakzente auf leer
											$color = ""; 
											$critical_abw_bcolor = "";
											$critical_abw_fcolor = "color: #fff;";
											
											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = "&mdash;&mdash;&mdash;";
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = "&mdash;&mdash;&mdash;";
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
												
											//	Abweichung größer gleich 10 Sekunden
											if($array_abweichung_dezimal[$con_tmember[$b]] >= 10) {
												$critical_abw_bcolor = "background-color: #ffff00;";
												$critical_abw_fcolor = "color: #000;";
											} else {
												$critical_abw_bcolor = "";
												$critical_abw_fcolor = "";
											}
											
											// Übergebe Abweichung an Funktion zur Konvertierung
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] = convertTime($array_abweichung_dezimal[$con_tmember[$b]]);
											
											// Addiere Abweichung Dezimal zur Gesamtabweichung
											$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = abs((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]] + (float)$array_abweichung_dezimal[$con_tmember[$b]]);
											$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = number_format((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]], 2, '.', '');	
											
											// Setze Farbakzente für Unter- bzw. Überschreiten der Sollzeit
											if($array_abweichung_dezimal[$con_tmember[$b]] >= $array_sollzeit_sekunde[$d] AND $array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] != "&mdash;&mdash;&mdash;") {
												$color = "";
											} elseif($array_abweichung_dezimal[$con_tmember[$b]] < $array_sollzeit_sekunde[$d] AND $array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] != "&mdash;&mdash;&mdash;") {
												$color = "style='color: #F30000;'";
												$critical_abw_bcolor = "";
											} 
											
											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = convertTime($array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]]);
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = convertTime($array_abweichung_dezimal_gesamt[$con_tmember[$b]]);
										// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
										} else {
											// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
											$array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . $d]		= "&mdash;&mdash;&mdash;";
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d]		= "&mdash;&mdash;&mdash;";
											
											// Setze Farbakzente auf leer
											$color = ""; 
											$critical_abw_bcolor = "";
											$critical_abw_fcolor = "color: #fff;";
											
											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = "&mdash;&mdash;&mdash;";
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = "&mdash;&mdash;&mdash;";
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
											
											//	Abweichung größer gleich 10 Sekunden
											if($array_abweichung_dezimal[$con_tmember[$b]] >= 10) {
												$critical_abw_bcolor = "background-color: #ffff00;";
												$critical_abw_fcolor = "color: #000;";
											} else {
												$critical_abw_bcolor = "";
												$critical_abw_fcolor = "";
											}
											
											// Übergebe Abweichung an Funktion zur Konvertierung
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] = convertTime($array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d]);
											
											// Addiere Abweichung Dezimal zur Gesamtabweichung
											@$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = abs((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]] + (float)$array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d]);
											$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = number_format((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]], 2, '.', '');
											
											// Setze Farbakzente für Unter- bzw. Überschreiten der Sollzeit
											if($array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d] >= $array_sollzeit_sekunde[$d] AND $array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] != "&mdash;&mdash;&mdash;") {
												$color = "";
											} elseif($array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d] < $array_sollzeit_sekunde[$d] AND $array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] != "&mdash;&mdash;&mdash;") {
												$color = "style='color: #F30000;'";
												$critical_abw_bcolor = "";
											} 
											
											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = convertTime($array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]]);
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = convertTime($array_abweichung_dezimal_gesamt[$con_tmember[$b]]);
										// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
										} else {
											// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
											$array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . $d]		= "&mdash;&mdash;&mdash;";
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d]		= "&mdash;&mdash;&mdash;";
											
											// Setze Farbakzente auf leer
											$color = ""; 
											$critical_abw_bcolor = "";
											$critical_abw_fcolor = "color: #fff;";
											
											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = "&mdash;&mdash;&mdash;";
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = "&mdash;&mdash;&mdash;";
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
											
											//	Abweichung größer gleich 10 Sekunden
											if($array_abweichung_dezimal[$con_tmember[$b]] >= 10) {
												$critical_abw_bcolor = "background-color: #ffff00;";
												$critical_abw_fcolor = "color: #000;";
											} else {
												$critical_abw_bcolor = "";
												$critical_abw_fcolor = "";
											}
												
											// Übergebe Abweichung an Funktion zur Konvertierung
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] = convertTime($array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d]);
											
											// Addiere Abweichung Dezimal zur Gesamtabweichung
											$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = abs((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]] + (float)$array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d]);
											$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = number_format((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]], 2, '.', '');	
											
											// Setze Farbakzente für Unter- bzw. Überschreiten der Sollzeit
											if($array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d] >= $array_sollzeit_sekunde[$d] AND $array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] != "&mdash;&mdash;&mdash;") {
												$color = "";
											} elseif($array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d] < $array_sollzeit_sekunde[$d] AND $array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] != "&mdash;&mdash;&mdash;") {
												$color = "style='color: #F30000;'";
												$critical_abw_bcolor = "";
											} 
											
											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = convertTime($array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]]);
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = convertTime($array_abweichung_dezimal_gesamt[$con_tmember[$b]]);
										// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
										} else {
											// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
											$array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . $d]		= "&mdash;&mdash;&mdash;";
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d]		= "&mdash;&mdash;&mdash;";
											
											// Setze Farbakzente auf leer
											$color = ""; 
											$critical_abw_bcolor = "";
											$critical_abw_fcolor = "color: #fff;";
											
											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = "&mdash;&mdash;&mdash;";
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = "&mdash;&mdash;&mdash;";
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
											
											//	Abweichung größer gleich 10 Sekunden
											if($array_abweichung_dezimal[$con_tmember[$b]] >= 10) {
												$critical_abw_bcolor = "background-color: #ffff00;";
												$critical_abw_fcolor = "color: #000;";
											} else {
												$critical_abw_bcolor = "";
												$critical_abw_fcolor = "";
											}
												
											// Übergebe Abweichung an Funktion zur Konvertierung
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] = convertTime($array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d]);
											
											// Addiere Abweichung Dezimal zur Gesamtabweichung
											$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = abs((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]] + (float)$array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d]);
											$array_abweichung_dezimal_gesamt[$con_tmember[$b]] = number_format((float)$array_abweichung_dezimal_gesamt[$con_tmember[$b]], 2, '.', '');	
											
											// Setze Farbakzente für Unter- bzw. Überschreiten der Sollzeit
											if($array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d] >= $array_sollzeit_sekunde[$d] AND $array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] != "&mdash;&mdash;&mdash;") {
												$color = "";
											} elseif($array_abweichung_dezimal[$con_tmember[$b]]["ab" . $d] < $array_sollzeit_sekunde[$d] AND $array_abweichung_konvert[$con_tmember[$b]]["ab" . $d] != "&mdash;&mdash;&mdash;") {
												$color = "style='color: #F30000;'";
												$critical_abw_bcolor = "";
											} 
											
											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = convertTime($array_fahrtzeit_dezimal_gesamt[$con_tmember[$b]]);
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = convertTime($array_abweichung_dezimal_gesamt[$con_tmember[$b]]);
										// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
										} else {
											// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
											$array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . $d]		= "&mdash;&mdash;&mdash;";
											$array_abweichung_konvert[$con_tmember[$b]]["ab" . $d]		= "&mdash;&mdash;&mdash;";
											
											// Setze Farbakzente auf leer
											$color = ""; 
											$critical_abw_bcolor = "";
											$critical_abw_fcolor = "color: #fff;";
											
											// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
											$array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] = "&mdash;&mdash;&mdash;";
											$array_abweichung_konvert_gesamt[$con_tmember[$b]] = "&mdash;&mdash;&mdash;";
										}
									}
								} elseif($rd_calc == 3) {
									
								}
								
								// Prüfe letztmals auf Berechnungsmodus und gebe individuellen Wert aus
								// Bei Modus 1 (Ab Start) fällt die Gesamtfahrtzeit weg. An Ihre Stelle
								// tritt die Differenz zwischen Start und Ziel (da nicht überschreitbar)
								if($rd_calc == 1) {
									if($d >= 0 AND $d < (($rd_tpos - 1) - 1)) {
										// Gebe Fahrtzeit aus (HH:MM:SS,UU)					
										echo	'
												<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1">' . $array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . $d] . '</font></td>
												';
									} elseif($d >= 0 AND $d == (($rd_tpos - 1) - 1)) {
										// Gebe Hinweis auf Gesamtfahrtzeit aus				
										echo	'
												<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1">&#9495;&mdash;&rarr;</font></td>
												';
									}										
								} elseif($rd_calc == 2) {
									// Gebe Fahrtzeit aus (HH:MM:SS,UU)					
									echo	'
											<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; ' . $critical_abw_fcolor . ' ' . $critical_abw_bcolor . '"><font size="1">' . $array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . $d] . '</font></td>
											';
								}
							}

							// Prüfe letztmals auf Berechnungsmodus und gebe individuellen Wert aus
							if($rd_calc == 1) {
								// Gebe Gesamtfahrtzeit aus	(HH:MM:SS,UU)					
								echo	'
											<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1">' . $array_fahrtzeit_konvert[$con_tmember[$b]]["fz" . (($rd_tpos - 1) - 1)] . '</font></td>
										';
							} elseif($rd_calc == 2) {
								// Gebe Gesamtfahrtzeit aus	(HH:MM:SS,UU)					
								echo	'
											<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1">' . $array_fahrtzeit_konvert_gesamt[$con_tmember[$b]] . '</font></td>
										';
							}
							
							// Gebe jede Abweichung basierend auf den Gesamtpositionen minus 2 (Start und Ziel) aus
							for($e = 0; $e < ($rd_tpos - 1); $e++) {
								if($e < ($rd_tpos - 1)) {
									// Gebe Abweichung aus (HH:MM:SS,UU)						
									echo	'
												<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1" ' . $color . '><strong>' . $array_abweichung_konvert[$con_tmember[$b]]["ab" . $e] . '</strong></font></td>
											';
								}
							}
								
							// Gebe abschließend die aufaddierte Gesamtabweichung aus (HH:MM:SS,UU)						
							echo	'
											<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1">' . $array_abweichung_konvert_gesamt[$con_tmember[$b]] . '</font></td>
									';
									
							// OUTPUT EVERY RESULT DIFFERENCE BASED ON TOTAL POSITION MINUS 1
							for($f = 0; $f < ($rd_tpos - 1); $f++) {
								if($f < ($rd_tpos - 1)) {
									// OUTPUT TARGET TIME EACH FOR DIRECT COMPARISON					
									echo	'
											<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1">' . $array_sollzeit_konvert[$f] . '</font></td>
											';
								}
							}
							
							// Setze Farbakzente auf leer
							$color = ""; 
							$critical_abw_bcolor = "";
							$critical_abw_fcolor = "color: #fff;";
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
						
					// Führe Tabellen Header weiter und schließe diesen direkt
					echo	'
								<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1"><strong>Fahrtzeit</strong></font></td>
								<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1"><strong>Abweichung</strong></font></td>
								<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1"><strong>Sollzeit</strong></font></td>
							</tr>
							';
						
					// Beginne mit der Berechnung und Ausgabe der Ergebnisse
					for($a = 1; $a < (count($con_tmember) + 1); $a++) {
						// Öffne erste Tabellenzeile für Inhalt
						// Spalte: Startnummer
						echo	'
								<tr>
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1">' . $con_tmember[$a] . '</font></td>
								';
								
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
							$array_abweichung_dezimal[$con_tmember[$a]] = abs($array_fahrtzeit_dezimal[$a] - $sollzeit_sekunden);
							$array_abweichung_dezimal[$con_tmember[$a]] = number_format((float)$array_abweichung_dezimal[$con_tmember[$a]], 2, '.', '');
							
							//	Abweichung größer gleich 10 Sekunden
							if($array_abweichung_dezimal[$con_tmember[$a]] >= 10) {
								$critical_abw_bcolor = "background-color: #ffff00;";
								$critical_abw_fcolor = "color: #000;";
							} else {
								$critical_abw_bcolor = "";
								$critical_abw_fcolor = "";
							}
							
							// Übergebe Fahrtzeit an Funktion zur Konvertierung
							$array_abweichung_konvert[$a] = convertTime($array_abweichung_dezimal[$con_tmember[$a]]);
							
							// Setze Farbakzente für Unter- bzw. Überschreiten der Sollzeit
							if($array_fahrtzeit_dezimal[$a] >= $sollzeit_sekunden AND $array_fahrtzeit_konvert[$a] != "&mdash;&mdash;&mdash;") {
								$color = "";
							} elseif($array_fahrtzeit_dezimal[$a] < $sollzeit_sekunden AND $array_fahrtzeit_konvert[$a] != "&mdash;&mdash;&mdash;") {
								$color = "style='color: #F30000;'";
								$critical_abw_bcolor = "";
							} 
						// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
						} else {
							// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
							$array_fahrtzeit_konvert[$a]	= "&mdash;&mdash;&mdash;";
							$array_abweichung_konvert[$a]	= "&mdash;&mdash;&mdash;";
							
							// Setze Farbakzente auf leer
							$color = "";
							$critical_abw_bcolor = "";
							$critical_abw_fcolor = "color: #fff;";
						}
						
						// Spalten: Fahrtzeit, Abweichung und Sollzeit in konvertiertem Format, danach schließe Tabellenzeile		
						echo	'
									<td id="scrollto_' . $getrow_spr['id'] . '" align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><input type="text" class="edit_' . $getrow_spr['id'] . '" id="' . $rid . '_' . $getrow_spr['id'] . '" value="' . $getrow_spr['t_realtime'] . '" style="border: 0; background-color: transparent; font-size: x-small;" disabled /><span id="substitute_' . $getrow_spr['id'] . '"><img src="images/edit.png" title="Ergebnis editieren" alt="Ergebnis editieren" id="edit_' . $getrow_spr['id'] . '" class="edit"></img></span></td>
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; ' . $critical_abw_fcolor . ' ' . $critical_abw_bcolor . '"><font size="1" ' . $color . '><strong>' . $array_abweichung_konvert[$a] . '</strong></font></td>
									<td align="center" style="padding-top: 2.5px; padding-bottom: 2.5px; border-bottom: 1px solid #9E9482; border-right: 1px solid #9E9482; color: #fff;"><font size="1">' . $sollzeit . '</font></td>
								</tr>
								';
								
						// Setze Farbakzente auf leer
						$color = ""; 
						$critical_abw_bcolor = "";
						$critical_abw_fcolor = "color: #fff;";
					}

					// CLOSE TABLE
					echo	'
							</table>
							';				
				} 			
			// Es wurden keine Ergebnisse gefunden
			} elseif($numrow_rd == 0) {
					echo 	'
							<table width="100%" cellspacing="5px" style="border: 1px solid #9E9482;">
								<tr id="table_status">
									<td align="center" colspan="2">Keine Rennergebnisse verfügbar</td>
								</tr>
							</table>
							';
				}
		// Die gesuchte Prüfung ist nicht existent (Manupulationsversuch?)
		} elseif($numrow_rd_info == 0) {
			echo 	'
					<table width="100%" cellspacing="5px" style="border: 1px solid #9E9482;">
						<tr id="table_status">
							<td align="center" colspan="2">Diese Prüfung ist nicht existent</td>
						</tr>
					</table>
					';
		}
	// Direktzugriff wird mit Hinweis auf Prüfungswahl ausgegeben
	} else {
		echo 	'
				<table width="100%" cellspacing="5px" style="border: 1px solid #9E9482;">
					<tr id="table_status">
						<td align="center" colspan="2">Bitte Prüfung auswählen</td>
					</tr>
				</table>
				';
	}