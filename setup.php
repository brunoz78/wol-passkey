<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/store.php';

$error = null;
$success = null;

// Solange in config.php kein eigener Setup-Schlüssel gesetzt wurde, ist das
// Setup gesperrt - sonst könnte jeder Besucher das Passwort einer frischen
// Installation setzen.
$setupKeyMissing = (SETUP_KEY === '' || SETUP_KEY === SETUP_KEY_PLACEHOLDER);

if (!$setupKeyMissing && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültige Anfrage, bitte Seite neu laden.';
    } else {
        $setupKey = $_POST['setup_key'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $newPassword2 = $_POST['new_password2'] ?? '';

        $allowed = is_logged_in() || (SETUP_KEY !== '' && hash_equals(SETUP_KEY, $setupKey));

        if (!$allowed) {
            $error = 'Setup-Schlüssel ist falsch.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
        } elseif ($newPassword !== $newPassword2) {
            $error = 'Die beiden Passwörter stimmen nicht überein.';
        } else {
            $data = auth_load();
            $data['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
            $data['failed_attempts'] = 0;
            $data['locked_until'] = 0;
            if (auth_save($data)) {
                $success = 'Passwort erfolgreich gesetzt. Du kannst dich jetzt einloggen.';
            } else {
                $error = 'Speichern fehlgeschlagen: Der Ordner "auth" ist für den Webserver nicht beschreibbar. '
                       . 'Bitte dem Web-Benutzer (Gruppe "http") Schreibrechte auf den Ordner geben und erneut versuchen.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <link rel="stylesheet" href="smartphone.css" />
    <title><?php echo htmlspecialchars($sitename); ?> - Setup</title>
  </head>
  <body>
    <div class="title">Login-Setup</div>
    <div class="undertitle">Passwort setzen oder zurücksetzen.</div>

    <?php if ($setupKeyMissing): ?>
      <div class="messageNOK">Setup gesperrt: Bitte zuerst in der Datei config.php einen eigenen
        geheimen Wert für $setup_key eintragen (siehe config.sample.php) und die Datei neu hochladen.</div>
      <hr />
    <?php endif; ?>

    <?php if (!auth_storage_writable()): ?>
      <div class="messageNOK">Achtung: Der Ordner "auth" ist für den Webserver nicht beschreibbar.
        Das Passwort kann so nicht gespeichert werden. Bitte in der File Station der Gruppe "http"
        Lese-/Schreibrechte auf den Ordner "auth" geben.</div>
      <hr />
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="messageOK"><?php echo htmlspecialchars($success); ?></div>
      <hr />
      <div class="normal"><a href="login.php">Zum Login</a></div>
    <?php elseif (!$setupKeyMissing): ?>
      <?php if ($error): ?>
        <div class="messageNOK"><?php echo htmlspecialchars($error); ?></div>
        <hr />
      <?php endif; ?>
      <form method="post" action="setup.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
        <?php if (!is_logged_in()): ?>
        <div class="normal">
          <label>Setup-Schlüssel<br />
            <input type="password" name="setup_key" autocomplete="off" required />
          </label>
        </div>
        <?php endif; ?>
        <div class="normal">
          <label>Neues Passwort<br />
            <input type="password" name="new_password" autocomplete="new-password" required />
          </label>
        </div>
        <div class="normal">
          <label>Neues Passwort wiederholen<br />
            <input type="password" name="new_password2" autocomplete="new-password" required />
          </label>
        </div>
        <div class="normal">
          <input id="submit" type="submit" value="Passwort speichern" />
        </div>
      </form>
    <?php endif; ?>
  </body>
</html>
