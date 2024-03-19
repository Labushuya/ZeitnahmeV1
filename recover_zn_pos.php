<? error_reporting(E_ALL);
	date_default_timezone_set("Europe/Berlin");

    // Binde Funktionen ein
	include_once 'includes/functions.php';
	
	// Binde die DB-Connect ein
	include_once 'includes/db_connect.php';
	
	// Prüfe, auf Übergabeparameter
	if(isset($_POST['eid']) && !empty($_POST['eid']) AND isset($_POST['zid']) && !empty($_POST['zid'])) {
		// Bereinige Übergabeparameter
		$eid = mysqli_real_escape_string($mysqli, utf8_encode($_POST['eid']));
		$zid = mysqli_real_escape_string($mysqli, utf8_encode($_POST['zid']));
		
		//  Suche in Ergebnistabelle nach bereits vorhandenen Ergebnissen dieses Zeitnehmers
		$select = "SELECT DISTINCT(`position`), `rid`, `zid` FROM `_main_wpresults` WHERE `eid` = '" . $eid . "' AND `zid` = '" . $zid . "'";
		$result = mysqli_query($mysqli, $select);
		$numrow = mysqli_num_rows($result);
		
		$count = 0;
		
		while(@$getrow = mysqli_fetch_assoc($result)) {
		    /*
		        Suche in Zeitnehmer Positionen Tabelle nach bereits vorhandenen
		        Positonen, um doppelte Einträge zu vermeiden
		    */
		    $select_zpos = "SELECT * FROM `_optio_zpositions` WHERE `pos` = '" . $getrow['t_pos'] . "' AND `zid` = '" . $zid . "'";
		    $result_zpos = mysqli_query($mysqli, $select_zpos);
		    $numrow_zpos = mysqli_num_rows($result_zpos);
		    
		    //  Zeitnehmer Position nicht gefunden
		    if($numrow_zpos == 0) {
		        //  Stelle diese wieder her
		        $insert =   "
    		                INSERT INTO
    		                    `_optio_zpositions`(
    		                        `id`,
    		                        `zid`,
    		                        `rid`,
    		                        `pos`
    		                    )
    		                VALUES(
    		                    NULL,
    		                    '" . $zid . "',
    		                    '" . $getrow['rid'] . "',
    		                    '" . $getrow['t_pos'] . "'
    		                )
    		                ";
		        $result = mysqli_query($mysqli, $insert);
		    
    		    if(mysqli_affected_rows($mysqli) == 1) {
    		        $count++;
    		    }
		    }
		}
		
		if($numrow > 0) {
    		if($count == $numrow AND $count > 0) {
    		    echo "success";
    		} elseif($count != $numrow AND $count > 0) {
    		    echo "partial";
    		}
		} elseif($numrow == 0) {
		    echo "nothing";
		}
	}
?>