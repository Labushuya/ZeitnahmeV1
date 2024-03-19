<?php
	if(isset($_POST['ergebnisdaten']) && !empty($_POST['ergebnisdaten'])) {
		// SET ERROR REPORTING LEVEL
		error_reporting(E_ALL);

		// SET TIMEZONE
		date_default_timezone_set("Europe/Berlin");

		// Prüfe, ob Session bereits gestartet wurde
		// PHP Version < 5.4.0
		if (session_id() == '') {
			session_start();
		}
		// PHP Version > 5.4.0, 7
		/*
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
		*/

		// Prüfe auf aktive Session-Parameter
    if(
      isset($_SESSION['eid']) && is_numeric($_SESSION['eid']) && $_SESSION['eid'] > 0 &&
      isset($_SESSION['rid']) && is_numeric($_SESSION['rid']) && $_SESSION['rid'] > 0
    ) {
			// Binde Funktionsdatei ein
			include_once '../includes/functions.php';

			// Binde DB-Verbindungsparameter ein
			include_once '../includes/db_connect.php';

			// Bereinige Übergabeparameter
			$eid = $_SESSION['eid'];
			$rid = $_SESSION['rid'];
			$zid = $_SESSION['uid'];

			// Bereinige übergebene Ergebnisdaten
			$tempData = html_entity_decode($_POST['ergebnisdaten']);
			$cleanData = json_decode($tempData);

			// Wandle Objekt in Array um
			$cleanData = (array)$cleanData;

			/*
				stdClass Object
				(
					[11.Start] => 11;12:34:56,78;Start
				)
			*/

			// Debugging
			/*
				echo "<pre>";
				print_r($cleanData);
				echo "</pre>";
			*/

			// Debugging
			// print_r($cleanData) . "\n\r";

			##	Lege nötige Prüfvariablen an  ##
			// Zähler Erfolgreich
			if(!isset($success)) {
				$success = 0;
			}

			// Callback-Array
			$callback =	array();

			// Suche zugehöriges Veranstaltungsdatum für korrekten Zeitstempel
			$select = "SELECT `id`, `eid`, `rid`, `eventdate` FROM `_optio_zmembers` WHERE `id` = '" . $zid . "' AND `eid` = '" . $eid . "' AND `rid` = '" . $rid . "'";
			$result = mysqli_query($mysqli, $select);
			$numrow = mysqli_num_rows($result);

			// Prüfe, ob Zeitnehmer unter diesen Daten existiert
			if($numrow > 0) {
				// Hole zugehöriges Veranstaltungsdatum
				$getrow = mysqli_fetch_assoc($result);

				$eventdate = $getrow['eventdate'];

				// Bereinige Übergabeparameter
				$keyMuster = "/([1-9]{1}[0-9]{0,2})\.{1}([S][p][r][i][n][t]|[S][t][a][r][t]|[Z][Z]([1-9]{1}[0-9]{0,2})|[Z][i][e][l]){1}/";
				$valMuster = "/[1-9]{1}[0-9]{0,2}\;{1}[1-9]{1}[0-9]{0,2}\:{1}([0-5][0-9]){1}\:{1}([0-5][0-9]){1}\,{1}[0-9][0-9]\;{1}([S][t][a][r][t]|[Z][Z]([1-9]{1}[0-9]{0,2})|[Z][i][e][l]){1}/";

				foreach($cleanData as $arrObjKey => $arrObjVal) {
					// Prüfe auf Gültigkeit (korrektes Ergebnisformat)
					if(
						preg_match($keyMuster, $arrObjKey) &&
						preg_match($valMuster, $arrObjVal)
					) {
						// Debugging
						// echo $arrObjKey . "\n\r";
						// echo $arrObjVal . "\n\r";

						/*
							1;10:00:00,00;Start
							11;12:34:56,00;ZZ1
							66;12:34:56,00;Ziel
						*/

						// Splitte Ergebnisdaten auf
						$spalte = explode(";", $arrObjVal);

						/*
	           * Prüfe jedes Feld auf korrekten Inhalt und sobald
	           * auch nur eine Unstimmigkeit auftritt, gilt dieses
	           * Ergebnis als vollständig ungültig und wird gesondert
	           * zurückgegeben
	           *
	           * Startnummer  ($spalte[0] => int(1-9999))
	           * Zeit         ($spalte[1] => string(pattern: 'hh:mm:ss,uu'))
	           * Position     ($spalte[2] => string('Sprint'/'Start'/'ZZX'/'Ziel'))
	           */
	          $sid = 0;
	          $erg = 0;
	          $pos = 0;

	          if(isset($spalte[0]) && preg_match("/(?:[1-9][0-9]{3}|[1-9][0-9]{2}|[1-9][0-9]|[1-9])/", $spalte[0])) {
	            $sid = 1;
	          }

	          if(isset($spalte[1]) && preg_match("/[1-9]{1}[0-9]{0,2}\:{1}([0-5][0-9]){1}\:{1}([0-5][0-9]){1}\,{1}[0-9][0-9]/", $spalte[1])) {
	            $erg = 1;
	          }

	          if(isset($spalte[2]) &&
	            (
	              strpos("Sprint", $spalte[2]) !== false ||
	              strpos("Start", $spalte[2]) !== false ||
	              strpos("Ziel", $spalte[2]) !== false ||
	              preg_match("/ZZ(?:[1-9][0-9]{3}|[1-9][0-9]{2}|[1-9][0-9]|[1-9])/", $spalte[2])
	            )
	          ) {
	            $pos = 1;
	          }

						// Wurden alle drei Parameter korrekt übergeben, beginne mit hochladen
	          if($sid == 1 && $erg == 1 && $pos == 1) {
	            // Prüfe String-Länge
	  					if(strlen($spalte[1]) == 11) {
	  						// Splitte Ergebnis-String auf
	  						$time = explode(":", $spalte[1]);
	  						$erg_hh = $time[0];
	  						$erg_mm = $time[1];
	  						$erg_ss = $time[2];

	  						// Hole Sekunden und Hundertstel
	  						$ergCenti = explode(",", $erg_ss);
	  						$erg_ss = $ergCenti[0];
	  						$erg_ms = $ergCenti[1];

	  						// Marker für weiteren Ablauf
	  						$proceed = 1;
	  					} elseif(strlen($spalte[1]) == 8) {
	  						// Splitte Ergebnis-String auf
	  						$time = explode(":", $spalte[1]);
	  						$erg_hh = "00";
	  						$erg_mm = $time[0];
	  						$erg_ss = $time[1];

	  						// Hole Sekunden und Hundertstel
	  						$ergCenti = explode(",", $erg_ss);
	  						$erg_ss = $ergCenti[0];
	  						$erg_ms = $ergCenti[1];

	  						// Marker für weiteren Ablauf
	  						$proceed = 1;
	  					// Ergebnis-Länge ungültig
	  					} else {
	  						// Markiere Eintrag entsprechend
	  						$callback[] = 'strlen#' . $spalte[0] . ";" . $spalte[1] . ";" . $spalte[2] . '#' . $arrObjKey;

	  						// Marker für weiteren Ablauf
	  						$proceed = 0;
	  					}

	            // Führe weiteren Programmcode nur aus, wenn Bedingung erfüllt (Marker)
	  					if($proceed == 1) {
	  						// Hole einzelne, unberührte Hundertstel
	  						$ergHundertstel = $erg_ms;

	  						// Erstelle abhängig von Prüfungstyp Zeitstempel für spätere Berechnung
	  						if($spalte[2] !== "Sprint") {
	  							// Erstelle Zeitstempel (ab 01.01.1970)
	  							$zeitstempel = $eventdate . " " . $erg_hh . ":" . $erg_mm . ":" . $erg_ss . "." . $erg_ms;

	  							$ergSekunden = strtotime($zeitstempel);
	  						} elseif($spalte[2] == "Sprint") {
	  							// Erstelle Gesamtsekunden
	  							$seconds = intval(($erg_mm * 60) + $erg_ss);

	  							// Erstelle Zeitstempel (aus Gesamtsekunden)
	  							$ergSekunden = $seconds;
	  						}

	              // Suche nach Ergebnis und Position für diesen Teilnehmer
								$select = 'SELECT * FROM `_main_wpresults` WHERE `eid` = ' . $eid . ' AND `rid` = ' . $rid . ' AND `sid` = ' . $spalte[0] . ' AND `position` = "' . $spalte[2] . '"';
								$result = mysqli_query($mysqli, $select);
	  						$numrow = mysqli_num_rows($result);

								// Ereignis Zeitstempel
								$zeitstempelLog = time();

								/*
								 * Ist bereits ein Ergebnis für diese Position vorhanden,
								 * prüfe, ob das aktuelle Ergebnis mit dem übergebenen
								 * übereinstimmt, um dieses ggf. zu überspringen und einen
								 * entsprechenden Marker zu setzen. So kann die Sichtbarkeit
								 * von Ereignissen individuell angezeigt werden
								 *
								 * db:	explizit = 1		Datensatz ausschließlich für Auswerter
								 *			explizit = 0		Datensatz wird allen angezeigt
								 *
								 * Datensatz noch nicht vorhanden
								 */
								if($numrow == 0) {
									// Speichere Datensatz als Initiativ-Eintragung
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
	                              `zeitstempel`
	                            )
	                          VALUES(
	                            NULL,
	                            '" . $eid . "',
	                            '" . $rid . "',
	                            '" . $zid . "',
	                            '" . $spalte[0] . "',
	                            '" . $spalte[2] . "',
	                            '" . $ergSekunden . "',
	                            '" . $ergHundertstel . "',
	                            '" . $spalte[1] . "',
	                            '" . $zeitstempelLog . "'
	                          )
	                          ";
	                $result = mysqli_query($mysqli, $insert);

									// Wurde der Datensatz gespeichert
	                if(mysqli_affected_rows($mysqli) == 1) {
	                  // Füge aktuellen Ergebnisdaten-Schlüssel Callback-Array hinzu
	                  $callback[] = 'isokay#' . $spalte[0] . ";" . $spalte[1] . ";" . $spalte[2] . '#' . $arrObjKey;

										// Erstelle Ereignis-Eintrag
										$insertLog =	"
				                          INSERT INTO
				                            `_optio_tmembers_event`(
				                              `id`,
				                              `eid`,
				                              `rid`,
				                              `xid`,
				                              `sid`,
				                              `funktionaer`,
				                              `zeitstempel`,
				                              `aktion`,
				                              `explizit`
				                            )
				                          VALUES(
				                            NULL,
				                            '" . $eid . "',
				                            '" . $rid . "',
				                            '" . $zid . "',
				                            '" . $spalte[0] . "',
				                            'mz',
				                            '" . $zeitstempelLog . "',
				                            '" . $spalte[2] . "-Zeit erfasst',
				                            '0'
				                          )
				                          ";
		                $resultLog = mysqli_query($mysqli, $insertLog);
	                // Datensatz konnte nicht gespeichert werden
	                } elseif(mysqli_affected_rows($mysqli) == 0) {
	                  // Füge Ergebnis dem Callback-Array hinzu (für Ausgabe)
	                  $callback[] = 'failed#' . $spalte[0] . ";" . $spalte[1] . ";" . $spalte[2] . '#' . $arrObjKey;
	                }
								// Ergebnis bereits vorhanden
								} else {
									// Prüfe übergebenes Ergebnis auf Übereinstimmung
									while($getrow = mysqli_fetch_assoc($result)) {
										// Ergebnis ist bereits in Datenbank gespeichert
										if($getrow['ergebnis_string'] == $spalte[1]) {
											// Überspringe Ergebnis, da Duplikat
											$callback[] = 'skiped#' . $spalte[0] . ";" . $spalte[1] . ";" . $spalte[2] . '#' . $arrObjKey;
										// Ergebnis kein Duplikat, sondern ggf. Korrektur
										} else {
											// Speichere Datensatz als Initiativ-Eintragung
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
			                              `zeitstempel`
			                            )
			                          VALUES(
			                            NULL,
			                            '" . $eid . "',
			                            '" . $rid . "',
			                            '" . $zid . "',
			                            '" . $spalte[0] . "',
			                            '" . $spalte[2] . "',
			                            '" . $ergSekunden . "',
			                            '" . $ergHundertstel . "',
			                            '" . $spalte[1] . "',
			                            '" . $zeitstempelLog . "'
			                          )
			                          ";
			                $resultInsert = mysqli_query($mysqli, $insert);

											// Wurde der Datensatz gespeichert
			                if(mysqli_affected_rows($mysqli) == 1) {
			                  // Füge aktuellen Ergebnisdaten-Schlüssel Callback-Array hinzu
			                  $callback[] = 'isokay#' . $spalte[0] . ";" . $spalte[1] . ";" . $spalte[2] . '#' . $arrObjKey;

												// Erstelle Ereignis-Eintrag
												$insertLog =	"
						                          INSERT INTO
						                            `_optio_tmembers_event`(
						                              `id`,
						                              `eid`,
						                              `rid`,
						                              `xid`,
						                              `sid`,
						                              `funktionaer`,
						                              `zeitstempel`,
						                              `aktion`,
						                              `explizit`
						                            )
						                          VALUES(
						                            NULL,
						                            '" . $eid . "',
						                            '" . $rid . "',
						                            '" . $zid . "',
						                            '" . $spalte[0] . "',
						                            'mz',
						                            '" . $zeitstempelLog . "',
						                            '" . $spalte[2] . "-Zeit korrigiert',
						                            '1'
						                          )
						                          ";
				                $resultLog = mysqli_query($mysqli, $insertLog);
			                // Datensatz konnte nicht gespeichert werden
			                } elseif(mysqli_affected_rows($mysqli) == 0) {
			                  // Füge Ergebnis dem Callback-Array hinzu (für Ausgabe)
			                  $callback[] = 'failed#' . $spalte[0] . ";" . $spalte[1] . ";" . $spalte[2] . '#' . $arrObjKey;
			                }
										}
									}
								}
	  					}
	          // Etwas ist schiefgelaufen
	          } else {
							// Prüfe einzelne Spaltenparameter
							if(!isset($spalte[0]) || $spalte[0] == "") {
								$spalte[0] = "";
							}

							if(!isset($spalte[1]) || $spalte[1] == "") {
								$spalte[1] = "";
							}

							if(!isset($spalte[2]) || $spalte[2] == "") {
								$spalte[2] = "";
							}

							/*
							 * Wenn Startnummer und Position gesetzt und nicht leer sind,
							 * kann der zugehörige Local Storage Index-Schlüssel daraus
							 * generiert und an den String angehängt werden (Bsp 11.Start)
							 */
							if(!preg_match($keyMuster, $arrObjKey)) {
								$lsIndex = "";
								$errorCD = "errXXX";
							} else {
								$lsIndex = $arrObjKey;
								$errorCD = "err000";
							}

	            $callback[] = $errorCD . '#' . $spalte[0] . ";" . $spalte[1] . ";" . $spalte[2] . '#' . $lsIndex;
	          }
					}

					// Setze Marker zurück
					$proceed = 0;
				}

				// Erstelle finalen Callback
				echo json_encode($callback);
			// Zeitnehmer existiert nicht (unter diesen Daten)
			} else {
				echo "keinZeitnehmer";
			}
    // Keine aktive Session
    } else {
      echo "keineSession";
    }
	}
?>
