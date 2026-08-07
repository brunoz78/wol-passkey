<?php
/*
  English texts. German (lang/de.php) is the source language: if a key is
  missing here, the German text is used automatically.
*/
return [
    // ---------- Navigation / top bar ----------
    'nav.wake'        => 'Wake up',
    'nav.devices'     => 'Manage devices',
    'nav.passkey'     => 'Manage passkeys',
    'nav.backup'      => 'Backup',
    'nav.logout'      => 'Sign out',
    'nav.aria'        => 'Navigation',
    'nav.open'        => 'Open menu',
    'nav.close'       => 'Close menu',
    'nav.language'    => 'Language',
    'nav.about'       => 'About (version %s)',

    // ---------- Update notice ----------
    'update.available' => 'Update available: version %s',

    'theme.aria'      => 'Choose a theme',
    'theme.light'     => 'Light theme',
    'theme.dark'      => 'Dark theme',
    'theme.vivid'     => 'Vivid theme',
    'lang.aria'       => 'Choose a language',

    // ---------- Home page (wake) ----------
    'index.sub'          => 'Pick a device and wake it up',
    'index.csrf'         => 'Invalid request, please submit the form again.',
    'index.wake_failed'  => 'The magic packet could not be sent.',
    'index.wake_sent'    => 'Wake-up sent to %s',
    'index.no_devices'   => 'No devices added yet.',
    'index.add_device'   => 'Add device',
    'index.your_devices' => 'Your devices',
    'index.wake'         => 'Wake up',

    // ---------- Login ----------
    'login.title'          => 'Login',
    'login.sub'            => 'Please sign in',
    'login.csrf'           => 'Invalid request, please reload the page.',
    'login.locked'         => 'Too many failed attempts. Please try again in %d minute(s).',
    'login.no_password'    => 'No password has been set yet. Please open setup.php first.',
    'login.wrong_password' => 'Incorrect password.',
    'login.with_passkey'   => 'Sign in with passkey',
    'login.or_password'    => 'or use your password',
    'login.password'       => 'Password',
    'login.submit'         => 'Sign in',

    // ---------- Setup ----------
    'setup.title'        => 'Setup',
    'setup.brand'        => 'Login setup',
    'setup.sub'          => 'Set or reset the password',
    'setup.csrf'         => 'Invalid request, please reload the page.',
    'setup.locked'       => 'Setup is locked: please set your own secret value for {key} in the file '
                          . 'config.php first (see config.sample.php) and upload the file again.',
    'setup.not_writable' => 'The folder "auth" is not writable for the web server. The password cannot '
                          . 'be saved. Please grant write permission on the folder "auth" to the web '
                          . 'server user (usually group "http").',
    'setup.key_wrong'    => 'The setup key is incorrect.',
    'setup.pw_too_short' => 'The password must be at least 8 characters long.',
    'setup.pw_mismatch'  => 'The two passwords do not match.',
    'setup.saved'        => 'Password set successfully. You can sign in now.',
    'setup.save_failed'  => 'Saving failed: the folder "auth" is not writable for the web server. Please '
                          . 'grant write permission on the folder to the web server user (group "http") '
                          . 'and try again.',
    'setup.to_login'     => 'Go to login',
    'setup.key_label'    => 'Setup key',
    'setup.new_pw'       => 'New password',
    'setup.new_pw2'      => 'Repeat new password',
    'setup.save'         => 'Save password',

    // ---------- Device management ----------
    'devices.title'         => 'Devices',
    'devices.brand'         => 'Manage devices',
    'devices.sub'           => 'Add or remove target devices',
    'devices.csrf'          => 'Invalid request, please try again.',
    'devices.name_required' => 'Please enter a device name.',
    'devices.name_too_long' => 'The device name must not exceed 40 characters.',
    'devices.mac_invalid'   => 'The MAC address is invalid. It must contain 12 hex characters, e.g. 00:11:22:33:44:55.',
    'devices.exists'        => 'A device with this name already exists. Please remove it first or pick another name.',
    'devices.added'         => 'Device "%s" added.',
    'devices.removed'       => 'Device "%s" removed.',
    'devices.not_found'     => 'Device not found.',
    'devices.save_failed'   => 'Saving failed: the folder "auth" is not writable for the web server.',
    'devices.not_writable'  => 'The folder "auth" is not writable for the web server – changes cannot be saved.',
    'devices.none'          => 'No devices added yet.',
    'devices.your_devices'  => 'Your devices',
    'devices.remove'        => 'Remove',
    'devices.confirm'       => 'Really remove device "%s"?',
    'devices.new'           => 'New device',
    'devices.name'          => 'Device name',
    'devices.name_ph'       => 'e.g. Living room PC',
    'devices.mac'           => 'MAC address',
    'devices.ip'            => 'IP address',
    'devices.ip_ph'         => 'e.g. 192.168.1.50 (optional)',
    'devices.ip_invalid'    => 'The IP address is invalid.',
    'devices.ip_save'       => 'Save IP address',
    'devices.ip_saved'      => 'IP address for "%s" saved.',
    'devices.add'           => 'Add device',

    // ---------- Passkey management ----------
    'passkey.title'          => 'Passkey',
    'passkey.brand'          => 'Manage passkeys',
    'passkey.sub'            => 'Fingerprint / Face ID of this device',
    'passkey.csrf'           => 'Invalid request, please try again.',
    'passkey.not_writable'   => 'The folder "auth" is not writable for the web server – changes cannot be saved.',
    'passkey.registered'     => 'Registered passkeys',
    'passkey.created'        => 'registered on %s',
    'passkey.unnamed'        => 'Unnamed',
    'passkey.device_name'    => 'Name for this device',
    'passkey.device_name_ph' => 'e.g. My smartphone',
    'passkey.register'       => 'Register passkey',
    'passkey.default_device' => 'Unnamed device',
    'passkey.remove'         => 'Remove',
    'passkey.confirm'        => 'Really remove passkey "%s"?',
    'passkey.removed'        => 'Passkey "%s" removed.',
    'passkey.not_found'      => 'Passkey not found.',
    'passkey.save_failed'    => 'Saving failed: the folder "auth" is not writable for the web server.',

    // ---------- Backup / restore ----------
    'backup.title'          => 'Backup',
    'backup.brand'          => 'Backup',
    'backup.sub'            => 'Back up or restore configuration and data',
    'backup.csrf'           => 'Invalid request, please try again.',
    'backup.unsupported'    => 'The PHP extension "zip" is not active on this server - backup and restore '
                             . 'are therefore unavailable.',
    'backup.create_title'   => 'Create backup',
    'backup.create_desc'    => 'Downloads a ZIP with config.php and the runtime data (login, passkeys, '
                             . 'device list). The file contains the setup key and the password hash - '
                             . 'keep it safe.',
    'backup.create'         => 'Download backup',
    'backup.create_failed'  => 'The backup could not be created.',
    'backup.restore_title'  => 'Restore backup',
    'backup.restore_desc'   => 'Overwrites config.php and the runtime data with the contents of the '
                             . 'uploaded ZIP file. Files missing from the ZIP are left untouched.',
    'backup.file_label'     => 'Backup ZIP',
    'backup.restore_button' => 'Restore',
    'backup.restore_confirm'=> 'Really restore? Existing configuration and data will be overwritten.',
    'backup.no_file'        => 'Please select a ZIP file.',
    'backup.invalid_zip'    => 'Not a valid backup ZIP.',
    'backup.restore_failed' => 'Restore failed: file(s) could not be written.',
    'backup.restore_ok'     => 'Restore successful. Already signed-in sessions (including this one) remain active.',

    // ---------- WebAuthn messages from the server ----------
    'wa.not_logged_in'    => 'Not signed in.',
    'wa.bad_request'      => 'Invalid request.',
    'wa.no_reg_request'   => 'No active registration request. Please reload the page.',
    'wa.no_login_request' => 'No active sign-in request. Please reload the page.',
    'wa.no_passkey'       => 'No passkey was submitted.',
    'wa.unknown_passkey'  => 'This passkey is not registered here.',
    'wa.save_failed'      => 'Saving failed: the folder "auth" is not writable for the web server.',

    // ---------- Messages shown in the browser (JavaScript) ----------
    'js.no_support'          => 'This browser does not support passkeys.',
    'js.no_secure_context'   => 'Passkeys require a secure connection (HTTPS). This page is served over HTTP – only password login is available here.',
    'js.confirm_biometry'    => 'Please confirm with biometrics on your device …',
    'js.confirm_fingerprint' => 'Please confirm with fingerprint/Face ID …',
    'js.register_prepare'    => 'Could not prepare the registration.',
    'js.register_failed'     => 'Registration failed.',
    'js.register_ok'         => 'Passkey registered successfully!',
    'js.login_prepare'       => 'Could not prepare the sign-in.',
    'js.login_failed'        => 'Sign-in failed.',
    'js.login_ok'            => 'Signed in, redirecting …',
    'js.err_cancelled'       => 'Cancelled or timed out. Please try again.',
    'js.err_already_registered' => 'This device already has a passkey registered.',
    'js.err_origin'          => 'This page address does not match the passkey. A passkey is always tied to one exact hostname.',
    'js.unknown_error'       => 'Unknown error',
    'js.device_online'       => 'Running',
];
