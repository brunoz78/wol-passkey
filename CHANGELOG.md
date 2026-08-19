# Changelog

Alle nennenswerten Änderungen an diesem Projekt.
Das Format orientiert sich an [Keep a Changelog](https://keepachangelog.com/de/1.1.0/),
die Versionsnummern an [Semantic Versioning](https://semver.org/lang/de/).

## [1.4.4] – 2026-08-08

### Hinzugefügt
- **Automatische Passkey-Abfrage abschaltbar:** Auf Geräten mit Passkey startete
  die Abfrage beim Öffnen der Loginseite immer von selbst. Am Handy ist das
  bequem, am Desktop stört es, wenn man sich lieber mit dem Passwort anmeldet.
  Unter dem Passkey-Knopf steht jetzt „Beim Öffnen automatisch fragen" - die
  Einstellung gilt pro Gerät und Browser.

### Behoben
- **Abbruch der automatischen Abfrage sah aus wie ein Fehler:** Schloss man das
  Passkey-Fenster des Betriebssystems, erschien die rote Meldung „Abgebrochen
  oder Zeitlimit überschritten". Wer die Abfrage gar nicht gestartet hat, hat
  auch nichts falsch gemacht: Es kommt jetzt ein neutraler Hinweis, und die
  automatische Abfrage schaltet sich auf diesem Gerät ab. Bricht man eine
  selbst gestartete Abfrage ab, erscheint weiterhin die bisherige Meldung.
- **Nach dem Abmelden blieb die automatische Abfrage dauerhaft aus:**
  `logout.php` hängt `?logout=1` an die Adresse, damit direkt nach dem
  Abmelden nicht sofort wieder nach dem Passkey gefragt wird. Der Parameter
  blieb danach aber in der Adresse stehen - und Mobilbrowser stellen beim
  Neustart den letzten Tab samt Adresse wieder her. Dadurch kam die Abfrage
  auf diesem Gerät nie wieder von selbst, obwohl die Einstellung an war. Der
  Parameter wird jetzt nach dem Laden aus der Adresse entfernt; gemeint war
  immer "dieses eine Mal", nicht "diese Adresse für immer".
- **Abfrage kam nicht, wenn der Tab nur nach vorne geholt wurde:** Öffnet man
  die Seite über eine Verknüpfung auf dem Startbildschirm, holt der Browser
  häufig nur den noch offenen Tab der letzten Sitzung nach vorne, statt die
  Seite neu zu laden. Damit lief der Startcode kein zweites Mal und die
  Abfrage erschien erst nach einem manuellen Neuladen. Die Seite
  reagiert jetzt zusätzlich darauf, wenn sie wieder sichtbar wird, den
  Fokus bekommt oder aus dem Ruhezustand zurückkehrt.
- **Hängengebliebene Abfrage blockierte alle weiteren:** Geht die Seite in den
  Hintergrund, während eine Passkey-Abfrage läuft, beendet vor allem Android
  die Abfrage oft nie. Die interne Sperre gegen doppelte Abfragen blieb
  dadurch dauerhaft gesetzt, und beim Zurückkehren kam nie wieder eine
  Abfrage - nur ein Neuladen half. Solche Abfragen werden jetzt beim
  Zurückkehren erkannt und abgeräumt.
- **Fehlschlag ohne Fenster-Fokus schaltete die Abfrage ab:** Ohne Fokus lehnt
  Chrome eine Passkey-Abfrage mit demselben Fehler ab wie ein Abbruch durch
  den Nutzer. Das hätte die automatische Abfrage fälschlich ausgeschaltet;
  dieser Fall bleibt jetzt folgenlos und wird beim nächsten Fokus erneut
  versucht.
- **Zwei gleichzeitige Passkey-Abfragen:** Drückte man den Knopf, während die
  automatische Abfrage noch offen war, startete eine zweite - Chrome und Brave
  weisen die aber sofort mit „NotAllowedError" ab, ohne Dialog. Das sah aus
  wie ein sofortiger Abbruch durch den Nutzer. Ein Klick bricht eine laufende
  Abfrage jetzt sauber ab (`AbortController`), statt eine zweite danebenzu-
  stellen; die abgelöste Abfrage zeigt keine Meldung und ändert keine
  Einstellung.

  Randnotiz für alle, die eine Verknüpfung auf dem Startbildschirm
  angelegt haben: Wurde sie in einem Moment erstellt, in dem die Adresse
  noch `?logout=1` enthielt, speichert Android das dauerhaft mit - jeder
  Tipp auf das Icon würde dann fälschlich als "gerade abgemeldet" gelten.
  Die Verknüpfung einmal neu anlegen (von der reinen Adresse ohne Zusatz)
  behebt das.
- **Alte Meldung blieb beim Klick stehen:** Der Knopf räumt die vorherige
  Meldung jetzt weg, bevor er startet - sonst wirkte eine ältere Fehlermeldung
  wie die Antwort auf den gerade erfolgten Klick.
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
