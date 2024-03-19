<? error_reporting(E_ALL);
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';

	// SANITIZE $_POST
	$editval = strip_tags($_POST['editval']);
	$editval = trim($editval);
	$editval = mysqli_real_escape_string($mysqli, $editval);
	
	// CHECK FOR VALUE TO UPDATE CORRECTLY
	if($editval == "yes") {
		$editval = "";
	}
		
	$eid = strip_tags($_POST['eid']);
	$eid = trim($eid);
	$eid = mysqli_real_escape_string($mysqli, $eid);
		
	$sid = strip_tags($_POST['sid']);
	$sid = trim($sid);
	$sid = mysqli_real_escape_string($mysqli, $sid);
	
	$select_tmember = "SELECT `eid`, `sid`, `ready` FROM `_optio_tmembers` WHERE `eid` = '" . $eid . "' AND `sid` = '" . $sid . "'";
	$result_tmember = mysqli_query($mysqli, $select_tmember);
	$getrow_tmember = mysqli_fetch_assoc($result_tmember);
	$numrow_tmember = mysqli_num_rows($result_tmember);
	
	// CHECK IF USER EXISTS
	if($numrow_tmember > 0) {
		$update_tmember = "UPDATE `_optio_tmembers` SET `ready` = '" . $editval . "' WHERE `eid` = '" . $eid . "' AND `sid` = '" . $sid . "'";
		$result_tmmeber = mysqli_query($mysqli, $update_tmember);
		
		// CHECK IF STATEMENT WAS SUCCESSFUL
		if(mysqli_affected_rows($mysqli) > 0) {
			// RUN NEW QUERY TO CHECK CURRENT READY STATUS
			$select_tmember = "SELECT `eid`, `sid`, `ready` FROM `_optio_tmembers` WHERE `eid` = '" . $eid . "' AND `sid` = '" . $sid . "'";
			$result_tmember = mysqli_query($mysqli, $select_tmember);
			$getrow_tmember = mysqli_fetch_assoc($result_tmember);			
			
			// SET RETURN VALUE
			$status = $getrow_tmember['ready'];
			
			// CHECK STATUS
			if($status == "") {
				$image = "green";
			} else {
				$image = "red";
			}
			
			echo $image;
		}
	}
?>