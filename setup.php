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
        $error = t('setup.csrf');
    } else {
        $setupKey = $_POST['setup_key'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $newPassword2 = $_POST['new_password2'] ?? '';

        $allowed = is_logged_in() || (SETUP_KEY !== '' && hash_equals(SETUP_KEY, $setupKey));

        if (!$allowed) {
            $error = t('setup.key_wrong');
        } elseif (strlen($newPassword) < 8) {
            $error = t('setup.pw_too_short');
        } elseif ($newPassword !== $newPassword2) {
            $error = t('setup.pw_mismatch');
        } else {
            $data = auth_load();
            $data['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
            $data['failed_attempts'] = 0;
            $data['locked_until'] = 0;
            if (auth_save($data)) {
                $success = t('setup.saved');
            } else {
                $error = t('setup.save_failed');
            }
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
