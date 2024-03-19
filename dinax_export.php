<?php 
	// CHECK IF GET HAS BEEN SUBMITTED
	if(	$_GET['eid'] != 0 OR !empty($_GET['eid']) OR
		$_GET['rid'] != 0 OR !empty($_GET['rid'])		
	) {
		// SET ERROR LEVEL
		error_reporting(E_ALL);
		
		// SET TIMEZONE
		date_default_timezone_set("Europe/Berlin");
		
		// INCLUDE FUNCTIONS
		include_once 'includes/functions.php';
		
		// INCLUDE DB_CONNECT
		include_once 'includes/db_connect.php';
		
		// START SECURE SESSION
		sec_session_start();
		
		// CUSTOM NAVBAR
		if(login_check($mysqli) == true) {
			// IF LOGIN CHECK IS PASSED, CHECK FOR IDENTICAL USER ID
			if($_SESSION['user_id'] == $_GET['eid']) {		
				// REWRITE RID
				$rid = mysqli_real_escape_string($mysqli, utf8_encode($_GET['rid']));
			
				// CHECK FOR VALID RID
				if(isset($_GET['rid']) AND $_GET['rid'] != "" OR $_GET['rid'] != 0 OR !empty($_GET['rid'])) {			
					// SEARCH FOR USER EVENTS
					// CREATE EVENT ID FROM ACTIVE SESSION
					$eid	= $_SESSION['user_id'];
					
					// SEARCH FOR EVENTS FROM LOGGED IN USER
					$select_event = "SELECT * FROM _race_run_events WHERE `eid` = '" . $eid . "' AND `active` = '1'";
					$result_event = mysqli_query($mysqli, $select_event);
					$numrow_event = mysqli_num_rows($result_event);
					
					// SEARCH FOR EVENTS FROM LOGGED IN USER
					if($numrow_event == 1) {
						$getrow_event = mysqli_fetch_assoc($result_event);
						$image_path = $getrow_event['image_path_100'];
						
						// BUILD ROUND DESCRIPTION
						$rd_desc = $getrow_event['master_rid_type'] . $rid;
					} elseif($numrow_event == 0) {
						header("Location: my_event.php");
					}
				} else {
					header("Location: my_event.php");
				}
			} elseif($_SESSION['user_id'] != $_GET['eid']) {
				echo "<script>window.close();</script>";
			}
		} else {
			echo "<script>window.close();</script>";
		}
	} else {
		echo "<script>window.close();</script>";
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
		<!--	INCLUDING STYLESHEET	-->
		<link rel="stylesheet" type="text/css" href="css/dinax_export.css" />
		
		<style>
			input[type="button"]:disabled {
				padding: 2px;
				border: solid 1px #dcdcdc;
				transition: border 0.3s;
				background: transparent;
				background-color: #A09A8E;
				color: #FFFFFF;
				width: 175px;
			}			
			
			input[type="button"] {
				padding: 2px;
				border: solid 1px #dcdcdc;
				transition: border 0.3s;
				background: transparent;
				background-color: #FFFFFF;
				color: #8E6516;
				width: 175px;
			}
			
			input[type="button"]:focus {
				border-bottom: solid 1px #8E6516;
				color: #8E6516;
				padding: 2px;
				transition: border 0.3s;
				background: #FFFFFF;
				width: 175px;
			}
			
			input[type="button"]:read-only {
				padding: 2px;
				border: solid 1px #dcdcdc;
				transition: border 0.3s;
				background: transparent;
				background-color: #A09A8E;
				color: #FFFFFF;
				width: 175px;
			}
		</style>
		
		<script>
			function printDiv(divName) {
				var printContents = document.getElementById(divName).innerHTML;
				var originalContents = document.body.innerHTML;

				document.body.innerHTML = printContents;

				window.print();

				document.body.innerHTML = originalContents;
			}
		</script>
				
		<noscript>
			<div style="z-index: 5000; left: 0; position: fixed; text-align: center; width: 100%; height: 100%; background-color: rgba(48, 48, 48, 0.75);">
				<h2 style="line-height: 100%; padding-top: 25%;">
					<span style="border: 1px dotted #fff; padding: 25px 50px 25px 50px; background-color: rgba(255, 0, 0, 0.25)">Bitte aktivieren Sie JavaScript!</span>
				</h2>
			</div>
		</noscript>
	</head>
	<body>
		<div id="non-printable">
			<div id="print_marker">
				<span id="print_marker_content">
					<input type="button" id="printbtn" onclick="printDiv('printable');" value="<?php echo $rd_desc; ?> Auswertung drucken" />
					<select name="export_type" id="export_type" style="border: 1px solid #DCDCDC; background: transparent; background-color: #FFFFFF; color: #8E6516; width: 175px; height: 23px;" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
						<option selected="selected" disabled="disabled">Sortieren nach</option>
						<option value="dinax_export.php?eid=<?php echo $eid; ?>&rid=<?php echo $rid; ?>&sort=abw">Abweichung</option>
						<option value="dinax_export.php?eid=<?php echo $eid; ?>&rid=<?php echo $rid; ?>&sort=cls">Klasse</option>
					</select>
				</span>
			</div>
		</div>
		<div id="printable">
			<page size="A4" layout="portrait">
				<div class="margin">
					<p>
						<table width="100%" cellspacing="0" cellpadding="5px" style="border: 1px solid #8E6516;">
							<tr>
								<td align="left"><h2>Gesamtergebnis <?php echo $rd_desc; ?></h2></td>
								<td align="right"><?php echo "<img src=\"" . $image_path . "\">"; ?></td>
							</tr>
						</table>
					</p>
						
					<p>				
						<?php
							// Suche nach Prüfungstyp basierend auf Runden-ID
							$select_rd_info = "SELECT * FROM _main_wptable WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' LIMIT 1";
							$result_rd_info = mysqli_query($mysqli, $select_rd_info);
							$numrow_rd_info = mysqli_num_rows($result_rd_info);
							
							// Ist die gesuchte Prüfung vorhanden
							if($numrow_rd_info == 1) {
								// Suche nach Ergebnissen basierend auf aktiver User-Session und übergebener Prüfungs-ID
								$select_rd_id = "SELECT * FROM _main_wpresults WHERE `rid` = '" . $rid . "' AND `eid` = '" . $eid . "' ORDER BY `sid` ASC";
								$result_rd_id = mysqli_query($mysqli, $select_rd_id);
								$numrow_rd_id = mysqli_num_rows($result_rd_id);
							
								// Es wurden Ergebnisse gefunden
								if($numrow_rd_id > 0) {
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
									$select_tmember = "SELECT * FROM _optio_tmembers WHERE `eid` = '" . $eid . "' ORDER BY `sid` ASC";
									$result_tmember = mysqli_query($mysqli, $select_tmember);
									
									while($getrow_tmember = mysqli_fetch_assoc($result_tmember)) {
										// Speichere aktuellen Durchlauf in Index mit aktuellem Zählerstand
										$con_tmember[$tcount]['sid'] = $getrow_tmember['sid'];
										$con_tmember[$tcount]['class'] = $getrow_tmember['class'];
										$con_tmember[$tcount]['driver'] = $getrow_tmember['vname_1'] . " " . $getrow_tmember['nname_1'];
										$con_tmember[$tcount]['codriver'] = $getrow_tmember['vname_2'] . " " . $getrow_tmember['nname_2'];
										$con_tmember[$tcount]['vehicle'] = $getrow_tmember['fabrikat'] . " " . $getrow_tmember['typ'];
										
										// Stelle sicher, dass auch Baujahr gegeben ist
										if(	$getrow_tmember['baujahr'] == 0 OR
											$getrow_tmember['baujahr'] == "" OR
											$getrow_tmember['baujahr'] == " " OR
											$getrow_tmember['baujahr'] == null OR
											empty($getrow_tmember['baujahr'])											
										) {
											$con_tmember[$tcount]['built'] = 9999;
										} else {
											$con_tmember[$tcount]['built'] = $getrow_tmember['baujahr'];
										}
										
										$con_tmember[$tcount]['raw_deviation'] = 0;
										$con_tmember[$tcount]['edt_deviation'] = 0;
										
										// Erhöhe danach Zählerstand um eins
										$tcount++;
									}
									
									// Beginne mit Aufbau der Tabelle
									// Tabellen Header
									echo	'
											<table width="100%" cellspacing="0" cellpadding="5px" border="1" bordercolor="#8E6516" style="border-collapse: collapse; font-size: small;">
												<tr>
													<th>&#8470;</th>
													<th>#</th>
													<th>Kl.</th>
													<td><strong>Fahrer</strong></td>
													<td><strong>Beifahrer</strong></td>
													<td><strong>Fahrzeug</strong></td>
													<th>Baujahr</th>
													<th>Abweichung</th>
												</tr>
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
											
											// Beginne mit der Berechnung und Ausgabe der Ergebnisse
											for($a = 1; $a < (count($con_tmember) + 1); $a++) {
												// Suche nach Startzeit für aktuellen Teilnehmer
												$select_pre = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $con_tmember[$a]['sid'] . "' AND `position` = 'Start'";
												$result_pre = mysqli_query($mysqli, $select_pre);
												$numrow_pre = mysqli_num_rows($result_pre);
												$getrow_pre = mysqli_fetch_assoc($result_pre);
												
												// Startzeit wurde gefunden (darf maximal 1 sein!)
												if($numrow_pre == 1) {
													// Speichere als dezimale Sekunden in jeweiligen Arrays
													$array_zeit_als_dezimal[$a]["pre"] = $getrow_pre['t_time'] . "." . $getrow_pre['t_centi'];
																	
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
													$array_zeit_als_dezimal[$a]["pre"] = 0;
												}
												
												// Suche nach Zielzeit für aktuellen Teilnehmer
												$select_seq = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $con_tmember[$a]['sid'] . "' AND `position` = 'Ziel'";
												$result_seq = mysqli_query($mysqli, $select_seq);
												$numrow_seq = mysqli_num_rows($result_seq);
												$getrow_seq = mysqli_fetch_assoc($result_seq);
													
												// Zielzeit wurde gefunden (darf maximal 1 sein!)
												if($numrow_seq == 1) {
													// Speichere als dezimale Sekunden in jeweiligen Arrays
													$array_zeit_als_dezimal[$a]["seq"] = $getrow_seq['t_time'] . "." . $getrow_seq['t_centi'];
									
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
													$array_zeit_als_dezimal[$a]["seq"] = 0;
												}
												
												// Starte mit der Berechnung, vorausgesetzt es handelt sich nicht um Leerwerte
												if($array_zeit_als_dezimal[$a]["pre"] != 0 AND $array_zeit_als_dezimal[$a]["seq"] != 0) {
													// Berechne Fahrtzeit
													$array_fahrtzeit_dezimal[$a] = abs((float)$array_zeit_als_dezimal[$a]["seq"] - (float)$array_zeit_als_dezimal[$a]["pre"]);
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
													$array_fahrtzeit_konvert[$a]	= "&mdash;&mdash;&mdash;";
													$array_abweichung_konvert[$a]	= "&mdash;&mdash;&mdash;";
													
													$array_abweichung_dezimal[$a] = 9999;
												}
												
												// Speichere abschließend die aufaddierte Gesamtabweichung						
												$con_tmember[$a]['raw_deviation'] = $array_abweichung_dezimal[$a];

												// Speichere abschließend die aufaddierte Gesamtabweichung (HH:MM:SS,UU)						
												$con_tmember[$a]['edt_deviation'] = $array_abweichung_konvert[$a];
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
											
											$select_calc = "SELECT `eid`, `t_calc` FROM _race_run_events WHERE `eid` = '" . $eid . "' AND `active` = '1'";
											$result_calc = mysqli_query($mysqli, $select_calc);
											$getrow_calc = mysqli_fetch_assoc($result_calc);
												
											// Weise Berechnungsart zu
											$rd_calc = $getrow_calc['t_calc'];
											
											// Führe Berechnungen und Tabellenaufbau in Hauptschleife durch
											for($b = 1; $b < (count($con_tmember) + 1); $b++) {
												// Hole Ergebnis für Start für aktuellen Teilnehmer
												$select_pre = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $con_tmember[$b]['sid'] . "' AND `position` = 'Start'";
												$result_pre = mysqli_query($mysqli, $select_pre);
												$numrow_pre = mysqli_num_rows($result_pre);
												$getrow_pre = mysqli_fetch_assoc($result_pre);
												
												// Startzeit wurde gefunden (darf maximal 1 sein!)
												if($numrow_pre == 1) {
													// Speichere als dezimale Sekunden in jeweiligen Arrays
													$array_zeit_als_dezimal[$b]["pre"] = $getrow_pre['t_time'] . "." . $getrow_pre['t_centi'];
												// Startzeit wurde nicht gefunden
												} elseif($numrow_pre == 0) {
													// Speichere als dezimale Sekunden in jeweiligen Arrays
													$array_zeit_als_dezimal[$b]["pre"] = 0;
												}
												
												// Hole Ergebnis für jede Zwischenzeit für aktuellen Teilnehmer
												for($c = 0; $c < ($rd_tpos - 2); $c++) {
													$select_opt = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $con_tmember[$b]['sid'] . "' AND `position` = 'ZZ" . ($c + 1) . "'";
													$result_opt = mysqli_query($mysqli, $select_opt);
													$numrow_opt = mysqli_num_rows($result_opt);
													$getrow_opt = mysqli_fetch_assoc($result_opt);
																				
													// Zwischenzeit X wurde gefunden
													if($numrow_opt == 1) {
														// Speichere als dezimale Sekunden in jeweiligen Arrays
														$array_zeit_als_dezimal[$b]["zz" . ($c + 1)] = $getrow_opt['t_time'] . "." . $getrow_opt['t_centi'];
													// Zwischenzeit X wurde nicht gefunden
													} elseif($numrow_opt == 0) {
														// Speichere als dezimale Sekunden in jeweiligen Arrays
														$array_zeit_als_dezimal[$b]["zz" . ($c + 1)] = 0;
													}
												}
												
												// Hole Ergebnis für Ziel für aktuellen Teilnehmer
												$select_seq = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $con_tmember[$b]['sid'] . "' AND `position` = 'Ziel'";
												$result_seq = mysqli_query($mysqli, $select_seq);
												$numrow_seq = mysqli_num_rows($result_seq);
												$getrow_seq = mysqli_fetch_assoc($result_seq);
												
												// Zielzeit wurde gefunden (darf maximal 1 sein!)
												if($numrow_seq == 1) {
													// Speichere als dezimale Sekunden in jeweiligen Arrays
													$array_zeit_als_dezimal[$b]["seq"] = $getrow_seq['t_time'] . "." . $getrow_seq['t_centi'];
												// Zielzeit wurde nicht gefunden
												} elseif($numrow_seq == 0) {
													// Speichere als dezimale Sekunden in jeweiligen Arrays
													$array_zeit_als_dezimal[$b]["seq"] = 0;
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
															if($array_zeit_als_dezimal[$b]["pre"] != 0 AND $array_zeit_als_dezimal[$b]["zz" . ($d + 1)] != 0) {
																// Berechne Fahrtzeit
																$array_fahrtzeit_dezimal[$b] = abs((float)$array_zeit_als_dezimal[$b]["zz" . ($d + 1)] - (float)$array_zeit_als_dezimal[$b]["pre"]);
																$array_fahrtzeit_dezimal[$b] = number_format((float)$array_fahrtzeit_dezimal[$b], 2, '.', '');
																	
																// Übergebe Fahrtzeit an Funktion zur Konvertierung
																$array_fahrtzeit_konvert[$b]["fz" . $d] = convertTime($array_fahrtzeit_dezimal[$b]);
																
																// Addiere Fahrtzeit Dezimal zur Gesamtfahrtzeit
																@$array_fahrtzeit_dezimal_gesamt[$b] = abs((float)$array_fahrtzeit_dezimal_gesamt[$b] + (float)$array_fahrtzeit_dezimal[$b]);
																$array_fahrtzeit_dezimal_gesamt[$b] = number_format((float)$array_fahrtzeit_dezimal_gesamt[$b], 2, '.', '');
																
																// Berechne Abweichung
																$array_abweichung_dezimal[$b] = abs((float)$array_fahrtzeit_dezimal[$b] - (float)$array_sollzeit_sekunde[$d]);
																$array_abweichung_dezimal[$b] = number_format((float)$array_abweichung_dezimal[$b], 2, '.', '');
																	
																// Übergebe Abweichung an Funktion zur Konvertierung
																$array_abweichung_konvert[$b]["ab" . $d] = convertTime($array_abweichung_dezimal[$b]);
																
																// Addiere Abweichung Dezimal zur Gesamtabweichung
																@$array_abweichung_dezimal_gesamt[$b] = abs((float)$array_abweichung_dezimal_gesamt[$b] + (float)$array_abweichung_dezimal[$b]);
																$array_abweichung_dezimal_gesamt[$b] = number_format((float)$array_abweichung_dezimal_gesamt[$b], 2, '.', '');

																// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
																$array_fahrtzeit_konvert_gesamt[$b] = convertTime($array_fahrtzeit_dezimal_gesamt[$b]);
																$array_abweichung_konvert_gesamt[$b] = convertTime($array_abweichung_dezimal_gesamt[$b]);
															// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
															} else {
																// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
																$array_fahrtzeit_konvert[$b]["fz" . $d]		= "&mdash;&mdash;&mdash;";
																$array_abweichung_konvert[$b]["ab" . $d]		= "&mdash;&mdash;&mdash;";
																
																// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
																$array_fahrtzeit_konvert_gesamt[$b] = "&mdash;&mdash;&mdash;";
																$array_abweichung_konvert_gesamt[$b] = "&mdash;&mdash;&mdash;";
																
																// Workaround für Gesamtabweichung als Dezimales
																$array_abweichung_dezimal_gesamt[$b]	= 999999999999;
															}								
														// Ende mit Differenz aus Zielzeit abzgl. Startzeit
														} elseif($d >= 0 AND $d == (($rd_tpos - 1) - 1)) {
															// Starte mit der Berechnung, vorausgesetzt es handelt sich nicht um Leerwerte
															if($array_zeit_als_dezimal[$b]["pre"] != 0 AND $array_zeit_als_dezimal[$b]["seq"] != 0) {
																// Berechne Fahrtzeit
																$array_fahrtzeit_dezimal[$b] = abs((float)$array_zeit_als_dezimal[$b]["seq"] - (float)$array_zeit_als_dezimal[$b]["pre"]);
																$array_fahrtzeit_dezimal[$b] = number_format((float)$array_fahrtzeit_dezimal[$b], 2, '.', '');
																	
																// Übergebe Fahrtzeit an Funktion zur Konvertierung
																$array_fahrtzeit_konvert[$b]["fz" . $d] = convertTime($array_fahrtzeit_dezimal[$b]);
																
																// Addiere Fahrtzeit Dezimal zur Gesamtfahrtzeit
																$array_fahrtzeit_dezimal_gesamt[$b] = abs((float)$array_fahrtzeit_dezimal_gesamt[$b] + (float)$array_fahrtzeit_dezimal[$b]);
																$array_fahrtzeit_dezimal_gesamt[$b] = number_format((float)$array_fahrtzeit_dezimal_gesamt[$b], 2, '.', '');
																
																// Berechne Abweichung
																$array_abweichung_dezimal[$b] = abs((float)$array_fahrtzeit_dezimal[$b] - (float)$array_sollzeit_sekunde[$d]);
																$array_abweichung_dezimal[$b] = number_format((float)$array_abweichung_dezimal[$b], 2, '.', '');
																	
																// Übergebe Abweichung an Funktion zur Konvertierung
																$array_abweichung_konvert[$b]["ab" . $d] = convertTime($array_abweichung_dezimal[$b]);
																
																// Addiere Abweichung Dezimal zur Gesamtabweichung
																$array_abweichung_dezimal_gesamt[$b] = abs((float)$array_abweichung_dezimal_gesamt[$b] + (float)$array_abweichung_dezimal[$b]);
																$array_abweichung_dezimal_gesamt[$b] = number_format((float)$array_abweichung_dezimal_gesamt[$b], 2, '.', '');	
																
																// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
																$array_fahrtzeit_konvert_gesamt[$b] = convertTime($array_fahrtzeit_dezimal_gesamt[$b]);
																$array_abweichung_konvert_gesamt[$b] = convertTime($array_abweichung_dezimal_gesamt[$b]);
															// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
															} else {
																// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
																$array_fahrtzeit_konvert[$b]["fz" . $d]		= "&mdash;&mdash;&mdash;";
																$array_abweichung_konvert[$b]["ab" . $d]		= "&mdash;&mdash;&mdash;";
																
																// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
																$array_fahrtzeit_konvert_gesamt[$b] = "&mdash;&mdash;&mdash;";
																$array_abweichung_konvert_gesamt[$b] = "&mdash;&mdash;&mdash;";
																
																// Workaround für Gesamtabweichung als Dezimales
																$array_abweichung_dezimal_gesamt[$b]	= 999999999999;
															}
														}
													} elseif($rd_calc == 2) {
														// Beginne bei erster Zwischenzeit abzgl. Startzeit
														if($d == 0) {
															// Starte mit der Berechnung, vorausgesetzt es handelt sich nicht um Leerwerte
															if($array_zeit_als_dezimal[$b]["pre"] != 0 AND $array_zeit_als_dezimal[$b]["zz" . ($d + 1)] != 0) {
																// Berechne Fahrtzeit
																$array_fahrtzeit_dezimal[$b]["fz" . $d] = abs((float)$array_zeit_als_dezimal[$b]["zz" . ($d + 1)] - (float)$array_zeit_als_dezimal[$b]["pre"]);
																$array_fahrtzeit_dezimal[$b]["fz" . $d] = number_format((float)$array_fahrtzeit_dezimal[$b]["fz" . $d], 2, '.', '');
																	
																// Übergebe Fahrtzeit an Funktion zur Konvertierung
																$array_fahrtzeit_konvert[$b]["fz" . $d] = convertTime($array_fahrtzeit_dezimal[$b]["fz" . $d]);
																
																// Addiere Fahrtzeit Dezimal zur Gesamtfahrtzeit
																@$array_fahrtzeit_dezimal_gesamt[$b] = abs((float)$array_fahrtzeit_dezimal_gesamt[$b] + (float)$array_fahrtzeit_dezimal[$b]["fz" . $d]);
																$array_fahrtzeit_dezimal_gesamt[$b] = number_format((float)$array_fahrtzeit_dezimal_gesamt[$b], 2, '.', '');
																
																// Berechne Abweichung
																$array_abweichung_dezimal[$b]["ab" . $d] = abs((float)$array_fahrtzeit_dezimal[$b]["fz" . $d] - (float)$array_sollzeit_sekunde[$d]);
																$array_abweichung_dezimal[$b]["ab" . $d] = number_format((float)$array_abweichung_dezimal[$b]["ab" . $d], 2, '.', '');
																	
																// Übergebe Abweichung an Funktion zur Konvertierung
																$array_abweichung_konvert[$b]["ab" . $d] = convertTime($array_abweichung_dezimal[$b]["ab" . $d]);
																
																// Addiere Abweichung Dezimal zur Gesamtabweichung
																@$array_abweichung_dezimal_gesamt[$b] = abs((float)$array_abweichung_dezimal_gesamt[$b] + (float)$array_abweichung_dezimal[$b]["ab" . $d]);
																$array_abweichung_dezimal_gesamt[$b] = number_format((float)$array_abweichung_dezimal_gesamt[$b], 2, '.', '');
																
																// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
																$array_fahrtzeit_konvert_gesamt[$b] = convertTime($array_fahrtzeit_dezimal_gesamt[$b]);
																$array_abweichung_konvert_gesamt[$b] = convertTime($array_abweichung_dezimal_gesamt[$b]);
															// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
															} else {
																// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
																$array_fahrtzeit_konvert[$b]["fz" . $d]		= "&mdash;&mdash;&mdash;";
																$array_abweichung_konvert[$b]["ab" . $d]		= "&mdash;&mdash;&mdash;";
																
																// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
																$array_fahrtzeit_konvert_gesamt[$b] = "&mdash;&mdash;&mdash;";
																$array_abweichung_konvert_gesamt[$b] = "&mdash;&mdash;&mdash;";
																
																// Workaround für Gesamtabweichung als Dezimales
																$array_abweichung_dezimal_gesamt[$b]	= 999999999999;
															}
														// Verrechnene Zwischenzeiten miteinander (ZZX - ZZX-1)
														} elseif($d >= 1 AND $d < (($rd_tpos - 1) - 1)) {
															// Starte mit der Berechnung, vorausgesetzt es handelt sich nicht um Leerwerte
															if($array_zeit_als_dezimal[$b]["zz" . $d] != 0 AND $array_zeit_als_dezimal[$b]["zz" . ($d + 1)] != 0) {
																// Berechne Fahrtzeit
																$array_fahrtzeit_dezimal[$b]["fz" . $d] = abs((float)$array_zeit_als_dezimal[$b]["zz" . ($d + 1)] - (float)$array_zeit_als_dezimal[$b]["zz" . $d]);
																$array_fahrtzeit_dezimal[$b]["fz" . $d] = number_format((float)$array_fahrtzeit_dezimal[$b]["fz" . $d], 2, '.', '');
																	
																// Übergebe Fahrtzeit an Funktion zur Konvertierung
																$array_fahrtzeit_konvert[$b]["fz" . $d] = convertTime($array_fahrtzeit_dezimal[$b]["fz" . $d]);
																
																// Addiere Fahrtzeit Dezimal zur Gesamtfahrtzeit
																$array_fahrtzeit_dezimal_gesamt[$b] = abs((float)$array_fahrtzeit_dezimal_gesamt[$b] + (float)$array_fahrtzeit_dezimal[$b]["fz" . $d]);
																$array_fahrtzeit_dezimal_gesamt[$b] = number_format((float)$array_fahrtzeit_dezimal_gesamt[$b], 2, '.', '');
																
																// Berechne Abweichung
																$array_abweichung_dezimal[$b]["ab" . $d] = abs((float)$array_fahrtzeit_dezimal[$b]["fz" . $d] - (float)$array_sollzeit_sekunde[$d]);
																$array_abweichung_dezimal[$b]["ab" . $d] = number_format((float)$array_abweichung_dezimal[$b]["ab" . $d], 2, '.', '');
																	
																// Übergebe Abweichung an Funktion zur Konvertierung
																$array_abweichung_konvert[$b]["ab" . $d] = convertTime($array_abweichung_dezimal[$b]["ab" . $d]);
																
																// Addiere Abweichung Dezimal zur Gesamtabweichung
																$array_abweichung_dezimal_gesamt[$b] = abs((float)$array_abweichung_dezimal_gesamt[$b] + (float)$array_abweichung_dezimal[$b]["ab" . $d]);
																$array_abweichung_dezimal_gesamt[$b] = number_format((float)$array_abweichung_dezimal_gesamt[$b], 2, '.', '');	
																
																// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
																$array_fahrtzeit_konvert_gesamt[$b] = convertTime($array_fahrtzeit_dezimal_gesamt[$b]);
																$array_abweichung_konvert_gesamt[$b] = convertTime($array_abweichung_dezimal_gesamt[$b]);
															// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
															} else {
																// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
																$array_fahrtzeit_konvert[$b]["fz" . $d]		= "&mdash;&mdash;&mdash;";
																$array_abweichung_konvert[$b]["ab" . $d]		= "&mdash;&mdash;&mdash;";
																
																// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
																$array_fahrtzeit_konvert_gesamt[$b] = "&mdash;&mdash;&mdash;";
																$array_abweichung_konvert_gesamt[$b] = "&mdash;&mdash;&mdash;";
																
																// Workaround für Gesamtabweichung als Dezimales
																$array_abweichung_dezimal_gesamt[$b]	= 999999999999;
															}										
														// Ende mit Differenz aus Zielzeit abzgl. vorangegangener Zwischenzeit
														} elseif($d > 0 AND $d == (($rd_tpos - 1) - 1)) {
															// Starte mit der Berechnung, vorausgesetzt es handelt sich nicht um Leerwerte
															if($array_zeit_als_dezimal[$b]["zz" . $d] != 0 AND $array_zeit_als_dezimal[$b]["seq"] != 0) {
																// Berechne Fahrtzeit
																$array_fahrtzeit_dezimal[$b]["fz" . $d] = abs((float)$array_zeit_als_dezimal[$b]["seq"] - (float)$array_zeit_als_dezimal[$b]["zz" . $d]);
																$array_fahrtzeit_dezimal[$b]["fz" . $d] = number_format((float)$array_fahrtzeit_dezimal[$b]["fz" . $d], 2, '.', '');
																	
																// Übergebe Fahrtzeit an Funktion zur Konvertierung
																$array_fahrtzeit_konvert[$b]["fz" . $d] = convertTime($array_fahrtzeit_dezimal[$b]["fz" . $d]);
																
																// Addiere Fahrtzeit Dezimal zur Gesamtfahrtzeit
																$array_fahrtzeit_dezimal_gesamt[$b] = abs((float)$array_fahrtzeit_dezimal_gesamt[$b] + (float)$array_fahrtzeit_dezimal[$b]["fz" . $d]);
																$array_fahrtzeit_dezimal_gesamt[$b] = number_format((float)$array_fahrtzeit_dezimal_gesamt[$b], 2, '.', '');
																
																// Berechne Abweichung
																$array_abweichung_dezimal[$b]["ab" . $d] = abs((float)$array_fahrtzeit_dezimal[$b]["fz" . $d] - (float)$array_sollzeit_sekunde[$d]);
																$array_abweichung_dezimal[$b]["ab" . $d] = number_format((float)$array_abweichung_dezimal[$b]["ab" . $d], 2, '.', '');
																	
																// Übergebe Abweichung an Funktion zur Konvertierung
																$array_abweichung_konvert[$b]["ab" . $d] = convertTime($array_abweichung_dezimal[$b]["ab" . $d]);
																
																// Addiere Abweichung Dezimal zur Gesamtabweichung
																$array_abweichung_dezimal_gesamt[$b] = abs((float)$array_abweichung_dezimal_gesamt[$b] + (float)$array_abweichung_dezimal[$b]["ab" . $d]);
																$array_abweichung_dezimal_gesamt[$b] = number_format((float)$array_abweichung_dezimal_gesamt[$b], 2, '.', '');	
																																
																// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
																$array_fahrtzeit_konvert_gesamt[$b] = convertTime($array_fahrtzeit_dezimal_gesamt[$b]);
																$array_abweichung_konvert_gesamt[$b] = convertTime($array_abweichung_dezimal_gesamt[$b]);
															// Ansonsten weise der Fahrtzeit und Sollzeit Variablen Platzhalter zu
															} else {
																// Ergebnisse unvollständig => Zeit kann nicht berechnet werden
																$array_fahrtzeit_konvert[$b]["fz" . $d]		= "&mdash;&mdash;&mdash;";
																$array_abweichung_konvert[$b]["ab" . $d]		= "&mdash;&mdash;&mdash;";
																
																// Konvertiere Gesamtfahrtzeit und Gesamtabweichung
																$array_fahrtzeit_konvert_gesamt[$b] = "&mdash;&mdash;&mdash;";
																$array_abweichung_konvert_gesamt[$b] = "&mdash;&mdash;&mdash;";
																
																// Workaround für Gesamtabweichung als Dezimales
																$array_abweichung_dezimal_gesamt[$b]	= 999999999999;
															}
														}
													} elseif($rd_calc == 3) {
														
													}
												}
												
												// Speichere abschließend die aufaddierte Gesamtabweichung						
												$con_tmember[$b]['raw_deviation'] = $array_abweichung_dezimal_gesamt[$b];

												// Speichere abschließend die aufaddierte Gesamtabweichung (HH:MM:SS,UU)						
												$con_tmember[$b]['edt_deviation'] = $array_abweichung_konvert_gesamt[$b];
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
											
										// Beginne mit der Berechnung und Ausgabe der Ergebnisse
										for($a = 1; $a < (count($con_tmember) + 1); $a++) {
											// Suche nach Startzeit für aktuellen Teilnehmer
											$select_spr = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $con_tmember[$a]['sid'] . "' AND `position` = 'Sprint'";
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
												$array_fahrtzeit_konvert[$a]	= "&mdash;&mdash;&mdash;";
												$array_abweichung_konvert[$a]	= "&mdash;&mdash;&mdash;";
												
												// Workaround für Gesamtabweichung als Dezimales
												$array_abweichung_dezimal[$a]	= 999999999999;
											}
											
											// Speichere abschließend die aufaddierte Gesamtabweichung						
											$con_tmember[$a]['raw_deviation'] = $array_abweichung_dezimal[$a];

											// Speichere abschließend die aufaddierte Gesamtabweichung (HH:MM:SS,UU)						
											$con_tmember[$a]['edt_deviation'] = $array_abweichung_konvert[$a];
										}
									}
									
									// Workaround: Zusätzlicher Index mit Leerwerten
									$con_tmember[9999]['sid'] = 9999;
									$con_tmember[9999]['class'] = "";
									$con_tmember[9999]['driver'] = "";
									$con_tmember[9999]['codriver'] = "";
									$con_tmember[9999]['vehicle'] = "";
									$con_tmember[9999]['built'] = "";
									$con_tmember[9999]['raw_deviation'] = 0;
									$con_tmember[9999]['edt_deviation'] = 0;
									
									// Sortiere Array nach Abweichung (als Dezimal vorliegend) als Standard
									// usort($con_tmember, 'sortByDeviation');
									// usort($con_tmember, build_sorter('raw_deviation'));	
									// Sortiere nach Abweichung wenn kein Parameter oder regulär
									if(!isset($_GET['sort']) OR $_GET['sort'] == "abw" OR $_GET['sort'] == "") {
										$con_tmember = array_orderby($con_tmember, 'raw_deviation', SORT_ASC, 'built', SORT_ASC);
										
										// Access Variable für Formatierung
										$access_format = 0;
									} elseif($_GET['sort'] == "cls") {
										$con_tmember = array_orderby($con_tmember, 'class', SORT_ASC, 'raw_deviation', SORT_ASC, 'built', SORT_ASC);
										
										// Access Variable für Formatierung
										$access_format = 1;
									}
									
									// Erstelle Tabelle als Auswertung
									for($z = 1; $z < (count($con_tmember) + 1); $z++) {
										$class_separator = "";
										
										if($access_format == 1) {
											if($z > 1 AND $z < (count($con_tmember) - 1)) {
												if($con_tmember[$z]['class'] != $con_tmember[($z + 1)]['class']) {
													$class_separator = "border-bottom: 3px solid #8E6516 !important;";
												}
											}
										}
										
										if($z % 2 == 0) {
											$background = "background-color: rgba(142, 101, 22, 0.10);";
										} else {
											$background = "background-color: #FFFFFF;";
										}
										
										if($z < count($con_tmember)) {
											if(	$z == 19 OR
												$z == 37 OR
												$z == 55 OR
												$z == 73 OR
												$z == 91 OR
												$z == 109 OR
												$z == 127 OR
												$z == 145 OR
												$z == 181 OR
												$z == 199
											) {
												echo	"
																	</table>
																</p>
																
																<p>
																	<table width=\"100%\" cellspacing=\"0\" cellpadding=\"5px\" border=\"1\" bordercolor=\"#8E6516\" style=\"border-collapse: collapse; font-size: small;\">
																		<tr>
																			<th>Seite " . floor($z / 18) . " von " . ceil(count($con_tmember) / 18). "</th>
																		</tr>
																	</table>
																</p>
															</div>
														</page>
														<page size=\"A4\" layout=\"portrait\">
															<div class=\"margin\">
																<p>
																	<table width=\"100%\" cellspacing=\"0\" cellpadding=\"5px\" style=\"border: 1px solid #8E6516;\">
																		<tr>
																			<td align=\"left\"><h2>Gesamtergebnis " . $rd_desc . "</h2></td>
																			<td align=\"right\"><img src=\"" . $image_path . "\"></td>
																		</tr>
																	</table>
																</p>
																	
																<p>
																	<table width=\"100%\" cellspacing=\"0\" cellpadding=\"5px\" border=\"1\" bordercolor=\"#8E6516\" style=\"border-collapse: collapse; font-size: small;\">
																		<tr>
																			<th>&#8470;</th>
																			<th>#</th>
																			<th>Kl.</th>
																			<td><strong>Fahrer</strong></td>
																			<td><strong>Beifahrer</strong></td>
																			<td><strong>Fahrzeug</strong></td>
																			<th>Baujahr</th>
																			<th>Abweichung</th>
																		</tr>
														";
											}
											
											echo	"
													<tr style=\"" . $background . " " . $class_separator . "\">
														<td align=\"center\">" . $z . "</td>
														<td align=\"center\">" . $con_tmember[$z]['sid'] . "</td>
														<td align=\"center\">" . $con_tmember[$z]['class'] . "</td>
														<td>" . $con_tmember[$z]['driver'] . "</td>
														<td>" . $con_tmember[$z]['codriver'] . "</td>
														<td>" . $con_tmember[$z]['vehicle'] . "</td>
														<td align=\"center\">" . $con_tmember[$z]['built'] . "</td>
														<td align=\"center\">" . $con_tmember[$z]['edt_deviation'] . "</td>
													</tr>
													";
										}
									}
									
									echo "</table>";
								} else {
									echo "<script>window.close();</script>";
								}						
							} else {
								echo "<script>window.close();</script>";
							}
						?>
					</p>
						
					<p>
						<table width="100%" cellspacing="0" cellpadding="5px" border="1" bordercolor="#8E6516" style="border-collapse: collapse; font-size: small;">
							<tr>
								<th>Seite <?php echo ceil(count($con_tmember) / 18); ?> von <?php echo ceil(count($con_tmember) / 18); ?></th>
							</tr>
						</table>
					</p>
				</div>
			</page>
			<!-- SAMPLES
			<page size="A4"></page>
			<page size="A5"></page>
			<page size="A5" layout="portrait"></page>
			<page size="A3"></page>
			<page size="A3" layout="portrait"></page>
			-->
		</div>
	</body>
<html>