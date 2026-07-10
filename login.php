<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/store.php';

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
    } elseif (!empty($data['locked_until']) && $data['locked_until'] > time()) {
        $wait = $data['locked_until'] - time();
        $error = t('login.locked', (int)ceil($wait / 60));
    } elseif ($noPasswordSet) {
        $error = t('login.no_password');
    } elseif (password_verify($_POST['password'], $data['password_hash'])) {
        $data['failed_attempts'] = 0;
        $data['locked_until'] = 0;
        auth_save($data);
        $_SESSION['authenticated'] = true;
        session_regenerate_id(true);
        header('Location: index.php');
        exit;
    } else {
        $data['failed_attempts'] = (int)$data['failed_attempts'] + 1;
        if ($data['failed_attempts'] >= AUTH_MAX_ATTEMPTS) {
            $data['locked_until'] = time() + AUTH_LOCKOUT_SECONDS;
            $data['failed_attempts'] = 0;
        }
        auth_save($data);
        $error = t('login.wrong_password');
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

    <script src="assets/webauthn-client.js"></script>
    <?php if ($_SERVER['REQUEST_METHOD'] === 'GET'): ?>
    <script>
      // Automatisch nach dem Passkey fragen, wenn dieses Gerät schon einen hat.
      // Nur beim normalen Seitenaufruf - nicht nach einem Passwort-Fehlversuch.
      waAutoLoginIfKnownDevice(document.getElementById('waStatus'));
    </script>
    <?php endif; ?>
<?php require __DIR__ . '/partials/foot.php'; ?>
