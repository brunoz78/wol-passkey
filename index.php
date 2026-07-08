<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/devices.php';
require_login();
?>
<!DOCTYPE html>
<html>
  <!--

    PHP Wake on Lan

    Version 1.0 - 2014.11.01

    Uses icon from http://www.streamlineicons.com/ Free Pack via https://www.iconfinder.com/icons/185036/remote_control_streamline_icon#size=128
    (c) 2014 Barry Schiffer. Based on code provided by Barry Schiffer at http://www.barryschiffer.com/using-synology-disk-station-wake-lan
    (c) 2014 Manuel Azevedo <azevedo.manuel@gmail.com> 
      * Adapted to iPhone resolution
      * Added image
      * Adapt code
      * Create config.php
      * Add support for multiple mac formats
    
    -->
  <?php include 'wol.php'; ?>
  <head>
    <meta id="viewport" name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <link rel="stylesheet" href="smartphone.css" />
    <title><?php echo htmlspecialchars($sitename); ?></title>
  </head>
  <body>
    <div class="title"><?php echo htmlspecialchars($sitename); ?></div>
    <div class="undertitle">Sendet ein Magic Packet Signal an das angegebene Zielgerät.</div>
    <div class="logo"><img src="remote.png" alt="PHP Wake on Lan" /></div>
    <?php
      $result = null;

      $wakemachine = $_GET["wake_machine"] ?? "";

      if($wakemachine != "" && $wakemachine != "-1") {
        if (!csrf_check($_GET['csrf_token'] ?? '')) {
          echo "<div class=\"messageNOK\">Ungültige Anfrage, bitte Formular erneut absenden.</div>\n<hr />\n";
        } else {
          $result = WakeOnLan($networkbroadcast, $wakemachine, $port);
        }
      }

      if($result != null)
        echo "<div class=\"messageOK\">WOL für ".$wakemachine." war erfolgreich!</div>\n<hr />\n";
    ?>
    <form name="WakeOnLan" method="get" action="index.php">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
      <div class="normal">
        <label for="WakeOnLan" class="label">Bitte Zielgerät auswählen.<br /></label>
      </div>
      <div class="normal">
        <select name="wake_machine" id="WakeOnLan">
          <option id="select" value="-1">klick mich</option><?php PopulateMACList(devices_load()); ?>
        </select>
        <input id="submit" type="submit" value="Aufwecken" />
      </div>
    </form>
    <div class="normal">
      <a href="devices.php">Geräte verwalten</a> · <a href="register-passkey.php">Passkey verwalten</a> · <a href="logout.php">Abmelden</a>
    </div>
  </body>
</html>
