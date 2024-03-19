<?php
	error_reporting(E_ALL);

	include_once 'db_connect.php';
	include_once 'functions.php';
	
	session_start();
	
	//	Setze Teilnehmerstatus in DB auf inaktiv
	$update_session = "UPDATE `_option_zmembers` SET `active` = '0' WHERE `id` = '" . $_SESSION['user_id'] . "' AND `active` = '1'";
	$result_session = mysqli_query($mysqli, $update_session);
	
	//	Zerstöre Session, wenn Teilnehmerstatus geändert
	if(mysqli_affected_rows($mysqli) == 1) {
		//	Teilnehmeränderung erfolgreich
		session_unset();
	 
		//	Setze alle Session-Werte zurück 
		$_SESSION = array();
		 
		//	Hole Session-Parameter 
		$params = session_get_cookie_params();
		 
		//	Lösche das aktuelle Cookie. 
		setcookie(	session_name(),
					'', 
					time() - 42000, 
					$params["path"], 
					$params["domain"], 
					$params["secure"], 
					$params["httponly"]
		);
		 
		//	Vernichte die Session 
		session_destroy();
	}
	
	//	Leite weiter
	header('Location: /msdn/index.php');
?>