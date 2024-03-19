<?php
	//	Setze Fehlerlevel
	error_reporting(E_ALL);
	
	//	Binde relevante Dateien ein
	include_once '../includes/functions.php';
	include_once '../includes/db_connect.php';
	
	//	Suche alle aktiven Zeitnehmer Logins
	$select_zmembers = "SELECT * FROM `_optio_zmembers` WHERE `active` = '1'";
	$result_zmembers = mysqli_query($mysqli, $select_zmembers);
	$numrow_zmembers = mysqli_num_rows($result_zmembers);
		
	//	Vergleiche, wenn mindestens ein Treffer erzielt wurde
	if($numrow_zmembers > 0) {
		//	Suche nach allen Log-Einträgen und vergleiche mit letztem Login
		$select_log = "SELECT * FROM `_optio_zmembers_log` WHERE `id` > 0 AND `logtime` > 0";
		$result_log = mysqli_query($mysqli, $select_log);
		$numrow_log = mysqli_num_rows($result_log);
			
		//	Fahre fort, sofern mindestens ein Log-Eintrag gefunden wurde
		if($numrow_log > 0) {
			//	Speichere alle Zeitnehmer-Zugänge (ID) in Array
			$zmembers_id = array();
			$zmembers_logtime = array();
				
			//	Manuelle Iteration um identische Indizes in beiden Arrays zu erreichen
			$m = 0;
				
			while($getrow_zmembers = mysqli_fetch_assoc($result_zmembers)) {
				$zmembers_id[$m] = $getrow_zmembers['id'];
				$zmembers_logtime[$m] = $getrow_zmembers['logintime'];
				
				//	Erhöhe manuelle Iteration um eins
				$m++;
			}
				
			//	Suche nach allen Einträgen jedes Zeitnehmer-Zugangs
			for($i = 0; $i < count($zmembers_id); $i++) {
				//	Suche nach höchstem Zeitstempel bei jedem Durchgang
				$select_each = "SELECT `zid`, `logtime` FROM `_optio_zmembers_log` WHERE `zid` = '" . $zmembers_id[$i] . "' LIMIT 1";
				$result_each = mysqli_query($mysqli, $select_each);
				$numrow_each = mysqli_num_rows($result_each);
				
				//	Wenn mindestens ein Eintrag gefunden wurde, prüfe Inaktivität
				if($numrow_each > 0) {
					$getrow_each = mysqli_fetch_assoc($result_each);
					
					//	Wenn Zeit der Inaktivität größer gleich 1800 Sekunden (15 Minuten) ist, logge Zeitnehmer aus
					if(abs($getrow_each['logtime'] - $zmembers_logtime[$i]) >= (60 * 30)) {
						$update_zmember =	"
											UPDATE 
												`_optio_zmembers` 
											SET 
												`active` = '0',
												`logintime` = '0'
											WHERE	
												`id` = '" . $zmembers_id[$i] . "'
											";
						mysqli_query($mysqli, $update_zmember);
					} else {
						continue;
					}
				} else {
					continue;
				}
			}
		//	Ansonsten breche ab
		} else {
			exit();
		}
	//	Ansonsten breche ab
	} else {
		exit();
	}
?>