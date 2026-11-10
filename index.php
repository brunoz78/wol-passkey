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

/*
  Aufwecken läuft per POST und leitet danach auf die Seite zurück. Vorher
  stand alles in der Adresse (GET): Der CSRF-Token landete damit im
  Zugriffsprotokoll von Webserver und Proxy und im Browserverlauf, und ein
  Neuladen der Seite weckte das Gerät ein zweites Mal. Das Ergebnis wandert
  für die eine Anzeige nach der Weiterleitung über die Sitzung.
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wakemachine = is_string($_POST['wake_machine'] ?? null) ? $_POST['wake_machine'] : '';
    $flash = ['label' => null, 'wait' => null, 'error' => null];

    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $flash['error'] = t('index.csrf');
    } else {
        // Nur eingetragene Geräte - die Auswahl kommt aus der eigenen Liste.
        $wakeName = null;
        $wakeIp   = '';
        foreach ($devices as $name => $dev) {
            if ($wakemachine !== '' && strcasecmp($dev['mac'], $wakemachine) === 0) {
                $wakeName = (string)$name;
                $wakeIp   = $dev['ip'];
                break;
            }
        }
        if ($wakeName === null) {
            $flash['error'] = t('index.wake_failed');
        } else {
            $ok = WakeOnLan($networkbroadcast, $devices[$wakeName]['mac'], $port, $wakeIp);
            wol_log($ok ? 'wake' : 'wake_failed', ['device' => $wakeName, 'ip' => wol_client_ip()]);
            if ($ok) {
                $flash['label'] = $wakeName;
                $flash['wait'] = $wakeIp !== '' ? $wakeName : null;
            } else {
                $flash['error'] = t('index.wake_failed');
            }
        }
    }

    $_SESSION['wake_flash'] = $flash;
    header('Location: index.php'); // relativ, siehe Reverse-Proxy-Hinweis in CLAUDE.md
    exit;
}

if (isset($_SESSION['wake_flash']) && is_array($_SESSION['wake_flash'])) {
    $wakeLabel    = $_SESSION['wake_flash']['label'] ?? null;
    $wakeWaitName = $_SESSION['wake_flash']['wait'] ?? null;
    $wakeError    = $_SESSION['wake_flash']['error'] ?? null;
    unset($_SESSION['wake_flash']);
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
      <form name="WakeOnLan" method="post" action="index.php">
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
