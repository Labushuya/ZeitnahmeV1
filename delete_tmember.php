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
		$mt_id = mysqli_real_escape_string($mysqli, $_POST['id']);
	
	    // GRAB POST AND DELETE ROW
        $delete_query = "DELETE FROM _optio_tmembers WHERE `id` = '" . $mt_id . "' AND `eid` = '" . $eid . "'";
        mysqli_query($mysqli, $delete_query);
		
		// SEARCH TMEMBERS AND COUNT ROWS THEN UPDATE COUNT OF TMEMBERS FOR EVENT
        $select = "SELECT id FROM _optio_tmembers WHERE `eid` = '" . $eid . "'";
		$result = mysqli_query($mysqli, $select);
		$numrow = mysqli_num_rows($result);
		
		// PREPARE UPDATE NEW COUNT TMEMBERS
		$update = "UPDATE _race_run_events SET `count_tmembers` = '" . $numrow . "' WHERE `eid` = '" . $eid . "' AND `active` = '1'";
		mysqli_query($mysqli, $update);
    }
?>