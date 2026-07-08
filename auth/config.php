<?php
/*
  Technische Auth-Konfiguration.
  Persönliche Einstellungen (Setup-Schlüssel, Seitenname, Netzwerk) gehören
  NICHT hierhin, sondern in die config.php im Hauptordner (siehe
  config.sample.php). Diese Datei muss beim Installieren nicht angepasst werden.
*/

// Benutzer-Einstellungen laden ($setup_key, $sitename, ...)
require_once dirname(__DIR__) . '/config.php';

// Name, der im Passkey-Dialog des Geräts angezeigt wird
define('WEBAUTHN_RP_NAME', isset($sitename) && $sitename !== '' ? $sitename : 'Wake on LAN');

// Die RP-ID (= Domain, an die Passkeys gebunden sind) wird automatisch aus der
// aufgerufenen Adresse übernommen, damit die Seite über mehrere Domains
// funktioniert. Achtung: Ein Passkey gilt immer nur für die Domain, unter der
// er registriert wurde.
//
// Hinter einem Reverse Proxy steht die Original-Domain nicht im Host-Header,
// sondern in X-Forwarded-Host - deshalb hat dieser Header Vorrang. Der Proxy
// muss dafür "X-Forwarded-Host $host" setzen (siehe README).
$waHost = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
$waHost = trim(explode(',', $waHost)[0]);       // bei Proxy-Ketten: erster Eintrag
$waHost = strtolower($waHost);
$waHost = preg_replace('/:\d+$/', '', $waHost); // Port entfernen (z.B. :8088)
define('WEBAUTHN_RP_ID', $waHost);

define('WEBAUTHN_TIMEOUT', 60);

// Setup-Schlüssel aus config.php; setup.php verweigert den Dienst, solange
// der Platzhalter aus config.sample.php nicht ersetzt wurde.
define('SETUP_KEY', isset($setup_key) && is_string($setup_key) ? $setup_key : '');
define('SETUP_KEY_PLACEHOLDER', 'BITTE-EIGENEN-SCHLUESSEL-EINTRAGEN');

define('AUTH_DATA_FILE', __DIR__ . '/data.php');
define('AUTH_SESSION_NAME', 'wol_auth');

// Nach so vielen fehlgeschlagenen Passwort-Versuchen wird das Login gesperrt.
define('AUTH_MAX_ATTEMPTS', 5);
define('AUTH_LOCKOUT_SECONDS', 300);
