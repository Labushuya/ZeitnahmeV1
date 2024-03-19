<?php error_reporting(E_ALL);
	// BUFFER OUTPUT
	ob_start();

	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
	
	// START SECURE SESSION
	sec_session_start();
	 
	// CUSTOM NAVBAR
	if(login_check($mysqli) == true) {
		// SEARCH FOR USER EVENTS
		// CREATE EVENT ID FROM ACTIVE SESSION
		$eid	= $_SESSION['user_id'];
		
		// SEARCH FOR EVENTS FROM LOGGED IN USER
		$select_event = "SELECT `id`, `eid` FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `active` = '1'";
		$result_event = mysqli_query($mysqli, $select_event);
		$numrow_event = mysqli_num_rows($result_event);
		
		// SEARCH FOR EVENTS FROM LOGGED IN USER
		if($numrow_event == 1) {
			$logged = file_get_contents("essentials/logout.html");
			$navbar = file_get_contents("essentials/navbar_logged_in_active.html");
		// NO EVENT FOUND
		} elseif($numrow_event == 0) {
			$logged = file_get_contents("essentials/logout.html");
			$navbar = file_get_contents("essentials/navbar_logged_in.html");
		}
	} else {
		$logged = file_get_contents("essentials/login.html");
		$navbar = file_get_contents("essentials/navbar_logged_out.html");
		header("Location: index.php");
	}
		
	// CHECK FOR ERRORS
	if(isset($_GET['error'])) {
        echo "<p id=\"error\">Error Logging In!</p>";
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
			include("lib/library_int_my_event.html");
		?>
		
		<script>
			$(document).ready(function() {
				// Ergebnisse: CSV Export
				$('#csv_export').click(function(){
					var rid = $('#rd_fetch').val();
					window.location.href = 'csv_export.php?rid=' + rid;
				});
				
				// Ergebnisse: XLS Export
				$('#xls_export').click(function(){
					var rid = $('#rd_fetch').val();		
					window.location.href = 'xls_export.php?rid=' + rid;
				});
				
				// Ergebnisse: XLSX Export
				$('#xlsx_export').click(function(){
					var rid = $('#rd_fetch').val();		
					window.location.href = 'xlsx_export.php?rid=' + rid;
				});
				
				// Ergebnisse: ODS Export
				$('#ods_export').click(function(){
					var rid = $('#rd_fetch').val();		
					window.location.href = 'ods_export.php?rid=' + rid;
				});
				
				// Auswertung: Export
				$('.eval_select').change(function(){
					// Export als?
					var export_type = $("option:selected", this).text();
					
					// rid
					var rid = $(this).val();	

					// Prüfe nach Typ
					if(export_type == "CSV") {
						window.location.href = 'eval_csv_export.php?eid=' + <?php echo $eid; ?> + '&rid=' + rid;
					} else if(export_type == "ODS") {
						window.location.href = 'eval_ods_export.php?eid=' + <?php echo $eid; ?> + '&rid=' + rid;
					} else if(export_type == "XLS") {
						window.location.href = 'eval_xls_export.php?eid=' + <?php echo $eid; ?> + '&rid=' + rid;
					} else if(export_type == "XLSX") {
						window.location.href = 'eval_xlsx_export.php?eid=' + <?php echo $eid; ?> + '&rid=' + rid;
					} else if(export_type == "PDF") {
						window.location.href = 'eval_pdf_export.php?eid=' + <?php echo $eid; ?> + '&rid=' + rid;
					} else if(export_type == "HTML") {
						// Popup-Block verhindern
						// Setze URL
						var export_html_url = "dinax_export.php?eid=" + <?php echo $eid; ?> + "&rid=" + rid + "&sort=abw";
						
						$.ajax({
							url: "dinax_export.php",
							data: {
									eid: <?php echo $eid; ?>, 
									rid: rid,
									sort: "abw"
							},
							success: function(){
								window.open(export_html_url);
							},
							async: false
						});
					}
				});				
				
				/*	
					Verstecke Radio Buttons zum Neutralisieren der einzelnen 
					Prüfungen aber behalte Original-Breite bei
				*/
				$(".toggle_neutralize").css('visibility', 'hidden');
				
				//	Bei Klick wird die Sicht freigegeben oder gesperrt
				$("#toggle_switch_neutralize").click(function() {
					if($(".toggle_neutralize").css("visibility") == "hidden") {
						$(".toggle_neutralize").css('visibility', 'visible');
					} else if($(".toggle_neutralize").css("visibility") == "visible") {
						$(".toggle_neutralize").css('visibility', 'hidden');
					}
				});
				
				//	Zeitnehmer ausloggen
				$('.logout').click(function(){
					var fid = $(this).attr('id');		
					
					$.ajax({
						type: 'POST',
						url: "fn_logout.php",
						data: {
								eid: <?php echo $eid; ?>, 
								fid: fid
						},
						success: function(html){
							if(html == "success") {
								$("#" + fid).html("<font size=\"2\" color=\"#FF0000\">Ausgeloggt</font>");
								alert("Funktionär wurde ausgeloggt!");
							} else if(html == "multiple") {
								alert("Funktionär mehrfach vorhanden!");
							} else if(html == "nouser") {
								$("#" + fid).html("<font size=\"2\" color=\"#FF0000\">Ausgeloggt</font>");
								alert("Funktionär nicht vorhanden!\r\n(zwischenzeitlich gelöscht?)");
							} else if(html == "already") {
								$("#" + fid).html("<font size=\"2\" color=\"#FF0000\">Ausgeloggt</font>");
								alert("Funktionär bereits ausgeloggt!\r\n(zwischenzeitlich ausgeloggt?)");
							}							
						}
					});
				});
				
				//	Als Teilnehmer einloggen
				$('.tn_login').click(function(){
					var sid = $(this).attr('id');	
					var eid = <?php echo $eid; ?>;
					var win = window.open('/msdn/racer.php?eid=' + eid + '&sid=' + sid, '_blank');
                                
                    if(win) {
                        //  Setze Fokus auf neues Tab
                        win.focus();
                    } else {
                        //  Browser lässt keine Popups zu
                        alert('Bitte erlauben Sie Popups in Ihrem Browser!');
                    }
				});
				
				//  Verstecke Modal Container
				$("#repair-confirm").hide();
				
				//  Reparieren-Funktion
				$(".repair").click(function() {
				    //  Setze Standard Text zurück
				    $("#repair-text").html("");
				    $("#repair-text").html("<span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:12px 12px 20px 0;\"></span>Bitte Wahl der Wiederherstellung wählen<br /><br /><select id=\"recovery_type\" name=\"revovery_type\" style=\"width: 250px !important;\"><option selected disabled>Bitte wählen</option><option value=\"results\">Aus Ergebnisliste extrapolieren</option><option disabled value=\"position\">Positionen manuell übergeben</option></select>");
				    
				    //  Setze ZID Buffer zurück
				    $("#zid_buffer").val("");
				    
				    //  Hole ID von Zeitnehmer, dessen Positionen repariert werden sollen
				    var zid = $(this).attr('id');
				    
				    //  Und speichere ihn in Hidden Input für nächsten Abruf
				    $("#zid_buffer").val(zid);
				    
				    $("#repair-confirm").dialog({
                        resizable: false,
                        height: "auto",
                        width: 400,
                        modal: true,
                        buttons: {
                            Cancel: function() {
                                $(this).dialog("close");
                            }
                        }
                    });  
				});
				
				$("body").on("change", "#recovery_type", function() {
				    //  Hole ID von Zeitnehmer, dessen Positionen repariert werden sollen
				    var zid = $("#zid_buffer").val();
				    
				    $.ajax({
						type: 'POST',
						url: "recover_zn_pos.php",
						data: {
								eid: <?php echo $eid; ?>, 
								zid: zid
						},
						success: function(html){
						    if(html == "success") {
						        $("#append_repair_zn").html(html);
								$("#repair-text").html("Gefundene Zeitnehmer Positionen konnten erfolgreich aus bereits erfolgten<span style=\"color: #FF0000;\">*</span> Zeiteneingaben wiederhergestellt werden!<br /><br /><br /><br /><span style=\"color: #FF0000;\">*</span> Es werden ausschließlich Positionen wiederhergestellt, für die Ergebnis Eingaben existieren. Ihr Gegenprüfen ist erforderlich!");
							} else if(html == "partial") {
								$("#repair-text").html("Gefundene Zeitnehmer Positionen konnten <strong>teilweise</strong> wiederhergestellt werden!<br /><br />Bitte kontaktieren Sie ggf. den Support, um dieses Problem zu lösen oder löschen Sie den Zeitnehmer und legen diesen anschließend neu an!");
							} else if(html == "nothing") {
							   $("#repair-text").html("Es konnten keine Zeitnehmer Positionen ermittelt werden!");    
							}
						},
						error: function(xhr, status, error){
                            var errorMessage = xhr.status + ': ' + xhr.statusText;
						    $("#repair-text").html("<span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:12px 12px 20px 0;\"></span><span style=\"border-bottom: 1px solid red; font-weight: 800;\">Fehler:</span><br /><br />" + errorMessage);
						}
					});	
				});
				
				//	Editiere Zeit
				$(document).on("click", ".edit", function() {
					//	Stoppe automatische Aktualisierung
					clearInterval(interval);
					
					//	Hole ID zu geklicktem Element
					var id = $(this).attr("id");
								
					//	Splitte ID in reine DS-ID auf
					var split = id.split("_");
					
					//	Hole Element
					var scrollto = $("#scrollto_" + split[1]);
					
					//	Scrolle zu betroffener Zeile
					$('#dialog_res').animate({
						scrollTop: scrollto.offset().top - 375
					}, 2000);
					
					//	Prüfe, auf Prüfungsart (Sprint oder regulär --> Eingabemaske)
					var rid = $("#rd_fetch").val();
					
					$.ajax({
						type: 'POST',
						url: 'fetch_pruefung_type.php',
						data: {
								rid: rid
						},
						success: function(html) {
							//	Gebe Eingabefeld frei
							$("." + id).removeAttr("disabled");
							
							//	Gebe klaren Hinweis auf mögliche Eingabe
							$("." + id).focus();
							
							//	Prüfe, ob Anfrage Ergebnis gebracht hat
							if(html != "no_eid") {
								//	Weise Eingabemaske zu
								if(html == "is_sprint") {
									$("." + id).mask("99:99,99",{placeholder:"MM:SS,00"});
									
									//	Setze Platzhalter
									$("." + id).attr("placeholder", "MM:SS,00");
									
									//	Setze Eingabemuster
									var format = new RegExp('^([0-5][0-9]:[0-5][0-9],[0-9][0-9]){1}$');
								} else if(html == "no_sprint") {
									$("." + id).mask("99:99:99,99",{placeholder:"HH:MM:SS,00"});
									
									//	Setze Platzhalter
									$("." + id).attr("placeholder", "HH:MM:SS,00");
									
									//	Setze Eingabemuster
									var format = new RegExp('^(([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9],[0-9][0-9]){1}$');
								}
								
								//	Ersetze Bearbeiten Icon durch Speichern Icon
								$("#substitute_" + split[1]).html('<img src="images/save.png" title="Ergebnis speichern" alt="Ergebnis speichern" id="save_result_edit_' + split[1] + '" class="save_result_edit"></img><img src="images/cross.png" title="Aktion abbrechen" alt="Aktion abbrechen" id="cancel_result_edit_' + split[1] + '" class="cancel_result_edit"></img>');
								
								$("." + id).keyup(function() {
									//	Hole Wert
									var result_input = this.value;

									//	Teste auf vorgegebenes Format
									if(format.test(result_input)) {
										$("#substitute_" + split[1]).html('<img src="images/save.png" title="Ergebnis speichern" alt="Ergebnis speichern" id="save_result_edit_' + split[1] + '" class="save_result_edit"></img><img src="images/cross.png" title="Aktion abbrechen" alt="Aktion abbrechen" id="cancel_result_edit_' + split[1] + '" class="cancel_result_edit"></img>');
									} else {
										$("#substitute_" + split[1]).html('<img src="images/save_disabled.png" title="Eingabeformat beachten!" alt="Eingabeformat beachten!" id="save_' + split[1] + '"></img><img src="images/cross.png" title="Aktion abbrechen" alt="Aktion abbrechen" id="cancel_result_edit_' + split[1] + '" class="cancel_result_edit"></img>');
									}
								});
							//	Keine EID gefunden, schließe Eingabe und fordere zum Login auf
							} else {
								$("#substitute_" + split[1]).html('');
								
								//	Sperre Eingabefeld
								$("." + id).attr("disabled");
								
								alert("Ihre Event-ID konnte nicht ermittelt werden! Bitte loggen Sie sich erneut ein!");
							}
						}
					});
					
					//	Breche Editieren von Zeit ab
					$(document).on("click", ".cancel_result_edit", function() {			
						//	Hole ID zu geklicktem Element
						var id = $(this).attr("id");
						
						//	Splitte ID in reine DS-ID auf
						var split = id.split("cancel_result_edit_");
						
						//	Ersetze Speichern Icon durch Bearbeiten Icon
						$("#substitute_" + split[1]).html('<img src="images/edit.png" title="Ergebnis editieren" alt="Ergebnis editieren" id="edit_' + split[1] + '" class="edit"></img>');
						
						//	Sperre Eingabefeld
						$("." + id).attr("disabled");
						
						//	Scrolle zu betroffener Zeile
						$('#dialog_res').animate({
							scrollTop: scrollto.offset().top - 375
						}, 2000);
						
						//	Stelle Intervall wieder her
						var interval =	setInterval(function() {
											fetchResult();
										}, 60000);
					});
					
					//	Speichere editierte Zeit
					$(document).on("click", ".save_result_edit", function() {			
						//	Hole ID zu geklicktem Element
						var id = $(this).attr("id");
						
						//	Splitte ID in reine DS-ID auf
						var split = id.split("save_result_edit_");
						
						//	ID zu Datensatz
						var vid = split[1];
						
						//	Hole Klasse und ID zu zugehörigem Eingabefeld
						var input_id = $(".edit_" + split[1]).attr("id");
						
						//	Splitte Input-ID in reine DS-ID auf
						var split2 = input_id.split("_");
						
						//	Hole RID
						var rid = split2[0];
						
						//	Hole editierten Wert
						var val = $("#" + input_id).val();
						
						$.ajax({
							type: 'POST',
							url: "edit_result.php",
							data: {
									rid: rid,
									vid: vid,
									val: val
							},
							success: function(html) {
								if(html != "no_eid") {
									//	Keine RID übergeben worden
									if(html == "no_rid") {
										alert("[<img src=\"images/cross.png\"></img>] Prüfung existiert nicht (mehr)!");
									} else if(html == "failed") {
										alert("[<img src=\"images/cross.png\"></img>] Aktualisierung konnte nicht durchgeführt werden. Laden Sie die Seite neu und versuchen Sie es noch einmal.\r\nSollte der Fehler weiterhin bestehen, kontaktieren Sie bitte den Kundendienst!");
									} else if(html == "no_change") {
										alert("[<img src=\"images/cross.png\"></img>] Aktualisierung wurde nicht durchgeführt, da beide Ergebnisse identisch sind!");
									} else if(html == "no_result") {
										alert("[<img src=\"images/cross.png\"></img>] Aktualisierung konnte nicht durchgeführt werden, da Datensatz nicht (mehr) existiert. Loggen Sie sich als Zeitnehmer der entsprechenden Prüfung und Position ein, und vergeben Sie das Ergebnis für diesen Teilnehmer manuell!");
									} else if(html == "success") {
										alert("[<img src=\"images/tick.png\"></img>] Aktualisierung durchgeführt! Ergebnisliste wird neu geladen!");
									}
								//	Keine EID gefunden, schließe Eingabe und fordere zum Login auf
								} else {
									$("#substitute_" + split[1]).html('');
									
									//	Sperre Eingabefeld
									$("." + id).attr("disabled");
									
									alert("Ihre Event-ID konnte nicht ermittelt werden! Bitte loggen Sie sich erneut ein!");
								}
							},
							//	Ändern von Zeit nicht möglich
							error: function(xhr, status, error) {
								alert("Es konnte keine Verbindung hergestellt werden. Bitte versuchen Sie es zu einem späteren Zeitpunkt erneut!");
							}
						});	
						
						//	Stelle Ursprung wieder her
						//	Ersetze Speichern Icon durch Bearbeiten Icon
						$("#substitute_" + split[1]).html('<img src="images/edit.png" title="Ergebnis editieren" alt="Ergebnis editieren" id="edit_' + split[1] + '" class="edit"></img>');
						
						//	Sperre Eingabefeld
						$("." + id).attr("disabled");
						
						//	Scrolle zu betroffener Zeile
						$('#dialog_res').animate({
							scrollTop: scrollto.offset().top - 375
						}, 2000);
						
						//	Stelle Intervall wieder her
						var interval =	setInterval(function() {
											fetchResult();
										}, 60000);
					});
				});
				
				$(document).on("click", ".save", function() {
					//	Hole ID zu geklicktem Element
					var id = $(this).attr("id");
					
					$.ajax({
						type: 'POST',
						url: "recover_zn_pos.php",
						data: {
								eid: <?php echo $eid; ?>, 
								zid: zid
						},
						success: function(html){
						    if(html == "success") {
						        $("#append_repair_zn").html(html);
								$("#repair-text").html("Gefundene Zeitnehmer Positionen konnten erfolgreich aus bereits erfolgten<span style=\"color: #FF0000;\">*</span> Zeiteneingaben wiederhergestellt werden!<br /><br /><br /><br /><span style=\"color: #FF0000;\">*</span> Es werden ausschließlich Positionen wiederhergestellt, für die Ergebnis Eingaben existieren. Ihr Gegenprüfen ist erforderlich!");
							} else if(html == "partial") {
								$("#repair-text").html("Gefundene Zeitnehmer Positionen konnten <strong>teilweise</strong> wiederhergestellt werden!<br /><br />Bitte kontaktieren Sie ggf. den Support, um dieses Problem zu lösen oder löschen Sie den Zeitnehmer und legen diesen anschließend neu an!");
							} else if(html == "nothing") {
							   $("#repair-text").html("Es konnten keine Zeitnehmer Positionen ermittelt werden!");    
							}
						},
						error: function(xhr, status, error){
                            var errorMessage = xhr.status + ': ' + xhr.statusText;
						    $("#repair-text").html("<span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:12px 12px 20px 0;\"></span><span style=\"border-bottom: 1px solid red; font-weight: 800;\">Fehler:</span><br /><br />" + errorMessage);
						}
					});	
				});
			});
		</script>
		
		<style>
			label:before {
				background-image: url(images/tick.png);
			}
			
			:checked + label:before {
				background-image: url(images/cross.png);
			}
		</style>
		
		<noscript>
			<div style="z-index: 5000; left: 0; position: fixed; text-align: center; width: 100%; height: 100%; background-color: rgba(48, 48, 48, 0.75);">
				<h2 style="line-height: 100%; padding-top: 25%; color: #fff;"><span style="border: 1px dotted #fff; padding: 25px 50px 25px 50px; background-color: rgba(255, 0, 0, 0.25)">Bitte aktivieren Sie JavaScript!</span></h2>
			</div>
		</noscript>
	</head>

	<body>
		<div id="container">
			<div id="linkList">			
				<!--	NAVIGATION	-->
				<?php
					echo $navbar;
				?>
				
				<!--	LOGIN 		-->
				<?php
					echo $logged;
				?>
			</div>
			
			<div id="intro">
				<div id="pageHeader">
					<h1><span style="position:absolute; right: 30px; top: 25px;">Z 3 : 1 T : 0 0 , 000</span></h1>
					<h3><span style="position:absolute; right: 30px; top: 64px;"><i>tempus fugit ..</i></span></h3>
				</div>
			
				<!-- 	COLUMN 1	-->
				<div id="modul_1" align="center">
					<h3>Meine Veranstaltung</h3>
					<p>
						<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="my_event_form">
							<?php error_reporting(E_ALL);
								// EVENT SAVE / EDIT
								// CHECK FOR LOGGED IN USER
								if(login_check($mysqli) == true) {											
									// CREATE QUERIES
									$se_event				= "SELECT * FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `active` = '1'";
									$se_active				= "SELECT * FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `active` = '1' AND `edit` = '0'";
									$se_inactive			= "SELECT * FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `active` = '1' AND `edit` = '1'";
									$up_estatus_setactive	= "UPDATE `_race_run_events` SET `edit` = '1' WHERE `eid` = '" . $eid . "' AND `active` = '1'";
									$up_estatus_setinactive	= "UPDATE `_race_run_events` SET `edit` = '0' WHERE `eid` = '" . $eid . "' AND `active` = '1'";
									
									// EVENT EXISTS?
									$result_se_event = mysqli_query($mysqli, $se_event);
									$spalte_se_event = mysqli_fetch_assoc($result_se_event);
									$anzahl_se_event = mysqli_num_rows($result_se_event);
								
									// SEARCH FOR EVENTS FROM LOGGED IN USER									
									// EVENT FOUND	
									if($anzahl_se_event > 0) {
										$result_se_active = mysqli_query($mysqli, $se_active);
										$anzahl_se_active = mysqli_num_rows($result_se_active);
										
										$result_se_inactive = mysqli_query($mysqli, $se_inactive);
										$anzahl_se_inactive = mysqli_num_rows($result_se_inactive);
																														
										// FETCH EVENT INFORMATION
										// CREATE QUERIES
										$se_event_timee			= "SELECT * FROM `_optio_zmembers` WHERE `eid` = '" . $eid . "'";
										$se_event_zcont			= "SELECT * FROM `_optio_zcontrol` WHERE `eid` = '" . $eid . "'";
										$se_event_zstamp		= "SELECT * FROM `_optio_zstamp` WHERE `eid` = '" . $eid . "'";
										$se_event_bmemb			= "SELECT * FROM `_optio_bmembers` WHERE `eid` = '" . $eid . "'";
										$se_event_racee			= "SELECT * FROM `_optio_tmembers` WHERE `eid` = '" . $eid . "'";
										$se_event_round			= "SELECT * FROM `_main_wptable` WHERE `eid` = '" . $eid . "'";
										$se_event_reslt         = "SELECT * FROM `_main_wpresults` WHERE `eid` = '" . $eid . "'";
										
										// TIMEBUDDIES
										$result_se_timee		= mysqli_query($mysqli, $se_event_timee);
										$anzahl_se_timee 		= mysqli_num_rows($result_se_timee);
										
										//	Zeitkontrolle
										$result_se_zcont		= mysqli_query($mysqli, $se_event_zcont);
										$anzahl_se_zcont 		= mysqli_num_rows($result_se_zcont);
										
										//	Stempelkontrolle
										$result_se_zstamp		= mysqli_query($mysqli, $se_event_zstamp);
										$anzahl_se_zstamp 		= mysqli_num_rows($result_se_zstamp);
										
										//	Bordkartenkontrolle
										$result_se_bmemb		= mysqli_query($mysqli, $se_event_bmemb);
										$anzahl_se_bmemb 		= mysqli_num_rows($result_se_bmemb);
											
										// RACEBUDDIES
										$result_se_racee		= mysqli_query($mysqli, $se_event_racee);
										$anzahl_se_racee 		= mysqli_num_rows($result_se_racee);
										
										// ROUNDS
										$result_se_round		= mysqli_query($mysqli, $se_event_round);
										$anzahl_se_round 		= mysqli_num_rows($result_se_round);
										
										// RESULTS
										$result_se_reslt		= mysqli_query($mysqli, $se_event_reslt);
										$anzahl_se_reslt 		= mysqli_num_rows($result_se_reslt);
																																										
										// EVENT ON STATUS ACTIVE FOUND
										if($anzahl_se_active > 0) {
											// FETCH EVENT ROWS
											$rows_se_event = mysqli_fetch_assoc($result_se_active);
											
											// COUNTS										
											$count_se_round			= $rows_se_event['count_wptable'];
											$count_se_timee			= $rows_se_event['count_zmembers'];
											$count_se_racee			= $rows_se_event['count_tmembers'];
											$count_se_zcont			= $rows_se_event['count_zcontrol'];
											$count_se_zstamp		= $rows_se_event['count_zstamped'];
											$count_se_bmemb			= $rows_se_event['count_boarding'];
																						
											// SHOW INFORMATION AND EDIT BUTTON
											// ROUND INFORMATION
											if($anzahl_se_round > 0) {
												echo	'
														<table width="385px" cellspacing="5px">
															<tr>
																<th colspan="2">Prüfungen</th>
															</tr>
															<tr>
																<th colspan="2"><hr /></th>
															</tr>
															<tr>
																<th colspan="2" align="center">
																	<div id="dialog_rd" class="modal_fix" title="' . $anzahl_se_round . ' Prüfungen" style="color: #FFFFFF; background: transparent; background-color: #A09A8E;">
																		<p>
																			<table width="100%" id="tbl_result" style="border: 1px solid #FFFFFF;">
																				<tr>
														';
														/*
																					<td align="left"><font size="2" color="#FFFFFF"><strong>Typ</strong></font></td>
																					<td align="left"><font size="2" color="#FFFFFF"><strong>Nr.</strong></font></td>
														*/
												echo	'
																					<td align="center"><font size="2" color="#FFFFFF"><strong>#</strong></font></td>
																					<td align="left"><font size="2" color="#FFFFFF"><strong>Typ</strong></font></td>
																					<td align="left"><font size="2" color="#FFFFFF"><strong>Struktur</strong></font></td>
														';
														/*
																					<td align="center">
																						<span class="tooltip_table_def">
																							<font size="2" color="#FFFFFF">
																								<strong>&odash;</strong>
																								<span class="tooltiptext_table_def">Prüfung neutralisieren?</span>
																							</font>
																						</span>
																					</td>
														*/
												echo	'
																					<td align="center"><font size="2" color="#FFFFFF"><strong>Endet</strong></font></td>
																					<td align="left"><font size="2" color="#FFFFFF" style="margin-left: 5px;"><strong>Gesamtergebnis</strong></font></td>
																					<td align="left">
																						<font size="2" color="#FF0000" class="tooltip_secret">
																							Geheim?
																							<span class="tooltiptext_secret">
																								<strong>Geheimprüfung(en) umschalten <span style="color: #FF0000;">&#9888;</span></strong>
																								<p style="font-size: x-small;">
																									<strong><span style="color: #FF0000;"><u>Nur</u> bei Geheimprüfungen möglich!</span></strong> 
																									<br />&#9642; Prüfung wird verborgen / freigegeben
																									<br />&#9642; Alle Teilnehmer können Ergebnisse frühestens 
																									<br /><span style="color: #fff;">&#9642;</span> <strong>mit</strong> Freigabe einsehen
																								</p>
																							</span>
																						</font>
																					</td>
																					<td align="center">&nbsp;</td>
																					<td align="left">
																						<font size="2" color="#FFFFFF" class="tooltip_neutralize">
																							<img alt="Prüfung neutralisieren" id="toggle_switch_neutralize" src="images/lock_disable.png" style="cursor: pointer;"></img>
																							<span class="tooltiptext_neutralize">
																								<strong>Prüfung(en) neutralisieren <span style="color: #FF0000;">&#9888;</span></strong>
																								<p style="font-size: x-small;">
																									<strong><span style="color: #FF0000;">Aktion kann <u>nicht</u> rückgängig gemacht werden!</span></strong> 
																									<br />&#9642; Prüfung wird unverzüglich neutralisiert
																									<br />&#9642; Alle Zeitnehmer werden sofort ausgeloggt
																									<br />&#9642; Alle Zeitnehmer werden hierfür gesperrt
																									<br />&#9642; Alle Teilnehmer werden auf rot geschaltet
																								</p>
																							</span>	
																						</font>
																					</td>
																				</tr>																		
														';
												
												// FETCH RELEVANT RESULTS
												while($datensatz_round = mysqli_fetch_assoc($result_se_round)) {
													// REWRITE ROW VALUES FOR OUTPUT
													// STATUS
												    if($datensatz_round['suspend'] == 1) {
														$checked = "checked = 'checked'";
												        $setvalue = 0;
												        $geheimval = 0;
														$image = "cross";
														$contenteditable = "false";
														$disabled = "disabled='disabled'";
														$d_border = "#C0C0C0";
														$content_color = "color: #fff;";
														$content_bgcolor = "background-color: #ff0000;";
												    } elseif($datensatz_round['suspend'] == 0) {
												        $checked = "";
														$setvalue = 1;
														$geheimval = 1;
														$image = "tick";
														$contenteditable = "true";
														$disabled = "";
														$d_border = "#FFD700";
														$content_color = "color: #8e6516;";
														$content_bgcolor = "background-color: transparent;";
												    }
													
													//	Prüfe, ob freie Slots zum Setzen von Geheimprüfungen vorhanden
													if($datensatz_round['secret'] == 1 AND $datensatz_round['toggle_secret'] == 1) {
														$checked_scrt = "checked = 'checked'";
												        $setvalue_scrt = 0;
														$geheimstatus = "freigeben";
												    } elseif($datensatz_round['secret'] == 0 AND $datensatz_round['toggle_secret'] == 0) {
												        $checked_scrt = "";
												        $setvalue_scrt = 1;
														$geheimstatus = "verbergen";
												    }
													
													//	Nur bereits als geheim festgelegte Prüfungen können umgeschaltet werden
													if($datensatz_round['secret'] == 1) {
														$disabled_scrt = "";
													} elseif($datensatz_round['secret'] == 0) {
														$disabled_scrt = "disabled='disabled'";
													}
													
													
													// TYPE
													if($datensatz_round['z_entry'] == 1) {
														$round_has_type = "Spr.";
														$round_has_build = "Nur Fahrtzeit";
												    } elseif($datensatz_round['z_entry'] == 0) {
														if($datensatz_round['rid_attr'] == 0) {
															$round_has_type = "Reg.";
															$round_has_build = "Start &rarr; Ziel";
														} elseif($datensatz_round['rid_attr'] > 0) {
															$round_has_type = "Reg.";
															$round_has_build = "Start &rarr; " . $datensatz_round['rid_attr'] . "x ZZ &rarr; Ziel";
														}
												    }
													
													// CONVERT ROUND END
													$end = date("H:i", $datensatz_round['finished']);
													
													// CHECK FOR ANY TMEMBERS (TO ADD WAITING PERIOD)
													$select_check_tmembers = "SELECT * FROM _optio_tmembers WHERE `eid` = '" . $eid . "'";
													$result_check_tmembers = mysqli_query($mysqli, $select_check_tmembers);
													$numrow_check_tmembers = mysqli_num_rows($result_check_tmembers);
													
													// CHECK WAITING PERIOD
													$select_waiting = "SELECT * FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `active` = '1'";
													$result_waiting = mysqli_query($mysqli, $select_waiting);
													$numrow_waiting = mysqli_num_rows($result_waiting);
													
													if($numrow_waiting > 0) {
														$getrow_waiting = mysqli_fetch_assoc($result_waiting);
														$waiting_period = $getrow_waiting['waiting_period'];
													} else {
														$waiting_period = 0;
													}
												
													if($numrow_check_tmembers > 0) {
														// MULTIPLY 60 SECONDS PER TMEMBER
														$numrow_check_tmembers = (60 * $numrow_check_tmembers);
														
														// ADD WAITING PERIOD UP
														$waiting_period = $waiting_period + $numrow_check_tmembers;
														
														// FORCE UTC TIMEZONE BECAUSE OF +1 HOUR GMT
														date_default_timezone_set("UTC");
														$waiting_period = date("H:i", $waiting_period);
														
														// FINALIZE SPAN
														$wperiod = "(+ " . $waiting_period . ")";
													} else {
														$wperiod = "";
													}
													
													// DISABLE EXPORT IF ROUND IS PENDING
													$now = time();
													
													if($now < $datensatz_round['finished']) {
														$eval_export_disabled = "disabled='disabled'";
														$eval_export_plholder = "Kein Prüfungsende";
													} elseif($now >= $datensatz_round['finished']) {
														$eval_export_disabled = "";
														$eval_export_plholder = "Exportieren als";
													}
													
													echo					'
																				<tr id="is_neutralized_' . $datensatz_round['id'] . '" style="' . $content_bgcolor . '">
																					<td align="left">
																						<table width="100%" cellspacing="0">
																							<tr>
																								<td align="center"><font size="2" style="' . $content_color . '">' . $datensatz_round['rid_type'] . $datensatz_round['rid'] . '</font></td>
																							</tr>
																						</table>
																			';
																				/*
																							<tr>
																								<td align="left"><font size="2" color="#8E6516">'.$datensatz_round['rid_type'].'</font></td>
																							</tr>
																						</table>
																					</td>
																					<td align="left">
																						<table width="35px" cellspacing="0">
																							<tr>
																								<td align="left"><font size="2" color="#8E6516">'.$datensatz_round['rid'].'</font></td>
																							</tr>																				
																							<tr>
																								<font size="2" color="#8E6516"><td align="left" contenteditable="'.$contenteditable.'" class="edit_rd" onBlur="saveToDatabase(this,\'rid\','.$datensatz_round['id'].');" onClick="showEdit(this);" style="border: 1px solid '.$d_border.';" '.$disabled.'>'.$datensatz_round['rid'].'</td></font>
																							</tr>													
																						</table>
																					</td>
																				*/
													echo					'							
																					<td align="left">
																						<table width="100%" cellspacing="0">
																							<tr>
																								<td align="left"><font size="2" style="' . $content_color . '">' . $round_has_type . '</font></td>
																							</tr>
																						</table>
																					</td>
																					<td align="left">
																						<table width="100%" cellspacing="0">
																							<tr>
																								<td align="left"><font size="2" style="' . $content_color . '">' . $round_has_build . '</font></td>
																							</tr>
																						</table>
																					</td>
																			';
																			/*
																					<td align="left">
																						<table width="100%" cellspacing="0">
																							<tr>
																								<td align="center"><img src="images/' . $image . '.png" id="status"></img></td>
																							</tr>
																						</table>
																					</td>
																			*/
													echo					'
																					<td align="left">
																						<table width="100%" cellspacing="0">
																							<tr>
																								<td align="center"><font size="2" style="' . $content_color . '">' . $end . ' Uhr ' . $wperiod . '</font></td>
																							</tr>
																						</table>
																					</td>
																					<td align="left">
																						<table width="100%" cellspacing="5px" cellpadding="0">
																							<tr>
																								<td align="left">
																									<select name="select[]" class="eval_select" ' . $eval_export_disabled . ' style="' . $content_color . ' ' . $content_bgcolor . '">
																										<option selected disabled>' . $eval_export_plholder . '</option>
																										<option style="' . $content_color . ' ' . $content_bgcolor . '" value="' . $datensatz_round['rid'] . '">ODS</option>
																										<option style="' . $content_color . ' ' . $content_bgcolor . '" value="' . $datensatz_round['rid'] . '">XLS</option>
																										<option style="' . $content_color . ' ' . $content_bgcolor . '" value="' . $datensatz_round['rid'] . '">XLSX</option>
																										<option style="' . $content_color . ' ' . $content_bgcolor . '" value="' . $datensatz_round['rid'] . '">HTML</option>
																										<option disabled>CSV [ zukünftig ]</option>
																										<option disabled>PDF [ zukünftig ]</option>
																									</select>
																								</td>
																							</tr>
																						</table>
																					</td>
																					<td>																								
																						<label class="control control-radio">
																							<input type="checkbox" name="geheim[]" id="geheimXP_' . $datensatz_round['id'] . '" value="' . $setvalue_scrt . '" ' . $checked_scrt . ' ' . $disabled_scrt . ' onclick="saveToDatabaseGeheim(' . $setvalue_scrt . ', \'toggle_secret\', ' . $datensatz_round['id'] . ', ' . $eid . ', ' . $datensatz_round['rid'] . ');" />
																							<div class="control_indicator tooltip_secret" style="margin-top: -12.5px; margin-left: 20px;">
																								<span class="tooltiptext_secret">
																									<strong>Geheimstatus von ' . $datensatz_round['rid_type'] . $datensatz_round['rid'] . ' <span style="color: #FF0000;">&#9888;</span></strong>
																									<p style="font-size: x-small;">
																										<strong>Geheimprüfung ' . $datensatz_round['rid_type'] . $datensatz_round['rid'] . ' <span id="secret_state_' . $datensatz_round['id'] . '">' . $geheimstatus . '</span></strong>
																									</p>
																								</span>	
																							</div>
																						</label>
																					</td>
																					<td align="center">&nbsp;</td>
																					<td align="left">
																						<table width="100%" cellspacing="0">
																							<tr>
																								<td class="toggle_neutralize">																								
																									<label class="control control-radio">
																										<input type="checkbox" name="neutralize[]" id="checkboxOneInput_' . $datensatz_round['id'] . '" value="' . $setvalue . '" ' . $checked . ' ' . $disabled . ' onclick="saveToDatabaseCBRD(' . $setvalue . ', \'suspend\', ' . $datensatz_round['id'] . ', ' . $eid . ', ' . $datensatz_round['rid'] . ');" />
																										<div class="control_indicator tooltip_neutralize" style="margin-top: -12.5px; margin-left: 10.5px;">
																											<span class="tooltiptext_neutralize">
																												<strong>' . $datensatz_round['rid_type'] . $datensatz_round['rid'] . ' neutralisieren <span style="color: #FF0000;">&#9888;</span></strong>
																												<p style="font-size: x-small;">
																													<strong><span style="color: #FF0000;">Aktion kann <u>nicht</u> rückgängig gemacht werden!</span></strong> 
																													<br />&#9642; Prüfung wird unverzüglich neutralisiert
																													<br />&#9642; Alle Zeitnehmer werden sofort ausgeloggt
																													<br />&#9642; Alle Zeitnehmer werden hierfür gesperrt
																													<br />&#9642; Alle Teilnehmer werden auf rot geschaltet
																												</p>
																											</span>	
																										</div>
																									</label>
																								</td>
																							</tr>
																						</table>
																					</td>
																				</tr>
																			';
												}
												// WIPE RESULTS
												mysqli_free_result($result_se_round);
												
												// CHECK IF TOTAL RESULT MAKING IS POSSIBLE
												$select_totalres = "SELECT `eid`, MAX(`finished`) AS `maxedfin` FROM `_main_wptable` WHERE `eid` = '" . $eid . "'";
												$result_totalres = mysqli_query($mysqli, $select_totalres);
												$numrow_totalres = mysqli_num_rows($result_totalres);
												
												if($numrow_totalres > 0) {
													$getrow_totalres = mysqli_fetch_assoc($result_totalres);
													$maxed_timestamp = $getrow_totalres['maxedfin'];
													
													// COMPARE CURRENT TIMESTAMP WITH HIGHEST STORED TIMESTAMP
													if(time() >= $maxed_timestamp) {
														$totalres_disabled = '';
													} elseif(time() < $maxed_timestamp) {
														$totalres_disabled = 'disabled="disabled"';
													}
												}
												
												echo						'
																			</table>
																		</p>
																		<p>&nbsp;</p>
																		<p>
																			<table width="100%" cellspacing="5px" style="border: 1px solid #FFFFFF;">
																				<tr>
																					<td align="left">
																						<table width="100%" cellspacing="0">
																							<tr>
																								<td align="left">
																									<font size="2" color="#8E6516">Gesamtauswertung erstellen</font>
																								</td>
																							</tr>
																						</table>
																					</td>
																					<td align="right">
																						<table width="100%" cellspacing="0">
																							<tr>
																								<td align="right">
																									<select name="awges_select[]" class="awges_select" ' . $totalres_disabled . '>
																										<option selected disabled>' . $eval_export_plholder . '</option>
																										<option value="' . $datensatz_round['rid'] . '">ODS</option>
																										<option value="' . $datensatz_round['rid'] . '">XLS</option>
																										<option value="' . $datensatz_round['rid'] . '">XLSX</option>
																										<option value="' . $datensatz_round['rid'] . '">HTML</option>
																										<option value="' . $datensatz_round['rid'] . '" disabled>CSV [ zukünftig ]</option>
																										<option value="' . $datensatz_round['rid'] . '" disabled>PDF [ zukünftig ]</option>
																									</select>
																								</td>
																							</tr>
																						</table>
																					</td>
																				</tr>
																			</table>
																		</>
																	</div>
																	<table width="100%" cellspacing="0">
																		<tr>
																			<td align="center" style="font-weight: bold; font-style: bold; font-size: small; border: 1px solid #FFFFFF;">
																				<a href="#" id="opener_rd" style="color: #FFFFFF;">' . $anzahl_se_round . ' Prüfungen anzeigen</a>
																			</td>
																		</tr>
																	</table>
																</th>
															</tr>
														</table>
														';
											} else {
												echo	'
														<table width="385px" cellspacing="5px">
															<tr>
																<th colspan="2">Prüfungen</th>
															</tr>
															<tr>
																<th colspan="2"><hr /></th>
															</tr>
															<tr>
																<th colspan="2" style="border: 1px solid #FFFFFF;" align="center"><font size="2" color="#FFFFFF">Aktuell liegen keine Prüfungen für diese Veranstaltung vor. Bearbeiten Sie die Veranstaltung, um Prüfungen hinzufügen.</font></th>
															</tr>
														</table>
														';
											}
											
											// ROUNDS REQUIRED FOR SHOWING FOLLOWING INFORMATION
											if($anzahl_se_round > 0) {
												// TIMEBUDDY INFORMATION
												if($anzahl_se_timee > 0) {
													echo	'
															<table width="385px" cellspacing="5px">
																<tr>
																	<th colspan="2">Zeitnahme</th>
																</tr>
																<tr>
																	<th colspan="2"><hr /></th>
																</tr>
																<tr>
																	<th colspan="2" align="center">
																		<div id="dialog_mz" class="modal_fix" title="' . $anzahl_se_timee . ' Zeitnehmer" style="color: #8E6516; background: transparent; background-color: #A09A8E;">
																			<p>
																				<table width="100%" cellspacing="5px" style="border: 1px solid #FFFFFF;">
																					<tr>
																						<td align="left"><font size="2"><strong>Kennung</strong></font></td>
																						<td align="left"><font size="2"><strong>Kennwort</strong></font></td>
																						<td align="center"><font size="2"><strong>Prüfung</strong></font></td>
																						<td align="center"><font size="2"><strong>Position(en)</strong></font></td>
																						<td align="left"><font size="2"><strong>Alias</font></td>
																						<td align="center"><font size="2"><strong>Login?</strong></font></td>
																					</tr>																				
															';
													
													// FETCH RELEVANT RESULTS
													while($datensatz_timee = mysqli_fetch_assoc($result_se_timee)) {
														// PREPARE FETCHING POSITIONS FOR ZMEMBERS
														$select_zmember = "SELECT `id`, `zid`, `pos` FROM `_optio_zpositions` WHERE `zid` = '" . $datensatz_timee['id'] . "'";
														$result_zmember = mysqli_query($mysqli, $select_zmember);
														$numrow_zmember = mysqli_num_rows($result_zmember);
														
														// PREPARE DATAFIELD (PSEUDO-ARRAY) FOR STORING POSITIONS
														$datafd_zmember = "";
														
														//	Prüfe auf Login-Status
														if($datensatz_timee['active'] == 1 OR $datensatz_timee['logintime'] > 0) {
															$is_logged = '<a href="#" class="logout" id="mz_' . $datensatz_timee['id'] . '"><font size="2" style="color: #00FF00; cursor: pointer;">Eingeloggt</font></a>';
														} elseif($datensatz_timee['active'] == 0 OR $datensatz_timee['logintime'] == 0) {
															$is_logged = '<font size="2" color="#FF0000">Ausgeloggt</font>';
														}
														
														echo	'
																					<tr>
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="left"><font size="2" color="#8E6516">' . utf8_encode($datensatz_timee['uname']) . '</font></td>
																								</tr>
																							</table>
																						</td>
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="left"><font size="2" color="#8E6516">' . utf8_encode($datensatz_timee['upass']) . '</font></td>
																								</tr>
																							</table>
																						</td>
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="center"><font size="2" color="#8E6516">' . utf8_encode($datensatz_timee['rid_type']) . utf8_encode($datensatz_timee['rid']) . '</font></td>
																								</tr>
																							</table>
    																					</td>
																';
														
														// GET EVERY POSITION AS STACK FOR EVERY ZMEMBER
														if($numrow_zmember > 0) {
															while($getrow_zmember = mysqli_fetch_assoc($result_zmember)) {
																$datafd_zmember .= $getrow_zmember['pos'] . " ";
															}
														} elseif($numrow_zmember == 0) {
															$datafd_zmember =   '
															                    <span style="color: #FF0000;">
															                        <font size="2" color="#FF0000" class="tooltip_secret">
															                            Fehler
	                                                                                    <span class="tooltiptext_secret">
		                                                                                    <strong>Zeitnehmer fehlerhaft <span style="color: #FF0000;">&#9888;</span></strong>
		                                                                                    <p style="font-size: x-small; text-align: justify;">
		                                                                                	    <strong>
		                                                                                	        <span style="color: #FF0000;">
		                                                                                	           Zeitnehmer besitzt keine korrekt konfigurierten Positionen. Klicken Sie auf "<img src="images/gears.png" title="Reparieren">", um zu versuchen die gel&ouml;schten Positionen wiederherzustellen!
		                                                                                	        </span>
                                                                                	            </strong> 
	                                                                                        </p>
	                                                                                    </span>
                                                                                    </font>	
                                                                                    <img style="cursor: pointer;" src="images/gears.png" title="Reparieren" class="repair" id="' . $datensatz_timee['id'] . '"></img>
															                    </span>
															                    ';
														}
														
														echo	'
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="center"><font size="2" color="#8E6516" id="append_repair_zn_' . $datensatz_timee['id'] . '">' . utf8_encode($datafd_zmember) . '</font></td>
																								</tr>
																							</table>
																						</td>																						
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<font size="2" color="#FFFFFF"><td align="left" contenteditable="true" class="mz" onBlur="saveToDatabaseMZ(this,\'opt_whois\',' . $datensatz_timee['id'] . ');" onClick="showEdit(this);" style="border: 1px solid #FFD700;">' . $datensatz_timee['opt_whois'] . '</td></font>
																								</tr>
																							</table>
																						</td>
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="center" id="mz_' . $datensatz_timee['id'] . '">' . $is_logged . '</td>
																								</tr>
																							</table>
    																					</td>
																					</tr>
																';
													}
													// WIPE RESULTS
													mysqli_free_result($result_se_timee);
													echo	'
																				</table>
																			</p>
																		</div>
																		<table width="100%" cellspacing="0">
																			<tr>
																				<td align="center" style="font-weight: bold; font-style: bold; font-size: small; border: 1px solid #FFFFFF;">
																					<a href="#" id="opener_mz" style="color: #FFFFFF;">' . $anzahl_se_timee . ' Zeitnehmer anzeigen</a>
																				</td>
																			</tr>
																		</table>
																	</th>
																</tr>
															</table>
															';
												} else {
													echo	'
															<table width="385px" cellspacing="5px">
																<tr>
																	<th colspan="2">Zeitnahme</th>
																</tr>
																<tr>
																	<th colspan="2"><hr /></th>
																</tr>
																<tr>
																	<th colspan="2" style="border: 1px solid #FFFFFF;" align="center"><font size="2" color="#FFFFFF">Aktuell liegen keine Zeitnehmer für diese Veranstaltung vor. Bearbeiten Sie die Veranstaltung, um Zeitnehmer hinzufügen.</font></th>
																</tr>
															</table>
														';
												}
												
												//	TIMECONTROL
												if($anzahl_se_zcont > 0) {
													echo	'
															<table width="385px" cellspacing="5px">
																<tr>
																	<th colspan="2">Zeitkontrolle</th>
																</tr>
																<tr>
																	<th colspan="2"><hr /></th>
																</tr>
																<tr>
																	<th colspan="2" align="center">
																		<div id="dialog_tc" class="modal_fix" title="' . $anzahl_se_zcont . ' Zeitkontrolle" style="color: #8E6516; background: transparent; background-color: #A09A8E;">
																			<p>
																				<table width="100%" cellspacing="5px" style="border: 1px solid #FFFFFF;">
																					<tr>
																						<td align="left"><font size="2"><strong>Kennung</strong></font></td>
																						<td align="left"><font size="2"><strong>Kennwort</strong></font></td>
																						<td align="center"><font size="2"><strong>Veranstaltungstag</strong></font></td>
																						<td align="left"><font size="2"><strong>Alias</font></td>
																						<td align="center"><font size="2"><strong>Login?</strong></font></td>
																					</tr>																				
															';
													
													// FETCH RELEVANT RESULTS
													while($datensatz_zcont = mysqli_fetch_assoc($result_se_zcont)) {
														//	Prüfe auf Login-Status
														if($datensatz_zcont['active'] == 1 OR $datensatz_zcont['logintime'] > 0) {
															$is_logged_zcont = '<a href="#" class="logout" id="zk_' . $datensatz_zcont['id'] . '"><font size="2" color="#00FF00">Eingeloggt</font></a>';
														} elseif($datensatz_zcont['active'] == 0 OR $datensatz_zcont['logintime'] == 0) {
															$is_logged_zcont = '<font size="2" color="#FF0000">Ausgeloggt</font>';
														}
														
														//	Konvertiere Datum zu dd.mm.yyyy
														$explode = explode("-", $datensatz_zcont['eventdate']);
														$eventdate = $explode[2] . "." . $explode[1] . "." . $explode[0];
														
														echo	'
																					<tr>
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="left"><font size="2" color="#8E6516">' . utf8_encode($datensatz_zcont['uname']) . '</font></td>
																								</tr>
																							</table>
																						</td>
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="left"><font size="2" color="#8E6516">' . utf8_encode($datensatz_zcont['upass']) . '</font></td>
																								</tr>
																							</table>
																						</td>
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="center"><font size="2" color="#8E6516">' . utf8_encode($eventdate) . '</font></td>
																								</tr>
																							</table>
    																					</td>																					
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="left"><font size="2" color="#FFFFFF">' . $datensatz_zcont['opt_whois'] . '</font></td>
																								</tr>
																							</table>
																						</td>
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="center" id="zk_' . $datensatz_zcont['id'] . '">' . $is_logged_zcont . '</td>
																								</tr>
																							</table>
    																					</td>
																					</tr>
																';
													}
													// WIPE RESULTS
													mysqli_free_result($result_se_zcont);
													echo	'
																				</table>
																			</p>
																		</div>
																		<table width="100%" cellspacing="0">
																			<tr>
																				<td align="center" style="font-weight: bold; font-style: bold; font-size: small; border: 1px solid #FFFFFF;">
																					<a href="#" id="opener_tc" style="color: #FFFFFF;">' . $anzahl_se_zcont . ' Zeitkontrollen anzeigen</a>
																				</td>
																			</tr>
																		</table>
																	</th>
																</tr>
															</table>
															';
												} else {
													echo	'
															<table width="385px" cellspacing="5px">
																<tr>
																	<th colspan="2">Zeitkontrolle</th>
																</tr>
																<tr>
																	<th colspan="2"><hr /></th>
																</tr>
																<tr>
																	<th colspan="2" style="border: 1px solid #FFFFFF;" align="center"><font size="2" color="#FFFFFF">Aktuell liegen keine Zeitkontrollen für diese Veranstaltung vor. Bearbeiten Sie die Veranstaltung, um diese hinzufügen.</font></th>
																</tr>
															</table>
														';
												}
												
												//	STAMPCONTROL
												if($anzahl_se_zstamp > 0) {
													echo	'
															<table width="385px" cellspacing="5px">
																<tr>
																	<th colspan="2">Stempelkontrolle</th>
																</tr>
																<tr>
																	<th colspan="2"><hr /></th>
																</tr>
																<tr>
																	<th colspan="2" align="center">
																		<div id="dialog_sc" class="modal_fix" title="' . $anzahl_se_zstamp . ' Stempelkontrolle" style="color: #8E6516; background: transparent; background-color: #A09A8E;">
																			<p>
																				<table width="100%" cellspacing="5px" style="border: 1px solid #FFFFFF;">
																					<tr>
																						<td align="left"><font size="2"><strong>Kennung</strong></font></td>
																						<td align="left"><font size="2"><strong>Kennwort</strong></font></td>
																						<td align="center"><font size="2"><strong>Veranstaltungstag</strong></font></td>
																						<td align="left"><font size="2"><strong>Alias</font></td>
																						<td align="center"><font size="2"><strong>Login?</strong></font></td>
																					</tr>																				
															';
													
													// FETCH RELEVANT RESULTS
													while($datensatz_zstamp = mysqli_fetch_assoc($result_se_zstamp)) {
														//	Prüfe auf Login-Status
														if($datensatz_zstamp['active'] == 1 OR $datensatz_zstamp['logintime'] > 0) {
															$is_logged_zstamp = '<a href="#" class="logout" id="zs_' . $datensatz_zstamp['id'] . '"><font size="2" color="#00FF00">Eingeloggt</font></a>';
														} elseif($datensatz_zstamp['active'] == 0 OR $datensatz_zstamp['logintime'] == 0) {
															$is_logged_zstamp = '<font size="2" color="#FF0000">Ausgeloggt</font>';
														}
														
														//	Konvertiere Datum zu dd.mm.yyyy
														$explode = explode("-", $datensatz_zstamp['eventdate']);
														$eventdate = $explode[2] . "." . $explode[1] . "." . $explode[0];
																												
														echo	'
																					<tr>
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="left"><font size="2" color="#8E6516">' . utf8_encode($datensatz_zstamp['uname']) . '</font></td>
																								</tr>
																							</table>
																						</td>
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="left"><font size="2" color="#8E6516">' . utf8_encode($datensatz_zstamp['upass']) . '</font></td>
																								</tr>
																							</table>
																						</td>
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="center"><font size="2" color="#8E6516">' . utf8_encode($eventdate) . '</font></td>
																								</tr>
																							</table>
    																					</td>																					
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="left"><font size="2" color="#FFFFFF">' . $datensatz_zstamp['opt_whois'] . '</font></td>
																								</tr>
																							</table>
																						</td>
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="center" id="zs_' . $datensatz_zstamp['id'] . '">' . $is_logged_zstamp . '</td>
																								</tr>
																							</table>
    																					</td>
																					</tr>
																';
													}
													// WIPE RESULTS
													mysqli_free_result($result_se_zstamp);
													echo	'
																				</table>
																			</p>
																		</div>
																		<table width="100%" cellspacing="0">
																			<tr>
																				<td align="center" style="font-weight: bold; font-style: bold; font-size: small; border: 1px solid #FFFFFF;">
																					<a href="#" id="opener_sc" style="color: #FFFFFF;">' . $anzahl_se_zstamp . ' Stempelkontrollen anzeigen</a>
																				</td>
																			</tr>
																		</table>
																	</th>
																</tr>
															</table>
															';
												} else {
													echo	'
															<table width="385px" cellspacing="5px">
																<tr>
																	<th colspan="2">Stempelkontrolle</th>
																</tr>
																<tr>
																	<th colspan="2"><hr /></th>
																</tr>
																<tr>
																	<th colspan="2" style="border: 1px solid #FFFFFF;" align="center"><font size="2" color="#FFFFFF">Aktuell liegen keine Stempelkontrollen für diese Veranstaltung vor. Bearbeiten Sie die Veranstaltung, um diese hinzufügen.</font></th>
																</tr>
															</table>
														';
												}
												
												//	BOADINGCONTROl
												if($anzahl_se_bmemb > 0) {
													echo	'
															<table width="385px" cellspacing="5px">
																<tr>
																	<th colspan="2">Bordkartenkontrolle</th>
																</tr>
																<tr>
																	<th colspan="2"><hr /></th>
																</tr>
																<tr>
																	<th colspan="2" align="center">
																		<div id="dialog_bc" class="modal_fix" title="' . $anzahl_se_bmemb . ' Bordkartenkontrolle" style="color: #8E6516; background: transparent; background-color: #A09A8E;">
																			<p>
																				<table width="100%" cellspacing="5px" style="border: 1px solid #FFFFFF;">
																					<tr>
																						<td align="left"><font size="2"><strong>Kennung</strong></font></td>
																						<td align="left"><font size="2"><strong>Kennwort</strong></font></td>
																						<td align="center"><font size="2"><strong>Veranstaltungstag</strong></font></td>
																						<td align="left"><font size="2"><strong>Alias</font></font></td>
																						<td align="center"><font size="2"><strong>Login?</strong></font></td>
																					</tr>																				
															';
													
													// FETCH RELEVANT RESULTS
													while($datensatz_bmemb = mysqli_fetch_assoc($result_se_bmemb)) {
														//	Prüfe auf Login-Status
														if($datensatz_bmemb['active'] == 1 OR $datensatz_bmemb['logintime'] > 0) {
															$is_logged_bmemb = '<a href="#" class="logout" id="bc_' . $datensatz_bmemb['id'] . '"><font size="2" color="#00FF00">Eingeloggt</font></a>';
														} elseif($datensatz_bmemb['active'] == 0 OR $datensatz_bmemb['logintime'] == 0) {
															$is_logged_bmemb = '<font size="2" color="#FF0000">Ausgeloggt</font>';
														}
														
														//	Konvertiere Datum zu dd.mm.yyyy
														$explode = explode("-", $datensatz_bmemb['eventdate']);
														$eventdate = $explode[2] . "." . $explode[1] . "." . $explode[0];
														
														echo	'
																					<tr>
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="left"><font size="2" color="#8E6516">' . utf8_encode($datensatz_bmemb['uname']) . '</font></td>
																								</tr>
																							</table>
																						</td>
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="left"><font size="2" color="#8E6516">' . utf8_encode($datensatz_bmemb['upass']) . '</font></td>
																								</tr>
																							</table>
																						</td>
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="center"><font size="2" color="#8E6516">' . utf8_encode($eventdate) . '</font></td>
																								</tr>
																							</table>
    																					</td>																					
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="left"><font size="2" color="#FFFFFF">' . $datensatz_bmemb['opt_whois'] . '</font></td>
																								</tr>
																							</table>
																						</td>
																						<td align="left">
																							<table width="100%" cellspacing="0">
																								<tr>
																									<td align="center" id="bc_' . $datensatz_bmemb['id'] . '">' . $is_logged_bmemb . '</td>
																								</tr>
																							</table>
    																					</td>
																					</tr>
																';
													}
													// WIPE RESULTS
													mysqli_free_result($result_se_bmemb);
													echo	'
																				</table>
																			</p>
																		</div>
																		<table width="100%" cellspacing="0">
																			<tr>
																				<td align="center" style="font-weight: bold; font-style: bold; font-size: small; border: 1px solid #FFFFFF;">
																					<a href="#" id="opener_bc" style="color: #FFFFFF;">' . $anzahl_se_bmemb . ' Bordkartenkontrollen anzeigen</a>
																				</td>
																			</tr>
																		</table>
																	</th>
																</tr>
															</table>
															';
												} else {
													echo	'
															<table width="385px" cellspacing="5px">
																<tr>
																	<th colspan="2">Bordkartenkontrolle</th>
																</tr>
																<tr>
																	<th colspan="2"><hr /></th>
																</tr>
																<tr>
																	<th colspan="2" style="border: 1px solid #FFFFFF;" align="center"><font size="2" color="#FFFFFF">Aktuell liegen keine Bordkartenkontrollen für diese Veranstaltung vor. Bearbeiten Sie die Veranstaltung, um diese hinzufügen.</font></th>
																</tr>
															</table>
														';
												}
											}
											
											// RACER INFORMATION
											// ROUNDS REQUIRED FOR SHOWING RACERS
											if($anzahl_se_round > 0 /*AND $anzahl_se_timee > 0*/) {
												if($anzahl_se_racee > 0) {
													echo	'
															<table width="385px" cellspacing="5px">
																<tr>
																	<th colspan="2">Teilnahme</th>
																</tr>
																<tr>
																	<th colspan="2"><hr /></th>
																</tr>
																<tr>
																	<th colspan="2" align="center">
																		<div id="dialog_mt" class="modal_fix" title="' . $anzahl_se_racee . ' Teilnehmer" style="color: #8E6516; background: transparent; background-color: #A09A8E;">
																			<p>
																				<table width="100%" cellspacing="5px" style="border: 1px solid #FFFFFF;">
																					<tr>
																						<td align="center"><font size="2"><strong>Sie befinden sich derzeit auf der Kompaktübersicht</strong></font></td>
																						<td align="center"><a href=\'_showmt.php\' alt=\'Vollständige Teilnehmerliste anzeigen\'><font color="#fff" size="2">[ zur vollständigen Übersicht wechseln ]</font></a></td>
																					</tr>
																				</table>
																			</p>
																			<p>&nbsp;</p>
																			<p>
																				<table width="100%" cellspacing="5px" style="border: 1px solid #FFFFFF;">
																					<tr>
																						<td align="center"><font size="2"><strong>#</strong></font></td>
																						<td align="center"><font size="2"><strong>Kl.</strong></font></td>
																						<td align="left"><font size="2"><strong>Fahrer</strong></font></td>
																						<td align="left"><font size="2"><strong>Beifahrer</strong></font></td>
																						<td align="center"><font size="2"><strong>Kennung</strong></font></td>
																						<td align="center"><font size="2"><strong>Kennwort</strong></font></td>
																						<td align="center"><font size="2"><strong>Login</strong></font></td>
															';
													/*	
																						<td align="center"><img style="margin-left: 7px;" alt="Teilnehmerstatus" src="images/ready.png"></img></td>
													*/
													echo	'
																					</tr>																			
															';
															
													$i = 1;
													
													// FETCH RELEVANT RESULTS
													while($datensatz_racee = mysqli_fetch_assoc($result_se_racee)) {
														// REWRITE ROW VALUES FOR OUTPUT
    												    if($datensatz_racee['ready'] == "") {
    														$checked = "checked = 'checked'";
    												        $setvalue = "no";
															$geheimval = "no";
    														$image = "green";
															// $switch = "rot";
    												    } elseif($datensatz_racee['ready'] == "no") {
    												        $checked = "";
    														$setvalue = "yes";
															$geheimval = "yes";
    														$image = "red";
															// $switch = "bereit";
    												    }
																		
														echo	'
																					<tr>
																						<td align="center"><font size="2" color="#8E6516">' . $datensatz_racee['sid'] . '</font></td>
																						<td align="center"><font size="2" color="#8E6516">' . $datensatz_racee['class'] . '</font></td>
																						<td align="left"><font size="2" color="#8E6516">' . $datensatz_racee['vname_1'] . ' ' . $datensatz_racee['nname_1'] . '</font></td>
																						<td align="left"><font size="2" color="#8E6516">' . $datensatz_racee['vname_2'] . ' ' . $datensatz_racee['nname_2'] . '</font></td>
																						<td align="center"><font size="2" color="#8E6516">' . $datensatz_racee['uname'] . '</font></td>
																						<td align="center"><font size="2" color="#8E6516">' . $datensatz_racee['upass'] . '</font></td>
																						<td align="center"><font size="2" color="#8E6516"><a href="#" class="tn_login" id="' . $datensatz_racee['sid'] . '">Einloggen</a></font></td>
																';
																/*
																						<td align="center style="vertical-align: middle;">
																							<font size="2">																
																								<div class="tooltip_short">
																									<label for="check_state_' . $i . '">
																										<input type="checkbox" name="neutralize[]" id="check_state_' . $i . '" value="' . $setvalue . '" ' . $checked . ' onclick="saveToDatabaseCBMT(this.value, ' . $datensatz_racee['eid'] . ', ' . $datensatz_racee['sid'] . ');" />
																										<img src="images/'.$image.'.png" id="status_' . $i . '">
																									</label>
																									<span class="tooltiptext_short">' . $switch . '?</span>
																								</div>
																
																							</font>
																						</td>
																*/
														echo	'
																					</tr>
																';
																
														$i++;
													}
													// WIPE RESULTS
													mysqli_free_result($result_se_racee);
													echo	'
																				</table>
																				</p>
																			</div>
																			<table width="100%" cellspacing="0">
																				<tr>
																					<td align="center" style="font-weight: bold; font-style: bold; font-size: small; border: 1px solid #FFFFFF;">
																						<a href="#" id="opener_mt" style="color: #FFFFFF;">' . $anzahl_se_racee . ' Teilnehmer anzeigen</a>
																					</td>
																				</tr>
																			</table>
																		</th>
																	</tr>
																</table>
															';
												} else {
													echo	'
															<table width="385px" cellspacing="5px">
																<tr>
																	<th colspan="2">Teilnahme</th>
																</tr>
																<tr>
																	<th colspan="2"><hr /></th>
																</tr>
																<tr>
																	<th colspan="2" style="border: 1px solid #FFFFFF;" align="center"><font size="2" color="#FFFFFF">Aktuell liegen keine Teilnehmer für diese Veranstaltung vor. Bearbeiten Sie die Veranstaltung, um Teilnehmer hinzufügen.</font></th>
																</tr>
															</table>
															';
												}
											}
											
											// RESULT INFORMATION
											// ONLY SHOW, WHEN RESULTS AVAILABLE
											if($anzahl_se_reslt > 0 /*AND $anzahl_se_timee > 0*/) {
												
												if($anzahl_se_racee > 0) {
													echo	'
																<table width="385px" cellspacing="5px">
																	<tr>
																		<th colspan="2">Zeiten</th>
																	</tr>
																	<tr>
																		<th colspan="2"><hr /></th>
																	</tr>
																	<tr>
																		<th colspan="2" align="center">
																			<div id="dialog_res" class="modal_fix" title="' . $anzahl_se_reslt . ' Zeiten" style="color: #8E6516; background: transparent; background-color: #A09A8E;">
																				<p>
																					<table width="100%" cellspacing="0">
																						<tr>
																							<td align="left" id="titles">Bitte Prüfung auswählen</td>
																							<td align="right">
																								<select name="export_type" id="export_type" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 150px; height: 28px;">
																									<option selected="selected" disabled="disabled">Exportieren als</option>
																									<option id="xlsx_export">XLSX</option>
																									<option id="xls_export">XLS</option>
																									<option id="ods_export">ODS</option>
																									<option id="csv_export">CSV</option>
																									<option id="pdf_export" disabled>PDF [ zukünftig ]</option>
																								</select>
																								<button type="button" id="reload" style="border: 1px solid #dcdcdc; height: 28px; width: 28px; vertical-align: middle; margin-left: 1px; margin-top: -3px;"><img src="images/reload.png"></button>
																								<select name="rd_fetch" id="rd_fetch" class="input-block-level" placeholder="Prüfung wählen" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 150px; height: 28px;" required="required">
																									<option value="0" selected="selected" disabled="disabled">Prüfung wählen</option>
															';
															
													// FETCH EVERY rid
													$select_main_wpt = "SELECT * FROM _main_wptable WHERE `eid` = '" . $eid . "'";
													$result_main_wpt = mysqli_query($mysqli, $select_main_wpt);
													$numrow_main_wpt = mysqli_num_rows($result_main_wpt);
													
													// CHECK GRAMMAR ON PLURAL
													if($numrow_main_wpt > 1) {
														$grammar_var = "Prüfungen";
													} elseif($numrow_main_wpt == 1 OR $numrow_main_wpt == 0) {
														$grammar_var = "Prüfung";
													}
													
													echo "Anzahl: " . $numrow_main_wpt;
													
													// FETCH rid_type AND INFOR FROM DATABASE
													// rid_type IN _RACE_RUN_EVENTS FOUND
													while($spalte_main_wpt = mysqli_fetch_assoc($result_main_wpt)) {
														if($spalte_main_wpt["z_entry"] == 1) {
																	$what = " &mdash; Sprint";
																} else {
																	$what = "";
																}
														
														echo	'					
																						<option value="' . $spalte_main_wpt['rid'] . '">' . $spalte_main_wpt['rid_type'] . $spalte_main_wpt['rid'] . $what . '</option>
																';
													}
													
													echo	'
																								</select>
																							</td>
																						</tr>
																					</table>
																				</p>
																				<p><br /></p>
																				<p id="fetch_rd"></p>
																			</div>
																			<table width="100%" cellspacing="0">
																				<tr>
																					<td align="center" style="font-weight: bold; font-style: bold; font-size: small; border: 1px solid #FFFFFF;">
																						<a href="#" id="opener_res" style="color: #FFFFFF;">' . $anzahl_se_reslt . ' Zeiten in ' . $numrow_main_wpt . ' ' . $grammar_var . ' anzeigen</a>
																					</td>
																				</tr>
																			</table>
																		</th>
																	</tr>
																</table>
															';
												} else {
													echo	'
															<table width="385px" cellspacing="5px">
																<tr>
																	<th colspan="2">Zeiten</th>
																</tr>
																<tr>
																	<th colspan="2"><hr /></th>
																</tr>
																<tr>
																	<th colspan="2" style="border: 1px solid #FFFFFF;" align="center"><font size="2" color="#FFFFFF">Aktuell liegen keine Zeiten für diese Veranstaltung vor.</font></th>
																</tr>
															</table>
															';
												}
											}
											
											
											// EDIT BUTTON
											echo	'
														<table width="385px" cellspacing="5px">	
														<tr>
															<th colspan="2">&nbsp;</th>
														</tr>
														<tr>
															<th style="border: 1px solid #FFFFFF;" colspan="2">
																<table width="100%" cellspacing="5px">
																	<tr>
																		<td align="left">Veranstaltung bearbeiten</td>
																		<td align="right"><input type="submit" name="edit_event" value="Bearbeiten" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" /></td>
																	</tr>
																</table>
															</th>
														</tr>
													</table>
													';
											// EDIT BUTTON CLICKED
											if(isset($_POST['edit_event'])) {
												// SEARCH FOR ACTIVE EVENT FROM LOGGED IN USER
												if($se_active == true) {
													$result_se_active = mysqli_query($mysqli, $se_active);
													$anzahl_se_active = mysqli_num_rows($result_se_active);
													
													// SET EVENT TO INACTIVE AND RELOAD
													mysqli_query($mysqli, $up_estatus_setactive);
													header('Location: /msdn/my_event.php');
													ob_end_flush(); 
												}
											}
										// EVENT ON STATUS INACTIVE FOUND
										} else {
											// FETCH EVENT ROWS
											$rows_se_event = mysqli_fetch_assoc($result_se_inactive);
											
											// COUNTS										
											$count_se_round			= $rows_se_event['count_wptable'];
											$count_se_timee			= $rows_se_event['count_zmembers'];
											$count_se_racee			= $rows_se_event['count_tmembers'];
											$count_se_zcont			= $rows_se_event['count_zcontrol'];
											$count_se_zstamp		= $rows_se_event['count_zstamped'];
											$count_se_bmemb			= $rows_se_event['count_boarding'];
											
											// SHOW INFORMATION
											if($count_se_round > 0) {
												$colspan_rd = "";
												$width_rd = "width='50%'";
											} else {
												$colspan_rd = "colspan='2'";
												$width_rd = "width='100%'";
											}
											
											echo	'												
																<table width="385px" cellspacing="5px">
																	<tr>
																		<th colspan="2">Prüfungen</th>
																	</tr>
																	<tr>
																		<th colspan="2"><hr /></th>
																	</tr>
																	<span>
																	<tr>
																		<th ' . $width_rd . ' style="border: 1px solid #FFFFFF;" align="center" ' . $colspan_rd . '><a href="_addrd.php" target="_self"><font size="2">hinzufügen</font></a></th>
													';
											if($count_se_round > 0) {
												echo	'							
																		<th ' . $width_rd . ' style="border: 1px solid #FFFFFF;" align="center" ' . $colspan_rd . '><a href="_delrd.php" target="_self"><font size="2">entfernen</font></a></th>
														';
											}
											
											echo	'
																	</tr>
																	</span>
																</table>
													';
											// IF ROUNDS ARE SET SHOW TIMEBUDDIES AND RELATED
											if($anzahl_se_round > 0) {
												// SHOW INFORMATION OF TIMEBUDDIES
												if($count_se_timee > 0) {
													$colspan_mz = "";
													$width_mz = "width='50%'";
												} else {
													$colspan_mz = "colspan='2'";
													$width_mz = "width='100%'";
												}
												
												echo	'												
																<table width="385px" cellspacing="5px">
																	<tr>
																		<th colspan="2">Zeitnahme</th>
																	</tr>
																	<tr>
																		<th colspan="2"><hr /></th>
																	</tr>
																	<span>
																	<tr>
																		<th ' . $width_mz . ' style="border: 1px solid #FFFFFF;" align="center" ' . $colspan_mz . '><a href="_addmz.php" target="_self"><font size="2">hinzufügen</font></a></th>
														';
												if($count_se_timee > 0) {
													echo	'							
																		<th ' . $width_mz . ' style="border: 1px solid #FFFFFF;" align="center" ' . $colspan_mz . '><a href="_delmz.php" target="_self"><font size="2">entfernen</font></a></th>
															';
												}
												
												echo	'
																	</tr>
																	</span>
																</table>
														';
														
												// SHOW INFORMATION OF TIMECONTROL
												if($count_se_zcont > 0) {
													$colspan_tc = "";
													$width_tc = "width='50%'";
												} else {
													$colspan_tc = "colspan='2'";
													$width_tc = "width='100%'";
												}
												
												echo	'												
																<table width="385px" cellspacing="5px">
																	<tr>
																		<th colspan="2">Zeitkontrolle</th>
																	</tr>
																	<tr>
																		<th colspan="2"><hr /></th>
																	</tr>
																	<span>
																	<tr>
																		<th ' . $width_tc . ' style="border: 1px solid #FFFFFF;" align="center" ' . $colspan_tc . '><a href="_addzk.php" target="_self"><font size="2">hinzufügen</font></a></th>
														';
												if($count_se_zcont > 0) {
													echo	'							
																		<th ' . $width_tc . ' style="border: 1px solid #FFFFFF;" align="center" ' . $colspan_tc . '><a href="_delzk.php" target="_self"><font size="2">entfernen</font></a></th>
															';
												}
												
												echo	'
																	</tr>
																	</span>
																</table>
														';
														
												// SHOW INFORMATION OF STAMPCONTROL
												if($count_se_zstamp > 0) {
													$colspan_sc = "";
													$width_sc = "width='50%'";
												} else {
													$colspan_sc = "colspan='2'";
													$width_sc = "width='100%'";
												}
												
												echo	'												
																<table width="385px" cellspacing="5px">
																	<tr>
																		<th colspan="2">Stempelkontrolle</th>
																	</tr>
																	<tr>
																		<th colspan="2"><hr /></th>
																	</tr>
																	<span>
																	<tr>
																		<th ' . $width_sc . ' style="border: 1px solid #FFFFFF;" align="center" ' . $colspan_sc . '><a href="_addzs.php" target="_self"><font size="2">hinzufügen</font></a></th>
														';
												if($count_se_zstamp > 0) {
													echo	'							
																		<th ' . $width_sc . ' style="border: 1px solid #FFFFFF;" align="center" ' . $colspan_tc . '><a href="_delzs.php" target="_self"><font size="2">entfernen</font></a></th>
															';
												}
												
												echo	'
																	</tr>
																	</span>
																</table>
														';
														
												// SHOW INFORMATION OF BOARDINGCONTROL
												if($count_se_bmemb > 0) {
													$colspan_bc = "";
													$width_bc = "width='50%'";
												} else {
													$colspan_bc = "colspan='2'";
													$width_bc = "width='100%'";
												}
												
												echo	'												
																<table width="385px" cellspacing="5px">
																	<tr>
																		<th colspan="2">Bordkartenkontrolle</th>
																	</tr>
																	<tr>
																		<th colspan="2"><hr /></th>
																	</tr>
																	<span>
																	<tr>
																		<th ' . $width_bc . ' style="border: 1px solid #FFFFFF;" align="center" ' . $colspan_bc . '><a href="_addbc.php" target="_self"><font size="2">hinzufügen</font></a></th>
														';
												if($count_se_bmemb > 0) {
													echo	'							
																		<th ' . $width_bc . ' style="border: 1px solid #FFFFFF;" align="center" ' . $colspan_bc . '><a href="_delbc.php" target="_self"><font size="2">entfernen</font></a></th>
															';
												}
												
												echo	'
																	</tr>
																	</span>
																</table>
														';	
											}
											
											// IF ROUNDS AND TIMEBUDDIES ARE SET SHOW RACERS
											if($anzahl_se_round > 0 AND $anzahl_se_timee > 0) {
												if($count_se_racee < 201) {
													// SHOW INFORMATION
													if($count_se_racee > 0) {
														$colspan_mt = "";
														$width_mt = "width='50%'";
													} else {
														$colspan_mt = "colspan='2'";
														$width_mt = "width='100%'";
													}
													
													echo	'												
																<table width="385px" cellspacing="5px">
																	<tr>
																		<th colspan="2">Teilnehmer</th>
																	</tr>
																	<tr>
																		<th colspan="2"><hr /></th>
																	</tr>
																	<span>
																	<tr>
																		<th ' . $width_mt . ' style="border: 1px solid #FFFFFF;" align="center" ' . $colspan_mt . '><a href="_addmt.php" target="_self"><font size="2">hinzufügen</font></a></th>
															';
													if($count_se_racee > 0) {
														echo	'							
																		<th ' . $width_mt . ' style="border: 1px solid #FFFFFF;" align="center" ' . $colspan_mt . '><a href="_delmt.php" target="_self"><font size="2">entfernen</font></a></th>
																';
													}
													
													echo	'
																	</tr>
																	</span>
																</table>
															';
												} elseif($count_se_racee == 200) {
													echo	'
																	<table width="385px" cellspacing="5px">
																		<tr>
																			<th colspan="2">Teilnahme</th>
																		</tr>
																		<tr>
																			<th colspan="2"><hr /></th>
																		</tr>
																		<tr>
																			<th colspan="2" style="border: 1px solid #FFFFFF;" align="center"><font size="2" color="#FFFFFF">Sie haben das Teilnehmerlimit für diese Veranstaltung erreicht. Bearbeiten Sie die Veranstaltung und löschen sie Teilnehmer, um Teilnehmer hinzufügen zu können.</font></th>
																		</tr>
																	</table>
															';
												}
											}
											
											// SET OWN WP / SP / GP
											/*
											echo	'
													<table width="385px" cellspacing="5px">	
														<tr>
															<th colspan="2">&nbsp;</th>
														</tr>
														<tr>
															<th style="border: 1px solid #FFFFFF;" colspan="2">
																<table width="100%">
																	<tr>
																		<td align="left"><font size="2">Auswerter Position auf ' . $spalte_se_event['rid_type'] . '</font></td>
																		<td align="right">
																		    <select name="auswerter_choice" placeholder="Bitte auswählen" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" required="required"  >
													';
																		        if($spalte_se_event['rid'] == "" OR empty($spalte_se_event["rid"])) {
																		            $disabled_status = "disabled='disabled'";
																		            $auswerter_choice = "Bitte auswählen";
																		        } elseif($spalte_se_event["rid"] != "" OR !empty($spalte_se_event["rid"])) {
																		            $disabled_status = "";
																		            $auswerter_choice = $spalte_se_event["rid"];
																		        }
											echo							    '
                            													<option selected="selected" ' . $disabled_status . '>' . $auswerter_choice . '</option>
                            												    ';
                            													$select_option	= "SELECT * FROM _main_wptable WHERE `eid` = '".$eid."'";
                            													$result_option	= mysqli_query($mysqli, $select_option);
                            													$anzahl_option	= mysqli_num_rows($query_option);
                            																										
                            													while($row = mysqli_fetch_assoc($result_option)) {
                            														echo "<option value='".$row["rid"]."'>".$row["rid_type"].$row["rid"]."</option>";
                            													}
                            				echo							'
                            												</select>
                                    									</td>
																	</tr>
																</table>
															</th>
														</tr>
													</table>
												';
											*/
											
											// SAVE BUTTON
											echo	'
													<table width="385px" cellspacing="5px">	
														<tr>
															<th colspan="2">&nbsp;</th>
														</tr>
														<tr>
															<th style="border: 1px solid #FFFFFF;" colspan="2">
																<table width="100%" cellspacing="5px">
																	<tr>
																		<td align="left">Bearbeiten fertigstellen</td>
																		<td align="right"><input type="submit" name="save_event" value="Speichern" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;" /></td>
																	</tr>
																</table>
															</th>
														</tr>
													</table>
												';												
												
											// SAVE BUTTON CLICKED
											if(isset($_POST['save_event'])) {
												// SEARCH FOR INACTIVE EVENT FROM LOGGED IN USER
												if($se_inactive == true) {
													$result_se_inactive = mysqli_query($mysqli, $se_inactive);
													$anzahl_se_inactive = mysqli_num_rows($result_se_inactive);
													
													// SET EVENT TO ACTIVE AND RELOAD
													mysqli_query($mysqli, $up_estatus_setinactive);
													header('Location: /msdn/my_event.php');
													ob_end_flush(); 
												}
											}
										}
									// NO EVENT FROM LOGGED IN USER FOUND
									} else {
										// MESSAGE AND LINK TO CREATE NEW EVENT
										echo 		'<meta http-equiv="refresh" content="5; url=/msdn/make_event.php" />';
										echo		'
													<table width="385px" cellspacing="5px">
														<tr>
															<th colspan="2">Aktuell haben Sie keine laufende Veranstaltung. Sie werden weitergeleitet ... <img src="images/ripple12px-fts2.gif" /></th>
														</tr>
													</table>
													';
									}
								// NOT LOGGED IN
								} else {
									login_check($mysqli) == false;
									header('Location: /msdn/index.php');
								}
							?>
						</form>
						
						<table width="385px" cellspacing="5px" style="border: 0;">
							<tr>
								<th colspan="2">&nbsp;</th>
							</tr>
						</table>
					</p>
					
					<div id="repair-confirm" title="Zeitnehmer Positionen wiederherstellen">
                        <p id="repair-text">
                            <span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
                            Bitte Wahl der Wiederherstellung wählen
                            <br />
                            <br />
                            <select id="recovery_type" name="revovery_type" style="width: 250px !important;">
                                <option selected disabled>Bitte wählen</option>
                                <option value="results">Aus Ergebnisliste extrapolieren</option>
                                <option disabled value="position">Positionen manuell übergeben</option>
                            </select>
                        </p>
                        
                        <input type="hidden" name="zid_buffer" id="zid_buffer" />
                    </div>
				</div>
		
				<div id="supportingText">
					<!-- 	COLUMN 2	-->
					<!--
					<div id="modul_2" align="center">
						<h3><span>ÜBERSCHRIFT 2</span></h3>
						<p>
							Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentialsly unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
						</p>
					</div>
					-->
				</div>

				<!-- 	FOOTER 		-->
				<?php
					include("essentials/footer.php");
				?>
			</div>
		</div>
	</body>
</html>