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
	$error_maindescript = "Scheinbar ist Ihr Funktionär-Zugang nicht korrekt erstellt worden oder relevante Einstellungen wurden gelöscht / nicht vorgenommen. ";
	
	// CHECK FOR ERRORS
	if(isset($_GET['error'])) {
		switch($_GET['error']) {
			case "no_ftype":
				$error_header			=	"Fehlerhaften Login entdeckt";
				$error_code				=	"no_ftype";
				$error_last_action		=	"Login [Interface]";
				$error_description		=	"Kein Funktionär-Typ übergeben";
				$error_responsible		=	"User";
				$error_solution			=	"Auswahl des Funktionär-Types bei Login erforderlich!";
				$error_timestamp		=	date("d.m.Y - H:i:s");
			break;
			case "no_cred":
				$error_header			=	"Fehlerhaften Login entdeckt";
				$error_code				=	"no_log";
				$error_last_action		=	"Login [Interface]";
				$error_description		=	"Keine Logindaten übergeben";
				$error_responsible		=	"User";
				$error_solution			=	"Eingabe der Logindaten erforderlich!";
				$error_timestamp		=	date("d.m.Y - H:i:s");
			break;
			case "no_user":
				$error_header			=	"Fehlerhaften Login entdeckt";
				$error_code				=	"no_user";
				$error_last_action		=	"Login [Interface]";
				
				if(isset($_GET['add'])) {
					switch($_GET['add']) {
						case "zn":
							$additional = " als Zeitnehmer";
						break;
						case "zk":
							$additional = " als Zeitkontrolle";
						break;
						case "zs":
							$additional = " als Stempelkontrolle";
						break;
						case "bc":
							$additional = " als Bordkartenkontrolle";
						break;
						default:
							$additional = "";
						break;
					}
				}
				
				$error_description		=	"Benutzer existiert nicht" . $additional;
				$error_responsible		=	"User";
				$error_solution			=	"Korrekte Auswahl des Funktionär-Types!";
				$error_timestamp		=	date("d.m.Y - H:i:s");
			break;
			case "no_log":
				$error_header			=	"Fehlerhaften Login entdeckt";
				$error_code				=	"no_log";
				$error_last_action		=	"Login [Interface]";
				
				if(isset($_GET['add'])) {
					switch($_GET['add']) {
						case "zn":
							$additional = "Zeitnehmer-";
						break;
						case "zk":
							$additional = "Zeitkontrollen-";
						break;
						case "zs":
							$additional = "Stempelkontrollen-";
						break;
						case "bc":
							$additional = "Bordkarten-";
						break;
						default:
							$additional = "";
						break;
					}
				}
				
				$error_description		=	"Dieser " . $additional . "Zugang wurde gesperrt / neutralisiert!";
				$error_responsible		=	"User";
				$error_solution			=	"Sollte es sich hierbei um einen Fehler handeln, so wenden Sie sich bitte an Ihren zuständigen Auswerter!";
				$error_timestamp		=	date("d.m.Y - H:i:s");
			break;
			case "is_online":
				$error_header			=	"Fehlerhaften Login entdeckt";
				$error_code				=	"is_online";
				$error_last_action		=	"Login [Interface]";
				
				if(isset($_GET['add'])) {
					switch($_GET['add']) {
						case "zn":
							$additional = "Zeitnehmer-";
						break;
						case "zk":
							$additional = "Zeitkontrollen-";
						break;
						case "zs":
							$additional = "Stempelkontrollen-";
						break;
						case "bc":
							$additional = "Bordkarten-";
						break;
						default:
							$additional = "";
						break;
					}
				}
				
				$error_description		=	"Dieser " . $additional . "Zugang ist bereits eingeloggt!";
				$error_responsible		=	"User";
				$error_solution			=	"Bitte wenden Sie sich an Ihren zuständigen Auswerter, um ggf. einen manuellen Logout zu veranlassen!";
				$error_timestamp		=	date("d.m.Y - H:i:s");
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