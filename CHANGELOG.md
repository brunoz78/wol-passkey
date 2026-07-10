# Changelog

Alle nennenswerten Änderungen an diesem Projekt.
Das Format orientiert sich an [Keep a Changelog](https://keepachangelog.com/de/1.1.0/),
die Versionsnummern an [Semantic Versioning](https://semver.org/lang/de/).

## [1.1.1] – 2026-07-10

### Behoben
- **Sprachwechsel hinter einem Reverse Proxy** führte zu „Not Found": Nach dem
  Umschalten wurde auf den *internen* Pfad des Proxys umgeleitet (z.B.
  `/WOL/login.php` statt `/login.php`). Die Umleitung erfolgt jetzt relativ und
  funktioniert dadurch hinter jedem Proxy.

## [1.1.0] – 2026-07-10

### Hinzugefügt
- **Mehrsprachigkeit (Multilanguage):** Die Oberfläche gibt es jetzt auf
  **Deutsch** und **Englisch**. Die Sprache lässt sich im Hamburger-Menü
  umschalten (auf der Login- und Setup-Seite über den `DE|EN`-Umschalter in der
  Kopfleiste) und wird pro Browser gemerkt.
- Automatische Spracherkennung beim ersten Besuch anhand der Browser-Einstellung
  (`Accept-Language`), mit Englisch als Rückfallwert.
- Weitere Sprachen lassen sich ohne Code-Änderung ergänzen: eine Datei in
  `lang/` anlegen und den Sprachcode in `auth/i18n.php` eintragen.
- Englische Projektdokumentation: [README_en.md](README_en.md).
- Dieses Changelog.

### Geändert
- Im Passkey-Dialog des Geräts wird nun der in `config.php` gesetzte `$sitename`
  angezeigt statt eines fest verdrahteten Namens.

## [1.0.0] – 2026-07-09

### Hinzugefügt
- Wake on LAN per Magic Packet (UDP-Broadcast) im Heimnetz.
- Passwort-Login mit Sperre nach zu vielen Fehlversuchen.
- Passkeys (WebAuthn): Anmeldung per Fingerabdruck/Face ID, pro Gerät
  registrierbar; auf bekannten Geräten startet die Abfrage automatisch.
- Drei umschaltbare Designs (Hell, Dunkel, Bunt), die Wahl wird pro Browser
  gemerkt.
- Für Smartphones optimierte Oberfläche mit Hamburger-Menü und antippbaren
  Gerätekacheln.
- Geräteverwaltung im Browser (Zielgeräte hinzufügen/entfernen) ohne
  Datei-Editieren.
- Betrieb hinter gängigen Reverse Proxies (Nginx Proxy Manager, Traefik, Caddy,
  Synology DSM).
- Keine Datenbank: alle Daten liegen in selbstschützenden Dateien in `auth/`.
- Installations-ZIP als Release-Asset (`wol-passkey-<version>.zip`) sowie ein
  Build-Skript (`tools/build-release.php`) samt Windows-Starter.

[1.1.1]: https://github.com/brunoz78/wol-passkey/releases/tag/v1.1.1
[1.1.0]: https://github.com/brunoz78/wol-passkey/releases/tag/v1.1.0
[1.0.0]: https://github.com/brunoz78/wol-passkey/releases/tag/v1.0.0
