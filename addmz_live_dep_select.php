<? error_reporting(E_ALL);
    // INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
	
	// START SECURE SESSION
	sec_session_start();
	
	// CREATE EVENT ID FROM ACTIVE SESSION
	$eid	= $_SESSION['user_id'];
	
	// GET SELECT OPTIONS FOR rid
	if(isset($_POST["rid"]) && !empty($_POST["rid"])) {  
	    // GET ROUND DESIGNATION
		$rd_designation = mysqli_real_escape_string($mysqli, utf8_encode($_POST['rid']));
									
		// SANITIZE AND EXPLODE TO rid_type (GP, SP, WP) AND rid (1, 2, n-1)
		if(strpos($rd_designation, "WP") !== false) {
			$split = explode("WP", $rd_designation);
			$mz_id_type = $split[0];
			$mz_id_round = $split[1];
		} elseif(strpos($rd_designation, "SP") !== false) {
			$split = explode("SP", $rd_designation);
			$mz_id_type = $split[0];
			$mz_id_round = $split[1];
		} elseif(strpos($rd_designation, "GP") !== false) {
			$split = explode("GP", $rd_designation);
			$mz_id_type = $split[0];
			$mz_id_round = $split[1];
	    }
		
		// CHECK WHETHER HAS REGULAR TIME INPUT OR ONLY DRIVING TIME (SEE ADDRD FOR INFO)
		$select_main_wptable = "SELECT eid, rid, z_entry FROM _main_wptable WHERE `eid` = '" . $eid . "' AND `rid` = '" . $mz_id_round . "'";
		$result_main_wptable = mysqli_query($mysqli, $select_main_wptable);
		$getrow_main_wptable = mysqli_fetch_assoc($result_main_wptable);
		
		// IF REGULAR TIME INPUT (E. G. START AND GOAL)
		if($getrow_main_wptable['z_entry'] == 0) {
			// GRAB ALL POST AND BUILD QUERY
			$select_query = "SELECT * FROM _main_wptable WHERE `rid` = " . $mz_id_round . " AND `eid` = " . $eid . " ORDER BY rid DESC";
			$result_query = mysqli_query($mysqli, $select_query);
				
			// COUNT TOTAL NUMBER OF ROWS
			$anzahl = mysqli_num_rows($result_query);
			
			// DISPLAY POSITIONS LIST
			if($anzahl > 0){
				echo '<option value="">Position zuweisen</option>';
				while($spalte = mysqli_fetch_assoc($result_query)) {
					if($spalte['rid_attr'] > 0) {
						echo '<option value="Start">Start</option>';
						for($i = 0; $i < $spalte['rid_attr']; $i++) {
							$j = $i + 1;
							echo '<option value="ZZ' . $j . '">Zwischenzeit ' . $j . '</option>';
						}
						echo '<option value="Ziel">Ziel</option>';
					} elseif($spalte['rid_attr'] == 0) {
						echo '<option value="Start">Start</option>';
						echo '<option value="Ziel">Ziel</option>';
					}
				}
			} else {
				echo '<option>Nicht verfügbar</option>';
			}
		// ELSE IRREGULAR TIME INPUT (E. G. DRIVING TIME ONLY)
		} elseif($getrow_main_wptable['z_entry'] == 1) {
			// GRAB ALL POST AND BUILD QUERY
			$select_query = "SELECT * FROM _main_wptable WHERE `rid` = " . $mz_id_round . " AND `eid` = " . $eid . " ORDER BY rid DESC";
			$result_query = mysqli_query($mysqli, $select_query);
				
			// COUNT TOTAL NUMBER OF ROWS
			$anzahl = mysqli_num_rows($result_query);
			
			// DISPLAY POSITIONS LIST
			if($anzahl > 0){
				echo '<option value="">Position zuweisen</option>';
				echo '<option value="Sprint">Sprint</option>';
			} else {
				echo '<option>Nicht verfügbar</option>';
			}
			
			echo	'
						<script>
							$(".rid_pos_1_2").attr("disabled", "disabled");
							$(".rid_pos_1_3").attr("disabled", "disabled");
							$(".rid_pos_1_4").attr("disabled", "disabled");
							$(".rid_pos_1_5").attr("disabled", "disabled");
						</script>
					';
		}
    }
?>