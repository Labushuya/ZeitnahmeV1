<?php error_reporting(E_ALL);
	date_default_timezone_set("Europe/Berlin");

	// INCLUDE CONFIG
	include_once 'psl-config.php';

	// SECURE SESSION
	function sec_session_start() {
		$session_name = 'sec_session_id';   // vergib einen Sessionnamen
		$secure = SECURE;
		// Damit wird verhindert, dass JavaScript auf die session id zugreifen kann.
		$httponly = true;
		// Zwingt die Sessions nur Cookies zu benutzen.
		if(ini_set('session.use_only_cookies', 1) === FALSE) {
			header("Location: /msdn/error.php?err=Could not initiate a safe session (ini_set)");
			exit();
		}
		// Holt Cookie-Parameter.
		$cookieParams = session_get_cookie_params();
		session_set_cookie_params($cookieParams["lifetime"],
			$cookieParams["path"],
			$cookieParams["domain"],
			$secure,
			$httponly);
		// Setzt den Session-Name zu oben angegebenem.
		session_name($session_name);
		@session_start();            // Startet die PHP-Sitzung
		session_regenerate_id();    // Erneuert die Session, löscht die alte.
	}

	// ALOGIN
	function login($email, $password, $mysqli) {
		// Das Benutzen vorbereiteter Statements verhindert SQL-Injektion.
		if($stmt = $mysqli->prepare("SELECT id, username, password, salt FROM _main_amembers WHERE email = ? LIMIT 1")) {
			$stmt->bind_param('s', $email);  // Bind "$email" to parameter.
			$stmt->execute();    // Führe die vorbereitete Anfrage aus.
			$stmt->store_result();

			// hole Variablen von result.
			$stmt->bind_result($uid, $username, $db_password, $salt);
			$stmt->fetch();

			// hash das Passwort mit dem eindeutigen salt.
			$password = hash('sha512', $password . $salt);
			if($stmt->num_rows == 1) {
				// Wenn es den Benutzer gibt, dann wird überprüft ob das Konto
				// blockiert ist durch zu viele Login-Versuche

				if(checkbrute($uid, $mysqli) == true) {
					// Konto ist blockiert
					// Schicke E-Mail an Benutzer, dass Konto blockiert ist
					return false;
				} else {
					// Überprüfe, ob das Passwort in der Datenbank mit dem vom
					// Benutzer angegebenen übereinstimmt.
					if($db_password == $password) {
						// Passwort ist korrekt!
						// Hole den user-agent string des Benutzers.
						$user_browser = $_SERVER['HTTP_USER_AGENT'];
						// XSS-Schutz, denn eventuell wir der Wert gedruckt
						$uid = preg_replace("/[^0-9]+/", "", $uid);
						$_SESSION['user_id'] = $uid;
						// XSS-Schutz, denn eventuell wir der Wert gedruckt
						$username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $username);
						$_SESSION['username'] = $username;
						$_SESSION['login_string'] = hash('sha512', $password . $user_browser);
						// Login erfolgreich.
						return true;
					} else {
						// Passwort ist nicht korrekt
						// Der Versuch wird in der Datenbank gespeichert
						$now = time();
						$mysqli->query("INSERT INTO login_attempts(user_id, time)
										VALUES ('$uid', '$now')");
						return false;
					}
				}
			} else {
				//Es gibt keinen Benutzer.
				return false;
			}
		}
	}

	// VLOGIN
	function vlogin($email, $password, $mysqli) {
		// Das Benutzen vorbereiteter Statements verhindert SQL-Injektion.
		if($stmt = $mysqli->prepare("SELECT id, username, password, salt FROM _main_vmembers WHERE email = ? LIMIT 1")) {
			$stmt->bind_param('s', $email);  // Bind "$email" to parameter.
			$stmt->execute();    // Führe die vorbereitete Anfrage aus.
			$stmt->store_result();

			// hole Variablen von result.
			$stmt->bind_result($uid, $username, $db_password, $salt);
			$stmt->fetch();

			// hash das Passwort mit dem eindeutigen salt.
			$password = hash('sha512', $password . $salt);
			if($stmt->num_rows == 1) {
				// Wenn es den Benutzer gibt, dann wird überprüft ob das Konto
				// blockiert ist durch zu viele Login-Versuche

				if(checkbrute($uid, $mysqli) == true) {
					// Konto ist blockiert
					// Schicke E-Mail an Benutzer, dass Konto blockiert ist
					return false;
				} else {
					// Überprüfe, ob das Passwort in der Datenbank mit dem vom
					// Benutzer angegebenen übereinstimmt.
					if($db_password == $password) {
						// Passwort ist korrekt!
						// Hole den user-agent string des Benutzers.
						$user_browser = $_SERVER['HTTP_USER_AGENT'];
						// XSS-Schutz, denn eventuell wir der Wert gedruckt
						$uid = preg_replace("/[^0-9]+/", "", $uid);
						$_SESSION['user_id'] = $uid;
						// XSS-Schutz, denn eventuell wir der Wert gedruckt
						$username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $username);
						$_SESSION['username'] = $username;
						$_SESSION['login_string'] = hash('sha512', $password . $user_browser);
						// Login erfolgreich.
						return true;
					} else {
						// Passwort ist nicht korrekt
						// Der Versuch wird in der Datenbank gespeichert
						$now = time();
						$mysqli->query("INSERT INTO login_attempts(user_id, time)
										VALUES ('" . $uid . "', '" . $now . "')");
						return false;
					}
				}
			} else {
				//Es gibt keinen Benutzer.
				return false;
			}
		}
	}

	// ZLOGIN
	function zlogin($username, $password, $mysqli) {
		// Das Benutzen vorbereiteter Statements verhindert SQL-Injektion.
		if($stmt = $mysqli->prepare("SELECT id, username, password, salt FROM _optio_zmembers WHERE username = ? LIMIT 1")) {
			$stmt->bind_param('s', $username);  // Bind "$email" to parameter.
			$stmt->execute();    // Führe die vorbereitete Anfrage aus.
			$stmt->store_result();

			// hole Variablen von result.
			$stmt->bind_result($uid, $username, $db_password, $salt);
			$stmt->fetch();

			// hash das Passwort mit dem eindeutigen salt.
			$password = hash('sha512', $password . $salt);
			if($stmt->num_rows == 1) {
				// Wenn es den Benutzer gibt, dann wird überprüft ob das Konto
				// blockiert ist durch zu viele Login-Versuche

				if(checkbrute($uid, $mysqli) == true) {
					// Konto ist blockiert
					// Schicke E-Mail an Benutzer, dass Konto blockiert ist
					return false;
				} else {
					// Überprüfe, ob das Passwort in der Datenbank mit dem vom
					// Benutzer angegebenen übereinstimmt.
					if($db_password == $password) {
						// Passwort ist korrekt!
						// Hole den user-agent string des Benutzers.
						$user_browser = $_SERVER['HTTP_USER_AGENT'];
						// XSS-Schutz, denn eventuell wir der Wert gedruckt
						$uid = preg_replace("/[^0-9]+/", "", $uid);
						$_SESSION['user_id'] = $uid;
						// XSS-Schutz, denn eventuell wir der Wert gedruckt
						$username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $username);
						$_SESSION['username'] = $username;
						$_SESSION['login_string'] = hash('sha512', $password . $user_browser);
						// Login erfolgreich.
						return true;
					} else {
						// Passwort ist nicht korrekt
						// Der Versuch wird in der Datenbank gespeichert
						$now = time();
						$mysqli->query("INSERT INTO login_attempts(user_id, time)
										VALUES ('$uid', '$now')");
						return false;
					}
				}
			} else {
				//Es gibt keinen Benutzer.
				return false;
			}
		}
	}

	// TLOGIN
	function tlogin($username, $password, $mysqli) {
		// Das Benutzen vorbereiteter Statements verhindert SQL-Injektion.
		if($stmt = $mysqli->prepare("SELECT id, username, password, salt FROM _optio_zmembers WHERE username = ? LIMIT 1")) {
			$stmt->bind_param('s', $username);  // Bind "$email" to parameter.
			$stmt->execute();    // Führe die vorbereitete Anfrage aus.
			$stmt->store_result();

			// hole Variablen von result.
			$stmt->bind_result($uid, $username, $db_password, $salt);
			$stmt->fetch();

			// hash das Passwort mit dem eindeutigen salt.
			$password = hash('sha512', $password . $salt);
			if($stmt->num_rows == 1) {
				// Wenn es den Benutzer gibt, dann wird überprüft ob das Konto
				// blockiert ist durch zu viele Login-Versuche

				if(checkbrute($uid, $mysqli) == true) {
					// Konto ist blockiert
					// Schicke E-Mail an Benutzer, dass Konto blockiert ist
					return false;
				} else {
					// Überprüfe, ob das Passwort in der Datenbank mit dem vom
					// Benutzer angegebenen übereinstimmt.
					if($db_password == $password) {
						// Passwort ist korrekt!
						// Hole den user-agent string des Benutzers.
						$user_browser = $_SERVER['HTTP_USER_AGENT'];
						// XSS-Schutz, denn eventuell wir der Wert gedruckt
						$uid = preg_replace("/[^0-9]+/", "", $uid);
						$_SESSION['user_id'] = $uid;
						// XSS-Schutz, denn eventuell wir der Wert gedruckt
						$username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $username);
						$_SESSION['username'] = $username;
						$_SESSION['login_string'] = hash('sha512', $password . $user_browser);
						// Login erfolgreich.
						return true;
					} else {
						// Passwort ist nicht korrekt
						// Der Versuch wird in der Datenbank gespeichert
						$now = time();
						$mysqli->query("INSERT INTO login_attempts(user_id, time)
										VALUES ('" . $uid . "', '" . $now . "')");
						return false;
					}
				}
			} else {
				//Es gibt keinen Benutzer.
				return false;
			}
		}
	}

	// CHECK BRUTE FORCE
	function checkbrute($uid, $mysqli) {
		// Hole den aktuellen Zeitstempel
		$now = time();

		// Alle Login-Versuche der letzten zwei Stunden werden gezählt.
		$valid_attempts = $now - (2 * 60 * 60);

		if($stmt = $mysqli->prepare("SELECT time
								 FROM login_attempts <code><pre>
								 WHERE user_id = ?
								AND time > '" . $valid_attempts . "'")) {
			$stmt->bind_param('i', $uid);

			// Führe die vorbereitet Abfrage aus.
			$stmt->execute();
			$stmt->store_result();

			// Wenn es mehr als 5 fehlgeschlagene Versuche gab
			if($stmt->num_rows > 5) {
				return true;
			} else {
				return false;
			}
		}
	}

	// LOGIN CHECK
	function login_check($mysqli) {
		// Überprüfe, ob alle Session-Variablen gesetzt sind
		if(isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['login_string'])) {
			$uid = $_SESSION['user_id'];
			$login_string = $_SESSION['login_string'];
			$username = $_SESSION['username'];

			// Hole den user-agent string des Benutzers.
			$user_browser = $_SERVER['HTTP_USER_AGENT'];

			if($stmt = $mysqli->prepare("SELECT password FROM _main_amembers WHERE id = ? LIMIT 1")) {
				// Bind "$uid" zum Parameter.
				$stmt->bind_param('i', $uid);
				$stmt->execute();   // Execute the prepared query.
				$stmt->store_result();

				if($stmt->num_rows == 1) {
					// Wenn es den Benutzer gibt, hole die Variablen von result.
					$stmt->bind_result($password);
					$stmt->fetch();
					$login_check = hash('sha512', $password . $user_browser);

					if($login_check == $login_string) {
						// Eingeloggt!!!!
						return true;
					} else {
						// Nicht eingeloggt
						return false;
					}
				} else {
					// Nicht eingeloggt
					return false;
				}
			} else {
				// Nicht eingeloggt
				return false;
			}
		} else {
			// Nicht eingeloggt
			return false;
		}
	}

	// CLEAN URL FROM PHP_SELF
	function esc_url($url) {
		if('' == $url) {
			return $url;
		}

		$url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);

		$strip = array('%0d', '%0a', '%0D', '%0A');
		$url = (string) $url;

		$count = 1;
		while ($count) {
			$url = str_replace($strip, '', $url, $count);
		}

		$url = str_replace(';//', '://', $url);

		$url = htmlentities($url);

		$url = str_replace('&amp;', '&#038;', $url);
		$url = str_replace("'", '&#039;', $url);

		if($url[0] !== '/') {
			// Wir wollen nur relative Links von $_SERVER['PHP_SELF']
			return '';
		} else {
			return $url;
		}
	}

	// MESSENGER
	function get_data($mysqli) {
		// MAKE SEARCH QUERIES
		//$search_main_admins		= 'SELECT * FROM _main_admins WHERE `id` = '.$_SESSION['user_id'].'';
		$search_main_amembers	= 'SELECT * FROM _main_amembers WHERE `id` = '.$_SESSION['user_id'].'';
		$search_main_vmembers	= 'SELECT * FROM _main_vmembers WHERE `id` = '.$_SESSION['user_id'].'';
		$search_optio_vmembers	= 'SELECT * FROM _optio_vmembers WHERE `id` = '.$_SESSION['user_id'].'';
		$search_optio_zmembers	= 'SELECT * FROM _optio_zmembers WHERE `id` = '.$_SESSION['user_id'].'';

		if($mysqli == true) {
			// MAKE RESULT QUERIES
			//$result_main_admins = mysqli_query($mysqli, $search_main_admins);
			//$anzahl_main_admins = mysqli_num_rows($result_main_admins);

			$result_main_amembers = mysqli_query($mysqli, $search_main_amembers);
			$anzahl_main_amembers = mysqli_num_rows($result_main_amembers);

			$result_main_vmembers = mysqli_query($mysqli, $search_main_vmembers);
			$anzahl_main_vmembers = mysqli_num_rows($result_main_vmembers);

			$result_optio_vmembers = mysqli_query($mysqli, $search_optio_vmembers);
			$anzahl_optio_vmembers = mysqli_num_rows($result_optio_vmembers);

			$result_optio_zmembers = mysqli_query($mysqli, $search_optio_zmembers);
			$anzahl_optio_zmembers = mysqli_num_rows($result_optio_zmembers);

			// EXECUTE QUERIES
			/*if($anzahl_main_admins > 0 OR $anzahl_main_admins == 1) {
				while($datensatz_main_admins = mysqli_fetch_assoc($result_main_admins)) {
					$eid	= $datensatz_main_admins['id'];
					$color		= $datensatz_main_admins['color'];
					$vname 		= $datensatz_main_admins['vname'];
					$nname 		= $datensatz_main_admins['nname'];
					$type  		= "Admin";
					$_SESSION['eid']	= $eid;
					$_SESSION['vname']		= $vname;
					$_SESSION['nname']		= $nname;
					$_SESSION['type'] 		= $type;
					$_SESSION['color'] 		= $color;
				}
				return true;
			} else*/if($anzahl_main_amembers > 0 OR $anzahl_main_amembers == 1) {
				while($datensatz_main_amembers = mysqli_fetch_assoc($result_main_amembers)) {
					$eid	= $datensatz_main_amembers['id'];
					$color 		= $datensatz_main_amembers['color'];
					$vname 		= $datensatz_main_amembers['vname'];
					$nname 		= $datensatz_main_amembers['nname'];
					$type  		= "Auswerter";
					$_SESSION['eid']	= $eid;
					$_SESSION['vname']		= $vname;
					$_SESSION['nname']		= $nname;
					$_SESSION['type'] 		= $type;
					$_SESSION['color'] 		= $color;
					$_SESSION['logtype']	= "aw";
				}
				return true;
			} elseif($anzahl_main_vmembers > 0 OR $anzahl_main_vmembers == 1) {
				while($datensatz_main_vmembers = mysqli_fetch_assoc($result_main_vmembers)) {
					$eid	= $datensatz_main_vmembers['id'];
					$color 		= $datensatz_main_vmembers['color'];
					$vname 		= $datensatz_main_vmembers['vname'];
					$nname 		= $datensatz_main_vmembers['nname'];
					$type  		= "Veranstalter [M]";
					$_SESSION['eid']	= $eid;
					$_SESSION['vname']		= $vname;
					$_SESSION['nname']		= $nname;
					$_SESSION['type'] 		= $type;
					$_SESSION['color'] 		= $color;
					$_SESSION['logtype']	= "va";
				}
				return true;
			} elseif($anzahl_optio_vmembers > 0 OR $anzahl_optio_vmembers == 1) {
				while($datensatz_optio_vmembers = mysqli_fetch_assoc($result_optio_vmembers)) {
					$eid	= $datensatz_optio_vmembers['eid'];
					$color 		= $datensatz_optio_vmembers['color'];
					$vname 		= $datensatz_optio_vmembers['vname'];
					$nname 		= $datensatz_optio_vmembers['nname'];
					$type  		= "Veranstalter [O]";
					$_SESSION['eid']	= $eid;
					$_SESSION['vname']		= $vname;
					$_SESSION['nname']		= $nname;
					$_SESSION['type'] 		= $type;
					$_SESSION['color'] 		= $color;
					$_SESSION['logtype']	= "ov";
				}
				return true;
			} elseif($anzahl_optio_zmembers > 0 OR $anzahl_optio_zmembers == 1) {
				while($datensatz_optio_zmembers = mysqli_fetch_assoc($result_optio_zmembers)) {
					$eid	= $datensatz_optio_zmembers['eid'];
					$vname 		= $datensatz_optio_zmembers['vname'];
					$nname 		= $datensatz_optio_zmembers['nname'];
					if($datensatz_optio_zmembers['position'] == "Start") {
						$type  	= "Zeitnehmer [S]";
						$color 	= "FF0000";
					} elseif($datensatz_optio_zmembers['position'] == "Ziel") {
						$type  	= "Zeitnehmer [Z]";
						$color 	= "00FF00";
					}
					$_SESSION['eid']	= $eid;
					$_SESSION['vname']		= $vname;
					$_SESSION['nname']		= $nname;
					$_SESSION['type'] 		= $type;
					$_SESSION['color'] 		= $color;
					$_SESSION['logtype']	= "mz";
				}
				return true;
			}
		} else {
			echo "Keine Verbindung zur Datenbank möglich!";
			return false;
		}
	}

	// CONVERT DATE (FROM DB):
	function convert_from_db($datum) {
		$jahr = substr($datum, 0, 4);
		$mon  = substr($datum, 5, 2);
		$tag  = substr($datum, 8, 2);
		$datneu = $tag . '.' . $mon . '.' . $jahr;
	return $datneu;
	}

	// CONVERT DATE (TO DB):
	function convert_to_db($datum) {
		$jahr = substr($datum, 6, 4);
		$mon  = substr($datum, 3, 2);
		$tag  = substr($datum, 0, 2);
		$datneu = $jahr . '-' . $mon . '-' . $tag;
	return $datneu;
	}

	function multiexplode($delimiters, $string) {
		$ready = str_replace($delimiters, $delimiters[0], $string);
		$launch = explode($delimiters[0], $ready);
		return $launch;
	}

	/*
	function decimal_to_time($seconds) {
		$t = $seconds;

		return sprintf('%02d:%02d:%02d.%02d', ($t / 3600),(($t / 60) % 60), ($t % 60), ($t));
	}

	function timeinsec($ms_total) {
		// EXPLODE STRING
		$ms_total = multiexplode(array(":", "."), $ms_total);

		$min = intval($ms_total[0]);
		$sek = intval($ms_total[1]);
		$ms	= intval($ms_total[2]);

		$ms_total = (60 * $min + $sek) * 1000 + $ms;

		return $ms_total;
	}
	*/

	// PASS TIME AS DECIMAL (E. G. 120.03 => 00:02:00,03)
	function convertTime($decimal_seconds) {
		if($decimal_seconds != 0) {
			// Splitte Ergebnis
			$split = explode('.', $decimal_seconds);

			// Ermittle Sekunden
			$seconds = (int)$split[0];

			// Ermittle Millisekunden (t/100)
			$milliseconds = (int)$split[1];

			if($milliseconds <= 9) {
				$milliseconds = "0" . $milliseconds;
			} else {
				$milliseconds = $milliseconds;
			}

			// Wandle in lesbares Format um
			$converted = gmdate('H:i:s', $seconds) . "," . $milliseconds;
		} else {
			$converted = "00:00:00,00";
		}

		// Gebe Rückgabewert aus
		return $converted;
	}

	// PASS TIME AS DECIMAL (E. G. 120.03 => 00:02:00,03)
	function convertTimeRacer($decimal_seconds) {
		if($decimal_seconds != 0) {
			// Splitte Ergebnis
			$split = explode('.', $decimal_seconds);

			// Ermittle Sekunden
			$seconds = (int)$split[0];

			// Ermittle Millisekunden (t/100)
			$milliseconds = (int)$split[1];

			if($milliseconds <= 9) {
				$milliseconds = "0" . $milliseconds;
			} else {
				$milliseconds = $milliseconds;
			}

			// Wandle in lesbares Format um
			$converted = gmdate('i:s', $seconds) . "," . $milliseconds;
		} else {
			$converted = "00:00,00";
		}

		// Gebe Rückgabewert aus
		return $converted;
	}

	function resize($width, $height) {
		// GET ORIGINAL IMAGE X AND Y
		list($w, $h) = getimagesize($_FILES['logo']['tmp_name']);

		// CALCULATE NEW IMAGE SIZE WITH RATIO
		$ratio = max($width / $w, $height / $h);
		$h = ceil($height / $ratio);
		$x = ($w - $width / $ratio) / 2;
		$w = ceil($width / $ratio);

		// NEW FILE NAME
		$path = 'uploads/' . $width . 'x' . $height . '_' . $_FILES['logo']['name'];

		// READ BINARY DATA FROM IMAGE FILE
		$imgString = file_get_contents($_FILES['logo']['tmp_name']);

		// CREATE IMAGE FROM STRING
		$image = imagecreatefromstring($imgString);
		$tmp = imagecreatetruecolor($width, $height);

		imagecopyresampled($tmp, $image, 0, 0, $x, 0, $width, $height, $w, $h);

		// SAVE IMAGE
		switch ($_FILES['logo']['type']) {
			case 'image/jpeg':
				imagejpeg($tmp, $path, 100);
			break;
			case 'image/png':
				imagepng($tmp, $path, 0);
			break;
			case 'image/gif':
				imagegif($tmp, $path);
			break;
			default:
				exit;
			break;
		}

		return $path;

		// CLEAN UP MEMORY
		imagedestroy($image);
		imagedestroy($tmp);
	}

	// Funktion zum Sortieren basierend auf der Gesamtabweichung als Dezimale
	/*
	function build_sorter($key) {
		return function ($a, $b) use ($key) {
			return strnatcmp($a[$key], $b[$key]);
		};
	}
	*/

	// Funktion zum Sortieren basierend auf der Gesamtabweichung als Dezimale [v2]
	function array_orderby() {
		$args = func_get_args();
		$data = array_shift($args);
		foreach ($args as $n => $field) {
			if (is_string($field)) {
				$tmp = array();
				foreach ($data as $key => $row)
					$tmp[$key] = $row[$field];
				$args[$n] = $tmp;
				}
		}
		$args[] = &$data;
		call_user_func_array('array_multisort', $args);
		return array_pop($args);
	}

	/**
	* @return bool
	*/
	function is_session_started()
	{
		if ( php_sapi_name() !== 'cli' ) {
			if ( version_compare(phpversion(), '5.4.0', '>=') ) {
				return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
			} else {
				return session_id() === '' ? FALSE : TRUE;
			}
		}
		return FALSE;
	}

	//  Get IP Address
	function getRealIPAddress() {
	    //  Prüfe IP aus Internetzugang
        if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        //  IP aus Proxy?
        } elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    //  IP Version
    function getIPProtocol($ip) {
        return strpos($ip, ":") === false ? 4 : 6;
    }

	// SET FUNCTION TITLE CASE CORRECT: EVENT
	function titleCase($string, $delimiters = array(" ", "-", ".", "'", "O'", "Mc"), $exceptions = array("van", "de", "el", "la", "von", "vom", "der", "und", "zu", "auf", "dem", "dos", "I", "II", "III", "IV", "V", "VI")) {
		/*
		 * EXCEPTIONS IN LOWER CASE ARE WORDS YOU DONT WANT CONVERTED
		 * EXCEPTIONS ALL IN UPPER CASE ARE ANY WORDS YOU DONT WANT TO CONVERTED TO TITLE CASE
		 * BUT SHOULD BE CONVERTED TO UPPER CASE, E. G.:
		 * "king henry viii" OR "king henry Viii" SHOULD BE "King Henry VIII"
		*/
		$string = mb_convert_case($string, MB_CASE_TITLE, "UTF-8");

		foreach ($delimiters as $dlnr => $delimiter) {
			$words = explode($delimiter, $string);
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
			$string = join($delimiter, $newwords);
		} // FOREACH
		return $string;
	}
?>
