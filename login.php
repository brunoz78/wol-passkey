<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/store.php';
require_once __DIR__ . '/auth/log.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = null;
$data = auth_load();
$noPasswordSet = empty($data['password_hash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error = t('login.csrf');
    } else {
        /*
          Sperre prüfen, Passwort vergleichen und den Zähler fortschreiben
          geschieht gemeinsam unter der Dateisperre (auth_update). Vorher lief
          das über auth_load() + auth_save(): Gleichzeitig abgeschickte
          Versuche lasen alle denselben Zählerstand und schrieben denselben
          erhöhten Wert zurück - die Sperre nach AUTH_MAX_ATTEMPTS Versuchen
          liess sich so durch Parallelität umgehen. Der langsame
          password_verify()-Aufruf liegt bewusst mit in der Sperre: Damit
          bringt paralleles Ausprobieren keinen Zeitvorteil mehr.

          Sitzung, Protokoll und Weiterleitung bleiben ausserhalb - innerhalb
          der Sperre wird nur gerechnet, nicht umgeleitet.
        */
        $outcome = 'save_failed';
        $waitMinutes = 0;
        // "password[]=x" kommt als Array an; password_verify() bräche dann
        // mit einem Fehler ab, bevor der Versuch gezählt ist. So zählt er
        // als gewöhnlicher Fehlversuch.
        $password = is_string($_POST['password']) ? $_POST['password'] : '';

        $ok = auth_update(function ($data) use ($password, &$outcome, &$waitMinutes) {
            if (!empty($data['locked_until']) && $data['locked_until'] > time()) {
                $outcome = 'locked';
                $waitMinutes = (int)ceil(($data['locked_until'] - time()) / 60);
                return null; // nichts schreiben, Sperre läuft bereits
            }
            if (empty($data['password_hash'])) {
                $outcome = 'no_password';
                return null;
            }
            if (password_verify($password, $data['password_hash'])) {
                $outcome = 'ok';
                $data['failed_attempts'] = 0;
                $data['locked_until'] = 0;
                return $data;
            }

            $data['failed_attempts'] = (int)$data['failed_attempts'] + 1;
            if ($data['failed_attempts'] >= AUTH_MAX_ATTEMPTS) {
                $data['locked_until'] = time() + AUTH_LOCKOUT_SECONDS;
                $data['failed_attempts'] = 0;
                $outcome = 'locked_now';
                $waitMinutes = (int)ceil(AUTH_LOCKOUT_SECONDS / 60);
            } else {
                $outcome = 'wrong';
            }
            return $data;
        });

        // Schlug das Schreiben fehl (auth/ nicht beschreibbar), darf ein
        // falsches Passwort nicht trotzdem hereinlassen - der Zähler wäre
        // dann wirkungslos. Nur der Erfolgsfall ohne Schreibrecht wird
        // durchgelassen, damit eine Nur-Lese-Installation nutzbar bleibt.
        if (!$ok && $outcome !== 'ok') {
            $outcome = $outcome === 'locked' || $outcome === 'no_password' ? $outcome : 'wrong';
        }

        switch ($outcome) {
            case 'ok':
                $_SESSION['authenticated'] = true;
                session_regenerate_id(true);
                wol_log('login', ['method' => 'password', 'ip' => wol_client_ip()]);
                header('Location: index.php');
                exit;
            case 'locked':
                $error = t('login.locked', $waitMinutes);
                break;
            case 'no_password':
                $error = t('login.no_password');
                break;
            case 'locked_now':
                wol_log('login_failed', ['ip' => wol_client_ip()]);
                wol_log('login_locked', ['ip' => wol_client_ip(), 'minutes' => $waitMinutes]);
                $error = t('login.wrong_password');
                break;
            default:
                wol_log('login_failed', ['ip' => wol_client_ip()]);
                $error = t('login.wrong_password');
        }
    }
}
$page_title = t('login.title');
$brand_sub  = t('login.sub');
require __DIR__ . '/partials/head.php';
?>
    <div class="hero"><svg><use href="#i-fp"/></svg></div>

    <?php if ($error): ?>
      <div class="messageNOK"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <button class="btn" type="button" onclick="waLoginWithPasskey(document.getElementById('waStatus'))">
      <svg><use href="#i-fp"/></svg><?php te('login.with_passkey'); ?>
    </button>
    <label class="autoask" id="waAutoAsk" hidden>
      <input type="checkbox" id="waAutoAskBox" />
      <span><?php te('login.auto_ask'); ?></span>
    </label>
    <div id="waStatus"></div>

    <div class="divider"><?php te('login.or_password'); ?></div>

    <form method="post" action="login.php">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
      <div class="field">
        <label for="pw"><?php te('login.password'); ?></label>
        <input id="pw" type="password" name="password" autocomplete="current-password" autofocus required />
      </div>
      <div class="mt"><button class="btn btn-ghost" type="submit"><?php te('login.submit'); ?></button></div>
    </form>

    <div class="spacer"></div>

    <script src="<?php echo asset('assets/webauthn-client.js'); ?>"></script>
    <script>
      // Kästchen für die automatische Abfrage einblenden - nur auf Geräten,
      // die überhaupt schon einen Passkey benutzt haben.
      waInitAutoAsk();
    </script>
    <?php if ($_SERVER['REQUEST_METHOD'] === 'GET'): ?>
    <script>
      // Automatisch nach dem Passkey fragen, wenn dieses Gerät schon einen hat
      // und die Abfrage nicht abgeschaltet wurde. Nur beim normalen
      // Seitenaufruf - nicht nach einem Passwort-Fehlversuch.
      waAutoLoginIfKnownDevice(document.getElementById('waStatus'));

      // Und noch einmal, wenn der Browser die Seite später nur wieder in den
      // Vordergrund holt, ohne sie neu zu laden.
      waWatchReturn(document.getElementById('waStatus'));
    </script>
    <?php endif; ?>
<?php require __DIR__ . '/partials/foot.php'; ?>
