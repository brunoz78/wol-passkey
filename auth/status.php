<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/datafile.php';
require_once __DIR__ . '/log.php';

/*
  Merkt sich pro Gerät, ob es online ist und seit wann. Gefüttert wird das
  von jeder Statusprüfung - den Gerätekacheln im Browser und der
  Hintergrundprüfung (cron.php). Ohne Hintergrundprüfung wird nur geprüft,
  während jemand die Seite offen hat; ein Wechsel ist dann nur ungefähr
  datiert ("seit spätestens ...").
*/

define('WOL_STATUS_FILE', __DIR__ . '/status-data.php');
// Lag die vorige Prüfung höchstens so weit zurück, gilt ein erkannter Wechsel
// als genau datiert. Die Hintergrundprüfung läuft jede Minute.
define('WOL_STATUS_EXACT_GAP', 180);
// So lange nach ihrem letzten Lauf gilt die Hintergrundprüfung als aktiv.
define('WOL_STATUS_BG_MAX_AGE', 600);

/*
  Hält eine Beobachtung fest und liefert den Stand des Geräts:
  ['online' => bool, 'since' => Zeitstempel, 'exact' => bool, 'checked' => Zeitstempel].
  Ein Wechsel landet im Verlauf, die allererste Beobachtung nicht - da fehlt
  das Vorher.
*/
function status_record($name, $online) {
    $now = time();
    $state = ['online' => $online, 'since' => $now, 'exact' => false, 'checked' => $now];
    $changed = false;

    wol_datafile_update(WOL_STATUS_FILE, function ($data) use ($name, $online, $now, &$state, &$changed) {
        $prev = $data['devices'][$name] ?? null;
        if (is_array($prev) && (bool)$prev['online'] === $online) {
            $state = $prev;
            // Unverändert und eben erst geprüft: nicht bei jedem Seitenaufruf schreiben.
            if ($now - (int)$prev['checked'] < 60) {
                return null;
            }
            $state['checked'] = $now;
        } elseif (is_array($prev)) {
            $state['exact'] = $now - (int)$prev['checked'] <= WOL_STATUS_EXACT_GAP;
            $changed = true;
        }
        $data['devices'][$name] = $state;
        return $data;
    });

    if ($changed) {
        wol_log($online ? 'online' : 'offline', ['device' => $name, 'approx' => !$state['exact']]);
    }
    return $state;
}

/* Abschluss eines Laufs der Hintergrundprüfung; räumt gelöschte Geräte ab. */
function status_finish_run(array $names) {
    return wol_datafile_update(WOL_STATUS_FILE, function ($data) use ($names) {
        $data['devices'] = array_intersect_key($data['devices'] ?? [], array_flip($names));
        $data['last_run'] = time();
        return $data;
    });
}

/*
  Zieht den gemerkten Stand auf einen neuen Gerätenamen um (Umbenennen in
  devices.php). Ohne das würde das Gerät als unbekannt gelten und "online
  seit ..." neu bei null anfangen. Die Zeitplan-Markierung wandert mit, damit
  ein Plan, der in dieser Minute schon ausgelöst hat, nicht erneut feuert.
*/
function status_rename($old, $new) {
    return wol_datafile_update(WOL_STATUS_FILE, function ($data) use ($old, $new) {
        if (!isset($data['devices'][$old]) && !isset($data['fired'][$old])) {
            return null;
        }
        if (isset($data['devices'][$old])) {
            $data['devices'][$new] = $data['devices'][$old];
            unset($data['devices'][$old]);
        }
        if (isset($data['fired'][$old])) {
            $data['fired'][$new] = $data['fired'][$old];
            unset($data['fired'][$old]);
        }
        return $data;
    });
}

function status_forget($name) {
    return wol_datafile_update(WOL_STATUS_FILE, function ($data) use ($name) {
        if (!isset($data['devices'][$name])) {
            return null;
        }
        unset($data['devices'][$name]);
        return $data;
    });
}

/* Zeitpunkt des letzten Laufs der Hintergrundprüfung, 0 = noch nie. */
function status_background_last_run() {
    return (int)(wol_datafile_read(WOL_STATUS_FILE)['last_run'] ?? 0);
}

function status_background_active() {
    return time() - status_background_last_run() <= WOL_STATUS_BG_MAX_AGE;
}
