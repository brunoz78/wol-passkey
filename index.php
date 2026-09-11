<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/devices.php';
require_once __DIR__ . '/auth/log.php';
require_login();

/*
  PHP Wake on Lan – basierend auf dem Skript von Barry Schiffer / Manuel
  Azevedo (2014, http://www.barryschiffer.com/using-synology-disk-station-wake-lan).
  Erweitert um Login, Passkeys, Geräteverwaltung und Themes.
*/
require __DIR__ . '/wol.php';

$devices = devices_load();

// WOL-Verarbeitung
$wakeLabel    = null; // Name (oder MAC) des aufgeweckten Geräts für die Erfolgsmeldung
$wakeWaitName = null; // gesetzt, wenn der Status danach per JS abgefragt werden kann
$wakeError    = null;
$wakemachine = $_GET['wake_machine'] ?? '';

if ($wakemachine !== '' && $wakemachine !== '-1') {
    if (!csrf_check($_GET['csrf_token'] ?? '')) {
        $wakeError = t('index.csrf');
    } else {
        $wakeName = null;
        $wakeIp   = '';
        foreach ($devices as $name => $dev) {
            if (strcasecmp($dev['mac'], $wakemachine) === 0) {
                $wakeName = (string)$name;
                $wakeIp   = $dev['ip'];
                break;
            }
        }
        $ok = WakeOnLan($networkbroadcast, $wakemachine, $port, $wakeIp);
        wol_log($ok ? 'wake' : 'wake_failed', ['device' => $wakeName ?? $wakemachine, 'ip' => wol_client_ip()]);
        if ($ok) {
            $wakeLabel = $wakeName ?? $wakemachine;
            if ($wakeName !== null && $wakeIp !== '') {
                $wakeWaitName = $wakeName;
            }
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
    <?php elseif ($wakeLabel !== null): ?>
      <div class="messageOK"><?php te('index.wake_sent', $wakeLabel); ?></div>
      <?php if ($wakeWaitName !== null): ?>
        <div id="wakeWait" data-wait-name="<?php echo htmlspecialchars($wakeWaitName, ENT_QUOTES); ?>" hidden></div>
      <?php endif; ?>
    <?php endif; ?>

    <?php if (count($devices) === 0): ?>
      <p class="section-label" style="margin-top:20px"><?php te('index.no_devices'); ?></p>
      <a class="btn mt" href="devices.php"><svg><use href="#i-plus"/></svg><?php te('index.add_device'); ?></a>
    <?php else: ?>
      <form name="WakeOnLan" method="get" action="index.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
        <p class="section-label" style="margin-top:18px"><?php te('index.your_devices'); ?></p>
        <div class="devlist">
          <?php foreach ($devices as $name => $dev): $mac = $dev['mac']; $ip = $dev['ip']; ?>
            <label class="dev"<?php echo $ip !== '' ? ' data-check-name="' . htmlspecialchars($name, ENT_QUOTES) . '"' : ''; ?>>
              <input type="radio" name="wake_machine" value="<?php echo htmlspecialchars($mac, ENT_QUOTES); ?>" required />
              <span class="ic">
                <svg><use href="#i-mon"/></svg>
                <?php if ($ip !== ''): ?><span class="status-dot" aria-hidden="true"></span><?php endif; ?>
              </span>
              <span class="txt">
                <span class="nm"><?php echo htmlspecialchars($name); ?></span>
                <span class="mac"><?php echo htmlspecialchars($mac); ?></span>
                <?php if ($ip !== ''): ?><span class="since" hidden></span><?php endif; ?>
              </span>
              <span class="ind"></span>
            </label>
          <?php endforeach; ?>
        </div>
        <div class="mt"><button class="btn btn-wake" type="submit" id="wakeBtn" disabled><svg><use href="#i-pw"/></svg><?php te('index.wake'); ?></button></div>
      </form>
    <?php endif; ?>

    <div class="spacer"></div>
    <script src="<?php echo asset('assets/device-status.js'); ?>"></script>
    <script src="<?php echo asset('assets/wake-select.js'); ?>"></script>
<?php require __DIR__ . '/partials/foot.php'; ?>
