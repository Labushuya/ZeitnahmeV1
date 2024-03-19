<?php 
	//	Setze Fehlerlevel
	error_reporting(E_ALL);
	
	//	Binde relevante Dateien ein
	include_once 'db_connect.php';
	include_once 'functions.php';
	
	//	Starte Session
	session_start();
	
	//	Zeitnehmer Login
	//	POST-Variable korrekt übermittelt
	if(isset($_POST['ft_login'])) {
		//	Formular nicht leer
		if(($_POST['ft_uname'] != "" OR !empty($_POST['ft_uname'])) AND ($_POST['ft_upass'] != "" OR !empty($_POST['ft_upass']))) {
		    $login_type = mysqli_real_escape_string($mysqli, $_POST['fsid']);
		    
			//	Setze Login-Post zurück
			unset($_POST['ft_login']);
			
			//  Basierend auf übergebenem Login-Typ wird in entsprechender Tabelle gesucht
			switch($login_type) {
			    //  Login als Zeitnehmer
			    case "mz":
			        $ft_table = "_optio_zmembers";
			        $log_column = "z";
			        $redirect = "timebuddy.php";
		        break;
		        //  Login als Zeitkontrolle
		        case "zk":
		            $ft_table = "_optio_zcontrol";
		            $log_column = "z";
		            $redirect = "zcontrol.php";
	            break;
	            //  Login als Stempelkontrolle
	            case "zs":
	                $ft_table = "_optio_zstamp";
	                $log_column = "z";
	                $redirect = "zstamp.php";
                break;
                //  Login als Bordkartenkontrolle
                case "bc":
                    $ft_table = "_optio_bmembers";
                    $log_column = "b";
                    $redirect = "boarding.php";
                break;
                //  Kein Login-Typ übergeben worden
                default:
                    $ft_table = "";
                    $log_column = "";
                    $redirect = "";
                break;
			}
			
			//	Bereinige Übergabeparameter
			$uname = mysqli_real_escape_string($mysqli, $_POST['ft_uname']);
			$upass = mysqli_real_escape_string($mysqli, $_POST['ft_upass']);
			
			//  Beginne Suche nach User, sofern $ft_table gültigen Wert besitzt
			if($ft_table != "" OR !empty($ft_table)) {
			    $select = "SELECT * FROM `" . $ft_table . "` WHERE `uname` = '" . $uname . "' AND `upass` = '" . $upass . "' LIMIT 1";
			    $result = mysqli_query($mysqli, $select);
			    $numrow = mysqli_num_rows($result);
			    
			    //  Wurde User gefunden
			    if($numrow == 1) {
			        //  Hole Werte aus Datenbank
			        $getrow = mysqli_fetch_assoc($result);
			        
			        //  Prüfe auf Zugangsberechtigung
			        //  User neutralisiert?
			        if($getrow['neutralized'] == 0) {
			            //  User bereits eingeloggt?
			            if($getrow['active'] == 0) {
			                //  Hole IP-Adresse von User
			                $ip_address = getRealIPAddress();
			                
			                //  Prüfe auf Protokoll
		                    $protocol = getIPProtocol($ip_address);
		                    
		                    if($protocol == 4) {
		                        $ip_column = "ipv4";
		                    } elseif($protocol == 6) {
		                        $ip_column = "ipv6";
		                    }
			                
			                $update =	"
						                UPDATE
					                        `" . $ft_table . "`
					                    SET
		                                    `active` = 1,
								            `logintime` = '" . time() . "',
								            `" . $ip_column . "` = '" . $ip_address . "'
							            WHERE
							                `uname` = '" . $uname . "' 
							            AND 
							                `upass` = '" . $upass . "'
						                ";
			                //	Logge User ein
						    mysqli_query($mysqli, $update);
						    
						    //	Setze relevante Session
					    	$_SESSION['user_id'] = $getrow['id'];
					    	$_SESSION['eid'] = $getrow['eid'];
					    	
					    	//  Zusätzlich für Zeitnehmer
					    	if($login_type == "mz") {
						        $_SESSION['rid_type'] = $getrow['rid_type'];
						        $_SESSION['rid'] = $getrow['rid'];
					    	}
					    	
						    $_SESSION['username'] = $getrow['uname'];
						    $_SESSION['opt_whois'] = $getrow['opt_whois'];
						    $_SESSION['logtype'] = $login_type;
						    
						    //	Registriere Logeintrag
						    $insert_log =	"
						                    INSERT INTO 
										    	`" . $ft_table . "_log`(
						                            `id`,
						                            `" . $log_column . "id`,
						                            `eid`,
						                            `logtime`,
						                            `action`
						                        )
					                        VALUES(
					                            NULL,
											    '" . $getrow['id'] . "',
											    '" . $getrow['eid'] . "',
											    '" . time() . "',
											    'Login'
					                        )
						                    ";
				            $result_log = mysqli_query($mysqli, $insert_log);
				            
				            //	Leite zu Funktionär Interface weiter
						    header('Location: /msdn/' . $redirect);
			            //  User bereits eingeloggt
			            } elseif($getrow['active'] == 1) {
							//  Prüfe, ob Login-Versuch von selber IP-Adresse kommt
		                    //  Hole IP Adresse
		                    $ip_address = getRealIPAddress();
		                    
		                    //  Prüfe auf Protokoll
		                    $protocol = getIPProtocol($ip_address);
		                    
		                    if($protocol == 4) {
		                        $ip_column = "`ipv4`";
		                    } elseif($protocol == 6) {
		                        $ip_column = "`ipv6`";
		                    } else {
		                        $protocol = "";
		                    }
		                    
		                    //  Wurde Protokoll ermittelt, kann danach gesucht werden
		                    if($protocol != "" OR !empty($protocol)) {
								$select_ip = "SELECT * FROM " . $ft_table . " WHERE `id` = '" . $getrow['id'] . "' AND " . $ip_column . " = '" . $ip_address . "' LIMIT 1";
		                        $result_ip = mysqli_query($mysqli, $select_ip);
		                        $numrow_ip = mysqli_num_rows($result_ip);
		                        
		                        //  Wurde ein Ergebnis gefunden, logge User ein
		                        if($numrow_ip == 1) {
									$update =	"
						                        UPDATE
					                                `" . $ft_table . "`
					                            SET
					                                `active` = 1,
								                    `logintime` = '" . time() . "',
								                    `" . $ip_column . "` = '" . $ip_address . "'
							                    WHERE
							                        " . $ip_column . " = '" . $ip_address . "'
						                        ";
						            //	Logge User ein
						            mysqli_query($mysqli, $update);  
						            
						            //	Setze relevante Session
					    	        $_SESSION['user_id'] = $getrow['id'];
					    	        $_SESSION['eid'] = $getrow['eid'];
					    	        
					    	        //  Zusätzlich für Zeitnehmer
					    	        if($login_type == "mz" OR $login_type == "zm") {
					    	            $_SESSION['rid_type'] = $getrow['rid_type'];
						                $_SESSION['rid'] = $getrow['rid'];
					    	        }
					    	        
					    	        $_SESSION['username'] = $getrow['uname'];
						            $_SESSION['opt_whois'] = $getrow['opt_whois'];
					    	        $_SESSION['logtype'] = $login_type;
					    	        
			    	                //	Registriere Logeintrag
				                    $insert_log =	" 
					    	                        INSERT INTO 
										    	        `" . $ft_table . "_log`(
										    	            `id`,
						                                    `" . $log_column . "id`,
						                                    `eid`,
						                                    `logtime`,
						                                    `action`
						                                )
					                                VALUES(
					                                    NULL,
											            '" . $getrow['id'] . "',
											            '" . $getrow['eid'] . "',
											            '" . time() . "',
											            'Login'
				                                    )
					    	                        ";
					    	        $result_log = mysqli_query($mysqli, $insert_log);
					    	        
					    	        //	Leite zu Funktionär Interface weiter
						            header('Location: /msdn/' . $redirect);
		                        } elseif($numrow_ip == 0) {
		                            //  Leite weiter auf Fehlerseite
		                            header("Location: /msdn/error.php?code=login&add=loggedin&type=" . $login_type);
		                        }
		                    //  Protokoll konnte nicht ermittelt werden
		                    } else {
		                        //  Leite weiter auf Fehlerseite
		                        header("Location: /msdn/error.php?code=login&add=loggedin&type=" . $login_type);
		                    }
			            }
		            //  Login Berechtigung wurde neutralisiert
			        } else {
			            //  Leite weiter auf Fehlerseite
			            header("Location: /msdn/error.php?code=login&add=neutralized&type=" . $login_type);
			        }
		        //  User nicht gefunden
			    } elseif($numrow == 0) {
			        //  Leite weiter auf Fehlerseite
			        header("Location: /msdn/error.php?code=login&add=noresult&type=" . $login_type);
			    }
			} else {
			    //  Leite weiter auf Fehlerseite
			    header("Location: /msdn/error.php?code=login&add=noval");
			}
		} else {
			//	Kein Login, da Übergabeparameter nicht vollständig
			header("Location: /msdn/error.php?code=login&add=missing");
		}	
	//	POST-Variable nicht übermittelt oder Datei wurde direkt aufgerufen
	} else {
		//	Kein Login, da falsche Handhabung von Login-System
		header("Location: /msdn/index.php");
	}
?>