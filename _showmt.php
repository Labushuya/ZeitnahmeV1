<?php error_reporting(E_ALL);		
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
	
	// START SECURE SESSION
	sec_session_start();
	
	// CUSTOM NAVBAR
	if(login_check($mysqli) == true) {
		// SEARCH FOR USER EVENTS
		// RETURN SESSION TO LOCAL VARIABLES
		$eid	= $_SESSION['eid'];
		$username	= $_SESSION['username'];
		$vname		= $_SESSION['vname'];
		$nname		= $_SESSION['nname'];
		$type		= $_SESSION['type'];
		$color		= $_SESSION['color'];
		
		// SEARCH FOR USER EVENTS
		// CREATE EVENT ID FROM ACTIVE SESSION
		$eid	= $_SESSION['user_id'];
		
		// SEARCH FOR EVENTS FROM LOGGED IN USER
		$select_event = "SELECT id, eid FROM _race_run_events WHERE `eid` = '" . $eid . "' AND `active` = '1'";
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
		@$_SESSION['user_id'] = "";
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
		<?php include("lib/library.html"); ?>
		
		<style>
			.anchor {
				display: none;
				margin: 5px;
			}
		</style>
		
		<script>
			$(document).ready(function() {
				$("td[colspan=4]").find("div").hide();
				$("table").click(function(event) {
					event.stopPropagation();
					var $target = $(event.target);
					if ($target.closest("td#anchor") > 1) {
						$target.slideUp();
					} else {
						$target.closest("tr").next().find("div").slideToggle();
					}  

					$("#close").click(function(){
						$target.closest("tr").prev().find("div").slideUp();
						$target.closest("tr").next().find("div").slideUp();
					});
				});
				
				$(".gen_qr").click(function() {
					$(".qr_reload").html("");
					
					var did = $(this).val();
					var eid = <?php echo $eid; ?>;
					
					$.ajax({
						type: 'POST',
						data: 	{
									did: did, 
									eid: eid								
								},
						url: 'post_gen_qr_login.php',
						success: function(data) {
							$(".display_qr").html(data);
						}
					});
				});
				
				$("#export_tmember").change(function(){
				    var id = $(this).children(":selected").attr("id");
				    
				    if(id == "csv_export") {
				        $("#export_state_csv").show();
				        $("#export_as_csv").show();
						
						$("#export_state_ods").hide();
				        $("#export_as_ods").hide();
						
						$("#force_csv_export").click(function(){
							window.location.href = 'tm_info_csv_export.php';
							$("#export_tmember").prop('selectedIndex', 0);
						});
					} else if(id == "ods_export") {
						$("#export_state_ods").show();
				        $("#export_as_ods").show();
						
						$("#export_state_csv").hide();
				        $("#export_as_csv").hide();
						
						$("#force_ods_export").click(function(){
							window.location.href = 'tm_info_ods_export.php';
							$("#export_tmember").prop('selectedIndex', 0);
						});
					} else {
				        $("#export_state_csv").hide();
				        $("#export_as_csv").hide();
						
						$("#export_state_ods").hide();
				        $("#export_as_ods").hide();
						
						if(id == "xlsx_export") {
							window.location.href = 'tm_info_xlsx_export.php';
							$("#export_tmember").prop('selectedIndex', 0);
						} else if(id == "xls_export") {
							window.location.href = 'tm_info_xls_export.php';
							$("#export_tmember").prop('selectedIndex', 0);
						}
				    }
				});
			});
			
			// AJAX FOR DIRECT DEACTIVATION OF TMEMBER FOR SPECIFIC ROUNDS
			function updatesidRD(rid, eid, sid) {
				$.ajax({
					url: "update_mt_rd.php",
					type: "POST",
					data:	{
								rid: rid,
								eid: eid,													
								sid: sid
							},
					success: function(data) {
						if(data == "ins") {
							$('#' + eid + rid + sid).css('background', 'transparent');
							$('#' + eid + rid + sid).css('background-color', '#FF0000');
							$('#' + eid + rid + sid).css('color', '#FFFFFF');
						} else if(data == "del") {
							$('#' + eid + rid + sid).css('background', 'transparent');
							$('#' + eid + rid + sid).css('background-color', '#00FF00');
							$('#' + eid + rid + sid).css('color', '#A09A8E');
						}
					}
				});
			}
		</script>
		
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
					<h3>Gesamtübersicht Ihrer Teilnehmer</h3>
					<p>
					    <table width="385px" cellspacing="5px" style="border: 1px solid #FFFFFF; font-size: small;">
							<tr>
								<th align="left">Teilnehmer Informationen</th>
								<th align="right">
								    <select name="export_tmember" id="export_tmember" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 100%;">
										<option selected="selected" disabled="disabled">Exportieren als</option>
										<option id="xlsx_export">XLSX</option>
										<option id="xls_export">XLS</option>
										<option id="ods_export">ODS</option>
										<option id="csv_export">CSV</option>
										<option id="pdf_export" disabled>PDF [ zukünftig ]</option>
									</select>
								</th>
							</tr>
							<tr id="export_state_csv" style="display: none;">
							    <th colspan="2" style="text-align: center; color: #8E6516;">
									<p>Dieses Format enthält <u>keine</u> QR-Code Images!</p>
								</th>
							</tr>
							<tr id="export_state_ods" style="display: none;">
							    <th colspan="2" style="text-align: center; color: #8E6516;">
									<p>Dieses Format ist <u>ohne</u> Formatierung und enthält <br /><u>keine</u> QR-Code Images!</p>
								</th>
							</tr>
							<tr id="export_as_csv" style="display: none;">
							    <th colspan="2" style="text-align: center;">
									<button id="force_csv_export" style="padding: 5px; background: transparent; background-color: #fff; border: 1px solid #8E6516; color: #8E6516; width: 80%;"><i class="fas fa-download"></i> dennoch herunterladen</button>
								</th>
					        </tr>
							<tr id="export_as_ods" style="display: none;">
							    <th colspan="2" style="text-align: center;">
									<button id="force_ods_export" style="padding: 5px; background: transparent; background-color: #fff; border: 1px solid #8E6516; color: #8E6516; width: 80%;"><i class="fas fa-download"></i> dennoch herunterladen</button>
								</th>
					        </tr>
						</table>
						
						<br />
					    
					    <table width="385px" cellspacing="5px" style="border: 1px solid #FFFFFF; border-bottom: 0; font-size: small;">
							<tr>
								<th align="left">Bitte Teilnehmer wählen</th>
								<th align="right"><button id="close" style="background: transparent; background-color: #A09A8E; border: 0; width: 15px" /><img id="status_img" src="images/minus.png" alt="Übersicht schließen"></button></th>
							</tr>
						</table>
						
						<table style="border: 1px solid #FFFFFF; font-size: small;" cellpadding="5px" width="385px" id="table_slider">
							<?
								// SEARCH FOR TMEMBERS
								$select_tmembers = "SELECT * FROM `_optio_tmembers` WHERE `eid` = '" . $eid . "'";
								$result_tmembers = mysqli_query($mysqli, $select_tmembers);
								$numrow_tmembers = mysqli_num_rows($result_tmembers);
								
								// CHECK IF THERE ARE ANY TMEMBERS
								if($numrow_tmembers > 0) {
									// TMEMBERS FOUND, ECHO TABLE HEADER AND CONTENT
									while($getrow_tmembers = mysqli_fetch_assoc($result_tmembers)) {
										echo	"
												<tr>
													<td align=\"center\">
														<div>" . $getrow_tmembers['sid'] . "</div>
													</td>
													<td>
														<div>" . $getrow_tmembers['vname_1'] . " " . $getrow_tmembers['nname_1'] . "</div>
													</td>
													<td>
														<div>" . $getrow_tmembers['vname_2'] . " " . $getrow_tmembers['nname_2'] . "</div>
													</td>
													<td align=\"right\">
														<div><img style=\"margin-right: 3px;\" class=\"status_image_tm\" id=\"status_img_t" . $getrow_tmembers['sid'] . "\" src=\"images/plus.png\"></div>
													</td>
												</tr>
												<tr>
													<td colspan=\"4\">
														<div class=\"anchor\">
															<table style=\"border: 1px solid #FFFFFF; font-size: small;\" cellpadding=\"5px\" width=\"100%\">
																<tr>
																	<td align=\"left\" colspan=\"5\"><strong>Fahrerbezogene Daten:</strong></td>
																</tr>
																<tr>
																	<th>Kennung</td>
																	<th>Kennwort</td>
																	<th>Klasse</td>
																	<td><strong>Fahrzeug</strong></td>
																	<th>Baujahr</td>
																</tr>
																<tr>
																	<td align=\"center\">" . $getrow_tmembers['uname'] . "</td>
																	<td align=\"center\">" . $getrow_tmembers['upass'] . "</td>
																	<td align=\"center\">" . $getrow_tmembers['class'] . "</td>
																	<td>" . $getrow_tmembers['fabrikat'] . " " . $getrow_tmembers['typ'] . "</td>
																	<td align=\"center\">" . $getrow_tmembers['baujahr'] . "</td>
																</tr>
															</table>
														</div>
												";	
										
										// FETCH AMOUNT OF ROUNDS
										$select_round = "SELECT `eid`, `count_wptable`, `master_rid_type` FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `active` = '1'";
										$result_round = mysqli_query($mysqli, $select_round);
										$getrow_round = mysqli_fetch_assoc($result_round);
										$total_rounds = $getrow_round['count_wptable'];
										$rid_type = $getrow_round['master_rid_type'];
										
										// START FURTHER OUTPUT
										echo	"
														<div class=\"anchor\">
															<table style=\"border: 1px solid #FFFFFF; font-size: small;\" cellpadding=\"5px\" width=\"100%\">
												";
												
										for($i = 1; $i < ($total_rounds + 1); $i++) {
											// ENABLE TABLE HEADER ONCE
											if($i == 1) {
												echo	"
																<tr>
																	<td align=\"left\" colspan=\"" . ($total_rounds + 1) . "\"><strong>Statusübersicht:</strong></td>
																</tr>
																<tr>
														";
											}
											
											// SEARCH POSSIBLE LOCK IN ANY ROUND FOR EACH START ID
											$select_lock = "SELECT `eid`, `rid`, `sid` FROM `_optio_tmembers_lock` WHERE `eid` = '" . $eid . "' AND `rid` = '" . $i . "' AND `sid` = '" . $getrow_tmembers['sid'] . "'";
											$result_lock = mysqli_query($mysqli, $select_lock);
											$numrow_lock = mysqli_num_rows($result_lock);
											
											// ALLOCATE STATUS COLOR BASED ON NUM ROWS
											if($numrow_lock == 0) {
												$status = "#00FF00";
												$color = "#8E6516";
											} elseif($numrow_lock == 1) {
												$status = "#FF0000";
												$color = "#FFFFFF";
											}
											
											echo	'
																	<td align="center" style="width: 71px;">
																		<input type="button" id="' . $eid . $i . $getrow_tmembers['sid'] . '" style="color: ' . $color . '; width: 71px; background: transparent; background-color: ' . $status . ';" value="' . $i . '" name="rid" onclick="updatesidRD(this.value, ' . $eid . ', ' . $getrow_tmembers['sid'] . ');" />
																	</td>
													';
													
											if($i > 0 AND $i % 5 == 0 AND !($i % 5)) {
												echo	"
																</tr>
																<tr>
														";
											}
										}
										
										if($getrow_tmembers['image_path'] == "") {
											$getrow_tmembers['image_path'] = "<button type=\"button\" class='gen_qr' value=\"" . $getrow_tmembers['id'] . "\" style=\"border: 1px solid #dcdcdc;\">Nicht gefunden. Neu laden?</button>";
										} else {
											$getrow_tmembers['image_path'] = "<img src=\"" . $getrow_tmembers['image_path'] . "\"></img>";
										}
										
										// END FURTHER OUTPUT
										echo	"
																</tr>
																<tr>
																	<td colspan=\"100%\" align=\"center\" style=\"font-size: small; color: #8E6516;\">[ " . $rid_type . " anklicken, um Teilnehmer zu sperren / freizugeben ]</td>
																</tr>
															</table>
															<table style=\"border: 1px solid #FFFFFF; border-top: 0; font-size: small;\" cellpadding=\"5px\" width=\"100%\">
																<tr>
																	<td align=\"left\"><strong>QR-Code Login:</strong></td>
																	<td align=\"right\" class=\"display_qr\"><span class=\"qr_reload\">" . $getrow_tmembers['image_path'] . "</span></td>
																</tr>
															</table>
														</div>
														<div class=\"anchor\">
															<table style=\"border: 0; font-size: small;\" cellpadding=\"5px\" width=\"100%\">
																<tr>
																	<td colspan=\"100%\" align=\"center\"><hr class=\"white-hr\" /></td>
																</tr>
															</table>
														</div>
												";
									}
								} elseif($numrow_tmembers == 0) {
									echo	"
											<tr>
												<td>Es wurden keine Teilnehmer gefunden</td>
											</tr>
											";
								}
								?>
						</table>
						
						<table width="385px" cellspacing="5px" style="border: 0;">
							<tr>
								<th colspan="2">&nbsp;</th>
							</tr>
						</table>
					</p>
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