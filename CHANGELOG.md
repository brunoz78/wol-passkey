# Changelog

Alle nennenswerten Änderungen an diesem Projekt.
Das Format orientiert sich an [Keep a Changelog](https://keepachangelog.com/de/1.1.0/),
die Versionsnummern an [Semantic Versioning](https://semver.org/lang/de/).

## [1.6.2] – 2026-10-09

### Geändert
- **Geräte nachträglich bearbeiten:** Name, MAC- und IP-Adresse eines
  gespeicherten Geräts lassen sich jetzt ändern – bisher half nur Löschen und
  neu Erfassen. Ein Tippen auf den Eintrag klappt die Bearbeitung auf. Beim
  Umbenennen bleiben Position in der Liste, Zeitpläne und die Online-Historie
  des Geräts erhalten; die Änderung steht im Verlauf.

## [1.6.1] – 2026-09-28

### Hinzugefügt
- **Zeitpläne bearbeiten:** Bisher liess sich ein gespeicherter Eintrag nur
  löschen und neu anlegen. Jetzt klappt ein Tippen auf den Eintrag die
  Bearbeitung auf, in der sich Uhrzeit und Wochentage ändern lassen. Die
  Änderung steht auch im Verlauf.

## [1.6.0] – 2026-09-28

### Hinzugefügt
- **Zeitgesteuertes Aufwecken:** Neue Seite „Zeitplan" im Menü. Pro Gerät
  lassen sich Uhrzeit und Wochentage festlegen, z.B. Mo–Fr um 07:30, auch
  mehrere Einträge pro Gerät. Ausgeführt wird das von der Hintergrundprüfung
  (`cron.php`) – ohne sie passiert nichts, worauf die Seite deutlich hinweist.
  Ein Gerät, das nachweislich schon läuft, wird nicht geweckt; verpasste
  Zeitpunkte werden nicht nachgeholt, damit ein Rechner nicht Stunden später
  unvermittelt startet. Die Zeitpläne stehen im Verlauf und werden bei der
  Sicherung mitgenommen, weil sie am Gerät hängen.
- **Zeitzone einstellbar:** Neu `$timezone` in der `config.php`. Ohne Eintrag
  wird die Zeitzone des Servers übernommen (Linux: `/etc/timezone`), sonst
  bleibt es bei UTC. Das betrifft die Zeitpläne und die Zeitangaben im
  Verlauf; die Seite „Zeitplan" zeigt die Serverzeit zur Kontrolle an.

## [1.5.0] – 2026-09-11

### Hinzugefügt
- **Verlauf:** Neue Seite im Menü. Sie zeigt, wann welches Gerät aufgeweckt
  wurde, Anmeldungen mit Passwort oder Passkey, falsche Passwörter und
  Sperren, Online-/Offline-Wechsel sowie hinzugefügte oder entfernte Geräte,
  Passkeys und das Setzen des Passworts. Mit Filtern, Zeiten in der Zeitzone
  des Browsers und gespeichert in `auth/log-data.php` (die letzten 500
  Einträge). Hinter einem Reverse Proxy wird die echte Adresse des Aufrufers
  protokolliert, aber nur wenn die Anfrage aus dem eigenen Netz kommt.
- **Seit wann läuft ein Gerät:** Die Gerätekacheln zeigen „Läuft seit
  3 Std. 12 Min." bzw. „Offline seit 2 Tg.".
- **Hintergrundprüfung (`cron.php`):** Sie prüft jede Minute alle Geräte mit
  IP, unabhängig davon, ob jemand die Seite offen hat. Erst damit sind die
  Zeiten genau. Ohne sie wird der Status nur bei Seitenaufrufen erfasst: Die
  Kacheln zeigen dann ehrlich „spätestens seit 14:32", und der Verlauf weist
  auf die fehlende Prüfung hin. Im Proxmox-LXC richtet das Script sie als
  systemd-Timer ein, auch bei bestehenden Containern mit dem nächsten
  `update`. Überall sonst genügt eine Zeile in der Crontab (siehe README).
- **Rückmeldung nach dem Aufwecken:** Ist beim Gerät eine IP hinterlegt, fragt
  die Seite nach dem Aufwecken bis zu 3 Minuten lang nach und meldet, sobald
  es erreichbar ist - statt nur „Aufwecken gesendet". Die Meldung nennt jetzt
  den Gerätenamen statt der MAC-Adresse.

### Geändert
- **Magic Packet an mehrere Ziele:** Bisher ging genau ein Paket an die
  Broadcast-Adresse aus `config.php`. Jetzt zusätzlich an 255.255.255.255 und
  - falls eine IPv4 hinterlegt ist - direkt an das Gerät, jeweils auf dem
  eingestellten Port sowie auf 9 und 7. Manche Netzwerkkarten und Switches
  verschlucken ein einzelnes Paket; der direkte Versand hilft bei Geräten in
  einem anderen Subnetz, wenn der Router Unicast-WoL weiterleitet.

### Behoben
- **README zeigte ein veraltetes Proxmox-Installationskommando:** Der Befehl
  mit `misc/run.sh` funktioniert seit der Umstellung des Frameworks auf
  `community-scripts/core` nicht mehr. Die README zeigt jetzt denselben Aufruf
  wie `proxmox/README.md`.

## [1.4.4] – 2026-08-08

### Hinzugefügt
- **Automatische Passkey-Abfrage abschaltbar:** Auf Geräten mit Passkey startete
  die Abfrage beim Öffnen der Loginseite immer von selbst. Am Handy ist das
  bequem, am Desktop stört es, wenn man sich lieber mit dem Passwort anmeldet.
  Unter dem Passkey-Knopf steht jetzt „Beim Öffnen automatisch fragen" - die
  Einstellung gilt pro Gerät und Browser. Sie funktioniert zuverlässig auch
  beim Wechsel zwischen Tabs, im Hintergrund und bei mehreren gleichzeitigen
  Anmeldeversuchen.
- **Manueller Update-Check:** Im Menü gibt es jetzt „Jetzt nach Updates
  suchen" - fragt sofort bei GitHub nach, statt bis zu 24h auf den
  automatischen Hinweis zu warten.

  Randnotiz für alle, die eine Verknüpfung auf dem Startbildschirm
  angelegt haben: Wurde sie in einem Moment erstellt, in dem die Adresse
  noch `?logout=1` enthielt, speichert Android das dauerhaft mit - jeder
  Tipp auf das Icon würde dann fälschlich als "gerade abgemeldet" gelten und
  die automatische Passkey-Abfrage bliebe aus. Die Verknüpfung einmal neu
  anlegen (von der reinen Adresse ohne Zusatz) behebt das.

### Behoben
- **Browser behielten alte JS- und CSS-Dateien:** Der Webserver liefert diese
  Dateien ohne Cache-Header aus, Browser durften sie deshalb beliebig lange
  behalten. Nach einem Update entstand so eine Mischung aus neuem PHP und
  alter JavaScript-Datei mit schwer nachvollziehbaren Fehlern. Die Verweise
  tragen jetzt die Versionsnummer (`?v=…`), womit jede neue Version frisch
  geladen wird.
- **Klick auf „Aufwecken" ohne ausgewähltes Gerät tat scheinbar nichts:** Die
  Radio-Buttons der Geräteliste sind unsichtbar gemacht (eigenes Aussehen der
  Karten), wodurch der Browser seine „Bitte auswählen"-Sprechblase an eine
  0×0 grosse Stelle verankerte - sie war da, aber nicht zu sehen. Der Knopf
  bleibt jetzt hellgrau und inaktiv, bis ein Gerät ausgewählt ist.

## [1.4.3] – 2026-08-08

### Behoben
- **Ausgeschaltete Geräte wurden als online angezeigt:** Die Erreichbarkeits-
  prüfung schloss bisher aus einem *schnellen* Fehlschlag, dass das Gerät läuft
  und nur der Port zu ist. Das trifft im selben Subnetz aber nicht zu: Bekommt
  der Kernel auf seine ARP-Anfrage keine Antwort, merkt er sich das Gerät eine
  Weile als „failed" und beantwortet weitere Verbindungsversuche sofort selbst
  mit „no route to host" - genauso schnell wie eine echte Ablehnung. Als
  laufend gilt jetzt nur noch, was das Zielgerät selbst beantwortet hat
  (Verbindung angenommen oder ausdrücklich abgelehnt).

### Geändert
- Die Ports werden gleichzeitig statt nacheinander geprüft, und
  `device-status.php` gibt die Session sofort wieder frei. Ohne das würde die
  korrigierte Prüfung deutlich länger dauern, weil ein ausgeschaltetes Gerät
  jetzt immer ins Zeitlimit läuft und PHPs Session-Sperre die parallelen
  Abfragen der Gerätekacheln hintereinander abgearbeitet hätte.

## [1.4.2] – 2026-08-08

### Behoben
- **MAC-Adresse wurde wieder überdeckt:** Der in 1.4.1 hinzugekommene Ziehgriff
  brauchte so viel Breite, dass die MAC-Adresse auf schmalen Bildschirmen erneut
  vom „Entfernen"-Button angeschnitten wurde (derselbe Fehler wie in 1.3.2).
  Abstände und Symbolgrösse in der Liste sind jetzt knapper, und die MAC-Adresse
  wird notfalls sauber gekürzt statt unter den Button zu rutschen.

### Geändert
- **Eigene Desktop-Ansicht:** Ab 1000px Fensterbreite bleibt das Menü
  dauerhaft als Seitenleiste offen (der Hamburger entfällt), der Inhalt
  bekommt eine angenehmere Breite. Bisher war am grossen Bildschirm
  einfach das Smartphone-Layout in der Mitte zu sehen. Login und Setup
  haben kein Menü und bleiben unverändert zentriert; bis 999px ändert
  sich nichts.
- Screenshots aktualisiert und um die Sicherungs-Seite ergänzt; die englische
  Dokumentation zeigt jetzt die englische Oberfläche statt der deutschen.

## [1.4.1] – 2026-08-07

### Hinzugefügt
- **Geräte-Reihenfolge per Drag&Drop änderbar:** In der Geräteverwaltung
  lässt sich die Reihenfolge jetzt per Ziehgriff direkt verschieben (Maus
  und Touch) - bisher entsprach sie fest der Erfassungsreihenfolge. Wirkt
  sich auch auf die Aufwecken-Seite aus, da beide dieselbe gespeicherte
  Reihenfolge nutzen.

### Behoben
- **Dateiauswahl der Sicherung blieb unübersetzt:** Beschriftung („Datei
  auswählen" / „Keine ausgewählt") und Pflichtfeld-Meldung kamen vom Browser
  und richteten sich nach dessen Sprache statt nach der App-Sprache. Die
  Dateiauswahl hat jetzt eine eigene, übersetzte Beschriftung und zeigt den
  gewählten Dateinamen an.

## [1.4.0] – 2026-08-07

### Hinzugefügt
- **Sicherung & Wiederherstellung:** Neuer Menüpunkt „Sicherung", über den sich
  `config.php` sowie die Laufzeitdaten (`auth/data.php`, `auth/devices-data.php`
  - Login-Passwort, Passkeys, Geräteliste) als ZIP herunterladen und aus einer
  ZIP-Datei wiederherstellen lassen (`auth/backup.php`, `backup.php`). Fehlt
  eine Datei im ZIP, bleibt sie beim Wiederherstellen unangetastet. Ohne die
  PHP-Extension `zip` zeigt die Seite einen entsprechenden Hinweis.
- **Französisch und Spanisch:** Zwei weitere Sprachen (`lang/fr.php`,
  `lang/es.php`), in `i18n_languages()` registriert - insgesamt vier
  Sprachen: Deutsch, Englisch, Französisch, Spanisch.
- **Sprachauswahl als Untermenü:** Der Sprachbereich im Hamburger-Menü ist
  jetzt ein Ausklapp-Untermenü (zeigt kollabiert nur die aktuelle Sprache),
  der kompakte Umschalter auf Login/Setup ein Dropdown-Button statt einer
  Reihe von Kürzeln - bleibt damit übersichtlich, auch wenn später weitere
  Sprachen dazukommen.
- **Passkey löschen:** Registrierte Passkeys lassen sich in „Passkey
  verwalten" jetzt auch wieder entfernen (Papierkorb-Symbol, mit
  Bestätigungsdialog) - bisher liessen sie sich nur auflisten und neu
  registrieren.

### Geändert
- **Online-Status besser sichtbar:** Der grüne Punkt an der Gerätekachel
  (siehe [1.3.0]) war sehr klein - er ist jetzt grösser und hat einen
  dezenten Leuchteffekt.

## [1.3.2] – 2026-08-07

### Behoben
- **MAC-Adresse in der Geräteverwaltung überdeckt:** Auf schmalen Bildschirmen
  (Smartphone-Breite) liess der "Entfernen"-Button neben Gerätename und MAC
  zu wenig Platz - die MAC-Adresse wurde vom Button teilweise überdeckt. Der
  Button zeigt jetzt nur noch das Papierkorb-Symbol (wie schon der
  Speichern-Button bei der IP-Adresse), was genug Platz für die volle
  MAC-Adresse schafft.

## [1.3.1] – 2026-08-06

### Behoben
- **Englische Browser-Fehlermeldungen beim Passkey-Dialog:** Bricht man den
  Dialog ab oder läuft er ins Zeitlimit, reichte die Oberfläche die rohe
  `DOMException` des Browsers durch – englischer Text samt Link zur
  WebAuthn-Spezifikation. Die gängigen Fälle werden jetzt anhand von
  `err.name` übersetzt (Abbruch, Passkey bereits vorhanden, Hostname passt
  nicht zur RP-ID, keine Unterstützung); unbekannte Fehler zeigen weiterhin
  den Originaltext.

## [1.3.0] – 2026-08-04

### Hinzugefügt
- **Online-Status pro Gerät:** Optionales IP-Adress-Feld in der
  Geräteverwaltung. Ist eine IP hinterlegt, prüft der Server auf der
  Aufwecken-Seite per TCP-Verbindungsversuch (ein paar gängige Ports,
  kein `shell_exec()`, keine Root-Rechte nötig) ob das Gerät bereits läuft,
  und zeigt das mit einem grünen Punkt an der Gerätekachel an
  (`auth/reachability.php`, `device-status.php`, `assets/device-status.js`).
- **Proxmox-Installation per Script:** Neuer Ordner `proxmox/` mit den drei
  Dateien nach der Spezifikation von [community-scripts](https://community-scripts.org)
  (ct-, install- und json-Datei). Ein Aufruf auf der Proxmox-Shell legt einen
  Debian-13-LXC mit nginx und PHP-FPM an, rollt das Release-ZIP aus, erzeugt
  einen zufälligen Setup-Schlüssel und leitet die Broadcast-Adresse aus dem
  Subnetz des Containers ab. Updates laufen über dieselbe Mechanik und sichern
  vorher `config.php` sowie die Laufzeitdaten. Anleitung in
  `proxmox/README.md`.
- **Projektlogo** (`docs/logo.svg`) für die Metadaten der Script-Sammlung.

### Behoben
- **Irreführende Passkey-Meldung ohne HTTPS:** Ohne gesicherte Verbindung
  blendet der Browser `window.PublicKeyCredential` komplett aus. Das wurde als
  „Dieser Browser unterstützt keine Passkeys" gemeldet, obwohl nur das
  Zertifikat fehlte. Die beiden Fälle werden jetzt unterschieden und der
  HTTPS-Hinweis benennt die tatsächliche Ursache.

## [1.2.0] – 2026-08-04

### Hinzugefügt
- **Update-Hinweis:** Nach dem Login prüft die App höchstens 1x täglich über
  die GitHub-Releases-API, ob eine neuere Version verfügbar ist, und zeigt
  dann einen Hinweis mit Link zur Releases-Seite an. Es wird nichts
  automatisch heruntergeladen oder installiert; das Ergebnis wird lokal
  gecacht (`auth/update-check-data.php`), Netzwerkfehler werden verschluckt.
- Neuer Abschnitt „Aktualisieren" im README mit Anleitung zum manuellen Update.
- **„Über"-Eintrag im Hamburger-Menü:** zeigt die installierte Version an und
  verlinkt auf die GitHub-Projektseite.

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

[1.4.4]: https://github.com/brunoz78/wol-passkey/releases/tag/v1.4.4
[1.4.3]: https://github.com/brunoz78/wol-passkey/releases/tag/v1.4.3
[1.4.2]: https://github.com/brunoz78/wol-passkey/releases/tag/v1.4.2
[1.4.1]: https://github.com/brunoz78/wol-passkey/releases/tag/v1.4.1
[1.4.0]: https://github.com/brunoz78/wol-passkey/releases/tag/v1.4.0
[1.3.1]: https://github.com/brunoz78/wol-passkey/releases/tag/v1.3.1
[1.3.0]: https://github.com/brunoz78/wol-passkey/releases/tag/v1.3.0
[1.2.0]: https://github.com/brunoz78/wol-passkey/releases/tag/v1.2.0
[1.1.1]: https://github.com/brunoz78/wol-passkey/releases/tag/v1.1.1
[1.1.0]: https://github.com/brunoz78/wol-passkey/releases/tag/v1.1.0
[1.0.0]: https://github.com/brunoz78/wol-passkey/releases/tag/v1.0.0
