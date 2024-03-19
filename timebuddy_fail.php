<?php error_reporting(E_ALL);
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
	
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// START SECURE SESSION
	sec_session_start();
	 
	// CUSTOM NAVBAR
	$logged = file_get_contents("essentials/login.html");
	$navbar = file_get_contents("essentials/navbar_logged_out.html");
	
	//	Vorbelegen von Hauptbeschreibung
	$error_maindescript = "Scheinbar ist Ihr Zeitnehmer-Zugang nicht korrekt erstellt worden oder relevante Einstellungen wurden gelöscht / nicht vorgenommen. ";
	
	// CHECK FOR ERRORS
	if(isset($_GET['error'])) {
		switch($_GET['error']) {
			case "0x2000":
				$error_header			=	"Fehlerhaften Zugang entdeckt";
				$error_code				=	"0x2000";
				$error_last_action		=	"Login [Interface]";
				$error_description		=	"Zugang besitzt keine Prüfungszuweisung";
				$error_responsible		=	"Auswerter / Veranstalter";
				$error_solution			=	"Zeitnehmer-Zugang im Editiermodus neu anlegen und <u>Prüfungszuweisung</u> durchführen";
				$error_timestamp		=	date("d.m.Y - H:i:s");
			break;
			case "0x2001":
				$error_header			=	"Fehlerhaften Zugang entdeckt";
				$error_code				=	"0x2001";
				$error_last_action		=	"Login [Interface]";
				$error_description		=	"Zugang besitzt keine Positionenzuweisung";
				$error_responsible		=	"Auswerter / Veranstalter";
				$error_solution			=	"Zeitnehmer-Zugang im Editiermodus neu anlegen und <u>Positionenzuweisung</u> durchführen";
				$error_timestamp		=	date("d.m.Y - H:i:s");
			break;
			case "0x2002":
				switch(true) {
					case $_GET['id'] == "":
					case $_GET['id'] == 0:
						$error_header		=	"Fehlerhaften Datensatz entdeckt";
						$error_code			=	"0x2002";
						$error_description	=	"Teilnehmer besitzt mehrere identische Ergebnispositionen (Bsp. mehrmals Zielergebnis)";
						$error_responsible	= 	"Auswerter / Veranstalter";
						$error_solution		= 	"Auswerter muss identische Ergebnispositionen für Teilnehmer löschen. Anschließend Ergebnis erneut eintragen";
						$error_last_action	= 	"Formular [Verarbeitung]";
						$error_timestamp	= 	date("d.m.Y - H:i:s");
					break;
					case $_GET['id'] != "":
					case $_GET['id'] > 0:
						$error_header		=	"Fehlerhaften Datensatz entdeckt";
						$error_code			=	"0x2002";
						$error_last_action	=	"Formular [Verarbeitung]";
						$error_description	=	"Teilnehmer besitzt mehrere identische Ergebnispositionen (Bsp. mehrmals Zielergebnis)";
						$error_responsible	=	"Auswerter / Veranstalter";
						$error_solution		=	"
														Folgende Datensatznummer/n löschen: 
														<br />
														<ul>
													";
													$error_id = explode(".", $_GET['id']);
													for($i = 0; $i < count($error_id); $i++) {
														$error_solution .= "<li>ID: " . $error_id[$i] . "</li>";
													}
													$error_solution .="</ul>Anschließend Ergebnis erneut eintragen";
						$error_timestamp	=	date("d.m.Y - H:i:s");
					break;
					default:
						$error_header		=	"Fehlerhaften Datensatz entdeckt";
					break;
				}				
			break;
			case "0x2003":
				$error_header			=	"Fehlerhaften Zugang entdeckt";
				$error_code				=	"0x2003";
				$error_last_action		=	"Login [Interface]";
				$error_description		=	"Inkorrekte Handhabung des Login-Systems";
				$error_responsible		=	"Anwender / Benutzer";
				$error_solution			=	"Login-System bitte ordnungsgemäß verwenden - keine Direktaufrufe";
				$error_timestamp		=	date("d.m.Y - H:i:s");
			break;
			case "0x2004":
				$error_header			=	"Fehlerhaften Zugang entdeckt";
				$error_code				=	"0x2004";
				$error_last_action		=	"Login [Interface]";
				$error_description		=	"Fehlerhafte Übergabeparameter entdeckt";
				$error_responsible		=	"Anwender / Benutzer";
				$error_solution			=	"Login-System bitte ordnungsgemäß verwenden - keine Direktaufrufe";
				$error_timestamp		=	date("d.m.Y - H:i:s");
			break;
			case "0x2005":
				$error_header			=	"Fehlerhaften Zugang entdeckt";
				$error_code				=	"0x2005";
				$error_last_action		=	"Login [Interface]";
				$error_description		=	"Kein Zeitnehmer-Zugang mit dieser Kennung gefunden";
				$error_responsible		=	"Anwender / Benutzer / Auswerter / Veranstalter";
				$error_solution			=	"Überprüfen Sie Ihre Zugangsdaten und wenden Sie sich ggf. an Ihren zuständigen Veranstalter / Auswerter";
				$error_timestamp		=	date("d.m.Y - H:i:s");
			break;
			case "0x2006":
				$error_header			=	"Prüfung wurde neutralisiert";
				$error_code				=	"0x2006";
				$error_last_action		=	"Login [Interface]";
				$error_description		=	"Diese Prüfung wurde neutralisiert";
				$error_responsible		=	"Auswerter / Veranstalter";
				$error_solution			=	"Wenden Sie sich ggf. an Ihren zuständigen Veranstalter / Auswerter";
				$error_timestamp		=	date("d.m.Y - H:i:s");
				$error_maindescript		=	"";
			break;
			case "0x2007":
				$error_header			=	"Fehlerhaften Zugang entdeckt";
				$error_code				=	"0x2007";
				$error_last_action		=	"Login [Interface]";
				$error_description		=	"Dieser Zeitnehmer-Zugang wird bereits verwendet (<strong><span style='color: red;'>aktiver Login</span></strong>)";
				$error_responsible		=	"Anwender / Benutzer / Auswerter / Veranstalter";
				$error_solution			=	"Wenden Sie sich ggf. an Ihren zuständigen Veranstalter / Auswerter";
				$error_timestamp		=	date("d.m.Y - H:i:s");
			break;
			case "0x2008":
				$error_header			=	"Fehlerhaften Zugang entdeckt";
				$error_code				=	"0x2008";
				$error_last_action		=	"Login [Interface]";
				$error_description		=	"Dieser Zeitnehmer-Zugang existiert mehrfach";
				$error_responsible		=	"Auswerter / Veranstalter / System";
				$error_solution			=	"Wenden Sie sich ggf. an Ihren zuständigen Veranstalter / Auswerter";
				$error_timestamp		=	date("d.m.Y - H:i:s");
			break;
			case "0x2010":
				$error_header			=	"Logout war nicht möglich";
				$error_code				=	"0x2010";
				$error_last_action		=	"Logout [Interface]";
				$error_description		=	"Dieser Zeitnehmer-Zugang konnte nicht ausgeloggt werden";
				$error_responsible		=	"Auswerter / Veranstalter / System";
				$error_solution			=	"Loggen Sie sich erneut ein bzw. aus oder wenden Sie sich ggf. an Ihren zuständigen Veranstalter / Auswerter";
				$error_timestamp		=	date("d.m.Y - H:i:s");
				$error_maindescript		=	"";
			break;
			case "0x2011":
				$error_header			=	"Bordkarten Zugang neutralisiert";
				$error_code				=	"0x2006";
				$error_last_action		=	"Login [Interface]";
				$error_description		=	"Dieser Zugang wurde neutralisiert";
				$error_responsible		=	"Auswerter / Veranstalter";
				$error_solution			=	"Wenden Sie sich ggf. an Ihren zuständigen Veranstalter / Auswerter";
				$error_timestamp		=	date("d.m.Y - H:i:s");
				$error_maindescript		=	"";
			break;
			case "0x2012":
				$error_header			=	"Fehlerhaften Zugang entdeckt";
				$error_code				=	"0x2005";
				$error_last_action		=	"Login [Interface]";
				$error_description		=	"Kein Bordkarten Zugang mit dieser Kennung gefunden";
				$error_responsible		=	"Anwender / Benutzer / Auswerter / Veranstalter";
				$error_solution			=	"Überprüfen Sie Ihre Zugangsdaten und wenden Sie sich ggf. an Ihren zuständigen Veranstalter / Auswerter";
				$error_timestamp		=	date("d.m.Y - H:i:s");
			break;
			case "0x2013":
				$error_header			=	"Logout war nicht möglich";
				$error_code				=	"0x2010";
				$error_last_action		=	"Logout [Interface]";
				$error_description		=	"Dieser Bordkarten Zugang konnte nicht ausgeloggt werden";
				$error_responsible		=	"Auswerter / Veranstalter / System";
				$error_solution			=	"Loggen Sie sich erneut ein bzw. aus oder wenden Sie sich ggf. an Ihren zuständigen Veranstalter / Auswerter";
				$error_timestamp		=	date("d.m.Y - H:i:s");
				$error_maindescript		=	"";
			break;
			default:
				$error_header	=	"Fehlerhaften Zugang entdeckt";
		}
	} else {
		$error_header		= "Fehler";
		$error_code			= "Unbekannt";
		$error_description	= "Unbekannt";
		$error_responsible	= "Kundendienst";
		$error_solution		= "Unbekannt";
		$error_last_action	= "Unbekannt";
		$error_timestamp	= date("d.m.Y - H:i:s");
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
					<h3><? echo $error_header; ?></h3>
					<p><? echo $error_maindescript; ?></p>
					<p>
						<table cellspacing="5px" cellpadding="5px" style="border: 1px solid #FFFFFF; font-size: small;" width="385px">
							<tr>
								<th>Fehlercode:</th>
								<td><? echo $error_code; ?></td>
							</tr>
							<tr>
								<th>Letzte Aktion:</th>
								<td><? echo $error_last_action; ?></td>
							</tr>
							<tr>
								<th>Verantwortlicher:</th>
								<td><? echo $error_responsible; ?></td>
							</tr>
							<tr>
								<th>Fehlerbeschreibung:</th>
								<td><? echo $error_description; ?></td>
							</tr>
							<tr>
								<th>Lösung:</th>
								<td><? echo $error_solution; ?></td>
							</tr>
							<tr>
								<th>Zeitstempel:</th>
								<td><? echo $error_timestamp; ?></td>
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