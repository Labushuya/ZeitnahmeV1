<? error_reporting(E_ALL);
	date_default_timezone_set("Europe/Berlin");

    // INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
	
	// START SECURE SESSION
	sec_session_start();
	
	// CREATE EVENT ID FROM ACTIVE SESSION
	$eid	= $_SESSION['user_id'];
	
	// GET SELECT OPTIONS FOR RD_ID
	if(isset($_POST["changeXP"]) && !empty($_POST["changeXP"])) {
		$rid_type = $_POST["changeXP"];
		
		// ONLY UPDATE RD_TYPE
		if(strpos($rid_type, "changeto_GP") !== false OR strpos($rid_type, "changeto_SP") !== false OR strpos($rid_type, "changeto_WP") !== false) {
			// SANITIZE STRING
			$rid_type = str_replace("changeto_", "", $rid_type);
			
			// UPDATE EVERY EMPTY RD_TYPE
			// SET UPDATE QUERIES
			$update_race_run = "UPDATE `_race_run_events` SET `master_rid_type` = '" . $rid_type . "' WHERE `eid` = '" . $eid . "' AND `active` = '1'";
			$update_main_wpt = "UPDATE `_main_wptable` SET `rid_type` = '" . $rid_type . "' WHERE `eid` = '" . $eid . "'";
			$update_optiozme = "UPDATE `_optio_zmembers` SET `rid_type` = '" . $rid_type . "' WHERE `eid` = '" . $eid . "'";
				
			// TOTAL VARIABLE
			$pts = 0;
				
			// RUN QUERIES
			mysqli_query($mysqli, $update_race_run);
			if($update_race_run == true) {
				$pts++;
			}
			mysqli_query($mysqli, $update_main_wpt);
			if($update_main_wpt == true) {
				$pts++;
			}
			mysqli_query($mysqli, $update_optiozme);
			if($update_optiozme == true) {
				$pts++;
			}
			
			// COUNT ROWS AND UPDATE _RACE_RUN_EVENTS
			// SEARCH TMEMBERS AND COUNT ROWS THEN UPDATE COUNT OF TMEMBERS FOR EVENT
			$select = "SELECT id, eid FROM _main_wptable WHERE `eid` = '" . $eid . "'";
			$result = mysqli_query($mysqli, $select);
			$numrow = mysqli_num_rows($result);
					
			// PREPARE UPDATE NEW COUNT TMEMBERS
			$update = "UPDATE _race_run_events SET `count_wptable` = '" . $numrow . "' WHERE `eid` = '" . $eid . "' AND `active` = '1'";
			mysqli_query($mysqli, $update);
			
			// IF QUERY SUCCESSFUL
			if($pts == 3) {
				echo '<span style="margin-left: 5px; border-bottom: 1px dotted #8E6516;"><font color="#FFD700">Erfolgreich!</font></span><br />';
				echo '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Prüfungsbezeichnung geändert!</span><br />';
			} else {
				echo '<span style="margin-left: 5px; border-bottom: 1px dotted #8E6516;"><font color="#FFD700">Fehler:</font></span><br />';
				echo '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Prüfungsbezeichnung konnte nicht geändert werden!</span><br />';
			}	
		// NO CHANGETO VALUE HAS BEEN PASSED
		} else {
			echo 'Fehler:';
			echo '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Ungültiger Übergabeparamenter!</span><br />';
		}
	}
?>