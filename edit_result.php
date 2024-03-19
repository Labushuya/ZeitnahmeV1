<? error_reporting(E_ALL);
	date_default_timezone_set("Europe/Berlin");

    // Binde Funktionen ein
	include_once 'includes/functions.php';
	
	// Binde die DB-Connect ein
	include_once 'includes/db_connect.php';
	
	// Starte sichere Session
	sec_session_start();
	
	// Prüfe, ob Zugang aktiv, ansonsten leite weiter
	if(isset($_SESSION['user_id'])) {
		// Baue Event ID aus aktiver User-Session
		$eid	= $_SESSION['user_id'];
		
		// Prüfe, ob korrekte POST übergeben wurde
		if(
			(isset($_POST['rid']) && !empty($_POST['rid'])) AND			
			(isset($_POST['vid']) && !empty($_POST['vid'])) AND			
			(isset($_POST['val']) && !empty($_POST['val']))		
		) {
			// Bereinige übergebene POST und baue Runden-ID
			$rid = mysqli_real_escape_string($mysqli, utf8_encode($_POST['rid']));
			$vid = mysqli_real_escape_string($mysqli, utf8_encode($_POST['vid']));
			$val = mysqli_real_escape_string($mysqli, utf8_encode($_POST['val']));
			
			$select = "SELECT * FROM `_main_wptable` WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' LIMIT 1";
			$result = mysqli_query($mysqli, $select);
			$numrow = mysqli_num_rows($result);
			
			//	Prüfung ist Sprint
			if($numrow == 1) {
				$getrow = mysqli_fetch_assoc($result);
				
				$select_rslt = "SELECT * FROM `_main_wpresults` WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `id` = '" . $vid . "' LIMIT 1";
				$result_rslt = mysqli_query($mysqli, $select_rslt);
				$numrow_rslt = mysqli_num_rows($result_rslt);
				
				if($numrow_rslt == 1) {
					$getrow_rslt = mysqli_fetch_assoc($result_rslt);
					
					//	Speichere ursprüngliche Werte für späteren Vergleich
					$ergSekunden_pre = $getrow_rslt['t_time'];
					$t_realtime_pre = $getrow_rslt['t_realtime'];
					$t_centi_pre = $getrow_rslt['t_centi'];
					
					//	Prüfvariable
					$is_sprint = $getrow['z_entry'];
					
					//	Prüfe Formatlänge
					if(strlen($val) == 11) {
						//	Hole Prüfungsdatum
						$execute = $getrow['execute'];
						
						//	Splitte Ergebnis auf (HH:MM:SS,UU)
						$time = explode(":", $val);
						$val_hh = $time[0];
						$val_mm = $time[1];
						$val_ss = $time[2];
						
						//	Hole Hundertstel und ermittle Sekunden
						$val_ct = explode(",", $val_ss);
						$val_ss = $val_ct[0];
						$val_ms = $val_ct[1];
						
						$seconds = $execute . " " . $val_hh . ":" . $val_mm . ":" . $val_ss . "." . $val_ms;
					} elseif(strlen($val) == 8) {
						//	Splitte Ergebnis auf (MM:SS,UU)
						$time = explode(":", $val);
						$val_hh = "00";
						$val_mm = $time[0];
						$val_ss = $time[1];
						
						//	Hole Hundertstel und ermittle Sekunden
						$val_ct = explode(",", $val_ss);
						$val_ss = $val_ct[0];
						$val_ms = $val_ct[1];
						
						//	Ermittle Sekunden auf Übergabeparameter
						$seconds = intval(($val_mm * 60) + $val_ss);
					}
					
					//	Prüfung ist Sprint
					if($is_sprint == "1") {
						$ergebnis = $seconds;
					//	Prüfung ist regulär
					} else {
						$ergebnis = strtotime($seconds);
					}
					
					//	Ersetze Punkt durch Komma, da zusätzlich Zeit direkt gespeichert wird
					$zeitstempel = str_replace('.', ',', $val);
					
					//	Suche und aktualisiere Ergebnis für betroffenen Datensatz
					$update =	"
								UPDATE
									`_main_wpresults`
								SET
									`ergebnis_sekunden` = '" . $ergebnis . "',
									`ergebnis_hundertstel` = '" . $val_ms . "',
									`ergebnis_string` = '" . $zeitstempel . "'
								WHERE
									`id` = '" . $vid . "'
								AND
									`rid` = '" . $rid . "'
								";
					$result = mysqli_query($mysqli, $update);
					
					if(mysqli_affected_rows($mysqli) == 1) {
						echo "success";
					//	Kein geänderter Datensatz
					} else {
						//	Vergleiche vorherige Werte mit jetzigen Werten
						$select_rslt = "SELECT * FROM `_main_wpresults` WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `id` = '" . $vid . "' LIMIT 1";
						$result_rslt = mysqli_query($mysqli, $select_rslt);
						
						//	Speichere ursprüngliche Werte für späteren Vergleich
						$ergSekunden_post = $getrow_rslt['t_time'];
						$t_realtime_post = $getrow_rslt['t_realtime'];
						$t_centi_post = $getrow_rslt['t_centi'];
						
						if(
							$ergSekunden_pre == $ergSekunden_post OR
							$t_realtime_pre == $t_realtime_post OR
							$t_centi_pre == $t_centi_post
						) {
							echo "no_change";
						} else {
							echo "failed";
						}
					}
				//	Kein Ergebnis mit dieser Datensatz-ID gefunden
				} else {
					echo "no_result";
				}
			//	Prüfung nicht gefunden
			} else {
				echo "no_rid";
			}
		}
	} elseif(!isset($_SESSION['user_id']) OR $_SESSION['user_id'] == "") {
		echo "no_eid";
	}
	
	