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

	$update = "UPDATE _main_wptable SET `" . $column . "` = '" . $editval . "' WHERE `eid` = '" . $eid . "' AND `id` = '" . $id . "'";
	
	echo $update;
	mysqli_query($mysqli, $update);	
?>