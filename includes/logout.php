<?php
	include_once 'functions.php';
	sec_session_start();
	
	session_unset();
	 
	// Setze alle Session-Werte zurück 
	$_SESSION = array();
	 
	// hole Session-Parameter 
	$params = session_get_cookie_params();
	 
	// Lösche das aktuelle Cookie. 
	setcookie(session_name(),
			'', time() - 42000, 
			$params["path"], 
			$params["domain"], 
			$params["secure"], 
			$params["httponly"]);
	 
	// Vernichte die Session 
	session_destroy();
	header('Location: /msdn/index.php');
?>