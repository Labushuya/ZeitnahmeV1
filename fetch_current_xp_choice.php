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
	if(isset($_POST["rid_type"]) && !empty($_POST["rid_type"])) {
		$rid_type = $_POST["rid_type"];
	
		// QUERIES _RACE_RUN_EVENTS
		$select_race_run = "SELECT * FROM `_race_run_events` WHERE `eid` = '".$eid."' AND `active` = '1'";
		$result_race_run = mysqli_query($mysqli, $select_race_run);
		$spalte_race_run = mysqli_fetch_assoc($result_race_run);

		// QUERIES _MAIN_WPTABLE
		$select_main_wpt = "SELECT * FROM `_main_wptable` WHERE `eid` = '".$eid."'";
		$result_main_wpt = mysqli_query($mysqli, $select_main_wpt);
		$spalte_main_wpt = mysqli_fetch_assoc($result_main_wpt);
			
		// FETCH RD_TYPE AND INFOR FROM DATABASE
		// RD_TYPE IN _RACE_RUN_EVENTS FOUND
		echo "<option value='" . $spalte_race_run['master_rid_type'] . "' selected='selected'>" . $spalte_race_run['master_rid_type'] . "</option>";
			echo "<optgroup label='ändern zu ..' style='color: #8E6516;'>";
			// SHOW INDIVIDUAL OPTIONS BASED ON DATABASE ENTRY
			if($spalte_race_run['master_rid_type'] == "GP") {
				echo "<option value='changeto_WP'>WP</option>";
				echo "<option value='changeto_SP'>SP</option>";
			} elseif($spalte_race_run['master_rid_type'] == "WP") {
				echo "<option value='changeto_GP'>GP</option>";
				echo "<option value='changeto_SP'>SP</option>";
			} elseif($spalte_race_run['master_rid_type'] == "SP") {
				echo "<option value='changeto_GP'>GP</option>";
				echo "<option value='changeto_WP'>WP</option>";
			}
			echo "</optgroup>";
	}
?>