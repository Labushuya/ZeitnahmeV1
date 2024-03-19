<? error_reporting(E_ALL);
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';

	// START SESSION
	session_start();

	// CHECK FOR PENDING UPLOADS
	if(!empty($_SESSION['holder'])) {
		// CALCULATE COUNT OF TOTAL ROWS
		$count = count($_SESSION['holder']) / 9;
				
		// PREPARE UPLOAD
		for($i = 0; $i < count($_SESSION['holder']); $i++) {
			// GET EVENT FROM SESSION STACK
			$eid		= $_SESSION['holder'][$i];
			// INCREMENT BY ONE
			$i++;
			// GET rid FROM SESSION STACK
			$rid			= $_SESSION['holder'][$i];
			// INCREMENT BY ONE
			$i++;
			// GET USER_ID FROM SESSION STACK
			$uid		= $_SESSION['holder'][$i];
			// INCREMENT BY ONE
			$i++;
			// GET sid FROM SESSION STACK
			$sid		= $_SESSION['holder'][$i];
			// INCREMENT BY ONE
			$i++;
			// GET CUR_POS FROM SESSION STACK
			$cur_pos		= $_SESSION['holder'][$i];
			// INCREMENT BY ONE
			$i++;
			// GET T_TIME FROM SESSION STACK
			$ergSekunden			= $_SESSION['holder'][$i];
			// INCREMENT BY ONE
			$i++;
			// GET CENTISECONDS FROM SESSION STACK
			$ergHundertstel	= $_SESSION['holder'][$i];
			// INCREMENT BY ONE
			$i++;			
			// GET TIME_MERGED FROM SESSION STACK
			$time_merged	= $_SESSION['holder'][$i];
			// INCREMENT BY ONE
			$i++;
			// GET T_VALIDATION FROM SESSION STACK
			$t_validation	= $_SESSION['holder'][$i];
			
			// UNSET INDIZES STARTING FROM ZERO TO EIGHT [ 9 BLOCKS ]
			unset($_SESSION['holder'][0]);
			unset($_SESSION['holder'][1]);
			unset($_SESSION['holder'][2]);
			unset($_SESSION['holder'][3]);
			unset($_SESSION['holder'][4]);
			unset($_SESSION['holder'][5]);
			unset($_SESSION['holder'][6]);
			unset($_SESSION['holder'][7]);
			unset($_SESSION['holder'][8]);			
			
			// RE-ARRANGE INDIZES
			array_values($_SESSION['holder']);
			
			// UPLOAD PENDING DATA
			$insert_stack = "INSERT INTO 
									_main_wpresults(
										id,
										eid,
										rid,
										zid,
										sid,
										t_pos,
										t_time,
										t_centi,
										t_realtime,
										t_validation
									)
								VALUES(
									NULL,
									'" . $eid . "',
									'" . $rid . "',
									'" . $uid . "',
									'" . $sid . "',
									'" . $cur_pos . "',
									'" . $ergSekunden . "',
									'" . $ergHundertstel . "',
									'" . $time_merged . "',
									'" . $t_validation . "'
								)";
			mysqli_query($mysqli, $insert_stack);
		}
		
		// CHECK IF SUCCESSFUL
		if(mysqli_affected_rows($mysqli) > 0) {
			// GET CORRECT GRAMMAR
			if($count == 1) {
				$grammar = "Ergebnisse";
			} elseif($count > 1) {
				$grammar = "Ergebnis";
			}
			
			echo	'
					<table width="385px" cellspacing="5px" style="border: 0;">
						<tr>
							<th align="center">
								' . mysqli_affected_rows($mysqli) . ' / ' . $count . ' ' . $grammar . ' erfolgreich hochgeladen! Browser kann geschlossen werden, sofern keine weitere Eingabe erfolgt
							</th>
						</tr>
					</table>
					
					<table width="385px" cellspacing="5px" style="border: 0;">
						<tr>
							<th align="center">&nbsp;</th>
						</tr>
					</table>
				';
		} elseif(mysqli_affected_rows($mysqli) == 0) {
			// GET CORRECT GRAMMAR
			if($count == 1) {
				$grammar = "Ergebnisse";
			} elseif($count > 1) {
				$grammar = "Ergebnis";
			}
			
			echo	'
					<table width="385px" cellspacing="5px" style="border: 0;">
						<tr>
							<th align="center">
								Derzeit kein Upload möglich. Erneuter Versuch in 30 Sekunden! Browser nicht schließen. ' . $count - mysqli_affected_rows($mysqli) . ' / ' . $count . ' ' . $grammar . ' ausstehend
							</th>
						</tr>
					</table>
					
					<table width="385px" cellspacing="5px" style="border: 0;">
						<tr>
							<th align="center">&nbsp;</th>
						</tr>
					</table>
				';
		} /*else {		
			echo "<pre>";
			print_r($_SESSION['holder']);
			echo "</pre>";
		}*/
	} else {
		echo	'
					<table width="385px" cellspacing="5px" style="border: 0;">
						<tr>
							<th align="center">
								Keine ausstehenden Zeiten für Upload vorhanden
							</th>
						</tr>
					</table>
					
					<table width="385px" cellspacing="5px" style="border: 1;">
						<tr>
							<th align="center">&nbsp;</th>
						</tr>
					</table>
				';
	}
?>