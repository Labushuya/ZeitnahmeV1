<?php
	//	Setze Fehlerlevel
	error_reporting(E_ALL);
	
	//	Binde UUID ein
	require_once 'classes/uuid/uuid.php';
							
	//	Binde QR Code Generierung ein
	require_once 'classes/phpqrcode/qrlib.php';
	
	if(isset($_POST['did']) AND isset($_POST['eid'])) {
		// INCLUDE FUNCTIONS
		include_once 'includes/functions.php';
		
		// INCLUDE DB_CONNECT
		include_once 'includes/db_connect.php';
		
		$did = mysqli_real_escape_string($mysqli, $_POST['did']);
		$eid = mysqli_real_escape_string($mysqli, $_POST['eid']);
		
		//	Suche nach Teilnehmer
		$select_tmember = "SELECT * FROM `_optio_tmembers` WHERE `id` = '" . $did . "' AND `eid` = '" . $eid . "'";
		$result_tmember = mysqli_query($mysqli, $select_tmember);
		$getrow_tmember = mysqli_fetch_assoc($result_tmember);
		
		//	Kein QR Code hinterlegt
		if($getrow_tmember['qr_validation'] == "" OR $getrow_tmember['image_path'] == "") {
			//	Generiere neuen QR Code
			$qr_validation = UUID::v4();
			
			//	Prüfe, ob bereits vorhanden
			$select_qr = "SELECT * FROM `_optio_tmembers` WHERE `id` = '" . $did . "' AND `eid` = '" . $eid . "' AND `qr_validation` = '" . $qr_validation . "'";
			$result_qr = mysqli_query($mysqli, $select_qr);
			$numrow_qr = mysqli_num_rows($result_qr);
			
			//	Wenn QRID bereits vorhanden ist (das geht? O_o), durchlaufe Schleife bis neue, einzigartige erstellt wurde
			if($numrow_qr > 0) {
				$getrow_qr = mysqli_fetch_assoc($result);
				
				while($qr_validation == $getrow_qr['qr_validation']) {
					//	Erstelle QRID und prüfe, ob bereits vorhanden
					$qr_validation = UUID::v4();
					
					$select_qr_loop = "SELECT * FROM `_optio_tmembers` WHERE `id` = '" . $did . "' AND `eid` = '" . $eid . "' AND `qr_validation` = '" . $qr_validation . "'";
					$result_qr_loop = mysqli_query($mysqli, $select_qr_loop);
					$numrow_qr_loop = mysqli_num_rows($result_qr_loop);
					
					if($numrow_qr_loop == 0) {
						break;						
					}		
				}
			}
			
			$tempDir = getcwd() . "/images/qr/";		
			$codeContents = 'https://mindsources.net/msdn/qr_login.php?sso=' . $qr_validation;
			
			//	Generiere Dateiname aus Event-ID und Startnummer mit führender Null
			if(strlen($eid) == 1) {
				$file_eid = "00" . $eid;
			} elseif(strlen($eid) == 2) {
				$file_eid = "0" . $eid;
			} elseif(strlen($eid) == 3) {
				$file_eid = $eid;
			}
			
			$fileName = $file_eid . '_' . rand(100, 999) . rand(100, 999) . '_' . md5($codeContents) . '.png';				
			$pngAbsoluteFilePath = $tempDir . $fileName;	
				
			//	Generiere QR-Code
			QRcode::png($codeContents, $pngAbsoluteFilePath);
			
			//	Speichere neu generierten QR-Code in Spalte von Teilnehmer
			$update =	"
						UPDATE 
							`_optio_tmembers` 
						SET 
							`image_path` = 'images/qr/" . $fileName . "', 
							`qr_validation` = '" . $qr_validation . "' 
						WHERE 
							`id` = '" . $did . "' 
						AND 
							`eid` = '" . $eid . "';
						";
			$result_save = mysqli_query($mysqli, $update);

			//	Prüfe, ob Speichern erfolgreich war
			if(mysqli_affected_rows($mysqli) > 0) {
				//	Suche nach Teilnehmer
				$select_tmember = "SELECT * FROM `_optio_tmembers` WHERE `id` = '" . $did . "' AND `eid` = '" . $eid . "'";
				$result_tmember = mysqli_query($mysqli, $select_tmember);
				$getrow_tmember = mysqli_fetch_assoc($result_tmember);
				
				echo "<img src=\"" . $getrow_tmember['image_path'] . "\"></img>";
			} else {
				echo "<span style=\"color: red;\">QR-Code konnte nicht generiert werden!</span>";
			}
		}
	}
?>