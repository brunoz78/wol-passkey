<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/store.php';
require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error = t('passkey.csrf');
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $data = auth_load();
        $id = $_POST['credential_id'] ?? '';
        $name = t('passkey.unnamed');
        $found = false;

        foreach ($data['credentials'] as $i => $cred) {
            if (($cred['id'] ?? '') === $id) {
                $name = $cred['name'] ?? $name;
                unset($data['credentials'][$i]);
                $found = true;
                break;
            }
        }

        if (!$found) {
            $error = t('passkey.not_found');
        } else {
            $data['credentials'] = array_values($data['credentials']);
            if (auth_save($data)) {
                $success = t('passkey.removed', $name);
            } else {
                $error = t('passkey.save_failed');
            }
        }
    }
}

$data = auth_load();
$page_title  = t('passkey.title');
$brand_title = t('passkey.brand');
$brand_sub   = t('passkey.sub');
$show_menu   = true;
require __DIR__ . '/partials/head.php';
?>
    <div class="hero"><svg><use href="#i-fp"/></svg></div>

    <?php if (!auth_storage_writable()): ?>
      <div class="messageNOK"><?php te('passkey.not_writable'); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="messageNOK"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
      <div class="messageOK"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (count($data['credentials']) > 0): ?>
      <p class="section-label"><?php te('passkey.registered'); ?></p>
      <?php foreach ($data['credentials'] as $cred): $credName = $cred['name'] ?? t('passkey.unnamed'); ?>
        <div class="item">
          <span class="ic"><svg><use href="#i-fp"/></svg></span>
          <span class="txt grow">
            <span class="nm"><?php echo htmlspecialchars($credName); ?></span>
            <span class="mac"><?php te('passkey.created', $cred['createdAt'] ?? '?'); ?></span>
          </span>
          <form method="post" action="register-passkey.php"
                onsubmit="return confirm(<?php echo htmlspecialchars(json_encode(t('passkey.confirm', $credName)), ENT_QUOTES); ?>);">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
            <input type="hidden" name="action" value="delete" />
            <input type="hidden" name="credential_id" value="<?php echo htmlspecialchars($cred['id'] ?? '', ENT_QUOTES); ?>" />
            <button class="icon-btn" type="submit" aria-label="<?php te('passkey.remove'); ?>" title="<?php te('passkey.remove'); ?>"><svg><use href="#i-trash"/></svg></button>
          </form>
        </div>
      <?php endforeach; ?>
      <hr />
    <?php endif; ?>

    <div class="field">
      <label for="deviceName"><?php te('passkey.device_name'); ?></label>
      <input type="text" id="deviceName" placeholder="<?php te('passkey.device_name_ph'); ?>" />
    </div>
    <div class="mt">
      <button class="btn" type="button" onclick="waDoRegister()"><svg><use href="#i-fp"/></svg><?php te('passkey.register'); ?></button>
    </div>
    <div id="waStatus"></div>

    <div class="spacer"></div>

    <script src="assets/webauthn-client.js"></script>
    <script>
      const waDefaultDeviceName = <?php echo json_encode(t('passkey.default_device')); ?>;
      function waDoRegister() {
        const name = document.getElementById('deviceName').value || waDefaultDeviceName;
        waRegisterPasskey(document.getElementById('waStatus'), name).then(function(ok) {
          if (ok) {
            setTimeout(function() { window.location.reload(); }, 1200);
          }
        });
      }
    </script>
<?php require __DIR__ . '/partials/foot.php'; ?>
