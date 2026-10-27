<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/devices.php';
require_once __DIR__ . '/auth/log.php';
require_once __DIR__ . '/auth/schedule.php';
require_login();

$error = '';
$success = '';
$devices = devices_load();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = (string)($_POST['device_name'] ?? '');

    /*
      Prüfung und Änderung laufen gemeinsam in devices_update(), also unter
      der Dateisperre: Ein Zeitplan hängt am Geräteeintrag, und ein zweiter
      offener Tab (oder die Geräteseite) dürfte die Änderung nicht
      dazwischen überschreiben.
    */
    $outcome = '';
    $time = '';

    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error = t('sched.csrf');
    } elseif ($action === 'add' || $action === 'update' || $action === 'delete') {
        $index = (int)($_POST['index'] ?? -1);
        $wanted = devices_normalize_schedules([[
            'time' => $_POST['time'] ?? '',
            'days' => $_POST['days'] ?? [],
        ]]);

        $ok = devices_update(function ($devices) use ($action, $name, $index, $wanted, &$outcome, &$time) {
            if (!isset($devices[$name])) {
                $outcome = 'not_found';
                return null;
            }
            $plans = $devices[$name]['schedules'];

            if ($action === 'delete') {
                if (!isset($plans[$index])) {
                    $outcome = 'not_found';
                    return null;
                }
                $time = $plans[$index]['time'];
                array_splice($plans, $index, 1);
                $outcome = 'removed';
            } else {
                if ($action === 'update' && !isset($plans[$index])) {
                    $outcome = 'not_found';
                    return null;
                }
                if ($wanted === []) {
                    $outcome = 'invalid';
                    return null;
                }
                // Beim Ändern zählt der eigene Eintrag nicht als Dublette.
                $others = $plans;
                if ($action === 'update') {
                    unset($others[$index]);
                }
                if (in_array($wanted[0], $others, true)) {
                    $outcome = 'exists';
                    return null;
                }
                if ($action === 'update') {
                    $plans[$index] = $wanted[0];
                    $outcome = 'changed';
                } else {
                    $plans[] = $wanted[0];
                    $outcome = 'added';
                }
                $time = $wanted[0]['time'];
                usort($plans, function ($a, $b) {
                    return strcmp($a['time'], $b['time']);
                });
            }

            $devices[$name]['schedules'] = array_values($plans);
            return $devices;
        });

        if ($outcome === 'not_found') {
            $error = t('sched.not_found');
        } elseif ($outcome === 'invalid') {
            $error = t('sched.invalid');
        } elseif ($outcome === 'exists') {
            $error = t('sched.exists');
        } elseif (!$ok) {
            $error = t('sched.save_failed');
        } elseif ($outcome === 'added') {
            $success = t('sched.added', $name);
            wol_log('schedule_added', ['device' => $name, 'schedule' => $time, 'ip' => wol_client_ip()]);
        } elseif ($outcome === 'changed') {
            $success = t('sched.changed', $name);
            wol_log('schedule_changed', ['device' => $name, 'schedule' => $time, 'ip' => wol_client_ip()]);
        } elseif ($outcome === 'removed') {
            $success = t('sched.removed', $name);
            wol_log('schedule_removed', ['device' => $name, 'schedule' => $time, 'ip' => wol_client_ip()]);
        }
    } else {
        $error = t('sched.not_found');
    }
    $devices = devices_load();
}

$bgActive = status_background_active();
$howto = 'https://github.com/' . WOL_UPDATE_REPO . '/blob/main/'
       . (i18n_current() === 'de' ? 'README.md#hintergrundprüfung' : 'README_en.md#background-check');

$page_title  = t('sched.title');
$brand_title = t('sched.brand');
$brand_sub   = t('sched.sub');
$show_menu   = true;
require __DIR__ . '/partials/head.php';
?>
    <?php if ($error): ?>
      <div class="messageNOK"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
      <div class="messageOK"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (!$bgActive): ?>
      <div class="messageNOK">
        <?php te('sched.bg_missing'); ?>
        <a href="<?php echo htmlspecialchars($howto); ?>" target="_blank" rel="noopener"><?php te('sched.bg_howto'); ?></a>
      </div>
    <?php endif; ?>

    <?php if (count($devices) === 0): ?>
      <p class="section-label" style="margin-top:16px"><?php te('sched.no_devices'); ?></p>
      <a class="btn mt" href="devices.php"><svg><use href="#i-plus"/></svg><?php te('index.add_device'); ?></a>
    <?php else: ?>
      <p class="section-label" style="margin-top:16px"><?php te('sched.planned'); ?></p>
      <?php $any = false; ?>
      <?php foreach ($devices as $name => $dev): foreach ($dev['schedules'] as $i => $s): $any = true; $id = 'p' . md5($name . '#' . $i); ?>
        <details class="item foldable">
          <summary>
            <span class="ic"><svg><use href="#i-clock"/></svg></span>
            <span class="txt grow">
              <span class="nm"><?php echo htmlspecialchars($name); ?></span>
              <span class="plan"><?php echo htmlspecialchars($s['time'] . ' · ' . schedule_days_label($s['days'])); ?></span>
            </span>
            <svg class="fold-chev"><use href="#i-chevron"/></svg>
          </summary>

          <form class="fold-edit" method="post" action="schedule.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="device_name" value="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>" />
            <input type="hidden" name="index" value="<?php echo (int)$i; ?>" />
            <label class="label" for="<?php echo $id; ?>t"><?php te('sched.time'); ?></label>
            <input id="<?php echo $id; ?>t" type="time" name="time" value="<?php echo htmlspecialchars($s['time'], ENT_QUOTES); ?>" required />
            <span class="label"><?php te('sched.days'); ?></span>
            <div class="daypick">
              <?php for ($d = 1; $d <= 7; $d++): ?>
                <label class="day">
                  <input type="checkbox" name="days[]" value="<?php echo $d; ?>"<?php echo in_array($d, $s['days'], true) ? ' checked' : ''; ?> />
                  <span><?php te('sched.d' . $d); ?></span>
                </label>
              <?php endfor; ?>
            </div>
            <div class="fold-actions">
              <button class="icon-btn" type="submit"><svg><use href="#i-check"/></svg><?php te('sched.save'); ?></button>
            </div>
          </form>

          <form class="fold-del" method="post" action="schedule.php"
                onsubmit="return confirm(<?php echo htmlspecialchars(json_encode(t('sched.confirm', $s['time'], $name)), ENT_QUOTES); ?>);">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
            <input type="hidden" name="action" value="delete" />
            <input type="hidden" name="device_name" value="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>" />
            <input type="hidden" name="index" value="<?php echo (int)$i; ?>" />
            <button class="icon-btn" type="submit"><svg><use href="#i-trash"/></svg><?php te('sched.remove'); ?></button>
          </form>
        </details>
      <?php endforeach; endforeach; ?>
      <?php if (!$any): ?>
        <p class="hint"><?php te('sched.none'); ?></p>
      <?php endif; ?>

      <hr />
      <form method="post" action="schedule.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
        <input type="hidden" name="action" value="add" />
        <p class="section-label"><?php te('sched.new'); ?></p>
        <div class="field">
          <label for="sd"><?php te('sched.device'); ?></label>
          <select id="sd" name="device_name">
            <?php foreach ($devices as $name => $dev): ?>
              <option value="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>"><?php echo htmlspecialchars($name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="st"><?php te('sched.time'); ?></label>
          <input id="st" type="time" name="time" value="07:30" required />
        </div>
        <div class="field">
          <span class="label"><?php te('sched.days'); ?></span>
          <div class="daypick">
            <?php for ($d = 1; $d <= 7; $d++): ?>
              <label class="day">
                <input type="checkbox" name="days[]" value="<?php echo $d; ?>"<?php echo $d <= 5 ? ' checked' : ''; ?> />
                <span><?php te('sched.d' . $d); ?></span>
              </label>
            <?php endfor; ?>
          </div>
        </div>
        <div class="mt"><button class="btn" type="submit"><svg><use href="#i-plus"/></svg><?php te('sched.add'); ?></button></div>
      </form>
    <?php endif; ?>

    <p class="hint" style="margin-top:14px"><?php te('sched.now', date('H:i'), WOL_TIMEZONE); ?></p>

    <div class="spacer"></div>
<?php require __DIR__ . '/partials/foot.php'; ?>
