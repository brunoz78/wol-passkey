<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/backup.php';
require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error = t('backup.csrf');
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'backup') {
            $zipPath = backup_create();
            if ($zipPath === null) {
                $error = t('backup.create_failed');
            } else {
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . backup_filename() . '"');
                header('Content-Length: ' . filesize($zipPath));
                readfile($zipPath);
                @unlink($zipPath);
                exit;
            }
        } elseif ($action === 'restore') {
            if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
                $error = t('backup.no_file');
            } else {
                $result = backup_restore($_FILES['backup_file']['tmp_name']);
                if ($result === true) {
                    $success = t('backup.restore_ok');
                } else {
                    $errorKeys = [
                        'unsupported'  => 'backup.unsupported',
                        'invalid_zip'  => 'backup.invalid_zip',
                        'write_failed' => 'backup.restore_failed',
                    ];
                    $error = t($errorKeys[$result] ?? 'backup.restore_failed');
                }
            }
        }
    }
}

$page_title  = t('backup.title');
$brand_title = t('backup.brand');
$brand_sub   = t('backup.sub');
$show_menu   = true;
require __DIR__ . '/partials/head.php';
?>
    <div class="hero"><svg><use href="#i-archive"/></svg></div>

    <?php if (!backup_supported()): ?>
      <div class="messageNOK"><?php te('backup.unsupported'); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="messageNOK"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
      <div class="messageOK"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <p class="section-label" style="margin-top:16px"><?php te('backup.create_title'); ?></p>
    <p class="hint"><?php te('backup.create_desc'); ?></p>
    <form method="post" action="backup.php">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
      <input type="hidden" name="action" value="backup" />
      <div class="mt">
        <button class="btn" type="submit"><svg><use href="#i-download"/></svg><?php te('backup.create'); ?></button>
      </div>
    </form>

    <hr />

    <p class="section-label"><?php te('backup.restore_title'); ?></p>
    <p class="hint"><?php te('backup.restore_desc'); ?></p>
    <form method="post" action="backup.php" enctype="multipart/form-data"
          onsubmit="return confirm(<?php echo htmlspecialchars(json_encode(t('backup.restore_confirm')), ENT_QUOTES); ?>);">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>" />
      <input type="hidden" name="action" value="restore" />
      <div class="field">
        <label for="bf"><?php te('backup.file_label'); ?></label>
        <input id="bf" type="file" name="backup_file" accept=".zip" required />
      </div>
      <div class="mt">
        <button class="btn btn-ghost" type="submit"><svg><use href="#i-upload"/></svg><?php te('backup.restore_button'); ?></button>
      </div>
    </form>

    <div class="spacer"></div>
<?php require __DIR__ . '/partials/foot.php'; ?>
