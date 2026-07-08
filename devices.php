<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/devices.php';
require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültige Anfrage, bitte erneut versuchen.';
    } else {
        $devices = devices_load();
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $name = trim($_POST['device_name'] ?? '');
            $mac = devices_normalize_mac(trim($_POST['device_mac'] ?? ''));

            // preg_match('//u', ...) prüft UTF-8-Gültigkeit ohne mbstring
            if ($name === '' || preg_match('//u', $name) !== 1) {
                $error = 'Bitte einen Gerätenamen angeben.';
            } elseif (strlen($name) > 40) {
                $error = 'Der Gerätename darf höchstens 40 Zeichen lang sein.';
            } elseif ($mac === null) {
                $error = 'Die MAC-Adresse ist ungültig. Erlaubt sind 12 Hex-Zeichen, z.B. 00:11:22:33:44:55.';
            } elseif (isset($devices[$name])) {
                $error = 'Ein Gerät mit diesem Namen existiert bereits. Bitte zuerst entfernen oder anderen Namen wählen.';
            } else {
                $devices[$name] = $mac;
                if (devices_save($devices)) {
                    $success = 'Gerät "' . $name . '" hinzugefügt.';
                } else {
                    $error = 'Speichern fehlgeschlagen: Der Ordner "auth" ist für den Webserver nicht beschreibbar.';
                }
            }
        } elseif ($action === 'delete') {
            $name = $_POST['device_name'] ?? '';
            if (!isset($devices[$name])) {
                $error = 'Gerät nicht gefunden.';
            } else {
                unset($devices[$name]);
                if (devices_save($devices)) {
                    $success = 'Gerät "' . $name . '" entfernt.';
                } else {
                    $error = 'Speichern fehlgeschlagen: Der Ordner "auth" ist für den Webserver nicht beschreibbar.';
                }
            }
        }
    }
}

$devices = devices_load();
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <link rel="stylesheet" href="smartphone.css" />
    <title><?php echo htmlspecialchars($sitename); ?> - Geräte</title>
  </head>
  <body>
    <div class="title">Geräte verwalten</div>
    <div class="undertitle">Zielgeräte für Wake on LAN hinzufügen oder entfernen.</div>

    <?php if (!devices_storage_writable()): ?>
      <div class="messageNOK">Achtung: Der Ordner "auth" ist für den Webserver nicht beschreibbar.
        Änderungen können so nicht gespeichert werden.</div>
      <hr />
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="messageNOK"><?php echo htmlspecialchars($error); ?></div>
      <hr />
    <?php elseif ($success): ?>
      <div class="messageOK"><?php echo htmlspecialchars($success); ?></div>
      <hr />
    <?php endif; ?>

    <?php if (count($devices) === 0): ?>
      <div class="normal">Noch keine Geräte eingetragen.</div>
    <?php else: ?>
      <?php foreach ($devices as $name => $mac): ?>
        <div class="device-row">
          <div class="device-info">
            <?php echo htmlspecialchars($name); ?><br />
            <span class="device-mac"><?php echo htmlspecialchars($mac); ?></span>
          </div>
          <form method="post" action="devices.php"
                onsubmit="return confirm('Gerät &quot;<?php echo htmlspecialchars($name, ENT_QUOTES); ?>&quot; wirklich entfernen?');">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
            <input type="hidden" name="action" value="delete" />
            <input type="hidden" name="device_name" value="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>" />
            <button class="btn-small" type="submit">Entfernen</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <hr />
    <form method="post" action="devices.php">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
      <input type="hidden" name="action" value="add" />
      <div class="normal">
        <label>Gerätename<br />
          <input type="text" name="device_name" maxlength="40" placeholder="z.B. Wohnzimmer-PC" required />
        </label>
      </div>
      <div class="normal">
        <label>MAC-Adresse<br />
          <input type="text" name="device_mac" placeholder="00:11:22:33:44:55" required />
        </label>
      </div>
      <div class="normal">
        <button class="btn" type="submit">Gerät hinzufügen</button>
      </div>
    </form>

    <hr />
    <div class="normal"><a href="index.php">Zurück</a></div>
  </body>
</html>
