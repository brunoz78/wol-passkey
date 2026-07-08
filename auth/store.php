<?php
require_once __DIR__ . '/config.php';

/*
  Speichert Login-Passwort-Hash + Passkey-Credentials in auth/data.php.
  Die Datei beginnt mit einem PHP-Tag, der bei direktem Web-Zugriff sofort
  abbricht (403) - unabhängig davon, ob der Webserver .htaccess-Regeln
  beachtet. Die eigentlichen Daten stehen base64-kodiert danach.
*/

function auth_default_data() {
    return [
        'password_hash' => null,
        'user_id' => bin2hex(random_bytes(16)),
        'credentials' => [],
        'failed_attempts' => 0,
        'locked_until' => 0,
    ];
}

function auth_load() {
    $defaults = auth_default_data();

    if (!is_file(AUTH_DATA_FILE)) {
        return $defaults;
    }

    $raw = file_get_contents(AUTH_DATA_FILE);
    $marker = '?>';
    $pos = strpos($raw, $marker);
    $encoded = $pos === false ? '' : trim(substr($raw, $pos + strlen($marker)));
    $data = null;

    if ($encoded !== '') {
        $json = base64_decode($encoded, true);
        $data = $json !== false ? json_decode($json, true) : null;
    }

    if (!is_array($data)) {
        $data = [];
    }

    // fehlende Felder mit Default auffüllen (z.B. nach Update)
    return $data + $defaults;
}

/**
 * Speichert die Auth-Daten. Gibt false zurück, wenn das Schreiben fehlschlägt
 * (typisch: Ordner auth/ ist für den Webserver-Benutzer nicht beschreibbar).
 */
function auth_save(array $data) {
    // JSON_INVALID_UTF8_SUBSTITUTE: falls z.B. ein Gerätename beim Kürzen
    // mitten in einem Umlaut abgeschnitten wurde, darf das Speichern nicht scheitern.
    $encoded = base64_encode(json_encode($data, JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE));
    $content = "<?php http_response_code(403); exit; ?>\n" . $encoded . "\n";

    $tmp = AUTH_DATA_FILE . '.tmp';
    if (@file_put_contents($tmp, $content, LOCK_EX) !== strlen($content)) {
        return false;
    }
    if (!@rename($tmp, AUTH_DATA_FILE)) {
        @unlink($tmp);
        return false;
    }
    return true;
}

/**
 * Prüft, ob auth_save überhaupt funktionieren kann (Schreibrecht im Ordner).
 */
function auth_storage_writable() {
    return is_file(AUTH_DATA_FILE) ? is_writable(AUTH_DATA_FILE) && is_writable(__DIR__) : is_writable(__DIR__);
}
