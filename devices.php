<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/devices.php';
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

            // preg_match('//u', ...) prüft UTF-8-Gültigkeit ohne mbstring
            if ($name === '' || preg_match('//u', $name) !== 1) {
                $error = t('devices.name_required');
            } elseif (strlen($name) > 40) {
                $error = t('devices.name_too_long');
            } elseif ($mac === null) {
                $error = t('devices.mac_invalid');
            } elseif (isset($devices[$name])) {
                $error = t('devices.exists');
            } else {
                $devices[$name] = $mac;
                if (devices_save($devices)) {
                    $success = t('devices.added', $name);
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
      <?php foreach ($devices as $name => $mac): ?>
        <div class="item">
          <span class="ic"><svg><use href="#i-mon"/></svg></span>
          <span class="txt grow">
            <span class="nm"><?php echo htmlspecialchars($name); ?></span>
            <span class="mac"><?php echo htmlspecialchars($mac); ?></span>
          </span>
          <?php /* json_encode liefert ein gültiges JS-String-Literal, auch bei Anführungszeichen im Namen. */ ?>
          <form method="post" action="devices.php"
                onsubmit="return confirm(<?php echo htmlspecialchars(json_encode(t('devices.confirm', $name)), ENT_QUOTES); ?>);">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
            <input type="hidden" name="action" value="delete" />
            <input type="hidden" name="device_name" value="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>" />
            <button class="icon-btn" type="submit"><svg><use href="#i-trash"/></svg><?php te('devices.remove'); ?></button>
          </form>
        </div>
      <?php endforeach; ?>
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
      <div class="mt"><button class="btn" type="submit"><svg><use href="#i-plus"/></svg><?php te('devices.add'); ?></button></div>
    </form>

    <div class="spacer"></div>
<?php require __DIR__ . '/partials/foot.php'; ?>
