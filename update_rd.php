<? error_reporting(E_ALL);
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';

		// SANITIZE $_POST
		$eid = strip_tags($_POST['eid']);
		$eid = trim($eid);
		
		$rid = strip_tags($_POST['rid']);
		$rid = trim($rid);
		
		$editval = strip_tags($_POST['editval']);
		$editval = trim($editval);
		
		$column = strip_tags($_POST['column']);
		$column = trim($column);
		
		$id = strip_tags($_POST['id']);
		$id = trim($id);

		$update = "UPDATE `_main_wptable` SET `" . $column . "` = '" . $editval . "' WHERE `eid` = '" . $eid . "' AND `id` = '" . $id . "'";
		mysqli_query($mysqli, $update);
		
		//	Hole Teilnehmerliste
		$select_tmembers = "SELECT `eid`, `sid` FROM `_optio_tmembers` WHERE `eid` = '" . $eid . "'";
		$result_tmembers = mysqli_query($mysqli, $select_tmembers);
		
		//	Setze alle Teilnehmer der übergebenen Prüfung auf rot
		while($getrow_tmembers = mysqli_fetch_assoc($result_tmembers)) {
			$sid = $getrow_tmembers['sid'];
			
			//	Führe Insert in _optio_tmembers_lock aus
			$insert_tmembers =	"
								INSERT INTO 
									`_optio_tmembers_lock`(
										`id`, 
										`eid`, 
										`rid`, 
										`sid`
									) 
								VALUES(
									NULL, 
									'" . $eid . "', 
									'" . $rid . "',
									'" . $sid . "'
								)
								";			
			mysqli_query($mysqli, $insert_tmembers);
			
			//	Logge alle Zeitnehmer dieser Prüfung aus
			$update_zmembers =	"
								UPDATE 
									`_optio_zmembers` 
								SET 
									`active`	= '0'
								WHERE 
									`eid`	= '" . $eid . "'
								AND
									`rid`		= '" . $rid . "'
								AND
									`active`	= '1'
								";
		}		
?>