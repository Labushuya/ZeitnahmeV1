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
					    $("#veranstaltungsdatum").val(selected);
					    
					    //	Hole ausgewählten Veranstaltungstages
    					var date = $("#veranstaltungsdatum").val();
    					
    					//	Sende AJAX Anfrage basierend auf Veranstaltungsdatum
    					$.ajax({
    						url: "fetch_bc_constellation.php",
    						data: {
    								eid: <?php echo $eid; ?>, 
    								date: date
    						},
    						type: "POST",
    						success: function(html){
    							//	Füge Anzahl verfügbarer X-Positionen hinzu
    							$("#reihenfolge_anzahl").html(html);
    						}
    					});
					}
				});
				
				$("#reset").click(function() {
					$("#veranstaltungsdatum").val("");
					
					//	Setze Container für Positionen zurück
					$("#reihenfolge_anzahl").html("");
				});
				
				/*	
					Wenn Select-Feld für Position geändert wird
					füge Attribut required für Select-Feld 1. KP
					hinzu, um fehlerhafte Eingaben zu vermeiden
				*/
				$("body").on("change", ".pos", function() {
					$(this).closest('tr').find('.xpos:first').prop("required", true);
					
					//	Entferne disabled Attribut
					$(this).closest('tr').find('.xpos:first').prop("disabled", false);
					
					//	Schalte nächstes Select-Feld für KP frei
					$(this).closest('tr').next().find('.pos').prop("disabled", false);
				});
				
				//	Gebe zweites X-Positionen Select-Feld frei, sobald erstes belegt
				$("body").on("change", ".xpos", function() {
					//	Entferne disabled Attribut
					$(this).closest('tr').find('.xpos').not(this).prop("disabled", false);
				});
				
				//	Sperre bereits vergebene Positionen
				$("body").on("change", ".pos", function() {
					var selections = [];
				
					$('.pos option:selected').each(function() {
						if($(this).val())
							selections.push($(this).val());
					});
				  
					//	console.log(selections);
				 
					$('.pos option').each(function() {
						$(this).attr('disabled', $.inArray($(this).val(),selections) > -1 && !$(this).is(":selected"));
					});
				});
				
				//	Sperre bereits vergebene X-Positionen
				$("body").on("change", ".xpos", function() {
					var selections = [];
				
					$('.xpos option:selected').each(function() {
						if($(this).val())
							selections.push($(this).val());
					});
				  
					//	console.log(selections );
				 
					$('.xpos option').each(function() {
						$(this).attr('disabled', $.inArray($(this).val(),selections) > -1 && !$(this).is(":selected"));
					});
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
							<div>								
								<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 1px solid #FFFFFF; border-right: 1px solid #FFFFFF; border-bottom: 0;">
									<tr>
										<th align="left">Bordkartenkontrolle hinzufügen</th>
										<th align="right"></th>
									</tr>
									<tr>
										<th colspan="2"><hr /></th>
									</tr>
								</table>
								
								<div>
									<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;">
										<tr>
										    <td colspan="2" align="justify">
										        Erstellen Sie hier Ihre Bordkartenkontrollen. Die Zuweisung erfolgt auf Basis des jeweiligen Veranstaltungstages.
												Bitte legen Sie die Reihenfolge fest. Dies dient der Übersicht und ist identisch zur Bordkarte in Papierformat.
										    </td>   
										</tr>
										<tr>
										    <td>&nbsp;</td>
										</tr>
										<tr>
											<td align="left">Veranstaltungsdatum<font color="#8E6516">*</font></td>
											<td align="right">
												<input name="veranstaltungsdatum" id="veranstaltungsdatum" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 107px;" placeholder="TT.MM.JJJJ" required="required" pattern="^((0[1-9]|[1-2][0-9]|3[0-1]).(0[1-9]|1[0-2]).([1-9]{1}[0-9]{3}))$" /><img src="images/cross.png" id="reset" style="margin: -2px 0 0 5px; width: 22px; height: 22px; vertical-align: middle; text-align: center;"></img>
											</td>
										</tr>
										<tr>
										    <td>&nbsp;</td>
										</tr>
										<tr>
											<td align="left">Bezeichnung</td>
											<td align="right">
												<input name="bezeichnung" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" />
											</td>
										</tr>
										<tr>
										    <td>&nbsp;</td>
										</tr>
									</table>
									<table id="reihenfolge_anzahl" width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 0;">
									    
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
									<td align="right"><input type="submit" name="zs_save" id="zs_save" value="Speichern" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" /></td>
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
							
							if(isset($_POST['zs_save'])) {
								// UNSET SUBMIT
								unset($_POST['zs_save']);
								
								/*
									echo "Anzahl POST: " . count($_POST); 
									echo "<br /><br />";
									echo "<pre>";
									print_r($_POST);
									echo "</pre>";
									//	exit;
								*/	
								
								//	Suche Kennungsdaten aller aktiven Zeitkontrollen
								$select = "SELECT `eid`, `uname`, `upass` FROM `_optio_bmembers` WHERE `eid` = '" . $eid . "'";
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
								
								//	Passe Format von Veranstaltungsdatum an
								$explode = explode(".", $veranstaltungsdatum);
								
								$veranstaltungsdatum = $explode[2] . "-" . $explode[1] . "-" . $explode[0];
								
								if(isset($_POST['bezeichnung']) AND $_POST['bezeichnung'] != "") {
									$bezeichnung = mysqli_real_escape_string($mysqli, $_POST['bezeichnung']);
								} else {
									$bezeichnung = "BK" . ($numrow + 1);
								}
								
								//	Prüfe, ob übergebenes Veranstaltungsdatum im zulässigen Bereich ist
								$veranstaltungsdatum_beginn = strtotime($beginn);
								$veranstaltungsdatum_endend = strtotime($endend);
								
								if(
									strtotime($veranstaltungsdatum) >= $veranstaltungsdatum_beginn &&
									strtotime($veranstaltungsdatum) <= $veranstaltungsdatum_endend
								) {
									//	Generiere Zugangsdaten für ZK(n)
									//	Erstelle zufällige Benutzerkennung
									$uname = rand(100, 999) . rand(100, 999);
											
									//	Erstelle zufälliges Passwort
									$upass = rand(18273645, 51486237);
									
									//	Prüfe auf Einmaligkeit
									while(in_array($uname, $uname_pool)) {
										//	Erstelle zufällige Benutzerkennung
										$uname = rand(100, 999) . rand(100, 999);
									}
									
									//	Prüfe auf Einmaligkeit
									while(in_array($upass, $upass_pool)) {
										//	Erstelle zufälliges Passwort
										$upass = rand(18273645, 51486237);
									}
									
									//	Speichere Zeitkontrollen
									$insert =	"
												INSERT INTO
													`_optio_bmembers`(
														`id`,
														`eid`,
														`uname`,
														`upass`,
														`opt_whois`,
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
													'" . $uname . "',
													'" . $upass . "',
													'" . $bezeichnung . "',
													'0',
													'0',
													'0',
													'" . $veranstaltungsdatum . "',
													'',
													''
												)
												";
									$result = mysqli_query($mysqli, $insert);
									
									//	Prüfe, ob Datensatz angelegt wurde
									if(mysqli_affected_rows($mysqli) == 1) {
										//	Hole zuletzt eingefügte ID
										$inserted_id = mysqli_insert_id($mysqli);
										
										//	Zähler für max. mögliche Datensätze (korrekte Angaben)
										$max_insert = 0;
										
										//	Zähler für erfolgreich eingefügte Datensätze
										$success = 0;
										
										for($i = 0; $i < count($_POST['reihenfolge']); $i++) {
											if(isset($_POST['reihenfolge'][$i][0]) AND $_POST['reihenfolge'][$i][0] != "Wählen") {
												//	Erhöhe max. mögliche Datensätze
												$max_insert++;
												
												//	Prüfe auf primäre und sekundäre X-Positionen
												$xpos_primary = $_POST['reihenfolge'][$i][1][0];
												
												if(isset($_POST['reihenfolge'][$i][1][1]) AND $_POST['reihenfolge'][$i][1][1] != "Bitte wählen") {
													$xpos_secondary = $_POST['reihenfolge'][$i][1][1];
												} else {
													$xpos_secondary = "";
												}
												
												//	Speichere zugehörige Zeitkontrollen-Informationen
												$insert =	"
															INSERT INTO
																`_optio_bmembers_order`(
																	`id`,
																	`bid`,
																	`lfd`,
																	`pos_primary`,
																	`pos_secondary`
																)
															VALUES(
																NULL,
																'" . $inserted_id . "',
																'" . ($i + 1) . "',
																'" . $xpos_primary . "',
																'" . $xpos_secondary . "'
															)
															";
												$result = mysqli_query($mysqli, $insert);
												
												if(mysqli_affected_rows($mysqli) == 1) {
													$success++;
												}
											}
										}
										
										if($max_insert == 0) {
											$state      = 'Fehler:';
											$error_msg  = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Keine ordnungsgemäßen Eingaben erkannt!</span><br />';
										} else {
											if($success == $max_insert) {
												//	Bordkartenkontrolle angelegt
												$state      = 'Erfolgreich!';
												$error_msg  = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Borkartenkontrollen-Benutzerkonto angelegt!</span><br />';
												
												//  Aktualisiere bestehende Anzahl Bordkarten-Kontrollen
												$update =   "
												            UPDATE
												                `_race_run_events`
												            SET
												                `count_boarding` = `count_boarding` + 1
												            WHERE
												                `eid` = '" . $eid . "'
												            AND
												                `active` = '1'
												            ";
												mysqli_query($mysqli, $update);
											} else {
												$state      = 'Fehler:';
												$error_msg  = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Borkartenkontrollen-Benutzerkonto konnte nicht angelegt werden!</span><br />';
											}
										}
									//	Datensatz wurde nicht angelegt
									} else {
										$state      = 'Fehler:';
										$error_msg  = '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Borkartenkontrollen-Benutzerkonto konnte nicht angelegt werden!</span><br />';
									}
									
									//	Lege HTML Redirect fest
									$redir = '<meta http-equiv="refresh" content="3; url=/msdn/_addbc.php">';
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