<?php
	error_reporting(E_ALL);

	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';

	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';

	//	Führe Programmcode aus, sobald Übergabeparameter gesetzt
	if(isset($_POST['eid']) AND isset($_POST['rid']) AND isset($_POST['zid']) AND isset($_POST['sid']) AND isset($_POST['pos'])) {
		//	Prüfe, ob Übergabeparameter korrekt befüllt
		if(
			($_POST['eid'] != "" OR !empty($_POST['eid'])) OR
			($_POST['rid'] != "" OR !empty($_POST['rid'])) OR
			($_POST['zid'] != "" OR !empty($_POST['zid'])) OR
			($_POST['sid'] != "" OR !empty($_POST['sid'])) OR
			($_POST['pos'] != "" OR !empty($_POST['pos']))
		) {
			//	Bereinige Übergabeparameter
			$eid	= mysqli_real_escape_string($mysqli, $_POST['eid']);
			$rid	= mysqli_real_escape_string($mysqli, $_POST['rid']);
			$zid	= mysqli_real_escape_string($mysqli, $_POST['zid']);
			$sid	= mysqli_real_escape_string($mysqli, $_POST['sid']);
			$pos	= mysqli_real_escape_string($mysqli, $_POST['pos']);

			$pos	= explode(":", $pos);

			//	Suche zuerst nach Teilnehmer
			$select_tmember = "SELECT * FROM `_optio_tmembers` WHERE `eid` = '" . $eid . "' AND `sid` = '" . $sid . "' LIMIT 1";
			$result_tmember = mysqli_query($mysqli, $select_tmember);
			$numrow_tmember = mysqli_num_rows($result_tmember);

			if($numrow_tmember == 1) {
				//	Suche nach Ergebnissen basierend auf gegebenen Zeitnehmer Positionen
				for($i = 0; $i < count($pos); $i++) {
					//	Suche je nach ältestem Ergebnis
					$select_results =	"
										SELECT
											*
										FROM
											`_main_wpresults`
										WHERE
											`eid` = '" . $eid . "'
										AND
											`zid` = '" . $zid . "'
										AND
											`rid` = '" . $rid . "'
										AND
											`sid` = '" . $sid . "'
										AND
											`position` = '" . $pos[$i] . "'
										ORDER BY
											`ergebnis_sekunden`
										DESC
										";
					$result_results = mysqli_query($mysqli, $select_results);
					$numrow_results = mysqli_num_rows($result_results);

					//	Ein Ergebnis gefunden
					if($numrow_results == 1) {
						//	Gebe derzeitige Position aus, wenn Ende der Schleife erreicht
						if($i == (count($pos) - 1)) {
							echo $pos[$i];
						}
					//	Mehrere Ergebnisse gefunden
					} elseif($numrow_results > 1) {
						//	Bereite Hilfsarray für Löschung multipler Einträge vor
						$deletion = array();

						//	Hole Ergebnisse
						while($getrow_results = mysqli_fetch_assoc($result_results)) {
							$deletion[] = $getrow_results['t_time'];
						}

						//	Entferne ersten Eintrag aus Array (ältestes Ergebnis)
						unset($deletion[0]);

						//	Sortiere Array Schlüssel neu
						$deletion = array_values($deletion);

						//	Darf nicht vorkommen! Lösche alle bis auf ältesten Eintrag
						for($j = 0; $j < count($deletion); $j++) {
							$delete = 	"
										DELETE
											*
										FROM
											`_main_wpresults`
										WHERE
											`eid` = '" . $eid . "'
										AND
											`zid` = '" . $zid . "'
										AND
											`rid` = '" . $rid . "'
										AND
											`sid` = '" . $sid . "'
										AND
											`position` = '" . $deletion[$j] . "'
										";
							$result = mysqli_query($mysqli, $delete);
						}

						//	Gebe derzeitige Position aus, wenn Ende der Schleife erreicht
						if($i == (count($pos) - 1)) {
							echo $pos[$i];
						}
					//	Keine Ergebnisse
					} elseif($numrow_results == 0) {
						//	Gebe erste Index Position aus
						echo $pos[$i];

						//	Verlasse Schleife
						exit;
					}
				}
			//	Teilnehmer nicht vorhanden
			} else {
				echo "no_result";
			}
		} else {
			echo "incomplete";
		}
	}
