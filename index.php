<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/devices.php';
require_login();

/*
  PHP Wake on Lan – basierend auf dem Skript von Barry Schiffer / Manuel
  Azevedo (2014, http://www.barryschiffer.com/using-synology-disk-station-wake-lan).
  Erweitert um Login, Passkeys, Geräteverwaltung und Themes.
*/
require __DIR__ . '/wol.php';

$devices = devices_load();

// WOL-Verarbeitung
$wakeResult = null;
$wakeError  = null;
$wakemachine = $_GET['wake_machine'] ?? '';

if ($wakemachine !== '' && $wakemachine !== '-1') {
    if (!csrf_check($_GET['csrf_token'] ?? '')) {
        $wakeError = t('index.csrf');
    } else {
        ob_start();
        $ok = WakeOnLan($networkbroadcast, $wakemachine, $port);
        $wolOutput = ob_get_clean(); // technische Ausgabe von wol.php (Port/MAC/Daten)
        if ($ok) {
            $wakeResult = $wolOutput;
        } else {
            $wakeError = t('index.wake_failed');
        }
    }
}

$page_title = null;
$brand_sub  = t('index.sub');
$show_menu  = true;
require __DIR__ . '/partials/head.php';
?>
    <?php if ($wakeError !== null): ?>
      <div class="messageNOK"><?php echo htmlspecialchars($wakeError); ?></div>
    <?php elseif ($wakeResult !== null): ?>
      <div class="messageOK"><?php te('index.wake_sent', $wakemachine); ?></div>
    <?php endif; ?>

    <?php if (count($devices) === 0): ?>
      <p class="section-label" style="margin-top:20px"><?php te('index.no_devices'); ?></p>
      <a class="btn mt" href="devices.php"><svg><use href="#i-plus"/></svg><?php te('index.add_device'); ?></a>
    <?php else: ?>
      <form name="WakeOnLan" method="get" action="index.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
        <p class="section-label" style="margin-top:18px"><?php te('index.your_devices'); ?></p>
        <div class="devlist">
          <?php foreach ($devices as $name => $mac): ?>
            <label class="dev">
              <input type="radio" name="wake_machine" value="<?php echo htmlspecialchars($mac, ENT_QUOTES); ?>" required />
              <span class="ic"><svg><use href="#i-mon"/></svg></span>
              <span class="txt">
                <span class="nm"><?php echo htmlspecialchars($name); ?></span>
                <span class="mac"><?php echo htmlspecialchars($mac); ?></span>
              </span>
              <span class="ind"></span>
            </label>
          <?php endforeach; ?>
        </div>
        <div class="mt"><button class="btn btn-wake" type="submit"><svg><use href="#i-pw"/></svg><?php te('index.wake'); ?></button></div>
      </form>
    <?php endif; ?>

    <div class="spacer"></div>
<?php require __DIR__ . '/partials/foot.php'; ?>
