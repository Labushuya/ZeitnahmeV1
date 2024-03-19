<?php
	// SET ERROR REPORTING LEVEL
	error_reporting(E_ALL);
	
	// SET TIMEZONE
	date_default_timezone_set("Europe/Berlin");
	
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
	
	// INCLUDE SPREADSHEET FUNCIONS
	require 'classes/spreadsheet/vendor/autoload.php';
	
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	
	// START SESSION
	session_start();
	
	$error = "";
	
	// CHECK LOGGED IN USER AND VALIDATE INFORMATION
	if(isset($_SESSION['user_id']) AND $_SESSION['user_id'] != "") {	
		// RETURN SESSION TO LOCAL VARIABLES
		$uid	= $_SESSION['user_id'];
		$rid_type	= $_SESSION['rid_type'];
		$rid		= $_SESSION['rid'];
		$username	= $_SESSION['username'];
		$opt_whois	= $_SESSION['opt_whois'];
		$_SESSION['logtype'] = "zm";
		
		// FETCH EVENT ID
		$select_event = "SELECT * FROM `_optio_zmembers` WHERE `id` = '" . $uid . "'";
		$result_event = mysqli_query($mysqli, $select_event);
		$getrow_event = mysqli_fetch_assoc($result_event);
		$zid = $getrow_event['id'];
		$zdesc	= $getrow_event['rid_type'];
		$zmid	= $getrow_event['rid'];
		
		// DECLARE EVENT ID
		$eid = $getrow_event['eid'];
		
		//	Formular wurde abgesendet
		if(isset($_POST["upload"])) {
			$file_mimes =	array(
								'text/x-comma-separated-values', 
								'text/comma-separated-values', 
								'application/octet-stream', 
								'application/vnd.ms-excel', 
								'application/x-csv', 
								'text/x-csv', 
								'text/csv', 
								'application/csv', 
								'application/excel', 
								'application/vnd.msexcel', 
								'text/plain', 
								'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
								'application/vnd.oasis.opendocument.spreadsheet'
							);
			
			//	Prüfe, ob Datei zulässige Endungen besitzt
			if(isset($_FILES['file']['name']) && in_array($_FILES['file']['type'], $file_mimes)) {
				//	Array für Anzahl an Prüfungspositionen
				$container_zpos = array();
				
				//	Zählervariable für erfolgreiche Speicherung
				$success = 0;
				
				//	Zählervariable für unbekannte Teilnehmernummern
				$wrong_startid = 0;
				
				$array = explode('.', $_FILES['file']['name']);
				$extension = end($array);
				
				if($extension == 'csv') {
					$reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
				}/* elseif($extension == 'xls') {
					$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
				} elseif($extension == 'xlsx') {
					$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
				}*/
				
				/*
					$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
					$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xml();
					$reader = new \PhpOffice\PhpSpreadsheet\Reader\Ods();
					$reader = new \PhpOffice\PhpSpreadsheet\Reader\Slk();
					$reader = new \PhpOffice\PhpSpreadsheet\Reader\Gnumeric();
					$reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();				
				*/
				
				$spreadsheet = $reader->load($_FILES['file']['tmp_name']);
				
				$sheetData = $spreadsheet->getActiveSheet()->toArray();
				
				/*
					echo "<pre>";
					print_r($sheetData);
					echo "</pre>";
				
				
					Array
					(
						[0] => Array
							(
								[0] => 148;08:54:59
								[1] => 47
							)
					)
				*/
				
				//	Hole Positionen des eingeloggten Zeitnehmers
				$select_zmember_pos = "SELECT * FROM `_optio_zpositions` WHERE `zid` = '" . $uid . "'";
				$result_zmember_pos = mysqli_query($mysqli, $select_zmember_pos);
				$numrow_zmember_pos = mysqli_num_rows($result_zmember_pos);
				
				if($numrow_zmember_pos > 0) {
					while($getrow_zmember_pos = mysqli_fetch_assoc($result_zmember_pos)) {
						$container_zpos[] = $getrow_zmember_pos['pos'];
					}
					
					//	Anzahl Zeilen
					for($i = 0; $i < count($sheetData); $i++) {
						//	Trenne Startnummer von Zeit (ohne 1/100)
						$raw_input = explode(";", $sheetData[$i][0]);
						$raw_input2 = explode(";", $sheetData[$i][1]);
						
						$startnummer = $raw_input[0];
						$zeit = $raw_input[1];
						$hundertstel = $raw_input2[0];
						$position = $raw_input2[1];
						
						//	Debugging
						/*
							echo $startnummer . "<br />";
							echo $zeit . "<br />";
							echo $hundertstel . "<br />";
							echo $position . "<br />";
							exit;
						*/
						
						$gesamtzeit = $zeit . "," . $hundertstel;
						
						//	Prüfe, ob Startnummer vorhanden
						$select_tmember_valid = "SELECT * FROM `_optio_tmembers` WHERE `eid` = '" . $eid . "' AND `sid` = '" . $startnummer . "'";
						$result_tmember_valid = mysqli_query($mysqli, $select_tmember_valid);
						$numrow_tmember_valid = mysqli_num_rows($result_tmember_valid);
						
						if($numrow_tmember_valid == 1) {
							// CHECK FORMAT LENGTH
							if(strlen($gesamtzeit) >= 10 AND strlen($gesamtzeit) <= 11) {
								// GET TIME PARTS (HH:MM:SS,UU)
								$time = explode(":", $gesamtzeit);
								$gesamtzeit_hh = $time[0];
								$gesamtzeit_mm = $time[1];
								$gesamtzeit_ss = $time[2];
								
								// GET CENTISECONDS AND SET SECONDS
								$t_centi = explode(",", $gesamtzeit_ss);
								$gesamtzeit_ss = $t_centi[0];
								$gesamtzeit_ms = $t_centi[1];
								
								if(strlen($gesamtzeit_ms) == 1) {
									$gesamtzeit_ms = $gesamtzeit_ms . "0";
								}
							} elseif(strlen($gesamtzeit) >= 7 AND strlen($gesamtzeit) <= 8) {
								// GET TIME PARTS (MM:SS,UU)
								$time = explode(":", $gesamtzeit);
								$gesamtzeit_hh = "00";
								$gesamtzeit_mm = $time[0];
								$gesamtzeit_ss = $time[1];
								
								// GET CENTISECONDS AND SET SECONDS
								$t_centi = explode(",", $gesamtzeit_ss);
								$gesamtzeit_ss = $t_centi[0];
								$gesamtzeit_ms = $t_centi[1];
								
								if(strlen($gesamtzeit_ms) == 1) {
									$gesamtzeit_ms = $gesamtzeit_ms . "0";
								}
								
								// CREATE SECONDS FROM INPUT TO SAVE FOR DB INSTEAD OF STRTOTIME
								$seconds = intval(($gesamtzeit_mm * 60) + $gesamtzeit_ss);
							// NOTHING MATCHES --> EXIT AND REDIRECT WITH HINT
							} else {
								$error =	'
											<script>
												$(document).ready(function(){
													$("#dialog-confirm").dialog({
														autoOpen: true,
														resizable: false,
														height: "auto",
														width: 400,
														modal: true,
														buttons: {
															"Verstanden": function(){
																$(this).dialog("close");
															}
														}
													}).bind("clickoutside", function(e) {
														$target = $(e.target);
														if (!$target.filter(".hint").length
																&& !$target.filter(".hintclickicon").length) {
															$field_hint.dialog("close");
														}
													});
													
													$("#results").submit(function(){
														$("#dialog-confirm").modal("show");
													});
												});
											</script>
											<div id="dialog-confirm" title="Falsches Zeitenformat">
												<p align="justify">
													<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
													Bitte beachten Sie das korrekte Zeitenformat! [HH:MM:SS,00 oder MM:SS,00]
												</p>
											</div>
											';
								$state		= 'Fehler:';
								$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Falsches Zeitenformat!</span><br />';
							}
								
							// CREATE SECONDS FROM INPUT TO SAVE FOR DB INSTEAD OF STRTOTIME
							$seconds = intval(($gesamtzeit_mm * 60) + $gesamtzeit_ss);
								
							// GET SINGLE, UNTOUCHED CENTISECONDS
							$gesamtzeit_centi = $gesamtzeit_ms;
							
							// MERGE TIME TO CONVERT TO TIMESTAMP (USING : FOR CENTISECONDS TO MAKE SPLIT EASIER, WHEN CALCULATING RESULTS)
							/*	
								THE VERSION BENEATH GIVES EXACT RESULT AS NEW ONE, EXCEPT THAT IT WON'T ACCOUNT THE CURRENT DATE IF RESULT
								GETS ENTERED AFTER DAYS, ETC. => E. G. RESULT ENTERED 1 DAY AFTER EVENT == CALCULATED ~45:MM:SS,UU
								THE NEW VERSION TAKES THE REGISTERED DATE FOR THIS ROUND INTO ACCOUNT, SO THE RESULT WILL ALWAYS BE
								FROM THE "CURRENT" DATE
							*/
							// $time_merged = $gesamtzeit_hh . ":" . $gesamtzeit_mm . ":" . $gesamtzeit_ss . "." . $gesamtzeit_ms;
							
							// FETCH REGISTERED DATE FOR THIS ROUND
							$select_registered_date = "SELECT `eid`, `rid_type`, `rid`, `execute` FROM `_main_wptable` WHERE `eid` = '" .  $eid. "' AND `rid` = '" . $rid . "'";
							$result_registered_date = mysqli_query($mysqli, $select_registered_date);
							$getrow_registered_date = mysqli_fetch_assoc($result_registered_date);
							
							$registered_date = $getrow_registered_date['execute'];
							
							$time_merged = $registered_date . " " . $gesamtzeit_hh . ":" . $gesamtzeit_mm . ":" . $gesamtzeit_ss . "." . $gesamtzeit_ms;
							
							// CONVERT FOR SPECIFIC ZTYPE
							if(!in_array("Sprint", $container_zpos)) {
								// CREATE TIMESTAMP
								$ergSekunden = strtotime($time_merged);
							} elseif(in_array("Sprint", $container_zpos)) {
								$ergSekunden = $seconds;
							}
							
							// REWRITE MERGED TIME BECAUSE OF STORING AS DECIMAL
							$time_merged = str_replace('.', ',', $time_merged);
							
							$time_merged = explode(' ', $time_merged);
							$ergString = $time_merged[1];
							
							//	Suche nach Ergebnis für Startnummer basierend auf aktueller Prüfungsposition
							$select_tmember_result = "SELECT * FROM `_main_wpresults` WHERE `eid` = '" . $eid . "' AND `rid` = '" . $zmid . "' AND `sid` = '" . $startnummer . "' AND `position` = '" . $position . "'";
							$result_tmember_result = mysqli_query($mysqli, $select_tmember_result);
							$numrow_tmember_result = mysqli_num_rows($result_tmember_result);
							
							//	Kein Ergebnis für diese Prüfungsposition wurde gefunden
							if($numrow_tmember_result == 0) {
								//	Füge Ergebnis unter aktueller Prüfungsposition ein
								$insert =	"INSERT INTO 
												`_main_wpresults`(
													`id`,
													`eid`,
													`rid`,
													`zid`,
													`sid`,
													`position`,
													`ergebnis_sekunden`,
													`ergebnis_hundertstel`,
													`ergebnis_string`,
													`duplicate`
												)
												VALUES(
													NULL,
													'" . $eid . "',
													'" . $rid . "',
													'" . $uid . "',
													'" . $startnummer . "',
													'" . $position . "',
													'" . $ergSekunden . "',
													'" . $gesamtzeit_centi . "',
													'" . $ergString . "',
													'1'
												)
											";
								mysqli_query($mysqli, $insert);
											
								// CHECK IF QUERY WAS SUCCESSFUL AND ECHO MESSAGE
								if(mysqli_affected_rows($mysqli) == 1) {
									$success++;
								}
								
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
													'Eintrag: #" . $startnummer . " | " . $position . "-Ergebnis mit " . $ergString . "'
												)";
								$result_log = mysqli_query($mysqli, $insert_log);
							//	Ein Ergebnis für diese Prüfungsposition wurde gefunden
							} elseif($numrow_tmember_result == 1) {
								$getrow_tmember_result = mysqli_fetch_assoc($result_tmember_result);
								$id = $getrow_tmember_result['id'];
								$update = 	"UPDATE
												`_main_wpresults`
											SET
												`id`			= '" . $id . "',
												`eid`		= '" . $eid . "',
												`rid`		= '" . $rid . "',
												`zid`			= '" . $uid . "',
												`sid`	= '" . $startnummer . "',
												`position`			= '" . $position . "',
												`ergebnis_sekunden`		= '" . $ergSekunden . "',
												`ergebnis_hundertstel`		= '" . $gesamtzeit_centi . "',
												`ergebnis_string`	= '" . $ergString . "',
												`duplicate`	= '1'
											WHERE 
												`id`				= '" . $id . "'
											";
								mysqli_query($mysqli, $update);
								
								// CHECK IF QUERY WAS SUCCESSFUL AND ECHO MESSAGE
								if(mysqli_affected_rows($mysqli) == 1) {
									$success++;
								//	Wenn bei UPDATE Daten identisch, prüfe auf vorher und nachher
								} elseif(mysqli_affected_rows($mysqli) == 0) {
									if(($getrow_tmember_result['t_time'] == $ergSekunden) OR ($getrow_tmember_result['t_realtime'] == $ergString)) {
										$success++;
									}
								}
								
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
													'Korrektur: #" . $startnummer . " | " . $position . "-Ergebnis mit " . $ergString . "'
												)";
								$result_log = mysqli_query($mysqli, $insert_log);
							}
						//	Unbekannte Teilnehmernummer
						} elseif($numrow_tmember_valid == 0) {
							$wrong_startid++;
						}
					}
					
					//	Prüfe, ob alle Datensätze eingepflegt wurden
					if($success == $i) {
						$state		= 'Erfolgreich!';
						$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Alle Zeiten wurden erfolgreich hochgeladen!</span><br />';
					} elseif($success != $i) {
						//	Prüfe, ob Datenbankverbindungsfehler oder unbekannte Teilnehmernummer
						if($wrong_startid == 0) {
							$state		= 'Fehler:';
							$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Zeiten konnten nur teilweise hochgeladen werden!</span><br />';
						} elseif($wrong_startid > 0) {
							if(($wrong_startid + $success) == $i) {
								$state		= 'Erfolgreich!';
								$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Alle Zeiten wurden erfolgreich hochgeladen (' . $wrong_startid . ' ignoriert)!</span><br />';
							} else {
								$state		= 'Erfolgreich';
								$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Zeiten konnten nur teilweise hochgeladen werden!</span><br />';
							}
						}						
					}
				//	Zeitnehmer besitzt keine Prüfungspositionen	
				} else {
					//	Logge Zeitnehmer aus
					header("Location: timebuddy_fail.php?error=0x2001");
				}				
			//	Datei unzulässig
			} else {
				$state		= 'Fehler!';
				$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Dieses Dateiformat wird nicht unterstützt!</span><br />';
			}
		}
		
		// OUTPUT NAVBAR AND LOGIN / LOGOUT PANEL
		$logged = file_get_contents("essentials/opt_logout_ft.html");
		$navbar = file_get_contents("essentials/mz_navbar_logged_in.html");
		include("essentials/chat_slider.php");
	} elseif(isset($_SESSION['user_id']) AND $_SESSION['user_id'] != "" AND !isset($_SESSION['sid'])) {
		header('Location: /msdn/timebuddy.php');
	} elseif(isset($_SESSION['user_id']) AND $_SESSION['user_id'] != "" AND isset($_SESSION['sid'])) {
		header('Location: /msdn/racer.php');
	} else {
		$logged = file_get_contents("essentials/login.html");
		$navbar = file_get_contents("essentials/navbar_logged_out.html");
		header("Location: index.php");
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
				
				
		<!--	INCLUDING LIBS	-->
		<?php 
			include("lib/library.html");
			include("lib/library_int_timebuddy.html");
		?>
		
		<noscript>
			<div style="z-index: 5000; left: 0; position: fixed; text-align: center; width: 100%; height: 100%; background-color: rgba(48, 48, 48, 0.75);">
				<h2 style="line-height: 100%; padding-top: 25%; color: #fff;"><span style="border: 1px dotted #fff; padding: 25px 50px 25px 50px; background-color: rgba(255, 0, 0, 0.25)">Bitte aktivieren Sie JavaScript!</span></h2>
			</div>
		</noscript>
	</head>
	<body>
		<div id="container_tb">
			<div id="linkList_nobanner">			
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
				<!-- 	COLUMN 1	-->
				<div id="modul_1" align="center">
					<h3>Mein Event</h3>
					<p>	
						<form action="<? $_SERVER['PHP_SELF']; ?>" id="form" method="POST" enctype="multipart/form-data">
						<table cellspacing="5px" cellpadding="0" style="border: 1px solid #FFFFFF;" width="385px">
							<tr>	 
								<td colspan="2" align="center"><input type="file" name="file" id="file" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 100%" required="required" /></td>
							</tr>
							<tr>
								<th colspan="2">&nbsp;</th>
							</tr>
							<tr>
								<td align="left"><input type="reset" value="Zurücksetzen" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;"/></td>
								<td align="right"><input type="submit" name="upload" id="upload" value="Hochladen" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" /></td>
							</tr>
							<tr>
								<th colspan="2">&nbsp;</th>
							</tr>
							<?php
								//	Hole Prüfung
								$select_rd = "SELECT * FROM `_optio_zmembers` WHERE `id` = '" . $zid . "'";
								$result_rd = mysqli_query($mysqli, $select_rd);
								$numrow_rd = mysqli_num_rows($result_rd);
								
								//	Zeitnehmer-Zugang gefunden
								if($numrow_rd > 0) {
									echo	"
											<tr>
												<th colspan=\"2\">Zeitnehmer-Indikator</th>
											</tr>
											<tr>
												<td colspan=\"2\"><u>Zeiten werden hochgeladen für:</u></td>
											</tr>
											";
									
									//	Hole einzelne Werte
									$getrow_rd = mysqli_fetch_assoc($result_rd);
									
									echo	"
											<tr>
												<td colspan=\"2\">" . $getrow_rd['rid_type'] . $getrow_rd['rid'] . "</td>
											</tr>
											";
									
									//	Hole alle Positionen
									$select_zpos = "SELECT * FROM `_optio_zpositions` WHERE `zid` = '" . $zid . "'";
									$result_zpos = mysqli_query($mysqli, $select_zpos);
									$numrow_zpos = mysqli_num_rows($result_zpos);
									
									//	Positionen gefunden
									if($numrow_zpos > 0) {
										//	Hole einzelne Positionen
										while($getrow_zpos = mysqli_fetch_assoc($result_zpos)) {
											echo	"
													<tr>
														<td colspan=\"2\">" . $getrow_zpos['pos'] . "</td>
													</tr>
													";
										}
									//	Keine Positionen gefunden
									} else {
										//	Logge Zeitnehmer aus
										header("Location: timebuddy_fail.php?error=0x2001");
									}
								//	Zeitnehmer-Zugang nicht gefunden
								} else {
									//	Logge Zeitnehmer aus
									header("Location: timebuddy_fail.php?error=0x2000");
								}
							?>
							<tr>
								<th colspan="2">&nbsp;</th>
							</tr>
							<tr>
								<th colspan="2">Zulässiges Dateiformat: <!--[.xls(x)], [.ods] oder --> .csv (";")!</th>
							</tr>
							<tr>
								<th colspan="2"><img src="images/excel_example_result_upload.jpg" alt="Upload Format"></img></th>
							</tr>
						</table>
						</form>
						
						<div><br /></div>
						<?php
							if(!empty($error_msg)) {
								echo '<p class="error">';
									echo '<span style="margin-left: 5px; border-bottom: 1px dotted #8E6516;"><font color="#FFD700">' . $state . '</font></span><br />';
									echo $error_msg;
								echo '</p>';
							}
						?>
						
						<table width="385px" cellspacing="5px" style="border: 0;">
						<tr>
							<th colspan="2">&nbsp;</th>
						</tr>
						</table>
						
						<div id="special_hint"></div>
						
						<span id="logged_state"></span>
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
		</div>
	</body>
</html>