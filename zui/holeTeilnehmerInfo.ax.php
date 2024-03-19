<?php
  if(isset($_POST['sid']) && is_numeric($_POST['sid']) && $_POST['sid'] > 0) {
    // Lege Zeitzone fest
    date_default_timezone_set("Europe/Berlin");

    // Prüfe, ob Session bereits gestartet wurde
    // PHP Version < 5.4.0
    if (session_id() == '') {
      session_start();
    }
    // PHP Version > 5.4.0, 7
    /*
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }
    */

    // Prüfe auf aktive Session-Parameter
    if(
      isset($_SESSION['eid']) && is_numeric($_SESSION['eid']) && $_SESSION['eid'] > 0 &&
      isset($_SESSION['rid']) && is_numeric($_SESSION['rid']) && $_SESSION['rid'] > 0 &&
      isset($_SESSION['uid']) && is_numeric($_SESSION['uid']) && $_SESSION['uid'] > 0
    ) {
      // Binde Funktionsdateien ein
      include_once '../includes/functions.php';

      // Binde Konfigurationsdatei ein
      include_once '../includes/db_connect.php';

      // Bereinige Übergabeparameter
      $eid = $_SESSION['eid'];
      $rid = $_SESSION['rid'];
      $zid = $_SESSION['uid'];

      // Bereinige Übergabeparameter
      $sid = mysqli_real_escape_string($mysqli, $_POST['sid']);

      // Suche zuerst nach eingegebenen Ergebnissen
      $select = "SELECT `zeitstempel`, `aktion` FROM `_optio_tmembers_event` WHERE `eid` = " . $eid . " AND `rid` = " . $rid . " AND `sid` = " . $sid . " AND `explizit` = 0";
      $result = mysqli_query($mysqli, $select);
      $numrow = mysqli_num_rows($result);

      if($numrow > 0) {
        // Generiere HTML Tabelle
        echo  "
              <div class=\"table-wrapper\">
                <table>
                  <thead>
                    <tr>
                      <th>Zeitstempel</th>
                      <th>Ereignis</th>
                    </tr>
                  </thead>
                  <tbody>
              ";
        while($getrow = mysqli_fetch_assoc($result)) {
          echo  "
                    <tr>
                      <td>" . date('d.m.Y - H:i:s', $getrow['zeitstempel']) . " Uhr</td>
                      <td>" . $getrow['aktion'] . "</td>
                    </tr>
                ";
        }

        echo  "
                  </tbody>
                </table>
              </div>
              ";
      // Keine Ereignisse vorhanden
      } else {
        // Generiere HTML Tabelle
        echo  "
              <div class=\"table-wrapper\">
                <table>
                  <thead>
                    <tr>
                      <th>Derzeit sind keine Ereignisse vorhanden</th>
                    </tr>
                  </thead>
                </table>
              </div>
              ";
      }
    // Keine aktive Session
    } else {
      echo "keineSession";
    }
  }
?>
