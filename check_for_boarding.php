<? error_reporting(E_ALL);
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';

	//	Führe aus, wenn Übergabeparameter vorhandne
	if(isset($_POST['eid']) AND isset($_POST['dte']) AND isset($_POST['sid']) AND isset($_POST['pos'])) {
		//	Debugging
		/*
			echo "<pre>";
			print_r($_POST);
			echo "</pre>";
			//	exit;
		*/
		
		/*
			Array
			(
				[eid] => 1
				[sid] => 66
				[dte] => 2019-07-18
				[pos] => Array
					(
						[0] => zk_1
						[1] => zs_1
						[2] => zk_2
						[3] => zs_2
					)
			)
		*/
		
		//	Bereinige Übergabeparameter
		$eid = mysqli_real_escape_string($mysqli, $_POST['eid']);
		$sid = mysqli_real_escape_string($mysqli, $_POST['sid']);
		$dte = mysqli_real_escape_string($mysqli, $_POST['dte']);
		
		//	Setze Callback
		$callback = array();
		
		//	Suche nach Fahrer der Startnummer
		$select_tmember = "SELECT * FROM `_optio_tmembers` WHERE `eid` = '" . $eid . "' AND `sid` = '" . $sid . "'";
		$result_tmember = mysqli_query($mysqli, $select_tmember);
		$numrow_tmember = mysqli_num_rows($result_tmember);
		
		//	Prüfe Ergebnis
		if($numrow_tmember == 1) {
			$getrow_tmembers = mysqli_fetch_assoc($result_tmember);
			
			//	Prüfe, ob Name leer
			if($getrow_tmembers['vname_1'] != "" AND $getrow_tmembers['nname_1'] != "") {
				//	Gebe Fahrer des Fahrzeugs aus (Füge Delimiter bei)
				$callback['sid'] = $getrow_tmembers['vname_1'] . " " . $getrow_tmembers['nname_1'];
			//	Fahrer besitzt keinen Namen (Dummy?)
			} else {
				$callback['sid'] = "tnv";
			}
		//	Fahrer mehrfach vorhanden
		} elseif($numrow_tmember > 1) {
			$callback['sid'] = "tmv";
		//	Fahrer nicht vorhanden
		} elseif($numrow_tmember == 0) {
			$callback['sid'] = "tne";
		}
		
		//	Durchlaufe übergebene Positionen
		for($i = 0; $i < count($_POST['pos']); $i++) {
			//	Suche nach ZK (Zeitkontrollen), bzw. ZS (Stempelkontrollen)
			if(preg_match("/zk_[0-9]{1}/", $_POST['pos'][$i])) {
				//	Lege Tabelle fest
				$table = "_optio_zcontrol_results";
			} elseif(preg_match("/zs_[0-9]{1}/", $_POST['pos'][$i])) {
				$table = "_optio_zstamp_results";
			}
			
			//	Extrahiere ZID aus Array Schlüssel
			$explode = explode("_", $_POST['pos'][$i]);
			$zid = $explode[1];
			
			$flag = $explode[0];
			
			//	Suche nur, wenn Tabelle gesetzt wurde
			if(isset($table)) {
				//	Suche nach Zeitkontroll-Einträgen für diese Startnummer
				$select =	"
							SELECT 
								* 
							FROM 
								`" . $table . "` 
							WHERE 
								`eid` = '" . $eid . "' 
							AND 
								`zid` = '" . $zid . "' 
							AND 
								`sid` = '" . $sid . "' 
							AND 
								`eventdate` = '" . $dte . "'
							";
				$result = mysqli_query($mysqli, $select);
				$numrow = mysqli_num_rows($result);
				
				if($flag == "zk") {
					//	Marker für Stempelkontrolle (0 = regulär; 1 = Stempel)
					$marker = 0;
				} elseif($flag == "zs") {
					//	Marker für Stempelkontrolle (0 = regulär; 1 = Stempel)
					$marker = 1;
				}
				
				//	Eintrag gefunden
				if($numrow == 1) {
					$getrow = mysqli_fetch_assoc($result);
					
					if($marker == 0) {
						$callback[$_POST['pos'][$i]] = date("H:i", $getrow['time']);
					} elseif($marker == 1) {
						$callback[$_POST['pos'][$i]] = 1;
					}
				//	Kein Eintrag vorhanden
				} elseif($numrow == 0) {
					$getrow = mysqli_fetch_assoc($result);
					
					if($marker == 0) {
						$callback[$_POST['pos'][$i]] = "";
					} elseif($marker == 1) {
						$callback[$_POST['pos'][$i]] = 0;
					}
				}
			}
		}
		
		echo json_encode($callback);
	}
?>