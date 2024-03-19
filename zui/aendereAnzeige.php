<?php
  if(isset($_POST['mode'])) {
    // Ändere Anzeigemodus
    setcookie('darkmode', $_POST['mode'], time() + (86400 * 30), "/");
  }
?>
