<?php
require_once __DIR__ . '/config.php';

/*
  Sicherung/Wiederherstellung der persönlichen Konfiguration und Laufzeitdaten
  als ZIP: config.php (Hauptordner, enthält den Setup-Schlüssel) sowie
  auth/data.php und auth/devices-data.php (Login, Passkeys, Geräteliste).
  Fehlt eine der Dateien (z.B. frisch installiert), wird sie beim Erstellen
  einfach ausgelassen; beim Wiederherstellen bleiben im ZIP fehlende Dateien
  unangetastet.
*/

// Marker, mit dem die selbstschützenden Laufzeitdateien beginnen (siehe
// store.php/devices.php) - dient beim Wiederherstellen als Plausibilitätsprüfung.
define('BACKUP_PROTECTED_MARKER', '<?php http_response_code(403); exit; ?>');

function backup_supported() {
    return extension_loaded('zip');
}

function backup_file_map() {
    return [
        'config.php'             => dirname(__DIR__) . '/config.php',
        'auth/data.php'          => __DIR__ . '/data.php',
        'auth/devices-data.php'  => __DIR__ . '/devices-data.php',
    ];
}

function backup_filename() {
    return 'wol-backup-' . date('Y-m-d-His') . '.zip';
}

/*
  Erstellt das Sicherungs-ZIP in einer temporären Datei und gibt deren Pfad
  zurück (vom Aufrufer nach dem Ausliefern zu löschen), oder null bei Fehler.
*/
function backup_create() {
    if (!backup_supported()) {
        return null;
    }

    $tmp = tempnam(sys_get_temp_dir(), 'wolbk');
    if ($tmp === false) {
        return null;
    }

    $zip = new ZipArchive();
    if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
        @unlink($tmp);
        return null;
    }

    $added = 0;
    foreach (backup_file_map() as $entry => $path) {
        if (is_file($path) && $zip->addFile($path, $entry)) {
            $added++;
        }
    }
    $zip->close();

    if ($added === 0) {
        @unlink($tmp);
        return null;
    }
    return $tmp;
}

/*
  Stellt Dateien aus einem hochgeladenen Sicherungs-ZIP wieder her.
  Liefert true bei Erfolg, sonst einen Fehlercode:
  'unsupported' | 'invalid_zip' | 'write_failed'
*/
function backup_restore($uploadedTmpPath) {
    if (!backup_supported()) {
        return 'unsupported';
    }

    $zip = new ZipArchive();
    if ($zip->open($uploadedTmpPath) !== true) {
        return 'invalid_zip';
    }

    $contents = [];
    foreach (backup_file_map() as $entry => $path) {
        $data = $zip->getFromName($entry);
        if ($data === false) {
            continue; // im ZIP nicht enthalten - diese Datei bleibt unangetastet
        }

        $looksValid = $entry === 'config.php'
            ? preg_match('/^\s*<\?php/', $data) === 1
            : strpos($data, BACKUP_PROTECTED_MARKER) === 0;

        if (!$looksValid) {
            $zip->close();
            return 'invalid_zip';
        }
        $contents[$path] = $data;
    }
    $zip->close();

    if (count($contents) === 0) {
        return 'invalid_zip';
    }

    foreach ($contents as $path => $data) {
        $tmp = $path . '.tmp';
        if (@file_put_contents($tmp, $data, LOCK_EX) !== strlen($data) || !@rename($tmp, $path)) {
            @unlink($tmp);
            return 'write_failed';
        }
    }

    // config.php wird per require_once geladen - ohne Invalidierung könnte ein
    // aktiver Opcache (typisch auf NAS/PHP-FPM) den alten Stand weiter ausliefern.
    if (isset($contents[dirname(__DIR__) . '/config.php']) && function_exists('opcache_invalidate')) {
        @opcache_invalidate(dirname(__DIR__) . '/config.php', true);
    }

    return true;
}
