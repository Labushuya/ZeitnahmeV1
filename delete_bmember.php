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
		$bid = mysqli_real_escape_string($mysqli, $_POST['id']);
	
		// SEARCH ZMEMBER POSITIONS AND DELETE
        $delete_bpos = "DELETE FROM _optio_bmembers_order WHERE `bid` = '" . $bid . "'";
		mysqli_query($mysqli, $delete_bpos);
				
	    // GRAB POST AND DELETE ROW
        $delete_query = "DELETE FROM _optio_bmembers WHERE `id` = '" . $bid . "' AND `eid` = '" . $eid . "'";
        mysqli_query($mysqli, $delete_query);
		
		// RUN COUNT ROWS
		$select = "SELECT id FROM _optio_bmembers WHERE `eid` = '" . $eid . "'";
		$result = mysqli_query($mysqli, $select);
		$numrow = mysqli_num_rows($result);
		
		// PREPARE UPDATE NEW COUNT ZMEMBERS
		$update = "UPDATE _race_run_events SET `count_bmembers` = '" . $numrow . "' WHERE `eid` = '" . $eid . "'";
		mysqli_query($mysqli, $update);
    }
?>