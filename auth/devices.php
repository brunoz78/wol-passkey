<?php
require_once __DIR__ . '/config.php';

/*
  Verwaltung der WOL-Zielgeräte (Name => MAC-Adresse).
  Gespeichert wird in auth/devices-data.php im selben geschützten Format wie
  die Login-Daten. Beim ersten Aufruf wird die Liste einmalig aus der
  $maclist in config.php übernommen - danach ist diese Datei massgeblich.
*/

define('DEVICES_DATA_FILE', __DIR__ . '/devices-data.php');

/*
  Normalisiert eine MAC-Adresse in das Format AA:BB:CC:DD:EE:FF.
  Erlaubte Eingabeformate wie bisher in config.php: mit ":", "-", "." oder ohne
  Trennzeichen. Liefert null, wenn die Eingabe keine gültige MAC ist.
*/
function devices_normalize_mac($mac) {
    if (!is_string($mac) || preg_match('/[^A-Fa-f0-9\.\-: ]/', $mac)) {
        return null;
    }
    $hex = preg_replace('/[^A-Fa-f0-9]/', '', $mac);
    if (strlen($hex) !== 12) {
        return null;
    }
    return strtoupper(join(':', str_split($hex, 2)));
}

/*
  Normalisiert eine IP-Adresse (v4 oder v6). Leerer String = keine IP
  hinterlegt (gültig, IP ist optional). Liefert null bei ungültiger Eingabe.
*/
function devices_normalize_ip($ip) {
    $ip = trim((string)$ip);
    if ($ip === '') {
        return '';
    }
    return filter_var($ip, FILTER_VALIDATE_IP) !== false ? $ip : null;
}

/*
  Bringt die Geräteliste ins aktuelle Format ['mac' => ..., 'ip' => ...].
  Ältere Dateien (vor der IP-Erreichbarkeitsprüfung) speicherten pro Gerät
  nur die MAC-Adresse als String - die werden hier transparent migriert.
*/
function devices_normalize_all(array $devices) {
    $out = [];
    foreach ($devices as $name => $entry) {
        if (is_string($entry)) {
            $out[$name] = ['mac' => $entry, 'ip' => ''];
        } elseif (is_array($entry)) {
            $out[$name] = ['mac' => $entry['mac'] ?? '', 'ip' => $entry['ip'] ?? ''];
        }
    }
    return $out;
}

/*
  Einmalige Übernahme der Geräte aus config.php ($maclist).
  Das include innerhalb der Funktion hält die config-Variablen lokal.
*/
function devices_seed_from_config() {
    $maclist = [];
    @include dirname(__DIR__) . '/config.php';

    $seed = [];
    foreach ((array)$maclist as $name => $mac) {
        $norm = devices_normalize_mac($mac);
        if ($norm !== null && is_string($name) && $name !== '') {
            $seed[$name] = $norm;
        }
    }
    return $seed;
}

function devices_load() {
    if (!is_file(DEVICES_DATA_FILE)) {
        $seed = devices_normalize_all(devices_seed_from_config());
        devices_save($seed); // best effort - bei fehlendem Schreibrecht bleibt der Seed trotzdem nutzbar
        return $seed;
    }

    $raw = file_get_contents(DEVICES_DATA_FILE);
    $marker = '?>';
    $pos = strpos($raw, $marker);
    $encoded = $pos === false ? '' : trim(substr($raw, $pos + strlen($marker)));
    $data = null;

    if ($encoded !== '') {
        $json = base64_decode($encoded, true);
        $data = $json !== false ? json_decode($json, true) : null;
    }

    return devices_normalize_all(is_array($data) ? $data : []);
}

/*
  Speichert die Geräteliste. Gibt false zurück, wenn das Schreiben fehlschlägt
  (typisch: Ordner auth/ ist für den Webserver-Benutzer nicht beschreibbar).
*/
function devices_save(array $devices) {
    $encoded = base64_encode(json_encode($devices, JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE));
    $content = "<?php http_response_code(403); exit; ?>\n" . $encoded . "\n";

    $tmp = DEVICES_DATA_FILE . '.tmp';
    if (@file_put_contents($tmp, $content, LOCK_EX) !== strlen($content)) {
        return false;
    }
    if (!@rename($tmp, DEVICES_DATA_FILE)) {
        @unlink($tmp);
        return false;
    }
    return true;
}

function devices_storage_writable() {
    return is_file(DEVICES_DATA_FILE)
        ? is_writable(DEVICES_DATA_FILE) && is_writable(__DIR__)
        : is_writable(__DIR__);
}
