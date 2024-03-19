<? error_reporting(E_ALL);
    // INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
	
	// START SECURE SESSION
	sec_session_start();
	
	// CREATE EVENT ID FROM ACTIVE SESSION
	$eid	= $_SESSION['user_id'];
	
	// GET SELECT OPTIONS FOR rid
	if(isset($_POST["id"]) && !empty($_POST["id"])) {
		// SANITTIZE POST
		$rid = mysqli_real_escape_string($mysqli, $_POST['id']);
		
		// DELETE ZMEMBER POSITIONS 
        $delete_zpos = "DELETE FROM _optio_zpositions WHERE `rid` = '" . $rid . "'";
		mysqli_query($mysqli, $delete_zpos);
				
	    // DELETE ZMEMBERS
        $delete_query = "DELETE FROM _optio_zmembers WHERE `rid` = '" . $rid . "' AND `eid` = '" . $eid . "'";
        mysqli_query($mysqli, $delete_query);
		
		// SEARCH WPTABLE AND DELETE
        $delete_wpt = "DELETE FROM _main_wptable WHERE `rid` = '" . $rid . "' AND `eid` = '" . $eid . "'";
        mysqli_query($mysqli, $delete_wpt);
		
		// SEARCH WPTABLE_SZ AND DELETE
        $delete_wptsz = "DELETE FROM _main_wptable_sz WHERE `rid` = '" . $rid . "' AND `eid` = '" . $eid . "'";
        mysqli_query($mysqli, $delete_wptsz);
		
		// RUN COUNT ROWS
		$select = "SELECT id FROM _optio_zmembers WHERE `eid` = '" . $eid . "'";
		$result = mysqli_query($mysqli, $select);
		$numrow = mysqli_num_rows($result);
		
		// SEARCH WPTABLE AND COUNT ROWS
        $select_wptable = "SELECT id, eid, rid FROM _main_wptable WHERE `eid` = '" . $eid . "'";
		$result_wptable = mysqli_query($mysqli, $select_wptable);
		$getrow_wptable = mysqli_fetch_assoc($result_wptable);
		$numrow_wptable = mysqli_num_rows($result_wptable);	
		
		// UPDATE NEW COUNT WPTABLE AND ZMEMBERS
		$update = "UPDATE _race_run_events SET `count_wptable` = '" . $numrow_wptable . "', `count_zmembers` = '" . $numrow . "' WHERE `eid` = '" . $eid . "' AND `active` = '1'";
		mysqli_query($mysqli, $update);
    }
?>