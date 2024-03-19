<? error_reporting(E_ALL);
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
			
	//	Setze Callback
	$callback = "";

	//	Führe aus, wenn Übergabeparameter vorhandne
	if(isset($_POST['eid']) AND isset($_POST['dte']) AND isset($_POST['sid']) AND isset($_POST['pos'])) {
		//	Bereinige Übergabeparameter
		$eid = mysqli_real_escape_string($mysqli, $_POST['eid']);
		$sid = mysqli_real_escape_string($mysqli, $_POST['sid']);
		$dte = mysqli_real_escape_string($mysqli, $_POST['dte']);
		
		if(isset($_POST['val'])) {
			$val = mysqli_real_escape_string($mysqli, $_POST['val']);
		}
		
		$pos = mysqli_real_escape_string($mysqli, $_POST['pos']);
	
		if(isset($_POST['act']) AND $_POST['act'] = "delete") {
			//	Lösche Zeitkontrolle
			$zid = $pos;
			
			//	Suche Datensatz, um sicherzugehen, das gelöscht
			$select = "SELECT * FROM `_optio_zcontrol_results` WHERE `eid` = '" . $eid . "' AND `zid` = '" . $zid . "' AND `sid` = '" . $sid . "' AND `eventdate` = '" . $dte . "'";
			$result = mysqli_query($mysqli, $select);
			$numrow = mysqli_num_rows($result);
			
			if($numrow == 1) {
				$delete =	"
							DELETE FROM
								`_optio_zcontrol_results`
							WHERE
								`eid` = '" . $eid . "'
							AND
								`zid` = '" . $zid . "'
							AND
								`sid` = '" . $sid . "'
							AND
								`eventdate` = '" . $dte . "'
							";
				$result = mysqli_query($mysqli, $delete);
				
				//  Prüfe, ob Datensatz gelöscht wurde
				if(mysqli_affected_rows($mysqli) == 1) {
					//  Datensatz erfolgreich gelöscht
					$callback = 1;
				//  Datensatz wurde nicht gelöscht
				} elseif(mysqli_affected_rows($mysqli) == 0) {
					//	Nochmalige Suche
					$select_retry = "SELECT * FROM `_optio_zcontrol_results` WHERE `eid` = '" . $eid . "' AND `zid` = '" . $zid . "' AND `sid` = '" . $sid . "' AND `eventdate` = '" . $dte . "'";
					$result_retry = mysqli_query($mysqli, $select_retry);
					$numrow_retry = mysqli_num_rows($result_retry);
					
					//	Datensatz erfolgreich gelöscht
					if($numrow_retry == 0) {
						$callback = 1;
					//  Datensatz wurde nicht gelöscht
					} else {
						$callback = 0;
					}
				}
			} else {
				//	Bereits gelöscht
				$callback = -1;
			}
		} else {
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
					[val] => 0				[val] => 14:30
					[pos] => zs_1
				)
			*/
			
			//	Extrahiere ZID aus Array Schlüssel
			$explode = explode("_", $pos);
			$zid = $explode[1];
			$flag = $explode[0];
			
			//	Suche nach ZK (Zeitkontrollen), bzw. ZS (Stempelkontrollen)
			if($flag == "zk") {
				//  Erstelle passenden Zeitstempel aus übergebener Zeit
				$time = strtotime($dte . " " . $val);
				
				//	Suche Datensatz, um sicherzugehen, das existent
				$select = "SELECT * FROM `_optio_zcontrol_results` WHERE `eid` = '" . $eid . "' AND `zid` = '" . $zid . "' AND `sid` = '" . $sid . "' AND `eventdate` = '" . $dte . "'";
				$result = mysqli_query($mysqli, $select);
				$numrow = mysqli_num_rows($result);
				
				if($numrow == 1) {
					//	Hole derzeitige Werte für späteren Vergleich
					$getrow = mysqli_fetch_assoc($result);
					
					$time_pre = $getrow['time'];
					
					//	Aktualisiere bestehenden Datensatz
					$delete =	"
								UPDATE
									`_optio_zcontrol_results`
								SET
									`time` = " . $time . "
								WHERE
									`eid` = '" . $eid . "'
								AND
									`zid` = '" . $zid . "'
								AND
									`sid` = '" . $sid . "'
								AND
									`eventdate` = '" . $dte . "'
								";
					$result = mysqli_query($mysqli, $delete);
					
					//  Prüfe, ob Datensatz gelöscht wurde
					if(mysqli_affected_rows($mysqli) == 1) {
						//  Datensatz erfolgreich gelöscht
						$callback = 1;
					//  Datensatz wurde nicht gelöscht
					} elseif(mysqli_affected_rows($mysqli) == 0) {
						//	Vergleiche vorherigen Zeitstempel mit übergebenem
						if($time_pre == $time) {
							$callback = 1;
						//  Datensatz wurde nicht gespeichert
						} else {
							$callback = 0;
						}
					}
				} elseif($numrow == 0) {
					//	Lege neuen Datensatz an
					$insert =	"
								INSERT INTO
									`_optio_zcontrol_results`(
										`id`,
										`eid`,
										`zid`,
										`sid`,
										`eventdate`,
										`time`
									)
								VALUES(
									NULL,
									'" . $eid . "',
									'" . $zid . "',
									'" . $sid . "',
									'" . $dte . "',
									" . $time . "
								)
								";
					$result = mysqli_query($mysqli, $insert);
					
					//	Wurde Datensatz gespeichert
					if(mysqli_affected_rows($mysqli) == 1) {
						//	Gespeichert
						$callback = 1;
					} elseif(mysqli_affected_rows($mysqli) == 0) {
						//	Nicht gespeichert
						$callback = 0;
					}
				}
			} elseif($flag == "zs") {
				//	Suche Datensatz, um sicherzugehen, das gelöscht
				$select = "SELECT * FROM `_optio_zstamp_results` WHERE `eid` = '" . $eid . "' AND `zid` = '" . $zid . "' AND `sid` = '" . $sid . "' AND `eventdate` = '" . $dte . "'";
				$result = mysqli_query($mysqli, $select);
				$numrow = mysqli_num_rows($result);
				
				if($numrow == 1) {
					$delete =	"
								DELETE FROM
									`_optio_zstamp_results`
								WHERE
									`eid` = '" . $eid . "'
								AND
									`zid` = '" . $zid . "'
								AND
									`sid` = '" . $sid . "'
								AND
									`eventdate` = '" . $dte . "'
								";
					$result = mysqli_query($mysqli, $delete);
					
					//  Prüfe, ob Datensatz gelöscht wurde
					if(mysqli_affected_rows($mysqli) == 1) {
						//  Datensatz erfolgreich gelöscht
						$callback = 1;
					//  Datensatz wurde nicht gelöscht
					} elseif(mysqli_affected_rows($mysqli) == 0) {
						//	Nochmalige Suche
						$select_retry = "SELECT * FROM `_optio_zstamp_results` WHERE `eid` = '" . $eid . "' AND `zid` = '" . $zid . "' AND `sid` = '" . $sid . "' AND `eventdate` = '" . $dte . "'";
						$result_retry = mysqli_query($mysqli, $select_retry);
						$numrow_retry = mysqli_num_rows($result_retry);
						
						//	Datensatz erfolgreich gelöscht
						if($numrow_retry == 0) {
							$callback = 1;
						//  Datensatz wurde nicht gelöscht
						} else {
							$callback = 0;
						}
					}
				} else {
					$insert =	"
								INSERT INTO
									`_optio_zstamp_results`(
										`id`,
										`eid`,
										`zid`,
										`sid`,
										`eventdate`,
										`time`
									)
								VALUES(
									NULL,
									'" . $eid . "',
									'" . $zid . "',
									'" . $sid . "',
									'" . $dte . "',
									" . time() . "
								)
								";
					$result = mysqli_query($mysqli, $insert);
						
					//  Prüfe, ob Datensatz gespeichert wurde
					if(mysqli_affected_rows($mysqli) == 1) {
						//  Datensatz erfolgreich gespeichert
						$callback = 1;
					//  Datensatz wurde nicht gelöscht
					} elseif(mysqli_affected_rows($mysqli) == 0) {
						//	Suche
						$select_retry = "SELECT * FROM `_optio_zstamp_results` WHERE `eid` = '" . $eid . "' AND `zid` = '" . $zid . "' AND `sid` = '" . $sid . "' AND `eventdate` = '" . $dte . "'";
						$result_retry = mysqli_query($mysqli, $select_retry);
						$numrow_retry = mysqli_num_rows($result_retry);
						
						//	Datensatz erfolgreich gespeichert
						if($numrow_retry == 1) {
							$callback = 1;
						//  Datensatz wurde nicht gespeichert
						} else {
							$callback = 0;
						}
					}
				}
			}
		}
		
		echo $callback;
	}
?>