<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/devices.php';
require_once __DIR__ . '/auth/status.php';
require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error = t('devices.csrf');
    } else {
        $devices = devices_load();
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $name = trim($_POST['device_name'] ?? '');
            $mac = devices_normalize_mac(trim($_POST['device_mac'] ?? ''));
            $ip = devices_normalize_ip($_POST['device_ip'] ?? '');

            // preg_match('//u', ...) prüft UTF-8-Gültigkeit ohne mbstring
            if ($name === '' || preg_match('//u', $name) !== 1) {
                $error = t('devices.name_required');
            } elseif (strlen($name) > 40) {
                $error = t('devices.name_too_long');
            } elseif ($mac === null) {
                $error = t('devices.mac_invalid');
            } elseif ($ip === null) {
                $error = t('devices.ip_invalid');
            } elseif (isset($devices[$name])) {
                $error = t('devices.exists');
            } else {
                $devices[$name] = ['mac' => $mac, 'ip' => $ip];
                if (devices_save($devices)) {
                    $success = t('devices.added', $name);
                    wol_log('device_added', ['device' => $name, 'ip' => wol_client_ip()]);
                } else {
                    $error = t('devices.save_failed');
                }
            }
        } elseif ($action === 'delete') {
            $name = $_POST['device_name'] ?? '';
            if (!isset($devices[$name])) {
                $error = t('devices.not_found');
            } else {
                unset($devices[$name]);
                if (devices_save($devices)) {
                    $success = t('devices.removed', $name);
                    status_forget($name);
                    wol_log('device_removed', ['device' => $name, 'ip' => wol_client_ip()]);
                } else {
                    $error = t('devices.save_failed');
                }
            }
        } elseif ($action === 'update') {
            $old = (string)($_POST['device_name'] ?? '');
            $name = trim($_POST['new_name'] ?? '');
            $mac = devices_normalize_mac(trim($_POST['device_mac'] ?? ''));
            $ip = devices_normalize_ip($_POST['device_ip'] ?? '');

            if (!isset($devices[$old])) {
                $error = t('devices.not_found');
            } elseif ($name === '' || preg_match('//u', $name) !== 1) {
                $error = t('devices.name_required');
            } elseif (strlen($name) > 40) {
                $error = t('devices.name_too_long');
            } elseif ($mac === null) {
                $error = t('devices.mac_invalid');
            } elseif ($ip === null) {
                $error = t('devices.ip_invalid');
            } elseif ($name !== $old && isset($devices[$name])) {
                $error = t('devices.exists');
            } else {
                // Der Gerätename ist der Schlüssel der Liste. Beim Umbenennen
                // wird sie deshalb neu aufgebaut, damit das Gerät an seiner
                // Position bleibt (unset + neuer Eintrag würde es ans Ende
                // schieben). Zeitpläne hängen am Eintrag und wandern mit.
                $updated = [];
                foreach ($devices as $key => $dev) {
                    if ($key === $old) {
                        $dev['mac'] = $mac;
                        $dev['ip'] = $ip;
                        $updated[$name] = $dev;
                    } else {
                        $updated[$key] = $dev;
                    }
                }
                if (!devices_save($updated)) {
                    $error = t('devices.save_failed');
                } elseif ($name !== $old) {
                    status_rename($old, $name);
                    $success = t('devices.renamed', $old, $name);
                    wol_log('device_renamed', ['device' => $old, 'name' => $name, 'ip' => wol_client_ip()]);
                } else {
                    $success = t('devices.changed', $name);
                    wol_log('device_changed', ['device' => $name, 'ip' => wol_client_ip()]);
                }
            }
        } elseif ($action === 'reorder') {
            $order = $_POST['order'] ?? [];
            // Sicherheits-Gegenprobe: die gepostete Reihenfolge muss exakt dieselbe
            // Menge an Namen enthalten wie aktuell gespeichert (z.B. falls in einem
            // anderen Tab zwischenzeitlich ein Gerät gelöscht wurde) - sonst lieber
            // nichts speichern statt Geräte zu verlieren.
            $current = array_keys($devices);
            $posted = is_array($order) ? array_values($order) : [];
            sort($current);
            $postedSorted = $posted;
            sort($postedSorted);
            if ($posted === [] || $current !== $postedSorted) {
                $error = t('devices.reorder_failed');
            } else {
                $reordered = [];
                foreach ($posted as $k) {
                    $reordered[$k] = $devices[$k];
                }
                if (devices_save($reordered)) {
                    $devices = $reordered;
                } else {
                    $error = t('devices.save_failed');
                }
            }
        }
    }
}

$devices = devices_load();
$page_title  = t('devices.title');
$brand_title = t('devices.brand');
$brand_sub   = t('devices.sub');
$show_menu   = true;
require __DIR__ . '/partials/head.php';
?>
    <?php if (!devices_storage_writable()): ?>
      <div class="messageNOK"><?php te('devices.not_writable'); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="messageNOK"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
      <div class="messageOK"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (count($devices) === 0): ?>
      <p class="section-label" style="margin-top:16px"><?php te('devices.none'); ?></p>
    <?php else: ?>
      <p class="section-label" style="margin-top:16px"><?php te('devices.your_devices'); ?></p>
      <div class="devlist-manage" id="deviceList" data-csrf="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES); ?>">
      <?php foreach ($devices as $name => $dev): $mac = $dev['mac']; $ip = $dev['ip']; $id = 'd' . md5($name); ?>
        <details class="item foldable" data-name="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>">
          <summary>
            <span class="drag-handle" aria-label="<?php te('devices.reorder'); ?>" title="<?php te('devices.reorder'); ?>"><svg><use href="#i-grip"/></svg></span>
            <span class="ic"><svg><use href="#i-mon"/></svg></span>
            <span class="txt grow">
              <span class="nm"><?php echo htmlspecialchars($name); ?></span>
              <span class="mac"><?php echo htmlspecialchars($mac); ?></span>
            </span>
            <svg class="fold-chev"><use href="#i-chevron"/></svg>
          </summary>

          <form class="fold-edit" method="post" action="devices.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="device_name" value="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>" />
            <label class="label" for="<?php echo $id; ?>n"><?php te('devices.name'); ?></label>
            <input id="<?php echo $id; ?>n" type="text" name="new_name" maxlength="40"
                   value="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>" required />
            <label class="label" for="<?php echo $id; ?>m"><?php te('devices.mac'); ?></label>
            <input id="<?php echo $id; ?>m" type="text" name="device_mac" placeholder="00:11:22:33:44:55"
                   value="<?php echo htmlspecialchars($mac, ENT_QUOTES); ?>" required />
            <label class="label" for="<?php echo $id; ?>i"><?php te('devices.ip'); ?></label>
            <input id="<?php echo $id; ?>i" type="text" name="device_ip"
                   value="<?php echo htmlspecialchars($ip, ENT_QUOTES); ?>" placeholder="<?php te('devices.ip_ph'); ?>" />
            <div class="fold-actions">
              <button class="icon-btn" type="submit"><svg><use href="#i-check"/></svg><?php te('devices.save'); ?></button>
            </div>
          </form>

          <?php /* json_encode liefert ein gültiges JS-String-Literal, auch bei Anführungszeichen im Namen. */ ?>
          <form class="fold-del" method="post" action="devices.php"
                onsubmit="return confirm(<?php echo htmlspecialchars(json_encode(t('devices.confirm', $name)), ENT_QUOTES); ?>);">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
            <input type="hidden" name="action" value="delete" />
            <input type="hidden" name="device_name" value="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>" />
            <button class="icon-btn" type="submit"><svg><use href="#i-trash"/></svg><?php te('devices.remove'); ?></button>
          </form>
        </details>
      <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <hr />
    <form method="post" action="devices.php">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
      <input type="hidden" name="action" value="add" />
      <p class="section-label"><?php te('devices.new'); ?></p>
      <div class="field">
        <label for="dn"><?php te('devices.name'); ?></label>
        <input id="dn" type="text" name="device_name" maxlength="40" placeholder="<?php te('devices.name_ph'); ?>" required />
      </div>
      <div class="field">
        <label for="dm"><?php te('devices.mac'); ?></label>
        <input id="dm" type="text" name="device_mac" placeholder="00:11:22:33:44:55" required />
      </div>
      <div class="field">
        <label for="dip"><?php te('devices.ip'); ?></label>
        <input id="dip" type="text" name="device_ip" placeholder="<?php te('devices.ip_ph'); ?>" />
      </div>
      <div class="mt"><button class="btn" type="submit"><svg><use href="#i-plus"/></svg><?php te('devices.add'); ?></button></div>
    </form>

    <div class="spacer"></div>
    <script src="<?php echo asset('assets/device-reorder.js'); ?>"></script>
<?php require __DIR__ . '/partials/foot.php'; ?>
