<?php error_reporting(E_ALL);
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
	
	// START SECURE SESSION
	sec_session_start();
	 
	// CUSTOM NAVBAR
	if(login_check($mysqli) == true) {
		// SEARCH FOR USER EVENTS
		// CREATE EVENT ID FROM ACTIVE SESSION
		$eid	= $_SESSION['user_id'];
		
		// SEARCH FOR EVENTS FROM LOGGED IN USER
		$select_event = "SELECT `id`, `eid` FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `active` = '1'";
		$result_event = mysqli_query($mysqli, $select_event);
		$numrow_event = mysqli_num_rows($result_event);
		
		// SEARCH FOR EVENTS FROM LOGGED IN USER
		if($numrow_event == 1) {
			$logged = file_get_contents("essentials/logout.html");
			$navbar = file_get_contents("essentials/navbar_logged_in_active.html");
		// NO EVENT FOUND
		} elseif($numrow_event == 0) {
			$logged = file_get_contents("essentials/logout.html");
			$navbar = file_get_contents("essentials/navbar_logged_in.html");
		}
	} else {
		$logged = file_get_contents("essentials/login.html");
		$navbar = file_get_contents("essentials/navbar_logged_out.html");
		header("Location: index.php");
	}
	
	// EVENT SAVE / EDIT
	// CHECK FOR LOGGED IN USER
	if(@$_SESSION['user_id'] >= 1) {
		//	Trigger Status bei Initiativaufruf
		if(!isset($_SESSION['trigger_active_dummy'])) {
			$_SESSION['trigger_active_dummy'] = "";
		}
		
		if(!isset($_SESSION['trigger_active_single'])) {
			$_SESSION['trigger_active_single'] = "";
		}
		
		if(!isset($_SESSION['trigger_active_upload'])) {
			$_SESSION['trigger_active_upload'] = "trigger_active";
		}	
		
		// CREATE EVENT HANDLER
		$event_handler	= "";
		
		//	Zählervariable für erfolgreichen Update
		$success_update = 0;
		
		//	Zählervariable für erfolgreichen Update
		$success_insert = 0;
		
		// CREATE EVENT ID FROM ACTIVE SESSION
		$eid	= $_SESSION['user_id'];
		
		// CREATE QUERIES
		$se_event				= "SELECT * FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `active` = '1'";
		$se_active				= "SELECT `id`, `eid`, `edit` FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `edit` = '0' AND `active` = '1'";
		$se_inactive			= "SELECT `id`, `eid`, `edit` FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `edit` = '1' AND `active` = '1'";
		$up_estatus_setactive	= "UPDATE `_race_run_events` SET `edit` = 'yes' WHERE `eid` = '" . $eid . "' AND `active` = '1'";
		$up_estatus_setinactive	= "UPDATE `_race_run_events` SET `edit` = 'no' WHERE `eid` = '" . $eid . "' AND `active` = '1'";
		
		// SEARCH FOR EVENTS FROM LOGGED IN USER
		if($se_event == true) {
			$result_se_event = mysqli_query($mysqli, $se_event);
			$anzahl_se_event = mysqli_num_rows($result_se_event);
				
			// EVENT FOUND	
			if($anzahl_se_event > 0) {	
				$result_se_inactive = mysqli_query($mysqli, $se_inactive);
				$anzahl_se_inactive = mysqli_num_rows($result_se_inactive);
				
				// EVENT ON STATUS INACTIVE FOUND
				if($anzahl_se_inactive > 0) {
					// FETCH MAXIMUM TMEMBERS LEFT FOR UPLOAD
					$select_maximum = "SELECT `count_tmembers` FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `active` = '1'";
					$result_maximum = mysqli_query($mysqli, $select_maximum);
					$getrow_maximum = mysqli_fetch_assoc($result_maximum);
					$differ_maximum = 200 - (int)$getrow_maximum['count_tmembers'];
					// COLOR STATUS
					switch($differ_maximum) {
						case $differ_maximum >= 50:
							$status = "#00FF00";
						break;
						case ($differ_maximum <= 49 AND $differ_maximum >= 26):
							$status = "#FFFF00";
						break;
						case ($differ_maximum <= 25 AND $differ_maximum >= 0):
							$status = "#FF0000";
						break;
					}
					
					// CHECK IF LIMIT REACHED, ELSE ALLOW UPLOAD
					$select_running_event = "SELECT `id`, `eid`, `count_tmembers` FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `active` = '1'";
					$result_running_event = mysqli_query($mysqli, $select_running_event);
					$getrow_running_event = mysqli_fetch_assoc($result_running_event);
					if($getrow_running_event['count_tmembers'] == 200) {
						$event_handler = 	'
											<div>
											<table width="385px" cellspacing="5px" style="border: 1px solid #FFFFFF;">
												<tr>
													<td align="center" style="font-size: large; font-weight: bold; color: #FFD700;">Maximales Limit an Teilnehmern erreicht!</td> 
												</tr>
												<tr>
													<td align="center"><hr class="white-hr"></td>
												</tr>
												<tr>
													<td align="justify" style="font-size: small;"><br />Bitte löschen Sie bestehende Teilnehmer, um neue oder weitere Ihrer laufenden Veranstaltung hinzufügen zu können!<br /><br /></td>
												</tr>
												<tr>
													<td align="center"><hr class="white-hr"></td>
												</tr>
												<tr>
													<td align="center" style="font-size: small;"><br />Sie werden weitergeleitet ... <img src="images/ripple12px-fts2.gif"<br /><br /></td>
												</tr>
											</table>
											</div>
											';
						echo '<meta http-equiv="refresh" content="5; url=/msdn/_delmt.php" />';
					} elseif($getrow_running_event['count_tmembers'] <= 199) {						
						// DEFINE EMPTY VARIABLE
						$html			= '';
						$info_getback	= '';
						
						// DEFINE UPLOAD TABLE
						$upload =	'
									<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF;">
										<tr>
											<td colspan="2" align="center" class="hint"><font size="1">Dateien sind ausschließlich im Excel-,<div class="tooltip"><font color="#FFD700">*.xls</font><span class="tooltiptext">Speichern Sie Ihr Dokument als <strong>Excel 97 &mdash; 2003 Arbeitsmappe</strong> ab</span></div> bzw. Excel-Arbeitsmappe <div class="tooltip"><font color="#FFD700">*.xlsx</font><span class="tooltiptext">Speichern Sie Ihre Datei als Excel-Arbeitsmappe ab</span></div> gültig</font><br /><br /></td>
										</tr>
										<tr>
											<td colspan="2" align="center">
												<div id="dialog_mt_pic" class="modal_fix" title="Tutorial: Eine Teilnehmerliste korrekt hochladen" style="color: #8E6516; background: transparent; background-color: #A09A8E;">
													<p align="center"><img src="images/excel_example.jpg"></img></p>
													<p>
														<br />  
													</p>
													<p>
														<table width="100%" cellspacing="5px" style="border: 1px solid #FFFFFF;">
															<tr>
																<td align="center">Ende des Tutorials. Sie können das Fenster rechts oben über <img src="images/close_modal.png"></img> schließen!</td>
															</tr>
														</table>
													</p>
													<p>
														<br />  
													</p>
												</div>
												<a href="#" id="opener_mt_pic" style="color: #FFFFFF;"><img src="images/excel_example_thumbnail.jpg"></img></a>
											</td>
										</tr>
										<tr>
											<td colspan="2" align="center">&nbsp;</td>
										</tr>
										<tr>
											<td colspan="2" align="center"><input type="file" name="file" id="file" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 100%" required="required" /></td>
										</tr>
										<tr>
																	<td colspan="2" align="center">
																		<select name="override" style="padding: 2px; background: transparent; background-color: #FFFFFF; color: #8E6516; width: 100%" required="required">
																			<option selected disabled>Bestehende Daten werden:</option>
																			<option value="all">vollständig überschrieben</option>
																			<option value="whois">ohne Ändern der Zugangsdaten überschrieben</option>
																			<option value="skip">ignoriert / übersprungen</option>
																		</select>
																	</td>
																</tr>
									</table>
									<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF;">
										<tr>
											<th colspan="2">Eingaben speichern</th>
										</tr>
										<tr>
											<th colspan="2"><hr /></th>
										</tr>
										<tr>
											<td align="left"><input type="button" value="<<" onclick="location.href=\'my_event.php\';" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;"/></td>
											<td align="right"><input type="submit" name="upload" id="upload" value="Speichern" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" /></td>
										</tr>
										<tr>
											<td colspan="2" align="center">&nbsp;</td>
										</tr>
										<tr>
											<th colspan="2"><font size="2" color="' . $status . '">Sie können noch maximal ' . $differ_maximum . ' Teilnehmer hochladen!</font></th>
										</tr>
									</table>
									';
						
						// DATA UPLOAD
						if(isset($_POST['upload'])) {
							//	Binde UUID ein
							require_once 'classes/uuid/uuid.php';
							
							//	Binde QR Code Generierung ein
							require_once 'classes/phpqrcode/qrlib.php';
							
							//	Binde Excel Reader ein
							require 'classes/spreadsheet/vendor/autoload.php';
							
							$filename = $_FILES["file"]["tmp_name"];
							$filetype = $_FILES["file"]["type"];
							$allowed =  array(
							                0 => 'application/vnd.ms-excel', 
							                1 => 'application/msexcel',
											2 => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
							            );
							//	$ext = pathinfo($filename, PATHINFO_EXTENSION);
							
							//	echo $filetype;
							
							/*
							MICROSOFT OFFICE
							application/vnd.ms-excel od. application/msexcel                    .xls .xlb .xlt
							application/vnd.ms-excel.addin.macroEnabled.12                      .xlam
							application/vnd.ms-excel.sheet.binary.macroEnabled.12               .xlsb
							application/vnd.ms-excel.sheet.macroEnabled.12                      .xlsm
							application/vnd.ms-excel.template.macroEnabled.12                   .xltm
							application/vnd.openxmlformats-officedocument.spreadsheetml.sheet   .xlsx
							OPEN OFFICE
							application/vnd.oasis.opendocument.spreadsheet 	                    .ods
							*/
							
							//	Suche Kennungsdaten aller aktiven Teilnehmer
							$select_all_sid = "SELECT `eid`, `uname`, `upass`, `qr_validation` FROM `_optio_tmembers` WHERE `eid` = '" . $eid . "'";
							$result_all_sid = mysqli_query($mysqli, $select_all_sid);
							$numrow_all_sid = mysqli_num_rows($result_all_sid);
							
							//	Erstelle Array mit bereits bestehenden Kennungsdaten
							$uname_pool = array();
							$upass_pool = array();
							$uqrcd_pool = array();
							
							//	Keine aktiven Teilnehmer gefunden
							if($numrow_all_sid == 0) {
								//	Speichere je einen Leerwert in Pool-Arrays
								$uname_pool[] = "";
								$upass_pool[] = "";
								$uqrcd_pool[] = "";
							} else {
								//	Speichere Kennungsdaten für späteren Abgleich aus DB in Arrays
								while($getrow_all_sid = mysqli_fetch_assoc($result_all_sid)) {
									$uname_pool[] = $getrow_all_sid['uname'];
									$upass_pool[] = $getrow_all_sid['upass'];
									$uqrcd_pool[] = $getrow_all_sid['qr_validation'];
								}
							}														
							
							//	Bereinige Übergabeparameter für Überschreibungsmodus
							$override = mysqli_real_escape_string($mysqli, $_POST['override']);
							
							//	Prüfe, ob Übergabeparameter für Überschreiben gesetzt wurde
							if($override AND ($override == "whois" OR $override == "all" OR $override == "skip")) {						
								//	Prüfe, ob Datei-Typ in Array zulässiger Datei-Typen
								if(in_array($filetype, $allowed)) {
									switch($filetype) {
										case "application/vnd.ms-excel":
										case "application/msexcel":
											$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
										break;
										case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
											$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
										break;
										default:
											$reader = false;
										break;
										/*
											$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
											$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xml();
											$reader = new \PhpOffice\PhpSpreadsheet\Reader\Ods();
											$reader = new \PhpOffice\PhpSpreadsheet\Reader\Slk();
											$reader = new \PhpOffice\PhpSpreadsheet\Reader\Gnumeric();
											$reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
										*/
									}
									
									//	Wurde Reader gefunden, kann Upload beginnen
									if($reader != false) {
										//	Lade Datei in Spreadsheet Objekt
										$spreadsheet = $reader->load($filename);
										
										$sheetData = $spreadsheet->getActiveSheet()->toArray();
										
										/*
											echo "<pre>";
											print_r($spreadsheet);
											//	print_r($sheetData);
											echo "</pre>";
											//	exit;
										*/
										
										//	Array zum Abgreifen schadhafter Informationen
										$malicious =	array(
															0 => "TRUNCATE",
															1 => "DELETE",
															2 => "FLUSH",
															3 => "SELECT",
															4 => "INSERT",
															5 => "DROP",
															6 => "SHOW",
															7 => "CREATE",
															8 => "ALTER",
															9 => "UPDATE"
														);
														
										function striposa($haystack, $needle, $offset = 0) {
											if(!is_array($needle)) {
												$needle = array($needle);
											}
											
											foreach($needle AS $query) {
												if(stripos($haystack, $query, $offset) !== false) {
													//	Stoppe nach erstem Vorkommen
													return true;
												}
											}
											
											return false;
										}
										
										//	Erstelle Zähler für anschließenden Status
										$counter_success_update = 0;
										$counter_success_insert = 0;
										
										//	Fehlerhafte Daten werden übersprungen
										$counter_skipped = 0;
										
										//	Array zum Speichern doppelter Startnummern
										$duplicates = array();
										
										//	Extrahiere Daten aus Array
										for($i = 0; $i < count($sheetData); $i++) {
											$counter_skipped_sid = 0;
											$counter_skipped_vn1 = 0;
											$counter_skipped_nn1 = 0;
											$counter_skipped_vn2 = 0;
											$counter_skipped_nn2 = 0;
											$counter_skipped_cls = 0;
											$counter_skipped_typ = 0;
											$counter_skipped_fab = 0;
											$counter_skipped_bjr = 0;
											
											//	Nehme nur Datensätze, deren Inhalt vorhanden ist
											if(isset($sheetData[$i]) AND $sheetData[$i] != "") {
												//	Prüfe, ob Inhalt einzelner Sub-Indizes vorhanden
												//	Startnummer
												if(isset($sheetData[$i][0]) AND $sheetData[$i][0] != "") {
													$sid = mysqli_real_escape_string($mysqli, $sheetData[$i][0]);
													
													//	Prüfe, ob verwendete Strings schadhafte Informationen enthalten (=== false: nicht gefunden)
													if(striposa($sid, $malicious) !== false) {														//	Debugging														echo "SID: " . $sid . "<br />";														
														//	Schadhafte Informationen gefunden
														$counter_skipped_sid++;
														
														//	Leere String
														$sid = "";
													}
												} else {
													$sid = "";
												}
												
												//	Vorname Fahrer
												if(isset($sheetData[$i][1]) AND $sheetData[$i][1] != "") {
													$vn1 = mysqli_real_escape_string($mysqli, $sheetData[$i][1]);
													
													//	Prüfe, ob verwendete Strings schadhafte Informationen enthalten (=== false: nicht gefunden)
													if(striposa($vn1, $malicious) !== false) {														//	Debugging														echo "VN1: " . $vn1 . "<br />";														
														//	Schadhafte Informationen gefunden
														$counter_skipped_vn1++;
														
														//	Leere String
														$vn1 = "";
													}
												} else {
													$vn1 = "";
												}
												
												//	Nachname Fahrer
												if(isset($sheetData[$i][2]) AND $sheetData[$i][2] != "") {
													$nn1 = mysqli_real_escape_string($mysqli, $sheetData[$i][2]);
													
													//	Prüfe, ob verwendete Strings schadhafte Informationen enthalten (=== false: nicht gefunden)
													if(striposa($nn1, $malicious) !== false) {														//	Debugging														echo "NN1: " . $nn1 . "<br />";																												//	Schadhafte Informationen gefunden
														$counter_skipped_nn1++;
														
														//	Leere String
														$nn1 = "";
													}
												} else {
													$nn1 = "";
												}
												
												//	Vorname Beifahrer
												if(isset($sheetData[$i][3]) AND $sheetData[$i][3] != "") {
													$vn2 = mysqli_real_escape_string($mysqli, $sheetData[$i][3]);
													
													//	Prüfe, ob verwendete Strings schadhafte Informationen enthalten (=== false: nicht gefunden)
													if(striposa($vn2, $malicious) !== false) {														//	Debugging														echo "VN2: " . $vn2 . "<br />";														
														//	Schadhafte Informationen gefunden
														$counter_skipped_vn2++;
														
														//	Leere String
														$vn2 = "";
													}
												} else {
													$vn2 = "";
												}
												
												//	Nachname Beifahrer
												if(isset($sheetData[$i][4]) AND $sheetData[$i][4] != "") {
													$nn2 = mysqli_real_escape_string($mysqli, $sheetData[$i][4]);
													
													//	Prüfe, ob verwendete Strings schadhafte Informationen enthalten (=== false: nicht gefunden)
													if(striposa($nn2, $malicious) !== false) {														//	Debugging														echo "NN2: " . $nn2 . "<br />";														
														//	Schadhafte Informationen gefunden
														$counter_skipped_nn2++;
														
														//	Leere String
														$nn2 = "";
													}
												} else {
													$nn2 = "";
												}
												
												//	Klasse
												if(isset($sheetData[$i][5]) AND $sheetData[$i][5] != "") {
													$cls = mysqli_real_escape_string($mysqli, $sheetData[$i][5]);
													
													//	Prüfe, ob verwendete Strings schadhafte Informationen enthalten (=== false: nicht gefunden)
													if(striposa($cls, $malicious) !== false) {														//	Debugging														echo "CLS: " . $cls . "<br />";														
														//	Schadhafte Informationen gefunden
														$counter_skipped_cls++;
														
														//	Leere String
														$cls = "";
													}
												} else {
													$cls = "";
												}
												
												//	Fabrikat
												if(isset($sheetData[$i][6]) AND $sheetData[$i][5] != "") {
													$fab = mysqli_real_escape_string($mysqli, $sheetData[$i][6]);
													
													//	Prüfe, ob verwendete Strings schadhafte Informationen enthalten (=== false: nicht gefunden)
													if(striposa($fab, $malicious) !== false) {														//	Debugging														echo "FAB: " . $fab . "<br />";														
														//	Schadhafte Informationen gefunden
														$counter_skipped_fab++;
														
														//	Leere String
														$fab = "";
													}
												} else {
													$fab = "";
												}
												
												//	Typ
												if(isset($sheetData[$i][7]) AND $sheetData[$i][7] != "") {
													$typ = mysqli_real_escape_string($mysqli, $sheetData[$i][7]);
													
													//	Prüfe, ob verwendete Strings schadhafte Informationen enthalten (=== false: nicht gefunden)
													if(striposa($typ, $malicious) !== false) {														//	Debugging														echo "TYP: " . $typ . "<br />";														
														//	Schadhafte Informationen gefunden
														$counter_skipped_typ++;
														
														//	Leere String
														$typ = "";
													}
												} else {
													$typ = "";
												}
												
												//	Baujahr
												if(isset($sheetData[$i][8]) AND $sheetData[$i][8] != "") {
													$bjr = mysqli_real_escape_string($mysqli, $sheetData[$i][8]);
													
													//	Prüfe, ob verwendete Strings schadhafte Informationen enthalten (=== false: nicht gefunden)
													if(striposa($bjr, $malicious) !== false) {														//	Debugging														echo "BJR: " . $bjr . "<br /><hr /><br />";														
														//	Schadhafte Informationen gefunden
														$counter_skipped_bjr++;
														
														//	Leere String
														$bjr = "";
													}
												} else {
													$bjr = "";
												}
												
												//	Führe Upload nur aus, wenn Informationen in Ordnung
												if(
													$counter_skipped_sid == 0 AND
													$counter_skipped_vn1 == 0 AND
													$counter_skipped_nn1 == 0 AND
													$counter_skipped_vn2 == 0 AND
													$counter_skipped_nn2 == 0 AND
													$counter_skipped_cls == 0 AND
													$counter_skipped_typ == 0 AND
													$counter_skipped_bjr == 0
												) {
													//	Suche nach Startnummer
													$select_sid = "SELECT * FROM `_optio_tmembers` WHERE `eid` = '" . $eid . "' AND `sid` = '" . $sid . "'";
													$result_sid = mysqli_query($mysqli, $select_sid);
													$numrow_sid = mysqli_num_rows($result_sid);
													
													//	Startnummer existiert bereits
													if($numrow_sid > 0) {
														//	Prüfe auf Duplikate der Startnummer
														if($numrow_sid > 1) {
															//	Startnummer mehrfach vorhanden
															$getrow_sid = mysqli_fetch_assoc($result_sid);
															$duplicates[] = $getrow_sid['sid'];
														} elseif($numrow_sid == 1) {
															//	Prüfe auf Vorgehensweise bei bestehenden Daten
															//	Überschreibe alles (Zugangsdaten + personelle Informationen)
															if($override == "all") {
																//	Hole derzeige Informationen für späteren Abgleich
																$getrow_sid = mysqli_fetch_assoc($result_sid);
																
																//	Werte vor Änderung
																$pre_vn1 = $getrow_sid['vname_1'];
																$pre_nn1 = $getrow_sid['nname_1'];
																$pre_vn2 = $getrow_sid['vname_2'];
																$pre_nn2 = $getrow_sid['nname_2'];
																$pre_cls = $getrow_sid['class'];
																$pre_fab = $getrow_sid['fabrikat'];
																$pre_typ = $getrow_sid['typ'];
																$pre_bjr = $getrow_sid['baujahr'];
																
																//	Hole derzeitige QR-Validation, um alten QR-Code aus Verzeichnis zu löschen
																$uqrcd_old = $getrow_sid['qr_validation'];
																
																//	Erstelle zufällige Teilnehmerkennung
																$uname = rand(100, 999) . rand(100, 999);
																
																//	Erstelle zufälliges Kennwort
																$upass = rand(18273645, 51486237);
																
																//	Erstelle QRID und prüfe, ob bereits vorhanden
																$uqrcd = UUID::v4();
																
																//	Prüfe auf Einmaligkeit
																while(in_array($uname, $uname_pool)) {
																	//	Erstelle zufällige Teilnehmerkennung
																	$uname = rand(100, 999) . rand(100, 999);
																}
																
																//	Prüfe auf Einmaligkeit
																while(in_array($upass, $upass_pool)) {
																	//	Erstelle zufällige Teilnehmerkennung
																	$upass = rand(100, 999) . rand(100, 999);
																}
																
																//	Prüfe auf Einmaligkeit
																while(in_array($uqrcd, $uqrcd_pool)) {
																	//	Erstelle zufällige QRUUID
																	$uqrcd = UUID::v4();
																}
																
																$tempDir = getcwd() . "/images/qr/";
			
																$codeContents = 'https://mindsources.net/msdn/qr_login.php?sso=' . $uqrcd;
																			
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
																QRcode::png($codeContents, $pngAbsoluteFilePath);
																
																$deleteFile = glob($tempDir . "*" . md5("https://mindsources.net/msdn/qr_login.php?sso=" . $uqrcd_old) . ".png");

																//	Lösche alten QR-Code aus Verzeichnis
																unlink($deleteFile[0]);
																			
																//	Vollständiges Überschreiben
																$update =	"
																			UPDATE
																				`_optio_tmembers`
																			SET
																				`class`			=	'" . $cls . "', 
																				`fabrikat`		=	'" . $fab . "', 
																				`typ`			=	'" . $typ . "', 
																				`baujahr`		=	'" . $bjr . "', 
																				`uname`			=	'" . $uname . "', 
																				`upass`			=	'" . $upass . "', 
																				`qr_validation`	=	'" . $uqrcd . "', 
																				`image_path`	=	'images/qr/" . $fileName . "', 
																				`vname_1`		=	'" . $vn1 . "', 
																				`nname_1`		=	'" . $nn1 . "', 
																				`vname_2`		=	'" . $vn2 . "', 
																				`nname_2`		=	'" . $nn2 . "',
																				`ready`			=	'1'
																			WHERE
																				`eid`	=	'" . $eid . "'
																			AND
																				`sid`	=	'" . $sid . "'
																			AND
																				`id`	=	'" . $getrow_sid['id'] . "'
																			";
																$result_update = mysqli_query($mysqli, $update);
																
																//	Datensatz erfolgreich überschrieben
																if(mysqli_affected_rows($mysqli) == 1) {
																	$counter_success_update++;
																//	Datensatz nicht geändert worden
																} else {
																	//	Prüfe auf Ursache (Fehler oder Werte identisch?)
																	if(
																		$pre_vn1 == $vn1 AND
																		$pre_nn1 == $nn1 AND
																		$pre_vn2 == $vn2 AND
																		$pre_nn2 == $nn2 AND
																		$pre_cls == $cls AND
																		$pre_fab == $fab AND
																		$pre_typ == $typ AND
																		$pre_bjr == $bjr
																	) {
																		//	Werte identisch
																		$counter_success_update++;
																	}
																}
															//	Überschreibe nur personelle Informationen
															} elseif($override == "whois") {
																//	Hole derzeige Informationen für späteren Abgleich
																$getrow_sid = mysqli_fetch_assoc($result_sid);
																
																//	Werte vor Änderung
																$pre_vn1 = $getrow_sid['vname_1'];
																$pre_nn1 = $getrow_sid['nname_1'];
																$pre_vn2 = $getrow_sid['vname_2'];
																$pre_nn2 = $getrow_sid['nname_2'];
																$pre_cls = $getrow_sid['class'];
																$pre_fab = $getrow_sid['fabrikat'];
																$pre_typ = $getrow_sid['typ'];
																$pre_bjr = $getrow_sid['baujahr'];
																
																//	Überschreiben personeller Daten
																$update =	"
																			UPDATE
																				`_optio_tmembers`
																			SET
																				`class`			=	'" . $cls . "', 
																				`fabrikat`		=	'" . $fab . "', 
																				`typ`			=	'" . $typ . "', 
																				`baujahr`		=	'" . $bjr . "',
																				`vname_1`		=	'" . $vn1 . "', 
																				`nname_1`		=	'" . $nn1 . "', 
																				`vname_2`		=	'" . $vn2 . "', 
																				`nname_2`		=	'" . $nn2 . "'
																			WHERE
																				`eid`	=	'" . $eid . "'
																			AND
																				`sid`	=	'" . $sid . "'
																			AND
																				`id`	=	'" . $getrow_sid['id'] . "'
																			";
																$result_update = mysqli_query($mysqli, $update);
																
																//	Datensatz erfolgreich überschrieben
																if(mysqli_affected_rows($mysqli) == 1) {
																	$counter_success_update++;
																//	Datensatz nicht geändert worden
																} else {
																	//	Prüfe auf Ursache (Fehler oder Werte identisch?)
																	if(
																		$pre_vn1 == $vn1 AND
																		$pre_nn1 == $nn1 AND
																		$pre_vn2 == $vn2 AND
																		$pre_nn2 == $nn2 AND
																		$pre_cls == $cls AND
																		$pre_fab == $fab AND
																		$pre_typ == $typ AND
																		$pre_bjr == $bjr
																	) {
																		//	Werte identisch
																		$counter_success_update++;
																	}
																}
															//	Überspringe diesen Datensatz
															} elseif($override == "skip") {
																continue;
															}
														}
													//	Startnummer existiert nicht
													} else {
														//	Erstelle zufällige Teilnehmerkennung
														$uname = rand(100, 999) . rand(100, 999);
														
														//	Erstelle zufälliges Kennwort
														$upass = rand(18273645, 51486237);
														
														//	Erstelle QRID und prüfe, ob bereits vorhanden
														$uqrcd = UUID::v4();
														
														//	Prüfe auf Einmaligkeit
														while(in_array($uname, $uname_pool)) {
															//	Erstelle zufällige Teilnehmerkennung
															$uname = rand(100, 999) . rand(100, 999);
														}
														
														//	Prüfe auf Einmaligkeit
														while(in_array($upass, $upass_pool)) {
															//	Erstelle zufällige Teilnehmerkennung
															$upass = rand(100, 999) . rand(100, 999);
														}
														
														//	Prüfe auf Einmaligkeit
														while(in_array($uqrcd, $uqrcd_pool)) {
															//	Erstelle zufällige QRUUID
															$uqrcd = UUID::v4();
														}
														
														$tempDir = getcwd() . "/images/qr/";
	
														$codeContents = 'https://mindsources.net/msdn/qr_login.php?sso=' . $uqrcd;
																	
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
														QRcode::png($codeContents, $pngAbsoluteFilePath);

														//	Vollständiges Überschreiben
														$insert =	"
																	INSERT INTO
																		`_optio_tmembers`(
																			id, 
																			eid, 
																			sid, 
																			class, 
																			fabrikat, 
																			typ, 
																			baujahr, 
																			uname, 
																			upass, 
																			qr_validation, 
																			image_path, 
																			vname_1, 
																			nname_1, 
																			vname_2, 
																			nname_2,
																			ready
																		)
																	VALUES(
																		NULL,
																		'" . $eid . "',
																		'" . $sid . "',
																		'" . $cls . "', 
																		'" . $fab . "', 
																		'" . $typ . "',
																		'" . $bjr . "', 
																		'" . $uname . "', 
																		'" . $upass . "', 
																		'" . $uqrcd . "', 
																		'images/qr/" . $fileName . "', 
																		'" . $vn1 . "', 
																		'" . $nn1 . "', 
																		'" . $vn2 . "', 
																		'" . $nn2 . "',
																		'1'
																		)
																	";
														$result_insert = mysqli_query($mysqli, $insert);
														
														//	Datensatz erfolgreich angelegt
														if(mysqli_affected_rows($mysqli) == 1) {
															$counter_success_insert++;
														}
													}
													
													//	Marker: Datensatz übersprungen
													$skipped = 0;
												//	Mindestens ein schadhafter Datensatz gefunden
												} else {
													//	Erhöhe Zähler für schadhafte Informationen
													$counter_skipped++;
													
													//	Marker: Datensatz übersprungen
													$skipped = 1;
												}
											}
											
											//	Aktualisiere Master-Tabelle für Anzahl der Teilnehmer
											$select = "SELECT `eid` FROM `_optio_tmembers` WHERE `eid` = '" . $eid . "'";
											$result = mysqli_query($mysqli, $select);
											$numrow = mysqli_num_rows($result);
											
											// PREPARE UPDATE NEW COUNT TMEMBERS
											$update = "UPDATE `_race_run_events` SET `count_tmembers` = '" . $numrow . "' WHERE `eid` = '" . $eid . "' AND `active` = '1'";
											mysqli_query($mysqli, $update);
											
											//	Setze Wert für Dummy Upload auf sichtbar
											$_SESSION['trigger_active_dummy'] = "";
											$_SESSION['trigger_active_single'] = "";
											$_SESSION['trigger_active_upload'] = "trigger_active";
											
											// RE-DEFINE UPLOAD TABLE / SAVE AND GET BACK
											$upload =	'
														<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF;">
															<tr>
																<td colspan="2" align="center" class="hint">&nbsp;</td>
															</tr>
														';
															/*<tr>
																<td colspan="2" align="center" class="hint">Ihre Datei wurde hochgeladen</td>
															</tr>
															<tr>
																<td colspan="2" align="center" class="hint"><hr class="white-hr"></td>
															</tr>
															<tr>
																<td colspan="2" align="center" class="hint">&nbsp;</td>
															</tr>
															<tr>
																<td align="left"><input type="button" value="Dateiname: '.$_FILES["file"]["name"].'" disabled="disabled" style="background: transparent; background-color: #A09A8E; color: #8E6516; width: 185px;" /></td>
																<td align="right"><input type="button" value="Dateigröße: '.($_FILES["file"]["size"] / 1024).'KB" disabled="disabled" style="background: transparent; background-color: #A09A8E; color: #8E6516; width: 185px;" /></td>
															</tr>*/
											$upload .=	'
															<tr>
																<td colspan="2" align="center" class="hint">Ihre Teamübersicht</td>
															</tr>
															<tr>
																<td colspan="2" align="center" class="hint">&nbsp;</td>
															</tr>
														</table>
														';
											$info_getback =	'
															<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF;">
																<tr>
																	<td align="left"><input type="button" value="<<" onclick="location.href=\'my_event.php\';" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;"/></td>
																</tr>
															</table>
															';
											
											//	Erstelle HTML Aufstellung aller hochgeladenen Datensätze
											if($i == 0) {
												$html =	'
														<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF;">
															<tr>
																<td align="left" width="auto">
																	<font color="#FFFFFF" size="1">
																		<strong>#</strong>
																	</font>
																</td>
																<th align="left" width="auto">
																	<font color="#FFFFFF" size="1">
																		Fahrer
																	</font>
																</th>
																<th align="left" width="auto">
																	<font color="#FFFFFF" size="1">
																		Beifahrer
																	</font>
																</th>
																<th align="center" width="auto">
																	<font color="#FFFFFF" size="1">
																		Klasse
																	</font>
																</th>
																<th align="left" width="auto">
																	<font color="#FFFFFF" size="1">
																		Fabrikat
																	</font>
																</th>
																<th align="left" width="auto">
																	<font color="#FFFFFF" size="1">
																		Typ
																	</font>
																</th>
																<th align="left" width="auto">
																	<font color="#FFFFFF" size="1">
																		Baujahr
																	</font>
																</th>
																<th align="center" class="hint" width="auto">
																	<font color="#FFFFFF" size="1">
																		Fehler
																		<div class="tooltip">
																			<font color="#FF0000">?</font>
																			<span class="tooltiptext">
																				Zeigt aufgrund von schafhaftem Inhalt übersprungene Datensätze an.
																			</span>
																		</div>
																	</font>
																</th>
															</tr>
															<tr>
																<th colspan="8">
																	<hr class="white-hr" />
																</th>
															</tr>
														';
											}
											
											if($skipped == 1) {
												$image = "cross.png";
											} else {
												$image = "tick.png";
											}
											
											$html .=	'
															<tr>
																<td align="left" width="auto">
																	<font color="#FFFFFF" size="1">
																		' . $sid . '
																	</font>
																</td>
																<th align="left" width="auto">
																	<font color="#FFFFFF" size="1">
																		' . $vn1 . ' ' . $nn1 . '
																	</font>
																</th>
																<th align="left" width="auto">
																	<font color="#FFFFFF" size="1">
																		' . $vn2 . ' ' . $nn2 . '
																	</font>
																</th>
																<th align="center" width="auto">
																	<font color="#FFFFFF" size="1">
																		' . $cls . '
																	</font>
																</th>
																<th align="left" width="auto">
																	<font color="#FFFFFF" size="1">
																		' . $fab . '
																	</font>
																</th>
																<th align="left" width="auto">
																	<font color="#FFFFFF" size="1">
																		' . $typ . '
																	</font>
																</th>
																<th align="left" width="auto">
																	<font color="#FFFFFF" size="1">
																		' . $bjr . '
																	</font>
																</th>
																<th align="center" class="hint" width="auto">
																	<img src="images/' . $image . '"></img>
																</th>
															</tr>
													';
											
											if($i == (count($sheetData) - 1)) {
												$html .=	'
															<tr>
																<th colspan="8">
																	&nbsp;
																</th>
															</tr>
														</table>
														<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF;">
															<tr>
																<th colspan="2">
																	Status
																</th>
															</tr>
															<tr>
																<td>
																	Teilnehmerzahl:
																</td>
																<td>
																	' . count($sheetData) . '
																</td>
															</tr>
															<tr>
																<td>
																	-- davon gespeichert:
																</td>
																<td>
																	' . $counter_success_insert . '
																</td>
															</tr>
															<tr>
																<td>
																	-- davon aktualisiert:
																</td>
																<td>
																	' . $counter_success_update . '
																</td>
															</tr>
															<tr>
																<td>
																	-- davon übersprungen <span style="color: #FFD700">&#9888;</span>:
																</td>
																<td>
																	' . $counter_skipped . '
																</td>
															</tr>
														';
														
													//	Fehler: konnte nicht hochgeladen werden
													if((count($sheetData) - $counter_skipped - $counter_success_insert - $counter_success_update) > 0) {
														$html .=	'
															<tr>
																<td>
																	-- davon fehlerhaft <span style="color: #FF0000">&#9888;</span>:
																</td>
																<td>
																	' . (count($sheetData) - $counter_skipped - $counter_success_insert - $counter_success_update) . '
																</td>
															</tr>
																';
													}
												
												$html .=	'</table>';
											}
											
											//	Wurden doppelte Startnummern gefunden, gebe diese aus
											if(count($duplicates) > 0) {
												$list = implode(", ", $duplicates);
												
												$state      = 'Fehler:';								
												$error_msg = '<br /><span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Folgende Startnummern sind doppelt vorhanden: ' . $list . '</span><br />';
											}
										}
									//	Kein Reader gefunden
									} else {
										$state      = 'Fehler:';								
										$error_msg = '<br /><span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Format unbekannt!</span><br />';
									}
								//	Datei-Typ nicht gestattet
								} else {
									$state      = 'Fehler:';								
									$error_msg = '<br /><span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Format nicht erlaubt!</span><br />';
								}
							//	Übergabeparameter für Überschreiben fehlerhaft / nicht gesetzt
							} else {
								$state      = 'Fehler:';								
								$error_msg = '<br /><span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Überschreibungsmodus nicht gesetzt!</span><br />';
							}
						}
						
						// SHOW EDIT DRIVER INFORMATION
						$event_handler	=	'
											<form method="post" action="' . $_SERVER["PHP_SELF"] . '" name="dummy" id="dummy" accept-charset="UTF-8">
											<div class="trigger ' . $_SESSION['trigger_active_dummy'] . '">
											<table width="385px" cellspacing="5px" style="border: 1px solid #FFFFFF;">
												<tr>
													<th align="left">Dummy-Teilnehmer hochladen</th>
													<th align="right"><font size="1">200 pro Upload</font></th>
												</tr>
											</table>
											</div>
											<div class="toggle_container">										
											<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF;">
												<tr>
													<td colspan="2" align="center" class="hint"><font size="1">Ausschließlich Zugangsdaten für einzelne Startnummern zu generieren. </font><br /><br /></td>
												</tr>
												<tr>
													<td colspan="2" align="center">
														<div id="dialog_mt_pic_dummy" class="modal_fix" title="Tutorial: Eine Dummy-Teilnehmerliste erstellen" style="color: #8E6516; background: transparent; background-color: #A09A8E;">
															<p align="justify" style="color: white;">
																Sie haben die Möglichkeit, eine Teilnehmerliste zu erstellen, welche lediglich Startnummern und zugehörige Zugangsdaten enthält. So ist es Ihnen möglich, zu einem späteren Zeitpunkt
																Ihre Teilnehmer relevanten Daten zu komplettieren.
																<br />
																<br />
																<strong><u>Beachten Sie allerdings folgendes:</u></strong>
																<br />
																<br />
																<ul style="color: white;">
																	<li style="color: red;">&#9642;&emsp;<strong>die spätere Komplettierung der Teilnehmerdaten orientiert sich an der Startnummer und
																	<br />
																	&emsp; obliegt Ihrer Verantwortung! Achten Sie auf die korrekte Zuweisung der Zugangsdaten!</strong></li>
																	<li>&#9642;&emsp;das Hochladen von Teilnehmern aktualisiert bestehende Fahrerdaten, <strong>nicht jedoch Zugangsdaten</strong></li>
																	<li>&#9642;&emsp;Bestehende Daten werden: hierdurch <strong>nicht</strong> überschrieben</li>
																	<li>&#9642;&emsp;das Generieren neuer Dummy-Teilnehmer ist fortlaufend</li>
																	<li>&#9642;&emsp;das zulässige Maximum von 200 Teilnehmern kann <strong>nicht</strong> überschritten werden</li>
																	<li>&#9642;&emsp;es ist Ihnen möglich, einen Puffer zu erstellen und ggf. weitere Teilnehmer aufzufüllen.
																	<br />
																	&emsp; <u>Soll heißen: Sie benötigen lediglich 20 Dummy-Teilnehmer, erstellen jedoch 30</u></li>
																</ul>
																<table width="100%" cellspacing="5px" style="border: 0px;">
																	<tr>
																		<td align="center">&nbsp;</td>
																	</tr>
																</table>
															</p>
															<p>
																<table width="100%" cellspacing="5px" style="border: 1px solid #FFFFFF;">
																	<tr>
																		<td align="center">Ende des Tutorials. Sie können das Fenster rechts oben über <img src="images/close_modal.png"></img> schließen!</td>
																	</tr>
																</table>
															</p>
														</div>
														<a href="#" id="opener_mt_pic_dummy" style="color: #FFFFFF;"><strong><u>Hier klicken, um alle Einzelheiten zu erfahren!</u></strong></a>
													</td>
												</tr>
												<tr>
													<td colspan="2" align="center">&nbsp;</td>
												</tr>
												<tr>
													<td width="80%" align="left"><input type="range" id="dummy_slider" name="dummy_slider" min="0" max="' . $differ_maximum . '" value="0" step="1" style="background: transparent; color: #8E6516; width: 100%" required="required" /></td>
													<td width="20%" align="left"><label id="rangeText" style="padding-left: 5px;" /></td>
												</tr>												
												<tr>
													<td colspan="2" align="center">&nbsp;</td>
												</tr>
											</table>
											<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF;">
												<tr>
													<th colspan="2">Eingaben speichern</th>
												</tr>
												<tr>
													<th colspan="2"><hr /></th>
												</tr>
												<tr>
													<td align="left"><input type="button" value="<<" onclick="location.href=\'my_event.php\';" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;"/></td>
													<td align="right"><input type="submit" name="dummy" id="dummy" form="dummy" value="Speichern" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" /></td>
												</tr>
												<tr>
													<td colspan="2" align="center">&nbsp;</td>
												</tr>
												<tr>
													<th colspan="2"><font size="2" color="' . $status . '">Sie können noch maximal ' . $differ_maximum . ' Teilnehmer hochladen!</font></th>
												</tr>
											</table>
											</div>
											</form>
											<table width="385px" cellspacing="5px">
												<tr>
													<th colspan="2">&nbsp;</th>
												</tr>
											</table>
											<form action="" method="post" name="single_mt" id="single_mt" action="' . $_SERVER["PHP_SELF"] . '" accept-charset="UTF-8">
											<div class="trigger ' . $_SESSION['trigger_active_single'] . '">
											<table width="385px" cellspacing="5px" style="border: 1px solid #FFFFFF;">
												<tr>
													<th align="left">Teams einzeln hochladen</th>
													<th align="right"><font size="1">5 pro Upload</font></th>
												</tr>
											</table>
											</div>
											<div class="toggle_container">
											<div class="input_fields_wrap">
											<a href="#" class="add_field_button">
											<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;">
												<tr>
													<th align="left">Teams hinzufügen</th>
													<th align="right"><font color="#FFD700">[+]</font></th>
												</tr>
												<tr>
													<th colspan="2"><hr /></th>
												</tr>
											</table>
											</a>
											<div>
											<table id="addmt" width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;">
												<tr>
													<td align="left">Zuordnung<font color="#8E6516">*</font></td>
													<td align="right"><input name="mt_id[]" type="text" style="width: 66.5px; margin-right: 2px;" placeholder="#" required = "required" /><input name="mt_id[]" type="text" style="width: 66.5px;" placeholder="Klasse" required = "required" /></td>
												</tr>
												<tr>
													<td colspan="2">&nbsp;</td>
												</tr>
												<tr>
													<td align="left">Fahrzeug<font color="#8E6516">*</font></td>
													<td align="right"><input name="mt_id[]" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="Fabrikat" required = "required" /></td>
												</tr>
												<tr>
													<td align="left">&nbsp;</td>
													<td align="right"><input name="mt_id[]" type="text" style="width: 66.5px; margin-right: 2px;" placeholder="Typ" required = "required" /><input name="mt_id[]" type="text" style="width: 66.5px;" placeholder="Baujahr" required = "required" /></td>
												</tr>
												<tr>
													<td colspan="2">&nbsp;</td>
												</tr>
												<tr>
													<td align="left">Fahrer/-in<font color="#8E6516">*</font></td>
													<td align="right"><input name="mt_id[]" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="Vorname" required = "required" /></td>
												</tr>
												<tr>
													<td align="left">&nbsp;</td>
													<td align="right"><input name="mt_id[]" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="Nachname" required = "required" /></td>
												</tr>
												<tr>
													<td colspan="2">&nbsp;</td>
												</tr>
												<tr>
													<td align="left">Beifahrer/-in<font color="#8E6516">*</font></td>
													<td align="right"><input name="mt_id[]" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="Vorname" required = "required" /></td>
												</tr>
												<tr>
													<td align="left"&nbsp;</td>
													<td align="right"><input name="mt_id[]" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="Nachname" required = "required" /></td>
												</tr>
											</table>
											</div>
											</div>
											<table width="385px" cellspacing="5px" style="border: 1px solid #FFFFFF;">
												<tr>
													<th colspan="2">Eingaben speichern</th>
												</tr>
												<tr>
													<th colspan="2"><hr /></th>
												</tr>
												<tr>
													<td align="left"><input type="button" value="<<" onclick="location.href=\'my_event.php\';" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;"/></td>
													<td align="right"><input type="submit" name="t_save" value="Speichern" form="single_mt" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" /></td>
												</tr>
												<tr>
													<td colspan="2" align="center">&nbsp;</td>
												</tr>
												<tr>
													<th colspan="2"><font size="2" color="' . $status . '">Sie können noch maximal ' . $differ_maximum . ' Teilnehmer hochladen!</font></th>
												</tr>
											</table>
											</div>
											</form>
											<table width="385px" cellspacing="5px">
												<tr>
													<th colspan="2">&nbsp;</th>
												</tr>
											</table>
											<form enctype="multipart/form-data" method="post" action="' . $_SERVER["PHP_SELF"] . '" accept-charset="UTF-8">
											<div class="trigger ' . $_SESSION['trigger_active_upload'] . '">
											<table width="385px" cellspacing="5px" style="border: 1px solid #FFFFFF;">
												<tr>
													<th align="left">Teamliste hochladen</th>
													<th align="right"><font size="1">200 pro Upload</font></th>
												</tr>
											</table>
											</div>
											<div class="toggle_container">										
											' . $upload . '
											' . $html . '
											' . $info_getback . '
											</div>
											</form>
											<table width="385px" cellspacing="5px">
												<tr>
													<th colspan="2">&nbsp;</th>
												</tr>
											</table>
											';
											
						// SAVE BUTTON CLICKED
						if(isset($_POST['t_save'])) {
							//	Binde UUID ein
							require_once 'classes/uuid/uuid.php';
							
							//	Binde QR Code Generierung ein
							require_once 'classes/phpqrcode/qrlib.php';
							
							// UNSET SEARCH
							unset($_POST['t_save']);
							
							/*
							echo "Anzahl POST: " . count($_POST['mt_id']); 
							echo "<br /><br />";
							echo "<pre>";
							print_r($_POST['mt_id']);
							echo "</pre>";
							exit;
							*/
							
							// LOOP THROUGH POST
							for($i = 0; $i < count($_POST['mt_id']); $i++) {
								// This loop is created to get data in a table format
								$eid   = mysqli_real_escape_string($mysqli, $eid);
								$sid   = mysqli_real_escape_string($mysqli, $_POST['mt_id'][$i]);
								$i++;
								$class   = mysqli_real_escape_string($mysqli, $_POST['mt_id'][$i]);
								$i++;
								$fabrikat   = mysqli_real_escape_string($mysqli, $_POST['mt_id'][$i]);
								$i++;
								$typ   = mysqli_real_escape_string($mysqli, $_POST['mt_id'][$i]);
								$i++;
								$baujahr   = mysqli_real_escape_string($mysqli, $_POST['mt_id'][$i]);
								$i++;
								$vname_1    = mysqli_real_escape_string($mysqli, $_POST['mt_id'][$i]);
								$i++;
								$nname_1    = mysqli_real_escape_string($mysqli, $_POST['mt_id'][$i]);
								$i++;
								$vname_2    = mysqli_real_escape_string($mysqli, $_POST['mt_id'][$i]);
								$i++;
								$nname_2    = mysqli_real_escape_string($mysqli, $_POST['mt_id'][$i]);
								
								//	Suche nach Startnummer
								$select_sid = "SELECT `sid`, `eid` FROM `_optio_tmembers` WHERE `eid` = '" . $eid . "' AND `sid` = '" . $sid . "'";
								$result_sid = mysqli_query($mysqli, $select_sid);
								$numrow_sid = mysqli_num_rows($result_sid);
								
								//	Prüfvariable für Update oder Insert
								$query_type = "";
								
								//	Startnummer existiert bereits
								if($numrow_sid > 0) {
									//	Prüfe auf Duplikate der Startnummer
									if($numrow_sid > 1) {
										//	Startnummer mehrfach vorhanden
										$error_msg = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Startnummer ' . $sid . ' mehrfach vorhanden! Bitte löschen!</span><br />';
									} elseif($numrow_sid == 1) {
										//	Prüfvariable für Update oder Insert
										$query_type = "Update";
										
										//	Aktualisieren der Fahrerdaten, Startnummer und Zugangsdaten jedoch beibehalten
										$update =	"
													UPDATE
														`_optio_tmembers`
													SET
														`class`		=	'" . $class . "', 
														`fabrikat`	=	'" . $fabrikat . "', 
														`typ`		=	'" . $typ . "', 
														`baujahr`	=	'" . $baujahr . "', 
														`vname_1`	=	'" . $vname_1 . "', 
														`nname_1`	=	'" . $nname_1 . "', 
														`vname_2`	=	'" . $vname_2 . "', 
														`nname_2`	=	'" . $nname_2 . "'
													WHERE
														`eid`	=	'" . $eid . "'
													AND
														`sid`	=	'" . $sid . "'
													";
										$result_update_sid = mysqli_query($mysqli, $update);
										
										if(mysqli_affected_rows($mysqli) > 0) {
											$success_update++;
										}
									}
								//	Startnummer existiert nicht
								} elseif($numrow_sid == 0) {
									//	Prüfvariable für Update oder Insert
									$query_type = "Insert";
									
									// CREATE USERNAME
									$uname = rand(100, 999) . rand(100, 999);
											
									// CREATE PASSWORD
									$upass = rand(18273645, 51486237);
									
									// SEARCH FOR USERS WITH GENERATED CREDENTIALS TO AVOID DUPLICATES
									$select_dupe = "SELECT `uname`, `upass` FROM `_optio_tmembers` WHERE `uname` = '" . $uname . "' AND `upass` = '" . $upass . "'";
									$result_dupe = mysqli_query($mysqli, $select_dupe);
									$numrow_dupe = mysqli_num_rows($result_dupe);
									
									// CHECK WHETHER USER ALREADY EXISTS OR NOT
									if($numrow_dupe > 0) {
										// IF SO, THEN GENERATE NEW CREDENTIALS
										// CREATE USERNAME
										$uname = rand(100, 999) . rand(100, 999);
												
										// CREATE PASSWORD
										$upass = rand(18273645, 51486237);
									}
									
									//	Erstelle QRID und prüfe, ob bereits vorhanden
									$uqrcd = UUID::v4();
									
									$select_qr = "SELECT `qr_validation` FROM `_optio_tmembers` WHERE `qr_validation` = '" . $uqrcd . "'";
									$result_qr = mysqli_query($mysqli, $select_qr);
									$numrow_qr = mysqli_num_rows($result_qr);
									
									//	Wenn QRID bereits vorhanden ist (das geht? O_o), durchlaufe Schleife bis neue, einzigartige erstellt wurde
									if($numrow_qr > 0) {
										$getrow_qr = mysqli_fetch_assoc($result);
										
										while($uqrcd == $getrow_qr['qr_validation']) {
											//	Erstelle QRID und prüfe, ob bereits vorhanden
											$uqrcd = UUID::v4();
											
											$select_qr_loop = "SELECT `qr_validation` FROM `_optio_tmembers` WHERE `qr_validation` = '" . $uqrcd . "'";
											$result_qr_loop = mysqli_query($mysqli, $select_qr_loop);
											$numrow_qr_loop = mysqli_num_rows($result_qr_loop);
											
											if($numrow_qr_loop == 0) {
												break;
												
											}														
										}
									}
									
									$tempDir = getcwd() . "/images/qr/";

									$codeContents = 'https://mindsources.net/msdn/qr_login.php?sso=' . $uqrcd;
												
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
									QRcode::png($codeContents, $pngAbsoluteFilePath);
										
									$query	= 	"INSERT INTO
													_optio_tmembers(
														id, 
														eid, 
														sid, 
														class, 
														fabrikat, 
														typ, 
														baujahr, 
														uname, 
														upass, 
														qr_validation, 
														image_path, 
														vname_1, 
														nname_1, 
														vname_2, 
														nname_2,
														ready
													)
												VALUES(
													NULL,
													'" . @$eid . "',
													'" . @$sid . "',
													'" . @$class . "', 
													'" . @$fabrikat . "', 
													'" . @$typ . "',
													'" . @$baujahr . "', 
													'" . $uname . "', 
													'" . $upass . "', 
													'" . $uqrcd . "', 
													'images/qr/" . $fileName . "', 
													'" . @$vname_1 . "', 
													'" . @$nname_1 . "', 
													'" . @$vname_2 . "', 
													'" . @$nname_2 . "',
													'1'
													)";
									mysqli_query($mysqli, $query);
									
									if(mysqli_affected_rows($mysqli) > 0) {
										$success_insert++;
									}
								}													
							}
							
							if($query_type == "Update") {
								if($success_update == (count($_POST['mt_id']) / 9)) {
									$state      = 'Erfolgreich!';
									$error_msg = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Teilnehmerdaten aktualisiert!</span><br />';
								} elseif($success_update != (count($_POST['mt_id']) / 9)) {
									if($success_update == 0) {
										$state      = 'Fehler:';
										$error_msg = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Teilnehmerdaten bereits aktualisiert!</span><br />';
									} elseif($success_update > 0) {
										$state      = 'Fehler:';
										$error_msg = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Teilnehmerdaten unvollständig aktualisiert!</span><br />';
									}
								} elseif($success_update != (count($_POST['mt_id']) / 9) AND $success_update == 0) {
									$state      = 'Fehler:';
									$error_msg = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Teilnehmerdaten konnten nicht aktualisiert werden!</span><br />';
								}
							} elseif($query_type == "Insert") {
								if($success_insert == (count($_POST['mt_id']) / 9)) {
									$state      = 'Erfolgreich!';
									$error_msg = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Teilnehmerdaten angelegt!</span><br />';
								} elseif($success_insert != (count($_POST['mt_id']) / 9)) {
									if($success_insert == 0) {
										$state      = 'Fehler:';
										$error_msg = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Teilnehmerdaten bereits angelegt!</span><br />';
									} elseif($success_insert > 0) {
										$state      = 'Fehler:';
										$error_msg = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Teilnehmerdaten unvollständig angelegt!</span><br />';
									}
								} elseif($success_insert != (count($_POST['mt_id']) / 9) AND $success_insert == 0) {
									$state      = 'Fehler:';
									$error_msg = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Teilnehmerdaten konnten nicht angelegt werden!</span><br />';
								}
							}
							
							// COUNT ROWS AND UPDATE _RACE_RUN_EVENTS
							// SEARCH TMEMBERS AND COUNT ROWS THEN UPDATE COUNT OF TMEMBERS FOR EVENT
							$select = "SELECT id, eid FROM _optio_tmembers WHERE `eid` = '" . $eid . "'";
							$result = mysqli_query($mysqli, $select);
							$numrow = mysqli_num_rows($result);
											
							// PREPARE UPDATE NEW COUNT TMEMBERS
							$update = "UPDATE _race_run_events SET `count_tmembers` = '" . $numrow . "' WHERE `eid` = '" . $eid . "' AND `active` = '1'";
							mysqli_query($mysqli, $update);
							
							//	Setze Wert für Dummy Upload auf sichtbar
							$_SESSION['trigger_active_dummy'] = "";
							$_SESSION['trigger_active_single'] = "trigger_active";
							$_SESSION['trigger_active_upload'] = "";
						}
						
						//	Dummy Formular
						if(isset($_POST["dummy"])) {
							//	Binde UUID ein
							require_once 'classes/uuid/uuid.php';
							
							//	Binde QR Code Generierung ein
							require_once 'classes/phpqrcode/qrlib.php';
							
							//	Zählervariable für erfolgreiches Anlegen pro Durchlauf
							$success_dummy = 0;
							
							//	Hole Anzahl der zu erstellenden Dummy-Teilnehmer
							$dummy_amount = mysqli_real_escape_string($mysqli, $_POST["dummy_slider"]);
							
							//	Prüfe, ob zwischenzeitlich eine Änderung des max. Teilnehmerstandes erfolgt ist
							$select_max_tmember = "SELECT `eid`, `count_tmembers` FROM `_race_run_events` WHERE `eid` = '" . $eid . "'";
							$result_max_tmember = mysqli_query($mysqli, $select_max_tmember);
							$getrow_max_tmember = mysqli_fetch_assoc($result_max_tmember);
							
							$primary_tmember_count = $getrow_max_tmember['count_tmembers'];
							
							//	Hole originale Teilnehmeranzahl (falls fehlerhaft --> _race_run_event wird ggf. aktualisiert)
							$select_max_tmembers = "SELECT `eid` FROM `_optio_tmembers` WHERE `eid` = '" . $eid . "'";
							$result_max_tmembers = mysqli_query($mysqli, $select_max_tmembers);
							$numrow_max_tmembers = mysqli_num_rows($result_max_tmembers);
							
							$secondary_tmember_count = $numrow_max_tmembers;
							
							//	Wenn beide Werte voneinander abweichen, aktualisiere _race_run_events
							if($primary_tmember_count != $secondary_tmember_count) {
								$update_tmember_count =	"
														UPDATE
															`_race_run_events`
														SET
															`count_tmembers` = '" . $secondary_tmember_count . "'
														WHERE
															`eid` = '" . $eid . "'
														AND
															`active` = '1'
														";
								$result_update_tmember_count = mysqli_query($mysqli, $update_tmember_count);
								
								if(mysql_affected_rows($mysqli) == 0) {
									$error_msg  = '<span style="margin-left: 5px; font-size: small;"><font color="#red">&mdash;&emsp;</font>Teilnehmeranzahl wurde nicht aktualisiert!</span><br />';
								}
							}
							
							//	Prüfe, ob zulässige Grenze nicht überschritten wurde
							if($dummy_amount <= (200 - $primary_tmember_count)) {
								//	Suche nach höchster Startnummer
								$select_highest_startnumber = "SELECT `eid`, MAX(`sid`) AS `highest_sid` FROM `_optio_tmembers` WHERE `eid` = '" . $eid . "' LIMIT 1";
								$result_highest_startnumber = mysqli_query($mysqli, $select_highest_startnumber);
								$getrow_highest_startnumber = mysqli_fetch_assoc($result_highest_startnumber);
								
								//	Lege für die festgelegte Anzahl Dummy-Teilnehmer an
								for($i = $getrow_highest_startnumber['highest_sid']; $i < ($getrow_highest_startnumber['highest_sid'] + $dummy_amount); $i++) {
									//	Lege nächste freie Startnummer fest
									$sid = $i + 1;
									
									// CREATE USERNAME
									$uname = rand(100, 999) . rand(100, 999);
											
									// CREATE PASSWORD
									$upass = rand(18273645, 51486237);
									
									// SEARCH FOR USERS WITH GENERATED CREDENTIALS TO AVOID DUPLICATES
									$select_dupe = "SELECT `uname`, `upass` FROM `_optio_tmembers` WHERE `uname` = '" . $uname . "' AND `upass` = '" . $upass . "'";
									$result_dupe = mysqli_query($mysqli, $select_dupe);
									$numrow_dupe = mysqli_num_rows($result_dupe);
									
									// CHECK WHETHER USER ALREADY EXISTS OR NOT
									if($numrow_dupe > 0) {
										// IF SO, THEN GENERATE NEW CREDENTIALS
										// CREATE USERNAME
										$uname = rand(100, 999) . rand(100, 999);
												
										// CREATE PASSWORD
										$upass = rand(18273645, 51486237);
									}
									
									//	Erstelle QRID und prüfe, ob bereits vorhanden
									$uqrcd = UUID::v4();
									
									$select_qr = "SELECT `qr_validation` FROM `_optio_tmembers` WHERE `qr_validation` = '" . $uqrcd . "'";
									$result_qr = mysqli_query($mysqli, $select_qr);
									$numrow_qr = mysqli_num_rows($result_qr);
									
									//	Wenn QRID bereits vorhanden ist (das geht? O_o), durchlaufe Schleife bis neue, einzigartige erstellt wurde
									if($numrow_qr > 0) {
										$getrow_qr = mysqli_fetch_assoc($result);
										
										while($uqrcd == $getrow_qr['qr_validation']) {
											//	Erstelle QRID und prüfe, ob bereits vorhanden
											$uqrcd = UUID::v4();
											
											$select_qr_loop = "SELECT `qr_validation` FROM `_optio_tmembers` WHERE `qr_validation` = '" . $uqrcd . "'";
											$result_qr_loop = mysqli_query($mysqli, $select_qr_loop);
											$numrow_qr_loop = mysqli_num_rows($result_qr_loop);
											
											if($numrow_qr_loop == 0) {
												break;
												
											}														
										}
									}
									
									$tempDir = getcwd() . "/images/qr/";

									$codeContents = 'https://mindsources.net/msdn/qr_login.php?sso=' . $uqrcd;
												
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
									QRcode::png($codeContents, $pngAbsoluteFilePath);
									
									$query	= 	"INSERT INTO
													`_optio_tmembers`(
														`id`, 
														`eid`, 
														`sid`, 
														`uname`, 
														`upass`, 
														`qr_validation`, 
														`image_path`, 
														`ready`
													)
												VALUES(
													NULL,
													'" . @$eid . "',
													'" . @$sid . "',
													'" . $uname . "', 
													'" . $upass . "', 
													'" . $uqrcd . "', 
													'images/qr/" . $fileName . "', 
													'1'
													)";
							 
									mysqli_query($mysqli, $query);
									
									if(mysqli_affected_rows($mysqli) == 1) {
										$success_dummy++;
									}
								}
								
								// COUNT ROWS AND UPDATE _RACE_RUN_EVENTS
								// SEARCH TMEMBERS AND COUNT ROWS THEN UPDATE COUNT OF TMEMBERS FOR EVENT
								$select = "SELECT id, eid FROM _optio_tmembers WHERE `eid` = '" . $eid . "'";
								$result = mysqli_query($mysqli, $select);
								$numrow = mysqli_num_rows($result);
								
								// PREPARE UPDATE NEW COUNT TMEMBERS
								$update = "UPDATE _race_run_events SET `count_tmembers` = '" . $numrow . "' WHERE `eid` = '" . $eid . "' AND `active` = '1'";
								mysqli_query($mysqli, $update);
								
								//	Setze Wert für Dummy Upload auf sichtbar
								$_SESSION['trigger_active_dummy'] = "trigger_active";
								$_SESSION['trigger_active_single'] = "";
								$_SESSION['trigger_active_upload'] = "";
								
								if($dummy_amount == $success_dummy AND $dummy_amount > 0) {
									$state      = 'Erfolgreich!';
									$error_msg = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Dummy Teilnehmer wurden angelegt!</span><br />';
								} elseif($dummy_amount == $success_dummy AND $dummy_amount == 0) {
									$state      = 'Fehler:';
									$error_msg = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Es wurden keine Dummy Teilnehmer zum Anlegen gewählt!</span><br />';
								} elseif($dummy_amount != $success_dummy AND $dummy_amount > 0) {
									$state      = 'Fehler:';
									$error_msg = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Dummy Teilnehmer wurden unvollständig angelegt!</span><br />';
								}
							} else {
								$state      = 'Fehler:';
								$error_msg = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Sie haben das Maximum um ' . abs($primary_tmember_count - $dummy_amount) . ' Teilnehmer überschritten</span><br />';
							}
						}
					}
				// EVENT ON STATUS ACTIVE FOUND
				} else {
					// REDIRECT TO MY EVENT --> USER MUST EDIT FIRST
					header('Location: /msdn/my_event.php');
				}
			// NO EVENT FROM LOGGED IN USER FOUND
			} else {
				// REDIRECT TO MY EVENT --> USER MUST EDIT FIRST
				header('Location: /msdn/my_event.php');
			}
		}
	// NOT LOGGED IN --> HIDE CONTENT AND REDIRECT
	} else {
		header('Location: /msdn/index.php');
		@$_SESSION['user_id'] = "";
	}
	
	// CHECK FOR ERRORS
	if(isset($_GET['error'])) {
        echo "<p id=\"error\">Error Logging In!</p>";
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//DE" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" >
	<head>
		<!--	SET TITLE		-->
		<title>Z 3 : 1 T : 0 0 , 000</title>
		
		<!--	SET META		-->
		<meta name="description" content="TimeKeeper - Das Datenzentrum im Motorsport!">
		<meta name="author" content="Ultraviolent (www.mindsources.net)" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width">
		
		<!--	INCLUDING ICO	-->
		<link rel="shortcut icon" href="favicon.ico">
		<!--	INCLUDING LIB	-->
		<?php include("lib/library.html"); ?>
		
		<style>
			input[type=range] {
				-webkit-appearance: none; /* Hides the slider so that custom slider can be made */
				width: 100%; /* Specific width is required for Firefox. */
				background: transparent; /* Otherwise white in Chrome */
			}

			input[type=range]::-webkit-slider-thumb {
				-webkit-appearance: none;
			}

			input[type=range]:focus {
				outline: none; /* Removes the blue border. You should probably do some kind of focus styling for accessibility reasons though. */
			}

			input[type=range]::-ms-track {
				width: 100%;
				cursor: pointer;

				/* Hides the slider so custom styles can be added */
				background: transparent; 
				border-color: transparent;
				color: transparent;
			}
			
			/* Special styling for WebKit/Blink */
			input[type=range]::-webkit-slider-thumb {
				-webkit-appearance: none;
				border: 1px solid #000000;
				height: 36px;
				width: 16px;
				border-radius: 0px;
				background: #ffffff;
				cursor: pointer;
				margin-top: -14px; /* You need to specify a margin in Chrome, but in Firefox and IE it is automatic */
				box-shadow: 1px 1px 1px #000000, 0px 0px 1px #c0c0c0; /* Add cool effects to your sliders! */
			}

			/* All the same stuff for Firefox */
			input[type=range]::-moz-range-thumb {
				box-shadow: 1px 1px 1px #000000, 0px 0px 1px #c0c0c0;
				border: 1px solid #000000;
				height: 36px;
				width: 16px;
				border-radius: 0px;
				background: #ffffff;
				cursor: pointer;
			}

			/* All the same stuff for IE */
			input[type=range]::-ms-thumb {
				box-shadow: 1px 1px 1px #000000, 0px 0px 1px #c0c0c0;
				border: 1px solid #000000;
				height: 36px;
				width: 16px;
				border-radius: 0px;
				background: #ffffff;
				cursor: pointer;
			}
			
			input[type=range]::-webkit-slider-runnable-track {
				width: 100%;
				height: 8.4px;
				cursor: pointer;
				box-shadow: 1px 1px 1px #000000, 0px 0px 1px #c0c0c0;
				background: #8e6516;
				border-radius: 1.3px;
				border: 0.2px solid #010101;
			}

			input[type=range]:focus::-webkit-slider-runnable-track {
				background: #367ebd;
			}

			input[type=range]::-moz-range-track {
				width: 100%;
				height: 8.4px;
				cursor: pointer;
				box-shadow: 1px 1px 1px #000000, 0px 0px 1px #c0c0c0;
				background: #8e6516;
				border-radius: 1.3px;
				border: 0.2px solid #010101;
			}

			input[type=range]::-ms-track {
				width: 100%;
				height: 8.4px;
				cursor: pointer;
				background: transparent;
				border-color: transparent;
				border-width: 16px 0;
				color: transparent;
			}
			input[type=range]::-ms-fill-lower {
				background: #8e6516;
				border: 0.2px solid #010101;
				border-radius: 2.6px;
				box-shadow: 1px 1px 1px #000000, 0px 0px 1px #c0c0c0;
			}
			input[type=range]:focus::-ms-fill-lower {
				background: #8e6516;
			}
			input[type=range]::-ms-fill-upper {
				background: #8e6516;
				border: 0.2px solid #010101;
				border-radius: 2.6px;
				box-shadow: 1px 1px 1px #000000, 0px 0px 1px #c0c0c0;
			}
			input[type=range]:focus::-ms-fill-upper {
				background: #367ebd;
			}
		</style>
		
		<noscript>
			<div style="z-index: 5000; left: 0; position: fixed; text-align: center; width: 100%; height: 100%; background-color: rgba(48, 48, 48, 0.75);">
				<h2 style="line-height: 100%; padding-top: 25%; color: #fff;"><span style="border: 1px dotted #fff; padding: 25px 50px 25px 50px; background-color: rgba(255, 0, 0, 0.25)">Bitte aktivieren Sie JavaScript!</span></h2>
			</div>
		</noscript>
	</head>
	
	<body>
		<div id="container">
			<div id="linkList">			
				<!--	NAVIGATION	-->
				<?php
					echo $navbar;
				?>
				
				<!--	LOGIN 		-->
				<?php
					echo $logged;
				?>
			</div>
			
			<div id="intro">
				<div id="pageHeader">
					<h1><span style="position:absolute; right: 30px; top: 25px;">Z 3 : 1 T : 0 0 , 000</span></h1>
					<h3><span style="position:absolute; right: 30px; top: 64px;"><i>tempus fugit ..</i></span></h3>
				</div>
			
				<!-- 	COLUMN 1	-->
				<div id="modul_1" align="center">
					<h3>Mein Event</h3>
					<p>
						<?php echo $event_handler; ?>
						
						<?php
        					if(!empty($error_msg)) {
        						echo '<p class="error">';
        							echo '<span style="margin-left: 5px; border-bottom: 1px dotted #8E6516;"><font color="#FFD700">' . $state . '</font></span><br />';
        							echo $error_msg;
        						echo '</p>';
        					}
        				?>
						<table width="385px" cellspacing="5px">
							<tr>
								<th colspan="2">&nbsp;</th>
							</tr>
						</table>
					</p>
				</div>
		
				<div id="supportingText">
					<!-- 	COLUMN 2	-->
					<!--
					<div id="modul_2" align="center">
						<h3><span>ÜBERSCHRIFT 2</span></h3>
						<p>
							Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentialsly unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
						</p>
					</div>
					-->
				</div>

				<!-- 	FOOTER 		-->
				<?php
					include("essentials/footer.php");
				?>
			</div>
			<script>
				var rangeValues =
				{
					"1" : "1",
					"2" : "2",
					"3" : "3",
					"4" : "4",
					"5" : "5",
					"6" : "6",
					"7" : "7",
					"8" : "8",
					"9" : "9",
					"10" : "10",
					"11" : "11",
					"12" : "12",
					"13" : "13",
					"14" : "14",
					"15" : "15",
					"16" : "16",
					"17" : "17",
					"18" : "18",
					"19" : "19",
					"20" : "20",
					"21" : "21",
					"22" : "22",
					"23" : "23",
					"24" : "24",
					"25" : "25",
					"26" : "26",
					"27" : "27",
					"28" : "28",
					"29" : "29",
					"30" : "30",
					"31" : "31",
					"32" : "32",
					"33" : "33",
					"34" : "34",
					"35" : "35",
					"36" : "36",
					"37" : "37",
					"38" : "38",
					"39" : "39",
					"40" : "40",
					"41" : "41",
					"42" : "42",
					"43" : "43",
					"44" : "44",
					"45" : "45",
					"46" : "46",
					"47" : "47",
					"48" : "48",
					"49" : "49",
					"50" : "50",
					"51" : "51",
					"52" : "52",
					"53" : "53",
					"54" : "54",
					"55" : "55",
					"56" : "56",
					"57" : "57",
					"58" : "58",
					"59" : "59",
					"60" : "60",
					"61" : "61",
					"62" : "62",
					"63" : "63",
					"64" : "64",
					"65" : "65",
					"66" : "66",
					"67" : "67",
					"68" : "68",
					"69" : "69",
					"70" : "70",
					"71" : "71",
					"72" : "72",
					"73" : "73",
					"74" : "74",
					"75" : "75",
					"76" : "76",
					"77" : "77",
					"78" : "78",
					"79" : "79",
					"80" : "80",
					"81" : "81",
					"82" : "82",
					"83" : "83",
					"84" : "84",
					"85" : "85",
					"86" : "86",
					"87" : "87",
					"88" : "88",
					"89" : "89",
					"90" : "90",
					"91" : "91",
					"92" : "92",
					"93" : "93",
					"94" : "94",
					"95" : "95",
					"96" : "96",
					"97" : "97",
					"98" : "98",
					"99" : "99",
					"100" : "100",
					"101" : "101",
					"102" : "102",
					"103" : "103",
					"104" : "104",
					"105" : "105",
					"106" : "106",
					"107" : "107",
					"108" : "108",
					"109" : "109",
					"110" : "110",
					"111" : "111",
					"112" : "112",
					"113" : "113",
					"114" : "114",
					"115" : "115",
					"116" : "116",
					"117" : "117",
					"118" : "118",
					"119" : "119",
					"120" : "120",
					"121" : "121",
					"122" : "122",
					"123" : "123",
					"124" : "124",
					"125" : "125",
					"126" : "126",
					"127" : "127",
					"128" : "128",
					"129" : "129",
					"130" : "130",
					"131" : "131",
					"132" : "132",
					"133" : "133",
					"134" : "134",
					"135" : "135",
					"136" : "136",
					"137" : "137",
					"138" : "138",
					"139" : "139",
					"140" : "140",
					"141" : "141",
					"142" : "142",
					"143" : "143",
					"144" : "144",
					"145" : "145",
					"146" : "146",
					"147" : "147",
					"148" : "148",
					"149" : "149",
					"150" : "150",
					"151" : "151",
					"152" : "152",
					"153" : "153",
					"154" : "154",
					"155" : "155",
					"156" : "156",
					"157" : "157",
					"158" : "158",
					"159" : "159",
					"160" : "160",
					"161" : "161",
					"162" : "162",
					"163" : "163",
					"164" : "164",
					"165" : "165",
					"166" : "166",
					"167" : "167",
					"168" : "168",
					"169" : "169",
					"170" : "170",
					"171" : "171",
					"172" : "172",
					"173" : "173",
					"174" : "174",
					"175" : "175",
					"176" : "176",
					"177" : "177",
					"178" : "178",
					"179" : "179",
					"180" : "180",
					"181" : "181",
					"182" : "182",
					"183" : "183",
					"184" : "184",
					"185" : "185",
					"186" : "186",
					"187" : "187",
					"188" : "188",
					"189" : "189",
					"190" : "190",
					"191" : "191",
					"192" : "192",
					"193" : "193",
					"194" : "194",
					"195" : "195",
					"196" : "196",
					"197" : "197",
					"198" : "198",
					"199" : "199",
					"200" : "200"
				};

				$(document).ready(function(){
					// on page load, set the text of the label based the value of the range
					$('#rangeText').text(rangeValues[$('#dummy_slider').val()]);

					// setup an event handler to set the text when the range value is dragged (see event for input) or changed (see event for change)
					$('#dummy_slider').on('input change', function () {
						if($('#dummy_slider').val() < <?php echo $differ_maximum; ?>) {
							$('#rangeText').text(rangeValues[$(this).val()]);
						} else if($('#dummy_slider').val() == <?php echo $differ_maximum; ?>) {
							$('#rangeText').text(rangeValues[$(this).val()] + " max.");
						}
						
					});
				});
			</script>
		</div>
	</body>
</html>