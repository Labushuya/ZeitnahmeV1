<?php error_reporting(E_ALL);
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';
	
	// START SESSION
	session_start();
	
	// CHECK LOGGED IN USER AND VALIDATE INFORMATION
	if(
		isset($_SESSION['user_id']) AND $_SESSION['user_id'] != "" AND
		!isset($_GET['eid']) AND !isset($_GET['sid'])
	) {
		// RETURN SESSION TO LOCAL VARIABLES
		$uid	= $_SESSION['user_id'];
		$sid		= $_SESSION['sid'];
		$class		= $_SESSION['class'];
		$fabrikat	= $_SESSION['fabrikat'];
		$typ		= $_SESSION['typ'];
		$baujahr	= $_SESSION['baujahr'];
		$vname_1	= $_SESSION['vname_1'];
		$nname_1	= $_SESSION['nname_1'];
		$vname_2	= $_SESSION['vname_2'];
		$nname_2	= $_SESSION['nname_2'];
		$ready		= $_SESSION['ready'];
		
		// DECLARE EVENT ID
		$eid = $_SESSION['eid'];
		
		// FETCH EVENT TITLE
		$select_evdata = "SELECT `eid`, `title` FROM _race_run_events WHERE `eid` = '" . $eid . "' AND `active` = '1'";
		$result_evdata = mysqli_query($mysqli, $select_evdata);
		$numrow_evdata = mysqli_num_rows($result_evdata);
		
		if($numrow_evdata == 1) {
			$getrow_evdata = mysqli_fetch_assoc($result_evdata);
			$event_title = $getrow_evdata['title'];
			
			// OUTPUT NAVBAR AND LOGIN / LOGOUT PANEL
			$logged = file_get_contents("essentials/opt_logout_mt.html");
			$navbar = file_get_contents("essentials/mt_navbar_logged_in.html");
		} elseif($numrow_evdata == 0) {
			$logged = file_get_contents("essentials/login.html");
			$navbar = file_get_contents("essentials/navbar_logged_out.html");
			
			header("Location: index.php");
		}	
	} elseif(isset($_GET['eid']) AND isset($_GET['sid'])) {
	    //  Bereinige Übergabeparameter
	    $eid = mysqli_real_escape_string($mysqli, $_GET['eid']);
	    $sid = mysqli_real_escape_string($mysqli, $_GET['sid']);
	    
	    //  Hole alle Informationen über diesen Teilnehmer
	    $select_tmember = "SELECT * FROM `_optio_tmembers` WHERE `eid` = '" . $eid . "' AND `sid` = '" . $sid . "'";
	    $result_tmember = mysqli_query($mysqli, $select_tmember);
	    $numrow_tmember = mysqli_num_rows($result_tmember);
	    
	    if($numrow_tmember > 0) {
	        if($numrow_tmember == 1) {
	            $getrow_tmember = mysqli_fetch_assoc($result_tmember);
	            
	            //	Weise Teilnehmer Informationen zu
				$uid	= $getrow_tmember['id'];
				$sid		= $getrow_tmember['sid'];
				$class		= $getrow_tmember['class'];
				$fabrikat	= $getrow_tmember['fabrikat'];
				$typ		= $getrow_tmember['typ'];
				$baujahr	= $getrow_tmember['baujahr'];
				$vname_1	= $getrow_tmember['vname_1'];
				$nname_1	= $getrow_tmember['nname_1'];
				$vname_2	= $getrow_tmember['vname_2'];
				$nname_2	= $getrow_tmember['nname_2'];
				$ready		= $getrow_tmember['ready'];
				
				// FETCH EVENT TITLE
				$select_evdata = "SELECT `eid`, `title` FROM _race_run_events WHERE `eid` = '" . $eid . "' AND `active` = '1'";
				$result_evdata = mysqli_query($mysqli, $select_evdata);
				$numrow_evdata = mysqli_num_rows($result_evdata);
				
				if($numrow_evdata == 1) {
					$getrow_evdata = mysqli_fetch_assoc($result_evdata);
					$event_title = $getrow_evdata['title'];
				} elseif($numrow_evdata == 0) {
					header("Location: index.php");
				}	
	        } elseif($numrow_tmember > 1) {
				echo    "
						<script>
							window.close();
						</script>
						";
			}
	    } elseif($numrow_tmember == 0) {
	        echo    "
	                <script>
	                    window.close();
	                </script>
	                ";
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
		
		<!--	DECLARING AJAX VARIABLES FOR RACER_RESULT.JS	-->
		<script>
			// DECLARE EVENT ID AND ROUND ID BASED ON PHP VARIABLES
			var eid = <? echo $eid; ?>;	
			var sid = <? echo $sid; ?>;
		</script>
		
		<!--	INCLUDING LIB	-->
		<?php 
			include("lib/library.html");		
			include("lib/library_int_racer_result.html");		
		?>
		
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
			
				<!-- 	COLUMN 1	-->
				<div id="tm_modul_1" align="center">
					<h3><? echo $event_title; ?> &mdash; Teilnehmer Zugang</h3>
					<p>												
						<table cellspacing="5px" cellpadding="0" style="border: 1px solid #FFFFFF;" width="600px">
							<tr>
								<td colspan="5" style="border-bottom: 1px solid #FFF;"><font size="4"><strong>Team</strong></font></td>
							</tr>
							<tr>
								<td align="center" colspan="1"><font size="2"><strong>#</strong></font></td>
								<td align="center" colspan="2"><font size="2"><strong>Fahrer</strong></font></td>									
								<td align="center" colspan="2"><font size="2"><strong>Beifahrer</strong></font></td>
							</tr>
							<tr>
								<td align="center" colspan="1"><font color="#8E6516"><? echo $sid; ?></font></td>
								<td align="center" colspan="2"><font color="#8E6516"><? echo $vname_1 . " " . $nname_1; ?></font></td>
								<td align="center" colspan="2"><font color="#8E6516"><? echo $vname_2 . " " . $nname_2; ?></font></td>
							</tr>
						</table>
					</p>
					<p>
						<table cellspacing="5px" cellpadding="0" style="border: 1px solid #FFFFFF;" width="600px">
							<tr>
								<td colspan="5" style="border-bottom: 1px solid #FFF;"><font size="4"><strong>Fahrzeug & Klasse / Modus</strong></font></td>
							</tr>
							<tr>
								<td align="center" colspan="1"><font size="2"><strong>Klasse</strong></font></td>
								<td align="center" colspan="1"><font size="2">&nbsp;</font></td>
								<td align="center" colspan="1"><font size="2"><strong>Fabrikat</strong></font></td>
								<td align="center" colspan="1"><font size="2"><strong>Modell</strong></font></td>									
								<td align="center" colspan="1"><font size="2"><strong>Baujahr</strong></font></td>
							</tr>
							<tr>
								<td align="center" colspan="1"><font color="#8E6516"><? echo $class; ?></font></td>
								<td align="center" colspan="1"><font size="2">&nbsp;</font></td>
								<td align="center" colspan="1"><font color="#8E6516"><? echo $fabrikat; ?></font></td>
								<td align="center" colspan="1"><font color="#8E6516"><? echo $typ; ?></font></td>
								<td align="center" colspan="1"><font color="#8E6516"><? echo $baujahr; ?></font></td>
							</tr>
							<tr>
						</table>
					</p>
					<p id="racer_status">					
						
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
			</div>
		</div>
	</body>
</html>