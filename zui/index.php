<?php
	// Debugging
	error_reporting(E_ALL);

	// Lege Zeitzone fest
	date_default_timezone_set("Europe/Berlin");

	// Binde Funktionsdateien ein
	include_once '../includes/functions.php';

	// Binde Konfigurationsdatei ein
	include_once '../includes/db_connect.php';

	// Prüfe, ob Session bereits gestartet wurde
	// PHP Version < 5.4.0
	if(session_id() == '') {
		session_start();
	}
	// PHP Version > 5.4.0, 7
	/*
	if(session_status() == PHP_SESSION_NONE) {
		session_start();
	}
	*/

	// Heller oder dunkler Modus
	if(isset($_COOKIE['darkmode'])) {
		$reload = "";
		$darkmode = $_COOKIE['darkmode'];
	} else {
		setcookie('darkmode', 0, time() + (86400 * 30), "/");

		// Lade Seite erneut, um Cookie zu validieren
		$reload = "<script>window.location.reload(true);</script>";
	}

	// Test
	$_SESSION['eid'] = 1;
	$_SESSION['uid'] = 37;
	$_SESSION['rid_type'] = "GP";
	$_SESSION['rid'] = 1;
	$_SESSION['opt_whois'] = "";
	$_SESSION['logtype'] = "mz";

	$error = "";

	// Benutzer ist eingeloggt
	if(isset($_SESSION['uid']) and $_SESSION['uid'] != "") {
		// Validiere Session-Informationen
		$eid				= $_SESSION['eid'];
		$uid				= $_SESSION['uid'];
		$rid_type		= $_SESSION['rid_type'];
		$rid				= $_SESSION['rid'];
		$opt_whois	= $_SESSION['opt_whois'];
		$logtype    = $_SESSION['logtype'];

		// Prüfe Zugangsberechtigung und leite ggf. weiter
		if($logtype !== "mz") {
			switch ($logtype) {
				//  Zeitkontrolle
				case "zk":
					header("Location: ../zcontrol.php");
				break;
				//  Stempelkontrolle
				case "zs":
					header("Location: ../zstamp.php");
				break;
				//  Bordkartenkontrolle
				case "bc":
					header("Location: ../boarding.php");
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
										";

					if($darkmode == 0) {
						$error .= "confirmButtonText: '<span style=\"color:#fff!important;\"><i class=\"fas fa-redo\"></i>&emsp;Seite verlassen</span>',";
					} else {
						$error .= "confirmButtonText: '<span style=\"color:#f5eee1!important;\"><i class=\"fas fa-redo\"></i>&emsp;Seite verlassen</span>',";
					}

					$error .=		"
											confirmButtonColor: '#b94a48',
											backdrop:	`
											";
					if($darkmode == 0) {
						$error .= "#ede7da";
					} else {
						$error .= "#404040";
					}

					$error .=		"
												center
												no-repeat
											`
										}).then((result) => {
											if(result.value) {
												location.href = \"../index.php\";
											}
										});
										";
				break;
			}
		}

		// Hole alle zugewiesenen Positionen
		$select = "SELECT DISTINCT(`pos`) FROM `_optio_zpositions` WHERE `zid` = '" . $uid . "' AND `rid` = '" . $rid . "'";
		$result = mysqli_query($mysqli, $select);
		$numrow = mysqli_num_rows($result);

		if($numrow > 0) {
			$zpos = "";

			while($getrow = mysqli_fetch_assoc($result)) {
				$zpos .= $getrow['pos'] . ":";
			}

			// Prüfe, ob es sich bei der hinterlegten Prüfung um Sprint handelt
			if(strpos("Sprint", $zpos) === true) {
				$result_mask = '$("#ergebnis").mask("99:99,99",{placeholder:"MM:SS,00"});';
				$inp_pattern = '([0-5][0-9]:[0-5][0-9],[0-9][0-9]){1}';
				$ergebnisPlatzhalter = 'MM:SS,00';
				$result_form = 8;
			} else {
				$result_mask = '$("#ergebnis").mask("99:99:99,99",{placeholder:"HH:MM:SS,00"});';
				$inp_pattern = '(([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9],[0-9][0-9]){1}';
				$ergebnisPlatzhalter = 'HH:MM:SS,00';
				$result_form = 11;
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
								";

			if($darkmode == 0) {
				$error .= "confirmButtonText: '<span style=\"color:#fff!important;\"><i class=\"fas fa-redo\"></i>&emsp;Seite verlassen</span>',";
			} else {
				$error .= "confirmButtonText: '<span style=\"color:#f5eee1!important;\"><i class=\"fas fa-redo\"></i>&emsp;Seite verlassen</span>',";
			}

			$error .=		"
									confirmButtonColor: '#b94a48',
									backdrop:	`
									";
			if($darkmode == 0) {
				$error .= "#ede7da";
			} else {
				$error .= "#404040";
			}

			$error .=		"
										center
										no-repeat
									`
								}).then((result) => {
									if(result.value) {
										location.href = \"../index.php\";
									}
								});
								";
		}
	// Benutzer ist nicht eingeloggt, leite auf Hauptseite weiter
	} else {
		header("Location: ../index.php");
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<!--	SET TITLE		-->
		<title>zeitnah|me</title>

		<meta property="og:title" content="mind|sources" />
		<meta property="og:site_name" content="mind|sources" />
		<meta property="og:image" content="images/demo.jpg" />
		<meta property="og:image:type" content="image/jpeg" />
		<meta property="og:image:width" content="400" />
		<meta property="og:image:height" content="300" />
		<meta property="og:image:alt" content="Entwickler für webbasierte Onlinedienste" />
		<meta property="og:description" content="Entwickler für webbasierte Onlinedienste" />
		<meta property="og:url" content="https://mindsources.net" />
		<meta property="og:locale" content="de_DE" />
		<meta property="og:type" content="website" />

		<!--	INCLUDING ICO	-->
		<link rel="apple-touch-icon" sizes="57x57" href="images/fav/apple-icon-57x57.png">
		<link rel="apple-touch-icon" sizes="60x60" href="images/fav/apple-icon-60x60.png">
		<link rel="apple-touch-icon" sizes="72x72" href="images/fav/apple-icon-72x72.png">
		<link rel="apple-touch-icon" sizes="76x76" href="images/fav/apple-icon-76x76.png">
		<link rel="apple-touch-icon" sizes="114x114" href="images/fav/apple-icon-114x114.png">
		<link rel="apple-touch-icon" sizes="120x120" href="images/fav/apple-icon-120x120.png">
		<link rel="apple-touch-icon" sizes="144x144" href="images/fav/apple-icon-144x144.png">
		<link rel="apple-touch-icon" sizes="152x152" href="images/fav/apple-icon-152x152.png">
		<link rel="apple-touch-icon" sizes="180x180" href="images/fav/apple-icon-180x180.png">
		<link rel="icon" type="image/png" sizes="192x192"  href="images/fav/android-icon-192x192.png">
		<link rel="icon" type="image/png" sizes="32x32" href="images/fav/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="96x96" href="images/fav/favicon-96x96.png">
		<link rel="icon" type="image/png" sizes="16x16" href="images/fav/favicon-16x16.png">
		<link rel="manifest" href="images/fav/manifest.json">

		<!--	SET META		-->
		<meta name="msapplication-TileImage" content="images/fav/ms-icon-144x144.png">
		<meta name="theme-color" content="#C0C0C0" />
		<meta name="msapplication-TileColor" content="#C0C0C0">
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="description" content="zeitnah|me - Das Datenzentrum im Motorsport!" />
		<meta name="author" content="Montreal (www.mindsources.net)" />

		<!-- dient für Reload bei erstmaligem Setzen von COOKIE -->
		<?php echo $reload; ?>

		<meta charset="utf-8" />

		<meta http-equiv="X-UA-Compatible" content="ie=edge" />

		<!--	INCLUDING CSS			-->
		<!--[if lte IE 8]>
			<link rel="stylesheet" type="text/css" href="assets/css/ie8.css" />
		<![endif]-->
		<!--[if lte IE 9]>
			<link rel="stylesheet" type="text/css" href="assets/css/ie9.css" />
		<![endif]-->
		<?php
			if($darkmode == 0) {
				echo '<link rel="stylesheet" href="assets/css/main.min.css" />';
				echo '<style>
					#preloader{z-index:1000!important;position:fixed;background:#f5eee1;top:0;left:0;width:100%;height:100%}#loader{display:block;position:relative;left:50%;top:50%;width:150px;height:150px;margin:-75px 0 0 -75px;border-radius:50%;border:3px solid transparent;border-top-color:#8e6516;-webkit-animation:spin 2s linear infinite;animation:spin 2s linear infinite}#loader:before{content:"";position:absolute;top:5px;left:5px;right:5px;bottom:5px;border-radius:50%;border:3px solid transparent;border-top-color:#b68c2f;-webkit-animation:spin 3s linear infinite;animation:spin 3s linear infinite}#loader:after{content:"";position:absolute;top:15px;left:15px;right:15px;bottom:15px;border-radius:50%;border:3px solid transparent;border-top-color:#c0c0c0;-webkit-animation:spin 1.5s linear infinite;animation:spin 1.5s linear infinite}@-webkit-keyframes spin{0%{-webkit-transform:rotate(0);-ms-transform:rotate(0);transform:rotate(0)}100%{-webkit-transform:rotate(360deg);-ms-transform:rotate(360deg);transform:rotate(360deg)}}@keyframes spin{0%{-webkit-transform:rotate(0);-ms-transform:rotate(0);transform:rotate(0)}100%{-webkit-transform:rotate(360deg);-ms-transform:rotate(360deg);transform:rotate(360deg)}}
				</style>';
				echo '<link rel="stylesheet" href="assets/css/chat.min.css" />';
			} else {
				echo '<link rel="stylesheet" href="assets/css/main.dark.min.css" />';
				echo '<style>
					#preloader{z-index:1000!important;position:fixed;background:#333;top:0;left:0;width:100%;height:100%}#loader{display:block;position:relative;left:50%;top:50%;width:150px;height:150px;margin:-75px 0 0 -75px;border-radius:50%;border:3px solid transparent;border-top-color:#f5eee1;-webkit-animation:spin 2s linear infinite;animation:spin 2s linear infinite}#loader:before{content:"";position:absolute;top:5px;left:5px;right:5px;bottom:5px;border-radius:50%;border:3px solid transparent;border-top-color:#b68c2f;-webkit-animation:spin 3s linear infinite;animation:spin 3s linear infinite}#loader:after{content:"";position:absolute;top:15px;left:15px;right:15px;bottom:15px;border-radius:50%;border:3px solid transparent;border-top-color:#c0c0c0;-webkit-animation:spin 1.5s linear infinite;animation:spin 1.5s linear infinite}@-webkit-keyframes spin{0%{-webkit-transform:rotate(0);-ms-transform:rotate(0);transform:rotate(0)}100%{-webkit-transform:rotate(360deg);-ms-transform:rotate(360deg);transform:rotate(360deg)}}@keyframes spin{0%{-webkit-transform:rotate(0);-ms-transform:rotate(0);transform:rotate(0)}100%{-webkit-transform:rotate(360deg);-ms-transform:rotate(360deg);transform:rotate(360deg)}}
				</style>';
				echo '<link rel="stylesheet" href="assets/css/chat.dark.min.css" />';
			}
		?>
		<link rel="stylesheet" href="assets/css/all.min.css"/>
		<link rel="stylesheet" href="assets/css/jquery-ui.min.css" />
		<link rel="stylesheet" href="assets/css/jquery-ui.theme.min.css" />
		<link rel="stylesheet" href="assets/css/jquery-ui.structure.min.css" />
		<!-- Alle Plugin-Änderungen (als letztes einbinden) -->
		<?php
			if($darkmode == 0) {
				echo 	'
							<link rel="stylesheet" href="assets/css/custom.min.css" />
							<link rel="stylesheet" href="assets/css/rewrite.plugins.min.css" />
							';
			} else {
				echo 	'
							<link rel="stylesheet" href="assets/css/custom.dark.min.css" />
							<link rel="stylesheet" href="assets/css/rewrite.plugins.dark.min.css" />
							';
			}
		?>

		<noscript>
			<div style="z-index: 1250!important;left:0;position:fixed;text-align:center;width:100%;height:100%;background-color:rgba(185, 74, 72, 1);">
				<h1 style="line-height:100%;padding-top:25%;color:#fff;">Bitte aktivieren Sie JavaScript!</h1>
			</div>
		</noscript>
	</head>
	<body>
		<!-- Wrapper -->
		<div id="wrapper">
			<div id="preloader">
			  <div id="loader"></div>
			</div>

			<!-- Main -->
			<div id="main" style="display:none!important;">
				<div id="chatContainer" class="row uniform">
					<div class="12u$ 24u$">
						<button class="verbindungBenoetigt" id="showRight"><i class="fas fa-comments fa-2x"></i></button>
					</div>
					<nav class="12u$ 24u$ cbp-spmenu cbp-spmenu-vertical cbp-spmenu-right" id="cbp-spmenu-s2">
						<h3>Live Chat</h3>
						<div id="chat">
							<div id="chatChronicle">

							</div>
							<div id="chatMessage">
								<textarea name="demo-message" class="fit" id="demo-message" placeholder="Nachricht senden" rows="2"></textarea>
							</div>
							<div id="chatSending">
								<input type="submit" name="store" class="button fit" value="Nachricht senden" />
							</div>
						</div>
					</nav>
				</div>

				<div class="inner">
					<!-- Header -->
					<header id="header">
						<a href="/" class="logo"><span style="font-weight:800;">zeitnah|me</span> &ndash; <em>Die</em> Datenbank im Motorsport!</a>

						<ul class="icons">
							<li>
								<span id="pendingresState" style="display:none;color:#b94a48;">
									<span class="label">
										<i class="fas fa-database" title="Fehlerhafte Ergebnisdaten erkannt!"></i>
									</span>
								</span>
							</li>
							<li>
								<span id="localstoreState" style="color:#468847;">
									<span class="label">
										<i class="fas fa-save" title="Ihre Ergebnisdaten sind im Browser gespeichert!"></i>
									</span>
								</span>
							</li>
							<li>
								<span id="connectionState" style="color:#468847;">
									<span class="label">
										<i class="fas fa-wifi" title="Sie sind mit dem Internet verbunden!"></i>
									</span>
								</span>
							</li>
							<li>&nbsp;</li>
							<li>&nbsp;</li>
						</ul>
					</header>

					<!-- CONTENT SECTIONS -->
					<!-- REGISTRATION -->
					<section>
						<!--
						<header class="main">
							<h1 class="mittelgross">Ergebnisdaten Eingabe</h1>
						</header>
						-->

						<div class="row uniform">
							<div class="12u$ 24u$">
								<div class="12u$ 24u$">
									<h2>
										<span class="verbindungBenoetigt" id="zeigeTeilnehmerStatus">Teilnehmer Status <i class="fas fa-eye-slash fa-xs fa-fw"></i></span>
										<span class="verbindungBenoetigt" id="versteckeTeilnehmerStatus">Teilnehmer Status <i class="fas fa-eye fa-xs fa-fw"></i></span>
									</h2>
								</div>

								<div class="12u$ 24u$" id="teilnehmerStatus"></div>
								<div class="12u$ 24u$" id="supportingText"></div>
							</div>

							<div class="12u$ 24u$">
								<h4>Prüfungsnummer</h4>
							</div>
							<div class="12u$ 24u$">
								<?php
									// Hole zugewiesene Prüfung(en)
									$select_optio_zm = "SELECT `id`, `eid`, `rid_type`, `rid` FROM `_optio_zmembers` WHERE `eid` = '" . $eid . "' AND `id` = '" . $uid . "' LIMIT 1";
									$result_optio_zm = mysqli_query($mysqli, $select_optio_zm);
									$spalte_optio_zm = mysqli_fetch_assoc($result_optio_zm);
								?>
								<input type="text" name="pruefung" id="pruefung" value="<?php if(isset($spalte_optio_zm)) { echo $spalte_optio_zm['rid_type'] . $spalte_optio_zm['rid']; } ?>" placeholder="<?php if(isset($spalte_optio_zm)) { echo $spalte_optio_zm['rid_type'] . $spalte_optio_zm['rid']; } ?>" readonly required />
							</div>
							<div class="12u$ 24u$">
								<h4>Startnummer</h4>
							</div>
							<div class="12u$ 24u$">
								<input type="tel" name="startnummer" id="startnummer" maxlength="4" pattern="^[1-9]{1}[0-9]{0,3}$" required autofocus />
							</div>
							<div class="12u$ 24u$">
								<h4>Prüfungsposition</h4>
							</div>
							<div class="12u$ 24u$">
							<?php
								// Generiere Eingabefeld basierend auf Anzahl der Elemente
								if(isset($zpos)) {
									// Splitte String auf
									$arrayZPositionen = explode(":", $zpos);

									if(count($arrayZPositionen) == 1) {
							?>
								<input type="text" name="position" id="position" value="<?php if(isset($arrayZPositionen[$i])) { echo $arrayZPositionen[$i]; } ?>" required readonly />
							<?php
									} else {
							?>
								<div class="select-wrapper">
									<select name="position" id="position" required>
							<?php
										for ($i = 0; $i < (count($arrayZPositionen) - 1); $i++) {
							?>
										<option value="<?php if(isset($arrayZPositionen[$i])) { echo $arrayZPositionen[$i]; } ?>"><?php if(isset($arrayZPositionen[$i])) { echo $arrayZPositionen[$i]; } ?></option>
							<?php
										}
							?>
									</select>
							<?php
									}
								}
							?>
								</div>
							</div>
							<div class="12u$ 24u$"><h4>Ergebnis</h4></div>
							<div class="12u$ 24u$">
								<input type="tel" name="ergebnis" id="ergebnis" maxlength="11" placeholder="<?php echo $ergebnisPlatzhalter; ?>" required />
							</div>
							<div class="8u 16u">
								<input type="submit" name="store" class="button special" id="store" value="Ergebnis speichern" />
							</div>
							<div class="4u$ 8u$">
								<button class="button fit" style="opacity:1;" disabled>
									<a href="https://www.schnelle-online.info/Atomuhr-Uhrzeit.html" id="soitime220204983267" style="border: 0;" tabindex="-1" disabled>Uhrzeit</a>
									<script type="text/javascript">
										SOI = (typeof(SOI) != 'undefined') ? SOI : {};

										(SOI.ac21fs = SOI.ac21fs || []).push(function() {
											(new SOI.DateTimeService("220204983267", "DE")).start();
										});

										(function() {
											if(typeof(SOI.scrAc21) == "undefined") {
												SOI.scrAc21=document.createElement('script');
												SOI.scrAc21.type='text/javascript';
												SOI.scrAc21.async=true;
												SOI.scrAc21.src=((document.location.protocol == 'https:') ? 'https://' : 'http://') + 'homepage-tools.schnelle-online.info/Homepage/atomicclock2_1.js';
												var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(SOI.scrAc21, s);
											}
										})();
									</script>
								</button>
							</div>
						</div>
					</section>
				</div>
			</div>

			<!-- SIDEBAR -->
			<div id="sidebar">
				<div class="inner">
					<!-- Menu -->
					<nav id="menu">
						<header class="major">
							<h2>Menu</h2>
						</header>

						<!-- MENU -->
						<ul>
							<li class="verbindungBenoetigt"><a href="index.php"><i class="fas fa-stopwatch fa-fw"></i> Ergebnisdaten Eingabe</a></li>
							<li class="verbindungBenoetigt"><span id="importiereErgebnisdaten"><i class="fas fa-upload fa-fw"></i> Ergebnisdaten importieren</span></li>
							<!--<li><a href="#"><i class="fas fa-comment-dots"></i> Auswerter kontaktieren</a></li>-->
							<li>
								<span class="opener"><i class="fas fa-cog fa-fw"></i> Einstellungen</span>
								<ul>
								<?php
									if($darkmode == 0) {
										echo '<li class="verbindungBenoetigt" id="darkmodeLI"><span id="darkmode"><i class="icon fas fa-moon fa-fw"></i> Dunklen Modus aktivieren</span></li>';
										echo '<li class="verbindungBenoetigt" id="lightmodeLI" style="display: none;"><span id="lightmode"><i class="icon fas fa-sun fa-fw"></i> Hellen Modus aktivieren</span></li>';
									} else {
										echo '<li class="verbindungBenoetigt" id="darkmodeLI" style="display: none;"><span id="darkmode"><i class="icon fas fa-moon fa-fw"></i> Dunklen Modus aktivieren</span></li>';
										echo '<li class="verbindungBenoetigt" id="lightmodeLI"><span id="lightmode"><i class="icon fas fa-sun fa-fw"></i> Hellen Modus aktivieren</span></li>';
									}
								?>
								</ul>
							</li>
						</ul>

						<p>
							<!-- INCLUDE LOGIN SECTION -->
							<div class="container">
								<!-- MODAL LOGIN -->
								<a id="logout_trigger" href="process_logout.php" class="verbindungBenoetigt button special fit">Ausloggen</a>
							</div>
						</p>
					</nav>

					<footer id="footer">
						<p class="copyright" align="center">&copy; 2016 &ndash; <? echo date("Y", time()); ?> <font color="#8E6515">zeitnah|me GbR</font><br />Alle Rechte vorbehalten.</p>
					</footer>
				</div>
			</div>
		</div>

		<!--	INCLUDING JQUERY LIBS-->
		<script src="assets/js/jquery.min.js"></script>
		<script src="assets/js/jquery.migrate.min.js"></script>
		<script src="assets/js/jquery-ui.min.js"></script>
		<script src="assets/js/jquery.easing.min.js"></script>

		<!--	INCLUDING JQUERY PLUGINS	-->
		<script src="assets/js/ping.min.js"></script>
		<script src="assets/js/sweetalert2.min.js"></script>
		<script src="assets/js/jquery.maskedinput.min.js"></script>
		<script src="assets/js/universaljs.min.js"></script>
		<script src="assets/js/classie.min.js"></script>
		<script src="assets/js/skel.min.js"></script>
		<script src="assets/js/util.min.js"></script>
		<!--[if lte IE 8]>
			<script src="assets/js/respond.min.js"></script>
			<script src="assets/js/html5.shiv.min.js"></script>
		<![endif]-->
		<script src="assets/js/main.min.js"></script>

		<script>
			// Lege Ergebnisspeicher global fest
			var ergebnisspeicher = "",
					alreadyChanged = 0
					timeOut = "",
					zeigeFehlerUpload = 0
					tabelleStrlen = "",
					tabelleFailed = "",
					tabelleSkiped = "",
					tabelleErrXXX = "";

			// Werte Ergebnisdaten aus
			var	isokay = new Array,
					strlen = new Array,
					failed = new Array,
					skiped = new Array,
					errXXX = new Array;

			// Benutzer relevante Parameter
			var eid = <?php echo $eid; ?>,
					rid = <?php echo $rid; ?>;

			$(document).ready(function() {
				// Blende Preloader-Screen aus
				$('#preloader').delay(1000).fadeOut(1000, 'easeInOutCubic', function() {
					// Blende Inhalt ein
					$('#main').fadeIn(1000, 'easeInOutCubic');
				});

				// Fokussiere Startnummer-Eingabefeld
				$('#startnummer').focus();

				// Übergebe PHP-Fehler an jQuery
				<?php echo $error; ?>

				// Ändere Anzeigemodus zu Darkmode
				$('#darkmode').click(function() {
					// Sende Anfrage an Server, um Cookie anzupassen
					$.ajax({
						type: 'POST',
						url: 'aendereAnzeige.php',
						data:	{
							mode: 1
						},
						success: function(){
							// Binde neue Stylesheets ein
							$('head').append('<link rel="stylesheet" type="text/css" href="assets/css/main.dark.min.css">');
							$('head').append('<link rel="stylesheet" type="text/css" href="assets/css/chat.dark.min.css">');
							$('head').append('<link rel="stylesheet" type="text/css" href="assets/css/custom.dark.min.css">');
							$('head').append('<link rel="stylesheet" type="text/css" href="assets/css/rewrite.plugins.dark.min.css">');

							// Verstecke alles von Lightmode
							$('#darkmodeLI').hide();
							$('#lightmodeLI').show();

							// Entferne derzeitigen Plugin-Stylesheet für hellen Modus
							$('link[rel=stylesheet][href~="assets/css/custom.min.css"]').remove();
							$('link[rel=stylesheet][href~="assets/css/rewrite.plugins.min.css"]').remove();
						}
					});
				});

				// Ändere Anzeigemodus zu Lightmode
				$('#lightmode').click(function() {
					// Sende Anfrage an Server, um Cookie anzupassen
					$.ajax({
						type: 'POST',
						url: 'aendereAnzeige.php',
						data:	{
							mode: 0
						},
						success: function(){
							// Binde neue Stylesheets ein
							$('head').append('<link rel="stylesheet" type="text/css" href="assets/css/main.min.css">');
							$('head').append('<link rel="stylesheet" type="text/css" href="assets/css/chat.min.css">');
							$('head').append('<link rel="stylesheet" type="text/css" href="assets/css/custom.min.css">');
							$('head').append('<link rel="stylesheet" type="text/css" href="assets/css/rewrite.plugins.min.css">');

							// Verstecke alles von Lightmode
							$('#darkmodeLI').show();
							$('#lightmodeLI').hide();

							// Entferne derzeitigen Plugin-Stylesheet für dunklen Modus
							$('link[rel=stylesheet][href~="assets/css/custom.dark.min.css"]').remove();
							$('link[rel=stylesheet][href~="assets/css/rewrite.plugins.dark.min.css"]').remove();
						}
					});
				});

				$('#zeigeTeilnehmerStatus').click(function() {
					// Blende Icon anzeigen aus
					$('#zeigeTeilnehmerStatus').hide();
					// Blende Übersicht und Icon ausblenden ein
					$('#versteckeTeilnehmerStatus').show();
					$('#teilnehmerStatus').slideDown('easeInOutCubic');
				});

				$('#versteckeTeilnehmerStatus').click(function() {
					// Blende Icon anzeigen aus
					$('#versteckeTeilnehmerStatus').hide();
					// Blende Übersicht und Icon ausblenden ein
					$('#zeigeTeilnehmerStatus').show();
					$('#teilnehmerStatus').slideUp('easeInOutCubic');
				});

				// Zeige Teilnehmerstatus
				$('html').on('click', '.teilnehmerInfo', function() {
					console.log("Jep");

					// Hole ID, da hier SID gespeichert ist
					var sid = $(this).attr('id');

					// Hole Informationen über diesen Teilnehmer
					$.ajax({
						type: 'POST',
						url: 'holeTeilnehmerInfo.ax.php',
						data:	{
							sid: sid
						},
						success: function(data) {
							// Keine Session
							if(data == "keineSession") {
								Swal.fire({
									allowOutsideClick: false,
									allowEscapeKey: false,
									type: 'error',
									title: 'Login abgelaufen',
									text: 'Ihr aktiver Login wurde beendet. Bitte loggen Sie sich erneut ein!',
									footer: '<span style=\"font-size; small;\">Im Zweifelsfall kontaktieren Sie Ihren zuständigen Auswerter!<br /><br /><em>Fehlercode: znSessionExpired</em></span>',
									showConfirmButton: true,
									<?php
										if($darkmode == 0) {
											echo "confirmButtonText: '<span style=\"color:#fff!important;\"><i class=\"fas fa-redo\"></i>&emsp;Seite verlassen</span>',";
										} else {
											echo "confirmButtonText: '<span style=\"color:#f5eee1!important;\"><i class=\"fas fa-redo\"></i>&emsp;Seite verlassen</span>',";
										}
									?>
									confirmButtonColor: '#b94a48',
									backdrop:	`
									<?php
										if($darkmode == 0) {
											echo "#ede7da";
										} else {
											echo "#404040";
										}
									?>
										center
										no-repeat
									`
								}).then((result) => {
									if(result.value) {
										location.href = "../index.php";
									}
								});
							// Gebe HTML Tabelle aus
							} else {
								Swal.fire({
									allowOutsideClick: true,
									allowEscapeKey: true,
									type: 'info',
									title: 'Ereignisliste für Startnummer ' + sid,
									html: data,
									showConfirmButton: true,
									showCancelButton: false,
									focusConfirm: true,
									<?php
										if($darkmode == 0) {
											echo "confirmButtonText: '<span style=\"color:#fff!important;\">ausblenden</span>',";
											echo "confirmButtonColor: '#8e6516',";
										} else {
											echo "confirmButtonText: '<span style=\"color:#f5eee1!important;\">ausblenden</span>',";
											echo "confirmButtonColor: '#b68c2f',";
										}
									?>
									backdrop:	`
									<?php
										if($darkmode == 0) {
											echo "#ede7da";
										} else {
											echo "#404040";
										}
									?>
										center
										no-repeat
									`
								});
							}
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
								cde = 'parsererror';
							} else if(exception === 'timeout') {
								msg = 'Timeout-Fehler.';
								cde = 'timeout';
							} else if(exception === 'abort') {
								msg = 'Anfrage abgebrochen.';
								cde = 'abort';
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
								footer: '<span style="font-size; small;">Sollte das Problem weiterhin bestehen, so kontaktieren Sie den Support unter dem aufgeführten Fehlercode!<br /><br /><em>Fehlercode: ' + cde + '/znFetchTInfo</em></span>',
								showConfirmButton: true,
								confirmButtonText: '<span style="color: #fff;"><i class="fas fa-redo"></i>&emsp;Anfrage erneut senden</span>',
								confirmButtonColor: '#b94a48',
								backdrop:	`
									linear-gradient(145deg, rgba(205,205,205,.75), rgba(173,173,173,.75))
									center
									no-repeat
								`
							}).then((result) => {
								// Erneuter Versuch Teilnehmer Informationen abzurufen
								$('#' + sid).trigger("click");
							});
						}
					});
				});

				$('#importiereErgebnisdaten').click(function() {
					Swal.fire({
						allowOutsideClick: true,
						allowEscapeKey: true,
						title: 'Ergebnisdaten importieren',
						html: '<span style="text-align:left!important;">Achten Sie darauf, ausschließlich CSV (; separiert) hochzuladen, da es sonst zu Problemen kommen kann. In Kürze werden weitere Dateiformate unterstützt werden.<hr /><p><div class="table-wrapper"><table><thead><tr><td><i style="color:#468847;" class="fas fa-file-csv fa-fw"></i>&emsp;<strong>Format:</strong></td><td><code>SNr.;Ergebnis;Position</code></td></tr><tr><td><i class="fas fa-file-import fa-fw"></i>&emsp;<strong>Muster:</strong></td><td><code>33;12:34:56,78;Start</code></td></tr></thead></table></div></p></span>',
		        input: 'file',
						inputAttributes: {
					    accept: 'text/csv,text/plain,application/csv,text/comma-separated-values,application/excel,application/vnd.ms-excel,application/vnd.msexcel,text/anytext,application/octet-stream,application/txt',
							'aria-label': 'Ergebnisdaten als CSV importieren'
					  },
						showConfirmButton: true,
						showCancelButton: false,
						focusConfirm: false,
						focusCancel: false,
						<?php
							if($darkmode == 0) {
								echo "confirmButtonText: '<span style=\"color:#fff!important;\">hochladen</span>',";
								echo "confirmButtonColor: '#8e6516',";
							} else {
								echo "confirmButtonText: '<span style=\"color:#f5eee1!important;\">hochladen</span>',";
								echo "confirmButtonColor: '#b68c2f',";
							}
						?>
						backdrop:	`
						<?php
							if($darkmode == 0) {
								echo "#ede7da";
							} else {
								echo "#404040";
							}
						?>
							center
							no-repeat
						`,
		        onBeforeOpen: () => {
		            $(".swal2-file").change(function () {
		                var reader = new FileReader();
		                reader.readAsDataURL($(".swal2-file")[0].files[0]);
		            });
		        }
		    	}).then((file) => {
		        if(file.value) {
							// Leere vorherige Fehlertabelle
							var tabelleErrXXX = "",
									tabelleStrlen = "",
									tabelleSkiped = "",
									tabelleFailed = "";

							// Leere Array-Speicher
							strlen = [],
							failed = [],
							skiped = [],
							errXXX = [];

							// Entferne Fehler-Status Symbol (Datenbank-Icon oben rechts)
							$('#pendingresState').hide();

							// Beginne mit Validieren und Hochladen von Import-Datei
	            var impErgebnisdaten = new FormData();
	            var file = $('.swal2-file')[0].files[0];
	            impErgebnisdaten.append("csvImportieren", file);
	            $.ajax({
                headers: {
									'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
								},
                method: 'post',
                url: 'importiereErgebnisdaten.ax.php',
                data: impErgebnisdaten,
                processData: false,
                contentType: false,
                success: function(data) {
									/*
									 * Sofern kein Fehler vorhanden, enthält jedes
									 * Rückgabewert-Array Angaben über Status,
									 * Ergebnis und Array-Schlüssel
									 */
									if(data == "keineCSV") {
										// Keine CSV Datei
										Swal.fire({
											allowOutsideClick: true,
											allowEscapeKey: true,
											type: 'error',
											title: 'Keine CSV Datei',
											html: 'Die von Ihnen hochgeladene Datei ist keine CSV Datei! Bitte achten Sie darauf ausschließlich CSV Dateien auszuwählen!',
											showConfirmButton: true,
											focusConfirm: false,
											focusCancel: false,
											<?php
												if($darkmode == 0) {
													echo "confirmButtonText: '<span style=\"color:#fff!important;\">verstanden</span>',";
													echo "confirmButtonColor: '#8e6516',";
												} else {
													echo "confirmButtonText: '<span style=\"color:#f5eee1!important;\">verstanden</span>',";
													echo "confirmButtonColor: '#b68c2f',";
												}
											?>
											backdrop:	`
											<?php
												if($darkmode == 0) {
													echo "#ede7da";
												} else {
													echo "#404040";
												}
											?>
												center
												no-repeat
											`
										}).then((result) => {
											// Info anzeigen
											if(result.value) {
												$('#importiereErgebnisdaten').trigger('click');
											}
										});
									} else if(data == "keineSession") {
										Swal.fire({
											allowOutsideClick: false,
											allowEscapeKey: false,
											type: 'error',
											title: 'Login abgelaufen',
											text: 'Ihr aktiver Login wurde beendet. Bitte loggen Sie sich erneut ein!',
											footer: '<span style=\"font-size; small;\">Im Zweifelsfall kontaktieren Sie Ihren zuständigen Auswerter!<br /><br /><em>Fehlercode: znSessionExpired</em></span>',
											showConfirmButton: true,
											<?php
												if($darkmode == 0) {
													echo "confirmButtonText: '<span style=\"color:#fff!important;\"><i class=\"fas fa-redo\"></i>&emsp;Seite verlassen</span>',";
												} else {
													echo "confirmButtonText: '<span style=\"color:#f5eee1!important;\"><i class=\"fas fa-redo\"></i>&emsp;Seite verlassen</span>',";
												}
											?>
											confirmButtonColor: '#b94a48',
											backdrop:	`
											<?php
												if($darkmode == 0) {
													echo "#ede7da";
												} else {
													echo "#404040";
												}
											?>
												center
												no-repeat
											`
										}).then((result) => {
											if(result.value) {
												location.href = "../index.php";
											}
										});
									} else if(data == "csvUngueltig") {
										// Ungültige CSV Datei
										Swal.fire({
											allowOutsideClick: true,
											allowEscapeKey: true,
											type: 'error',
											title: 'Ungültige CSV Datei',
											html: 'Die von Ihnen hochgeladene CSV Datei besitzt keinen gültigen <a href="https://de.wikipedia.org/wiki/Internet_Media_Type" target="_blank">MIME-Type</a>!',
											showConfirmButton: true,
											focusConfirm: false,
											focusCancel: false,
											<?php
												if($darkmode == 0) {
													echo "confirmButtonText: '<span style=\"color:#fff!important;\">verstanden</span>',";
													echo "confirmButtonColor: '#8e6516',";
												} else {
													echo "confirmButtonText: '<span style=\"color:#f5eee1!important;\">verstanden</span>',";
													echo "confirmButtonColor: '#b68c2f',";
												}
											?>
											backdrop:	`
											<?php
												if($darkmode == 0) {
													echo "#ede7da";
												} else {
													echo "#404040";
												}
											?>
												center
												no-repeat
											`
										}).then((result) => {
											// Info anzeigen
											if(result.value) {
												$('#importiereErgebnisdaten').trigger('click');
											}
										});
									} else if(data == "csvLeer") {
										// CSV Datei ist leer
										// Ungültige CSV Datei
										Swal.fire({
											allowOutsideClick: true,
											allowEscapeKey: true,
											type: 'error',
											title: 'Leere CSV Datei',
											html: 'Die von Ihnen hochgeladene CSV Datei besitzt keinen Inhalt!',
											showConfirmButton: true,
											focusConfirm: false,
											focusCancel: false,
											<?php
												if($darkmode == 0) {
													echo "confirmButtonText: '<span style=\"color:#fff!important;\">verstanden</span>',";
													echo "confirmButtonColor: '#8e6516',";
												} else {
													echo "confirmButtonText: '<span style=\"color:#f5eee1!important;\">verstanden</span>',";
													echo "confirmButtonColor: '#b68c2f',";
												}
											?>
											backdrop:	`
											<?php
												if($darkmode == 0) {
													echo "#ede7da";
												} else {
													echo "#404040";
												}
											?>
												center
												no-repeat
											`
										}).then((result) => {
											// Info anzeigen
											if(result.value) {
												$('#importiereErgebnisdaten').trigger('click');
											}
										});
									// Übergabe in Ordnung
									} else {
										data = JSON.parse(data);

										console.log(data);

										// Durchlaufe Schleife, um hochgeladene Ergebnisdaten
										for(var i = 0; i < data.length; i++) {
											// Debugging
											console.log(data[i]);

											// Splitte für jeden Durchlauf aktuelle Ergebnisdaten auf
											var split = data[i].split('#');

											// Debugging
											console.log(split);

											// Prüfe zuerst alle erfolgreichen Einträge
											var status = split[0],
													ergdat = split[1],
													arrkey = split[2];

											switch(status) {
												// Ergebnis hochgeladen
												case "isokay":
													// Entferne hochgeladene und ungültige Ergebnisdaten aus Ergebnisspeicher
													delete ergebnisspeicher[arrkey];

													// Debugging
													console.log(status + " " + ergdat + " " + arrkey);
												break;
												// Ergebnislänge ungültig
												case "strlen":
													// Füge entsprechendem Array hinzu
													strlen.push(ergdat);

													// Entferne hochgeladene und ungültige Ergebnisdaten aus Ergebnisspeicher
													delete ergebnisspeicher[arrkey];
												break;
												// Ergebnis übersprungen (identisch)
												case "skiped":
													// Füge entsprechendem Array hinzu
													skiped.push(ergdat);

													// Entferne hochgeladene und ungültige Ergebnisdaten aus Ergebnisspeicher
													delete ergebnisspeicher[arrkey];
												break;
												// Ergebnis konnte nicht hochgeladen werden
												case "failed":
													// Füge entsprechendem Array hinzu
													failed.push(ergdat);

													// Entferne hochgeladene und ungültige Ergebnisdaten aus Ergebnisspeicher
													delete ergebnisspeicher[arrkey];
												break;
												/*
												 * Fehlercodes (Ergebnisdatensatz korrupt)
												 * Wenn Startnummer nicht gegeben ist (erste Zahl nach "err"),
												 * kann auch der zugehörige, lokal gespeicherte Datensatz nicht
												 * gelöscht werden, da der Index aus Startnummer und Position
												 * generiert wird (Bsp. 11.Start)
												 */
												case "errXXX":
													// Ergebnisdatensatz kann nicht aus lokalem Speicher gelöscht werden
													// Füge entsprechendem Array hinzu
													errXXX.push(status + '#' + ergdat);
												break;
												// Fehlercode mit funktionaler Startnummer
												case "err000":
													// Füge entsprechendem Array hinzu
													errXXX.push(status + '#' + ergdat);

													// Entferne hochgeladene und ungültige Ergebnisdaten aus Ergebnisspeicher
													delete ergebnisspeicher[arrkey];
												break;
											}
										}

										// Prüfe zusätzlich, ob alle Ergebnisdaten hochgeladen wurden
										if(ergebnisspeicher.length == 0) {
											if(
												strlen.length == 0 &&
												skiped.length == 0 &&
												failed.length == 0 &&
												errXXX.length == 0
											) {
												setTimeout(function() {
													Swal.fire({
														allowOutsideClick: true,
														allowEscapeKey: true,
														type: 'success',
														title: 'Ergebnisdaten importiert',
														html: 'Ihre importierten Ergebnisdaten wurden hochgeladen!',
														showConfirmButton: true,
														focusConfirm: true,
														focusCancel: false,
														<?php
															if($darkmode == 0) {
																echo "confirmButtonText: '<span style=\"color:#fff!important;\">verstanden</span>',";
																echo "confirmButtonColor: '#8e6516',";
															} else {
																echo "confirmButtonText: '<span style=\"color:#f5eee1!important;\">verstanden</span>',";
																echo "confirmButtonColor: '#b68c2f',";
															}
														?>
														backdrop:	`
														<?php
															if($darkmode == 0) {
																echo "#ede7da";
															} else {
																echo "#404040";
															}
														?>
															center
															no-repeat
														`
													});
												}, 2000);
											} else {
												setTimeout(function() {
													Swal.fire({
														allowOutsideClick: true,
														allowEscapeKey: true,
														type: 'warning',
														title: 'Fehlerhafte Ergebnisdaten',
														html: 'Nicht alle Ihre importierten Ergebnisdaten wurden hochgeladen! Einige fehlerhafte Ergebnisdatensätze wurden erkannt. Für eine genauere Info klicken Sie auf das Datenbank-Symbol oben links oder auf \'Info anzeigen\'!',
														showConfirmButton: true,
														showCancelButton: true,
														focusConfirm: true,
														focusCancel: false,
														<?php
															if($darkmode == 0) {
																echo "confirmButtonText: '<span style=\"color:#fff!important;\">verstanden</span>',";
																echo "confirmButtonColor: '#8e6516',";
																echo "cancelButtonText: '<span style=\"color:#fff!important;\">Info anzeigen</span>',";
																echo "cancelButtonColor: '#333',";
															} else {
																echo "confirmButtonText: '<span style=\"color:#f5eee1!important;\">verstanden</span>',";
																echo "confirmButtonColor: '#b68c2f',";
																echo "cancelButtonText: '<span style=\"color:#fff!important;\">Info anzeigen</span>',";
																echo "cancelButtonColor: '#c0c0c0',";
															}
														?>
														backdrop:	`
														<?php
															if($darkmode == 0) {
																echo "#ede7da";
															} else {
																echo "#404040";
															}
														?>
															center
															no-repeat
														`
													}).then((result) => {
														// Info anzeigen
													  if(!result.value) {
													    $('#pendingresState').trigger('click');
													  }
													});
												}, 2000);
											}

											// Sofern es Fehler / Ungültigkeiten gab, gebe hier eine Meldung aus
											if(strlen.length > 0) {
												// Blende Datenbank-Symbol (neben WiFi und Local-Storage Icon) ein
												$('#pendingresState').show();

												zeigeFehlerUpload = 1;

												tabelleStrlen = "<div class=\"table-wrapper\"><table><thead><tr><th align=\"left\" colspan=\"3\">Ungültige Ergebnislänge</th></tr><tr></tr><tr><th>Startnummer</th><th>Ergebnis</th><th>Position</th></tr></thead><tbody>";

												for(var i = 0; i < strlen.length; i++) {
													var explode = strlen[i].split(';');

													tabelleStrlen += "<tr><td>" + explode[0] + "</td><td>" + explode[1] + "</td><td>" + explode[2] + "</td></tr>";
												}

												tabelleStrlen += "</tbody></table></div><br />";
											}

											if(skiped.length > 0) {
												// Blende Datenbank-Symbol (neben WiFi und Local-Storage Icon) ein
												$('#pendingresState').show();

												zeigeFehlerUpload = 1;

												tabelleSkiped = "<div class=\"table-wrapper\"><table><thead><tr><th align=\"left\" colspan=\"3\">Ergebnis bereits hochgeladen</th></tr><tr></tr><tr><th>Startnummer</th><th>Ergebnis</th><th>Position</th></tr></thead><tbody>";

												for(var i = 0; i < skiped.length; i++) {
													var explode = skiped[i].split(';');

													tabelleSkiped += "<tr><td>" + explode[0] + "</td><td>" + explode[1] + "</td><td>" + explode[2] + "</td></tr>";
												}

												tabelleSkiped += "</tbody></table></div><br />";
											}

											if(failed.length > 0) {
												// Blende Datenbank-Symbol (neben WiFi und Local-Storage Icon) ein
												$('#pendingresState').show();

												zeigeFehlerUpload = 1;

												tabelleFailed = "<div class=\"table-wrapper\"><table><thead><tr><th align=\"left\" colspan=\"3\">Speicherfehler</th></tr><tr></tr><tr><th>Startnummer</th><th>Ergebnis</th><th>Position</th></tr></thead><tbody>";

												for(var i = 0; i < failed.length; i++) {
													var explode = failed[i].split(';');

													tabelleFailed += "<tr><td>" + explode[0] + "</td><td>" + explode[1] + "</td><td>" + explode[2] + "</td></tr>";
												}

												tabelleFailed += "</tbody></table></div><br />";
											}

											if(errXXX.length > 0) {
												// Blende Datenbank-Symbol (neben WiFi und Local-Storage Icon) ein
												$('#pendingresState').show();

												zeigeFehlerUpload = 1;

												tabelleErrXXX = "<div class=\"table-wrapper\"><table><thead><tr><th align=\"left\" colspan=\"2\">Fehler</th></tr><tr></tr><tr><th>Ergebnis Datensatz</th></tr></thead><tbody>";

												for(var i = 0; i < errXXX.length; i++) {
													// Hole Fehlerstatus
													var errStatus = errXXX[i].split('#');

													tabelleErrXXX += "<tr><td>" + errStatus[1] + "</td></tr>";
												}

												tabelleErrXXX += "</tbody></table></div>";
											}

											// Wenn nichts vorhanden, blende dieses wieder aus
											if(
												strlen.length == 0 &&
												skiped.length == 0 &&
												failed.length == 0 &&
												errXXX.length == 0
											) {
												// Blende Datenbank-Symbol (neben WiFi und Local-Storage Icon) aus
												$('#pendingresState').hide();

												zeigeFehlerUpload = 0;
											}

											if(zeigeFehlerUpload == 1) {
												$('#pendingresState').click(function() {
													Swal.fire({
														allowOutsideClick: true,
														allowEscapeKey: true,
														type: 'warning',
														title: 'Fehlerhafte Ergebnisdaten!',
														html: 'Es wurden fehlerhafte Ergebnisdaten entdeckt, die aus dem lokalen Speicher entfernt wurden. Bitte sorgen Sie stets dafür, korrekte Ergebnisdaten zu übermitteln und die folgenden ggf. nachzutragen bzw. erneut einzutragen!<p>' + tabelleStrlen + tabelleSkiped + tabelleFailed + tabelleErrXXX + '</p>',
														showConfirmButton: true,
														focusConfirm: false,
														focusCancel: false,
														<?php
															if($darkmode == 0) {
																echo "confirmButtonText: '<span style=\"color:#fff!important;\">verstanden</span>',";
																echo "confirmButtonColor: '#8e6516',";
															} else {
																echo "confirmButtonText: '<span style=\"color:#f5eee1!important;\">verstanden</span>',";
																echo "confirmButtonColor: '#b68c2f',";
															}
														?>
														backdrop:	`
														<?php
															if($darkmode == 0) {
																echo "#ede7da";
															} else {
																echo "#404040";
															}
														?>
															center
															no-repeat
														`
													});
												});
											}
										}
									}
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
										cde = 'parsererror';
									} else if(exception === 'timeout') {
										msg = 'Timeout-Fehler.';
										cde = 'timeout';
									} else if(exception === 'abort') {
										msg = 'Anfrage abgebrochen.';
										cde = 'abort';
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
										footer: '<span style="font-size; small;">Sollte das Problem weiterhin bestehen, so kontaktieren Sie den Support unter dem aufgeführten Fehlercode!<br /><br /><em>Fehlercode: ' + cde + '/znImportRequest</em></span>',
										showConfirmButton: true,
										confirmButtonText: '<span style="color: #fff;"><i class="fas fa-redo"></i>&emsp;Anfrage erneut senden</span>',
										confirmButtonColor: '#b94a48',
										backdrop:	`
											linear-gradient(145deg, rgba(205,205,205,.75), rgba(173,173,173,.75))
											center
											no-repeat
										`
									}).then((result) => {
										// Erneuter Versuch Ergebnisdaten hochzuladend
										$('#importiereErgebnisdaten').trigger("click");
									});
								}
	            });
		        }
		    	});
				});

				// Initialisiere Tooltips jQuery-UI
				$(document).tooltip();

				// Starte Aktualisieren der Teilnehmer-Status Ergebnisliste
				auto_fetch();

				// Marker, um zu erfahren, ob erstmals aufgerufen oder bereits erfolgt
				var hasConnectivityProblem = 0;

				// Prüfe, ob localStorage verfügbar ist
				if(lsTest() === true) {
					// Local Storage verfügbar
					// Erstelle localStorage Objekt
					ergebnisspeicher = localStorage;

					Swal.fire({
						allowOutsideClick: false,
						allowEscapeKey: false,
						type: 'success',
						title: 'Wichtige Anmerkung!',
						html: 'Alle eingetragenen Ergebnisse werden lokal gesichert und in regelmäßigen Abständen hochgeladen, sollte eine Internetverbindung bestehen.<br /><hr /><i style="color:#468847;" class="fas fa-check"></i>&emsp;<strong>Ihr Browser verfügt über alle Voraussetzungen!</strong>',
						showConfirmButton: true,
						focusConfirm: false,
						focusCancel: false,
						<?php
							if($darkmode == 0) {
								echo "confirmButtonText: '<span style=\"color:#fff!important;\">verstanden</span>',";
								echo "confirmButtonColor: '#8e6516',";
							} else {
								echo "confirmButtonText: '<span style=\"color:#f5eee1!important;\">verstanden</span>',";
								echo "confirmButtonColor: '#b68c2f',";
							}
						?>
						backdrop:	`
						<?php
							if($darkmode == 0) {
								echo "#ede7da";
							} else {
								echo "#404040";
							}
						?>
							center
							no-repeat
						`
					});
				} else {
					// Local Storage nicht verfügbar
					// Blende Eingabemaske aus
					$('#wrapper').remove();

					Swal.fire({
						allowOutsideClick: false,
						allowEscapeKey: false,
						type: 'error',
						title: 'Veralteter Browser',
						text: 'Der von Ihnen verwendete Browser unterstützt keine lokale Speicherung. Um fortzufahren aktualisieren Sie diesen bitte auf die aktuellste Version!',
						showConfirmButton: true,
						<?php
							if($darkmode == 0) {
								echo "confirmButtonText: '<span style=\"color:#fff!important;\"><i class=\"fas fa-redo\"></i>&emsp;Seite aktualisieren</span>',";
							} else {
								echo "confirmButtonText: '<span style=\"color:#f5eee1!important;\"><i class=\"fas fa-redo\"></i>&emsp;Seite aktualisieren</span>',";
							}
						?>
						confirmButtonColor: '#b94a48',
						backdrop:	`
						<?php
							if($darkmode == 0) {
								echo "#ede7da";
							} else {
								echo "#404040";
							}
						?>
							center
							no-repeat
						`
					}).then((result) => {
						if(result.value) {
							location.href = "./index.php";
						}
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
				});

				setTimeout(function() {
					connectivity(0, 0, ergebnisspeicher);
				}, 20000);

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
			});

			// Erstelle Globals für Notify
			var successOptions = {
				globalPosition: 'bottom right',
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
				globalPosition: 'bottom right',
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
				globalPosition: 'bottom right',
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
				globalPosition: 'bottom right',
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

			// Live-Chat
			var menuRight = document.getElementById('cbp-spmenu-s2'),
					showRight = document.getElementById('showRight'),
					body = document.body;

			showRight.onclick = function() {
				classie.toggle(this, 'active');
				classie.toggle(menuRight, 'cbp-spmenu-open');
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

			function connectivity(connectivityProblemThen, connectivityProblemNow, ergebnisdaten) {
				var	p = new Ping();

				p.ping("https://google.de", function(err, data) {
					if(connectivityProblemNow === 1) {
						$.notify("Verbindungsaufbau ...", infoOptions);
						$('#connectionState').html("<i class=\"fas fa-wifi\" style=\"color: #f3f10e;\" title=\"Verbindungsaufbau ...\"></i>");

						// Hinweis für Teilnehmer-Status Liste
						$('#teilnehmerStatus').html('<div class="table-wrapper"><table><thead><tr><th>Verbindung unterbrochen ... <i class="fas fa-wifi" style="color: #f3f10e;" title="Verbindungsaufbau ..."></i></th></tr></thead></table></div>');
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

							// Beende Timeout
							clearTimeout(timeOut);

							// Markiere alle Aktionen, die eine aktive Internetverbindung benötigen
							$('.verbindungBenoetigt').addClass('offline');

							// Übergebe Hinweisfeld bei Überfahren der jeweiligen Elemente (Klasse: offline)
							$('.verbindungBenoetigt').attr('title', 'Für diese Aktion wird eine aktive Internetverbindung vorausgesetzt!');
						} else {
							setTimeout(function() {
								$.notify("Verbindung konnte nicht wiederhergestellt werden!", errorOptions);
							}, 2000);
						}

						// Verbindungsstatus
						$('#connectionState').html("<i class=\"fas fa-wifi\" style=\"color: #b94a48;\" title=\"Sie sind nicht mit dem Internet verbunden!\"></i>");

						// Hinweis für Teilnehmer-Status Liste
						$('#teilnehmerStatus').html('<div class="table-wrapper"><table><thead><tr><th>Sie sind nicht mit dem Internet verbunden! <i class="fas fa-wifi" style="color: #f3f10e;" title="Verbindungsabbruch"></i></th></tr></thead></table></div>');

						// Prüfe zusätzlich, ob alle Ergebnisdaten hochgeladen wurden
						if(ergebnisspeicher.length > 0) {
							// Prüfe, ob Verbindung vorhanden, ansonsten gebe Export Funktion aus
							$("#supportingText").html("<fieldset style=\"padding: 1em 1.5em; border: 1px solid #b94a48;\"><legend style=\"padding: 0 5px 0 5px;\"><i class=\"fas fa-exclamation-triangle\" style=\"color: #b94a48\"></i></legend><i class=\"fas fa-wifi\" style=\"color: #b94a48;\" title=\"Sie sind nicht mit dem Internet verbunden!\"></i>&emsp;<strong>Verbindungsabbruch!</strong><br /><br />Exportieren Sie etwaige nicht hochgeladene Ergebnisdaten. Ein späterer Upload erfolgt über die Menü-Option 'Ergebnisdaten importieren'.</span><p style=\"margin-top: 15px;\"><a href=\"#\" id=\"export\" class=\"button icon fa-download\">Ergebnisdaten exportieren</a></p></fieldset>");

							// Exportiere Ergebnisdaten
							$('#export').click(function() {
								var keyMuster = new RegExp('^([1-9]{1}[0-9]{0,2})\.{1}([S][t][a][r][t]|[Z][Z]([1-9]{1}[0-9]{0,2})|[Z][i][e][l]){1}$'),
										valMuster = new RegExp('^[1-9]{1}[0-9]{0,2}\;{1}[1-9]{1}[0-9]{0,2}\:{1}([0-5][0-9]){1}\:{1}([0-5][0-9]){1}\,{1}[0-9][0-9]\;{1}([S][t][a][r][t]|[Z][Z]([1-9]{1}[0-9]{0,2})|[Z][i][e][l]){1}$'),
										csvContent = "data:text/csv;charset=utf-8,";

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
					} else {
						// Korrigiere aktuellen Fehlerstand
						connectivityProblemThen = connectivityProblemNow;

						if(connectivityProblemNow === 1) {
							connectivityProblemNow = 0;
						}

						if(connectivityProblemThen === 1 && connectivityProblemNow === 0) {
							$.notify("Verbindung wiederhergestellt! Sie sind online!", successOptions);
							$('#connectionState').html("<i class=\"fas fa-wifi\" style=\"color: #468847;\" title=\"Sie sind mit dem Internet verbunden!\"></i>");

							// Hinweis für Teilnehmer-Status Liste
							$('#teilnehmerStatus').html('<div class="table-wrapper"><table><thead><tr><th>Teilnehmer-Status Liste wird aktualisiert! <i class="fas fa-wifi" style="color: #468847;" title="Verbindung wiederhergestellt"></i></th></tr></thead></table></div>');

							// Aktualisiere Teilnehmer-Status Liste
							auto_fetch();

							// Blende Export-Möglichkeit wieder aus
							$("#supportingText").html("");

							// Entferne Markierung für Elemente, die eine aktive Internetverbindung benötigen
							$('.verbindungBenoetigt').removeClass('offline');

							// Blende Hinweisfeld bei Überfahren der jeweiligen Elemente aus (Klasse: offline)
							$('.verbindungBenoetigt').removeAttr('title');
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
					connectivity(connectivityProblemThen, connectivityProblemNow, ergebnisdaten);
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
				console.log(ergebnisspeicher);
				// Leere vorherige Fehlertabelle
				var tabelleErrXXX = "",
						tabelleStrlen = "",
						tabelleSkiped = "",
						tabelleFailed = "";

				// Leere Array-Speicher
				strlen = [],
				failed = [],
				skiped = [],
				errXXX = [];

				// Entferne Fehler-Status Symbol (Datenbank-Icon oben rechts)
				$('#pendingresState').hide();

				// Lade Ergebnisdaten hoch
				$.ajax({
					url: "ladeErgebnisdatenHoch.ax.php",
					type: "POST",
					data:	{
						ergebnisdaten: JSON.stringify(ergebnisse)
					},
					dataType: "json",
					success: function(data) {
						/*
						 * Sofern kein Fehler vorhanden, enthält jedes
						 * Rückgabewert-Array Angaben über Status,
						 * Ergebnis und Array-Schlüssel
						 */
						 // Keine Session
 						if(data == "keineSession") {
 							Swal.fire({
 								allowOutsideClick: false,
 								allowEscapeKey: false,
 								type: 'error',
 								title: 'Login abgelaufen',
 								text: 'Ihr aktiver Login wurde beendet. Bitte loggen Sie sich erneut ein!',
 								footer: '<span style=\"font-size; small;\">Im Zweifelsfall kontaktieren Sie Ihren zuständigen Auswerter!<br /><br /><em>Fehlercode: znSessionExpired</em></span>',
 								showConfirmButton: true,
 								<?php
 									if($darkmode == 0) {
 										echo "confirmButtonText: '<span style=\"color:#fff!important;\"><i class=\"fas fa-redo\"></i>&emsp;Seite verlassen</span>',";
 									} else {
 										echo "confirmButtonText: '<span style=\"color:#f5eee1!important;\"><i class=\"fas fa-redo\"></i>&emsp;Seite verlassen</span>',";
 									}
 								?>
 								confirmButtonColor: '#b94a48',
 								backdrop:	`
 								<?php
 									if($darkmode == 0) {
 										echo "#ede7da";
 									} else {
 										echo "#404040";
 									}
 								?>
 									center
 									no-repeat
 								`
 							}).then((result) => {
 								if(result.value) {
 									location.href = "../index.php";
 								}
 							});
 						// Kein Zeitnehmer
						} else if(data == "keinZeitnehmer") {
							// Kein Zeitnehmer unter diesen Parametern vorhanden
							Swal.fire({
								allowOutsideClick: false,
								allowEscapeKey: false,
								type: 'error',
								title: 'Zeitnehmerdaten fehlerhaft',
								text: 'Entweder wurde Ihr Zugang zwischenzeitlich gelöscht, oder relevaten Daten sind fehlerhaft!',
								footer: '<span style=\"font-size; small;\">Im Zweifelsfall kontaktieren Sie Ihren zuständigen Auswerter!<br /><br /><em>Fehlercode: znDeletedAccount</em></span>',
								showConfirmButton: true,
								<?php
									if($darkmode == 0) {
										echo "confirmButtonText: '<span style=\"color:#fff!important;\"><i class=\"fas fa-redo\"></i>&emsp;Seite verlassen</span>',";
									} else {
										echo "confirmButtonText: '<span style=\"color:#f5eee1!important;\"><i class=\"fas fa-redo\"></i>&emsp;Seite verlassen</span>',";
									}
								?>
								confirmButtonColor: '#b94a48',
								backdrop:	`
								<?php
									if($darkmode == 0) {
										echo "#ede7da";
									} else {
										echo "#404040";
									}
								?>
									center
									no-repeat
								`
							}).then((result) => {
								if(result.value) {
									location.href = "../index.php";
								}
							});
						// Übergabe in Ordnung
						} else {
							console.log(data);

							// Durchlaufe Schleife, um hochgeladene Ergebnisdaten
							for(var i = 0; i < data.length; i++) {
								// Debugging
								console.log(data[i]);

								// Splitte für jeden Durchlauf aktuelle Ergebnisdaten auf
								var split = data[i].split('#');

								// Debugging
								console.log(split);

								// Prüfe zuerst alle erfolgreichen Einträge
								var status = split[0],
										ergdat = split[1],
										arrkey = split[2];

								switch(status) {
									// Ergebnis hochgeladen
									case "isokay":
										// Entferne hochgeladene und ungültige Ergebnisdaten aus Ergebnisspeicher
										delete ergebnisspeicher[arrkey];

										// Debugging
										console.log(status + " " + ergdat + " " + arrkey);
									break;
									// Ergebnislänge ungültig
									case "strlen":
										// Füge entsprechendem Array hinzu
										strlen.push(ergdat);

										// Entferne hochgeladene und ungültige Ergebnisdaten aus Ergebnisspeicher
										delete ergebnisspeicher[arrkey];
									break;
									// Ergebnis übersprungen (identisch)
									case "skiped":
										// Füge entsprechendem Array hinzu
										skiped.push(ergdat);

										// Entferne hochgeladene und ungültige Ergebnisdaten aus Ergebnisspeicher
										delete ergebnisspeicher[arrkey];
									break;
									// Ergebnis konnte nicht hochgeladen werden
									case "failed":
										// Füge entsprechendem Array hinzu
										failed.push(ergdat);

										// Entferne hochgeladene und ungültige Ergebnisdaten aus Ergebnisspeicher
										delete ergebnisspeicher[arrkey];
									break;
									/*
									 * Fehlercodes (Ergebnisdatensatz korrupt)
									 * Wenn Startnummer nicht gegeben ist (erste Zahl nach "err"),
									 * kann auch der zugehörige, lokal gespeicherte Datensatz nicht
									 * gelöscht werden, da der Index aus Startnummer und Position
									 * generiert wird (Bsp. 11.Start)
									 */
									case "errXXX":
										// Ergebnisdatensatz kann nicht aus lokalem Speicher gelöscht werden
										// Füge entsprechendem Array hinzu
										errXXX.push(status + '#' + ergdat);
									break;
									// Fehlercode mit funktionaler Startnummer
									case "err000":
										// Füge entsprechendem Array hinzu
										errXXX.push(status + '#' + ergdat);

										// Entferne hochgeladene und ungültige Ergebnisdaten aus Ergebnisspeicher
										delete ergebnisspeicher[arrkey];
									break;
								}
							}

							// Prüfe zusätzlich, ob alle Ergebnisdaten hochgeladen wurden
							if(ergebnisspeicher.length == 0) {
								if(
									strlen.length == 0 &&
									skiped.length == 0 &&
									failed.length == 0 &&
									errXXX.length == 0
								) {
									setTimeout(function() {
										$.notify("Alle bisherigen Ergebnisdaten wurden hochgeladen!", successOptions);
									}, 2000);
								} else {
									setTimeout(function() {
										Swal.fire({
											allowOutsideClick: true,
											allowEscapeKey: true,
											type: 'warning',
											title: 'Fehlerhafte Ergebnisdaten',
											html: 'Nicht alle Ihre eingegebenen Ergebnisdaten wurden hochgeladen! Einige fehlerhafte Ergebnisdatensätze wurden erkannt. Für eine genauere Info klicken Sie auf das Datenbank-Symbol oben links oder auf \'Info anzeigen\'!',
											showConfirmButton: true,
											showCancelButton: true,
											focusConfirm: true,
											focusCancel: false,
											<?php
												if($darkmode == 0) {
													echo "confirmButtonText: '<span style=\"color:#fff!important;\">verstanden</span>',";
													echo "confirmButtonColor: '#8e6516',";
													echo "cancelButtonText: '<span style=\"color:#fff!important;\">Info anzeigen</span>',";
													echo "cancelButtonColor: '#333',";
												} else {
													echo "confirmButtonText: '<span style=\"color:#f5eee1!important;\">verstanden</span>',";
													echo "confirmButtonColor: '#b68c2f',";
													echo "cancelButtonText: '<span style=\"color:#fff!important;\">Info anzeigen</span>',";
													echo "cancelButtonColor: '#c0c0c0',";
												}
											?>
											backdrop:	`
											<?php
												if($darkmode == 0) {
													echo "#ede7da";
												} else {
													echo "#404040";
												}
											?>
												center
												no-repeat
											`
										}).then((result) => {
											// Info anzeigen
											if(!result.value) {
												$('#pendingresState').trigger('click');
											}
										});
									}, 2000);
								}

								// Sofern es Fehler / Ungültigkeiten gab, gebe hier eine Meldung aus
								if(strlen.length > 0) {
									// Blende Datenbank-Symbol (neben WiFi und Local-Storage Icon) ein
									$('#pendingresState').show();

									zeigeFehlerUpload = 1;

									tabelleStrlen = "<div class=\"table-wrapper\"><table><thead><tr><th align=\"left\" colspan=\"3\">Ungültige Ergebnislänge</th></tr><tr></tr><tr><th>Startnummer</th><th>Ergebnis</th><th>Position</th></tr></thead><tbody>";

									for(var i = 0; i < strlen.length; i++) {
										var explode = strlen[i].split(';');

										tabelleStrlen += "<tr><td>" + explode[0] + "</td><td>" + explode[1] + "</td><td>" + explode[2] + "</td></tr>";
									}

									tabelleStrlen += "</tbody></table></div><br />";
								}

								if(skiped.length > 0) {
									// Blende Datenbank-Symbol (neben WiFi und Local-Storage Icon) ein
									$('#pendingresState').show();

									zeigeFehlerUpload = 1;

									tabelleSkiped = "<div class=\"table-wrapper\"><table><thead><tr><th align=\"left\" colspan=\"3\">Ergebnis bereits hochgeladen</th></tr><tr></tr><tr><th>Startnummer</th><th>Ergebnis</th><th>Position</th></tr></thead><tbody>";

									for(var i = 0; i < skiped.length; i++) {
										var explode = skiped[i].split(';');

										tabelleSkiped += "<tr><td>" + explode[0] + "</td><td>" + explode[1] + "</td><td>" + explode[2] + "</td></tr>";
									}

									tabelleSkiped += "</tbody></table></div><br />";
								}

								if(failed.length > 0) {
									// Blende Datenbank-Symbol (neben WiFi und Local-Storage Icon) ein
									$('#pendingresState').show();

									zeigeFehlerUpload = 1;

									tabelleFailed = "<div class=\"table-wrapper\"><table><thead><tr><th align=\"left\" colspan=\"3\">Speicherfehler</th></tr><tr></tr><tr><th>Startnummer</th><th>Ergebnis</th><th>Position</th></tr></thead><tbody>";

									for(var i = 0; i < failed.length; i++) {
										var explode = failed[i].split(';');

										tabelleFailed += "<tr><td>" + explode[0] + "</td><td>" + explode[1] + "</td><td>" + explode[2] + "</td></tr>";
									}

									tabelleFailed += "</tbody></table></div><br />";
								}

								if(errXXX.length > 0) {
									// Blende Datenbank-Symbol (neben WiFi und Local-Storage Icon) ein
									$('#pendingresState').show();

									zeigeFehlerUpload = 1;

									tabelleErrXXX = "<div class=\"table-wrapper\"><table><thead><tr><th align=\"left\" colspan=\"2\">Fehler</th></tr><tr></tr><tr><th>Ergebnis Datensatz</th></tr></thead><tbody>";

									for(var i = 0; i < errXXX.length; i++) {
										// Hole Fehlerstatus
										var errStatus = errXXX[i].split('#');

										tabelleErrXXX += "<tr><td>" + errStatus[1] + "</td></tr>";
									}

									tabelleErrXXX += "</tbody></table></div>";
								}

								// Wenn nichts vorhanden, blende dieses wieder aus
								if(
									strlen.length == 0 &&
									skiped.length == 0 &&
									failed.length == 0 &&
									errXXX.length == 0
								) {
									// Blende Datenbank-Symbol (neben WiFi und Local-Storage Icon) aus
									$('#pendingresState').hide();

									zeigeFehlerUpload = 0;
								}

								if(zeigeFehlerUpload == 1) {
									$('#pendingresState').click(function() {
										Swal.fire({
											allowOutsideClick: true,
											allowEscapeKey: true,
											type: 'warning',
											title: 'Fehlerhafte Ergebnisdaten!',
											html: 'Es wurden fehlerhafte Ergebnisdaten entdeckt, die aus dem lokalen Speicher entfernt wurden. Bitte sorgen Sie stets dafür, korrekte Ergebnisdaten zu übermitteln und die folgenden ggf. nachzutragen bzw. erneut einzutragen!<p>' + tabelleStrlen + tabelleSkiped + tabelleFailed + tabelleErrXXX + '</p>',
											showConfirmButton: true,
											focusConfirm: false,
											focusCancel: false,
											<?php
												if($darkmode == 0) {
													echo "confirmButtonText: '<span style=\"color:#fff!important;\">verstanden</span>',";
													echo "confirmButtonColor: '#8e6516',";
												} else {
													echo "confirmButtonText: '<span style=\"color:#f5eee1!important;\">verstanden</span>',";
													echo "confirmButtonColor: '#b68c2f',";
												}
											?>
											backdrop:	`
											<?php
												if($darkmode == 0) {
													echo "#ede7da";
												} else {
													echo "#404040";
												}
											?>
												center
												no-repeat
											`
										});
									});
								}
							}
						}
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
							cde = 'parsererror';
						} else if(exception === 'timeout') {
							msg = 'Timeout-Fehler.';
							cde = 'timeout';
						} else if(exception === 'abort') {
							msg = 'Anfrage abgebrochen.';
							cde = 'abort';
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
							footer: '<span style="font-size; small;">Sollte das Problem weiterhin bestehen, so kontaktieren Sie den Support unter dem aufgeführten Fehlercode!<br /><br /><em>Fehlercode: ' + cde + '/znUploadRequest</em></span>',
							showConfirmButton: true,
							confirmButtonText: '<span style="color: #fff;"><i class="fas fa-redo"></i>&emsp;Anfrage erneut senden</span>',
							confirmButtonColor: '#b94a48',
							backdrop:	`
								linear-gradient(145deg, rgba(205,205,205,.75), rgba(173,173,173,.75))
								center
								no-repeat
							`
						}).then((result) => {
							// Erneuter Versuch Ergebnisdaten hochzuladen
							ladeErgebnisdatenHoch(ergebnisspeicher);
						});
					}
				});
			}

			// Aktualisieren der Teilnehmer-Status Ergebnisliste
			function auto_fetch() {
				$.ajax({
					type: 'POST',
					url: 'holeTeilnehmerStatus.ax.php',
					success: function(html) {
						// Keine Session
						if(html == "keineSession") {
							Swal.fire({
								allowOutsideClick: false,
								allowEscapeKey: false,
								type: 'error',
								title: 'Login abgelaufen',
								text: 'Ihr aktiver Login wurde beendet. Bitte loggen Sie sich erneut ein!',
								footer: '<span style=\"font-size; small;\">Im Zweifelsfall kontaktieren Sie Ihren zuständigen Auswerter!<br /><br /><em>Fehlercode: znSessionExpired</em></span>',
								showConfirmButton: true,
								<?php
									if($darkmode == 0) {
										echo "confirmButtonText: '<span style=\"color:#fff!important;\"><i class=\"fas fa-redo\"></i>&emsp;Seite verlassen</span>',";
									} else {
										echo "confirmButtonText: '<span style=\"color:#f5eee1!important;\"><i class=\"fas fa-redo\"></i>&emsp;Seite verlassen</span>',";
									}
								?>
								confirmButtonColor: '#b94a48',
								backdrop:	`
								<?php
									if($darkmode == 0) {
										echo "#ede7da";
									} else {
										echo "#404040";
									}
								?>
									center
									no-repeat
								`
							}).then((result) => {
								if(result.value) {
									location.href = "../index.php";
								}
							});
						// Gebe Callback aus
						} else {
							$('#teilnehmerStatus').html(html);
						}
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
							cde = 'parsererror';
						} else if(exception === 'timeout') {
							msg = 'Timeout-Fehler.';
							cde = 'timeout';
						} else if(exception === 'abort') {
							msg = 'Anfrage abgebrochen.';
							cde = 'abort';
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
							footer: '<span style="font-size; small;">Sollte das Problem weiterhin bestehen, so kontaktieren Sie den Support unter dem aufgeführten Fehlecode!<br /><br /><em>Fehlercode: ' + cde + '/znFetchTStatus</em></span>',
							showConfirmButton: true,
							confirmButtonText: '<span style="color: #fff;"><i class="fas fa-redo"></i>&emsp;Anfrage erneut senden</span>',
							confirmButtonColor: '#b94a48',
							backdrop:	`
								linear-gradient(145deg, rgba(205,205,205,.75), rgba(173,173,173,.75))
								center
								no-repeat
							`
						}).then((result) => {
							// Erneuter Versuch Teilnehmer Informationen abzurufen
							auto_fetch();
						});
					}
				});

				timeOut = setTimeout(function() {
					auto_fetch();
				}, 15000);
			}
		</script>
	</body>
</html>
