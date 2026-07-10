<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/store.php';
require_login();

$data = auth_load();
$page_title  = t('passkey.title');
$brand_title = t('passkey.brand');
$brand_sub   = t('passkey.sub');
$show_menu   = true;
require __DIR__ . '/partials/head.php';
?>
    <div class="hero"><svg><use href="#i-fp"/></svg></div>

    <?php if (count($data['credentials']) > 0): ?>
      <p class="section-label"><?php te('passkey.registered'); ?></p>
      <?php foreach ($data['credentials'] as $cred): ?>
        <div class="item">
          <span class="ic"><svg><use href="#i-fp"/></svg></span>
          <span class="txt grow">
            <span class="nm"><?php echo htmlspecialchars($cred['name'] ?? t('passkey.unnamed')); ?></span>
            <span class="mac"><?php te('passkey.created', $cred['createdAt'] ?? '?'); ?></span>
          </span>
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
