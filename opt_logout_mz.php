<?php
	error_reporting(E_ALL);

	include_once 'db_connect.php';
	include_once 'functions.php';
	
	session_start();
	
	//	Prüfe Logintyp aus Session
	if($_SESSION['logtype'] == "mz") {
		//	Setze Teilnehmerstatus in DB auf inaktiv
		$mz_select = "SELECT * FROM _optio_zmembers WHERE `id` = '" . $_SESSION['user_id'] . "' LIMIT 1";
		$mz_result = mysqli_query($mysqli, $mz_select);
		$mz_getrow = mysqli_fetch_assoc($mz_result);
		
		$eid = $mz_getrow['eid'];
		
		// RETURN SESSION TO LOCAL VARIABLES
		$uid	= $_SESSION['user_id'];
		$rid	= $_SESSION['rid'];
		
		$update_session =	"
							UPDATE 
								`_optio_zmembers` 
							SET 
								`active` = '0',
								`logintime` = '0'
							WHERE 
								`id` = '" . $_SESSION['user_id'] . "' 
							AND 
								`active` = '1'
							";
		$result_session = mysqli_query($mysqli, $update_session);
		
		//	Zerstöre Session, wenn Teilnehmerstatus geändert
		if(mysqli_affected_rows($mysqli) == 1) {
			//	Registriere Logeintrag
			$insert_log =	"
							INSERT INTO 
								`_optio_zmembers_log`(
									`id`,
									`zid`,
									`eid`,
									`rid`,
									`logtime`,
									`action`
								)
							VALUES(
								'NULL',
								'" . $uid . "',
								'" . $eid . "',
								'" . $rid . "',
								'" . time() . "',
								'Logout'
							)";
			$result_log = mysqli_query($mysqli, $insert_log);
			
			//	Teilnehmeränderung erfolgreich
			session_unset();
		 
			//	Setze alle Session-Werte zurück 
			$_SESSION = array();
			 
			//	Hole Session-Parameter 
			$params = session_get_cookie_params();
			 
			//	Lösche das aktuelle Cookie. 
			setcookie(	
						session_name(),
						'', 
						(time() - 42000), 
						$params["path"], 
						$params["domain"], 
						$params["secure"], 
						$params["httponly"]
			);
			
			//	Vernichte die Session 
			session_destroy();
			
			if(is_session_started() === TRUE) {
				if(isset($_GET['error'])) {
					//	Leite weiter
					header('Location: ../timebuddy_fail.php?error=' . $_GET['error']);
				} elseif(!isset($_GET['error'])) {
					//	Leite weiter
					header('Location: ../timebuddy_fail.php?error=0x2010');
				}
			} elseif(is_session_started() === FALSE) {
				//	Leite weiter
				header('Location: ../index.php');
			}
		}
	} elseif($_SESSION['logtype'] == "mb") {
		$bid		= $_SESSION['user_id'];
		$uname		= $_SESSION['username'];
		$whois		= $_SESSION['opt_whois'];
		$logtype	= $_SESSION['logtype'];
		$eid 		= $_SESSION['eid'];
		
		//	Registriere Logeintrag
		$insert_log =	"
						INSERT INTO 
							`_optio_bmembers_log`(
								`id`,
								`bid`,
								`eid`,
								`logtime`,
								`action`
							)
						VALUES(
							'NULL',
							'" . $bid . "',
							'" . $eid . "',
							'" . time() . "',
							'Logout'
						)";
		$result_log = mysqli_query($mysqli, $insert_log);
		
		//	Teilnehmeränderung erfolgreich
		session_unset();
	 
		//	Setze alle Session-Werte zurück 
		$_SESSION = array();
		 
		//	Hole Session-Parameter 
		$params = session_get_cookie_params();
		 
		//	Lösche das aktuelle Cookie. 
		setcookie(	
					session_name(),
					'', 
					(time() - 42000), 
					$params["path"], 
					$params["domain"], 
					$params["secure"], 
					$params["httponly"]
		);
		
		//	Vernichte die Session 
		session_destroy();
		
		if(is_session_started() === TRUE) {
			if(isset($_GET['error'])) {
				//	Leite weiter
				header('Location: ../timebuddy_fail.php?error=' . $_GET['error']);
			} elseif(!isset($_GET['error'])) {
				//	Leite weiter
				header('Location: ../timebuddy_fail.php?error=0x2013');
			}
		} elseif(is_session_started() === FALSE) {
			//	Leite weiter
			header('Location: ../index.php');
		}
	//	Da kein gültiger Parameter vorhanden
	} else {
		//	Leite weiter
		header('Location: ../index.php');
	}
?>