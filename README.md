# WOL mit Passkey-Login

Eine schlanke Wake-on-LAN-Weboberfläche für den Heimgebrauch – mit modernem
**Passkey-Login (Fingerabdruck / Face ID)** statt nur Passwort. Ein Ordner PHP,
keine Datenbank, kein Composer, keine Build-Tools: hochladen, Passwort setzen, fertig.

Vom Smartphone aus: Seite öffnen → Finger auflegen → Rechner aufwecken.

## Funktionen

- 🖥️ **Wake on LAN**: weckt Rechner im Heimnetz per Magic Packet (UDP-Broadcast)
- 🔐 **Login-Schutz**: Passwort-Login mit Sperre nach zu vielen Fehlversuchen
- 👆 **Passkeys (WebAuthn)**: Anmeldung per Fingerabdruck/Face ID, pro Gerät registrierbar;
  auf bekannten Geräten startet die Abfrage beim Öffnen der Seite automatisch
- 📱 **Für Smartphones optimiert**: grosse Buttons und Eingabefelder
- ⚙️ **Geräteverwaltung im Browser**: Zielgeräte (Name + MAC) hinzufügen und entfernen,
  ohne Dateien zu editieren
- 🔁 **Reverse-Proxy-tauglich**: funktioniert hinter Nginx Proxy Manager, DSM-Reverse-Proxy u.ä.
- 🗂️ **Keine Datenbank**: alle Daten liegen in selbstschützenden Dateien im Ordner `auth/`

## Voraussetzungen

- PHP **8.0 oder neuer** mit den Extensions **openssl** und **sockets**
  (kein mbstring, kein Composer nötig)
- Ein Webserver (Apache, nginx, ...) – auf Synology z.B. die Web Station
- **HTTPS** mit gültigem Zertifikat – ohne HTTPS verweigert der Browser Passkeys
- Der Server muss **im selben LAN** stehen wie die aufzuweckenden Geräte
  (Magic Packets sind Broadcasts und verlassen das lokale Netz nicht –
  ein gemieteter Webspace im Internet funktioniert daher nicht)
- Docker: nur mit `network_mode: host`, sonst kommen die Broadcasts nicht ins LAN

## Installation

1. Alle Dateien in einen Ordner des Webservers hochladen
2. `config.sample.php` nach `config.php` kopieren und anpassen:
   - `$setup_key`: **einen eigenen langen Zufallswert eintragen** (z.B. aus dem
     Passwort-Manager). Wer diesen Schlüssel kennt, kann das Login-Passwort
     zurücksetzen – geheim halten!
   - `$networkbroadcast`: Broadcast-Adresse des Heimnetzes
     (Netz `192.168.1.x` → `192.168.1.255`)
   - `$maclist`: optional erste Zielgeräte eintragen (später bequem über die
     Weboberfläche pflegbar)
3. Dem Webserver-Benutzer **Schreibrechte auf den Ordner `auth/`** geben
   (Synology: File Station → Ordner `auth` → Eigenschaften → Berechtigung →
   Gruppe `http` → Lesen/Schreiben)
4. `https://deine-domain/setup.php` aufrufen, Setup-Schlüssel eingeben und
   Login-Passwort setzen
5. Anmelden und unter **„Passkey verwalten"** den Fingerabdruck des Geräts
   registrieren – ab dann geht der Login ohne Passwort

## Betrieb hinter einem Reverse Proxy

Passkeys sind an die Domain gebunden, die im Browser steht. Damit der Server
diese Domain kennt, muss der Proxy zwei Header mitschicken:

**Nginx Proxy Manager** (Custom Location bzw. Advanced-Tab):

```nginx
location / {
    proxy_pass http://192.168.1.10/wol/;
    proxy_set_header X-Forwarded-Host $host;
    proxy_set_header X-Forwarded-Proto $scheme;
}
```

**Synology DSM-Reverse-Proxy**: Systemsteuerung → Anmeldeportal → Erweitert →
Reverse Proxy → Eintrag bearbeiten → Benutzerdefinierte Kopfzeile →
`X-Forwarded-Host` = `$host` und `X-Forwarded-Proto` = `$scheme`.

Ohne diese Header erscheint bei der Passkey-Registrierung die Fehlermeldung
*„The relying party ID is not a registrable domain suffix of, nor equal to the
current domain"*.

**Wichtig:** Ein Passkey gilt nur für die Domain, unter der er registriert
wurde. Die Seite also immer über dieselbe Adresse aufrufen (Lesezeichen!).

## Sicherheitshinweise

- Die Anwendung ist bewusst ein **Ein-Benutzer-System** für den Heimgebrauch:
  ein gemeinsames Passwort, eine gemeinsame Passkey-Liste – keine Benutzerkonten,
  keine Rollen
- `config.php` (enthält den Setup-Schlüssel) niemals veröffentlichen;
  sie steht deshalb in der `.gitignore`
- Die Datendateien in `auth/` (Passwort-Hash, Passkey-Schlüssel, Geräteliste)
  schützen sich selbst gegen direkten Web-Zugriff (403). Die mitgelieferten
  `.htaccess`-Dateien blockieren die Ordner auf Apache zusätzlich; auf nginx
  empfiehlt sich analog:

  ```nginx
  location ~ ^/(auth|lib)/ { deny all; }
  ```

- Nach 5 falschen Passwort-Versuchen wird das Login 5 Minuten gesperrt
  (einstellbar in `auth/config.php`)
- Passwort vergessen? `setup.php` aufrufen und mit dem Setup-Schlüssel ein
  neues setzen

## Credits

- Ursprüngliches WOL-Skript: © 2014 [Barry Schiffer](http://www.barryschiffer.com),
  erweitert von Manuel Azevedo
- WebAuthn-Bibliothek: [lbuchs/WebAuthn](https://github.com/lbuchs/WebAuthn)
  (MIT-Lizenz, im Ordner `lib/webauthn/` enthalten)
- Icon: Streamline Icons Free Pack via Iconfinder

## Lizenz

MIT – siehe [LICENSE](LICENSE).
