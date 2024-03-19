<? error_reporting(E_ALL);
	date_default_timezone_set("Europe/Berlin");

    // Binde Funktionen ein
	include_once 'includes/functions.php';
	
	// Binde die DB-Connect ein
	include_once 'includes/db_connect.php';
	
	// Starte sichere Session
	sec_session_start();
	
	// Prüfe, ob Zugang aktiv, ansonsten leite weiter
	if(isset($_SESSION['user_id'])) {
		// Baue Event ID aus aktiver User-Session
		$eid	= $_SESSION['user_id'];
		
		// Prüfe, ob korrekte POST übergeben wurde
		if(isset($_POST['rid']) && !empty($_POST['rid'])) {
			// Bereinige übergebene POST und baue Runden-ID
			$rid = mysqli_real_escape_string($mysqli, utf8_encode($_POST['rid']));
			
			$select_zentry = "SELECT * FROM `_main_wptable` WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `z_entry` = 1 LIMIT 1";
			$result_zentry = mysqli_query($mysqli, $select_zentry);
			$numrow_zentry = mysqli_num_rows($result_zentry);
			
			if($numrow_zentry == 1) {
				echo "is_sprint";
			} else {
				echo "no_sprint";
			}
		}
	} elseif(!isset($_SESSION['user_id']) OR $_SESSION['user_id'] == "") {
		echo "no_eid";
	}
	
	