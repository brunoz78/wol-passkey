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

// Einstellungen, die in config.php stehen dürfen (siehe config.sample.php).
define('BACKUP_CONFIG_VARS', ['sitename', 'setup_key', 'port', 'timezone', 'networkbroadcast', 'maclist']);

/*
  Prüft, ob eine hochgeladene config.php nur das enthält, was eine
  Konfiguration ausmacht: Zuweisungen fester Werte an die bekannten
  Einstellungen, dazu Kommentare.

  Die Datei wird bei jedem Seitenaufruf ausgeführt. Vorher genügte es, dass
  sie mit "<?php" begann - über ein präpariertes Sicherungs-ZIP liess sich so
  beliebiger Code auf dem Server einschleusen. Deshalb wird der Quelltext hier
  in seine Bausteine zerlegt und alles abgelehnt, was nicht auf einer kurzen
  Erlaubnisliste steht: keine Funktionsaufrufe, kein include, keine
  Zeichenketten mit eingebetteten Variablen, kein HTML ausserhalb von PHP.
*/
function backup_config_is_plain($code) {
    try {
        $tokens = token_get_all((string)$code, TOKEN_PARSE);
    } catch (Throwable $e) {
        return false; // kein gültiges PHP
    }

    $opened = false;
    $closed = false;
    $hasKey = false;
    $prev = null; // letzter Baustein, der kein Leerraum/Kommentar ist

    foreach ($tokens as $token) {
        if ($closed) {
            // Nach dem schliessenden Tag darf nur noch Leerraum stehen.
            if (is_array($token) && $token[0] === T_INLINE_HTML && trim($token[1]) === '') {
                continue;
            }
            return false;
        }

        if (is_string($token)) {
            if ($token === '(') {
                // Nur als Teil von array( ... ). Eine Klammer hinter einer
                // Variablen oder Zeichenkette wäre ein Funktionsaufruf.
                if ($prev !== T_ARRAY) {
                    return false;
                }
            } elseif (!in_array($token, ['=', ';', ',', '[', ']', ')', '-'], true)) {
                return false;
            }
            $prev = $token;
            continue;
        }

        switch ($token[0]) {
            case T_WHITESPACE:
            case T_COMMENT:
            case T_DOC_COMMENT:
                continue 2;
            case T_OPEN_TAG:
                if ($opened) {
                    return false;
                }
                $opened = true;
                break;
            case T_CLOSE_TAG:
                $closed = true;
                break;
            case T_VARIABLE:
                if (!in_array(substr($token[1], 1), BACKUP_CONFIG_VARS, true)) {
                    return false;
                }
                $hasKey = $hasKey || $token[1] === '$setup_key';
                break;
            case T_STRING:
                if (!in_array(strtolower($token[1]), ['true', 'false', 'null'], true)) {
                    return false;
                }
                break;
            case T_CONSTANT_ENCAPSED_STRING:
            case T_LNUMBER:
            case T_DNUMBER:
            case T_DOUBLE_ARROW:
            case T_ARRAY:
                break;
            default:
                return false;
        }
        $prev = $token[0];
    }

    return $opened && $hasKey;
}

/*
  Eine Laufzeitdatei ist gültig, wenn auf die Schutzzeile ausschliesslich
  base64-kodiertes JSON folgt (Format siehe auth/datafile.php).
*/
function backup_datafile_is_plain($data) {
    if (strpos($data, BACKUP_PROTECTED_MARKER) !== 0) {
        return false;
    }
    $json = base64_decode(trim(substr($data, strlen(BACKUP_PROTECTED_MARKER))), true);
    return $json !== false && is_array(json_decode($json, true));
}

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
            ? backup_config_is_plain($data)
            : backup_datafile_is_plain($data);

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
        // Wiederherstellen darf die Rechte nicht lockern: Das Installations-
        // Script setzt config.php auf 640, ohne diese Zeile läge sie danach
        // mit der Standard-Umask (meist 0644) da - samt Setup-Schlüssel.
        @chmod($path, 0640);
    }

    // config.php wird per require_once geladen - ohne Invalidierung könnte ein
    // aktiver Opcache (typisch auf NAS/PHP-FPM) den alten Stand weiter ausliefern.
    if (isset($contents[dirname(__DIR__) . '/config.php']) && function_exists('opcache_invalidate')) {
        @opcache_invalidate(dirname(__DIR__) . '/config.php', true);
    }

    return true;
}
