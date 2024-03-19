<?php error_reporting(E_ALL);
	//	Setze Zeitzone
	setlocale(LC_TIME, "de_DE");
	date_default_timezone_set("Europe/Berlin");

	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
	
	// START SESSION
    session_start();
	
	if(isset($_SESSION['user_id']) AND ($_SESSION['user_id'] != "" OR !empty($_SESSION['user_id']))) {
		// RETURN SESSION TO LOCAL VARIABLES
		//	Speichere Session in lokale Variablen
		$zid		= $_SESSION['user_id'];
		$uname		= $_SESSION['username'];
		$whois		= $_SESSION['opt_whois'];
		$logtype	= $_SESSION['logtype'];
		
		//  Prüfe Login-Typ
		switch($logtype) {
	        //  Zeitnehmer
	        case "mz":
                header("Location: timebuddy.php");
            break;
            //  Zeitkontrolle
            case "zk":
                header("Location: zcontrol.php");
            break;
            case "bc":
                header("Location: boarding.php");
            break;
		}
		
		//	Lege Event ID fest
		$eid = $_SESSION['eid'];
		
		//  Hole alle Informationen über diesen Bordkarten Account
	    $select_zstamp = "SELECT * FROM `_optio_zstamp` WHERE `eid` = '" . $eid . "' AND `id` = '" . $zid . "'";
	    $result_zstamp = mysqli_query($mysqli, $select_zstamp);
	    $numrow_zstamp = mysqli_num_rows($result_zstamp);
	    
	    if($numrow_zstamp == 1) {
	        $getrow_zstamp = mysqli_fetch_assoc($result_zstamp);
	        
            //  Hole Titel und Bezeichnung
            $title = $getrow_zstamp['title'];
            $whois = $getrow_zstamp['opt_whois'];
            
            $identification = "Stempelkontrolle";
            
            if($whois != "" OR !empty($whois)) {
                $identification .= " <strong>" . $whois . "</strong>";
            }
            
            if($title != "" OR !empty($title)) {
	            $identification .= " - <span style=\"color: #8e6516;\">" . $title . "</span>";
            }
	        
	        $dte = $getrow_zstamp['eventdate'];
	        
	        //  Zugehöriges Datum konnte nicht ermittelt werden
	        if($dte == "" OR empty($dte)) {
	            $redir      = '<meta http-equiv="refresh" content="10; url=/msdn/error.php?code=interface&add=nodte&type=zs">';
		    	$state		= 'Fehler!';
		    	$error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Zugehöriges Veranstaltungsdatum konnte nicht ermittelt werden!</span><br />';
	        }
	    } else {
	        $identification = "Stempelkontrolle";
	    }
		
		//  Ausführung erst möglich, wenn Veranstaltungsdatum gesetzt wurde
		if(isset($dte) AND ($dte != "" OR !empty($dte))) {
    		if(isset($_POST['stamped_submit'])) {
    			//  Debugging
    			/*
        			echo "<pre>";
        			print_r($_POST);
        			echo "</pre>";
        			//  exit;
    			*/
    			
    			/*
    				Array
    				(
    					[fahrer] => Christopher Bott
    					[startnummer] => 66
    					[zs] => on
    					[stamped_submit] => Eintrag vornehmen
    				)
    			*/
    		
    			//	Entferne nicht benötigte Übergabe Parameter
    			unset($_POST['stamped_submit'], $_POST['fahrer']);
    			
    			//	Bereinige Übergabe Parameter
    			$sid = mysqli_real_escape_string($mysqli, $_POST['startnummer']);
    			
    			//	Prüfe, ob Stempel gesetzt
    			if(isset($_POST['zs']) AND ($_POST['zs'] != "" OR !empty($_POST['zs']))) {
    				$query = "insert";
    			} else {
    				$query = "delete";
    			}
    			
    			//	Suche bereits bestehenden Datensatz
    			$select = "SELECT * FROM `_optio_zstamp_results` WHERE `eid` = '" . $eid . "' AND `zid` = '" . $zid . "' AND `sid` = '" . $sid . "' AND `eventdate` = '" . $dte . "'";
    			$result = mysqli_query($mysqli, $select);
    			$numrow = mysqli_num_rows($result);
    			
    			//  Keine Stempel für diesen Teilnehmer für den aktuellen Veranstaltungstag gefunden
    			if($numrow == 0 AND $query == "insert") {
    			    $insert =	"
    							INSERT INTO
    							    `_optio_zstamp_results`(
    							        `id`,
    							        `eid`,
    							        `zid`,
    							        `sid`,
    							        `eventdate`,
    							        `time`
    							    )
    						    VALUES(
    						        NULL,
    						        '" . $eid . "',
    						        '" . $zid . "',
    						        '" . $sid . "',
    						        '" . $dte . "',
    						        '" . time() . "'
    						    )
    							";
    			    $result = mysqli_query($mysqli, $insert);
    			    
    			    //  Prüfe, ob Datensatz angelegt wurde
    			    if(mysqli_affected_rows($mysqli) == 1) {
    			        $state		= 'Erfolgreich:';
    			        $error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Stempelnachweis für Teilnehmer #' . $sid . ' gespeichert!</span><br />';
    			    } elseif(mysqli_affected_rows($mysqli) == 0) {
    			        $state		= 'Fehler!';
    			        $error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Stempelnachweis für Teilnehmer #' . $sid . ' konnte nicht gespeichert werden!</span><br />';   
    			    }
    			} elseif($numrow == 1 AND $query == "delete") {
    			    $delete =   "
    			                DELETE FROM
    			                    `_optio_zstamp_results`
			                    WHERE
			                        `eid` = '" . $eid . "'
		                        AND
		                            `zid` = '" . $zid . "'
		                        AND
		                            `sid` = '" . $sid . "'
		                        AND
		                            `eventdate` = '" . $dte . "'
    			                ";
	                $result = mysqli_query($mysqli, $delete);
	                
	                //  Prüfe, ob Datensatz gelöscht wurde
    			    if(mysqli_affected_rows($mysqli) == 1) {
    			        $state		= 'Erfolgreich:';
    			        $error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Stempelnachweis für Teilnehmer #' . $sid . ' entfernt!</span><br />';
    			    } elseif(mysqli_affected_rows($mysqli) == 0) {
    			        $state		= 'Fehler!';
    			        $error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Stempelnachweis für Teilnehmer #' . $sid . ' konnte nicht entfernt werden!</span><br />';   
    			    }
    			} else {
    			    $state		= 'Hinweis:';
    			    $error_msg	= '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Keine Änderung erfolgt!</span><br />';
    			}
    		}
	    }
	} else {
		header("Location: index.php");
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
		<?php 
			include("lib/library.html");				
		?>
		
		<script>
			$(document).ready(function() {
				//	Deaktiviere Eingabefelder für Zeit- und Stempelkontrolle bis Startnummer übergeben
				$(".custom_checkbox").prop("disabled", true);
				
				//	Funktion zur Verzögerung des .keyup
				function delay(callback, ms) {
					var timer = 0;
					
					return function() {
						var	context = this, 
							args = arguments;
						
						clearTimeout(timer);
					
						timer = setTimeout(function() {
							callback.apply(context, args);
						}, ms || 0);
					};
				}
				
				$(".time").mask("99:99", {
					placeholder: "HH:MM"
				});
				
				//	Prüfe, ob Eingaben für Teilnehmer bereits vorhanden
				$("#startnummer").keyup(delay(function(e) {
					if($("#startnummer").val() != "") {
						var	eid = <?php echo $eid; ?>,
							zid = <?php echo $zid; ?>,
							dte = '<?php echo $dte; ?>',
							sid = $("#startnummer").val();
							
						//	Blende Hinweistext für neue Anfrage aus
						$("#status_message").hide(500);
						
						//	Setze den Wert zurück
						$("#status_message").html("");
						
						//	Eintrag vorhanden. Zeige in Fahrer-Feld
						$("#fahrer").val("");
						
						//	Gebe Submit Button frei
						$("#stamped_submit").attr("disabled", false);
						
						//	Stelle ursprünglichen Wert von Submit Button wieder her
						$("#stamped_submit").val("Eintrag vornehmen");
						
						if(!isNaN(sid)) {
							$.ajax({
								url: "check_for_stamps.php",
								type: "POST",
								data:	{
											eid: eid,
											zid: zid,
											sid: sid,
											dte: dte
										},
								success: function(data) {
									if(data != "") {
										//	Splitte Callback
										var split = data.split("^");
										
										//	Fahrer besitzt keinen Namen (Dummy?)
										if(split[0] == "knv") {									
											//	Hinweistext
											$("#status_message").html("<br />Fahrer besitzt keinen Namen!<br />Möglicher Dummy. Dies zu Ihrer Information!");
											
											//	Blende Hinweistext ein
											$("#status_message").show(500);
											
											//	Eintrag vorhanden. Zeige in Fahrer-Feld
											$("#fahrer").val(split[0]);
											
											//	Gebe Stempel frei
											$(".custom_checkbox").prop("disabled", false);
											
											//	Gebe Submit Button frei
											$("#stamped_submit").attr("disabled", false);
										//	Fahrer mehrfach vorhanden
										} else if(split[0] == "kmv") {									
											//	Hinweistext
											$("#status_message").html("<br />Fahrer unter dieser Startnummer mehrfach vorhanden.<br />Bitte zuständigen Auswerter kontaktieren!");
											
											//	Blende Hinweistext ein
											$("#status_message").show(500);
											
											//	Sperre Submit Button
											$("#stamped_submit").attr("disabled", true);
										//	Fahrer nicht vorhanden
										} else if(split[0] == "kne") {									
											//	Hinweistext
											$("#status_message").html("<br />Kein Fahrer unter dieser Startnummer vorhanden.");
											
											//	Blende Hinweistext ein
											$("#status_message").show(500);
											
											//	Sperre Submit Button
											$("#stamped_submit").attr("disabled", true);
										} else if(split[0] == "kiu") {									
											//	Hinweistext
											$("#status_message").html("<br />Fahrer Name unvollständig.<br />Bitte zuständigen Auswerter kontaktieren!");
											
											//	Blende Hinweistext ein
											$("#status_message").show(500);
											
											//	Eintrag vorhanden. Zeige in Fahrer-Feld
											$("#fahrer").val(split[0]);
											
											//	Gebe Stempel frei
											$(".custom_checkbox").prop("disabled", false);
											
											//	Gebe Submit Button frei
											$("#stamped_submit").attr("disabled", false);
										} else {
											//	Eintrag vorhanden. Zeige in Fahrer-Feld
											$("#fahrer").val(split[0]);
											
											//	Gebe Stempel frei
											$(".custom_checkbox").prop("disabled", false);
											
											//	Gebe Submit Button frei
											$("#stamped_submit").attr("disabled", false);
										}
										
										if(split[1] == "1") {
											//	Ändere Wert von Hinweistext
											$("#stamped_submit").val("Eintrag überschreiben");
											
											//	Setze Stempel
											$(".custom_label").removeClass("clr");
											$(".custom_label").addClass("chk");
											$('#stamped').attr("checked", true);
										} else {
										    //	Lösche Stempel
											$(".custom_label").removeClass("chk");
											$(".custom_label").addClass("clr");
											$('#stamped').attr("checked", false);
										}
									}
								}
							});
						} else {
							//	Gebe Submit Button frei
							$("#stamped_submit").attr("disabled", true);
							
							//	Hinweistext
							$("#status_message").html("<br />Bitte ausschließlich numerische Werte übergeben!");
							
							//	Blende Hinweistext ein
							$("#status_message").show(500);
						}
					} else {
						//	Blende Hinweistext für neue Anfrage aus
						$("#status_message").hide(500);
						
						//	Setze den Wert zurück
						$("#status_message").html("");
						
						//	Eintrag vorhanden. Zeige in Fahrer-Feld
						$("#fahrer").val("");
						
						//	Gebe Submit Button frei
						$("#stamped_submit").attr("disabled", false);
						
						//	Stelle ursprünglichen Wert von Submit Button wieder her
						$("#stamped_submit").val("Eintrag vornehmen");
					}
				}, 500));
			});
			
			$("#stamped").click(function() {
				if($(".custom_label").hasClass("clr")) {
					//	Setze Stempel
					$(".custom_label").removeClass("clr");
					$(".custom_label").addClass("chk");
					$('#stamped').attr("checked", true);
				} else if($(".custom_label").hasClass("chk")) {
					//	Lösche Stempel
					$(".custom_label").removeClass("chk");
					$(".custom_label").addClass("clr");
					$('#stamped').attr("checked", false);
				}				
			});
		</script>
		
		<noscript>
			<div style="z-index: 5000; left: 0; position: fixed; text-align: center; width: 100%; height: 100%; background-color: rgba(48, 48, 48, 0.75);">
				<h2 style="line-height: 100%; padding-top: 25%; color: #fff;"><span style="border: 1px dotted #fff; padding: 25px 50px 25px 50px; background-color: rgba(255, 0, 0, 0.25)">Bitte aktivieren Sie JavaScript!</span></h2>
			</div>
		</noscript>
	</head>
	
	<body>
		<div id="container_tm">
			<div id="intro">
				<div id="pageHeader">
					<h1><span style="position:absolute; right: 30px; top: 25px;">Z 3 : 1 T : 0 0 , 000</span></h1>
					<h3><span style="position:absolute; right: 30px; top: 64px;"><i>tempus fugit ..</i></span></h3>
				</div>
			
				<div id="tm_modul_1" align="center">
					<form action="<? $_SERVER['PHP_SELF']; ?>" id="form" method="POST">
    				    <?php
    						//	Hole Bilddatei aus Datenbank
    						$select = "SELECT * FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `active` = 1 LIMIT 1";
    						$result = mysqli_query($mysqli, $select);
    						$numrow = mysqli_num_rows($result);
    						
    						if($numrow == 1) {
    							$getrow = mysqli_fetch_assoc($result);
    							$image_path = $getrow['image_path_100'];
    							
    							$image = "<img src=\"" . $image_path . "\" alt=\"Veranstaltungslogo\" height='50px' width='50px'></img>";
    						} elseif($numrow == 0) {
    							$image = "Keine Bilddatei hinterlegt!";
    						}
    					?>	
						<table cellspacing="0" cellpadding="0" border="0" width="600px">
							<tr>
								<td align="left"><h3><? echo $getrow['title']; ?> &mdash; Stempelkontrollen Zugang</h3></td>
								<td align="right"><a href="includes/opt_logout_ft.php"><strong>Ausloggen</strong></a></td>
							</tr>
						</table>						
						<p>										
							<table cellspacing="5px" cellpadding="0" style="border: 1px solid #FFFFFF;" width="600px">
								<tr>
									<td align="left" style="padding-top: 5px;"><?php echo $image; ?></td>
									<td align="center"><input type="text" name="fahrer" id="fahrer" style="background-color: rgb(236,236,236); border: 5px solid rgb(236,236,236); height: 50px; width: 100%; font-weight: 800; font-size: 15px; text-align: center; vertical-align: middle;" placeholder="Name des Fahrers" readonly required="required"></td>
									<td align="right"><input type="text" name="startnummer" id="startnummer" style="border: 5px solid #fff; height: 50px; width: 50px; font-weight: 800; font-size: 18px; text-align: center; vertical-align: middle;" placeholder="#" required="required"></td>
								</tr>
								<tr>
									<td align="left"><div style="margin-top: -10px; height: 15px; width: 0px; background-color: transparent;">&nbsp;</div></td>
									<td align="center"><div style="margin-top: -10px; text-align: center; height: 15px; width: 100%; background-color: transparent; color: #8e6516;">Bitte Startnummer eintragen</div></td>
									<td align="right"><div style="margin-top: -10px; text-align: center; height: 15px; width: 0px; background-color: transparent;">&nbsp;</div></td>
								</tr>
								<div>
									<tr>
										<th align="center" colspan="3" id="status_message" style="display: none !important;">
											<br />
											Für diesen Teilnehmer existiert bereits ein Eintrag.
											<br />
											Ein erneuter Eintrag überschreibt den bestehenden!											
										</th>
									</tr>
								</div>
							</table>
						</p>
						<p>										
							<table cellspacing="5px" cellpadding="0" style="border: 1px solid #FFFFFF;" width="600px">
								<tr>
									<th colspan="3" align="left"><?php echo strftime("%A, %d. %B %Y", strtotime($getrow_zstamp['eventdate'])); ?></th>
								</tr>
							</table>
						</p>
						<p>
							<form id="stempelkontrolle" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
								<table cellspacing="5px" cellpadding="0" style="border: 1px solid #FFFFFF;" width="600px">
									<tr>
										<td align="left"><?php echo $identification; ?></td>
										<td align="right">
											<input type="checkbox" name="zs" id="stamped" class="custom_checkbox" />
											<label for="stamped" class="custom_label"></label>
										</td>
									</tr>
									<tr>
										<td colspan="2"><hr class="normal-hr" /></td>
									</tr>
									<tr>
										<td colspan="2">&nbsp;</td>
									</tr>
									<tr>
										<td colspan="2" align="center"><input type="submit" name="stamped_submit" id="stamped_submit" value="Eintrag vornehmen" disabled /></td>
									</tr>
									</tr>
								</table>
							</form>
						</p>
					</form>
					
					<div><br /></div>
					
					<?php
						if(!empty($error_msg)) {
							echo '<p class="error">';
								echo '<span style="margin-left: 5px; border-bottom: 1px dotted #8E6516;"><font color="#FFD700">' . $state . '</font></span><br />';
								echo $error_msg;
							echo '</p>';
						}						
					
					if(isset($redir)) {
						echo $redir;
					}
					?>
				</div>
			</div>
		</div>
	</body>
</html>