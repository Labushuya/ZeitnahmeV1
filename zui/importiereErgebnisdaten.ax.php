<?php
  if(
    /*
    isset($_POST['eid']) && is_numeric($_POST['eid']) && $_POST['eid'] > 0 &&
    isset($_POST['rid']) && is_numeric($_POST['rid']) && $_POST['rid'] > 0 &&
    isset($_POST['zid']) && is_numeric($_POST['zid']) && $_POST['zid'] > 0 &&
    isset($_POST['ztype']) && ($_POST['ztype'] == "Regular" || $_POST['ztype'] == "Sprint") &&
    */
    isset($_FILES['csvImportieren'])
  ) {
    // Prüfe, ob Datei CSV Endung besitzt
    $ext = pathinfo($_FILES['csvImportieren']['name'], PATHINFO_EXTENSION);

    if($ext == 'csv') {
      // Lege Zeitzone fest
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
        isset($_SESSION['rid']) && is_numeric($_SESSION['rid']) && $_SESSION['rid'] > 0 &&
        isset($_SESSION['uid']) && is_numeric($_SESSION['uid']) && $_SESSION['uid'] > 0
      ) {
        // Binde Funktionsdateien ein
        include_once '../includes/functions.php';

        // Binde Konfigurationsdatei ein
        include_once '../includes/db_connect.php';

        // Bereinige Übergabeparameter
        $eid = $_SESSION['eid'];
        $rid = $_SESSION['rid'];
        $zid = $_SESSION['uid'];

        // Callback-Array
        $callback =	array();

        // Debugging
        /*
          echo "<pre>";
          print_r($_FILES);
          echo "</pre>";
          // exit;
        */

        /*
          Array $_FILES
          (
              [csvImportieren] => Array
                  (
                      [name] => ergebnisTest.csv
                      [type] => application/vnd.ms-excel
                      [tmp_name] => /run/tmp/phpjskHwm
                      [error] => 0
                      [size] => 44
                  )
          )
        */

        $csv_mime_types = [
            'text/csv',
            'text/plain',
            'application/csv',
            'text/comma-separated-values',
            'application/excel',
            'application/vnd.ms-excel',
            'application/vnd.msexcel',
            'text/anytext',
            'application/octet-stream',
            'application/txt'
        ];

        // Validiere CSV Datei
        if(in_array($_FILES['csvImportieren']['type'], $csv_mime_types)) {
          // Prüfe auf Inhalt
          if($_FILES['csvImportieren']['size'] > 0) {
            // Suche zugehöriges Veranstaltungsdatum für korrekten Zeitstempel
            $select = "SELECT `id`, `eid`, `rid`, `eventdate` FROM `_optio_zmembers` WHERE `id` = '" . $zid . "' AND `eid` = '" . $eid . "' AND `rid` = '" . $rid . "'";
            $result = mysqli_query($mysqli, $select);
            $numrow = mysqli_num_rows($result);

            // Prüfe, ob Zeitnehmer unter diesen Daten existiert
            if($numrow > 0) {
              // Hole zugehöriges Veranstaltungsdatum
              $getrow = mysqli_fetch_assoc($result);

              $eventdate = $getrow['eventdate'];

              // Öffne Datei
              $csvFile = fopen($_FILES['csvImportieren']['tmp_name'], "r");

              while(($spalte = fgetcsv($csvFile, 10000, ";")) !== FALSE) {
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

                if(isset($spalte[0])) {
                  // Entferne unsichtbare Zeichen, die beim Upload entstehen können
                  $spalte[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $spalte[0]);

                  if(preg_match("/(?:[1-9][0-9]{3}|[1-9][0-9]{2}|[1-9][0-9]|[1-9])/", $spalte[0])) {
                    $sid = 1;
                  }
                }

                if(isset($spalte[1])) {
                  // Entferne unsichtbare Zeichen, die beim Upload entstehen können
                  $spalte[1] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $spalte[1]);

                  if(preg_match("/[1-9]{1}[0-9]{0,2}\:{1}([0-5][0-9]){1}\:{1}([0-5][0-9]){1}\,{1}[0-9][0-9]/", $spalte[1])) {
                    $erg = 1;
                  }
                }

                if(isset($spalte[2])) {
                  // Entferne unsichtbare Zeichen, die beim Upload entstehen können
                  $spalte[2] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $spalte[2]);

                  if(
                    stripos("Sprint", $spalte[2]) !== false ||
                    stripos("Start", $spalte[2]) !== false ||
                    stripos("Ziel", $spalte[2]) !== false ||
                    preg_match("/ZZ(?:[1-9][0-9]{3}|[1-9][0-9]{2}|[1-9][0-9]|[1-9])/", $spalte[2])
                  ) {
                    $pos = 1;
                  }
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
                    $callback[] = 'strlen#' . $spalte[0] . ";" . $spalte[1] . ";" . $spalte[2] . '#' . $spalte[0] . "." . $spalte[1];

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
    	                  $callback[] = 'isokay#' . $spalte[0] . ";" . $spalte[1] . ";" . $spalte[2] . '#' . $spalte[0] . $spalte[2];

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
    	                  $callback[] = 'failed#' . $spalte[0] . ";" . $spalte[1] . ";" . $spalte[2] . '#' . $spalte[0] . $spalte[2];
    	                }
    								// Ergebnis bereits vorhanden
    								} else {
    									// Prüfe übergebenes Ergebnis auf Übereinstimmung
    									while($getrow = mysqli_fetch_assoc($result)) {
    										// Ergebnis ist bereits in Datenbank gespeichert
    										if($getrow['ergebnis_string'] == $spalte[1]) {
    											// Überspringe Ergebnis, da Duplikat
    											$callback[] = 'skiped#' . $spalte[0] . ";" . $spalte[1] . ";" . $spalte[2] . '#' . $spalte[0] . $spalte[2];
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
    			                  $callback[] = 'isokay#' . $spalte[0] . ";" . $spalte[1] . ";" . $spalte[2] . '#' . $spalte[0] . $spalte[2];

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
    			                  $callback[] = 'failed#' . $spalte[0] . ";" . $spalte[1] . ";" . $spalte[2] . '#' . $spalte[0] . $spalte[2];
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
                  if(
                    isset($spalte[0]) &&
                    preg_match("/(?:[1-9][0-9]{3}|[1-9][0-9]{2}|[1-9][0-9]|[1-9])/", $spalte[0]) &&
                    isset($spalte[2]) &&
                    (
                      stripos("Sprint", $spalte[2]) !== false ||
                      stripos("Start", $spalte[2]) !== false ||
                      stripos("Ziel", $spalte[2]) !== false ||
                      preg_match("/ZZ(?:[1-9][0-9]{3}|[1-9][0-9]{2}|[1-9][0-9]|[1-9])/", $spalte[2])
                    )
                  ) {
                    $lsIndex = $spalte[0] . "." . $spalte[2];
                    $errorCD = "err000";
                  } else {
                    $lsIndex = "";
                    $errorCD = "errXXX";
                  }

                  $callback[] = $errorCD . '#' . $spalte[0] . ";" . $spalte[1] . ";" . $spalte[2] . '#' . $lsIndex;
                }

                // Setze $proceed Variable zurück
                $proceed = 0;
              }

              // Erstelle finalen Callback
              echo json_encode($callback);
            // Kein Zeitnehmer unter diesen Daten vorhanden
            } else {
              echo "keinZeitnehmer";
            }
          // Datei ohne Inhalt
          } else {
            echo "csvLeer";
          }
        // Keine (gültige) CSV Datei
        } else {
          echo "csvUngueltig";
        }
      // Keine aktive Session
      } else {
        echo "keineSession";
      }
    // Keine CSV Datei (basierend auf Endung)
    } else {
      echo "keineCSV";
    }
  }
?>
