<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/store.php';
require_once __DIR__ . '/auth/log.php';

$error = null;
$success = null;

// Solange in config.php kein eigener Setup-Schlüssel gesetzt wurde, ist das
// Setup gesperrt - sonst könnte jeder Besucher das Passwort einer frischen
// Installation setzen.
$setupKeyMissing = (SETUP_KEY === '' || SETUP_KEY === SETUP_KEY_PLACEHOLDER);

if (!$setupKeyMissing && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error = t('setup.csrf');
    } else {
        // Arrays ("setup_key[]=x") wie leere Eingaben behandeln, siehe login.php.
        $text = function ($name) {
            return is_string($_POST[$name] ?? null) ? $_POST[$name] : '';
        };
        $setupKey = $text('setup_key');
        $newPassword = $text('new_password');
        $newPassword2 = $text('new_password2');
        $loggedIn = is_logged_in();

        /*
          Der Setup-Schlüssel ist passwortäquivalent: Wer ihn kennt, setzt ohne
          Anmeldung ein neues Passwort. Deshalb gilt hier dieselbe Sperre wie
          beim Login, und Fehlversuche landen im Verlauf - vorher konnte man
          unbegrenzt und spurlos raten. Prüfung und Zähler laufen gemeinsam
          unter der Dateisperre, sonst liesse sich die Sperre durch parallele
          Versuche umgehen (siehe auth_update in auth/store.php).
        */
        $outcome = 'save_failed';
        $waitMinutes = 0;

        $ok = auth_update(function ($data) use ($loggedIn, $setupKey, $newPassword, $newPassword2, &$outcome, &$waitMinutes) {
            if (!$loggedIn) {
                if (!empty($data['setup_locked_until']) && $data['setup_locked_until'] > time()) {
                    $outcome = 'locked';
                    $waitMinutes = (int)ceil(($data['setup_locked_until'] - time()) / 60);
                    return null;
                }
                if (SETUP_KEY === '' || !hash_equals(SETUP_KEY, $setupKey)) {
                    $data['setup_failed_attempts'] = (int)$data['setup_failed_attempts'] + 1;
                    if ($data['setup_failed_attempts'] >= AUTH_MAX_ATTEMPTS) {
                        $data['setup_locked_until'] = time() + AUTH_LOCKOUT_SECONDS;
                        $data['setup_failed_attempts'] = 0;
                        $outcome = 'locked_now';
                        $waitMinutes = (int)ceil(AUTH_LOCKOUT_SECONDS / 60);
                    } else {
                        $outcome = 'key_wrong';
                    }
                    return $data;
                }
            }

            // Schlüssel stimmt (oder angemeldet): Passwortregeln prüfen.
            if (strlen($newPassword) < 8) {
                $outcome = 'pw_too_short';
                return null;
            }
            if ($newPassword !== $newPassword2) {
                $outcome = 'pw_mismatch';
                return null;
            }

            $data['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
            $data['failed_attempts'] = 0;
            $data['locked_until'] = 0;
            $data['setup_failed_attempts'] = 0;
            $data['setup_locked_until'] = 0;
            $outcome = 'ok';
            return $data;
        });

        switch ($outcome) {
            case 'ok':
                if ($ok) {
                    $success = t('setup.saved');
                    wol_log('password_set', ['ip' => wol_client_ip()]);
                } else {
                    $error = t('setup.save_failed');
                }
                break;
            case 'locked':
                $error = t('setup.too_many', $waitMinutes);
                break;
            case 'locked_now':
                wol_log('setup_failed', ['ip' => wol_client_ip()]);
                wol_log('setup_locked', ['ip' => wol_client_ip(), 'minutes' => $waitMinutes]);
                $error = t('setup.key_wrong');
                break;
            case 'key_wrong':
                wol_log('setup_failed', ['ip' => wol_client_ip()]);
                $error = t('setup.key_wrong');
                break;
            case 'pw_too_short':
                $error = t('setup.pw_too_short');
                break;
            case 'pw_mismatch':
                $error = t('setup.pw_mismatch');
                break;
            default:
                $error = t('setup.save_failed');
        }
    }
}
$page_title  = t('setup.title');
$brand_title = t('setup.brand');
$brand_sub   = t('setup.sub');
require __DIR__ . '/partials/head.php';
?>
    <div class="hero"><svg><use href="#i-fp"/></svg></div>

    <?php if ($setupKeyMissing): ?>
      <?php /* {key} wird nach dem Escapen durch das <code>-Element ersetzt. */ ?>
      <div class="messageNOK"><?php
        echo str_replace('{key}', '<code>$setup_key</code>', htmlspecialchars(t('setup.locked')));
      ?></div>
    <?php endif; ?>

    <?php if (!auth_storage_writable()): ?>
      <div class="messageNOK"><?php te('setup.not_writable'); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="messageOK"><?php echo htmlspecialchars($success); ?></div>
      <a class="btn mt" href="login.php"><?php te('setup.to_login'); ?></a>
    <?php elseif (!$setupKeyMissing): ?>
      <?php if ($error): ?>
        <div class="messageNOK"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <form method="post" action="setup.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
        <?php if (!is_logged_in()): ?>
        <div class="field">
          <label for="sk"><?php te('setup.key_label'); ?></label>
          <input id="sk" type="password" name="setup_key" autocomplete="off" required />
        </div>
        <?php endif; ?>
        <div class="field">
          <label for="np1"><?php te('setup.new_pw'); ?></label>
          <input id="np1" type="password" name="new_password" autocomplete="new-password" required />
        </div>
        <div class="field">
          <label for="np2"><?php te('setup.new_pw2'); ?></label>
          <input id="np2" type="password" name="new_password2" autocomplete="new-password" required />
        </div>
        <div class="mt"><button class="btn" type="submit"><?php te('setup.save'); ?></button></div>
      </form>
    <?php endif; ?>

    <div class="spacer"></div>
<?php require __DIR__ . '/partials/foot.php'; ?>
