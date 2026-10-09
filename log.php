<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/devices.php';
require_once __DIR__ . '/auth/log.php';
require_once __DIR__ . '/auth/status.php';
require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'clear') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error = t('log.csrf');
    } elseif (wol_log_clear()) {
        $success = t('log.cleared_ok');
    } else {
        $error = t('log.save_failed');
    }
}

$filter = $_GET['f'] ?? 'all';
if (!isset(WOL_LOG_CATEGORIES[$filter])) {
    $filter = 'all';
}
$allEntries = wol_log_entries();
$entries = $filter === 'all' ? $allEntries : array_values(array_filter($allEntries, function ($e) use ($filter) {
    return in_array($e['type'] ?? '', WOL_LOG_CATEGORIES[$filter], true);
}));

// Hinweis auf die Hintergrundprüfung nur, wenn es überhaupt Geräte mit IP gibt.
$hasStatusDevices = false;
foreach (devices_load() as $dev) {
    if ($dev['ip'] !== '') {
        $hasStatusDevices = true;
        break;
    }
}
$bgLastRun = status_background_last_run();
$bgHint = null;
if ($hasStatusDevices && !status_background_active()) {
    $bgHint = $bgLastRun === 0 ? t('log.bg_missing') : t('log.bg_stale');
}
$bgHowto = 'https://github.com/' . WOL_UPDATE_REPO . '/blob/main/'
         . (i18n_current() === 'de' ? 'README.md#hintergrundprüfung' : 'README_en.md#background-check');

/* Anzeigetext, Icon und Farbton eines Eintrags. */
function log_view(array $e) {
    $p = is_array($e['p'] ?? null) ? $e['p'] : [];
    $dev = (string)($p['device'] ?? '');
    $name = (string)($p['name'] ?? '') !== '' ? (string)$p['name'] : t('passkey.unnamed');
    $plan = (string)($p['schedule'] ?? '');
    switch ($e['type'] ?? '') {
        case 'wake':
            return $plan !== ''
                ? [t('log.wake_schedule', $dev, $plan), 'i-clock', 'ok']
                : [t('log.wake', $dev), 'i-pw', 'ok'];
        case 'wake_failed':     return [t('log.wake_failed', $dev), 'i-pw', 'bad'];
        case 'schedule_added':   return [t('log.schedule_added', $plan, $dev), 'i-clock', ''];
        case 'schedule_changed':  return [t('log.schedule_changed', $dev, $plan), 'i-clock', ''];
        case 'schedule_removed': return [t('log.schedule_removed', $plan, $dev), 'i-clock', ''];
        case 'login':
            return ($p['method'] ?? '') === 'passkey'
                ? [t('log.login_passkey', $name), 'i-fp', 'ok']
                : [t('log.login_password'), 'i-lock', 'ok'];
        case 'login_failed':    return [t('log.login_failed'), 'i-lock', 'bad'];
        case 'login_locked':    return [t('log.login_locked', (int)($p['minutes'] ?? 0)), 'i-lock', 'bad'];
        case 'online':          return [t('log.online', $dev), 'i-mon', 'ok'];
        case 'offline':         return [t('log.offline', $dev), 'i-mon', 'muted'];
        case 'device_added':    return [t('log.device_added', $dev), 'i-plus', ''];
        case 'device_changed':  return [t('log.device_changed', $dev), 'i-mon', ''];
        case 'device_renamed':  return [t('log.device_renamed', $dev, $name), 'i-mon', ''];
        case 'device_removed':  return [t('log.device_removed', $dev), 'i-trash', ''];
        case 'passkey_added':   return [t('log.passkey_added', $name), 'i-fp', ''];
        case 'passkey_removed': return [t('log.passkey_removed', $name), 'i-trash', ''];
        case 'password_set':    return [t('log.password_set'), 'i-lock', ''];
        case 'log_cleared':     return [t('log.cleared'), 'i-trash', ''];
    }
    return [(string)($e['type'] ?? '?'), 'i-info', ''];
}

function log_detail(array $e) {
    $p = is_array($e['p'] ?? null) ? $e['p'] : [];
    $parts = [];
    if (!empty($p['approx'])) {
        $parts[] = t('log.approx');
    }
    if (!empty($p['ip'])) {
        $parts[] = t('log.from_ip', (string)$p['ip']);
    }
    return implode(' · ', $parts);
}

$page_title  = t('log.title');
$brand_title = t('log.brand');
$brand_sub   = t('log.sub');
$show_menu   = true;
require __DIR__ . '/partials/head.php';
?>
    <?php if ($error): ?>
      <div class="messageNOK"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
      <div class="messageOK"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($bgHint !== null): ?>
      <div class="messageInfo">
        <?php echo htmlspecialchars($bgHint); ?>
        <a href="<?php echo htmlspecialchars($bgHowto); ?>" target="_blank" rel="noopener"><?php te('log.bg_howto'); ?></a>
      </div>
    <?php endif; ?>

    <nav class="log-filter" aria-label="<?php te('log.filter_aria'); ?>">
      <?php foreach (['all' => 'log.f_all', 'wake' => 'log.f_wake', 'access' => 'log.f_access', 'status' => 'log.f_status', 'changes' => 'log.f_changes'] as $f => $key): ?>
        <a href="log.php<?php echo $f === 'all' ? '' : '?f=' . $f; ?>"<?php echo $f === $filter ? ' class="active" aria-current="true"' : ''; ?>><?php te($key); ?></a>
      <?php endforeach; ?>
    </nav>

    <?php if (count($entries) === 0): ?>
      <p class="section-label"><?php te('log.empty'); ?></p>
    <?php else: ?>
      <ol class="loglist" id="logList">
        <?php foreach ($entries as $e): [$text, $icon, $tone] = log_view($e); $detail = log_detail($e); $ts = (int)($e['t'] ?? 0); ?>
          <li class="logrow<?php echo $tone !== '' ? ' tone-' . $tone : ''; ?>" data-ts="<?php echo $ts; ?>">
            <span class="ic"><svg><use href="#<?php echo $icon; ?>"/></svg></span>
            <span class="txt">
              <span class="lx"><?php echo htmlspecialchars($text); ?></span>
              <?php if ($detail !== ''): ?><span class="ld"><?php echo htmlspecialchars($detail); ?></span><?php endif; ?>
            </span>
            <time class="lt" datetime="<?php echo gmdate('c', $ts); ?>"><?php echo date('d.m. H:i', $ts); ?></time>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>

    <?php if (count($allEntries) > 0): ?>
      <form method="post" action="log.php" class="mt"
            onsubmit="return confirm(<?php echo htmlspecialchars(json_encode(t('log.confirm_clear')), ENT_QUOTES); ?>);">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
        <input type="hidden" name="action" value="clear" />
        <button class="btn btn-ghost" type="submit"><svg><use href="#i-trash"/></svg><?php te('log.clear'); ?></button>
      </form>
    <?php endif; ?>

    <div class="spacer"></div>
    <script src="<?php echo asset('assets/log.js'); ?>"></script>
<?php require __DIR__ . '/partials/foot.php'; ?>
