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
$page_title = 'Setup';
$brand_title = 'Login-Setup';
$brand_sub   = 'Passwort setzen oder zurücksetzen';
require __DIR__ . '/partials/head.php';
?>
    <div class="hero"><svg><use href="#i-fp"/></svg></div>

    <?php if ($setupKeyMissing): ?>
      <div class="messageNOK">Setup gesperrt: Bitte zuerst in der Datei config.php einen eigenen
        geheimen Wert für <code>$setup_key</code> eintragen (siehe config.sample.php) und die Datei neu hochladen.</div>
    <?php endif; ?>

    <?php if (!auth_storage_writable()): ?>
      <div class="messageNOK">Der Ordner „auth“ ist für den Webserver nicht beschreibbar.
        Das Passwort kann so nicht gespeichert werden – bitte dem Web-Benutzer (Gruppe „http“)
        Schreibrechte auf den Ordner „auth“ geben.</div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="messageOK"><?php echo htmlspecialchars($success); ?></div>
      <a class="btn mt" href="login.php">Zum Login</a>
    <?php elseif (!$setupKeyMissing): ?>
      <?php if ($error): ?>
        <div class="messageNOK"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <form method="post" action="setup.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
        <?php if (!is_logged_in()): ?>
        <div class="field">
          <label for="sk">Setup-Schlüssel</label>
          <input id="sk" type="password" name="setup_key" autocomplete="off" required />
        </div>
        <?php endif; ?>
        <div class="field">
          <label for="np1">Neues Passwort</label>
          <input id="np1" type="password" name="new_password" autocomplete="new-password" required />
        </div>
        <div class="field">
          <label for="np2">Neues Passwort wiederholen</label>
          <input id="np2" type="password" name="new_password2" autocomplete="new-password" required />
        </div>
        <div class="mt"><button class="btn" type="submit">Passwort speichern</button></div>
      </form>
    <?php endif; ?>

    <div class="spacer"></div>
<?php require __DIR__ . '/partials/foot.php'; ?>
