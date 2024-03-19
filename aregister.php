<?php error_reporting(E_ALL);		
	// BUFFER OUTPUT
	ob_start();

	// INCLUDE REGISTER.INC
	include_once 'includes/aregister.inc.php';
	
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// START SECURE SESSION
	sec_session_start();
	 
	// CUSTOM NAVBAR
	if(login_check($mysqli) == true) {
		header("Location: index.php");
	} else {
		$logged = file_get_contents("essentials/login.html");
		$navbar = file_get_contents("essentials/navbar_logged_out.html");
		$chat = "";
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
			<!--	CHAT		-->
			<?php
				echo $chat;
			?>
		
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
					<h3>Auswerterkonto erstellen</h3>
					<p>
						<form action="<?php echo esc_url($_SERVER['PHP_SELF']); ?>" method="post" name="registration_form">
							<div class="toggle2_container trigger2_active">
							<table width="385px" cellspacing="5px" style="border: 1px solid #FFFFFF;">
								<tr>
									<th colspan="2" align="left">Benutzerangaben</th>
								</tr>
							</table>
							<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF;">
								<tr>
									<td align="left">Benutzername<font color="#8E6516">*</font></td>
									<td align="right"><input name="username" id="username" type="text" value="<?php if(!isset($_POST['username'])) { $ph_username = 'placeholder="Benutzername"'; } else { echo $_POST['username']; $ph_username = ''; } ?>" <? echo $ph_username; ?> required="required" /></td>
								</tr>
								<tr>
									<td align="left">Passwort<font color="#8E6516">*</font></td>
									<td align="right"><input name="password" id="password" type="password" value="<?php if(!isset($_POST['password'])) { $ph_password = 'placeholder="Mind. 6 Zeichen"'; } else { echo $_POST['password']; $ph_password = ''; } ?>" <? echo $ph_password; ?> required="required" /></td>
								</tr>
								<tr>
									<td align="left">Passwort<font color="#8E6516">*</font></td>
									<td align="right"><input name="confirmpwd" id="confirmpwd" type="password" value="<?php if(!isset($_POST['confirmpwd'])) { $ph_confirmpwd = 'placeholder="Passwort wiederholen"'; } else { echo $_POST['confirmpwd']; $ph_confirmpwd = ''; } ?>" <? echo $ph_confirmpwd; ?> required="required" /></td>
								</tr>
								<tr>
									<td align="left">E-Mail<font color="#8E6516">*</font></td>
									<td align="right"><input name="email" id="email" type="text" value="<?php if(!isset($_POST['email'])) { $ph_email = 'placeholder="E-Mail Adresse"'; } else { echo $_POST['email']; $ph_email = ''; } ?>" <? echo $ph_email; ?> required="required" /></td>
								</tr>
								<tr>
									<th colspan="2">&nbsp;</th>
								</tr>
								<tr>
									<td align="left">&nbsp;</td>
									<td align="right" class="trigger2 trigger2-next"><input type="button" value="Weiter" /></td>
								</tr>
							</table>
							</div>
							<div class="toggle2_container hidden">
							<table width="385px" cellspacing="5px" style="border: 1px solid #FFFFFF;">
								<tr>
									<th colspan="2" align="left">Kontaktangaben</th>
								</tr>
							</table>
							<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF;">
								<tr>
									<td align="left">Anrede<font color="#8E6516">*</font></td>
									<td align="right">
										<select name="anrede" id="anrede" class="input-block-level" placeholder="Bitte auswählen" required="required"  >
										<option value="None" <? if($_POST['anrede'] == "" OR $_POST['anrede'] == "None" OR empty($_POST['anrede'])) { ?> selected="selected" <? } ?> disabled="disabled">Bitte auswählen</option>
										<option value="Herr" <? if($_POST['anrede'] == "Herr") { ?> selected="selected" <? } ?>>Herr</option>
										<option value="Frau" <? if($_POST['anrede'] == "Frau") { ?> selected="selected" <? } ?>>Frau</option>
										</select>
									</td>
								</tr>
								<tr>
									<td align="left">Vorname<font color="#8E6516">*</font></td>
									<td align="right"><input name="vname" id="vname" type="text" value="<?php if(!isset($_POST['vname'])) { $ph_vname = 'placeholder="Vorname"'; } else { echo $_POST['vname']; $ph_vname = ''; } ?>" <? echo $ph_vname; ?> required="required" /></td>
								</tr>
								<tr>
									<td align="left">Nachname<font color="#8E6516">*</font></td>
									<td align="right"><input name="nname" id="nname" type="text" value="<?php if(!isset($_POST['nname'])) { $ph_nname = 'placeholder="Nachname"'; } else { echo $_POST['nname']; $ph_nname = ''; } ?>" <? echo $ph_nname; ?> required="required" /></td>
								</tr>
								<tr>
									<td align="left">Str. / Nr.<font color="#8E6516">*</font></td>
									<td align="right">
										<table width="135px" cellspacing="0px">
											<tr>
												<td align="left">
													<input name="str" id="str" type="text" style="width: 100px;" value="<?php if(!isset($_POST['str'])) { $ph_str = 'placeholder="Straße"'; } else { echo $_POST['str']; $ph_str = ''; } ?>" <? echo $ph_str; ?> required="required" />
												</td>
												<td align="right">
													<input name="nr" id="nr" type="text" style="width: 30px;" value="<?php if(!isset($_POST['nr'])) { $ph_nr = 'placeholder="Nr."'; } else { echo $_POST['nr']; $ph_nr = ''; } ?>" <? echo $ph_nr; ?> required="required" />
												</td>							
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td align="left">PLZ<font color="#8E6516">*</font></td>
									<td align="right"><input name="plz" id="plz" type="text" value="<?php if(!isset($_POST['plz'])) { $ph_plz = 'placeholder="PLZ"'; } else { echo $_POST['plz']; $ph_plz = ''; } ?>" <? echo $ph_plz; ?> required="required" /></td>
								</tr>
								<tr>
									<td align="left">Ort<font color="#8E6516">*</font></td>
									<td align="right"><input name="ort" id="ort" type="text" value="<?php if(!isset($_POST['ort'])) { $ph_ort = 'placeholder="Ort"'; } else { echo $_POST['ort']; $ph_ort = ''; } ?>" <? echo $ph_ort; ?> required="required" /></td>
								</tr>
								<tr>
									<th colspan="2">&nbsp;</th>
								</tr>
								<tr>
									<td align="left" class="trigger2 trigger2-back"><input type="button" value="Zurück" /></td>
									<td align="right" class="trigger2 trigger2-next"><input type="button" value="Weiter" /></td>
								</tr>
							</table>
							</div>							
							<div class="toggle2_container hidden">
							<table width="385px" cellspacing="5px" style="border: 1px solid #FFFFFF;">
								<tr>
									<th colspan="2" align="left">Abonnement Wahl</th>
								</tr>
							</table>
							<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF;">
								<tr>
									<td align="left"><font color="#C0C0C0">Basic</font><font color="#8E6516">*</font></td>
									<td align="center"><font color="#C0C0C0">250,00 € pro Veranstaltung</font></td>
									<td align="right"><label><input type="radio" name="abo" value="A1" <? if($_POST['abo'] == "A1") { echo "checked=\"checked\""; } ?> style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 50px;" /><img src="images/abo_silver.png" alt="Silber Abo" ></label></td>
								</tr>
								<tr>
									<td align="left"><font color="#FFD700">Premium</font><font color="#8E6516">*</font></td>
									<td align="center"><font color="#FFD700">500,00 € pro Veranstaltung</font></td>
									<td align="right"><label><input type="radio" name="abo" value="A2" <? if($_POST['abo'] == "A2") { echo "checked=\"checked\""; } ?> style="background: transparent; background-color: #FFFFFF; color: #8E6516; width: 50px;" /><img src="images/abo_gold.png" alt="Gold Abo" ></label></td>
								</tr>
							</table>
							<table width="385px" cellspacing="5px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF;">
								<tr>
									<th colspan="2" align="justify"><font size="1">Sie sind sich darüber im Klaren, dass Sie sich als <strong><font color="#8E6516">Auswerter</font></strong> zu den entsprechend hierfür geltenden Konditionen auf TimeKeeper registrieren.</font><br /><br /> <font size="1"><center>Falsche Konto-Wahl? <a href="/msdn/vregister.php"><font color="#8E6516">Hier geht's zum Registrierungsformular für Veranstalter</font></a></center></font></th>
								</tr>
								<tr>
									<th colspan="2">&nbsp;</th>
								</tr>
								<tr>
									<td align="left" class="trigger2 trigger2-back"><input type="button" value="Zurück" /></td>
									<td align="right" class="trigger2 trigger2-next"><input type="button" value="Weiter" /></td>
								</tr>
							</table>
							</div>							
							<div class="toggle2_container hidden">
							<table width="385px" cellspacing="5px" style="border: 1px solid #FFFFFF;">
								<tr>
									<th colspan="2" align="left">Allgemeine Geschäftsbedingungen</th>
								</tr>
							</table>
							<table width="385px" style="border-left: 1px solid #FFFFFF; border-top: 0; border-right: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF; border-spacing: 5px 0 5px 5px;">
								<tr>
									<th colspan="2"><div class="scrollbar"><?php include_once('includes/agb.php')?></div></th>
								</tr>
								<tr>								
									<td align="left">
										<section>
                                            <div class="checkboxFive">
                                          		<input type="checkbox" id="checkboxFiveInput" name="agb" value="accepted" <? if($_POST['agb'] == "accepted") { echo "checked=\"checked\""; } ?>  required="required" />
                                        	  	<label for="checkboxFiveInput"></label>
                                          	</div>
                                        </section>
									</td>
									<td align="right"><font size="1">Ich habe die AGB gelesen und akzeptiere diese</font></td>
								</tr>
								<tr>
									<th colspan="2">&nbsp;</th>
								</tr>
								<tr>
									<td align="left" class="trigger2 trigger2-back"><input type="button" value="Zurück" /></td>
									<td align="right"><input type="button" id="areg" value="Registrieren" onclick="return regformhash(this.form, this.form.username, this.form.email, this.form.password, this.form.confirmpwd, this.form.anrede, this.form.vname, this.form.nname, this.form.str, this.form.nr, this.form.plz, this.form.ort, this.form.agb, this.form.abo);" /></td>
								</tr>
							</table>
							</div>
						</form>
					</p>
					<?php
						if(!empty($error_msg)) {
							echo '<p class="error">';
								echo '<span style="margin-left: 5px; border-bottom: 1px dotted #8E6516;"><font color="#FFD700">Fehler:</font></span><br />';
								echo $error_msg;
								echo '<span style="margin-left: 5px; font-size: small;"><font color="#FFD700">&mdash;&emsp;</font>Zu Ihrer Sicherheit wurde Ihr Passwort nicht gespeichert</span><br />';
							echo '</p>';
						}
					?>
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