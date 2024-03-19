<?php 
	error_reporting(E_ALL);
	
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
	
	// START SECURE SESSION
	session_start();
	
	//	Test
	$_SESSION['uid'] = 37;
	$_SESSION['rid_type'] = "GP";
	$_SESSION['rid'] = 1;
	$_SESSION['opt_whois'] = "";
	$_SESSION['logtype'] = "mz";
	
	// RETURN SESSION TO LOCAL VARIABLES
	$uid	= $_SESSION['uid'];
	$rid_type	= $_SESSION['rid_type'];
	$rid		= $_SESSION['rid'];
	$opt_whois	= $_SESSION['opt_whois'];
	
	// FETCH EVENT ID
	$select_event = "SELECT * FROM _optio_zmembers WHERE `id` = '" . $uid . "'";
	$result_event = mysqli_query($mysqli, $select_event);
	$getrow_event = mysqli_fetch_assoc($result_event);
	
	// DECLARE EVENT ID
	$eid = $getrow_event['eid'];
	
	// NULLING $READY
	$ready_status = "";
	
	// CREATING ARRAY FILLED WITH TMEMBERS
	$select_tmember = "SELECT * FROM _optio_tmembers WHERE `eid` = '" . $eid . "' ORDER BY `sid` ASC";
	$result_tmember = mysqli_query($mysqli, $select_tmember);
	
	$con_tmembers = array();
	
	while($getrow_tmember = mysqli_fetch_assoc($result_tmember)) {
		$con_tmembers[] = $getrow_tmember['sid'];
	}
	
	// FETCHING TOTAL AMOUNT OF POSITIONS FOR ACTIVE ROUND
	$select_wptable = "SELECT * FROM _main_wptable WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "'";
	$result_wptable = mysqli_query($mysqli, $select_wptable);
	$getrow_wptable = mysqli_fetch_assoc($result_wptable);
	$total_position = $getrow_wptable['total_pos'];
	
	// LOOP THROUGH TMEMBERS AND CHECK STATUS
	for($i = 0; $i < count($con_tmembers); $i++) {
		// OLD VERSION (VIA READY COLUMN IN _OPTIO_TMEMBERS)
		// CHECK FOR SPECIAL STATUS (E. G. FROM EVENT HANDLER --> DISABLED)
		/*
		$select_special = "SELECT * FROM _optio_tmembers WHERE `eid` = '" . $eid . "' AND `sid` = '" . $con_tmembers[$i] . "'";
		$result_special = mysqli_query($mysqli, $select_special);
		$getrow_special = mysqli_fetch_assoc($result_special);
		$special_status = $getrow_special['ready'];
		*/
		
		// NEW VERSION (VIA NUM ROWS IN _OPTIO_TMEMBERS_LOCK)
		// CHECK FOR SPECIAL STATUS (E. G. FROM EVENT HANDLER --> DISABLED)
		$select_special = "SELECT `eid`, `rid`, `sid` FROM `_optio_tmembers_lock` WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $con_tmembers[$i] . "'";
		$result_special = mysqli_query($mysqli, $select_special);
		$numrow_special = mysqli_num_rows($result_special);
		
		if($numrow_special == 0) {
			$special_status = "";
		} elseif($numrow_special == 1) {
			$special_status = "no";
		}
		
		
		// NO SPECIAL STATUS, PROCEED WITH RESULT AMOUNT BASED STATUS CHECK
		if($special_status == "" OR empty($special_status)) {
			// CHECK FOR RESULT AMOUNT AND COMPARE WITH TOTAL POSITIONS
			$select_result = "SELECT * FROM _main_wpresults WHERE `eid` = '" . $eid . "' AND `rid` = '" . $rid . "' AND `sid` = '" . $con_tmembers[$i] . "'";
			$result_result = mysqli_query($mysqli, $select_result);
			$numrow_result = mysqli_num_rows($result_result);
			
			// NO START
			if($numrow_result == 0) {
				$ready_status = "yes";
			// PENDING
			} elseif($numrow_result > 0 AND $numrow_result < $total_position) {
				$ready_status = "pen";
			// FINISHED
			} elseif($numrow_result == $total_position) {
				$ready_status = "fin";
			}
		// SPECIAL STATUS FOUND
		} elseif($special_status != "" OR !empty($special_status)) {
			switch($special_status) {
				case "no":
					$ready_status = "out";
				break;
				default:
					$ready_status = "out";
				break;
			}
		}
		
		// DECLARE STATUS COLORS
		// STATUS OKAY
		if($ready_status == "yes") {
			$status = "#A09A8E";
		}
		// STATUS PENDING
		if($ready_status == "pen") {
			$status = "#FFFF00";
		}
		// STATUS FINISHED
		if($ready_status == "fin") {
			$status = "#00FF00";
		}		
		// STATUS ERROR
		if($ready_status == "out") {
			$status = "#FF0000";
		}
		
		// INITIAL LOOP
		if($i == 0) {
			echo "<tr>";
		}
		
		// MIDDLE LOOP 		
		if(	$i != 0 	|| $i != 10		|| $i != 20		|| $i != 30		|| $i != 40		|| $i != 50		|| $i != 60		|| $i != 70		|| $i != 80		|| $i != 90		|| 	$i != 100	|| 
			$i != 110	|| $i != 120	|| $i != 120	|| $i != 130	|| $i != 140	|| $i != 150	|| $i != 160	|| $i != 170	|| $i != 180	|| $i != 190	||  $i != 200	|| 
			$i != 210	|| $i != 220	|| $i != 220	|| $i != 230	|| $i != 240	|| $i != 250	|| $i != 260	|| $i != 270	|| $i != 280	|| $i != 290	||  $i != 300	|| 
			$i != 310	|| $i != 320	|| $i != 320	|| $i != 330	|| $i != 340	|| $i != 350	|| $i != 360	|| $i != 370	|| $i != 380	|| $i != 390	||  $i != 400	|| 
			$i != 410	|| $i != 420	|| $i != 420	|| $i != 430	|| $i != 440	|| $i != 450	|| $i != 460	|| $i != 470	|| $i != 480	|| $i != 490	||  $i != 500	|| 
			$i != 510	|| $i != 520	|| $i != 520	|| $i != 530	|| $i != 540	|| $i != 550	|| $i != 560	|| $i != 570	|| $i != 580	|| $i != 590	||  $i != 600	||  
			$i != 610	|| $i != 620	|| $i != 620	|| $i != 630	|| $i != 640	|| $i != 650	|| $i != 660	|| $i != 670	|| $i != 680	|| $i != 690	||  $i != 700	||  
			$i != 710	|| $i != 720	|| $i != 720	|| $i != 730	|| $i != 740	|| $i != 750	|| $i != 760	|| $i != 770	|| $i != 780	|| $i != 790	||  $i != 800	||  
			$i != 810	|| $i != 820	|| $i != 820	|| $i != 830	|| $i != 840	|| $i != 850	|| $i != 860	|| $i != 870	|| $i != 880	|| $i != 890	||  $i != 900	||  
			$i != 910	|| $i != 920	|| $i != 920	|| $i != 930	|| $i != 940	|| $i != 950	|| $i != 960	|| $i != 970	|| $i != 980	|| $i != 990	||  $i != 1000	) {
			echo	"
					<td align='center' style='font-size: small;'>
						<button style='height: 24px; border: 1px solid #FFFFFF; background: transparent; background-color: " . $status . "; color: #000000; width: 25px;' name='" . $con_tmembers[$i] . "'>
							" . $con_tmembers[$i] . "
						</button>
					</td>
					";
		}

		// MIDDLE LOOP
		if(	$i == 9		|| $i == 19		|| $i == 29		|| $i == 39		|| $i == 49		|| $i == 59		|| $i == 69		|| $i == 79		|| $i == 89		|| $i == 99		|| 
			$i == 109	|| $i == 119	|| $i == 129	|| $i == 139	|| $i == 149	|| $i == 159	|| $i == 169	|| $i == 179	|| $i == 189	|| $i == 199	||
			$i == 209	|| $i == 219	|| $i == 229	|| $i == 239	|| $i == 249	|| $i == 259	|| $i == 269	|| $i == 279	|| $i == 289	|| $i == 299	||
			$i == 309	|| $i == 319	|| $i == 329	|| $i == 339	|| $i == 349	|| $i == 359	|| $i == 369	|| $i == 379	|| $i == 389	|| $i == 399	||
			$i == 409	|| $i == 419	|| $i == 429	|| $i == 439	|| $i == 449	|| $i == 459	|| $i == 469	|| $i == 479	|| $i == 489	|| $i == 499	||
			$i == 509	|| $i == 519	|| $i == 529	|| $i == 539	|| $i == 549	|| $i == 559	|| $i == 569	|| $i == 579	|| $i == 589	|| $i == 599	||
			$i == 609	|| $i == 619	|| $i == 629	|| $i == 639	|| $i == 649	|| $i == 659	|| $i == 669	|| $i == 679	|| $i == 689	|| $i == 699	|| 
			$i == 709	|| $i == 719	|| $i == 729	|| $i == 739	|| $i == 749	|| $i == 759	|| $i == 769	|| $i == 779	|| $i == 789	|| $i == 799	|| 
			$i == 809	|| $i == 819	|| $i == 829	|| $i == 839	|| $i == 849	|| $i == 859	|| $i == 869	|| $i == 879	|| $i == 889	|| $i == 899	|| 
			$i == 909	|| $i == 919	|| $i == 929	|| $i == 939	|| $i == 949	|| $i == 959	|| $i == 969	|| $i == 979	|| $i == 989	|| $i == 999	) {
			echo "</tr>";
			echo "<tr>";
		}
		
		// END LOOP
		if($i == 1000) {
			echo "</tr>";
		}
	}
	
	// SHOW HINT TABLE
	echo	"
				<tr>
					<td colspan='10' align='center'><hr class='white-hr'></td>
				</tr>
				<tr>
					<td align='center'><font size='2' color='#9E9482'>&#9609;</font></td>
					<td colspan='9'><font size='2'>Nicht gestartet</font></td>
				</tr>
				<tr>
					<td align='center'><font size='2' color='#FFFF00'>&#9609;</font></td>
					<td colspan='9'><font size='2'>Gestartet aber nicht im Ziel</font></td>
				</tr>
			";
			
			/*
				<tr>
					<td align='center'><font size='2' color='#0000FF'>&#9609;</font></td>
					<td colspan='9'><font size='2'>Keine Start- aber Zielzeit</font></td>
				</tr>
			*/	
				
	echo	"
				<tr>
					<td align='center'><font size='2' color='#00FF00'>&#9609;</font></td>
					<td colspan='9'><font size='2'>Start- und Zielzeit vorhanden</font></td>
				</tr>
				<tr>
					<td align='center'><font size='2' color='#FF0000'>&#9609;</font></td>
					<td colspan='9'><font size='2'>Ausfall</font></td>
				</tr>
			";
?>