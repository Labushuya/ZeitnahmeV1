<?php 
	// SET ERROR LEVEL
	error_reporting(E_ALL);
		
	// BUFFER OUTPUT
	ob_start();
		
	// SET TIMEZONE
	date_default_timezone_set("Europe/Berlin");
	
	$timestamp = time();
		
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
		
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
	
	// START SECURE SESSION
	sec_session_start();
	
	//	Prüfe, ob Session gesetzt ist
	if(isset($_SESSION['user_id'])) {
		// CUSTOM NAVBAR
		if(login_check($mysqli) == true) {
			$eid	= $_SESSION['user_id'];
			
			//	Suche nach allen Teilnehmerdaten
			$select = "SELECT * FROM `_optio_tmembers` WHERE `eid` = '" . $eid . "' ORDER BY `sid` ASC";
			$result = mysqli_query($mysqli, $select);
			$numrow = mysqli_num_rows($result);
			
			if($numrow > 0) {
				// Erstelle Dateiname basieren auf Prüfung
				$document_desc = date("YmdHis", $timestamp) . "_teilnehmerdaten";
							
				// Ausgabe Header, um Datei herunterzuladen, anstelle sie anzuzeigen
				header("Last-Modified: " . date("d.m.Y - H:i:s", $timestamp));
				header('Content-Type: text/csv; charset=utf-8');

				// Wenn Datei nicht per AJAX angefordert wird, erzwinge Download
				if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
					header("Content-Disposition: attachment; filename=" . $document_desc . ".csv");
				}
				
				// CREATE A FILE POINTER CONNCETED TO THE OUTPUT STREAM
				// Erstelle einen Dateizeiger, der mit dem Ausgabenverlauf verbunden ist
				$output = fopen('php://output', 'w');
				fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
								
				// Erstelle Tabellenkopf
				$header =	[
								 1 => "Startnummer",
								 2 => "Klasse",
								 3 => "Fahrzeug",
								 4 => "Baujahr",
								 5 => "Fahrer",
								 6 => "Beifahrer",
								 7 => "Zugang-ID",
								 8 => "Kennwort",									
								 9 => "SSO URL"									
							];
											
				// PACKE HEADER IN SPREADSHEET
				fputcsv($output, $header, ';');
													
				$input_range = array();
				
				//	Manuelle Zählervariable
				$i = 1;
				
				while($getrow = mysqli_fetch_assoc($result)) {
					//	Prüfe, ob SSO Link vorhanden
					if($getrow['qr_validation'] != "") {
						//	Generiere SSO Link
						$sso = "https://mindsources.net/msdn/qr_login.php?sso=" . $getrow['qr_validation'];
					} else {
						$sso = "Kein QR Login hinterlegt. Bitte QR Code neu anfordern oder Teilnehmerdaten erneut anlegen!";
					}
					
					$input_range[$getrow['sid']][0] = $getrow['sid'];
					$input_range[$getrow['sid']][1] = $getrow['class'];
					$input_range[$getrow['sid']][2] = $getrow['fabrikat'] . " " . $getrow['typ'];
					$input_range[$getrow['sid']][3] = $getrow['baujahr'];
					$input_range[$getrow['sid']][4] = $getrow['vname_1'] . " " . $getrow['nname_1'];
					$input_range[$getrow['sid']][5] = $getrow['vname_2'] . " " . $getrow['nname_2'];
					$input_range[$getrow['sid']][6] = $getrow['uname'];
					$input_range[$getrow['sid']][7] = $getrow['upass'];
					$input_range[$getrow['sid']][8] = $sso;
					
					if(isset($input_range[$i]) AND $input_range[$i] != "") {
						// 	Packe Tabelle in CSV
						fputcsv($output, $input_range[$i], ';');
					}
					
					//	Erhöhe manuelle Zählervariable
					$i++;
				}
			}
		} else {
			echo "<script>window.close();</script>";
		}
	} else {
		echo "<script>window.close();</script>";
	}
?>