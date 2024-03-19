<?php
	// INCLUDE FUNCTIONS
	include_once '../includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once '../includes/db_connect.php';
	
	// START SECURE SESSION
	sec_session_start();
        require_once("config.php");
	require_once("chatClass.php");
	$id = intval( $_GET['lastTimeID'] );
	$jsonData = chatClass::getRestChatLines($id);
	print $jsonData;
?>