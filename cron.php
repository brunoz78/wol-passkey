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

$devices = devices_load();
foreach ($devices as $name => $dev) {
    if ($dev['ip'] !== '') {
        status_record((string)$name, device_is_reachable($dev['ip']));
    }
}

if (!status_finish_run(array_map('strval', array_keys($devices)))) {
    fwrite(STDERR, "Status konnte nicht gespeichert werden - ist auth/ für diesen Benutzer beschreibbar?\n");
    exit(1);
}
