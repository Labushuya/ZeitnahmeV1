<? error_reporting(E_ALL);
	// INCLUDE FUNCTIONS
	include_once 'includes/functions.php';
	
	// INCLUDE DB_CONNECT
	include_once 'includes/db_connect.php';

	// SANITIZE $_POST
	$editval = strip_tags($_POST['editval']);
	$editval = trim($editval);
	
	$column = strip_tags($_POST['column']);
	$column = trim($column);
	
	$id = strip_tags($_POST['id']);
	$id = trim($id);
	
	// SET FUNCTION TITLE CASE CORRECT: NNAME
	function titleCaseNName($nname, $delimiters = array(" ", "-", ".", "'", "O'", "Mc"), $exceptions = array("van", "de", "el", "la", "von", "vom", "der", "und", "zu", "auf", "dem", "dos", "I", "II", "III", "IV", "V", "VI")) {
	/*
	 * EXCEPTIONS IN LOWER CASE ARE WORDS YOU DONT WANT CONVERTED
	 * EXCEPTIONS ALL IN UPPER CASE ARE ANY WORDS YOU DONT WANT TO CONVERTED TO TITLE CASE
	 * BUT SHOULD BE CONVERTED TO UPPER CASE, E. G.:
	 * "king henry viii" OR "king henry Viii" SHOULD BE "King Henry VIII"
	*/
		$nname = mb_convert_case($nname, MB_CASE_TITLE, "UTF-8");
		foreach ($delimiters as $dlnr => $delimiter) {
			$words = explode($delimiter, $nname);
			$newwords = array();
			foreach ($words as $wordnr => $word) {
				if (in_array(mb_strtoupper($word, "UTF-8"), $exceptions)) {
					// CHECK EXCEPTIONS LIST FOR ANY WORDS THAT SHOULD BE IN UPPER CASE
					$word = mb_strtoupper($word, "UTF-8");
				} elseif (in_array(mb_strtolower($word, "UTF-8"), $exceptions)) {
					// CHECK EXCEPTIONS LIST FOR ANY WORDS THAT SHOULD BE IN UPPER CASE
					$word = mb_strtolower($word, "UTF-8");
				} elseif (!in_array($word, $exceptions)) {
					// CONVERT TO UPPER CASE (NON-UTF-8 ONLY)
					$word = ucfirst($word);
				}
				array_push($newwords, $word);
			}
			$nname = join($delimiter, $newwords);
		} // FOREACH
		return $nname;	
	}
	
	
	// SET FUNCTION TITLE CASE CORRECT: VNAME
	function titleCaseVName($vname, $delimiters = array(" ", "-", "'"), $exceptions = array("I", "II", "III", "IV", "V", "VI")) {
	/*
	 * EXCEPTIONS IN LOWER CASE ARE WORDS YOU DONT WANT CONVERTED
	 * EXCEPTIONS ALL IN UPPER CASE ARE ANY WORDS YOU DONT WANT TO CONVERTED TO TITLE CASE
	 * BUT SHOULD BE CONVERTED TO UPPER CASE, E. G.:
	 * "king henry viii" OR "king henry Viii" SHOULD BE "King Henry VIII"
	*/
		$vname = mb_convert_case($vname, MB_CASE_TITLE, "UTF-8");
		foreach ($delimiters as $dlnr => $delimiter) {
			$words = explode($delimiter, $vname);
			$newwords = array();
			foreach ($words as $wordnr => $word) {
				if (in_array(mb_strtoupper($word, "UTF-8"), $exceptions)) {
					// CHECK EXCEPTIONS LIST FOR ANY WORDS THAT SHOULD BE IN UPPER CASE
					$word = mb_strtoupper($word, "UTF-8");
				} elseif (in_array(mb_strtolower($word, "UTF-8"), $exceptions)) {
					// CHECK EXCEPTIONS LIST FOR ANY WORDS THAT SHOULD BE IN UPPER CASE
					$word = mb_strtolower($word, "UTF-8");
				} elseif (!in_array($word, $exceptions)) {
					// CONVERT TO UPPER CASE (NON-UTF-8 ONLY)
					$word = ucfirst($word);
				}
				array_push($newwords, $word);
			}
			$vname = join($delimiter, $newwords);
		} // FOREACH
		return $vname;	
	}
	
	$nname = titleCaseNName($editval);
	$vname = titleCaseVName($nname);
	$editval = $nname;
	
	// QUERY
	$update = "UPDATE _optio_zmembers SET `" . $column . "` = '" . $editval . "' WHERE `id` = " . $id . "";
	mysqli_query($mysqli, $update);
?>