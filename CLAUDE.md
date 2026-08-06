# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Projekt

Schlanke Wake-on-LAN-Weboberfläche mit Passkey-Login (WebAuthn). Reines Flat-File-PHP (≥ 8.0, Extensions `openssl` + `sockets`): kein Framework, kein Composer, keine Datenbank, kein Frontend-Build. Zielumgebung ist ein Heimserver/NAS, oft hinter einem Reverse Proxy.

**Sprache:** Code-Kommentare, Commit-Messages und die Ausgangs-UI-Texte sind Deutsch. Dabei bleiben. README.md (de) und README_en.md sind Spiegel – bei Doku-Änderungen beide pflegen.

## Befehle

```
# Dev-Server (bevorzugt über die Browser-Preview mit launch.json-Config "wol-php"):
php -d extension=openssl -d extension=sockets -S 127.0.0.1:8765 -t .

# Syntax-Check (es gibt keine Tests und keinen Linter):
php -l <datei.php>

# Release-ZIP bauen -> dist/wol-passkey-<version>.zip
build-release.cmd 1.2.3            # Windows-Starter
php -d extension=zip tools/build-release.php 1.2.3
```

Neue Dateien, die nur der Entwicklung dienen, müssen in `tools/build-release.php` in die Ausschlusslisten (`$excludes` / `$excludeDirs` / `$excludeGlobs`) eingetragen werden, sonst landen sie im Installations-ZIP.

## Architektur

**Seitenaufbau:** Jede Seite beginnt mit `require auth/session.php` – das lädt die Config, startet die Session, ruft `i18n_init()` auf (muss vor jeder Ausgabe laufen, macht ggf. Redirect) und stellt `require_login()`, `csrf_token()` / `csrf_check()` bereit. Geschützte Seiten rufen danach `require_login()`. HTML kommt aus `partials/head.php` (Topbar, Hamburger-Menü, Theme-Umschalter, SVG-Icon-Sprite, `window.WOL_I18N`) und `partials/foot.php`; Styling ausschliesslich in `smartphone.css` (drei Themes über `data-theme`-Attribut, gemerkt in localStorage via `assets/theme.js`).

**`auth/` enthält Code UND Laufzeitdaten:**
- Code: `config.php` (technische Konstanten, RP-ID-Ermittlung), `session.php`, `store.php` (Login-/Passkey-Daten), `devices.php` (Geräteliste), `i18n.php`
- Laufzeitdaten: `data.php` und `devices-data.php` – selbstschützende Dateien (PHP-Header mit 403-exit, danach base64-kodiertes JSON). Werden von `auth_save()` / `devices_save()` atomar geschrieben (tmp + rename). Niemals von Hand editieren; das Format in `store.php` ist massgeblich.
- Die `$maclist` in der Root-`config.php` ist nur **Erstbefüllung** – sobald `devices-data.php` existiert, zählt nur noch die Weboberfläche.

**Config-Trennung:** Root-`config.php` (gitignored, enthält den geheimen `$setup_key`) vs. `config.sample.php` (Vorlage, versioniert). Neue Benutzer-Einstellungen gehören in beide; technische Konstanten in `auth/config.php`.

**WebAuthn:** Vier JSON-Endpoints im Root (`webauthn-{register,login}-{options,verify}.php`), Client-Seite in `assets/webauthn-client.js`. Challenge liegt in der Session; die RP-ID wird in `auth/config.php` dynamisch aus `X-Forwarded-Host` bzw. `Host` abgeleitet. Die Bibliothek `lib/webauthn/` ist vendored Third-Party-Code (lbuchs/WebAuthn) – nicht modifizieren.

**i18n (`auth/i18n.php`):** `lang/de.php` ist die Ausgangssprache; andere Sprachen überschreiben per `array_merge`, fehlende Schlüssel fallen auf Deutsch zurück. Ausgabe über `t()` (roh, sprintf-fähig) und `te()` (HTML-escaped). Schlüssel mit Präfix `js.` werden als `window.WOL_I18N` ins JavaScript exportiert. Neue UI-Texte immer in `lang/de.php` **und** `lang/en.php` eintragen; neue Sprache = Datei kopieren + in `i18n_languages()` registrieren. Der Sprachwechsel `?lang=xx` setzt ein Cookie und leitet **relativ** um.

**Reverse Proxy ist eine feste Randbedingung:** `X-Forwarded-Proto` entscheidet über Secure-Cookies (session.php, i18n.php), `X-Forwarded-Host` über die WebAuthn-RP-ID, und Redirects/Links müssen relativ bleiben (interner Pfad ≠ öffentlicher Pfad). Bei Änderungen an Cookies, Redirects oder Host-Ermittlung dieses Szenario mitdenken.

## Konventionen

- Ausgaben immer über `htmlspecialchars()` bzw. `te()` escapen; zustandsändernde Requests mit `csrf_check()` absichern.
- `CHANGELOG.md` wird gepflegt; Versionsschema `x.y.z`.
