<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/store.php';
require_login();

$data = auth_load();
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <link rel="stylesheet" href="smartphone.css" />
    <title><?php echo htmlspecialchars($sitename); ?> - Passkey</title>
  </head>
  <body>
    <div class="title">Passkey verwalten</div>
    <div class="undertitle">Registriere den Fingerabdruck/Face ID dieses Geräts für den Login.</div>

    <?php if (count($data['credentials']) > 0): ?>
      <hr />
      <div class="normal">Registrierte Passkeys:</div>
      <ul>
        <?php foreach ($data['credentials'] as $cred): ?>
          <li><?php echo htmlspecialchars($cred['name'] ?? 'Unbenannt'); ?> (registriert am <?php echo htmlspecialchars($cred['createdAt'] ?? '?'); ?>)</li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <hr />
    <div class="normal">
      <label>Name für dieses Gerät<br />
        <input type="text" id="deviceName" placeholder="z.B. Mein Smartphone" />
      </label>
    </div>
    <div class="normal">
      <button class="btn" type="button" onclick="waDoRegister()">Passkey für dieses Gerät registrieren</button>
    </div>
    <div id="waStatus" class="normal"></div>

    <hr />
    <div class="normal"><a href="index.php">Zurück</a></div>

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
  </body>
</html>
