<? error_reporting(E_ALL);
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';

	if(isset($_POST['eid']) AND isset($_POST['fid'])) {
		// SANITIZE $_POST
		$eid = strip_tags($_POST['eid']);
		$eid = trim($eid);
		
		$fid = strip_tags($_POST['fid']);
		$fid = trim($fid);
		
		//	Splitte Übergabe Parameter auf
		$explode = explode("_", $fid);
		
		$fn = $explode[0];
		
		$fid = $explode[1];
		
		switch($fn) {
			case "mz":
				$table = "_optio_zmembers";
				$marker = "z";
			break;
			case "zk":
				$table = "_optio_zcontrol";
				$marker = "z";
			break;
			case "zs":
				$table = "_optio_zstamp";
				$marker = "z";
			break;
			case "bc":
				$table = "_optio_bmembers";
				$marker = "b";
			break;
		}
		
		//	Hole vorherige Werte als Referenz bei mysqli_affected_rows == 0
		$select = "SELECT * FROM `" . $table . "` WHERE `id` = '" . $fid . "'";
		$result = mysqli_query($mysqli, $select);
		$numrow = mysqli_num_rows($result);
		
		if($numrow == 1) {
			$getrow = mysqli_fetch_assoc($result);
			
			$update =	"
						UPDATE 
							`" . $table . "` 
						SET 
							`active`	= 0,
							`logintime`	= 0
						WHERE
							`eid` = '" . $eid . "' 
						AND 
							`id` = '" . $fid . "'";
			mysqli_query($mysqli, $update);
			
			//	Prüfe, ob erfolgreich
			if(mysqli_affected_rows($mysqli) == 1) {
				//	Registriere Logeintrag
				$insert_log =	"
								INSERT INTO 
									`" . $table . "_log`(
										`id`,
										`" . $marker . "id`,
										`eid`,
										`logtime`,
										`action`
									)
								VALUES(
									NULL,
									'" . $fid . "',
									'" . $eid . "',
									'" . time() . "',
									'manueller Logout'
								)
								";
				$result_log = mysqli_query($mysqli, $insert_log);
				
				if(mysqli_affected_rows($mysqli) == 1) {
					echo "success";
				} else {
					echo "fail";
				}
			} elseif(mysqli_affected_rows($mysqli) == 0) {
				if($getrow['active'] == 0 OR $getrow['logintime'] == 0) {
					echo "already";
				} elseif($getrow['active'] == 0 AND $getrow['logintime'] == 0) {
					echo "already";
				}
			}
		} elseif($numrow > 1) {
			echo "multiple";
		} elseif($numrow == 0) {
			echo "nouser";
		}
	}
?>