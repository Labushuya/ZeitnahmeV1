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
		$select_event = "SELECT `id`, `eid`, `start`, `end` FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `active` = '1'";
		$result_event = mysqli_query($mysqli, $select_event);
		$numrow_event = mysqli_num_rows($result_event);
		
		// SEARCH FOR EVENTS FROM LOGGED IN USER
		if($numrow_event == 1) {
			$logged = file_get_contents("essentials/logout.html");
			$navbar = file_get_contents("essentials/navbar_logged_in_active.html");
			
			//	Hole Veranstaltungsdatum Beginn und Ende
			$getrow_event = mysqli_fetch_assoc($result_event);
			$beginn = $getrow_event['start'];
			$endend = $getrow_event['end'];
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
		
		<script>
		    $(document).ready(function() {
		        // SELECT OPTION CHECK
		        $('#form').submit(function (e) {
                    if ($('.option').val() == '') {
						// STOP FORM SUBMISSION
                        e.preventDefault();
                    }
                });
				
				$.datepicker.regional['de'] = {
					showWeek: true,
					showButtonPanel: true,
					closeText: 'Fertig',
					prevText: '<<',
					nextText: '>>',
					currentText: 'heute',
					monthNames: [
						'Januar',
						'Februar',
						'März',
						'April',
						'Mai',
						'Juni',
						'Juli',
						'August',
						'September',
						'Oktober',
						'November',
						'Dezember'
					],
					monthNamesShort: [
						'Jan',
						'Feb',
						'Mär',
						'Apr',
						'Mai',
						'Jun',
						'Jul',
						'Aug',
						'Sep',
						'Okt',
						'Nov',
						'Dez'
					],
					dayNames: [
						'Sonntag',
						'Montag',
						'Dienstag',
						'Mittwoch',
						'Donnerstag',
						'Freitag',
						'Samstag'
					],
					dayNamesShort: [
						'So',
						'Mo',
						'Di',
						'Mi',
						'Do',
						'Fr',
						'Sa'
					],
					dayNamesMin: [
						'So',
						'Mo',
						'Di',
						'Mi',
						'Do',
						'Fr',
						'Sa'
					],
					weekHeader: 'KW',
					dateFormat: 'dd.mm.yy',
					firstDay: 0,
					isRTL: false,
					showMonthAfterYear: false,
					yearSuffix: ''
				};
				
				$.datepicker.setDefaults(
					$.datepicker.regional["de"], 
					$('#start').datepicker('option', 'dateFormat', 'dd/mm/yy')
				);		

				$("#veranstaltungsdatum").datepicker({
					minDate: new Date(<? echo "'" . $beginn . "'"; ?>),
					maxDate: new Date(<? echo "'" . $endend . "'"; ?>),
					onSelect: function(selected) {
						$("#veranstaltungsdatum").datepicker("option","minDate", selected);
						$("#veranstaltungsdatum").datepicker("option","maxDate", selected);
					}
				});
				
				$("#reset").click(function() {
					$("#veranstaltungsdatum").val("");
				});
            });
		</script>
		
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
					<h1><span style="position: absolute; right: 30px; top: 25px;">Z 3 : 1 T : 0 0 , 000</span></h1>
					<h3><span style="position: absolute; right: 30px; top: 64px;"><i>tempus fugit ..</i></span></h3>
				</div>
			
				<!-- 	COLUMN 1	-->
				<div id="modul_1" align="center">
					<h3>Mein Event</h3>
					<p>
						<form action="<? $_SERVER['PHP_SELF']; ?>" method="POST" id="form">
							<div class="zk_input_fields_wrap">								
								<a href="#" class="zk_add_field_button">
								<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 1px solid #FFFFFF; border-right: 1px solid #FFFFFF; border-bottom: 0;">
									<tr>
										<th align="left">Zeitkontrolle hinzufügen</th>
										<th align="right"><font color="#FFD700"><!--[+]--></font></th>
									</tr>
									<tr>
										<th colspan="2"><hr /></th>
									</tr>
								</table>
								</a>
								
								<div>
									<?php
										//	Suche nach bisherigen ZKs
										$select = "SELECT MAX(`id`) AS `highest_zkid` FROM `_optio_zcontrol` WHERE `eid` = '" . $eid . "'";
										$result = mysqli_query($mysqli, $select);
										$numrow = mysqli_num_rows($result);
										
										//	Wurden ZKs gefunden, setze Zähler auf nächsthöheres ZK-Paar
										if($numrow > 0) {
											$getrow = mysqli_fetch_assoc($result);
											$nextzk = $getrow['highest_zkid'] + 1;
										} else {
											$nextzk = 1;
										}
									?>
									<table id="addzk" width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;">
										<tr>
										    <td colspan="2" align="justify">
										        Erstellen Sie hier Ihre Zeitkontrollen. Die Zuweisung erfolgt auf Basis des jeweiligen Veranstaltungstages.
										    </td>   
										</tr>
										<tr>
										    <td>&nbsp;</td>
										</tr>
										<tr>
											<td align="left">Veranstaltungsdatum<font color="#8E6516">*</font></td>
											<td align="right">
												<input name="veranstaltungsdatum" id="veranstaltungsdatum" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 107px;" placeholder="TT.MM.JJJJ" required="required" pattern="^((0[1-9]|[1-2][0-9]|3[0-1]).(0[1-9]|1[0-2]).([1-9]{1}[0-9]{3}))$" readonly /><img src="images/cross.png" id="reset" style="margin: -2px 0 0 5px; width: 22px; height: 22px; vertical-align: middle; text-align: center;"></img>
											</td>
										</tr>
										<tr>
										    <td>&nbsp;</td>
										</tr>
										<tr>
										    <td align="left">Ort / Position ZK<?php echo $nextzk; ?></td>
										    <td align="right">
												<input name="title_1" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="Bsp. Opel Arena In" />
											</td>
									    </tr>
										<tr>
											<td align="left">Vorzeit ZK<?php echo $nextzk; ?> erlaubt?</td>
											<td align="right">
												<select name="vorzeit_1" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" >
													<option value='0' selected='selected'>Nein</option>
													<option value='1'>Ja</option>
												</select>
											</td>
										</tr>
										<tr>
										    <td>&nbsp;</td>
									    </tr>
									    <tr>
										    <td align="left">Ort / Position ZK<?php echo $nextzk + 1; ?></td>
										    <td align="right">
												<input name="title_2" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="Bsp. Opel Arena Out" />
											</td>
										</tr>
										<tr>
											<td align="left">Vorzeit ZK<?php echo $nextzk + 1; ?> erlaubt?</td>
											<td align="right">
												<select name="vorzeit_2" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" >
													<option value='0' selected='selected'>Nein</option>
													<option value='1'>Ja</option>
												</select>
											</td>
										</tr>
										<tr>
										    <td>&nbsp;</td>
									    </tr>
										<tr>
											<td align="left">Sollzeit in Min.<font color="#8E6516">*</font></td>
											<td align="right">
												<input name="sollzeit" maxlength="3" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" required="required" pattern="^[0-9]{1,3}$" />
											</td>
										</tr>
									</table>
								</div>
							</div>
							<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF;">
								<tr>
									<th colspan="2">&nbsp;</th>
								</tr>
								<tr>
									<th colspan="2">Eingaben speichern</th>
								</tr>
								<tr>
									<th colspan="2"><hr /></th>
								</tr>
								<tr>
									<td align="left"><input type="button" value="<<" onclick="window.location='my_event.php';" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;"/></td>
									<td align="right"><input type="submit" name="zk_save" value="Speichern" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" /></td>
								</tr>
							</table>
							<!--
							<div id="slider">
								<div id="custom-handle" class="ui-slider-handle"></div>
							</div>
							-->
							<table width="385px" cellspacing="5px" style="border: 0;">
								<tr>
									<th colspan="2">&nbsp;</th>
								</tr>
							</table>
						</form>
						
						<?
							// DISPLAY ALL ERRORS
							error_reporting(E_ALL);
							
							if(isset($_POST['zk_save'])) {
								// UNSET SUBMIT
								unset($_POST['zk_save']);
								
								/*
									echo "Anzahl POST: " . count($_POST); 
									echo "<br /><br />";
									echo "<pre>";
									print_r($_POST);
									echo "</pre>";
										exit;
								*/
								
								//	Suche nach höchster pid aller Zeitkontrollen
								$select_pid = "SELECT `pid` FROM `_optio_zcontrol` WHERE `eid` = '" . $eid . "' ORDER BY `pid` DESC LIMIT 1";
								$result_pid = mysqli_query($mysqli, $select_pid);
								$getrow_pid = mysqli_fetch_assoc($result_pid);
								
								//	Lege neue Pair ID fest
								$pid = $getrow_pid['pid'] + 1;
								
								//	Suche Kennungsdaten aller aktiven Zeitkontrollen
								$select = "SELECT `eid`, `uname`, `upass` FROM `_optio_zcontrol` WHERE `eid` = '" . $eid . "'";
								$result = mysqli_query($mysqli, $select);
								$numrow = mysqli_num_rows($result);
								
								//	Erstelle Arrays mit bereits bestehenden Kennungsdaten
								$uname_pool = array();
								$upass_pool = array();
								
								//	Keine aktiven Zeitkontrollen gefunden
								if($numrow == 0) {
									//	Speichere je einen Leerwert in Pool-Arrays
									$uname_pool[] = "";
									$upass_pool[] = "";
								} else {
									//	Speichere Kennungsdaten für späteren Abgleich aus DB in Arrays
									while($getrow = mysqli_fetch_assoc($result)) {
										$uname_pool[] = $getrow['uname'];
										$upass_pool[] = $getrow['upass'];
									}
								}
								
								//	Bereinige Übergabeparameter
								$veranstaltungsdatum = mysqli_real_escape_string($mysqli, $_POST['veranstaltungsdatum']);
								
								if(isset($_POST['title_1']) AND $_POST['title_1'] != "") {
								    $title_1 = mysqli_real_escape_string($mysqli, titleCase($_POST['title_1']));
								} else {
								    $title_1 = "";
								}
								
								if(isset($_POST['title_2']) AND $_POST['title_2'] != "") {
								    $title_2 = mysqli_real_escape_string($mysqli, titleCase($_POST['title_2']));
								} else {
								    $title_2 = "";
								}
								
								$bezeichnung_1 = "ZK" . ($numrow + 1);
								$bezeichnung_2 = "ZK" . ($numrow + 2);
								
								$vorzeit_1 = mysqli_real_escape_string($mysqli, $_POST['vorzeit_1']);
								$vorzeit_2 = mysqli_real_escape_string($mysqli, $_POST['vorzeit_2']);
								$sollzeit = mysqli_real_escape_string($mysqli, $_POST['sollzeit']);
								
								//	Passe Format von Veranstaltungsdatum an
								$explode = explode(".", $veranstaltungsdatum);
								
								$veranstaltungsdatum = $explode[2] . "-" . $explode[1] . "-" . $explode[0];
								
								//	Prüfe, ob übergebenes Veranstaltungsdatum im zulässigen Bereich ist
								$veranstaltungsdatum_beginn = strtotime($beginn);
								$veranstaltungsdatum_endend = strtotime($endend);
								
								//	Debugging
								/*
									echo "Beginn: " . $veranstaltungsdatum_beginn . "<br />";
									echo "Übergeben: " . strtotime($veranstaltungsdatum) . "<br />";
									echo "Endend: " . $veranstaltungsdatum_endend . "<br />";
									//	exit;
								*/
								
								if(
									strtotime($veranstaltungsdatum) >= $veranstaltungsdatum_beginn &&
									strtotime($veranstaltungsdatum) <= $veranstaltungsdatum_endend
								) {
									//	Generiere Zugangsdaten für ZK(n)
									//	Erstelle zufällige Benutzerkennung
									$uname_zk1 = rand(100, 999) . rand(100, 999);
											
									//	Erstelle zufälliges Passwort
									$upass_zk1 = rand(18273645, 51486237);
									
									//	Prüfe auf Einmaligkeit
									while(in_array($uname_zk1, $uname_pool)) {
										//	Erstelle zufällige Benutzerkennung
										$uname_zk1 = rand(100, 999) . rand(100, 999);
									}
									
									//	Füge eben erstellte Benutzerkennung dem Pool hinzu
									$uname_pool[] = $uname_zk1;
									
									//	Prüfe auf Einmaligkeit
									while(in_array($upass_zk1, $upass_pool)) {
										//	Erstelle zufälliges Passwort
										$upass_zk1 = rand(18273645, 51486237);
									}
									
									//	Füge eben erstelltes Passwort dem Pool hinzu
									$upass_pool[] = $upass_zk1;
									
									//	Generiere Zugangsdaten für ZK(n+1)
									//	Erstelle zufällige Benutzerkennung
									$uname_zk2 = rand(100, 999) . rand(100, 999);
											
									//	Erstelle zufälliges Passwort
									$upass_zk2 = rand(18273645, 51486237);
									
									//	Prüfe auf Einmaligkeit
									while(in_array($uname_zk2, $uname_pool)) {
										//	Erstelle zufällige Benutzerkennung
										$uname_zk2 = rand(100, 999) . rand(100, 999);
									}
									
									//	Prüfe auf Einmaligkeit
									while(in_array($upass_zk2, $upass_pool)) {
										//	Erstelle zufälliges Passwort
										$upass_zk2 = rand(18273645, 51486237);
									}
									
									//	Speichere Zeitkontrollen
									$insert_zk1 =	"
													INSERT INTO
														`_optio_zcontrol`(
															`id`,
															`eid`,
															`pid`,
															`uname`,
															`upass`,
															`opt_whois`,
															`title`,
															`vorzeit`,
															`active`,
															`neutralized`,
															`logintime`,
															`eventdate`,
															`ipv4`,
															`ipv6`
														)
													VALUES(
														NULL,
														'" . $eid . "',
														'" . $pid . "',
														'" . $uname_zk1 . "',
														'" . $upass_zk1 . "',
														'" . $bezeichnung_1 . "',
														'" . $title_1 . "',
														'" . $vorzeit_1 . "',
														'0',
														'0',
														'0',
														'" . $veranstaltungsdatum . "',
														'',
														''
													)
													";
									$result_zk1 = mysqli_query($mysqli, $insert_zk1);
									
									//	Prüfe, ob Datensatz ZK(n) gespeichert worden ist
									if(mysqli_affected_rows($mysqli) == 1) {
										//	ID des gespeicherten Datensatzes
										$inserted_zk1id = mysqli_insert_id($mysqli);
										
										//	Führe nächste Speicherung durch
										$insert_zk2 =	"
														INSERT INTO
															`_optio_zcontrol`(
																`id`,
																`eid`,
																`pid`,
																`uname`,
																`upass`,
																`opt_whois`,
																`title`,
																`vorzeit`,
																`active`,
																`neutralized`,
																`logintime`,
																`eventdate`,
																`ipv4`,
																`ipv6`
															)
														VALUES(
															NULL,
															'" . $eid . "',
															'" . $pid . "',
															'" . $uname_zk2 . "',
															'" . $upass_zk2 . "',
															'" . $bezeichnung_2 . "',
															'" . $title_2 . "'
															'" . $vorzeit_2 . "',
															'0',
															'0',
															'0',
															'" . $veranstaltungsdatum . "',
															'',
															''
														)
														";
										$result_zk2 = mysqli_query($mysqli, $insert_zk2);
										
										//	Prüfe, ob Datensatz ZK(n+1) gespeichert worden ist
										if(mysqli_affected_rows($mysqli) == 1) {
											//	ID des gespeicherten Datensatzes
											$inserted_zk2id = mysqli_insert_id($mysqli);
											
											//	Fahre mit Connector-Prozedur fort
											//	Suche nach Datensatz mit übergebenen Parametern
											$select_connector = "SELECT * FROM `_optio_zcontrol_relation` WHERE `eid` = '" . $eid . "' AND `pid` = '" . $pid . "' AND `zkid1` = '" . $inserted_zk1id . "' AND `zkid2` = '" . $inserted_zk2id . "'";
											
											$result_connector = mysqli_query($mysqli, $select_connector);
											$numrow_connector = mysqli_num_rows($result_connector);
											
											//	Kein Datensatz gefunden
											if($numrow_connector == 0) {
												//	Speichere Connector-Datensatz
												$insert_connector =	"
																	INSERT INTO
																		`_optio_zcontrol_relation`(
																			`id`,
																			`eid`,
																			`pid`,
																			`zkid1`,
																			`zkid2`,
																			`sollzeit_min`
																		)
																	VALUES(
																		NULL,
																		'" . $eid . "',
																		'" . $pid . "',
																		'" . $inserted_zk1id . "',
																		'" . $inserted_zk2id . "',
																		'" . $sollzeit . "'
																	)
																	";																	
												$result_insert_connector = mysqli_query($mysqli, $insert_connector);
												
												//	Prüfe, ob Datensatz gespeichert wurde
												if(mysqli_affected_rows($mysqli) == 1) {
													//	Zeitkontrollen-Prozedur vollständig durchgeführt
													$state      = 'Erfolgreich!';
													$error_msg  = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Zeitkontrollen-Benutzerkonto angelegt!</span><br />';
												//	Datensatz wurde nicht gespeichert
												} else {
													$state      = 'Fehler:';
													$error_msg  = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Speicherfehler [ Code: zkcon ]</span><br />';
												}
												
												//	Lege HTML Redirect fest
												$redir = '<meta http-equiv="refresh" content="3; url=/msdn/_addzk.php">';
											//	Datensatz bereits vorhanden?
											} else {
												//	Lösche Vorgang vollständig
												$delete_zkx = "DELETE FROM `_optio_zcontrol` WHERE `id` = '" . $inserted_zk1id . "' AND `id` = '" . $inserted_zk2id . "'";
												mysqli_query($mysqli, $delete_zkx);
												
												$delete_zkr = "DELETE FROM `_optio_zcontrol_relation` WHERE `zkid1` = '" . $inserted_zk1id . "' AND `zkid2` = '" . $inserted_zk2id . "'";
												mysqli_query($mysqli, $delete_zkr);
											}
										//	Keine ID vorhanden (return == 0)
										} else {
											$state      = 'Fehler:';
											$error_msg  = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Speicherfehler [ Code: zk2id ]</span><br />';
										}
									//	Keine ID vorhanden (return == 0)
									} else {
										$state      = 'Fehler:';
										$error_msg  = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Speicherfehler [ Code: zk1id ]</span><br />';
									}
								//	Manipulationsversuch? Veranstaltungsdatum nicht in zulässigem Bereich
								} else {
									$state      = 'Fehler:';
									$error_msg  = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Unzulässiges Veranstaltungsdatum</span><br />';
								}
							}
						?>
						
    					<?php
    						if(!empty($error_msg)) {
    							echo '<p class="error">';
    								echo '<span style="margin-left: 5px; border-bottom: 1px dotted #8E6516;"><font color="#FFD700">' . $state . '</font></span><br />';
    								echo $error_msg;
    							echo '</p>';
    						}							
							
							if(isset($redir)) {
								echo $redir;
							}
    					?>
						
						<table width="385px" cellspacing="5px" style="border: 0;">
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
		</div>
	</body>
</html>