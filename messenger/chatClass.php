<?php
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';

	// START SECURE SESSION
	session_start();
	
	// RETURN SESSION TO LOCAL VARIABLE
	$uid	= $_SESSION['user_id'];
	
	// FETCH EVENT ID
	$select_event = "SELECT * FROM _optio_zmembers WHERE `id` = '" . $uid . "'";
	$result_event = mysqli_query($mysqli, $select_event);
	$getrow_event = mysqli_fetch_assoc($result_event);
	// DECLARE EVENT ID
	$eid = $getrow_event['eid'];

	class chatClass {
		public static function getRestChatLines($id) {
			$arr = array();
			$jsonData = '{"results":[';
			$db_connection = new mysqli(mysqlServer, mysqlUser, mysqlPass, mysqlDB);
			$db_connection->query("SET NAMES 'UTF8'");
			$statement = $db_connection->prepare( "SELECT id, eid, username, color, chattext, chattime FROM _main_messenger WHERE `eid` = '" . $eid . "' AND `id` > ? AND `chattime` >= DATE_SUB(NOW(), INTERVAL 8 HOUR)");
			$statement->bind_param('i', $id);
			$statement->execute();
			$statement->bind_result($id, $username, $color, $chattext, $chattime);
			$line = new stdClass;
			while($statement->fetch()) {
				$line->id = $id;
				$line->eid = $eid;
				$line->username = $username;
				$line->color = $color;
				$line->chattext = $chattext;
				$line->chattime = date('H:i:s', strtotime($chattime));
				$arr[] = json_encode($line);
			}
			$statement->close();
			$db_connection->close();
			$jsonData .= implode(",", $arr);
			$jsonData .= ']}';
			return $jsonData;
		}
		
		public static function setChatLines($eid, $chattext, $username, $color) {
			$db_connection = new mysqli( mysqlServer, mysqlUser, mysqlPass, mysqlDB);
			$db_connection->query("SET NAMES 'UTF8'");
			$statement = $db_connection->prepare("INSERT INTO _main_messenger(eid, username, color, chattext) VALUES(?, ?, ?, ?)");
			$statement->bind_param('isss', $eid, $username, $color, $chattext);
			$statement->execute();
			$statement->close();
			$db_connection->close();
		}
	}
?>