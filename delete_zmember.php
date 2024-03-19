<? error_reporting(E_ALL);
    // INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
	
	// START SECURE SESSION
	sec_session_start();
	
	// CREATE EVENT ID FROM ACTIVE SESSION
	$eid	= $_SESSION['user_id'];
	
	// GET SELECT OPTIONS FOR RD_ID
	if(isset($_POST["id"]) && !empty($_POST["id"])) {
		// SANITTIZE POST
		$mz_id = mysqli_real_escape_string($mysqli, $_POST['id']);
	
		// SEARCH ZMEMBER POSITIONS AND DELETE
        $delete_zpos = "DELETE FROM _optio_zpositions WHERE `zid` = '" . $mz_id . "'";
		mysqli_query($mysqli, $delete_zpos);
				
	    // GRAB POST AND DELETE ROW
        $delete_query = "DELETE FROM _optio_zmembers WHERE `id` = '" . $mz_id . "' AND `eid` = '" . $eid . "'";
        mysqli_query($mysqli, $delete_query);
		
		// RUN COUNT ROWS
		$select = "SELECT id FROM _optio_zmembers WHERE `eid` = '" . $eid . "'";
		$result = mysqli_query($mysqli, $select);
		$numrow = mysqli_num_rows($result);
		
		// PREPARE UPDATE NEW COUNT ZMEMBERS
		$update = "UPDATE _race_run_events SET `count_zmembers` = '" . $numrow . "' WHERE `eid` = '" . $eid . "'";
		mysqli_query($mysqli, $update);
    }
?>