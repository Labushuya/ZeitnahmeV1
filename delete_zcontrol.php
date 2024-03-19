<? error_reporting(E_ALL);
    //	Binde Funktionen ein
	include_once 'includes/functions.php';
	
	//	Binde Config ein
	include_once 'includes/db_connect.php';
	
	//	Starte Funktion Secure Session
	sec_session_start();
	
	//	Hole Event ID aus aktiver Session
	$eid = $_SESSION['user_id'];
	
	//	Prüfe auf Übergabeparameter
	if(isset($_POST["id"]) && !empty($_POST["id"])) {
		//	Bereinige Übergabeparameter
		$zid = mysqli_real_escape_string($mysqli, $_POST['id']);
		
		//	Splitte Übergebene ID auf
		$explode = explode("_", $zid);
		
		$zkid1 = $explode[0];
		$zkid2 = $explode[1];
		
		//	Lösche zugehörige Datensätze
        $delete_zpos = "DELETE FROM `_optio_zcontrol` WHERE `id` = '" . $zkid1 . "' OR `id` = '" . $zkid2 . "'";
		mysqli_query($mysqli, $delete_zpos);
	
		//	Lösche zugehörige Datensatz Informationen
        $delete_bpos = "DELETE FROM `_optio_zcontrol_relation` WHERE `zkid1` = '" . $zkid1 . "' AND `zkid2` = '" . $zkid2 . "'";
		mysqli_query($mysqli, $delete_bpos);
		
		//	Zähle bestehende Datensätze, um Master-Tabelle zu aktualisieren
		$select = "SELECT id FROM `_optio_zcontrol` WHERE `eid` = '" . $eid . "'";
		$result = mysqli_query($mysqli, $select);
		$numrow = mysqli_num_rows($result);
		
		//	Aktualisiere Master-Tabelle
		$update = "UPDATE `_race_run_events` SET `count_zcontrol` = '" . $numrow . "' WHERE `eid` = '" . $eid . "'";
		mysqli_query($mysqli, $update);
    }
?>