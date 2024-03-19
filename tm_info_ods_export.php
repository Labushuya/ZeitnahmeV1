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
		
	// INCLUDE SPREADSHEET FUNCIONS
	require 'classes/spreadsheet/vendor/autoload.php';
		
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
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
							
				// ERSTELLE NEUES SPREADSHEET
				$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet();
								
				// ERSTELLE CONTAINER MIT ALPHABET
				$alphabet = 	[
									1 	=> 'A',
									2 	=> 'B',
									3 	=> 'C',
									4 	=> 'D',
									5 	=> 'E',
									6 	=> 'F',
									7 	=> 'G',
									8 	=> 'H',
									9 	=> 'I',
									10 	=> 'J',
									11 	=> 'K',
									12 	=> 'L',
									13 	=> 'M',
									14 	=> 'N',
									15 	=> 'O',
									16 	=> 'P',
									17 	=> 'Q',
									18 	=> 'R',
									19 	=> 'S',
									20 	=> 'T',
									21 	=> 'U',
									22 	=> 'V',
									23 	=> 'W',
									24 	=> 'X',
									25 	=> 'Y',
									26 	=> 'Z'
								];
								
				// Erstelle Tabellenkopf
				$xlsx_header =	[
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
				for($i = 1; $i < (count($xlsx_header) + 1); $i++) {
					$sheet->setCellValue(($alphabet[$i] . '1'), $xlsx_header[$i])->getStyle(($alphabet[$i] . '1'))->getFont()->setBold(true);
				}
													
				// SETZE HEADER STYLE
				$header_range = ($alphabet[1] . '1:' . $alphabet[count($xlsx_header)] . '1');
							
				// PACKE HEADER IN SPREADSHEET
				for($i = 1; $i < (count($xlsx_header) + 1); $i++) {
					$sheet->setCellValue(($alphabet[$i] . '1'), $xlsx_header[$i])->getStyle(($alphabet[$i] . '1'))->getFont()->setBold(true);
				}
									
				// SETZE STYLE FÜR HEADER
				$spreadsheet->getActiveSheet()->getStyle($header_range)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
												
				//	Benenne Worksheet um in "Auswertung XPN [ Sortierung nach Abweichung ]"
				$spreadsheet->getActiveSheet()->setTitle("Teilnehmerdaten");
									
				//	Automatische Breits der einzelnen Spalten
				foreach(range('A','I') as $col) {
					$spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);  
				}
				
				//	Manuelle Zählervariable
				$i = 2;
				
				while($getrow = mysqli_fetch_assoc($result)) {	
					//	Prüfe, ob SSO Link vorhanden
					if($getrow['qr_validation'] != "") {
						//	Generiere SSO Link
						$sso = "https://mindsources.net/msdn/qr_login.php?sso=" . $getrow['qr_validation'];
					} else {
						$sso = "Kein QR Login hinterlegt. Bitte QR Code neu anfordern oder Teilnehmerdaten erneut anlegen!";
					}
					
					//	Lege Werte fest
					$sheet->setCellValue(("A" . $i), $getrow['sid']);
					$sheet->setCellValue(("B" . $i), $getrow['class']);
					$sheet->setCellValue(("C" . $i), $getrow['fabrikat'] . " " . $getrow['typ']);
					$sheet->setCellValue(("D" . $i), $getrow['baujahr']);
					$sheet->setCellValue(("E" . $i), $getrow['vname_1'] . " " . $getrow['nname_1']);
					$sheet->setCellValue(("F" . $i), $getrow['vname_2'] . " " . $getrow['nname_2']);
					$sheet->setCellValue(("G" . $i), $getrow['uname']);
					$sheet->setCellValue(("H" . $i), $getrow['upass']);
					$sheet->setCellValue(("I" . $i), $sso);
					
					//	Erhöhe manuelle Zählervariable
					$i++;
				}
				
				//	Zentriere alles
				$spreadsheet->getActiveSheet()->getStyle('A1:J' . $i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
				$spreadsheet->getActiveSheet()->getStyle('A1:J' . $i)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
				
				$spreadsheet->setActiveSheetIndex(0);
									
				$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Ods');
				$writer->save($document_desc . '.ods');
				
				$file = $document_desc . ".ods";
				header('Content-Description: File Transfer');
				header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
				header('Content-Disposition: attachment; filename=' . $file);
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file));
				readfile($file);
				
				// WORKAROUND: DELETE FILE AFTER DOWNLOAD
				unlink($file);
				
				exit();
						
				// Wenn Datei nicht per AJAX angefordert wird, erzwinge Download
				if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
					header("Content-Disposition: attachment; filename=" . $document_desc . ".ods");
				}
			}
		} else {
			echo "<script>window.close();</script>";
		}
	} else {
		echo "<script>window.close();</script>";
	}
?>