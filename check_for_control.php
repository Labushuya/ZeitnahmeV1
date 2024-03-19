<? error_reporting(E_ALL);
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';

	//	Führe aus, wenn Übergabeparameter vorhandne
	if(isset($_POST['eid']) AND isset($_POST['zid']) AND isset($_POST['dte']) AND isset($_POST['sid'])) {
		//	Lege Leervariable an
		$callback = "";
		
		//	Bereinige Übergabeparameter
		$eid = mysqli_real_escape_string($mysqli, $_POST['eid']);
		$zid = mysqli_real_escape_string($mysqli, $_POST['zid']);
		$dte = mysqli_real_escape_string($mysqli, $_POST['dte']);
		$sid = mysqli_real_escape_string($mysqli, $_POST['sid']);
		
		//	Suche nach Bordkarten Einträgen dieses Teilnehmers
		$select = "SELECT * FROM `_optio_zcontrol_results` WHERE `zid` = '" . $zid . "' AND `sid` = '" . $sid . "' AND `eventdate` = '" . $dte . "'";
		
		$result = mysqli_query($mysqli, $select);
		$numrow = mysqli_num_rows($result);
		
		//	Prüfe Ergebnis(se)
		if($numrow == 0) {
			//	Keine Einträge vorhanden
			$callback = "0^";
		} elseif($numrow > 0) {
			//	Einträge vorhanden
			$getrow = mysqli_fetch_assoc($result);
			
			$callback = date("H:i", $getrow['time']) . "^";
		}
		
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
				$callback .= $getrow_tmembers['vname_1'] . " " . $getrow_tmembers['nname_1'];
			//	Fahrer besitzt keinen Namen (Dummy?)
			} else {
				$callback .= "knv";
			}
		//	Fahrer mehrfach vorhanden
		} elseif($numrow_tmember > 1) {
			$callback .= "kmv";
		} elseif($numrow_tmember == 0) {
			$callback .= "kne";
		}
		
		echo $callback;
	}
?>