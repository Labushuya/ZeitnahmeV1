<?php
	error_reporting(E_ALL);

	include_once 'db_connect.php';
	include_once 'functions.php';

	// Prüfe, ob Session bereits gestartet wurde
	// PHP Version < 5.4.0
	if (session_id() == '') {
		session_start();
	}
	// PHP Version > 5.4.0, 7
	/*
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}
	*/

	/*
		echo "<pre>";
		print_r($_SESSION);
		echo "</pre>";
		// exit;
	*/

	// Prüfe Logintyp aus Session
	if(isset($_SESSION['logtype']) AND $_SESSION['logtype'] != "") {
		// Prüfe, welchen Login-Typ SESSION enthält
		switch($_SESSION['logtype']) {
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

		$select = "SELECT * FROM `" . $table . "` WHERE `id` = '" . $_SESSION['user_id'] . "' LIMIT 1";
		$result = mysqli_query($mysqli, $select);
		$getrow = mysqli_fetch_assoc($result);

		$eid = $getrow['eid'];

		// RETURN SESSION TO LOCAL VARIABLES
		$uid	= $_SESSION['user_id'];

		$update_session =	"
							UPDATE
								`" . $table . "`
							SET
								`active` = '0',
								`logintime` = '0'
							WHERE
								`id` = '" . $_SESSION['user_id'] . "'
							AND
								`active` = '1'
							";
		$result_session = mysqli_query($mysqli, $update_session);

		// Zerstöre Session, wenn Teilnehmerstatus geändert
		if(mysqli_affected_rows($mysqli) == 1) {
			// Registriere Logeintrag
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
								'" . $uid . "',
								'" . $eid . "',
								'" . time() . "',
								'Logout'
							)
							";
			$result_log = mysqli_query($mysqli, $insert_log);

			// Teilnehmeränderung erfolgreich
			session_unset();

			// Setze alle Session-Werte zurück
			$_SESSION = array();

			// Hole Session-Parameter
			$params = session_get_cookie_params();

			// Lösche das aktuelle Cookie.
			setcookie(
						session_name(),
						'',
						(time() - 42000),
						$params["path"],
						$params["domain"],
						$params["secure"],
						$params["httponly"]
			);

			// Vernichte die Session
			session_destroy();

			if(is_session_started() === TRUE) {
				//  Leite weiter auf Fehlerseite
			    header("Location: /msdn/error.php?code=logout&add=failed&type=" . $_SESSION['logtype']);
			} elseif(is_session_started() === FALSE) {
				// Leite weiter
				header('Location: ../index.php');
			}
		}
	// Da kein gültiger Parameter vorhanden
	} else {
		// Setze alle Session-Werte zurück
		session_unset();
		$_SESSION = array();

		// Hole Session-Parameter
		$params = session_get_cookie_params();

		// Lösche das aktuelle Cookie.
		setcookie(
			session_name(),
			'',
			(time() - 42000),
			$params["path"],
			$params["domain"],
			$params["secure"],
			$params["httponly"]
		);

		// Vernichte die Session
		session_destroy();

		if(is_session_started() === TRUE) {
			//  Leite weiter auf Fehlerseite
			header("Location: /msdn/error.php?code=logout&add=failed&type=" . $_SESSION['logtype']);
		} elseif(is_session_started() === FALSE) {
			// Leite weiter
			header('Location: ../index.php');
		}
	}
?>
