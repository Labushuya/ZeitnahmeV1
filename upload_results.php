<?php
	// SET ERROR REPORTING LEVEL
	error_reporting(E_ALL);
	
	// SET TIMEZONE
	date_default_timezone_set("Europe/Berlin");
	
	if(
		isset(
			$_POST['eid'],
			$_POST['rid'],
			$_POST['zid'],
			$_POST['ztype'],
			$_POST['ergebnisdaten']
		) &&
		!empty($_POST['eid']) &&
		!empty($_POST['rid']) &&
		!empty($_POST['zid']) &&
		!empty($_POST['ztype']) &&
		!empty($_POST['ergebnisdaten'])
	) {
		//	Binde Funktionsdatei ein
		include_once 'includes/functions.php';
	
		//	Binde DB-Verbindungsparameter ein
		include_once 'includes/db_connect.php';
		
		//	Bereinige Übergabeparameter
		$eid = mysqli_real_escape_string($mysqli, $_POST['eid']);
		$rid = mysqli_real_escape_string($mysqli, $_POST['rid']);
		$zid = mysqli_real_escape_string($mysqli, $_POST['zid']);
		$ztype = mysqli_real_escape_string($mysqli, $_POST['ztype']);
				
		//	Bereinige übergebene Ergebnisdaten
		$tempData = html_entity_decode($_POST['ergebnisdaten']);
		$cleanData = json_decode($tempData);
		
		//	Wandle Objekt in Array um
		$cleanData = (array)$cleanData;
		
		/*
			stdClass Object
			(
				[11.Start] => 11;12:34:56,78;Start
			)
		*/
		
		//	Debugging
		/*
			echo "<pre>";
			print_r($cleanData);
			echo "</pre>";
		*/
		
		//	Debugging
		//	print_r($cleanData) . "\n\r";
		
		##	Lege nötige Prüfvariablen an  ##
		//	Zähler Erfolgreich
		if(!isset($success)) {
			$success = 0;
		}
		
		//	Callback-Array
		$callback =	array();
		
		//	Suche zugehöriges Veranstaltungsdatum für korrekten Zeitstempel
		$select = "SELECT `id`, `eid`, `rid`, `eventdate` FROM `_optio_zmembers` WHERE `id` = '" . $zid . "' AND `eid` = '" . $eid . "' AND `rid` = '" . $rid . "'";
		$result = mysqli_query($mysqli, $select);
		$numrow = mysqli_num_rows($result);
		
		//	Prüfe, ob Zeitnehmer unter diesen Daten existiert
		if($numrow > 0) {
			//	Hole zugehöriges Veranstaltungsdatum
			$getrow = mysqli_fetch_assoc($result);
			
			$eventdate = $getrow['eventdate'];
			
			//	Bereinige Übergabeparameter
			$keyMuster = "/([1-9]{1}[0-9]{0,2})\.{1}([S][t][a][r][t]|[Z][Z]([1-9]{1}[0-9]{0,2})|[Z][i][e][l]){1}/";
			$valMuster = "/[1-9]{1}[0-9]{0,2}\;{1}[1-9]{1}[0-9]{0,2}\:{1}([0-5][0-9]){1}\:{1}([0-5][0-9]){1}\,{1}[0-9][0-9]\;{1}([S][t][a][r][t]|[Z][Z]([1-9]{1}[0-9]{0,2})|[Z][i][e][l]){1}/";
			
			foreach($cleanData as $arrObjKey => $arrObjVal) {				
				//	Prüfe auf Gültigkeit (korrektes Ergebnisformat)
				if(
					preg_match($keyMuster, $arrObjKey) &&
					preg_match($valMuster, $arrObjVal)
				) {
					//	Debugging
					//	echo $arrObjKey . "\n\r";
					//	echo $arrObjVal . "\n\r";
					
					/*
						1;10:00:00,00;Start
						11;12:34:56,00;ZZ1
						66;12:34:56,00;Ziel
					*/
					
					//	Splitte Ergebnisdaten auf
					$split = explode(";", $arrObjVal);
					
					##	Weise einzelne Parameter zu
					//	Startnummer
					$sid = $split[0];
					
					//	Ergebnis (String)
					$erg = $split[1];
					
					//	Position
					$pos = $split[2];
					
					//	Prüfe String-Länge
					if(strlen($erg) == 11) {
						//	Splitte Ergebnis-String auf
						$time = explode(":", $erg);
						$erg_hh = $time[0];
						$erg_mm = $time[1];
						$erg_ss = $time[2];
						
						//	Hole Sekunden und Hundertstel
						$ergCenti = explode(",", $erg_ss);
						$erg_ss = $ergCenti[0];
						$erg_ms = $ergCenti[1];
						
						//	Marker für weiteren Ablauf
						$proceed = 1;
					} elseif(strlen($erg) == 8) {
						//	Splitte Ergebnis-String auf
						$time = explode(":", $erg);
						$erg_hh = "00";
						$erg_mm = $time[0];
						$erg_ss = $time[1];
						
						//	Hole Sekunden und Hundertstel
						$ergCenti = explode(",", $erg_ss);
						$erg_ss = $ergCenti[0];
						$erg_ms = $ergCenti[1];
						
						//	Marker für weiteren Ablauf
						$proceed = 1;
					//	Ergebnis-Länge ungültig
					} else {
						//	Markiere Eintrag entsprechend
						$callback[] = 'strlen#' . $arrObjKey;
						
						//	Marker für weiteren Ablauf
						$proceed = 0;
					}
					
					//	Führe weiteren Programmcode nur aus, wenn Bedingung erfüllt (Marker)
					if($proceed == 1) {
						//	Hole einzelne, unberührte Hundertstel
						$ergHundertstel = $erg_ms;
						
						//	Erstelle abhängig von Prüfungstyp Zeitstempel für spätere Berechnung
						if($ztype !== "Sprint") {
							//	Erstelle Zeitstempel (ab 01.01.1970)
							$zeitstempel = $eventdate . " " . $erg_hh . ":" . $erg_mm . ":" . $erg_ss . "." . $erg_ms;
							
							$ergSekunden = strtotime($zeitstempel);
						} elseif($ztype == "Sprint") {
							//	Erstelle Gesamtsekunden
							$seconds = intval(($erg_mm * 60) + $erg_ss);
						
							//	Erstelle Zeitstempel (aus Gesamtsekunden)
							$ergSekunden = $seconds;
						}
						
						//	Suche nach Ergebnis und Position für diesen Teilnehmer
						$select = "SELECT * FROM `_main_wpresults` WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $sid . "' AND `position` = '" . $pos . "' AND `duplicate` = 0";
						$result = mysqli_query($mysqli, $select);
						$numrow = mysqli_num_rows($result);
						
						//	Ergebnis vorhanden, überschreibe
						if($numrow > 0) {
							//	Hole vorherige Werte, für potenziellen Vergleich und wegen Datensatz-ID
							$getrow = mysqli_fetch_assoc($result);
							
							$id = $getrow['id'];
							
							/*
								Wurde mehr als ein zutreffender Datensatz gefunden, setze
								den älteren von beidene auf Duplikat und fahre wie gewohnt
								fort
							*/
							if($numrow > 1) {
								$update =	"
											UPDATE
												`_main_wpresults`
											SET
												`duplicate` = 1
											WHERE
												`id` = '" . $id . "'
											";
								$result = mysqli_query($mysqli, $update);
								
								//	Prüfe, ob Datensatz aktualisiert wurde
								if(mysqli_affected_rows($mysqli) == 0) {
									//	Füge Ergebnis dem fehler-Array hinzu (für Ausgabe)
									$callback[] = 'ferror#' . $arrObjKey;
								}
							}
							
							//	Speichere alle alten Werte, die relevant für Vergleich sind
							$ergSekundenAlt = $getrow['ergebnis_sekunden'];
							$ergHundertstelAlt = $getrow['ergebnis_hundertstel'];
							$ergStringAlt = $getrow['ergebnis_string'];
							
							//	Aktualisiere Datensatz
							$update =	"
										UPDATE
											`_main_wpresults`
										SET
											`ergebnis_sekunden` = '" . $ergSekunden . "',
											`ergebnis_hundertstel` = '" . $ergHundertstel . "',
											`ergebnis_string` = '" . $erg . "'
										WHERE
											`id` = '" . $id . "'									
										";
							$result = mysqli_query($mysqli, $update);
							
							//	Debugging
							//	echo $update . "\n\r";
							
							//	Prüfe, ob Datensatz aktualisiert wurde
							if(mysqli_affected_rows($mysqli) == 1) {
								//	Erhöhe Zähler um eins
								$success++;
								
								//	Füge aktuellen Ergebnisdaten-Schlüssel zu-entfernen-Array hinzu
								$callback[] = $arrObjKey;
							//	Keine betroffener Datensatz aktualisiert, prüfe warum (Werte identisch?)
							} elseif(mysqli_affected_rows($mysqli) == 0) {
								//	Vergleiche alte Werte mit neuen Werten
								if($ergSekunden !== $ergSekundenAlt) {
									//	Erhöhe Zähler um eins
									$success++;
									
									//	Füge aktuellen Ergebnisdaten-Schlüssel zu-entfernen-Array hinzu
									$callback[] = $arrObjKey;
								} elseif($ergHundertstel !== $ergHundertstelAlt) {
									//	Erhöhe Zähler um eins
									$success++;
									
									//	Füge aktuellen Ergebnisdaten-Schlüssel zu-entfernen-Array hinzu
									$callback[] = $arrObjKey;
								} elseif($erg !== $ergStringAlt) {
									//	Erhöhe Zähler um eins
									$success++;
									
									//	Füge aktuellen Ergebnisdaten-Schlüssel zu-entfernen-Array hinzu
									$callback[] = $arrObjKey;
								//	Allgemeiner Fehler
								} else {
									//	Füge Ergebnis dem fehler-Array hinzu (für Ausgabe)
									$callback[] = 'failed#' . $arrObjKey;
								}
							}
						//	Kein Ergebnis vorhanden, lege an
						} else {
							$insert =	"
										INSERT INTO
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
											'" . $zid . "',
											'" . $sid . "',
											'" . $pos . "',
											'" . $ergSekunden . "',
											'" . $ergHundertstel . "',
											'" . $erg . "',
											'0'
										)
										";
							$result = mysqli_query($mysqli, $insert);
							
							//	Debugging
							//	echo $insert . "\n\r";
							
							//	Prüfe, ob Datensatz aktualisiert wurde
							if(mysqli_affected_rows($mysqli) == 1) {
								//	Erhöhe Zähler um eins
								$success++;
								
								//	Füge aktuellen Ergebnisdaten-Schlüssel zu-entfernen-Array hinzu
								$callback[] = $arrObjKey;
							//	Keine betroffener Datensatz aktualisiert, prüfe warum (Werte identisch?)
							} elseif(mysqli_affected_rows($mysqli) == 0) {
								//	Füge Ergebnis dem fehler-Array hinzu (für Ausgabe)
								$callback[] = 'failed#' . $arrObjKey;
							}
						}
					}
				}
				
				//	Setze Marker zurück
				$proceed = 0;
			}
			
			//	Erstelle finalen Callback
			echo json_encode($callback);
		//	Zeitnehmer existiert nicht (unter diesen Daten)
		} else {
			echo "no_zuser";
		}		
	//	Fehlerhafter Übergabeparameter
	} else {
		//	Prüfe was fehlgeschlagen ist
		echo "no_param";
	}
?>