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
		// CREATE EVENT ID FROM ACTIVE SESSION
		$eid	= $_SESSION['user_id'];
		
		// SEARCH FOR EVENTS FROM LOGGED IN USER
		$select_event = "SELECT `id`, `eid` FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `edit` = '1'";
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
	
	// EVENT SAVE / EDIT
	// CHECK FOR LOGGED IN USER
	if(@$_SESSION['user_id'] >= 1) {
		// CREATE EVENT HANDLER
		$event_handler	= "";
		
		// CREATE EVENT ID FROM ACTIVE SESSION
		$eid	= $_SESSION['user_id'];
		
		// CREATE QUERIES
		$se_event				= "SELECT * FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `active` = '1'";
		$se_edit				= "SELECT `id`, `eid`, `active` FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `edit` = '0' AND `active` = '1'";
		$se_noedit				= "SELECT `id`, `eid`, `active` FROM `_race_run_events` WHERE `eid` = '" . $eid . "' AND `edit` = '1' AND `active` = '1'";
		$up_estatus_setedit		= "UPDATE `_race_run_events` SET `edit` = '1' WHERE `eid` = '" . $eid . "' AND `active` = '1'";
		$up_estatus_setnoedit	= "UPDATE `_race_run_events` SET `edit` = '0' WHERE `eid` = '" . $eid . "' AND `active` = '1'";
		
		// SEARCH FOR EVENTS FROM LOGGED IN USER
		if($se_event == true) {
			$result_se_event = mysqli_query($mysqli, $se_event);
			$anzahl_se_event = mysqli_num_rows($result_se_event);
				
			// EVENT FOUND	
			if($anzahl_se_event > 0) {	
				$result_se_noedit = mysqli_query($mysqli, $se_noedit);
				$anzahl_se_noedit = mysqli_num_rows($result_se_noedit);
				
				// EVENT ON STATUS noedit FOUND
				if($anzahl_se_noedit > 0) {
					// FETCH CURRENT AMOUNT OF ZMEMBERS
					$select_bmembers = "SELECT * FROM `_optio_bmembers` WHERE `eid` = '" . $eid . "' ORDER BY `id` ASC";
					$result_bmembers = mysqli_query($mysqli, $select_bmembers);
					$numrow_bmembers = mysqli_num_rows($result_bmembers);
				// EVENT ON STATUS ACTIVE FOUND
				} else {
					// REDIRECT TO MY EVENT --> USER MUST EDIT FIRST
					header('Location: /msdn/my_event.php');
				}
			// NO EVENT FROM LOGGED IN USER FOUND
			} else {
				// REDIRECT TO MY EVENT --> USER MUST EDIT FIRST
				header('Location: /msdn/my_event.php');
			}
		}
	// NOT LOGGED IN --> HIDE CONTENT AND REDIRECT
	} else {
		header('Location: /msdn/index.php');
		@$_SESSION['user_id'] = "";
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
		<?php include("lib/library.html"); ?>
		
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
					<h3>Mein Event</h3>
					<p>
						<?php 
							// CREATE OUTPUT BASED ON AMOUNT zmembers
							if($numrow_bmembers > 0) {
								echo	'
											<table width="385px" cellspacing="5px" style="border: 1px solid #FFFFFF; border-bottom: 0;">
												<tr>
													<th colspan="2">Bordkartenkontrollen löschen</th>
												</tr>
												<tr>
													<th colspan="2"><hr /></th>
												</tr>
												<tr>
													<th colspan="2" align="center">
														<table width="100%" cellspacing="0">
															<tr>
																<td align="left"><font size="2"><strong>Kennung</strong></font></td>
																<td align="left"><font size="2"><strong>Kennwort</strong></font></td>
																<td align="center"><font size="2"><strong>Bezeichnung</strong></font></td>
																<td align="center"><font size="2"><strong>Veranstaltungsdatum</strong></font></td>
																<td align="right">&nbsp;</td>
															</tr>														
										';
								while($getrow_bmembers = mysqli_fetch_assoc($result_bmembers)) {
								    //	Passe Datum an
                                    $explode = explode("-", $getrow_bmembers['eventdate']);
                                    $date = $explode[2] . "." . $explode[1] . "." . $explode[0];
								    
									echo	'
															<tr class="delete_mem_' . $getrow_bmembers['id'] . '">
																<td align="left"><font size="2" color="#8E6516">' . utf8_encode($getrow_bmembers['uname']) . '</font></td>
																<td align="left"><font size="2" color="#8E6516">' . utf8_encode($getrow_bmembers['upass']) . '</font></td>
																<td align="center"><font size="2" color="#8E6516">' . utf8_encode($getrow_bmembers['opt_whois']) . '</font></td>
																<td align="center"><font size="2" color="#8E6516">' . utf8_encode($date) . '</font></td>
											                    <td align="right"><a class="btn btn-success" id="' . $getrow_bmembers['id'] . '"><img src="images/cross.png" alt="Bordkartenkontrolle löschen"></img></a></td>
															</tr>
											';
								}
								echo	'
														</table>
													</th>
												</tr>
											</table>
											<table width="385px" cellspacing="5px" style="border: 1px solid #FFFFFF; border-top: 0;">
												<tr>
													<th colspan="2"><br /></th>
												</tr>
												<tr>
													<th colspan="2">Keine Änderungen vornehmen</th>
												</tr>
												<tr>
													<th colspan="2"><hr /></th>
												</tr>
												<tr>
													<td align="left"><input type="button" value="<<" onclick="location.href=\'my_event.php\';" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;"/></td>
													<td align="right"><input type="button" value="BKK hinzufügen" onclick="location.href=\'_addbc.php\';" style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 135px;"/></td>
												</tr>
											</table>
											<table width="385px" cellspacing="5px">
												<tr>
													<th colspan="2"><br /></th>
												</tr>
											</table>
										';
							} else {
								header("Location: _addbc.php");
							}
						?>
						
						<script type="text/javascript">
							$(document).ready(function() {
								$('.btn-success').click(function() {
									var id = $(this).attr("id");
									if (confirm("Sind Sie sich sicher, dass Sie diese Bordkartenkontrolle entfernen möchten?")) {
										$.ajax({
											type: "POST",
											url: "delete_bmember.php",
											data: ({
												id: id
											}),
											cache: false,
											success: function(data) {
												$(".delete_mem_" + id).fadeOut('slow');
											}
										});
									} else {
										return false;
									}
								});
							});
						</script>
						
						<?php
        					if(!empty($error_msg)) {
        						echo '<p class="error">';
        							echo '<span style="margin-left: 5px; border-bottom: 1px dotted #8E6516;"><font color="#FFD700">' . $state . '</font></span><br />';
        							echo $error_msg;
        						echo '</p>';
        					}
        				?>
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