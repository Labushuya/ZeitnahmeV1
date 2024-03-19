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
		
		//	Suche nach Teilnehmer
		$select_sid = "SELECT * FROM `_optio_tmembers` WHERE `eid` = '" . $eid . "' AND `sid` = '" . $sid . "'";
		$result_sid = mysqli_query($mysqli, $select_sid);
		$numrow_sid = mysqli_num_rows($result_sid);
		
		if($numrow_sid == 1) {
			$getrow_sid = mysqli_fetch_assoc($result_sid);
			
			if($getrow_sid['vname_1'] != "" AND $getrow_sid['vname_1'] != "") {
				$callback .= $getrow_sid['vname_1'] . " " . $getrow_sid['nname_1'];
			} elseif(
				($getrow_sid['vname_1'] != "" AND $getrow_sid['vname_1'] == "") OR
				($getrow_sid['vname_1'] == "" AND $getrow_sid['vname_1'] != "")				
			) {
				$callback .= "kiu";
			} elseif($getrow_sid['vname_1'] == "" AND $getrow_sid['vname_1'] == "") {
				$callback .= "knv";
			}
		} elseif($numrow_sid > 1) {
			$callback .= "kmv";
		} elseif($numrow_sid == 0) {
			$callback .= "kne";
		}
		
		//	Suche Datensatz, um sicherzugehen, das gelöscht
		$select = "SELECT * FROM `_optio_zstamp_results` WHERE `eid` = '" . $eid . "' AND `zid` = '" . $zid . "' AND `sid` = '" . $sid . "' AND `eventdate` = '" . $dte . "'";
		$result = mysqli_query($mysqli, $select);
		$numrow = mysqli_num_rows($result);
		
		if($numrow == 1) {
			$callback .= "^1";
		} else {
			$callback .= "^0";
		}
		
		echo $callback;
	}
?>