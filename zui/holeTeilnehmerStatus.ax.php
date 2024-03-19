<?php
  // Debugging
  error_reporting(E_ALL);

  // Lege Zeitzone fest
  date_default_timezone_set("Europe/Berlin");

  // Prüfe, ob Session bereits gestartet wurde
  // PHP Version < 5.4.0
  if (session_id() == '') {
    session_start();
  }
  // PHP Version > 5.4.0, 7
  /*
  if (session_status() == PHP_SESSION_NONE) {
    session_start();
  }
  */

  // Prüfe auf aktive Session-Parameter
  if(
    isset($_SESSION['eid']) && is_numeric($_SESSION['eid']) && $_SESSION['eid'] > 0 &&
    isset($_SESSION['rid']) && is_numeric($_SESSION['rid']) && $_SESSION['rid'] > 0
  ) {
    // Binde Funktionsdateien ein
    include_once '../includes/functions.php';

    // Binde Konfigurationsdatei ein
    include_once '../includes/db_connect.php';

    // Bereinige Übergabeparameter
    $eid = $_SESSION['eid'];
    $rid = $_SESSION['rid'];

    // Lege Status-Variable an
  	$readyStatus = "";

  	// Hole Teilnehmer-Liste
  	$selectTeilnehmer = "SELECT * FROM `_optio_tmembers` WHERE `eid` = " . $eid . " ORDER BY `sid` ASC";
  	$resultTeilnehmer = mysqli_query($mysqli, $selectTeilnehmer);
  	$numrowTeilnehmer = mysqli_num_rows($resultTeilnehmer);

    if($numrowTeilnehmer > 0) {
      $teilnehmerListe = array();

    	while($getrowTeilnehmer = mysqli_fetch_assoc($resultTeilnehmer)) {
    		$teilnehmerListe[] = $getrowTeilnehmer['sid'];
    	}

      // Hole Anzahl Positionen der aktuellen Prüfung
    	$selectPruefung = "SELECT `total_pos` FROM `_main_wptable` WHERE `eid` = " . $eid . " AND `rid` = " . $rid;
    	$resultPruefung = mysqli_query($mysqli, $selectPruefung);
    	$getrowPruefung = mysqli_fetch_assoc($resultPruefung);
    	$positionen = $getrowPruefung['total_pos'];

      echo  '
        <div class="table-wrapper" style="margin-bottom:2em!important;">
          <table class="alt">
            <tbody>
            ';

      // Durchlaufe Schleife, um Teilnehmer-Status zu prüfen
      for($i = 0; $i < count($teilnehmerListe); $i++) {
        // Prüfe Sonder-Status (vgl. TD, Unfall, etc.)
        $selectSonderstatus = "SELECT `eid`, `rid`, `sid` FROM `_optio_tmembers_lock` WHERE `eid` = " . $eid . " AND `rid` = " . $rid . " AND `sid` = " . $teilnehmerListe[$i];
    		$resultSonderstatus = mysqli_query($mysqli, $selectSonderstatus);
    		$numrowSonderstatus = mysqli_num_rows($resultSonderstatus);

        if($numrowSonderstatus == 0) {
    			$sonderStatus = "";
    		} elseif($numrowSonderstatus == 1) {
    			$sonderStatus = "no";
    		}

        // Kein Sonder-Status, fahre mit regulärem Status fort
    		if($sonderStatus == "" OR empty($sonderStatus)) {
    			// Prüfe Status basierend auf bereits vorhandenen Ergebnisdaten
    			$selectErgebnis = "SELECT DISTINCT(`position`) FROM `_main_wpresults` WHERE `eid` = " . $eid . " AND `rid` = " . $rid . " AND `sid` = " . $teilnehmerListe[$i];
    			$resultErgebnis = mysqli_query($mysqli, $selectErgebnis);
    			$numrowErgebnis = mysqli_num_rows($resultErgebnis);

    			// Nicht gestartet
    			if($numrowErgebnis == 0) {
    				$teilnehmerStatus = "yes";
    			// Ausstehend (noch nicht im Ziel)
          } elseif($numrowErgebnis > 0 AND $numrowErgebnis < $positionen) {
            $teilnehmerStatus = "pen";
          // Fertig (im Ziel angekommen)
          } elseif($numrowErgebnis == $positionen) {
            $teilnehmerStatus = "fin";
    			}
    		// Sonder-Status gefunden
    		} elseif($sonderStatus != "" OR !empty($sonderStatus)) {
    			switch($sonderStatus) {
    				case "no":
    					$teilnehmerStatus = "out";
    				break;
    				default:
    					$teilnehmerStatus = "out";
    				break;
    			}
    		}

        // Statusfarben; Status: Okay
    		if($teilnehmerStatus == "yes") {
    			$status = "border:0!important;color:#c0c0c0!important;background:#9e9482!important;";
    		}
    		// Status: Ausstehend
    		if($teilnehmerStatus == "pen") {
    			$status = "border:0!important;color:#333!important;background:#f3f10e!important;";
    		}
    		// Status: Fertig
    		if($teilnehmerStatus == "fin") {
    			$status = "border:0!important;color:#c0c0c0!important;background:#468847!important;";
    		}
    		// Status: Fehler
    		if($teilnehmerStatus == "out") {
    			$status = "border:0!important;color:#c0c0c0!important;background:#b94a48!important;";
    		}

        // Beginne mit Auflistung aller Teilnehmer
    		if($i == 0) {
    			echo  '
              <tr>
                ';
    		}

        // Mittlere Schleife (gerade Zahlen)
    		if(
          $i != 0 	|| $i != 10		|| $i != 20		|| $i != 30		|| $i != 40		|| $i != 50		|| $i != 60		|| $i != 70		|| $i != 80		|| $i != 90		|| 	$i != 100	||
    			$i != 110	|| $i != 120	|| $i != 120	|| $i != 130	|| $i != 140	|| $i != 150	|| $i != 160	|| $i != 170	|| $i != 180	|| $i != 190	||  $i != 200	||
    			$i != 210	|| $i != 220	|| $i != 220	|| $i != 230	|| $i != 240	|| $i != 250	|| $i != 260	|| $i != 270	|| $i != 280	|| $i != 290	||  $i != 300	||
    			$i != 310	|| $i != 320	|| $i != 320	|| $i != 330	|| $i != 340	|| $i != 350	|| $i != 360	|| $i != 370	|| $i != 380	|| $i != 390	||  $i != 400	||
    			$i != 410	|| $i != 420	|| $i != 420	|| $i != 430	|| $i != 440	|| $i != 450	|| $i != 460	|| $i != 470	|| $i != 480	|| $i != 490	||  $i != 500	||
    			$i != 510	|| $i != 520	|| $i != 520	|| $i != 530	|| $i != 540	|| $i != 550	|| $i != 560	|| $i != 570	|| $i != 580	|| $i != 590	||  $i != 600	||
    			$i != 610	|| $i != 620	|| $i != 620	|| $i != 630	|| $i != 640	|| $i != 650	|| $i != 660	|| $i != 670	|| $i != 680	|| $i != 690	||  $i != 700	||
    			$i != 710	|| $i != 720	|| $i != 720	|| $i != 730	|| $i != 740	|| $i != 750	|| $i != 760	|| $i != 770	|| $i != 780	|| $i != 790	||  $i != 800	||
    			$i != 810	|| $i != 820	|| $i != 820	|| $i != 830	|| $i != 840	|| $i != 850	|| $i != 860	|| $i != 870	|| $i != 880	|| $i != 890	||  $i != 900	||
    			$i != 910	|| $i != 920	|| $i != 920	|| $i != 930	|| $i != 940	|| $i != 950	|| $i != 960	|| $i != 970	|| $i != 980	|| $i != 990	||  $i != 1000
        ) {
    			echo	'
  					   <td align="center">
  				       <button class="small teilnehmerInfo" id="' . $teilnehmerListe[$i] . '" style="' . $status . 'box-shadow:none!important;" name="' . $teilnehmerListe[$i] . '">
  	               ' . $teilnehmerListe[$i] . '
  		           </button>
               </td>
  			        ';
    		}

        // Mittlere Schleife (ungerade Zahlen)
    		if(
          $i == 9		|| $i == 19		|| $i == 29		|| $i == 39		|| $i == 49		|| $i == 59		|| $i == 69		|| $i == 79		|| $i == 89		|| $i == 99		||
    			$i == 109	|| $i == 119	|| $i == 129	|| $i == 139	|| $i == 149	|| $i == 159	|| $i == 169	|| $i == 179	|| $i == 189	|| $i == 199	||
    			$i == 209	|| $i == 219	|| $i == 229	|| $i == 239	|| $i == 249	|| $i == 259	|| $i == 269	|| $i == 279	|| $i == 289	|| $i == 299	||
    			$i == 309	|| $i == 319	|| $i == 329	|| $i == 339	|| $i == 349	|| $i == 359	|| $i == 369	|| $i == 379	|| $i == 389	|| $i == 399	||
    			$i == 409	|| $i == 419	|| $i == 429	|| $i == 439	|| $i == 449	|| $i == 459	|| $i == 469	|| $i == 479	|| $i == 489	|| $i == 499	||
    			$i == 509	|| $i == 519	|| $i == 529	|| $i == 539	|| $i == 549	|| $i == 559	|| $i == 569	|| $i == 579	|| $i == 589	|| $i == 599	||
    			$i == 609	|| $i == 619	|| $i == 629	|| $i == 639	|| $i == 649	|| $i == 659	|| $i == 669	|| $i == 679	|| $i == 689	|| $i == 699	||
    			$i == 709	|| $i == 719	|| $i == 729	|| $i == 739	|| $i == 749	|| $i == 759	|| $i == 769	|| $i == 779	|| $i == 789	|| $i == 799	||
    			$i == 809	|| $i == 819	|| $i == 829	|| $i == 839	|| $i == 849	|| $i == 859	|| $i == 869	|| $i == 879	|| $i == 889	|| $i == 899	||
    			$i == 909	|| $i == 919	|| $i == 929	|| $i == 939	|| $i == 949	|| $i == 959	|| $i == 969	|| $i == 979	|| $i == 989	|| $i == 999
        ) {
    			echo  '
              </tr>
              <tr>
                ';
    		}

        // Ende Schleife (Tabelle-Reihe schließen)
    		if($i == 1000) {
    			echo  '
              </tr>
                ';
    		}
      }

      // Zeige Hinweis Tabelle
      echo	  '

            </tbody>
          </table>
        </div>
        <div class="box" style="margin-bottom:0.25em!important;">
          <strong>Legende</strong><br />
          <p>
            <button id="legendeTransparent" class="small" style="box-shadow:none!important;border:1px solid #8e6516!important;background:transparent;">bereit</button>
            <button class="small" style="box-shadow:none!important;border:1px solid #f3f10e!important;background:#f3f10e;color:#333!important;">gestartet</button>
            <button class="small" style="box-shadow:none!important;border:1px solid #468847!important;background:#468847;color:#fff!important;">fertig</button>
            <button class="small" style="box-shadow:none!important;border:1px solid #b94a48!important;background:#b94a48;color:#fff!important;">Ausfall</button>
          </p>
        </div>
              ';
    } else {
      echo  '
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Keine Teilnehmer vorhanden</th>
              </tr>
            </thead>
          </table>
        </div>
            ';
    }
  // Keine aktive Session
  } else {
    echo "keineSession";
  }
?>
