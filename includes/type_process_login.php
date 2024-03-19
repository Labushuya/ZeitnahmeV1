<?php 
	//	Setze Fehlerlevel
	error_reporting(E_ALL);
	
	//	Binde relevante Dateien ein
	include_once 'db_connect.php';
	include_once 'functions.php';
	
	//	Starte Session
	session_start();
	
	//	POST-Variable korrekt übermittelt
	if(isset($_POST['login'])) {
		//	Setze Login-Post zurück
		unset($_POST['login']);
		
		//	Formular nicht leer
		if(($_POST['uname'] != "" OR !empty($_POST['uname'])) AND ($_POST['upass'] != "" OR !empty($_POST['upass']))) {
			//	Bereinige Übergabeparameter
			$uname = mysqli_real_escape_string($mysqli, $_POST['uname']);
			$upass = mysqli_real_escape_string($mysqli, $_POST['upass']);
			
			//	Prüfe auf Login-Typ
			if(isset($_POST['funktionaertyp'])) {
				switch($_POST['funktionaertyp']) {
					//	Zeitnehmer
					case "zn":
						//	Lege Abfrage-Parameter fest
						$select = "SELECT `uname`, `upass` FROM `_optio_zmembers` WHERE `uname` = '" . $uname . "' AND `upass` = '" . $upass . "'";
						
						//	Lege Tabelle für Update fest
						$table = "_optio_zmembers";
						
						//	Lege Funktionärtyp fest
						$ftype = "zn";
					break;
					//	Zeitkontrolle
					case "zk":
						//	Lege Abfrage-Parameter fest
						$select = "SELECT `uname`, `upass` FROM `_optio_zcontrol` WHERE `uname` = '" . $uname . "' AND `upass` = '" . $upass . "'";
						
						//	Lege Tabelle für Update fest
						$table = "_optio_zcontrol";
						
						//	Lege Funktionärtyp fest
						$ftype = "zk";
					break;
					//	Stempelkontrolle
					case "zs":
						//	Lege Abfrage-Parameter fest
						$select = "SELECT `uname`, `upass` FROM `_optio_zstamp` WHERE `uname` = '" . $uname . "' AND `upass` = '" . $upass . "'";
						
						//	Lege Tabelle für Update fest
						$table = "_optio_zstamp";
						
						//	Lege Funktionärtyp fest
						$ftype = "zs";
					break;
					//	Bordkartenkontrolle
					case "bc":
						//	Lege Abfrage-Parameter fest
						$select = "SELECT `uname`, `upass` FROM `_optio_bmembers` WHERE `uname` = '" . $uname . "' AND `upass` = '" . $upass . "'";
						
						//	Lege Tabelle für Update fest
						$table = "_optio_bmembers";
						
						//	Lege Funktionärtyp fest
						$ftype = "bc";
					break;
					//	Kein passender Funktionärtyp übergeben worden
					default:
						//	Breche Loginvorgang ab
						header('Location: /msdn/functionary_fail.php?error=no_ftype');
					break;
				}
				
				//	Suche nach Benutzer
				$result = mysqli_query($mysqli, $select);
				
				//	Datenabfrage(n)
				$getrow = mysqli_fetch_assoc($result);
				$numrow = mysqli_num_rows($result);
				
				//	Prüfe, ob User in Zeitnehmer Tabelle existiert
				if($numrow == 1) {
					//	Prüfe auf Zugangsberechtigung (Zugang gesperrt / blockiert?)
					if($getrow['neutralized'] == 0) {
						//	Prüfe, ob Nutzer bereits eingeloggt
						if($mz_getrow['active'] == 0) {
							//	Logge User ein
							$update =	"
										UPDATE
											`" . $table . "`
										SET
											`active` = 1,
											`logintime` = " . time() . "
										WHERE
											`uname` = " . $uname . "
										AND
											`upass` = " . $upass . "
										";
							mysqli_query($mysqli, $mz_update);
							
							//	Setze Session Variable basierend auf Funktionärtyp
							if($ftype == "zn") {
								$_SESSION['rid_type'] = $getrow['rid_type'];
								$_SESSION['rid'] = $getrow['rid'];
							}
							
							$_SESSION['user_id'] = $getrow['id'];
							$_SESSION['eid'] = $getrow['eid'];
							$_SESSION['username'] = $getrow['uname'];
							$_SESSION['opt_whois'] = $getrow['opt_whois'];
							$_SESSION['logtype']	= $ftype;
							
							//	Registriere Logeintrag
							$insert_log =	"
											INSERT INTO 
												`" . $table . "_log`(
													`id`,
											";
											
							if($ftype == "zn") {
								$insert_log .=	"	`zid`,";
							} elseif($ftype == "bc") {
								$insert_log .=	"	`bid`,";
							} else
													
							$insert_log .=	"		`eid`,";
							
							if($ftype == "zn") {
								$insert_log .=	"	`rid`,";
							}
							
							$insert_log .=	"		`logtime`,
													`action`
												)
											VALUES(
												'NULL',
												'" . $mz_getrow['id'] . "',
												'" . $_SESSION['eid'] . "',
												'" . $_SESSION['rid'] . "',
												'" . time() . "',
												'Login'
											)";
							$result_log = mysqli_query($mysqli, $insert_log);
						} else {
							//	Kein Login, da User bereits eingeloggt 
							header('Location: /msdn/functionary_fail.php?error=is_online&add=' . $ftype);
						}
					} else {
						//	Kein Login, da Zugang gesperrt / blockiert wurde
						header('Location: /msdn/functionary_fail.php?error=no_log&add=' . $ftype);
					}
				//	User nicht existent
				} else {
					//	Kein Login, da User nicht exitiert
					header('Location: /msdn/functionary_fail.php?error=no_user&add=' . $ftype);
				}
		} else {
			//	Kein Login, da Übergabeparameter nicht vollständig
			header('Location: /msdn/functionary_fail.php?error=no_cred');
		}
		
		
		//	Login-Typ nicht gesetzt
		} else {
			//	Beende Loginvorgang
			header('Location: /msdn/timebuddy_fail.php?error=0x2003');
		}
		
		
		
		
				
			//	Prüfe, ob User in Zeitnehmer Tabelle existiert
			if($mz_numrow == 1) {
				//	Prüfe auf Zugangsberechtigung (neutralisiert?)
				if($mz_getrow['neutralized'] == 0) {
					//	Prüfe, ob Nutzer bereits eingeloggt
					if($mz_getrow['active'] == 0) {
						//	Logge User ein
						mysqli_query($mysqli, $mz_update);
						
						//	Setze relevante Session
						$_SESSION['user_id'] = $mz_getrow['id'];
						$_SESSION['eid'] = $mz_getrow['eid'];
						$_SESSION['rid_type'] = $mz_getrow['rid_type'];
						$_SESSION['rid'] = $mz_getrow['rid'];
						$_SESSION['username'] = $mz_getrow['uname'];
						$_SESSION['opt_whois'] = $mz_getrow['opt_whois'];
						$_SESSION['logtype']	= "mz";
						
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
											'" . $mz_getrow['id'] . "',
											'" . $_SESSION['eid'] . "',
											'" . $_SESSION['rid'] . "',
											'" . time() . "',
											'Login'
										)";
						$result_log = mysqli_query($mysqli, $insert_log);
								
						//	Leite zu Zeitnehmer Interface weiter
						header('Location: /msdn/timebuddy.php');						
					} else {
						//	Kein Login, da User bereits eingeloggt 
						header('Location: /msdn/timebuddy_fail.php?error=0x2007');
					}
				} else {
					//	Kein Login, da Prüfung neutralisiert wurde
					header('Location: /msdn/timebuddy_fail.php?error=0x2006');
				}
			//	Prüfe, ob User in Bordkarten Tabelle existent
			} elseif($mz_numrow == 0) {
				//	Führe Select durch
				$mb_squery = mysqli_query($mysqli, $mb_select);
				
				//	Datenabfrage(n)
				$mb_getrow = mysqli_fetch_assoc($mb_squery);
				$mb_numrow = mysqli_num_rows($mb_squery);
				
				//	Prüfe auf Zugangsberechtigung (neutralisiert?)
				if($mb_getrow['neutralized'] == 0) {						
					//	Setze relevante Session
					$_SESSION['user_id'] = $mb_getrow['id'];
					$_SESSION['eid'] = $mb_getrow['eid'];
					$_SESSION['username'] = $mb_getrow['uname'];
					$_SESSION['opt_whois'] = $mb_getrow['opt_whois'];
					$_SESSION['logtype']	= "mb";
						
					//	Registriere Logeintrag
					$insert_log =	"
									INSERT INTO 
										`_optio_bmembers_log`(
											`id`,
											`bid`,
											`eid`,
											`logtime`,
											`action`
										)
									VALUES(
										'NULL',
										'" . $mb_getrow['id'] . "',
										'" . $_SESSION['eid'] . "',
										'" . time() . "',
										'Login'
									)";
					$result_log = mysqli_query($mysqli, $insert_log);
							
					//	Leite zu Zeitnehmer Interface weiter
					header('Location: /msdn/boardingpass.php');
				} else {
					//	Kein Login, da Prüfung neutralisiert wurde
					header('Location: /msdn/timebuddy_fail.php?error=0x2011');
				}
			//	User nicht existent
			} else {
				//	Kein Login, da User nicht exitiert
				header('Location: /msdn/timebuddy_fail.php?error=0x2012');
			}
		} else {
			//	Kein Login, da Übergabeparameter nicht vollständig
			header('Location: /msdn/timebuddy_fail.php?error=0x2004');
		}	
	//	POST-Variable nicht übermittelt oder Datei wurde direkt aufgerufen
	} else {
		//	Kein Login, da falsche Handhabung von Login-System
		header('Location: /msdn/timebuddy_fail.php?error=0x2003');
	}
?>