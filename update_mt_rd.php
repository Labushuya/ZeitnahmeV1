<? error_reporting(E_ALL);
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';

	// SANITIZE $_POST
	$eid = strip_tags($_POST['eid']);
	$eid = trim($eid);
	$eid = mysqli_real_escape_string($mysqli, $eid);
		
	$rid = strip_tags($_POST['rid']);
	$rid = trim($rid);
	$rid = mysqli_real_escape_string($mysqli, $rid);
		
	$sid = strip_tags($_POST['sid']);
	$sid = trim($sid);
	$sid = mysqli_real_escape_string($mysqli, $sid);
	
	// SEARCH POSSIBLE LOCK IN ANY ROUND FOR EACH START ID
	$select_lock = "SELECT `eid`, `rid`, `sid` FROM `_optio_tmembers_lock` WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $sid . "'";
	$result_lock = mysqli_query($mysqli, $select_lock);
	$numrow_lock = mysqli_num_rows($result_lock);
	
	// IF ROW HAS BEEN FOUND, DELETE, OTHERWISE INSERT
	if($numrow_lock == 0) {
		$query	= 	"	
						INSERT INTO
							_optio_tmembers_lock(
								id,
								eid,
								rid,
								sid
							)
						VALUES(
							NULL,
							'" . $eid . "',
							'" . $rid . "',
							'" . $sid . "'
							)
					";
						 
		mysqli_query($mysqli, $query);
		$allocation = "ins";
	} elseif($numrow_lock == 1) {
		$query	= 	"	
						DELETE FROM
							_optio_tmembers_lock
						WHERE
							`eid` = '" . $eid . "'
						AND
							`rid` = '" . $rid . "'
						AND
							`sid` = '" . $sid . "'
					";
						 
		mysqli_query($mysqli, $query);
		$allocation = "del";
	}
	
	if($allocation != "" OR empty($allocation)) {
		echo $allocation;
	}
?>