# Proxmox-Helper-Scripts

Diese drei Dateien erzeugen einen fertigen Debian-LXC mit nginx, PHP-FPM und
installiertem WoL Passkey. Sie folgen exakt der Spezifikation von
[community-scripts](https://community-scripts.org) (`AGENTS.md` im Repo
[ProxmoxVED](https://github.com/community-scripts/ProxmoxVED)), damit sie ohne
Umbau eingereicht werden können, sobald die Aufnahmekriterien erfüllt sind.

| Datei | Läuft wo | Aufgabe |
|---|---|---|
| `ct/wol-passkey.sh` | Proxmox-Host | Legt den LXC an, definiert Ressourcen, enthält `update_script()` |
| `install/wol-passkey-install.sh` | im Container | nginx + PHP-FPM, App nach `/opt/wol-passkey`, vhost |
| `json/wol-passkey.json` | – | Metadaten für die Website (Kategorie, Port, Hinweise) |

Kommentare und Ausgaben sind hier bewusst **englisch** – anders als im übrigen
Projekt. Diese Dateien landen unverändert in einem englischsprachigen
Fremd-Repository.

## Jetzt schon nutzbar: Installation aus dem eigenen Fork

Das Framework (`misc/build.func`) ist öffentlich und respektiert die Variable
`COMMUNITY_SCRIPTS_URL`. Damit laufen die Scripts aus einem eigenen Fork, ohne
dass irgendetwas eingereicht oder freigegeben sein muss.

Der Fork ist bereits eingerichtet:
[brunoz78/ProxmoxVED](https://github.com/brunoz78/ProxmoxVED), Branch
`feat/wol-passkey`. Die drei Dateien liegen dort in `ct/`, `install/` und
`json/`.

Installation auf der **Proxmox-Host-Shell**:

```bash
BASE=https://raw.githubusercontent.com/brunoz78/ProxmoxVED/feat/wol-passkey; curl -fsSL "$BASE/misc/run.sh" | bash -s -- "$BASE" ct/wol-passkey.sh
```

`run.sh` reicht die Basis-URL an alle Folge-Downloads durch. Ohne diesen Umweg
sieht das Script nicht, von wo es geladen wurde, und würde das Install-Script
vom Upstream holen (wo es nicht existiert).

Der gleiche Weg ist auch der offizielle Testweg vor einem Pull Request. `main`
im Fork bleibt bewusst unverändert, damit er sich jederzeit mit dem Upstream
abgleichen lässt.

**Nach Änderungen an den Dateien hier** müssen sie in den Fork gespiegelt und
gepusht werden – der Ordner in diesem Repo ist die Quelle, der Fork nur die
Auslieferung.

## Einreichen bei community-scripts

**Neue Scripts gehen nach `ProxmoxVED`, nicht nach `ProxmoxVE`.** PRs mit neuen
Scripts gegen `ProxmoxVE` werden ohne Review geschlossen; die Maintainer
befördern ein Script nach erfolgreichem Test selbst ins Produktiv-Repo.

Die Aufnahmekriterien stehen im PR-Template von ProxmoxVED und werden
automatisiert geprüft:

- Anwendung mindestens **6 Monate alt**
- Anwendung **aktiv gepflegt**
- **600+ GitHub-Sterne**
- offizielle **Release-Tarballs** veröffentlicht

Solange die Sternzahl nicht erreicht ist, ist der Weg über den eigenen Fork
oben die praktikable Lösung – funktional identisch, nur ohne Listing auf der
Website.

## Vorgehen für weitere eigene Projekte

Die Struktur ist pro Projekt dieselbe; nur der App-Teil ändert sich.

1. **`APP` bestimmt den Dateinamen.** Das Framework bildet
   `NSAPP = APP kleingeschrieben ohne Leerzeichen` und lädt daraus
   `install/<NSAPP>-install.sh`. Ein Leerzeichen in `APP` würde also
   `wolpasskey-install.sh` suchen – deshalb heisst `APP` hier `WoL-Passkey`
   mit Bindestrich. In der JSON-Datei ist `name` reiner Anzeigetext und darf
   Leerzeichen enthalten; `slug` muss zu den Dateinamen passen.
2. **Release-Asset bereitstellen.** `fetch_and_deploy_gh_release` im
   `prebuild`-Modus erwartet ein Archiv, das genau **einen** obersten Ordner
   enthält – `tools/build-release.php` erzeugt das bereits so
   (`wol-passkey/…`). Der Glob im Script (`wol-passkey-*.zip`) muss zum
   Asset-Namen passen.
3. **Framework-Funktionen statt Eigenbau.** `misc/tools.func` enthält u. a.
   `setup_php`, `setup_nodejs`, `setup_postgresql`, `fetch_and_deploy_gh_release`,
   `check_for_gh_release`, `create_backup`/`restore_backup`. Eigene
   `curl`/`wget`-Download- oder Versionsvergleich-Logik ist ein
   Ablehnungsgrund.
4. **Verfügbarkeit immer gegen ProxmoxVED prüfen, nicht gegen ProxmoxVE.** Die
   beiden Repos haben *unterschiedliche* `tools.func`. `nginx_enable_site` und
   `get_php_fpm_socket` gibt es z. B. nur in ProxmoxVE – ein Script, das sie
   nutzt, bricht in VED mit Exit-Code 127 ab. Am schnellsten prüft man das im
   Klon des Forks:

   ```bash
   grep -n "^funktionsname() {" misc/*.func
   ```

   `catch_errors` liegt übrigens in `misc/error_handler.func`, nicht in
   `core.func`.
5. **Diese Funktionen nicht in `msg_info`/`msg_ok` einpacken** – sie bringen
   ihre eigenen Statusmeldungen mit. Nur eigener Code wird eingerahmt.
6. **`$STD` vor jedes `apt`/Build-Kommando**, `apt` statt `apt-get`. Basis-Pakete
   (`curl`, `sudo`, `wget`, `jq`, `gnupg`, `ca-certificates`) nicht als
   Abhängigkeit auflisten – die sind vorhanden.
7. **`update_script()` muss wirklich aktualisieren**: Dienst stoppen,
   `create_backup` der Konfig- und Datendateien, `CLEAN_INSTALL=1` neu
   ausrollen, `restore_backup`, Rechte setzen, Dienst starten. Am Ende `exit`.
8. **Kein Docker, keine eigenen Systembenutzer, kein `sudo`** – im LXC läuft
   alles als root, die App-Dateien gehören dem Dienstbenutzer (hier `www-data`).
9. **Kein `git pull`** für Updates.
10. **Dev-Dateien ausschliessen:** neue Ordner in
    `tools/build-release.php` unter `$excludeDirs` eintragen, sonst landen sie im
    Installations-ZIP.
11. Bei nginx: der `deny`-Block für interne Verzeichnisse muss **vor** dem
    `location ~ \.php$`-Block stehen – nginx nimmt den ersten passenden
    Regex-Block.

Massgeblich ist immer
[`AGENTS.md`](https://github.com/community-scripts/ProxmoxVED/blob/main/AGENTS.md)
im ProxmoxVED-Repo. Dort steht auch die Liste der Kategorie-IDs für die
JSON-Datei (WoL Passkey nutzt `4` = Network & Firewall).

## Bekannte Einschränkungen

- **Passkeys brauchen einen sicheren Kontext.** Über `http://<IP>` funktioniert
  nur die Passwort-Anmeldung. Für WebAuthn muss der Container hinter einem
  Reverse Proxy mit TLS und einem Hostnamen stehen – genau das Szenario, für
  das die App ohnehin ausgelegt ist.
- **Magic Packets erreichen nur das eigene Subnetz.** Das Install-Script leitet
  `$networkbroadcast` aus der Broadcast-Adresse des Containers ab. Liegen die
  Zielgeräte in einem anderen Netz, muss der Wert in
  `/opt/wol-passkey/config.php` angepasst werden.
- **Der Setup-Schlüssel wird zufällig erzeugt** und am Ende der Installation
  angezeigt. Ohne ihn lässt sich über `/setup.php` kein Login-Passwort setzen.
  Später nachschlagen lässt er sich vom Proxmox-Host aus mit:

  ```bash
  pct exec <CTID> -- grep setup_key /opt/wol-passkey/config.php
  ```
- **arm64 ist nicht getestet.** In `ct/wol-passkey.sh` ist `var_arm64` deshalb
  auskommentiert; das Framework fragt dann beim Anlegen nach. Erst nach einem
  echten Test auf `yes` setzen – dann auch `"has_arm": true` in die JSON-Datei
  aufnehmen (das Feld fehlt bewusst, solange nichts geprüft ist).
- **Installiert wird immer das neueste GitHub-Release**, nicht der Stand von
  `main`. Änderungen wirken sich erst nach einem neuen Release mit
  hochgeladenem `wol-passkey-<version>.zip` aus.
