<?php
/*
  Deutsche Texte. Dies ist die Ausgangssprache: Fehlt in einer anderen
  Sprachdatei ein Schlüssel, wird automatisch der Text von hier verwendet.

  Neue Sprache anlegen:
    1. Diese Datei nach lang/<code>.php kopieren (z.B. lang/fr.php)
    2. Die Texte rechts vom "=>" übersetzen
    3. Den Code in auth/i18n.php in i18n_languages() eintragen
*/
return [
    // ---------- Navigation / Kopfleiste ----------
    'nav.wake'        => 'Aufwecken',
    'nav.devices'     => 'Geräte verwalten',
    'nav.passkey'     => 'Passkey verwalten',
    'nav.logout'      => 'Abmelden',
    'nav.aria'        => 'Navigation',
    'nav.open'        => 'Menü öffnen',
    'nav.close'       => 'Menü schließen',
    'nav.language'    => 'Sprache',

    'theme.aria'      => 'Design wählen',
    'theme.light'     => 'Helles Design',
    'theme.dark'      => 'Dunkles Design',
    'theme.vivid'     => 'Buntes Design',
    'lang.aria'       => 'Sprache wählen',

    // ---------- Startseite (Aufwecken) ----------
    'index.sub'          => 'Gerät auswählen und aufwecken',
    'index.csrf'         => 'Ungültige Anfrage, bitte Formular erneut absenden.',
    'index.wake_failed'  => 'Magic Packet konnte nicht gesendet werden.',
    'index.wake_sent'    => 'Aufwecken gesendet an %s',
    'index.no_devices'   => 'Noch keine Geräte eingetragen.',
    'index.add_device'   => 'Gerät hinzufügen',
    'index.your_devices' => 'Deine Geräte',
    'index.wake'         => 'Aufwecken',

    // ---------- Login ----------
    'login.title'          => 'Login',
    'login.sub'            => 'Bitte anmelden',
    'login.csrf'           => 'Ungültige Anfrage, bitte Seite neu laden.',
    'login.locked'         => 'Zu viele Fehlversuche. Bitte in %d Minute(n) erneut versuchen.',
    'login.no_password'    => 'Es ist noch kein Passwort gesetzt. Bitte zuerst setup.php aufrufen.',
    'login.wrong_password' => 'Passwort ist falsch.',
    'login.with_passkey'   => 'Mit Passkey anmelden',
    'login.or_password'    => 'oder mit Passwort',
    'login.password'       => 'Passwort',
    'login.submit'         => 'Anmelden',

    // ---------- Setup ----------
    'setup.title'        => 'Setup',
    'setup.brand'        => 'Login-Setup',
    'setup.sub'          => 'Passwort setzen oder zurücksetzen',
    'setup.csrf'         => 'Ungültige Anfrage, bitte Seite neu laden.',
    'setup.locked'       => 'Setup gesperrt: Bitte zuerst in der Datei config.php einen eigenen '
                          . 'geheimen Wert für {key} eintragen (siehe config.sample.php) und die Datei neu hochladen.',
    'setup.not_writable' => 'Der Ordner „auth“ ist für den Webserver nicht beschreibbar. '
                          . 'Das Passwort kann so nicht gespeichert werden – bitte dem Web-Benutzer '
                          . '(Gruppe „http“) Schreibrechte auf den Ordner „auth“ geben.',
    'setup.key_wrong'    => 'Setup-Schlüssel ist falsch.',
    'setup.pw_too_short' => 'Das Passwort muss mindestens 8 Zeichen lang sein.',
    'setup.pw_mismatch'  => 'Die beiden Passwörter stimmen nicht überein.',
    'setup.saved'        => 'Passwort erfolgreich gesetzt. Du kannst dich jetzt einloggen.',
    'setup.save_failed'  => 'Speichern fehlgeschlagen: Der Ordner "auth" ist für den Webserver nicht '
                          . 'beschreibbar. Bitte dem Web-Benutzer (Gruppe "http") Schreibrechte auf den '
                          . 'Ordner geben und erneut versuchen.',
    'setup.to_login'     => 'Zum Login',
    'setup.key_label'    => 'Setup-Schlüssel',
    'setup.new_pw'       => 'Neues Passwort',
    'setup.new_pw2'      => 'Neues Passwort wiederholen',
    'setup.save'         => 'Passwort speichern',

    // ---------- Geräteverwaltung ----------
    'devices.title'         => 'Geräte',
    'devices.brand'         => 'Geräte verwalten',
    'devices.sub'           => 'Zielgeräte hinzufügen oder entfernen',
    'devices.csrf'          => 'Ungültige Anfrage, bitte erneut versuchen.',
    'devices.name_required' => 'Bitte einen Gerätenamen angeben.',
    'devices.name_too_long' => 'Der Gerätename darf höchstens 40 Zeichen lang sein.',
    'devices.mac_invalid'   => 'Die MAC-Adresse ist ungültig. Erlaubt sind 12 Hex-Zeichen, z.B. 00:11:22:33:44:55.',
    'devices.exists'        => 'Ein Gerät mit diesem Namen existiert bereits. Bitte zuerst entfernen oder anderen Namen wählen.',
    'devices.added'         => 'Gerät "%s" hinzugefügt.',
    'devices.removed'       => 'Gerät "%s" entfernt.',
    'devices.not_found'     => 'Gerät nicht gefunden.',
    'devices.save_failed'   => 'Speichern fehlgeschlagen: Der Ordner "auth" ist für den Webserver nicht beschreibbar.',
    'devices.not_writable'  => 'Der Ordner „auth“ ist für den Webserver nicht beschreibbar – '
                             . 'Änderungen können so nicht gespeichert werden.',
    'devices.none'          => 'Noch keine Geräte eingetragen.',
    'devices.your_devices'  => 'Deine Geräte',
    'devices.remove'        => 'Entfernen',
    'devices.confirm'       => 'Gerät "%s" wirklich entfernen?',
    'devices.new'           => 'Neues Gerät',
    'devices.name'          => 'Gerätename',
    'devices.name_ph'       => 'z.B. Wohnzimmer-PC',
    'devices.mac'           => 'MAC-Adresse',
    'devices.add'           => 'Gerät hinzufügen',

    // ---------- Passkey-Verwaltung ----------
    'passkey.title'          => 'Passkey',
    'passkey.brand'          => 'Passkey verwalten',
    'passkey.sub'            => 'Fingerabdruck / Face ID dieses Geräts',
    'passkey.registered'     => 'Registrierte Passkeys',
    'passkey.created'        => 'registriert am %s',
    'passkey.unnamed'        => 'Unbenannt',
    'passkey.device_name'    => 'Name für dieses Gerät',
    'passkey.device_name_ph' => 'z.B. Mein Smartphone',
    'passkey.register'       => 'Passkey registrieren',
    'passkey.default_device' => 'Unbenanntes Gerät',

    // ---------- WebAuthn-Meldungen vom Server ----------
    'wa.not_logged_in'   => 'Nicht angemeldet.',
    'wa.bad_request'     => 'Ungültige Anfrage.',
    'wa.no_reg_request'  => 'Keine aktive Registrierungsanfrage. Bitte Seite neu laden.',
    'wa.no_login_request' => 'Keine aktive Anmeldeanfrage. Bitte Seite neu laden.',
    'wa.no_passkey'      => 'Kein Passkey übermittelt.',
    'wa.unknown_passkey' => 'Dieser Passkey ist hier nicht registriert.',
    'wa.save_failed'     => 'Speichern fehlgeschlagen: Ordner "auth" ist für den Webserver nicht beschreibbar.',

    // ---------- Meldungen im Browser (JavaScript) ----------
    'js.no_support'           => 'Dieser Browser unterstützt keine Passkeys.',
    'js.confirm_biometry'     => 'Bitte Biometrie am Gerät bestätigen …',
    'js.confirm_fingerprint'  => 'Bitte Fingerabdruck/Face ID bestätigen …',
    'js.register_prepare'     => 'Fehler beim Vorbereiten der Registrierung.',
    'js.register_failed'      => 'Registrierung fehlgeschlagen.',
    'js.register_ok'          => 'Passkey erfolgreich registriert!',
    'js.login_prepare'        => 'Fehler beim Vorbereiten der Anmeldung.',
    'js.login_failed'         => 'Anmeldung fehlgeschlagen.',
    'js.login_ok'             => 'Angemeldet, du wirst weitergeleitet …',
    'js.unknown_error'        => 'Unbekannter Fehler',
];
