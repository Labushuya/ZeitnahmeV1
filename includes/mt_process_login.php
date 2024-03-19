<?php error_reporting(E_ALL);
	include_once 'db_connect.php';
	include_once 'functions.php';
	 
	// TLOGIN
	if(isset($_POST['mt_login'])) {
		// FORM NOT EMPTY
		if(($_POST['mt_uname'] != "" OR !empty($_POST['mt_uname'])) AND ($_POST['mt_upass'] != "" OR !empty($_POST['mt_upass']))) {
			// UNSET SUBMIT
			unset($_POST['mt_login']);
			// REWRITE $_POST
			$uname = mysqli_real_escape_string($mysqli, $_POST['mt_uname']);
			$upass = mysqli_real_escape_string($mysqli, $_POST['mt_upass']);
			
			// SET SELECT AND UPDATE
			$mt_select = "SELECT * FROM _optio_tmembers WHERE `uname` = '" . $uname . "' AND `upass` = '" . $upass . "' LIMIT 1";
			
			// QUERY
			$mt_squery = mysqli_query($mysqli, $mt_select);
			
			// RESULTS AND FETCHING
			$mt_getrow = mysqli_fetch_assoc($mt_squery);
			$mt_numrow = mysqli_num_rows($mt_squery);
			
			// CHECK, IF USER EXISTS
			if($mt_numrow == 1) {
				// START SESSION AND LOG IN USER
				session_start();
				
				// SET RELEVANT SESSIONS
				$_SESSION['user_id']	= $mt_getrow['id'];
				$_SESSION['eid']		= $mt_getrow['eid'];
				$_SESSION['sid']		= $mt_getrow['sid'];
				$_SESSION['class']		= $mt_getrow['class'];
				$_SESSION['fabrikat']	= $mt_getrow['fabrikat'];
				$_SESSION['typ']		= $mt_getrow['typ'];
				$_SESSION['baujahr']	= $mt_getrow['baujahr'];
				$_SESSION['vname_1']	= $mt_getrow['vname_1'];
				$_SESSION['nname_1']	= $mt_getrow['nname_1'];
				$_SESSION['vname_2']	= $mt_getrow['vname_2'];
				$_SESSION['nname_2']	= $mt_getrow['nname_2'];
				$_SESSION['ready']		= $mt_getrow['ready'];
				$_SESSION['logtype']	= "mt";
				
				// REDIRECT 
				header('Location: /msdn/racer.php');
			} else {
				// LOGIN ERROR 
				header('Location: /msdn/login_fail.php');
			}
		} else {
			// Login fehlgeschlagen 
			header('Location: /msdn/login_fail.php');
		}		
	} elseif(isset($_GET['sso']) AND strlen($_GET['sso']) == 36) {
		$qr_validation = mysqli_real_escape_string($mysqli, $_GET['sso']);
		
		// SET SELECT AND UPDATE
		$mt_select = "SELECT * FROM `_optio_tmembers` WHERE `qr_validation` = '" . $qr_validation . "' LIMIT 1";
		
		// QUERY
		$mt_squery = mysqli_query($mysqli, $mt_select);
			
		// RESULTS AND FETCHING
		$mt_getrow = mysqli_fetch_assoc($mt_squery);
		$mt_numrow = mysqli_num_rows($mt_squery);
			
		// CHECK, IF USER EXISTS
		if($mt_numrow == 1) {
			// START SESSION AND LOG IN USER
			session_start();
				
			// SET RELEVANT SESSIONS
			$_SESSION['user_id']	= $mt_getrow['id'];
			$_SESSION['eid']		= $mt_getrow['eid'];
			$_SESSION['sid']		= $mt_getrow['sid'];
			$_SESSION['class']		= $mt_getrow['class'];
			$_SESSION['fabrikat']	= $mt_getrow['fabrikat'];
			$_SESSION['typ']		= $mt_getrow['typ'];
			$_SESSION['baujahr']	= $mt_getrow['baujahr'];
			$_SESSION['vname_1']	= $mt_getrow['vname_1'];
			$_SESSION['nname_1']	= $mt_getrow['nname_1'];
			$_SESSION['vname_2']	= $mt_getrow['vname_2'];
			$_SESSION['nname_2']	= $mt_getrow['nname_2'];
			$_SESSION['ready']		= $mt_getrow['ready'];
			$_SESSION['logtype']	= "mt";
				
			// REDIRECT 
			header('Location: /msdn/racer.php');
		} else {
			// LOGIN ERROR 
			header('Location: /msdn/login_fail.php');
		}		
	} else {
		// Die korrekten POST-Variablen wurden nicht zu dieser Seite geschickt. 
		echo 'Ungültige Anfrage';
	}
?>
