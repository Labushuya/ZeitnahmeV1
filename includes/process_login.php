<?php error_reporting(E_ALL);
	include_once 'db_connect.php';
	include_once 'functions.php';
	 
	sec_session_start(); // Unsere selbstgemachte sichere Funktion um eine PHP-Sitzung zu starten.
	 
	// ALOGIN
	if(isset($_POST['email'], $_POST['p'])) {
		$email = $_POST['email'];
		$password = $_POST['p']; // Das gehashte Passwort.
	 
		if(login($email, $password, $mysqli) == true) {
			// Login erfolgreich 
			header('Location: /msdn/index.php');
		} else {
			// Login fehlgeschlagen 
			header('Location: /msdn/login_fail.php');
		}		
	} else {
		// Die korrekten POST-Variablen wurden nicht zu dieser Seite geschickt. 
		echo 'Ungültige Anfrage';
	}
	
	// VLOGIN
	/*
	if(isset($_POST['email'], $_POST['p'])) {
		$email = $_POST['email'];
		$password = $_POST['p']; // Das gehashte Passwort.
	 
		if(alogin($email, $password, $mysqli) == true) {
			// Login erfolgreich 
			header('Location: /msdn/index.php');
		} else {
			// Login fehlgeschlagen 
			header('Location: /msdn/login_fail.php');
		}
	} else {
		// Die korrekten POST-Variablen wurden nicht zu dieser Seite geschickt. 
		echo 'Ungültige Anfrage';
	}
	
	// ZLOGIN
	if(isset($_POST['email'], $_POST['p'])) {
		$email = $_POST['email'];
		$password = $_POST['p']; // Das gehashte Passwort.
	 
		if(alogin($email, $password, $mysqli) == true) {
			// Login erfolgreich 
			header('Location: /msdn/index.php');
		} else {
			// Login fehlgeschlagen 
			header('Location: /msdn/login_fail.php');
		}
	} else {
		// Die korrekten POST-Variablen wurden nicht zu dieser Seite geschickt. 
		echo 'Ungültige Anfrage';
	}
	
	// TLOGIN
	if(isset($_POST['email'], $_POST['p'])) {
		$email = $_POST['email'];
		$password = $_POST['p']; // Das gehashte Passwort.
	 
		if(alogin($email, $password, $mysqli) == true) {
			// Login erfolgreich 
			header('Location: /msdn/index.php');
		} else {
			// Login fehlgeschlagen 
			header('Location: /msdn/login_fail.php');
		}
	} else {
		// Die korrekten POST-Variablen wurden nicht zu dieser Seite geschickt. 
		echo 'Ungültige Anfrage';
	}
	*/
?>