<? error_reporting(E_ALL);
	date_default_timezone_set("Europe/Berlin");

    // Binde Funktionen ein
	include_once 'includes/functions.php';
	
	// Binde die DB-Connect ein
	include_once 'includes/db_connect.php';
	
	// Starte sichere Session
	sec_session_start();
	
	//	Prüfe auf Übergabeparameter
	if(
		isset($_POST['date']) AND 
		!empty($_POST['date']) AND 
		isset($_POST['eid']) AND 
		!empty($_POST['eid'])		
	) {
		//	Bereinige Übergabeparameter
		$eid = mysqli_real_escape_string($mysqli, $_POST['eid']);
		$date = mysqli_real_escape_string($mysqli, $_POST['date']);
		
		//	Ändere Format des Datums, sodass Datenbank konform
		$exploding = explode(".", $date);
		$date = $exploding[2] . "-" . $exploding[1] . "-" . $exploding[0];
		
		//	Erstelle Array für alle angelegten Prüfungen und X-Kontrollen
		$xpos =	array();
		
		//	Suche nach allen Prüfungen
		$select = "SELECT * FROM `_main_wptable` WHERE `eid` = '" . $eid . "' AND `execute` = '" . $date . "' ORDER BY `id` ASC";
		$result = mysqli_query($mysqli, $select);
		$numrow = mysqli_num_rows($result);
		
		//	Wenn Prüfungen gefunden wurden, füge die zugehörige ID dem Array hinzu
		if($numrow > 0) {
			while($getrow = mysqli_fetch_assoc($result)) {
				$xpos[] = "ex-" . $getrow['id'];
				
			}
		}
		
		//	Gebe reservierten Speicher frei
		mysqli_free_result($result);
		
		//	Suche nach allen Zeitkontrollen
		$select = "SELECT * FROM `_optio_zcontrol` WHERE `eid` = '" . $eid . "' AND `eventdate` = '" . $date . "' ORDER BY `id` ASC";
		$result = mysqli_query($mysqli, $select);
		$numrow = mysqli_num_rows($result);
		
		//	Wenn Prüfungen gefunden wurden, füge die zugehörige ID dem Array hinzu
		if($numrow > 0) {
			while($getrow = mysqli_fetch_assoc($result)) {
				$xpos[] = "zk-" . $getrow['id'];
			}
		}
		
		//	Gebe reservierten Speicher frei
		mysqli_free_result($result);
		
		//	Suche nach allen Stempelkontrollen
		$select = "SELECT * FROM `_optio_zstamp` WHERE `eid` = '" . $eid . "' AND `eventdate` = '" . $date . "' ORDER BY `id` ASC";
		$result = mysqli_query($mysqli, $select);
		$numrow = mysqli_num_rows($result);
		
		//	Wenn Prüfungen gefunden wurden, füge die zugehörige ID dem Array hinzu
		if($numrow > 0) {
			while($getrow = mysqli_fetch_assoc($result)) {
				$xpos[] = "zs-" . $getrow['id'];
			}
		}
		
		//	Gebe reservierten Speicher frei
		mysqli_free_result($result);
		
		//	Debugging
		/*
			echo "<pre>";
			print_r($xpos);
			echo "</pre><br />";
		*/
		
		if(count($xpos) > 0) {
			echo	"
						<tr>
							<th align=\"center\" colspan=\"3\" style=\"border-bottom: 1px solid #fff;\">Aufbau Bordkarte</th>
						</tr>
						<tr>
							<td align=\"justify\" colspan=\"3\">
								Hier legen Sie die Reihenfolge einzelner Positionen und Kontrollpunkte fest.
								Jeder Position können max. zwei Kontrollpunkte zugewiesen werden. Dies ist 
								zum Beispiel dann hilfreich, wenn sich direkt auf der Start Position sowohl
								die Prüfung selbst, als auch eine Zeitkontrolle befinden.
								<br />
								<br />
								Sollte lediglich ein Kontrollpunkt vorhanden sein, so lassen sie das Feld
								\"2. Kontrollpunkt\" an jeweiliger Position frei.
							</td>
						</tr>
						<tr>
							<td align=\"center\" colspan=\"3\" style=\"border-top: 1px solid #fff;\">&nbsp;</td>
						</tr>
						<tr>
							<td align=\"left\">Position</td>
							<td align=\"left\">1. Kontrollpunkt</td>
							<td align=\"left\">2. Kontrollpunkt</td>
						</tr>
					";
			
			//	Erstelle Ausgabe basierend auf der Anzahl der Elemente
			for($i = 0; $i < count($xpos); $i++) {
				//	Erstes Position-Feld ist immer Pflicht
				if($i == 0) {
					$attribute = "required";
				} else {
					$attribute = "disabled";
				}
				
				echo	"
						<tr>
							<td align=\"left\">
								<select class=\"pos\" name=\"reihenfolge[" . $i . "][0]\" style=\"width: 85px;\" " . $attribute . ">
									<option disabled selected>Wählen</option>
						";
						
				for($j = 0; $j < count($xpos); $j++) {
					//	Wenn Zähler noch bei erster Position ist, schreibe "Start"
					if($j == 0) {
						$alias = "1. / Start";
					} else {
						$alias = $j + 1 . ".";
					}
					
					echo	"
									<option value=\"" . ($j + 1) . "\">" . $alias . "</option>
							";
				}
						
				echo	"
								</select>
							</td>
							<td align=\"right\">
								<select class=\"xpos\" name=\"reihenfolge[" . $i . "][1][0]\" style=\"width: 135px;\" disabled>
									<option disabled selected>Bitte wählen</option>
						";
				
				for($k = 0; $k < count($xpos); $k++) {	
					//	Splitte Werte auf
					$explode = explode("-", $xpos[$k]);
					
					//	Suche nach zugehörigen Daten zur hinterlegten ID
					if($explode[0] == "ex") {
						//	Suche in Prüfungstabelle
						$select = "SELECT * FROM `_main_wptable` WHERE `id` = '" . $explode[1] . "'";
						$result = mysqli_query($mysqli, $select);
						$getrow = mysqli_fetch_assoc($result);
						$hilftyp = "ex";
						$alias = "Prüfung [" . $getrow['rid_type'] . $getrow['rid'] . "]";
					} elseif($explode[0] == "zk") {
						//	Suche in Prüfungstabelle
						$select = "SELECT * FROM `_optio_zcontrol` WHERE `id` = '" . $explode[1] . "'";
						$result = mysqli_query($mysqli, $select);
						$getrow = mysqli_fetch_assoc($result);
						$hilftyp = "zk";
						$alias = "Zeitkontrolle [" . $getrow['opt_whois'] . "]";
					} elseif($explode[0] == "zs") {
						//	Suche in Prüfungstabelle
						$select = "SELECT * FROM `_optio_zstamp` WHERE `id` = '" . $explode[1] . "'";
						$result = mysqli_query($mysqli, $select);
						$getrow = mysqli_fetch_assoc($result);
						$hilftyp = "zs";
						$alias = "Stempelkontrolle [" . $getrow['opt_whois'] . "]";
					}
					
					echo	"
										<option class=\"" . $hilftyp . "_" . $explode[1] . "\" value=\"" . $hilftyp . "_" . $explode[1] . "\">" . $alias . "</option>
							";
							
					//	Gebe reservierten Speicher frei
					mysqli_free_result($result);
				}
						
				echo	"
								</select>
							</td>
						";
						
				echo	"
							<td align=\"right\">
								<select class=\"xpos\" name=\"reihenfolge[" . $i . "][1][1]\" style=\"width: 135px;\" disabled>
									<option selected>Bitte wählen</option>
						";
				
				for($k = 0; $k < count($xpos); $k++) {	
					//	Splitte Werte auf
					$explode = explode("-", $xpos[$k]);
					
					//	Suche nach zugehörigen Daten zur hinterlegten ID
					if($explode[0] == "ex") {
						//	Suche in Prüfungstabelle
						$select = "SELECT * FROM `_main_wptable` WHERE `id` = '" . $explode[1] . "'";
						$result = mysqli_query($mysqli, $select);
						$getrow = mysqli_fetch_assoc($result);
						$hilftyp = "ex";
						$alias = "Prüfung [" . $getrow['rid_type'] . $getrow['rid'] . "]";
					} elseif($explode[0] == "zk") {
						//	Suche in Prüfungstabelle
						$select = "SELECT * FROM `_optio_zcontrol` WHERE `id` = '" . $explode[1] . "'";
						$result = mysqli_query($mysqli, $select);
						$getrow = mysqli_fetch_assoc($result);
						$hilftyp = "zk";
						$alias = "Zeitkontrolle [" . $getrow['opt_whois'] . "]";
					} elseif($explode[0] == "zs") {
						//	Suche in Prüfungstabelle
						$select = "SELECT * FROM `_optio_zstamp` WHERE `id` = '" . $explode[1] . "'";
						$result = mysqli_query($mysqli, $select);
						$getrow = mysqli_fetch_assoc($result);
						$hilftyp = "zs";
						$alias = "Stempelkontrolle [" . $getrow['opt_whois'] . "]";
					}
					
					echo	"
										<option class=\"" . $hilftyp . "_" . $explode[1] . "\" value=\"" . $hilftyp . "_" . $explode[1] . "\">" . $alias . "</option>
							";
							
					//	Gebe reservierten Speicher frei
					mysqli_free_result($result);
				}
						
				echo	"
								</select>
							</td>
						</tr>
						";
			}	
		} else {
			echo	"
						<tr>
							<th>Keine Kontrollpunkte verfügbar!</th>
						</tr>
					";
		}
	}