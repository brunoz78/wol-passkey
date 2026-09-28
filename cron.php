<?php
/*
  Hintergrundprüfung: prüft alle Geräte mit hinterlegter IP und hält Online-/
  Offline-Wechsel fest. Gedacht für einen Aufruf pro Minute, z.B. per Cron:

    * * * * * php /pfad/zu/wol-passkey/cron.php

  Muss als Webserver-Benutzer laufen (www-data, auf Synology http), sonst
  gehören die Datendateien in auth/ danach root und die Webseite kann sie
  nicht mehr ändern. Einrichtung siehe README ("Hintergrundprüfung").
*/

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
    fwrite(STDERR, "cron.php bitte als Webserver-Benutzer ausführen (z.B. www-data), nicht als root.\n");
    exit(1);
}

require_once __DIR__ . '/auth/config.php';
require_once __DIR__ . '/auth/devices.php';
require_once __DIR__ . '/auth/reachability.php';
require_once __DIR__ . '/auth/status.php';
require_once __DIR__ . '/auth/schedule.php';
require_once __DIR__ . '/wol.php';

$devices = devices_load();
$online = [];
foreach ($devices as $name => $dev) {
    if ($dev['ip'] !== '') {
        $online[(string)$name] = device_is_reachable($dev['ip']);
        status_record((string)$name, $online[(string)$name]);
    }
}

// Fällige Zeitpläne. Ein Gerät, das nachweislich schon läuft, wird nicht
// geweckt - ohne hinterlegte IP ist das nicht bekannt, dann wird gesendet.
foreach (schedule_due_devices($devices, schedule_now()) as $name => $time) {
    if ($online[$name] ?? false) {
        continue;
    }
    $ok = WakeOnLan($networkbroadcast, $devices[$name]['mac'], $port, $devices[$name]['ip']);
    wol_log($ok ? 'wake' : 'wake_failed', ['device' => $name, 'schedule' => $time]);
}

if (!status_finish_run(array_map('strval', array_keys($devices)))) {
    fwrite(STDERR, "Status konnte nicht gespeichert werden - ist auth/ für diesen Benutzer beschreibbar?\n");
    exit(1);
}
