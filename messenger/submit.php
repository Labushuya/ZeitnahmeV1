<?php
	// INCLUDE FUNCTIONS
	include_once '../includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once '../includes/db_connect.php';
	
	// START SECURE SESSION
	sec_session_start();
        require_once("config.php");
	require_once("chatClass.php");
	$chattext = strip_tags( $_GET['chattext'] );
	$whoami = $_SESSION['vname'][0] . $_SESSION['nname'];
	chatClass::setChatLines($chattext, $whoami, $_SESSION['color']);
?>