<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/store.php';
require_login();

$data = auth_load();
$page_title = 'Passkey';
$brand_title = 'Passkey verwalten';
$brand_sub   = 'Fingerabdruck / Face ID dieses Geräts';
$show_menu   = true;
require __DIR__ . '/partials/head.php';
?>
    <div class="hero"><svg><use href="#i-fp"/></svg></div>

    <?php if (count($data['credentials']) > 0): ?>
      <p class="section-label">Registrierte Passkeys</p>
      <?php foreach ($data['credentials'] as $cred): ?>
        <div class="item">
          <span class="ic"><svg><use href="#i-fp"/></svg></span>
          <span class="txt grow">
            <span class="nm"><?php echo htmlspecialchars($cred['name'] ?? 'Unbenannt'); ?></span>
            <span class="mac">registriert am <?php echo htmlspecialchars($cred['createdAt'] ?? '?'); ?></span>
          </span>
        </div>
      <?php endforeach; ?>
      <hr />
    <?php endif; ?>

    <div class="field">
      <label for="deviceName">Name für dieses Gerät</label>
      <input type="text" id="deviceName" placeholder="z.B. Mein Smartphone" />
    </div>
    <div class="mt">
      <button class="btn" type="button" onclick="waDoRegister()"><svg><use href="#i-fp"/></svg>Passkey registrieren</button>
    </div>
    <div id="waStatus"></div>

    <div class="spacer"></div>

    <script src="assets/webauthn-client.js"></script>
    <script>
      function waDoRegister() {
        const name = document.getElementById('deviceName').value || 'Unbenanntes Gerät';
        waRegisterPasskey(document.getElementById('waStatus'), name).then(function(ok) {
          if (ok) {
            setTimeout(function() { window.location.reload(); }, 1200);
          }
        });
      }
    </script>
<?php require __DIR__ . '/partials/foot.php'; ?>
