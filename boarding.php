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
		$bid		= $_SESSION['user_id'];
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
			//  Stempelkontrolle
			case "zs":
				header("Location: zstamp.php");
			break;
		}
		
		//	Lege Event ID fest
		$eid = $_SESSION['eid'];
		
		//  Hole alle Informationen über diesen Bordkarten Account
		$select_bmember = "SELECT * FROM `_optio_bmembers` WHERE `eid` = '" . $eid . "' AND `id` = '" . $bid . "'";
		$result_bmember = mysqli_query($mysqli, $select_bmember);
		$numrow_bmember = mysqli_num_rows($result_bmember);
		
		//  Erstelle Array Container für Funktionär-Typen
		$official = array();
		$jsonpos = array();
		
		if($numrow_bmember == 1) {
			$getrow_bmember = mysqli_fetch_assoc($result_bmember);
			
			$dte = $getrow_bmember['eventdate'];
			
			//	Hole Reihenfolge der Bordkartenkontrolle
			$select_order = "SELECT * FROM `_optio_bmembers_order` WHERE `bid` = '" . $bid . "' ORDER BY `lfd` ASC";
			$result_order = mysqli_query($mysqli, $select_order);
			$numrow_order = mysqli_num_rows($result_order);
			
			if($numrow_order > 0) {
				while($getrow_order = mysqli_fetch_assoc($result_order)) {
					$official[$getrow_order['lfd']][0] = $getrow_order['pos_primary'];
					
					//  Wenn sekundäre Position vorhanden ist, speichere diese ebenfalls
					if($getrow_order['pos_secondary'] != "" OR !empty($getrow_order['pos_secondary'])) {
						$official[$getrow_order['lfd']][1] = $getrow_order['pos_secondary'];
					}
					
					/*
						Array
						(
							[1] => Array
								(
									[0] => ex_1
									[1] => zk_1
								)
						
							[2] => Array
								(
									[0] => zk_2
								)
						)
					*/
					
					/*
						Speichere Positionen mit Ausnahme von Prüfungen
						zusätzlich in schlichtem Array für JavaScript
					*/
					if(!preg_match('/ex_[0-9]{1}/', $getrow_order['pos_primary']) AND ($getrow_order['pos_primary'] != "" OR !empty($getrow_order['pos_primary']))) {
						$jsonpos[] = $getrow_order['pos_primary'];
					}
					
					if(!preg_match('/ex_[0-9]{1}/', $getrow_order['pos_secondary']) AND ($getrow_order['pos_secondary'] != "" OR !empty($getrow_order['pos_secondary']))) {
						$jsonpos[] = $getrow_order['pos_secondary'];
					}
				}
				
				//	Setze Array Schlüssel zurück
				$jsonpos = array_values($jsonpos);
			//  Fehler: Keine Positionen gefunden
			} else {
				echo 	'<meta http-equiv="refresh" content="10; url=/msdn/error.php?code=interface&add=nopos&type=bc">';
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
				$(".time").prop("disabled", true);				
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
				
				//	Lösche Zeit
				$(".delete").click(function() {
					//	Hole ID
					var id = $(this).attr("id");
					var split = id.split("_");
					var pos = split[1];
					
					//  Setze Parameter
					var	eid = <?php echo $eid; ?>,
						dte = '<?php echo $dte; ?>',
						sid = $("#startnummer").val(),
						pos = pos;
						
						$.ajax({
							url: "update_boarding.php",
							type: "POST",
							data:	{
										eid: eid,
										pos: pos,
										sid: sid,
										dte: dte,
										act: "delete"
									},
							success: function(data) {
								if(data != "") {
									if(data == 1) {
										$("#status_message").html("<br />Zeit gelöscht!");
												
										//	Blende Hinweistext ein
										$("#status_message").show(500);
										
										$("#zk_" + pos).val("");
										$("#zk_" + pos).attr("placeholder", "HH:MM");
									} else if(data == 0) {
										$("#status_message").html("<br />Zeit konnte nicht gelöscht werden.<br />Bitte zuständigen Auswerter kontaktieren!");
												
										//	Blende Hinweistext ein
										$("#status_message").show(500);
									} else if(data == -1) {
										$("#status_message").html("<br />Zeit wurde bereits gelöscht!");
												
										//	Blende Hinweistext ein
										$("#status_message").show(500);
									}
								}
							},
							error: function(data) {
								//	Server nicht erreichbar
								$("#status_message").html("<br />Server nicht erreichbar!<br />Prüfen Sie Ihre Internetverbindung!");
								
								//	Blende Hinweistext ein
								$("#status_message").show(500);
							}
						});
				});
				
				//	Prüfe, ob Eingaben für Teilnehmer bereits vorhanden
				$("#startnummer").keyup(delay(function(e) {
				    //  Setze Parameter
					var	eid = <?php echo $eid; ?>,
						dte = '<?php echo $dte; ?>',
						sid = $("#startnummer").val(),
						pos = <?php echo json_encode($jsonpos); ?>;

					//	Blende Hinweistext für neue Anfrage aus
					$("#status_message").hide(500);
					
					//	Stelle ursprünglichen Wert von Submit Button wieder her
					$("#boarding").val("Eintrag vornehmen");
					
					//	Deaktiviere Eingabefelder für Zeit- und Stempelkontrolle bis Startnummer übergeben
					$(".time").prop("disabled", true);				
					$(".custom_checkbox").prop("disabled", true);
					
					//	Setze Eingabefelder zurück
					$(".time").val("");
					$(".custom_label").removeClass("chk");
					$(".custom_label").addClass("clr");
					$(".custom_checkbox").attr("checked", false);
					
					$("#fahrer").val("");
					
					var blocked = true;
					
					//	Ist Startnummer numerisch
					if(!isNaN(sid)) {
						//	Nur ausführen, wenn Wert vorhanden
						if(sid !== "") {
							$.ajax({
								url: "check_for_boarding.php",
								type: "POST",
								data:	{
											eid: eid,
											sid: sid,
											dte: dte,
											val: "",
											pos: pos
										},
								dataType: "json",
								success: function(data) {
									if(data != "") {
										$.each(data, function(key, value) {
											//	alert( "The key is '" + key + "' and the value is '" + value + "'" );
											
											//	Wenn Fahrer gefunden, setze diesen in Eingabefeld
											if(key == "sid") {
												if(value == "tmv") {
													$("#status_message").html("<br />Fahrer unter dieser Startnummer mehrfach vorhanden.<br />Bitte zuständigen Auswerter kontaktieren!");
													
													//	Blende Hinweistext ein
													$("#status_message").show(500);
												} else if(value == "tnv") {
													$("#status_message").html("<br />Fahrer besitzt keinen Namen!<br />Möglicher Dummy zu Ihrer Information!");
													
													$("#fahrer").val("Dummy-Teilnehmer");
													
													//	Blende Hinweistext ein
													$("#status_message").show(500);
													
													blocked = false;
												} else if(value == "tne") {
													$("#status_message").html("<br />Fahrer existiert nicht!<br />");
													
													//	Blende Hinweistext ein
													$("#status_message").show(500);
												} else {
													$("#fahrer").val(value);
													
													blocked = false;
												}
											} else if(key !== "sid") {
												if(blocked == false) {
													//	Gebe Eingabefelder frei
													$(".time").prop("disabled", false);				
													$(".custom_checkbox").prop("disabled", false);
													
													//	Stempel gesetzt
													if(value == 1) {
														//	Setze Stempel
														$("." + key).removeClass("clr");
														$("." + key).addClass("chk");
														$("#" + key).attr("checked", true);
													} else if(value !== "") {
														//	Setze Zeit
														$("#" + key).val(value);
													}
												}
											}
										});
									}
								},
								error: function(data) {
									//	Server nicht erreichbar
									$("#status_message").html("<br />Server nicht erreichbar!<br />Prüfen Sie Ihre Internetverbindung!");
									
									//	Blende Hinweistext ein
									$("#status_message").show(500);
								}
							});
						}
					} else {
						//	Kein numerischer Wert
						$("#status_message").html("<br />Bitte nur numerische Werte übergeben!");
						
						//	Blende Hinweistext ein
						$("#status_message").show(500);
					}
				}, 500));
				
				//  Erkenne Änderung von Stempelkontrolle
				$(".custom_checkbox").click(function() {
				    //  Setze Parameter
				    var	eid = <?php echo $eid; ?>,
						dte = '<?php echo $dte; ?>',
		                sid = $("#startnummer").val();
		                
			        //  Hole ID um eindeutig zuordnen zu können
			        var elem = $(this).attr("id");
				    
				    if($("#" + elem).is(":checked")) {
			            $("#" + elem).removeClass("chk");
			            $("#" + elem).addClass("clr");
			            $("#" + elem).attr("checked", false);
						
						//  Hole Wert des gewählten Elements
						var val = $("#" + elem).val();
				    } else {
			            $("#" + elem).removeClass("clr");
			            $("#" + elem).addClass("chk");
			            $("#" + elem).attr("checked", true);
						
						//  Hole Wert des gewählten Elements
						var val = $("#" + elem).val();
				    }
			            
		            //  Speichere neuen Wert direkt in Datenbank
		            $.ajax({
					    url: "update_boarding.php",
					    type: "POST",
					    data:	{
					                eid: eid,
    								sid: sid,
    								dte: dte,
    								val: val,
    								pos: elem
					    },
					    success: function(data) {
						    if(data == 1) {
                                //	Datensatz aktualisiert
							    $("#status_message").html("<br />Änderung wurde gespeichert!");
							    
							    //	Blende Hinweistext ein
							    $("#status_message").show(500);
						    } else if(data == 0) {
                                //	Datensatz nicht aktualisiert
                                $("#status_message").html("<br />Änderung wurde nicht gespeichert!");
                                
                                //	Blende Hinweistext ein
							    $("#status_message").show(500);
						    }
					    },
					    error: function(data) {
					        //	Server nicht erreichbar
							$("#status_message").html("<br />Server nicht erreichbar!<br />Prüfen Sie Ihre Internetverbindung!");
							
							//	Blende Hinweistext ein
							$("#status_message").show(500);
					    }
		            });
				});
				
				//	Erkenne Änderung von Zeitkontrolle
				$(".time").keyup(delay(function(e) {
					//  Setze Parameter
					var	eid = <?php echo $eid; ?>,
						dte = '<?php echo $dte; ?>',
						sid = $("#startnummer").val();
						
					//  Hole ID um eindeutig zuordnen zu können
					var elem = $(this).attr("id");
					
					//  Hole Wert des gewählten Elements
					var val = $("#" + elem).val();
					
					//  Speichere neuen Wert direkt in Datenbank
					$.ajax({
						url: "update_boarding.php",
						type: "POST",
						data:	{
									eid: eid,
									sid: sid,
									dte: dte,
									val: val,
									pos: elem
						},
						success: function(data) {
							if(data == 1) {
								//	Datensatz aktualisiert
								$("#status_message").html("<br />Änderung wurde gespeichert!");
								
								//	Blende Hinweistext ein
								$("#status_message").show(500);
							} else if(data == 0) {
								//	Datensatz nicht aktualisiert
								$("#status_message").html("<br />Änderung wurde nicht gespeichert!");
								
								//	Blende Hinweistext ein
								$("#status_message").show(500);
							} else if(data == -1) {
								//	Datensatz nicht aktualisiert
								$("#status_message").html("<br />Bitte gültigen Wert übergeben!");
								
								//	Blende Hinweistext ein
								$("#status_message").show(500);
							}
						},
						error: function(data) {
							//	Server nicht erreichbar
							$("#status_message").html("<br />Server nicht erreichbar!<br />Prüfen Sie Ihre Internetverbindung!");
							
							//	Blende Hinweistext ein
							$("#status_message").show(500);
						}
					});
				}, 1000));
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
								<td align="left"><h3><? echo $getrow['title']; ?> &mdash; Bordkarten Zugang</h3></td>
								<td align="right"><a href="includes/opt_logout_mz.php"><strong>Ausloggen</strong></a></td>
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
									<th colspan="3" align="left"><?php echo strftime("%A, %d. %B %Y", strtotime($getrow_bmember['eventdate'])); ?></th>
								</tr>
							</table>
						</p>
						<p>										
							<table cellspacing="5px" cellpadding="0" style="border-top: 1px solid #FFFFFF; border-left: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF; float: left;" width="450px">
								<?php
									//  Debugging
									/*
										echo "<pre>";
										print_r($official);
										echo "</pre><br />";
									*/ 
										
									/*
										Array
										(
											[1] => Array
												(
													[0] => ex_1
													[1] => zk_1
												)
										
											[2] => Array
												(
													[0] => zk_2
												)
										)
									*/
								
									for($i = 1; $i < (count($official) + 1); $i++) {
										//	Lege Bezeichner fest und setze diesen mit jedem Durchlauf zurück
										$bezeichner = "";
										
										//	Prüfe auf benötigtes "Start" Präfix
										if($i == 1) {
											$prefix = "Start ";
										} else {
											$prefix = "";
										}
										
										//	Differenziere auf entsprechende Positionen
										if(isset($official[$i][0])) {
											//  Extrapoliere Daten von primärer Position
											$explode = explode("_", $official[$i][0]);
											
											//  Art der Position (ex, zs, zk)
											$fn1 = $explode[0];
											
											//  ID
											$id1 = $explode[1];
										} 
										
										if(isset($official[$i][1])) {
											//  Extrapoliere Daten von sekundärer Position
											$explode = explode("_", $official[$i][1]);
											
											//  Art der Position (ex, zs, zk)
											$fn2 = $explode[0];
											
											//  ID
											$id2 = $explode[1];
										}
										
										//  Suche, entsprechender der Art der Position nach zugehörigen Daten
										switch($fn1) {
											case "ex":
												$select = "SELECT * FROM `_main_wptable` WHERE `eid` = '" . $eid . "' AND `id` = '" . $id1 . "'";
											break;
											case "zk":
												$select = "SELECT * FROM `_optio_zcontrol` WHERE `eid` = '" . $eid . "' AND `id` = '" . $id1 . "'";
											break;
											case "zs":
												$select = "SELECT * FROM `_optio_zstamp` WHERE `eid` = '" . $eid . "' AND `id` = '" . $id1 . "'";
											break;
										}
										
										$result = mysqli_query($mysqli, $select);
										$numrow = mysqli_num_rows($result);
										
										//  Wurde Ergebnis gefunden, hole Daten
										if($numrow == 1) {
											$getrow = mysqli_fetch_assoc($result);
											
											if($fn1 == "ex") {
												//	Lege Höhe dynamisch fest
												$height = "35px";
												
												$bezeichner .= $prefix . $getrow['rid_type'] . $getrow['rid'] . '&emsp; ' . $getrow['title'];
											} elseif($fn1 == "zk") {
												//	Lege Höhe dynamisch fest
												$height = "35px";
												
												$bezeichner .= $prefix . $getrow['opt_whois'] . '&emsp; ' . $getrow['title'];
											} elseif($fn1 == "zs") {
												//	Lege Höhe dynamisch fest
												$height = "43px";
												
												$bezeichner .= $prefix . $getrow['opt_whois'] . '&emsp; ' . $getrow['title'];
											}
										}
										
										if(isset($official[$i][1])) {
											//  Suche, entsprechender der Art der Position nach zugehörigen Daten
											switch($fn2) {
												case "ex":
													$select = "SELECT * FROM `_main_wptable` WHERE `eid` = '" . $eid . "' AND `id` = '" . $id2 . "'";
												break;
												case "zk":
													$select = "SELECT * FROM `_optio_zcontrol` WHERE `eid` = '" . $eid . "' AND `id` = '" . $id2 . "'";
												break;
												case "zs":
													$select = "SELECT * FROM `_optio_zstamp` WHERE `eid` = '" . $eid . "' AND `id` = '" . $id2 . "'";
												break;
											}
											
											$result = mysqli_query($mysqli, $select);
											$numrow = mysqli_num_rows($result);
											
											//  Wurde Ergebnis gefunden, hole Daten
											if($numrow == 1) {
												$getrow = mysqli_fetch_assoc($result);
												
												if($fn2 == "ex") {
													//	Lege Höhe dynamisch fest
													$height = "35px";
													
													$bezeichner .= " / " . $getrow['rid_type'] . $getrow['rid'] . '&emsp; ' . $getrow['title'];
												} elseif($fn2 == "zk") {
													//	Lege Höhe dynamisch fest
													$height = "35px";
													
													$bezeichner .= " / " . $getrow['opt_whois'] . '&emsp; ' . $getrow['title'];
												} elseif($fn2 == "zs") {
													//	Lege Höhe dynamisch fest
													$height = "43px";
													
													$bezeichner .= " / " . $getrow['opt_whois'] . '&emsp; ' . $getrow['title'];
												}
											}
										}
										
										echo	'
												<tr>
													<td style="height: ' . $height . ';">' . $bezeichner . '</td>
													<td style="height: ' . $height . ';">&nbsp;</td>
												</tr>
												';
									}
								?>
							</table>
							<table cellspacing="5px" cellpadding="0" style="border-top: 1px solid #FFFFFF; border-right: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF; display: inline-block;" width="150px">
								<?php
									for($i = 1; $i < (count($official) + 1); $i++) {
										for($j = 0; $j < count($official[$i]); $j++) {
											if($i == 1 AND $j == 0) {
												$prefix = "Start ";
											} else {
												$prefix = "";
											}
											
											//  Extrapoliere Daten
											$explode = explode("_", $official[$i][$j]);
											
											//  Art der Position (ex, zs, zk)
											$fn = $explode[0];
											
											//  ID
											$id = $explode[1];
											
											//  Suche, entsprechender der Art der Position nach zugehörigen Daten
											switch($fn) {
												case "ex":
													$select = "SELECT * FROM `_main_wptable` WHERE `eid` = '" . $eid . "' AND `id` = '" . $id . "'";
												break;
												case "zk":
													$select = "SELECT * FROM `_optio_zcontrol` WHERE `eid` = '" . $eid . "' AND `id` = '" . $id . "'";
												break;
												case "zs":
													$select = "SELECT * FROM `_optio_zstamp` WHERE `eid` = '" . $eid . "' AND `id` = '" . $id . "'";
												break;
											}
											
											$result = mysqli_query($mysqli, $select);
											$numrow = mysqli_num_rows($result);
											
											//  Wurde Ergebnis gefunden, hole Daten
											if($numrow == 1) {
												$getrow = mysqli_fetch_assoc($result);
												
												if($fn == "zk") {
													//	Lege Höhe dynamisch fest
													$height = "35px";
													
													echo	'
															<tr>
																<td style="height: ' . $height . '; text-align: right;">
																	<input type="tel" class="fn time" id="zk_' . $getrow['id'] . '" name="fn[]" value="" maxlength="5" required="required" placeholder="HH:MM" style="border: 1px solid white; height: 35px; width: 55px; font-weight: 800; font-size: 13px; text-align: center; vertical-align: middle;" />
																</td>
																<td style="height: ' . $height . '; text-align: right;">
																	<input type="text" value="Uhr" style="border: 0; background-color: transparent; height: 35px; width: 35px; font-weight: 800; font-size: 13px; text-align: center; vertical-align: middle;" readonly disabled />
																</td>
																<td style="height: ' . $height . '; text-align: right;">
																	<img src="images/delete.png" id="delzk_' . $getrow['id'] . '" class="delete"></img>
																</td>
															</tr>
															';
												} elseif($fn == "zs") {
													//	Lege Höhe dynamisch fest
													$height = "43px";
													
													echo	'
															<tr>
																<td style="height: ' . $height . ';" colspan="3">
																	<input type="checkbox" id="zs_' . $getrow['id'] . '" name="fn[]" class="custom_checkbox fn" />
																	<label for="zs_' . $getrow['id'] . '" class="custom_label zs_' . $getrow['id'] . '" style="margin-left: -3px;"></label>
																</td>
															</tr>
															';
												}
											}
										}
									}
								?>
							</table>
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