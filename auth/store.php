<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/datafile.php';

/*
  Speichert Login-Passwort-Hash + Passkey-Credentials in auth/data.php.
  Die Datei beginnt mit einem PHP-Tag, der bei direktem Web-Zugriff sofort
  abbricht (403) - unabhängig davon, ob der Webserver .htaccess-Regeln
  beachtet. Die eigentlichen Daten stehen base64-kodiert danach.
*/

/*
  Fehler, dessen Text bewusst für die Anzeige gedacht ist (bereits übersetzt).
  Die WebAuthn-Endpunkte unterscheiden daran, was sie dem Browser zeigen
  dürfen: eigene Meldungen ja, der Wortlaut unerwarteter Ausnahmen nicht -
  der kann Interna wie Dateipfade preisgeben.
*/
class WolUserMessage extends Exception {}

function auth_default_data() {
    return [
        'password_hash' => null,
        'user_id' => bin2hex(random_bytes(16)),
        'credentials' => [],
        'failed_attempts' => 0,
        'locked_until' => 0,
        // Eigene Zähler für setup.php: Ein Rateangriff auf den Setup-Schlüssel
        // soll nicht nebenbei die Passwort-Anmeldung sperren und damit den
        // rechtmässigen Benutzer aussperren.
        'setup_failed_attempts' => 0,
        'setup_locked_until' => 0,
    ];
}

/* Füllt fehlende Felder mit Default auf (z.B. nach einem Update). */
function auth_normalize($data) {
    return (is_array($data) ? $data : []) + auth_default_data();
}

function auth_load() {
    return auth_normalize(wol_datafile_read(AUTH_DATA_FILE));
}

/*
  Lese-Ändern-Schreiben unter exklusiver Sperre: $fn bekommt die aktuellen
  Daten und liefert die neuen zurück (null = nichts ändern).

  Zwingend überall dort, wo der neue Wert vom alten abhängt. Mit
  auth_load() + auth_save() lesen parallele Anfragen denselben Stand und
  überschreiben sich gegenseitig - beim Zähler der Fehlanmeldungen hätte
  das die Sperre nach fünf Versuchen praktisch ausgehebelt, weil gleichzeitig
  abgeschickte Versuche alle von null hochgezählt hätten.
*/
function auth_update(callable $fn) {
    return wol_datafile_update(AUTH_DATA_FILE, function ($raw) use ($fn) {
        return $fn(auth_normalize($raw));
    });
}

/**
 * Speichert die Auth-Daten. Gibt false zurück, wenn das Schreiben fehlschlägt
 * (typisch: Ordner auth/ ist für den Webserver-Benutzer nicht beschreibbar).
 */
function auth_save(array $data) {
    // Ersetzt den gesamten Inhalt - ebenfalls unter der Sperre, sonst könnte
    // ein ungesperrter Schreibvorgang eine laufende Änderung überschreiben.
    return wol_datafile_update(AUTH_DATA_FILE, function () use ($data) {
        return $data;
    });
}

/**
 * Prüft, ob auth_save überhaupt funktionieren kann (Schreibrecht im Ordner).
 */
function auth_storage_writable() {
    return is_file(AUTH_DATA_FILE) ? is_writable(AUTH_DATA_FILE) && is_writable(__DIR__) : is_writable(__DIR__);
}
