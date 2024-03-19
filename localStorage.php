<?php
	// Debugging
	error_reporting(E_ALL);

	// Lege Zeitzone fest
	date_default_timezone_set("Europe/Berlin");

	// Binde Funktionsdateien ein
	include_once 'includes/functions.php';

	// Binde Konfigurationsdatei ein
	include_once 'includes/db_connect.php';

	// Prüfe, ob Session bereits gestartet wurde
	// PHP Version < 5.4.0
	if (session_id() == '') {
		session_start();
	}
	// PHP Version > 5.4.0, 7
	/*
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}
	*/

	// Test
	$_SESSION['uid'] = 37;
	$_SESSION['rid_type'] = "GP";
	$_SESSION['rid'] = 1;
	$_SESSION['opt_whois'] = "";
	$_SESSION['logtype'] = "mz";

	$error = "";

	// Benutzer ist eingeloggt
	if (isset($_SESSION['uid']) and $_SESSION['uid'] != "") {
		// Validiere Session-Informationen
		$uid				= $_SESSION['uid'];
		$rid_type		= $_SESSION['rid_type'];
		$rid				= $_SESSION['rid'];
		$opt_whois	= $_SESSION['opt_whois'];
		$logtype    = $_SESSION['logtype'];

		// Prüfe Zugangsberechtigung und leite ggf. weiter
		switch ($logtype) {
			//  Zeitkontrolle
			case "zk":
				header("Location: zcontrol.php");
			break;
			//  Stempelkontrolle
			case "zs":
				header("Location: zstamp.php");
			break;
			//  Bordkartenkontrolle
			case "bc":
				header("Location: boarding.php");
			break;
			// Keine Zugangsberechtigung gefunden
			default:
				$error =	"
									Swal.fire({
										allowOutsideClick: false,
										allowEscapeKey: false,
										type: 'question',
										title: 'Zugangsberechtigung',
										text: 'Die Ihrem Zugang zugewiesene Berechtigung wurde gelöscht.',
										footer: '<span style=\"font-size; small;\">Im Zweifelsfall kontaktieren Sie Ihren zuständigen Auswerter!<br /><br /><em>Fehlercode: znDeletedAccess</em></span>',
										showConfirmButton: true,
										confirmButtonText: '<i style=\"color: #fff;\" class=\"fas fa-redo\"></i>&emsp;Seite verlassen',
										confirmButtonColor: '#8e6516',
										backdrop:	`
											rgba(162,166,164,1)
											center
											no-repeat
										`
									}).then((result) => {
										if (result.value) {
											location.href = \"index.php\";
										}
									});
									";
			break;
		}

		// Hole zugehörige Event-ID
		$select = "SELECT * FROM `_optio_zmembers` WHERE `id` = '" . $uid . "'";
		$result = mysqli_query($mysqli, $select);
		$numrow = mysqli_num_rows($result);

		// Zeitnehmer nicht (mehr) vorhanden
	 	if ($numrow == 0) {
			$error =	"
								Swal.fire({
									allowOutsideClick: false,
									allowEscapeKey: false,
									type: 'question',
									title: 'Zugang gelöscht',
									text: 'Der von Ihnen genutzte Zugang wurde zwischenzeitlich gelöscht! Sie werden ausgeloggt ..',
									footer: '<span style=\"font-size; small;\">Im Zweifelsfall kontaktieren Sie Ihren zuständigen Auswerter!<br /><br /><em>Fehlercode: znDeletedLogin</em></span>',
									showConfirmButton: true,
									confirmButtonText: '<i style=\"color: #fff;\" class=\"fas fa-redo\"></i>&emsp;Seite verlassen',
									confirmButtonColor: '#8e6516',
									backdrop:	`
										rgba(162,166,164,1)
										center
										no-repeat
									`
								}).then((result) => {
									if (result.value) {
										location.href = \"index.php\";
									}
								});
								";
		} elseif ($numrow == 1) {
			$getrow = mysqli_fetch_assoc($result);

			// Lege Event-ID fest
			$eid = $getrow['eid'];

			// Hole alle zugewiesenen Positionen
			$select = "SELECT DISTINCT(`pos`) FROM `_optio_zpositions` WHERE `zid` = '" . $uid . "' AND `rid` = '" . $rid . "'";
			$result = mysqli_query($mysqli, $select);
			$numrow = mysqli_num_rows($result);

			if ($numrow > 0) {
				$zpos = "";

				while ($getrow = mysqli_fetch_assoc($result)) {
					$zpos .= $getrow['pos'] . ":";
				}

				// Prüfe, ob es sich bei der hinterlegten Prüfung um Sprint handelt
				if (strpos("Sprint", $zpos) === true) {
					$result_mask = '$("#ergebnis").mask("99:99,99",{placeholder:"MM:SS,00"});';
					$inp_pattern = '([0-5][0-9]:[0-5][0-9],[0-9][0-9]){1}';
					$result_plho = 'MM:SS,00';
					$result_form = 8;

					// Lege Marker für Übergabe der Ergebnisdaten fest
					$ztypeSprint = "Sprint";
				} else {
					$result_mask = '$("#ergebnis").mask("99:99:99,99",{placeholder:"HH:MM:SS,00"});';
					$inp_pattern = '(([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9],[0-9][0-9]){1}';
					$result_plho = 'HH:MM:SS,00';
					$result_form = 11;

					// Lege Marker für Übergabe der Ergebnisdaten fest
					$ztypeSprint = "Regular";
				}
			// Zeitnehmer besitzt keine zugewiesenen Positionen
			} else {
				$error =	"
									Swal.fire({
										allowOutsideClick: false,
										allowEscapeKey: false,
										type: 'question',
										title: 'Zuweisungsfehler',
										text: 'Die Ihrem Zugang zugewiesene(n) Positionen fehlen oder wurden gelöscht. Dieser Zugang muss neu angelegt werden.',
										footer: '<span style=\"font-size; small;\">Im Zweifelsfall kontaktieren Sie Ihren zuständigen Auswerter!<br /><br /><em>Fehlercode: znDeletedPositions</em></span>',
										showConfirmButton: true,
										confirmButtonText: '<i style=\"color: #fff;\" class=\"fas fa-redo\"></i>&emsp;Seite verlassen',
										confirmButtonColor: '#8e6516',
										backdrop:	`
											rgba(162,166,164,1)
											center
											no-repeat
										`
									}).then((result) => {
										if (result.value) {
											location.href = \"index.php\";
										}
									});
									";
			}
		}
	// Benutzer ist nicht eingeloggt, leite auf Hauptseite weiter
	} else {
		header("Location: index.php");
	}
?>
<!DOCTYPE html>
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

		<!--	INCLUDING CSS			-->
		<link rel="stylesheet" type="text/css" href="css/style.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="css/default.css" />
		<link rel="stylesheet" type="text/css" href="css/component.css" />
		<link rel="stylesheet" type="text/css" href="css/plugins.css" />
		<link rel="stylesheet" type="text/css" href="css/navigation.css" />
		<link rel="stylesheet" type="text/css" href="css/all.css" />
		<link rel="stylesheet" type="text/css" href="css/jquery-ui-1.12.1-custom.css" />
		<link rel="stylesheet" type="text/css" href="css/jquery-ui-1.12.1.theme-custom.css" />
		<link rel="stylesheet" type="text/css" href="css/jquery-ui-1.12.1.structure-custom.css" />
		<link rel="stylesheet" type="text/css" href="css/pace-theme-corner-indicator.css" />

		<style>
			.swal2-confirm {
				width: 100% !important;
				padding: 10px 35px !important;
			}

			#outdated {
			  font-family: 'Lato', Calibri, Arial, sans-serif;
			  position: absolute;
			  background-color: #b94a48;
			  color: white;
			  display: none;
			  overflow: hidden;
			  left: 0;
			  position: fixed;
			  text-align: center;
			  text-transform: uppercase;
			  top: 0;
			  width: 100%;
			  z-index: 1500;
			  padding: 0 24px 24px 0;
			}

			#outdated.fullscreen {
	    	height: 100%;
			}

			#outdated .vertical-center {
		    display: table-cell;
		    text-align: center;
		    vertical-align: middle;
			}

			#outdated h6 {
		    font-size: 25px;
		    line-height: 25px;
		    margin: 12px 0;
			}

			#outdated p {
		    font-size: 12px;
		    line-height: 12px;
		    margin: 0;
			}

			#outdated #buttonUpdateBrowser {
		    border: 2px solid white;
		    color: white;
		    cursor: pointer;
		    display: block;
		    margin: 30px auto 0;
		    padding: 10px 20px;
		    position: relative;
		    text-decoration: none;
		    width: 230px;
			}

			#outdated #buttonUpdateBrowser:hover {
	      background-color: white;
	      color: #b94a48;
			}

			#outdated .last {
		    height: 20px;
		    position: absolute;
		    right: 70px;
		    top: 10px;
		    width: auto;
		    display: inline-table;
			}

			#outdated .last[dir=rtl] {
		    left: 25px !important;
		    right: auto !important;
			}

			#outdated #buttonCloseUpdateBrowser {
		    color: white;
		    display: block;
		    font-size: 36px;
		    height: 100%;
		    line-height: 36px;
		    position: relative;
		    text-decoration: none;
				width: 100%;
			}
		</style>

		<noscript>
			<div style="z-index: 5000; left: 0; position: fixed; text-align: center; width: 100%; height: 100%; background-color: rgba(185, 74, 72, 1);">
				<h1 style="line-height: 100%; padding-top: 25%; color: #fff;"><span style="padding: 25px 50px 25px 50px; background-color: transparent">Bitte aktivieren Sie JavaScript!</span></h1>
			</div>
		</noscript>
	</head>
	<body>
		<div id="preloader" style="z-index: 999; left: 0; position: fixed; text-align: center; width: 100%; height: 100%; background-color: #8e6516;">
			<h1 style="line-height: 100%; padding-top: 25%; color: #fff;"><span style="padding: 25px 50px 25px 50px; background-color: transparent">Lade benötigte Ressourcen</span></h1>
		</div>

		<div id="container_tb">
			<div id="linkList_nobanner">
				<!--	NAVIGATION	-->
			</div>

			<div id="intro">
				<!-- 	COLUMN 1	-->
				<div id="modul_1" align="center">
					<table width="100%" cellspacing="0" cellpadding="5px">
						<tr>
							<td><h3>Local Storage Test</h3></td>
							<td><span id="pendingresState" style="display: none;"><i class="fas fa-database" style="margin-right: 2px; color: #468847;" title="hochzuladende Ergebnisdaten ausstehend!"></i></span></td>
							<td><span id="zepositionState"><i class="fas fa-map-marker-alt" style="margin-right: 2px; color: #468847;" title="Automatische Ergebnis-Positionsbestimmung!"></i></span></td>
							<td><span id="localstoreState"><i class="fas fa-save" style="color: #468847;" title="Ihre Ergebnisdaten sind im Browser gespeichert!"></i></span></td>
							<td><span id="connectionState"><i class="fas fa-wifi" style="color: #468847;" title="Sie sind mit dem Internet verbunden!"></i></span></td>
						</tr>
					</table>

					<p>
						<table id="show_team_status" cellspacing="5px" cellpadding="5px" style="border: 1px solid #FFFFFF; font-size: small;" width="385px">
							<tr>
								<td onmouseover="this.style.color='#FFD700'" onmouseout="this.style.color='#FFFFFF'">Zeige Teamstatus</td>
								<td>&nbsp;</td>
							</tr>
						</table>

						<table id="hide_team_status" cellspacing="5px" cellpadding="5px" style="border: 1px solid #FFFFFF; border-bottom: 0; font-size: small;" width="385px">
							<tr>
								<td onmouseover="this.style.color='#FFD700'" onmouseout="this.style.color='#FFFFFF'">Verberge Teamstatus</td>
								<td>&nbsp;</td>
							</tr>
						</table>

						<table cellspacing="5px" cellpadding="5px" style="border: 1px solid #FFFFFF; font-size: small;" width="385px" id="teilnehmerStatus"></table>

						<br />

						<table cellspacing="5px" cellpadding="0" style="border: 1px solid #FFFFFF;" width="385px">
							<tr>
								<td align="left">
									Prüfungsnummer
								</td>
								<td align="right">
									<?php
										// Hole zugewiesene Prüfung(en)
										$select_optio_zm = "SELECT `id`, `eid`, `rid_type`, `rid` FROM `_optio_zmembers` WHERE `eid` = '" . $eid . "' AND `id` = '" . $uid . "' LIMIT 1";
										$result_optio_zm = mysqli_query($mysqli, $select_optio_zm);
										$spalte_optio_zm = mysqli_fetch_assoc($result_optio_zm);
									?>

									<input type="text" id="pruefung" name="pruefung" value="<?php echo $spalte_optio_zm['rid_type'] . $spalte_optio_zm['rid']; ?>" disabled="disabled" />
								</td>
							</tr>
							<tr>
								<td align="left">
									Startnummer
								</td>
								<td align="right">
									<input type="tel" id="startnummer" name="startnummer" maxlength="4" pattern="^[1-9]{1}[0-9]{0,3}$" placeholder="Erwarte Startnummer" required autofocus />
								</td>
							</tr>
							<?php
								// Generiere Eingabefeld basierend auf Anzahl der Elemente
								if (isset($zpos)) {
									// Splitte String auf
									$container_zpos = explode(":", $zpos);

									if (count($container_zpos) == 1) {
										echo	'
							<tr>
								<td align="left">
									Prüfungsposition
								</td>
								<td align="right">
									<input type="text" id="position" name="position" value="' . $container_zpos[$i] . '" required readonly="readonly" />
								</td>
							</tr>
								';
									} else {
										echo	'
							<tr>
								<td align="left">
									Prüfungsposition
								</td>
								<td align="right">
									<select name="position" id="position" style="background: transparent; background-color: #FFFFFF; color: #8e6516; width: 135px;" required>
										<option value="none">&#8987; Startnummer?</option>
													';

										for ($i = 0; $i < (count($container_zpos) - 1); $i++) {
											echo	'
										<option value="' . $container_zpos[$i] . '" disabled="disabled">' . $container_zpos[$i] . '</option>
														';
										}
										echo	'
									</select>
								</td>
							</tr>
													';
									}
								// ERROR
								} else {

								}
							?>
							<tr>
								<th colspan="2">&nbsp;</th>
							</tr>
							<tr>
								<td id="result_hint">&nbsp;</td>
							</tr>
							<tr>
								<td align="left">Zeit</td>
								<td align="right">
									<table width="135px" cellspacing="0px">
										<tr>
											<td align="left">
												<input type="tel" id="ergebnis" name="ergebnis" style="width: 135px;" maxlength="11" required placeholder="<?php echo $result_plho; ?>" />
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<th colspan="2">&nbsp;</th>
							</tr>
							<tr>
								<td align="left">
									<button id="time" name="time" style="height: 24px; border: 1px solid #FFFFFF; background: transparent; background-color: #A09A8E; color: #8e6516; width: 135px;" tabindex="-1" disabled='disabled' disabled>
										<a style="text-decoration: none; border-style: none; color: #8e6516; font-family: 'Lato', Calibri, Arial, sans-serif; font-size: 14px; pointer-events: none; cursor: default;" href="https://www.schnelle-online.info/Atomuhr-Uhrzeit.html" id="soitime220204983267" tabindex="-1">Uhrzeit</a>
										<script type="text/javascript">
											SOI = (typeof(SOI) != 'undefined') ? SOI : {};

											(SOI.ac21fs = SOI.ac21fs || []).push(function() {
												(new SOI.DateTimeService("220204983267", "DE")).start();
											});

											(function() {
												if (typeof(SOI.scrAc21) == "undefined") {
													SOI.scrAc21=document.createElement('script');
													SOI.scrAc21.type='text/javascript';
													SOI.scrAc21.async=true;
													SOI.scrAc21.src=((document.location.protocol == 'https:') ? 'https://' : 'http://') + 'homepage-tools.schnelle-online.info/Homepage/atomicclock2_1.js';
													var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(SOI.scrAc21, s);
												}
											})();
										</script>
									</button>
								</td>
								<td align="right">
									<input type="submit" name="store" id="store" value="Ergebnis speichern" />
								</td>
							</tr>
							<tr>
								<th colspan="2"></th>
							</tr>
							<?php
								// Hole zuletzt eingegebene Zeiten
								/*
								$select_recent_store = "SELECT * FROM `_main_wpresults` WHERE `zid` = '" . $uid . "' ORDER BY `id` DESC LIMIT 3";
								$result_recent_store = mysqli_query($mysqli, $select_recent_store);
								$numrow_recent_store = mysqli_num_rows($result_recent_store);

								if($numrow_recent_store > 0) {
									echo	"
							<tr>
								<td colspan=\"2\" style=\"border-bottom: 1px solid white;\"><strong>Zuletzt getätigte Eingaben</strong></td>
							</tr>
												";

									while($getrow_recent_store = mysqli_fetch_assoc($result_recent_store)) {
										echo	"
							<tr>
								<td align=\"left\">Startnummer <strong>" . $getrow_recent_store['sid'] . "</strong></td>
								<td align=\"right\">Zeit <strong>" . $getrow_recent_store['ergebnis_string'] . "</strong></td>
							</tr>
													";
									}
								}
								*/
							?>
						</table>
					</p>
					<p>
						<span id="status"></span>
					</p>
					<p id="supportingText"></p>
				</div>
			</div>
		</div>

		<div id="outdated"></div>

		<!--	INCLUDING JQ LIBS		-->
		<script src="js/jquery-3.4.1.min.js"></script>
		<script src="js/jquery-3.1.0.migrate.min.js"></script>

		<!--	INCLUDING JS			-->
		<script src="js/pace.min.js"></script>
		<script src="js/outdated-browser-rework.min.js"></script>
		<script src="js/nav.js"></script>
		<script src="js/modernizr.custom.js"></script>
		<script src="js/dlmenu.js"></script>
		<script src="js/keypress.js"></script>
		<script src="js/modal.js"></script>
		<script src="js/jspatch.js"></script>
		<script src="js/sweetalert2.min.js"></script>
		<script src="js/jquery-1.4.1.maskedinput.min.js"></script>

		<!--	INCLUDING JQ PLUGINS	-->
		<script src="js/jquery.easing.min.js"></script>
		<script src="js/jquery.dlmenu.js"></script>
		<script src="js/jquery-ui-1.12.1-custom.js"></script>

		<script>
			// Lege Ergebnisspeicher global fest
			var ergebnisspeicher = "",
			speicherStatus = "";

			// Falls offline: zugewiesene Ergebnis-Positionen
			var zpos = '<?php echo $zpos; ?>';

			Pace.on('done', function() {
				// Blende Preloader-Screen aus
				$('#preloader').hide();

				// Prüfe Browserkompatibilität
				outdatedBrowserRework({
					fullscreen: true,
					browserSupport: {
						IE: 9,
						Edge: 79,
						Firefox: 59,
						Chrome: 79,
						Safari: 13,
						Opera: 66,
						// Yandex: { major: 17, minor: 10 }
					},
					requireChromeOnAndroid: false,
					isUnknownBrowserOK: false,
					messages: {
						de: {
							outOfDate: "Ihr Browser ist veraltet!",
							unsupported: "Ihr Browser wird nicht unterstützt!",
							update: {
								web: "Aktualisieren Sie Ihren Browser, um einen reibungslosen Ablauf zu gewährleisten!",
								googlePlay: "Aktualisieren Sie Ihren Browser auf Google Play",
								appStore: "Aktualisieren Sie Ihren Browser auf iOS die Einstellungen"
							},
							url: "http://outdatedbrowser.com/",
							callToAction: "Meinen Browser aktualisieren",
							close: "Schließen"
						}
					}
				});

				console.log(outdatedBrowserRework);

				$(document).ready(function() {
					// Übergebe PHP-Fehler an jQuery
					<?php echo $error; ?>

					// Initialisiere Tooltips jQuery-UI
					$(document).tooltip();

					// Marker, um zu erfahren, ob erstmals aufgerufen oder bereits erfolgt
					var hasConnectivityProblem = 0;

					// Prüfe, ob localStorage verfügbar ist
					if(lsTest() === true) {
						// Local Storage verfügbar
						// Erstelle localStorage Objekt
						ergebnisspeicher = localStorage;
						speicherStatus = 1;

						Swal.fire({
							allowOutsideClick: false,
							allowEscapeKey: false,
							type: 'success',
							title: 'Wichtige Anmerkung!',
							html: 'Dieses System wurde so konzipiert, dass es automatisch in den Offline-Modus wechselt, sollte Ihre Internetverbindung einmal abbrechen. Alle eingetragenen Ergebnisse werden lokal gesichert und in regelmäßigen Abständen hochgeladen.<br /><br /><hr /><br /><i style="color: #468847" class="fas fa-check"></i>&emsp;<strong>Ihr Browser verfügt über alle Voraussetzungen!</strong>',
							showConfirmButton: true,
							confirmButtonText: 'Zur Kenntnis genommen',
							confirmButtonColor: '#8e6516',
							backdrop:	`
								rgba(162,166,164,.75)
								center
								no-repeat
							`
						});
					} else {
						// Local Storage nicht verfügbar
						// Erstelle Array
						ergebnisspeicher = (typeof ergebnisspeicher != 'undefined' && ergebnisspeicher instanceof Array) ? ergebnisspeicher : []
						speicherStatus = 0;

						// Deaktiviere F5 (Aktualisieren / Reload)
						$(document).on("keydown", disableF5);

						// Merke "Seite verlassen"-Meldung
						var merkeSeiteVerlassen = 0;

						// Local Storage Status
						$('#localstoreState').html("<i class=\"fas fa-save\" style=\"color: #b94a48;\"></i>");

						$(document).bind("mouseleave", function(e) {
							// Zeige Hinweis nur initiativ oder, wenn nicht explizit markiert
							if(merkeSeiteVerlassen == 0) {
								// Hole Element zum Überprüfen, ob Container bereits sichtbar
								var display = $('.swal2-container').is(':visible');

								if((e.pageY - $(window).scrollTop() <= 1) && !display) {
									Swal.fire({
										allowOutsideClick: false,
										allowEscapeKey: false,
										type: 'question',
										title: 'Seite verlassen?',
										html: '<div style="font-size: small; padding: 0 45px 0 45px !important;"><table width="100%" cellspacing="0" cellpadding="5px" border="0" style="border-collapse: collapse;"><tr><td colspan="2">Wenn Sie diese Seite verlassen, gehen alle nicht hochgeladenen Ergebnisdaten verloren!</td></tr><tr><td colspan="2">&nbsp;</td></tr><tr style="width: 80%; font-size: small; color: #8e6516; border: 1px solid #8e6516; background-color: rgba(142, 102, 22, .25);"><td>Diesen Hinweis icht mehr anzeigen</td><td><div class="checkboxOne"><input type="checkbox" id="checkboxOneInput" /><label for="checkboxOneInput"></label></div></td></tr></table></div>',
										footer: 'Exportieren Sie Ihre Ergebnisdaten über den zugehörigen Button!',
										showConfirmButton: true,
										showCancelButton: false,
										confirmButtonText: 'Auf Seite bleiben'
									}).then(function(result) {
										if(result.value) {
											// Merke Entscheidung
											if($('#checkboxOneInput').is(':checked')) {
												merkeSeiteVerlassen = 1;
											}
										}
									});
								}
							}
						});

						Swal.fire({
							allowOutsideClick: false,
							allowEscapeKey: false,
							type: 'warning',
							title: 'Wichtige Anmerkung!',
							html: '<table width="100%" cellspacing="0" cellpadding="5px"><tr><td colspan="2">Dieses System wurde so konzipiert, um auch dann zu funktionieren, wenn Ihre Internetverbindung abbrechen sollte. Im Regelfall werden Ihre eingetragenen Ergebnisdaten alle 60 Sekunden hochgeladen. Sollte Ihre Internetverbindung abbrechen, informiert Sie das System hierüber und Sie haben die Möglichkeit bisherige, nicht hochgeladene Ergebnisdaten als CSV zu exportieren und zu einem späteren Zeitpunkt über den zugehörigen Menüpunkt zu importieren.</td></tr><tr><td>Lokales Speichern:</td><td><i style="color: #b94a48" class="fas fa-times"></i></td></tr><tr><td colspan="2">Ihr Browser verfügt <strong>nicht</strong> über die Möglichkeit einer lokalen Speicherung. Zu Ihrer Sicherheit wurde die F5 Taste (<i class="fas fa-redo"></i>) deaktiviert. Sollten Sie den Browser schließen oder diese Seite dennoch aktualisieren, so gehen Ihre eingetragenen, nicht hochgeladenen Ergebnisdaten verloren!</td></tr></table>',
							footer: 'Eine Aktualisierung Ihres Browsers wird dringend empfohlen!',
							showConfirmButton: true,
							confirmButtonText: '<i style=\"color: #fff;\" class=\"fas fa-redo\"></i>&emsp;Ich bin mir der Risiken bewusst und habe diese verstanden!',
							confirmButtonColor: '#8e6516',
							backdrop:	`
								rgba(162,166,164,.75)
								center
								no-repeat
							`
						});
					}

					// localStorage.clear();

					$('#store').click(function() {
						// Hole Werte der Eingabe
						var startnummer = $("#startnummer").val();
						var ergebnis = $("#ergebnis").val();
						var position = $("#position").val();

						// Entferne required Attribut (verhindert roten Rand nach Absenden)
						$("#startnummer").removeAttr("required");
						$("#ergebnis").removeAttr("required");
						$("#position").removeAttr("required");

						// Setze Eingabewerte zurück
						$("#startnummer").val("");
						$("#ergebnis").val("");
						$("#position").val("");

						// Füge required Attribut wieder hinzu
						$("#startnummer").attr("required");
						$("#ergebnis").attr("required");
						$("#position").attr("required");

						var ergebnisdaten = startnummer + ";" + ergebnis + ";" + position;
						var key = startnummer + "." + position;

						// Suche vorab nach bereits vorhandenem Ergebnis
						var aktion = "";

						if(speicherStatus === 1) {
							if(ergebnisspeicher.getItem(key) === null) {
								// Element war noch nicht vorhanden
								aktion = "gespeichert";
							} else {
								// Element war bereits vorhanden
								aktion = "überschrieben";
							}

							// Setze Ergebnis
							ergebnisspeicher.setItem(key, ergebnisdaten);

							// Ergebnis konnte nicht gesetzt werden
							if(ergebnisspeicher.getItem(key) === null) {
								// Gebe Meldung aus
								$.notify("Ergebnis konnte nicht " + aktion + " werden!", errorOptions);

								// Debugging
								console.log(ergebnisspeicher);
							} else {
								if(aktion == "überschrieben") {
									// Lösche aktuelles Ergebnis
									ergebnisspeicher.removeItem(key);
								}

								// Setze (neues) Ergebnis
								ergebnisspeicher[key] = ergebnisdaten;

								// Gebe Meldung aus
								$.notify("Ergebnis " + aktion + "!", successOptions);

								// Debugging
								console.log(ergebnisspeicher);
							}
						} else if(speicherStatus === 0) {
							if(ergebnisspeicher.includes(key) === false) {
								// Element war noch nicht vorhanden
								aktion = "gespeichert";
							} else {
								// Element war bereits vorhanden
								aktion = "überschrieben";
							}

							// Setze Ergebnis
							ergebnisspeicher[setItem] = ergebnisdaten;

							// Ergebnis konnte nicht gesetzt werden
							if(ergebnisspeicher.includes(key) === false) {
								// Gebe Meldung aus
								$.notify("Ergebnis konnte nicht " + aktion + " werden!", errorOptions);

								// Debugging
								console.log(ergebnisspeicher);
							} else {
								if(aktion == "überschrieben") {
									// Lösche aktuelles Ergebnis
									delete ergebnisspeicher[key];
								}

								// Setze (neues) Ergebnis
								ergebnisspeicher[key] = ergebnisdaten;

								// Gebe Meldung aus
								$.notify("Ergebnis " + aktion + "!", successOptions);

								// Debugging
								console.log(ergebnisspeicher);
							}
						}
					});

					setTimeout(function() {
						connectivity(0, 0, lsTest(), ergebnisspeicher);
					}, 20000);


					$('#hide_team_status').hide();
					$('#teilnehmerStatus').hide();

					$('#show_team_status').click(function() {
						$('#show_team_status').hide();
						$('#hide_team_status').show();
						$('#teilnehmerStatus').show();
					});
					$('#hide_team_status').click(function() {
						$('#hide_team_status').hide();
						$('#teilnehmerStatus').hide();
						$('#show_team_status').show();
					});


					// INITIAL FETCH
					$.ajax({
						type: 'POST',
						url: 'timebuddy_state.php',
						success: function(html) {
							$('#teilnehmerStatus').html(html);
						}
					});

					// AUTO-FETCH EVERY 10 SECONDS
					function auto_fetch() {
						$.ajax({
							type: 'POST',
							url: 'timebuddy_state.php',
							success: function(html) {
								$('#teilnehmerStatus').html(html);
							}
						});
					}

					// INTERVAL FOR FETCHING EVERY 10 SECONDS
					setInterval(function() {
						auto_fetch();
					}, 10000);

					// INITIALLY BLOCK SUBMIT BUTTON
					$('#store').prop("disabled", true);

					// CHECK FOR CORRECT TIME PATTERN
					$("#ergebnis").keyup(function() {
						// Hole Ergebnis
						var ergzeit = this.value;

						var zeitFormat = new RegExp('^<?php echo $inp_pattern; ?>$');

						// Hole Startnummer
						var snr = $('#startnummer').val();

						// Hole ausgewählte Prüfungsposition
						var prpos = $('#position').val();

						// TEST IF CORRECT FORMAT
						if(zeitFormat.test(ergzeit) && snr > 0 && (prpos !== "" || prpos !== 'none')) {
							$('#store').prop("disabled", false);
						} else {
							$('#store').prop("disabled", true);
						}
					});

					$("#startnummer, #position, #ergebnis").change(function() {
						// Hole Ergebniszeit
						var ergzeit = this.value;

						var zeitFormat = new RegExp('^<?php echo $inp_pattern; ?>$');

						// Hole Startnummer
						var snr = $('#startnummer').val();

						// Hole ausgewählte Prüfungsposition
						var prpos = $('#position').val();

						// TEST IF CORRECT FORMAT
						if(zeitFormat.test(ergzeit) && snr > 0 && (prpos !== "" || prpos !== 'none')) {
							$('#store').prop("disabled", false);
						} else {
							$('#store').prop("disabled", true);
						}
					});

					// INPUT MASK FOR store
					<?php
						echo $result_mask;
					?>

					// INITIALLY DISABLE SELECT FIELD
					$('#position').prop('disabled', true);

					// CHECK IF INPUT IS SELECT FIELD AND NOT INPUT
					if($('#position').is("select")) {
						// PRE-SELECT TPOS BASED ON TMEMBER
						$('#startnummer')
						.change(function() {
							// DECLARE TMEMBER ID VARIABLE
							var sid = $('#startnummer').val();

							// DISABLE EVERY OPTION FROM PREVIOUS INPUT
							$('#position').val('none').prop("selected", true);
							$('#position').val('none').find(':selected').nextAll().prop("disabled", true);

							// CHECK WHETHER INPUT IS NUMERIC OR NOT
							if(sid.length == 0 || sid == 0 || !$.isNumeric(sid)) {
								$('#position').prop("disabled", true);
								$('#position').val('none').prop("selected", true);
								$('#position').val('none').find(':selected').nextAll().prop("disabled", true);
								// INPUT IS NUMERIC AND VALID --> PROCEED
							} else {
								// DECLARE EVENT ID AND ROUND ID BASED ON PHP VARIABLES
								var eid = <?php echo $eid; ?>;
								var zid = <?php echo $uid; ?>;
								var rid = <?php echo $rid; ?>;
								var pos = "<?php echo $zpos; ?>";

								// AJAX REQUEST
								$.ajax({
									type: 'POST',
									url: 'timebuddy_fetch_tpos.php',
									data:	{
										rid: rid,
										eid: eid,
										zid: zid,
										sid: sid,
										pos: pos
									},
									success: function(data){
										if(data !== "incomplete") {
											if(data !== "no_result") {
												// Aktiviere Eingabefeld
												$('#position').prop("disabled", false);

												// Verstecke Platzhalter Option
												$("#position option[value='none']").hide();

												// Aktiviere Auswahlfeld mit vorbelegtem Rückgabewert
												$("#position option[value='" + data + "']").prop("disabled", false);
												$('#position').val(data).prop("selected", true);
												$('#position').val(data).find(':selected').prevAll().prop('disabled', false);
												$('#ergebnis').prop("disabled", false);
											} else if(data == "no_result") {
												// BLOCK SELECT AND TIME INPUT
												$('#position').prop("disabled", true);
												$("#position option[value='none']").text("Kein Teilnehmer");
												$('#ergebnis').prop("disabled", true);
											}
										} else {
											// Übergabeparameter unvollständig
											// Blocke alle Eingabemöglichkeiten
											$('#position').prop("disabled", true);
											$("#position option[value='none']").text("Kritischer Fehler");
											$('#ergebnis').prop("disabled", true);
										}
									}
								});
							}
						})
						.keyup(delay(function(e) {
							// DECLARE TMEMBER ID VARIABLE
							var sid = $('#startnummer').val();

							// DISABLE EVERY OPTION FROM PREVIOUS INPUT
							$('#position').val('none').prop("selected", true);
							$('#position').val('none').find(':selected').nextAll().prop("disabled", true);

							// CHECK WHETHER INPUT IS NUMERIC OR NOT
							if(sid.length == 0 || sid == 0 || !$.isNumeric(sid)) {
								$('#position').prop("disabled", true);
								$('#position').val('none').prop("selected", true);
								$('#position').val('none').find(':selected').nextAll().prop("disabled", true);
								// INPUT IS NUMERIC AND VALID --> PROCEED
							} else {
								// DECLARE EVENT ID AND ROUND ID BASED ON PHP VARIABLES
								var eid = <?php echo $eid; ?>;
								var zid = <?php echo $uid; ?>;
								var rid = <?php echo $rid; ?>;
								var pos = '<?php echo $zpos; ?>';

								// AJAX REQUEST
								$.ajax({
									type: 'POST',
									url: 'timebuddy_fetch_tpos.php',
									data:	{
										rid: rid,
										eid: eid,
										zid: zid,
										sid: sid,
										pos: pos
									},
									success: function(data){
										if(data !== "incomplete") {
											if(data !== "no_result") {
												// Aktiviere Eingabefeld
												$('#position').prop("disabled", false);

												// Verstecke Platzhalter Option
												$("#position option[value='none']").hide();

												// Aktiviere Auswahlfeld mit vorbelegtem Rückgabewert
												$("#position option[value='" + data + "']").prop("disabled", false);
												$('#position').val(data).prop("selected", true);
												$('#position').val(data).find(':selected').prevAll().prop('disabled', false);
												$('#ergebnis').prop("disabled", false);
											} else if(data == "no_result") {
												// BLOCK SELECT AND TIME INPUT
												$('#position').prop("disabled", true);
												$("#position option[value='none']").text("Kein Teilnehmer");
												$('#ergebnis').prop("disabled", true);
											}
										} else {
											// Übergabeparameter unvollständig
											// Blocke alle Eingabemöglichkeiten
											$('#position').prop("disabled", true);
											$("#position option[value='none']").text("Kritischer Fehler");
											$('#ergebnis').prop("disabled", true);
										}
									}
								});
							}
						}, 500));
					}
				});

				// Erstelle Globals für Notify
				var successOptions = {
					globalPosition: 'bottom left',
					style: 'bootstrap',
					clickToHide: false,
					autoHide: true,
					autoHideDelay: 10000,
					showAnimation: 'slideDown',
					hideAnimation: "slideUp",
					showDuration: 500,
					hideDuration: 500,
					arrowShow: false,
					className: "success",
					gap: 2
				};

				var warnOptions = {
					globalPosition: 'bottom left',
					style: 'bootstrap',
					clickToHide: false,
					autoHide: true,
					autoHideDelay: 10000,
					showAnimation: 'slideDown',
					hideAnimation: "slideUp",
					showDuration: 500,
					hideDuration: 500,
					arrowShow: false,
					className: "warn",
					gap: 2
				};

				var infoOptions = {
					globalPosition: 'bottom left',
					style: 'bootstrap',
					clickToHide: false,
					autoHide: true,
					autoHideDelay: 10000,
					showAnimation: 'slideDown',
					hideAnimation: "slideUp",
					showDuration: 500,
					hideDuration: 500,
					arrowShow: false,
					className: "info",
					gap: 2
				};

				var errorOptions = {
					globalPosition: 'bottom left',
					style: 'bootstrap',
					clickToHide: false,
					autoHide: true,
					autoHideDelay: 10000,
					showAnimation: 'slideDown',
					hideAnimation: "slideUp",
					showDuration: 500,
					hideDuration: 500,
					arrowShow: false,
					className: "error",
					gap: 2
				};

				// localStorage verfügbar
				function lsTest() {
					var test = 'test';
					try {
						localStorage.setItem(test, test);
						localStorage.removeItem(test);
						return true;
					} catch(e) {
						return false;
					}
				}

				/* ping.js - v0.2.2 http://github.com/alfg/ping.js */
				var Ping = function(a) {
					this.opt = a || {},
					this.favicon = this.opt.favicon || "/favicon.ico",
					this.timeout = this.opt.timeout || 0,
					this.logError = this.opt.logError || !1
				};

				Ping.prototype.ping = function(a,b) {
					function c(a) {
						f.wasSuccess =! 0,
						e.call(f,a)
					}

					function d(a) {
						f.wasSuccess =! 1,
						e.call(f,a)
					}

					function e() {
						g && clearTimeout(g);
						var a = new Date - h;

						if("function" == typeof b)
						return this.wasSuccess?b(null,a):(f.logError&&console.error("Ressourcen Ladefehler"), b("error",a))
					}

					var f = this;
					f.wasSuccess =! 1,
					f.img = new Image,
					f.img.onload = c,
					f.img.onerror = d;
					var g,h = new Date;
					f.timeout && (g = setTimeout(function() {
						e.call(f,void 0)
					}, f.timeout)),
					f.img.src = a + f.favicon + "?" +  + new Date
				},
				"undefined" != typeof exports ? "undefined" != typeof module && module.exports && (module.exports = Ping):window.Ping = Ping;

				(function(e){typeof define=="function"&&define.amd?define(["jquery"],e):typeof module=="object"&&module.exports?module.exports=function(t,n){return n===undefined&&(typeof window!="undefined"?n=require("jquery"):n=require("jquery")(t)),e(n),n}:e(jQuery)})(function(e){function A(t,n,i){typeof i=="string"&&(i={className:i}),this.options=E(w,e.isPlainObject(i)?i:{}),this.loadHTML(),this.wrapper=e(h.html),this.options.clickToHide&&this.wrapper.addClass(r+"-hidable"),this.wrapper.data(r,this),this.arrow=this.wrapper.find("."+r+"-arrow"),this.container=this.wrapper.find("."+r+"-container"),this.container.append(this.userContainer),t&&t.length&&(this.elementType=t.attr("type"),this.originalElement=t,this.elem=N(t),this.elem.data(r,this),this.elem.before(this.wrapper)),this.container.hide(),this.run(n)}var t=[].indexOf||function(e){for(var t=0,n=this.length;t<n;t++)if(t in this&&this[t]===e)return t;return-1},n="notify",r=n+"js",i=n+"!blank",s={t:"top",m:"middle",b:"bottom",l:"left",c:"center",r:"right"},o=["l","c","r"],u=["t","m","b"],a=["t","b","l","r"],f={t:"b",m:null,b:"t",l:"r",c:null,r:"l"},l=function(t){var n;return n=[],e.each(t.split(/\W+/),function(e,t){var r;r=t.toLowerCase().charAt(0);if(s[r])return n.push(r)}),n},c={},h={name:"core",html:'<div class="'+r+'-wrapper">\n	<div class="'+r+'-arrow"></div>\n	<div class="'+r+'-container"></div>\n</div>',css:"."+r+"-corner {\n	position: fixed;\n	margin: 5px;\n	z-index: 1050;\n}\n\n."+r+"-corner ."+r+"-wrapper,\n."+r+"-corner ."+r+"-container {\n	position: relative;\n	display: block;\n	height: inherit;\n	width: inherit;\n	margin: 3px;\n}\n\n."+r+"-wrapper {\n	z-index: 1;\n	position: absolute;\n	display: inline-block;\n	height: 0;\n	width: 0;\n}\n\n."+r+"-container {\n	display: none;\n	z-index: 1;\n	position: absolute;\n}\n\n."+r+"-hidable {\n	cursor: pointer;\n}\n\n[data-notify-text],[data-notify-html] {\n	position: relative;\n}\n\n."+r+"-arrow {\n	position: absolute;\n	z-index: 2;\n	width: 0;\n	height: 0;\n}"},p={"border-radius":["-webkit-","-moz-"]},d=function(e){return c[e]},v=function(e){if(!e)throw"Missing Style name";c[e]&&delete c[e]},m=function(t,i){if(!t)throw"Missing Style name";if(!i)throw"Missing Style definition";if(!i.html)throw"Missing Style HTML";var s=c[t];s&&s.cssElem&&(window.console&&console.warn(n+": overwriting style '"+t+"'"),c[t].cssElem.remove()),i.name=t,c[t]=i;var o="";i.classes&&e.each(i.classes,function(t,n){return o+="."+r+"-"+i.name+"-"+t+" {\n",e.each(n,function(t,n){return p[t]&&e.each(p[t],function(e,r){return o+="	"+r+t+": "+n+";\n"}),o+="	"+t+": "+n+";\n"}),o+="}\n"}),i.css&&(o+="/* styles for "+i.name+" */\n"+i.css),o&&(i.cssElem=g(o),i.cssElem.attr("id","notify-"+i.name));var u={},a=e(i.html);y("html",a,u),y("text",a,u),i.fields=u},g=function(t){var n,r,i;r=x("style"),r.attr("type","text/css"),e("head").append(r);try{r.html(t)}catch(s){r[0].styleSheet.cssText=t}return r},y=function(t,n,r){var s;return t!=="html"&&(t="text"),s="data-notify-"+t,b(n,"["+s+"]").each(function(){var n;n=e(this).attr(s),n||(n=i),r[n]=t})},b=function(e,t){return e.is(t)?e:e.find(t)},w={clickToHide:!0,autoHide:!0,autoHideDelay:5e3,arrowShow:!0,arrowSize:5,breakNewLines:!0,elementPosition:"bottom",globalPosition:"bottom left",style:"bootstrap",className:"error",showAnimation:"slideDown",showDuration:400,hideAnimation:"slideUp",hideDuration:200,gap:5},E=function(t,n){var r;return r=function(){},r.prototype=t,e.extend(!0,new r,n)},S=function(t){return e.extend(w,t)},x=function(t){return e("<"+t+"></"+t+">")},T={},N=function(t){var n;return t.is("[type=radio]")&&(n=t.parents("form:first").find("[type=radio]").filter(function(n,r){return e(r).attr("name")===t.attr("name")}),t=n.first()),t},C=function(e,t,n){var r,i;if(typeof n=="string")n=parseInt(n,10);else if(typeof n!="number")return;if(isNaN(n))return;return r=s[f[t.charAt(0)]],i=t,e[r]!==undefined&&(t=s[r.charAt(0)],n=-n),e[t]===undefined?e[t]=n:e[t]+=n,null},k=function(e,t,n){if(e==="l"||e==="t")return 0;if(e==="c"||e==="m")return n/2-t/2;if(e==="r"||e==="b")return n-t;throw"Invalid alignment"},L=function(e){return L.e=L.e||x("div"),L.e.text(e).html()};A.prototype.loadHTML=function(){var t;t=this.getStyle(),this.userContainer=e(t.html),this.userFields=t.fields},A.prototype.show=function(e,t){var n,r,i,s,o;r=function(n){return function(){!e&&!n.elem&&n.destroy();if(t)return t()}}(this),o=this.container.parent().parents(":hidden").length>0,i=this.container.add(this.arrow),n=[];if(o&&e)s="show";else if(o&&!e)s="hide";else if(!o&&e)s=this.options.showAnimation,n.push(this.options.showDuration);else{if(!!o||!!e)return r();s=this.options.hideAnimation,n.push(this.options.hideDuration)}return n.push(r),i[s].apply(i,n)},A.prototype.setGlobalPosition=function(){var t=this.getPosition(),n=t[0],i=t[1],o=s[n],u=s[i],a=n+"|"+i,f=T[a];if(!f||!document.body.contains(f[0])){f=T[a]=x("div");var l={};l[o]=0,u==="middle"?l.top="45%":u==="center"?l.left="45%":l[u]=0,f.css(l).addClass(r+"-corner"),e("body").append(f)}return f.prepend(this.wrapper)},A.prototype.setElementPosition=function(){var n,r,i,l,c,h,p,d,v,m,g,y,b,w,E,S,x,T,N,L,A,O,M,_,D,P,H,B,j;H=this.getPosition(),_=H[0],O=H[1],M=H[2],g=this.elem.position(),d=this.elem.outerHeight(),y=this.elem.outerWidth(),v=this.elem.innerHeight(),m=this.elem.innerWidth(),j=this.wrapper.position(),c=this.container.height(),h=this.container.width(),T=s[_],L=f[_],A=s[L],p={},p[A]=_==="b"?d:_==="r"?y:0,C(p,"top",g.top-j.top),C(p,"left",g.left-j.left),B=["top","left"];for(w=0,S=B.length;w<S;w++)D=B[w],N=parseInt(this.elem.css("margin-"+D),10),N&&C(p,D,N);b=Math.max(0,this.options.gap-(this.options.arrowShow?i:0)),C(p,A,b);if(!this.options.arrowShow)this.arrow.hide();else{i=this.options.arrowSize,r=e.extend({},p),n=this.userContainer.css("border-color")||this.userContainer.css("border-top-color")||this.userContainer.css("background-color")||"white";for(E=0,x=a.length;E<x;E++){D=a[E],P=s[D];if(D===L)continue;l=P===T?n:"transparent",r["border-"+P]=i+"px solid "+l}C(p,s[L],i),t.call(a,O)>=0&&C(r,s[O],i*2)}t.call(u,_)>=0?(C(p,"left",k(O,h,y)),r&&C(r,"left",k(O,i,m))):t.call(o,_)>=0&&(C(p,"top",k(O,c,d)),r&&C(r,"top",k(O,i,v))),this.container.is(":visible")&&(p.display="block"),this.container.removeAttr("style").css(p);if(r)return this.arrow.removeAttr("style").css(r)},A.prototype.getPosition=function(){var e,n,r,i,s,f,c,h;h=this.options.position||(this.elem?this.options.elementPosition:this.options.globalPosition),e=l(h),e.length===0&&(e[0]="b");if(n=e[0],t.call(a,n)<0)throw"Must be one of ["+a+"]";if(e.length===1||(r=e[0],t.call(u,r)>=0)&&(i=e[1],t.call(o,i)<0)||(s=e[0],t.call(o,s)>=0)&&(f=e[1],t.call(u,f)<0))e[1]=(c=e[0],t.call(o,c)>=0)?"m":"l";return e.length===2&&(e[2]=e[1]),e},A.prototype.getStyle=function(e){var t;e||(e=this.options.style),e||(e="default"),t=c[e];if(!t)throw"Missing style: "+e;return t},A.prototype.updateClasses=function(){var t,n;return t=["base"],e.isArray(this.options.className)?t=t.concat(this.options.className):this.options.className&&t.push(this.options.className),n=this.getStyle(),t=e.map(t,function(e){return r+"-"+n.name+"-"+e}).join(" "),this.userContainer.attr("class",t)},A.prototype.run=function(t,n){var r,s,o,u,a;e.isPlainObject(n)?e.extend(this.options,n):e.type(n)==="string"&&(this.options.className=n);if(this.container&&!t){this.show(!1);return}if(!this.container&&!t)return;s={},e.isPlainObject(t)?s=t:s[i]=t;for(o in s){r=s[o],u=this.userFields[o];if(!u)continue;u==="text"&&(r=L(r),this.options.breakNewLines&&(r=r.replace(/\n/g,"<br/>"))),a=o===i?"":"="+o,b(this.userContainer,"[data-notify-"+u+a+"]").html(r)}this.updateClasses(),this.elem?this.setElementPosition():this.setGlobalPosition(),this.show(!0),this.options.autoHide&&(clearTimeout(this.autohideTimer),this.autohideTimer=setTimeout(this.show.bind(this,!1),this.options.autoHideDelay))},A.prototype.destroy=function(){this.wrapper.data(r,null),this.wrapper.remove()},e[n]=function(t,r,i){return t&&t.nodeName||t.jquery?e(t)[n](r,i):(i=r,r=t,new A(null,r,i)),t},e.fn[n]=function(t,n){return e(this).each(function(){var i=N(e(this)).data(r);i&&i.destroy();var s=new A(e(this),t,n)}),this},e.extend(e[n],{defaults:S,addStyle:m,removeStyle:v,pluginOptions:w,getStyle:d,insertCSS:g}),m("bootstrap",{html:"<div>\n<span data-notify-text></span>\n</div>",classes:{base:{"font-weight":"bold",padding:"8px 15px 8px 14px","text-shadow":"0 1px 0 rgba(255, 255, 255, 0.5)","background-color":"#fcf8e3",border:"1px solid #fbeed5","border-radius":"4px","white-space":"nowrap","padding-left":"25px","background-repeat":"no-repeat","background-position":"3px 7px"},error:{color:"#B94A48","background-color":"#F2DEDE","border-color":"#EED3D7","background-image":"url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAtRJREFUeNqkVc1u00AQHq+dOD+0poIQfkIjalW0SEGqRMuRnHos3DjwAH0ArlyQeANOOSMeAA5VjyBxKBQhgSpVUKKQNGloFdw4cWw2jtfMOna6JOUArDTazXi/b3dm55socPqQhFka++aHBsI8GsopRJERNFlY88FCEk9Yiwf8RhgRyaHFQpPHCDmZG5oX2ui2yilkcTT1AcDsbYC1NMAyOi7zTX2Agx7A9luAl88BauiiQ/cJaZQfIpAlngDcvZZMrl8vFPK5+XktrWlx3/ehZ5r9+t6e+WVnp1pxnNIjgBe4/6dAysQc8dsmHwPcW9C0h3fW1hans1ltwJhy0GxK7XZbUlMp5Ww2eyan6+ft/f2FAqXGK4CvQk5HueFz7D6GOZtIrK+srupdx1GRBBqNBtzc2AiMr7nPplRdKhb1q6q6zjFhrklEFOUutoQ50xcX86ZlqaZpQrfbBdu2R6/G19zX6XSgh6RX5ubyHCM8nqSID6ICrGiZjGYYxojEsiw4PDwMSL5VKsC8Yf4VRYFzMzMaxwjlJSlCyAQ9l0CW44PBADzXhe7xMdi9HtTrdYjFYkDQL0cn4Xdq2/EAE+InCnvADTf2eah4Sx9vExQjkqXT6aAERICMewd/UAp/IeYANM2joxt+q5VI+ieq2i0Wg3l6DNzHwTERPgo1ko7XBXj3vdlsT2F+UuhIhYkp7u7CarkcrFOCtR3H5JiwbAIeImjT/YQKKBtGjRFCU5IUgFRe7fF4cCNVIPMYo3VKqxwjyNAXNepuopyqnld602qVsfRpEkkz+GFL1wPj6ySXBpJtWVa5xlhpcyhBNwpZHmtX8AGgfIExo0ZpzkWVTBGiXCSEaHh62/PoR0p/vHaczxXGnj4bSo+G78lELU80h1uogBwWLf5YlsPmgDEd4M236xjm+8nm4IuE/9u+/PH2JXZfbwz4zw1WbO+SQPpXfwG/BBgAhCNZiSb/pOQAAAAASUVORK5CYII=)"},success:{color:"#468847","background-color":"#DFF0D8","border-color":"#D6E9C6","background-image":"url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAutJREFUeNq0lctPE0Ecx38zu/RFS1EryqtgJFA08YCiMZIAQQ4eRG8eDGdPJiYeTIwHTfwPiAcvXIwXLwoXPaDxkWgQ6islKlJLSQWLUraPLTv7Gme32zoF9KSTfLO7v53vZ3d/M7/fIth+IO6INt2jjoA7bjHCJoAlzCRw59YwHYjBnfMPqAKWQYKjGkfCJqAF0xwZjipQtA3MxeSG87VhOOYegVrUCy7UZM9S6TLIdAamySTclZdYhFhRHloGYg7mgZv1Zzztvgud7V1tbQ2twYA34LJmF4p5dXF1KTufnE+SxeJtuCZNsLDCQU0+RyKTF27Unw101l8e6hns3u0PBalORVVVkcaEKBJDgV3+cGM4tKKmI+ohlIGnygKX00rSBfszz/n2uXv81wd6+rt1orsZCHRdr1Imk2F2Kob3hutSxW8thsd8AXNaln9D7CTfA6O+0UgkMuwVvEFFUbbAcrkcTA8+AtOk8E6KiQiDmMFSDqZItAzEVQviRkdDdaFgPp8HSZKAEAL5Qh7Sq2lIJBJwv2scUqkUnKoZgNhcDKhKg5aH+1IkcouCAdFGAQsuWZYhOjwFHQ96oagWgRoUov1T9kRBEODAwxM2QtEUl+Wp+Ln9VRo6BcMw4ErHRYjH4/B26AlQoQQTRdHWwcd9AH57+UAXddvDD37DmrBBV34WfqiXPl61g+vr6xA9zsGeM9gOdsNXkgpEtTwVvwOklXLKm6+/p5ezwk4B+j6droBs2CsGa/gNs6RIxazl4Tc25mpTgw/apPR1LYlNRFAzgsOxkyXYLIM1V8NMwyAkJSctD1eGVKiq5wWjSPdjmeTkiKvVW4f2YPHWl3GAVq6ymcyCTgovM3FzyRiDe2TaKcEKsLpJvNHjZgPNqEtyi6mZIm4SRFyLMUsONSSdkPeFtY1n0mczoY3BHTLhwPRy9/lzcziCw9ACI+yql0VLzcGAZbYSM5CCSZg1/9oc/nn7+i8N9p/8An4JMADxhH+xHfuiKwAAAABJRU5ErkJggg==)"},info:{color:"#3A87AD","background-color":"#D9EDF7","border-color":"#BCE8F1","background-image":"url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3QYFAhkSsdes/QAAA8dJREFUOMvVlGtMW2UYx//POaWHXg6lLaW0ypAtw1UCgbniNOLcVOLmAjHZolOYlxmTGXVZdAnRfXQm+7SoU4mXaOaiZsEpC9FkiQs6Z6bdCnNYruM6KNBw6YWewzl9z+sHImEWv+vz7XmT95f/+3/+7wP814v+efDOV3/SoX3lHAA+6ODeUFfMfjOWMADgdk+eEKz0pF7aQdMAcOKLLjrcVMVX3xdWN29/GhYP7SvnP0cWfS8caSkfHZsPE9Fgnt02JNutQ0QYHB2dDz9/pKX8QjjuO9xUxd/66HdxTeCHZ3rojQObGQBcuNjfplkD3b19Y/6MrimSaKgSMmpGU5WevmE/swa6Oy73tQHA0Rdr2Mmv/6A1n9w9suQ7097Z9lM4FlTgTDrzZTu4StXVfpiI48rVcUDM5cmEksrFnHxfpTtU/3BFQzCQF/2bYVoNbH7zmItbSoMj40JSzmMyX5qDvriA7QdrIIpA+3cdsMpu0nXI8cV0MtKXCPZev+gCEM1S2NHPvWfP/hL+7FSr3+0p5RBEyhEN5JCKYr8XnASMT0xBNyzQGQeI8fjsGD39RMPk7se2bd5ZtTyoFYXftF6y37gx7NeUtJJOTFlAHDZLDuILU3j3+H5oOrD3yWbIztugaAzgnBKJuBLpGfQrS8wO4FZgV+c1IxaLgWVU0tMLEETCos4xMzEIv9cJXQcyagIwigDGwJgOAtHAwAhisQUjy0ORGERiELgG4iakkzo4MYAxcM5hAMi1WWG1yYCJIcMUaBkVRLdGeSU2995TLWzcUAzONJ7J6FBVBYIggMzmFbvdBV44Corg8vjhzC+EJEl8U1kJtgYrhCzgc/vvTwXKSib1paRFVRVORDAJAsw5FuTaJEhWM2SHB3mOAlhkNxwuLzeJsGwqWzf5TFNdKgtY5qHp6ZFf67Y/sAVadCaVY5YACDDb3Oi4NIjLnWMw2QthCBIsVhsUTU9tvXsjeq9+X1d75/KEs4LNOfcdf/+HthMnvwxOD0wmHaXr7ZItn2wuH2SnBzbZAbPJwpPx+VQuzcm7dgRCB57a1uBzUDRL4bfnI0RE0eaXd9W89mpjqHZnUI5Hh2l2dkZZUhOqpi2qSmpOmZ64Tuu9qlz/SEXo6MEHa3wOip46F1n7633eekV8ds8Wxjn37Wl63VVa+ej5oeEZ/82ZBETJjpJ1Rbij2D3Z/1trXUvLsblCK0XfOx0SX2kMsn9dX+d+7Kf6h8o4AIykuffjT8L20LU+w4AZd5VvEPY+XpWqLV327HR7DzXuDnD8r+ovkBehJ8i+y8YAAAAASUVORK5CYII=)"},warn:{color:"#C09853","background-color":"#FCF8E3","border-color":"#FBEED5","background-image":"url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAMAAAC6V+0/AAABJlBMVEXr6eb/2oD/wi7/xjr/0mP/ykf/tQD/vBj/3o7/uQ//vyL/twebhgD/4pzX1K3z8e349vK6tHCilCWbiQymn0jGworr6dXQza3HxcKkn1vWvV/5uRfk4dXZ1bD18+/52YebiAmyr5S9mhCzrWq5t6ufjRH54aLs0oS+qD751XqPhAybhwXsujG3sm+Zk0PTwG6Shg+PhhObhwOPgQL4zV2nlyrf27uLfgCPhRHu7OmLgAafkyiWkD3l49ibiAfTs0C+lgCniwD4sgDJxqOilzDWowWFfAH08uebig6qpFHBvH/aw26FfQTQzsvy8OyEfz20r3jAvaKbhgG9q0nc2LbZxXanoUu/u5WSggCtp1anpJKdmFz/zlX/1nGJiYmuq5Dx7+sAAADoPUZSAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQfdBgUBGhh4aah5AAAAlklEQVQY02NgoBIIE8EUcwn1FkIXM1Tj5dDUQhPU502Mi7XXQxGz5uVIjGOJUUUW81HnYEyMi2HVcUOICQZzMMYmxrEyMylJwgUt5BljWRLjmJm4pI1hYp5SQLGYxDgmLnZOVxuooClIDKgXKMbN5ggV1ACLJcaBxNgcoiGCBiZwdWxOETBDrTyEFey0jYJ4eHjMGWgEAIpRFRCUt08qAAAAAElFTkSuQmCC)"}}}),e(function(){g(h.css).attr("id","core-notify"),e(document).on("click","."+r+"-hidable",function(t){e(this).trigger("notify-hide")}),e(document).on("notify-hide","."+r+"-wrapper",function(t){var n=e(this).data(r);n&&n.show(!1)})})})

					function connectivity(connectivityProblemThen, connectivityProblemNow, lsTestReturn, ergebnisdaten) {
						var	p = new Ping(),
						ls = lsTestReturn;

						p.ping("https://google.de", function(err, data) {
							if(connectivityProblemNow === 1) {
								$.notify("Verbindungsaufbau ...", infoOptions);
								$('#zepositionState').html("<i class=\"fas fa-map-marker-alt\" style=\"margin-right: 2px; color: #f3f10e;\" title=\"Verbindungsaufbau ...\"></i>");
								$('#connectionState').html("<i class=\"fas fa-wifi\" style=\"color: #f3f10e;\" title=\"Verbindungsaufbau ...\"></i>");
							}

							// Zeige Fehler, wenn vorhanden
							if(err) {
								// Korrigiere aktuellen Fehlerstand
								connectivityProblemThen = connectivityProblemNow;

								if(connectivityProblemNow === 0) {
									connectivityProblemNow = 1;
								}

								if(connectivityProblemThen === 0 && connectivityProblemNow === 1) {
									$.notify("Verbindungsabbruch! Sie sind offline ..", errorOptions);
								} else {
									setTimeout(function() {
										$.notify("Verbindung konnte nicht wiederhergestellt werden!", errorOptions);
									}, 2000);
								}

								var ls = false;

								// Verbindungsstatus
								$('#zepositionState').html("<i class=\"fas fa-map-marker-alt\" style=\"margin-right: 2px; color: #b94a48;\" title=\"Keine automatische Ergebnis-Positionsbestimmung!\"></i>");
								$('#connectionState').html("<i class=\"fas fa-wifi\" style=\"color: #b94a48;\" title=\"Sie sind nicht mit dem Internet verbunden!\"></i>");

								// Prüfe zusätzlich, ob alle Ergebnisdaten hochgeladen wurden
								if(ergebnisspeicher.length > 0) {
									// Prüfe, ob Verbindung vorhanden, ansonsten gebe Export Funktion aus
									$("#supportingText").html("<fieldset style=\"font-size: small; padding: 10px 10px 0 10px; border: 1px solid red;\"><legend style=\"padding: 0 5px 0 5px; margin: 0 5px 0 5px;\"><i class=\"fas fa-exclamation-triangle\" style=\"color: #b94a48\"></i></legend><i class=\"fas fa-wifi\" style=\"color: #b94a48;\" title=\"Sie sind mit dem Internet verbunden!\"></i>&emsp;<strong>Verbindungsabbruch!</strong><br /><br />Exportieren Sie etwaige nicht hochgeladene Ergebnisdaten. Ein späterer Upload erfolgt über die Menü-Option 'Zeiten hochladen'.</span><p style=\"width: 100% !important; margin-top: 15px;\"><button style=\"width: 100% !important; padding: 5px; margin-top: 5px;\" id=\"export\">Ergebnisdaten exportieren</button></p></fieldset>");

									// Exportiere Ergebnisdaten
									$('#export').click(function() {
										var keyMuster = new RegExp('^([1-9]{1}[0-9]{0,2})\.{1}([S][t][a][r][t]|[Z][Z]([1-9]{1}[0-9]{0,2})|[Z][i][e][l]){1}$'),
										valMuster = new RegExp('^[1-9]{1}[0-9]{0,2}\;{1}[1-9]{1}[0-9]{0,2}\:{1}([0-5][0-9]){1}\:{1}([0-5][0-9]){1}\,{1}[0-9][0-9]\;{1}([S][t][a][r][t]|[Z][Z]([1-9]{1}[0-9]{0,2})|[Z][i][e][l]){1}$');

										var csvContent = "data:text/csv;charset=utf-8,";

										$.each(ergebnisdaten, function(key, value) {
											if(keyMuster.test(key) && valMuster.test(value)) {
												csvContent += value + "\r\n";
											}
										});

										var encodedUri = encodeURI(csvContent),
										link = document.createElement("a");

										// Für späteres Löschen des temporär hinzugefügten DOM-Elements
										link.setAttribute("class", "tempHREF");
										link.setAttribute("href", encodedUri);
										link.setAttribute("download", "<?php echo "Exportdaten_" . date('Ymdhis', time()) . "_" . ($_SESSION['opt_whois'] != "" ? $_SESSION['opt_whois'] : $_SESSION['rid_type'] . $_SESSION['rid']) . ".csv"; ?>");

										// Workaround für Firefox
										document.body.appendChild(link);

										// Download triggern
										link.click();

										// Lösche temporär hinzugefügtes HREF-Element
										$('.tempHREF').remove();
									});
								} else {
									$("#supportingText").html("");
								}

								/*
								 * Automatische Ergebnis-Positionsbestimmung offline,
								 * also gebe Auswahlmöglichkeiten frei
								 */
								$('#position').prop('disabled', false);
								$('#position option').prop('disabled', false);
							} else {
								/*
								 * Automatische Ergebnis-Positionsbestimmung online,
								 * also sperre Auswahlmöglichkeiten wieder
								 */
								$('#position').prop('disabled', true);
								$('#position option').prop('disabled', true);

								// Korrigiere aktuellen Fehlerstand
								connectivityProblemThen = connectivityProblemNow;

								if(connectivityProblemNow === 1) {
									connectivityProblemNow = 0;
								}

								if(connectivityProblemThen === 1 && connectivityProblemNow === 0) {
									$.notify("Verbindung wiederhergestellt! Sie sind online!", successOptions);
									$('#zepositionState').html("<i class=\"fas fa-map-marker-alt\" style=\"margin-right: 2px; color: #468847;\" title=\"Automatische Ergebnis-Positionsbestimmung!\"></i>");
									$('#connectionState').html("<i class=\"fas fa-wifi\" style=\"color: #468847;\" title=\"Sie sind mit dem Internet verbunden!\"></i>");

									// Blende Export-Möglichkeit wieder aus
									$("#supportingText").html("");
								}

								setTimeout(function() {
									$.notify("Prüfe auf Ergebnisdaten ...", infoOptions);
								}, 2000);

								// Eigentlicher Upload der Ergebnisdaten
								if(pruefeInhalt(ergebnisdaten) === true) {
									setTimeout(function() {
										$.notify("Lade Ergebnisdaten hoch ...", infoOptions);

										ladeErgebnisdatenHoch(ergebnisdaten);
									}, 4000);
								} else {
									setTimeout(function() {
										$.notify("Keine Ergebnisdaten gefunden!", infoOptions);
									}, 4000);
								}
							}
						});

						setTimeout(function() {
							connectivity(connectivityProblemThen, connectivityProblemNow, lsTest(), ergebnisdaten);
						}, 30000);
					};

					// Prüfe auf Inhalt für Upload
					function pruefeInhalt(ergebnisse) {
						// Wenn Array Inhalt besitzt und
						if(typeof ergebnisse !== 'undefined' && ergebnisse.length > 0) {
							return true;
						} else {
							return false;
						}
					};

					function ladeErgebnisdatenHoch(ergebnisse) {
						// Lade Ergebnisdaten hoch
						$.ajax({
							url: "upload_results.php",
							type: "POST",
							data:	{
								eid: <?php echo $eid; ?>,
								rid: <?php echo $rid; ?>,
								zid: <?php echo $uid; ?>,
								ztype: '<?php echo $ztypeSprint; ?>',
								ergebnisdaten: JSON.stringify(ergebnisse)
							},
							dataType: "json",
							success: function(data) {
								var	strlen = new Array,
								ferror = new Array,
								failed = new Array;

								// Durchlaufe Schleife, um hochgeladene Ergebnisdaten
								for(var i = 0; i < data.length; i++) {
									console.log(data[i]);

									// Prüfe zuerst alle fehlerhaften Einträge und extrahiere diese
									// Fehlerhafte Länge
									if(data[i].indexOf('strlen#') !== -1) {
										// Splitte String
										var split = data[i].split('strlen#');

										// Füge Element Array hinzu
										strlen.push(split[1]);

										// Entferne Element aus Callback
										delete ergebnisspeicher[split[1]];
										// Allgemeiner Fehler
									} else if(data[i].indexOf('failed#') !== -1) {
										// Splitte String
										var split = data[i].split('failed#');

										// Füge Element Array hinzu
										failed.push(split[1]);

										// Entferne Element aus Callback
										delete ergebnisspeicher[split[1]];
										// Duplikat-Aktualisierungsfehler
									} else if(data[i].indexOf('ferror#') !== -1) {
										// Splitte String
										var split = data[i].split('ferror#');

										// Füge Element Array hinzu
										ferror.push(split[1]);

										// Entferne Element aus Callback
										delete ergebnisspeicher[split[1]];
										// Keine Fehler
									} else {
										// Entferne hochgeladene Ergebnisdaten aus Ergebnisspeicher
										delete ergebnisspeicher[data[i]];

										console.log(data[i]);
										console.log(ergebnisspeicher);

										console.log("Ergebnisspeicher: " + ergebnisspeicher);
									}
								}

								// Prüfe zusätzlich, ob alle Ergebnisdaten hochgeladen wurden
								if(ergebnisspeicher.length == 0) {
									setTimeout(function() {
										$.notify("Alle bisherigen Ergebnisdaten wurden hochgeladen!", successOptions);
									}, 2000);
								}

								console.log(ergebnisspeicher);

								/*
								Array
								(
								[0] => 1.Start
								[1] => 11.Start
								[2] => 66.Start
								[2] => strlen#99.Start
							)
							*/
						},
						// Fehler: gebe Möglichkeit zu Export aus
						error: function (jqXHR, exception) {
							var msg = '',
							cde = '';

							if(jqXHR.status === 0) {
								msg = 'Nicht verbunden. Überprüfen Sie das Netzwerk.';
								cde = 0;
							} else if(jqXHR.status == 404) {
								msg = 'Angeforderte Seite nicht gefunden.';
								cde = 404;
							} else if(jqXHR.status == 500) {
								msg = 'Interner Serverfehler.';
								cde = 500;
							} else if(exception === 'parsererror') {
								msg = 'Angeforderte JSON-Analyse fehlgeschlagen.';
								cde = parsererror;
							} else if(exception === 'timeout') {
								msg = 'Timeout-Fehler.';
								cde = timeout;
							} else if(exception === 'abort') {
								msg = 'Anfrage abgebrochen.';
								cde = abort;
							} else {
								msg = 'Nicht erfasster Fehler.';
								cde = jqXHR.responseText;
							}

							Swal.fire({
								allowOutsideClick: false,
								allowEscapeKey: false,
								allowEnterKey: false,
								type: 'error',
								title: 'Fehler ' + jqXHR.status,
								html: '<span class="purpur">Fehler: ' + msg + '</span>',
								footer: '<span style="font-size; small;">Sollte das Problem weiterhin bestehen, so kontaktieren Sie den Support unter dem aufgeführten Fehlecode!<br /><br /><em>Fehlercode: ' + cde + '/znUploadRequest</em></span>',
								showConfirmButton: true,
								confirmButtonText: '<i style="color: #fff;" class="fas fa-redo"></i>&emsp;Anfrage erneut senden',
								confirmButtonColor: 'rgb(145,11,67)',
								backdrop:	`
									linear-gradient(145deg, rgba(205,205,205,.75), rgba(173,173,173,.75))
									center
									no-repeat
								`
							}).then((result) => {
								if (result.value) {
									// Erneuter Versuch Ergebnisdaten hochzuladend
									ladeErgebnisdatenHoch(ergebnisdaten);
								}
							});
						}
					});
				}

				function disableF5(e) {
					if((e.which || e.keyCode) == 116) {
						e.preventDefault();

						// Gebe Meldung über gesperrten F5-Button aus
						$.notify("Verlust von Ergebnisdaten durch Aktualisieren verhindert!", errorOptions);

						setTimeout(function() {
							$.notify("Vermeiden Sie jegliches Aktualisieren oder Verlassen der Seite!", infoOptions);
						}, 2000);
					};
				};

				function delay(callback, ms) {
					var timer = 0;
					return function() {
						var context = this, args = arguments;
						clearTimeout(timer);
						timer = setTimeout(function () {
							callback.apply(context, args);
						}, ms || 0);
					};
				}
			});
		</script>
	</body>
</html>
