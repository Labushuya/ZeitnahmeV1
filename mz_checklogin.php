<?php
	//	Setze Fehlerlevel
	error_reporting(E_ALL);

	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
	
	if(isset($_POST['eid']) AND isset($_POST['eid']) AND isset($_POST['eid'])) {
		//	Schreibe POST um
		$eid = mysqli_real_escape_string($mysqli, $_POST['eid']);
		$uid = mysqli_real_escape_string($mysqli, $_POST['uid']);
		$rid = mysqli_real_escape_string($mysqli, $_POST['rid']);
		
		//	Suche in DB nach aktiver Berechtigung
		$select_active = "SELECT `id`, `eid`, `rid`, `active`, `neutralized` FROM `_optio_zmembers` WHERE `id` = '" . $uid . "' AND `eid` = '" . $eid . "' AND `rid` = '" . $rid . "'";
		$result_active = mysqli_query($mysqli, $select_active);
		$numrow_active = mysqli_num_rows($result_active);
		$getrow_active = mysqli_fetch_assoc($result_active);
		
		//	Prüfe, ob Benutzer vorhanden
		//	Benutzer vorhanden
		if($numrow_active == 1) {
			//	Prüfe Loginstatus
			//	Loginstatus nicht okay
			if($getrow_active['neutralized'] == 1) {
				echo "regular";
			}
		//	Benutzer nicht vorhanden
		} elseif($numrow_active == 0) {
			echo "nouser";
		//	Error: Benutzer mehrmals vorhanden
		} elseif($numrow_active > 1) {
			echo "multiuser";
		} else {
			echo "criticalerr";
		}
	} else {
		echo "nopost";
	}
?>