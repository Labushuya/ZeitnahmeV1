<?php error_reporting(E_ALL);
	// BUFFER OUTPUT
	ob_start();
	
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
		$select_event = "SELECT id, eid FROM _race_run_events WHERE `eid` = '" . $eid . "' AND `active` = '1'";
		$result_event = mysqli_query($mysqli, $select_event);
		$numrow_event = mysqli_num_rows($result_event);
		
		// SEARCH FOR EVENTS FROM LOGGED IN USER
		if($numrow_event == 1) {
			header("Location: index.php");
			
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
	
	// CHECK FOR ERRORS
	if(isset($_GET['error'])) {
		echo "<p class=\"error\">Error Logging In!</p>";
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
			.ui-widget-content .ui-icon {
				color: white !important;
			}
		</style>
		
		<script>
			$(document).ready(function(){
				$(function($){
					$("#waiting_period").mask("99:99",{placeholder:"HH:MM"});
					$("#penalty_early").mask("99,99",{placeholder:"SS,00"});
					$("#penalty_late").mask("99,99",{placeholder:"SS,00"});
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

				$("#start").datepicker({
					minDate: new Date(<? echo date('Y', (time() + (24 * 60 * 60))); ?>, <? echo date('m', (time() + (24 * 60 * 60))); ?>, <? echo date('d', (time() + (24 * 60 * 60))); ?>),
					onSelect: function(selected) {
						$("#end").datepicker("option","minDate", selected)
					}
				});
				
				$("#end").datepicker({
					maxDate: new Date(<? echo date('Y', (time() + (14 * 24 * 60 * 60))); ?>, <? echo date('m', (time() + (14 * 24 * 60 * 60))); ?>, <? echo date('d', (time() + (14 * 24 * 60 * 60))); ?>),
					onSelect: function(selected) {
						$("#start").datepicker("option","maxDate", selected)
					}
				}); 

				// EVENT HANDLER ON KEYUP
				$(".input").keyup(function() {
					// CALL FUNCTION
					var cp_value = ucwords($(this).val(),true) ;
					$(this).val(cp_value );
				});
			
				// FUNCTION FOR CAPITALIZING AFTER DASHES AND HYPHENS
				function ucwords(str,force) {
					str = force ? str.toLowerCase() : str;
					return str.replace(/(^([a-züöäßA-ZÜÖÄ\p{M}]))|([ -][a-züöäßA-ZÜÖÄ\p{M}])/g,
					function(firstLetter) {
					   return firstLetter.toUpperCase();
					});
				}
				
				// CALCULATION HINT BASED ON SELECT OPTION
				$(function() {
					$('#t_calc').change(function(){
						$('.t_calc_type').hide();
						$('#' + $(this).val()).show();
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
					<h1><span style="position:absolute; right: 30px; top: 25px;">Z 3 : 1 T : 0 0 , 000</span></h1>
					<h3><span style="position:absolute; right: 30px; top: 64px;"><i>tempus fugit ..</i></span></h3>
				</div>
			
				<!-- 	COLUMN 1	-->
				<div id="modul_1" align="center">
					<h3>Veranstaltung erstellen</h3>
					<p>
						<form action="<?php echo esc_url($_SERVER['PHP_SELF']); ?>" method="POST" name="make_event_form" enctype="multipart/form-data">
							<table width="385px" cellspacing="5px">
								<tr>
									<th colspan="2">Organisatorische Angaben</th>
								</tr>
								<tr>
									<th colspan="2"><hr /></th>
								</tr>
								<tr>
									<td align="left">Veranstaltung<font color="#8E6516">*</font></td>
									<td align="right"><input class="input" name="event" id="event" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="Bezeichnung" required="required" /></td>
								</tr>
								<tr>
									<td align="left">Veranstalter<font color="#8E6516">*</font></td>
									<td align="right"><input name="event_owner" class="input" id="event_owner" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="Organisation / Verein" required="required" /></td>
								</tr>
								<tr>
									<td align="left">Logo<font color="#8E6516">*</font></td>
									<td align="right"><input id="file" class="input inputfile" name="logo" type="file" accept="image/*" required="required" /><label for="file"><img src="images/upload.png" style="vertical-align: middle;" class="upload">&emsp;Datei wählen</label></td>
								</tr>
								<tr>
									<th colspan="2">&nbsp;</th>
								</tr>
								<tr>
									<th colspan="2">Berechnungsgrundlegende Reglement Angaben</th>
								</tr>
								<tr>
									<th colspan="2"><hr /></th>
								</tr>
								<tr>
									<td align="left">Zeitenberechnung<font color="#8E6516">*</font></td>
									<td align="right">
										<select name="t_calc" id="t_calc" class="input-block-level" placeholder="Bitte auswählen" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" required="required" />
											<option selected="selected" disabled="disabled">Bitte auswählen</option>
											<option value="1">Ab Start</option>
											<option value="2">Einzeln</option>
											<!--<option value="3">S|Z ges.</option>-->
										</select>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<div id="1" class="t_calc_type" style="display: none; border: 1px solid #8E6516; color: #8E6516; text-align: center;">Die Fahrtzeit wird immer <strong>ab Start</strong> berechnet</div>
										<div id="2" class="t_calc_type" style="display: none; border: 1px solid #8E6516; color: #8E6516; text-align: center;">Die Fahrtzeit wird immer <strong>einzeln</strong> berechnet</div>
										<!--<div id="3" class="t_calc_type" style="display: none">Die Fahrtzeit wird immer ausgleichend berechnet</div>-->
									</td>
								</tr>								
								<tr>
									<td align="left">Karenzzeit<font color="#8E6516">*</font></td>
									<td align="right">
										<input name="waiting_period" id="waiting_period" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 120px;" placeholder="HH:SS" pattern="^(([01]?[0-9]|2[0-3]):[0-5][0-9]){1}$" required="required" />
										<a id="opener_karenzzeit_hilfe" href="#" style="color: #8E6516; padding-left: 3px;">?</a>
										<div id="help_karenzzeit" class="modal_fix" title="Hilfe: Karenzzeit" style="color: #FFFFFF; background: transparent; background-color: #A09A8E;">
											<p>
												<table width="100%" cellspacing="5px" style="border: 1px solid #FFFFFF;">
													<tr>
														<td align="justify">
															<font style="text-decoration: none; font-size: 17px;">
																Die Karenzzeit gibt an, wann eine Prüfung max. beendet sein sollte. Natürlich können 
																Sie nach wie vor Zeiten für einzelne Teilnehmer übermitteln. Diese Angabe dient mehr
																der Übersicht.
																<br />
																<br />
																<span style="border-bottom: 1px solid #fff;">Die absolute Karenzzeit errechnet sich wie folgt:</span>
																<br />
																<div style="padding: 16px; margin: 25px; height: 50px; background-color: #fff; color: #8e6516; text-align: center;">
																	<strong>Karenzzeit Angabe &plus; (Anzahl der Teilnehmer &times; 60 Sekunden)</strong>
																</div>
															</font>
														</td>
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
												<p>
													<br />  
												</p>
											</p>
										</div>
									</td>
								</tr>
								<!--
								<tr>
									<td align="left">Strafsekunden<font color="#8E6516">*</font></td>
									<td align="right">
										<table width="100%" cellspacing="0" cellpadding="0" border="0">
											<tr>
												<td align="right">
													<input name="penalty_early" id="penalty_early" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 66px;" placeholder="Vorzeit" pattern="^(([01]?[0-9]|2[0-3]),[0-9][0-9]){1}$" required="required" />
													<input name="penalty_late" id="penalty_late" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 65px;" placeholder="Überfällig" pattern="^(([01]?[0-9]|2[0-3]),[0-9][0-9]){1}$" required="required" />
												</td>
											</tr>
										</table>									
									</td>
								</tr>
								<tr>
									<td align="left">&nbsp;</td>
									<td align="right">
										<input name="penalty_late" id="penalty_boarding" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="Bordkarte lückenhaft" pattern="^(([01]?[0-9]|2[0-3]),[0-9][0-9]){1}$" required="required" />								
									</td>
								</tr>
								<tr>
									<td align="left">&nbsp;</td>
									<td align="right">
										<input name="penalty_late" id="penalty_stamp" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="Stempel fehlt" pattern="^(([01]?[0-9]|2[0-3]),[0-9][0-9]){1}$" required="required" />									
									</td>
								</tr>
								<tr>
									<th colspan="2">&nbsp;</th>
								</tr>
								-->
								<tr>
									<th colspan="2">Dauer der Veranstaltung</th>
								</tr>
								<tr>
									<th colspan="2"><hr /></th>
								</tr>
								<tr>
									<td align="left">Beginnend<font color="#8E6516">*</font></td>
									<td align="right"><input name="start" id="start" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="TT.MM.JJJJ" required="required" pattern="^((0[1-9]|[1-2][0-9]|3[0-1]).(0[1-9]|1[0-2]).([1-9]{1}[0-9]{3}))$" readonly /></td>
								</tr>
								<tr>
									<td align="left">Endend<font color="#8E6516">*</font></td>
									<td align="right"><input name="end" id="end" type="text" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" placeholder="TT.MM.JJJJ" required="required" pattern="^((0[1-9]|[1-2][0-9]|3[0-1]).(0[1-9]|1[0-2]).([1-9]{1}[0-9]{3}))$" readonly /></td>
								</tr>
								<tr>
									<th colspan="2">&nbsp;</th>
								</tr>
								<tr>
									<th colspan="2">Eingaben bestätigen</th>
								</tr>
								<tr>
									<th colspan="2"><hr /></th>
								</tr>
								<tr>
									<td align="left"><input name="reset" type="reset" value="Eingaben löschen" style="background: transparent; border-color: #8E6516; color: #FFFFFF; width: 135px;" /></td>
									<td align="right"><input type="submit" value="Jetzt erstellen" name="make_event" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" /></td>
								</tr>
							</table>
						</form>
						
						<?php
							// USER CAN MAKE EVENT
							if(isset($_POST['make_event'])) {
								// MAX SIZE 200KB
								$max_file_size = 1024*200;
								
								// VALID EXTENSIONS
								$valid_exts = array('jpeg', 'jpg', 'png', 'gif');
								
								// THUMBNAIL SIZES
								$sizes = array(100 => 100, 150 => 150, 250 => 250);
								
								if($_FILES['logo']['size'] < $max_file_size ) {
									// GET FILE EXTENSION
									$ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
									if(in_array($ext, $valid_exts)) {
										// RESIZE IMAGE
										foreach($sizes as $w => $h) {
											$files[] = resize($w, $h);
										}
									
										// PREPARE USER INPUT FOR DB
										// REWRITE POST VARIABLES
										$event			= $_POST['event'];
										$event_owner	= $_POST['event_owner'];
										$t_calc			= $_POST['t_calc'];
										$wperiod		= explode(':', $_POST['waiting_period']);
										$waiting_period = (3600 * intval($wperiod[0])) + (60 * intval($wperiod[1]));
										
										$start			= $_POST['start'];
										$end			= $_POST['end'];
										
										// SQL INJECTION DEFENCE
										$image = addslashes(file_get_contents($_FILES['logo']['tmp_name']));
										$image_name = addslashes($_FILES['logo']['name']);
												
										// SET FUNCTION TITLE CASE CORRECT: EVENT
										function titleCaseEvent($event, $delimiters = array(" ", "-", ".", "'", "O'", "Mc"), $exceptions = array("van", "de", "el", "la", "von", "vom", "der", "und", "zu", "auf", "dem", "dos", "I", "II", "III", "IV", "V", "VI")) {
											/*
											 * EXCEPTIONS IN LOWER CASE ARE WORDS YOU DONT WANT CONVERTED
											 * EXCEPTIONS ALL IN UPPER CASE ARE ANY WORDS YOU DONT WANT TO CONVERTED TO TITLE CASE
											 * BUT SHOULD BE CONVERTED TO UPPER CASE, E. G.:
											 * "king henry viii" OR "king henry Viii" SHOULD BE "King Henry VIII"
											*/
											$event = mb_convert_case($event, MB_CASE_TITLE, "UTF-8");
										
											foreach ($delimiters as $dlnr => $delimiter) {
												$words = explode($delimiter, $event);
												$newwords = array();
									
												foreach ($words as $wordnr => $word) {
													if (in_array(mb_strtoupper($word, "UTF-8"), $exceptions)) {
														// CHECK EXCEPTIONS LIST FOR ANY WORDS THAT SHOULD BE IN UPPER CASE
														$word = mb_strtoupper($word, "UTF-8");
													} elseif (in_array(mb_strtolower($word, "UTF-8"), $exceptions)) {
														// CHECK EXCEPTIONS LIST FOR ANY WORDS THAT SHOULD BE IN UPPER CASE
														$word = mb_strtolower($word, "UTF-8");
													} elseif (!in_array($word, $exceptions)) {
														// CONVERT TO UPPER CASE (NON-UTF-8 ONLY)
														$word = ucfirst($word);
													}
													array_push($newwords, $word);
												}
												$event = join($delimiter, $newwords);
											} // FOREACH
											return $event;	
										}
												
										// SET FUNCTION TITLE CASE CORRECT: EVENT OWNER
										function titleCaseEventOwner($event_owner, $delimiters = array(" ", "-", ".", "'", "O'", "Mc"), $exceptions = array("van", "de", "el", "la", "von", "vom", "der", "und", "zu", "auf", "dem", "dos", "I", "II", "III", "IV", "V", "VI")) {
										/*
										 * EXCEPTIONS IN LOWER CASE ARE WORDS YOU DONT WANT CONVERTED
										 * EXCEPTIONS ALL IN UPPER CASE ARE ANY WORDS YOU DONT WANT TO CONVERTED TO TITLE CASE
										 * BUT SHOULD BE CONVERTED TO UPPER CASE, E. G.:
										 * "king henry viii" OR "king henry Viii" SHOULD BE "King Henry VIII"
										*/
											$event_owner = mb_convert_case($event_owner, MB_CASE_TITLE, "UTF-8");
											foreach ($delimiters as $dlnr => $delimiter) {
												$words = explode($delimiter, $event_owner);
												$newwords = array();
												foreach ($words as $wordnr => $word) {
													if (in_array(mb_strtoupper($word, "UTF-8"), $exceptions)) {
														// CHECK EXCEPTIONS LIST FOR ANY WORDS THAT SHOULD BE IN UPPER CASE
														$word = mb_strtoupper($word, "UTF-8");
													} elseif (in_array(mb_strtolower($word, "UTF-8"), $exceptions)) {
														// CHECK EXCEPTIONS LIST FOR ANY WORDS THAT SHOULD BE IN UPPER CASE
														$word = mb_strtolower($word, "UTF-8");
													} elseif (!in_array($word, $exceptions)) {
														// CONVERT TO UPPER CASE (NON-UTF-8 ONLY)
														$word = ucfirst($word);
													}
													array_push($newwords, $word);
												}
												$event_owner = join($delimiter, $newwords);
											} // FOREACH
											return $event_owner;	
										}
												
										// FORMAT EVENT VARIABLES
										$event			= titleCaseEvent($event);
										$event_owner	= titleCaseEventOwner($event_owner);
											
										// CONVERT DATES
										$start = convert_to_db($start);
										$end = convert_to_db($end);
																				
										// INSERT SANITIZED VARIABLES
										$insert =	"
													INSERT INTO
														_race_run_events(
															id,
															eid,
															title,
															start,
															end,
															event_owner,
															image_path_100,
															image_path_150,
															image_path_250,
															count_wptable,
															count_zmembers,
															count_tmembers,
															count_zcontrol,
															count_zstamped,
															count_boarding,
															master_rid_type,
															t_calc,
															waiting_period,
															edit,
															active
														)
													VALUES (
														NULL,
														'".mysqli_real_escape_string($mysqli, utf8_decode($eid))."',
														'".mysqli_real_escape_string($mysqli, utf8_decode($event))."',
														'".mysqli_real_escape_string($mysqli, utf8_decode($start))."',
														'".mysqli_real_escape_string($mysqli, utf8_decode($end))."',
														'".mysqli_real_escape_string($mysqli, utf8_decode($event_owner))."',
														'".$files[0]."',
														'".$files[1]."',
														'".$files[2]."',
														'".mysqli_real_escape_string($mysqli, 0)."',
														'".mysqli_real_escape_string($mysqli, 0)."',
														'".mysqli_real_escape_string($mysqli, 0)."',
														'".mysqli_real_escape_string($mysqli, 0)."',
														'".mysqli_real_escape_string($mysqli, 0)."',
														'".mysqli_real_escape_string($mysqli, 0)."',
														'".mysqli_real_escape_string($mysqli, utf8_decode("NA"))."',
														'".mysqli_real_escape_string($mysqli, utf8_decode($t_calc))."',
														'".mysqli_real_escape_string($mysqli, $waiting_period)."',
														'".mysqli_real_escape_string($mysqli, 0)."',
														'".mysqli_real_escape_string($mysqli, 1)."'
													)";
										mysqli_query($mysqli, $insert);
												
										// REDIRECT AFTER INSERT TRY
										if($insert == true) {
											header('Location: /msdn/make_event_success.php');
											ob_end_flush(); 
										} else {
											header('Location: /msdn/make_event_fail.php');
											ob_end_flush(); 
										}
									} else {
										echo	'
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
												<div id="dialog-confirm" title="Logo größer als erlaubt">
													<p align="justify">
														<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
														Bitte halten Sie sich an die vorgegebene Größe des Logos!
													</p>
												</div>
												';
									}
								} else {
									echo	'
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
											<div id="dialog-confirm" title="Unbekanntes Format">
												<p align="justify">
													<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
													Dieses Format wird nicht unterstützt!<br /><br /><p style="text-align: center;"><u>Unterstützte Formate: jpeg, jpg, png, gif</u></p>
												</p>
											</div>
											';
								}
							}	
						?>
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