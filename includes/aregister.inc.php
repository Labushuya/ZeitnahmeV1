<?php
	include_once 'includes/db_connect.php';
	include_once 'includes/psl-config.php';
	 
	$error_msg = "";
	 
	if(isset($_POST['username'], $_POST['email'], $_POST['p'], $_POST['anrede'], $_POST['vname'], $_POST['nname'], $_POST['str'], $_POST['nr'], $_POST['plz'], $_POST['ort'], $_POST['agb'], $_POST['abo'])) {
		// Bereinige und überprüfe die Daten
		$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
		$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
		$email = filter_var($email, FILTER_VALIDATE_EMAIL);
		$anrede = filter_input(INPUT_POST, 'anrede', FILTER_SANITIZE_STRING);
		$vname = filter_input(INPUT_POST, 'vname', FILTER_SANITIZE_STRING);
		$nname = filter_input(INPUT_POST, 'nname', FILTER_SANITIZE_STRING);
		$str = filter_input(INPUT_POST, 'str', FILTER_SANITIZE_STRING);
		$nr = filter_input(INPUT_POST, 'nr', FILTER_SANITIZE_STRING);
		$plz = filter_input(INPUT_POST, 'plz', FILTER_SANITIZE_STRING);
		$ort = filter_input(INPUT_POST, 'ort', FILTER_SANITIZE_STRING);
		$agb = filter_input(INPUT_POST, 'agb', FILTER_SANITIZE_STRING);
		$abo = filter_input(INPUT_POST, 'abo', FILTER_SANITIZE_STRING);
		$color = "838B8B";
		$position = "Omni";
		$parent_val = "_main_amembers";
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			// keine gültige E-Mail
			$error_msg .= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>E-Mail Adresse ungültig</span><br />';
		}
	 
		$password = filter_input(INPUT_POST, 'p', FILTER_SANITIZE_STRING);
		if(strlen($password) != 128) {
			// Das gehashte Passwort sollte 128 Zeichen lang sein.
			// Wenn nicht, dann ist etwas sehr seltsames passiert
			$error_msg .= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Ungültige Passwortkonfiguration</span><br />';
		}
	 
		// Benutzername und Passwort wurde auf der Benutzer-Seite schon überprüft.
		// Das sollte genügen, denn niemand hat einen Vorteil, wenn diese Regeln   
		// verletzt werden.
		//
	 
		$prep_stmt = "SELECT id FROM _main_amembers WHERE email = ? LIMIT 1";
		$stmt = $mysqli->prepare($prep_stmt);
	 
		if($stmt) {
			$stmt->bind_param('s', $email);
			$stmt->execute();
			$stmt->store_result();
	 
			if($stmt->num_rows == 1) {
				// Ein Benutzer mit dieser E-Mail-Adresse existiert schon
				$error_msg .= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>E-Mail Adresse bereits vergeben</span><br />';
			}
		} else {
			$error_msg .= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Datenbankfehler</span><br />';
		}
	 
		// Noch zu tun: 
		// Wir müssen uns noch um den Fall kümmern, wo der Benutzer keine
		// Berechtigung für die Anmeldung hat indem wir überprüfen welche Art 
		// von Benutzer versucht diese Operation durchzuführen.
	 
		if(empty($error_msg)) {
			// Erstelle ein zufälliges Salt
			$random_salt = hash('sha512', uniqid(openssl_random_pseudo_bytes(16), TRUE));
	 
			// Erstelle saltet Passwort 
			$password = hash('sha512', $password . $random_salt);
	 
			// Trage den neuen Benutzer in die Datenbank ein 
			if($insert_stmt = $mysqli->prepare("INSERT INTO _main_amembers (username, email, password, salt, anrede, vname, nname, str, nr, plz, ort, agb, abo, color, position, parent_val) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
				$insert_stmt->bind_param('ssssssssssssssss', $username, $email, $password, $random_salt, $anrede, $vname, $nname, $str, $nr, $plz, $ort, $agb, $abo, $color, $position, $parent_val);
				// Führe die vorbereitete Anfrage aus.
				if(! $insert_stmt->execute()) {
					header('Location: /msdn/register_fail.php');
				}
			}
			header('Location: /msdn/register_success.php');
		}
	}
?>