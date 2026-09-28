<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/datafile.php';
require_once __DIR__ . '/status.php';

/*
  Zeitgesteuertes Aufwecken. Die Zeitpläne hängen am Gerät
  (auth/devices-data.php, siehe devices_normalize_schedules) und werden von
  der Hintergrundprüfung cron.php ausgeführt - ohne sie passiert nichts.

  Gearbeitet wird minutengenau: Ein Zeitplan ist fällig, wenn Wochentag und
  Uhrzeit auf die laufende Minute passen. Verpasste Zeitpunkte werden nicht
  nachgeholt; war der Server um 07:30 aus, soll der Rechner nicht um 09:00
  unvermittelt starten.
*/

/* Wochentag 1 (Montag) bis 7 (Sonntag), wie date('N'). */
function schedule_now() {
    return new DateTime('now', new DateTimeZone(WOL_TIMEZONE));
}

function schedule_is_due(array $schedule, DateTime $now) {
    return in_array((int)$now->format('N'), $schedule['days'], true)
        && $schedule['time'] === $now->format('H:i');
}

/*
  Liefert die Geräte, deren Zeitplan gerade fällig ist, und merkt sich die
  Minute. Der Merker verhindert ein zweites Aufwecken, wenn die Prüfung
  innerhalb derselben Minute erneut läuft (z.B. von Hand gestartet).
*/
function schedule_due_devices(array $devices, DateTime $now) {
    $minute = $now->format('Y-m-d H:i');
    $due = [];

    foreach ($devices as $name => $dev) {
        foreach ($dev['schedules'] as $schedule) {
            if (schedule_is_due($schedule, $now)) {
                $due[(string)$name] = $schedule['time'];
                break;
            }
        }
    }
    if ($due === []) {
        return [];
    }

    $fresh = [];
    wol_datafile_update(WOL_STATUS_FILE, function ($data) use ($due, $minute, &$fresh) {
        $fired = is_array($data['fired'] ?? null) ? $data['fired'] : [];
        foreach ($due as $name => $time) {
            if (($fired[$name] ?? '') !== $minute) {
                $fired[$name] = $minute;
                $fresh[$name] = $time;
            }
        }
        if ($fresh === []) {
            return null;
        }
        // Merker anderer Geräte nur so lange halten, wie sie gebraucht werden.
        $data['fired'] = array_filter($fired, function ($m) use ($minute) {
            return $m === $minute;
        });
        return $data;
    });

    return $fresh;
}

/* Text für die Anzeige: "07:30 · Mo Di Mi Do Fr" */
function schedule_days_label(array $days) {
    $out = [];
    foreach ($days as $d) {
        $out[] = t('sched.d' . $d);
    }
    return implode(' ', $out);
}
