<?php
	if(isset($_GET['sso'])) {
		header("Location: /msdn/includes/mt_process_login.php?sso=" . $_GET['sso']);
	}
?>