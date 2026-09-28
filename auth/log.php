<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/datafile.php';

/*
  Verlauf (log.php): Aufwecken, Anmeldungen, Online-/Offline-Wechsel und
  Änderungen an Geräten, Passkeys und Passwort. Gespeichert werden nur Typ,
  Zeitpunkt und Parameter - den Text erzeugt erst die Anzeige, damit er in
  der Sprache des Betrachters erscheint.
*/

define('WOL_LOG_FILE', __DIR__ . '/log-data.php');
define('WOL_LOG_MAX', 500);

// Eintragstypen je Filter auf der Verlaufsseite
define('WOL_LOG_CATEGORIES', [
    'wake'    => ['wake', 'wake_failed'],
    'access'  => ['login', 'login_failed', 'login_locked'],
    'status'  => ['online', 'offline'],
    'changes' => ['device_added', 'device_removed', 'schedule_added', 'schedule_removed',
                  'passkey_added', 'passkey_removed', 'password_set', 'log_cleared'],
]);

function wol_log($type, array $params = []) {
    $entry = ['t' => time(), 'type' => $type, 'p' => $params];
    return wol_datafile_update(WOL_LOG_FILE, function ($data) use ($entry) {
        $entries = $data['entries'] ?? [];
        $entries[] = $entry;
        $data['entries'] = array_slice($entries, -WOL_LOG_MAX);
        return $data;
    });
}

/* Alle Einträge, neueste zuerst. */
function wol_log_entries() {
    $entries = wol_datafile_read(WOL_LOG_FILE)['entries'] ?? [];
    return array_reverse(is_array($entries) ? $entries : []);
}

function wol_log_clear() {
    $ok = wol_datafile_update(WOL_LOG_FILE, function () {
        return ['entries' => []];
    });
    return $ok && wol_log('log_cleared', ['ip' => wol_client_ip()]);
}

/*
  IP-Adresse des Aufrufers für das Protokoll. Hinter dem Reverse Proxy kommt
  jede Anfrage von dessen Adresse; die echte steht dann im letzten Eintrag
  von X-Forwarded-For (den hat der Proxy selbst angehängt, frühere Einträge
  könnte der Aufrufer fälschen). Dem Header wird nur geglaubt, wenn die
  Anfrage tatsächlich aus dem eigenen Netz kommt - sonst könnte ihn jeder
  direkte Aufrufer setzen und seine Adresse im Protokoll verschleiern.
*/
function wol_client_ip() {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $fromLocalProxy = $remote !== '' && filter_var(
        $remote, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    ) === false;
    $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($fromLocalProxy && $forwarded !== '') {
        $parts = array_map('trim', explode(',', $forwarded));
        $last = end($parts);
        if (filter_var($last, FILTER_VALIDATE_IP) !== false) {
            return $last;
        }
    }
    return $remote;
}
