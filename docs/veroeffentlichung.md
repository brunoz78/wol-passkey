# Veröffentlichungskanäle

Wo das Projekt eingetragen werden kann, was dort jeweils verlangt wird und
welche Beschreibung hingehört. Stand: 06.08.2026.

| Kanal | Status |
|---|---|
| [selfh.st – Newsletter](#selfhst--self-host-weekly-newsletter) | **sofort möglich** |
| [selfh.st – Verzeichnis](#selfhst--verzeichnis) | erst wenn das Projekt gesetzter ist |
| [awesome-selfhosted](#awesome-selfhosted) | **ab ca. 09.11.2026** |
| [community-scripts.org](#community-scriptsorg) | blockiert (600 Sterne) |

Wichtig: Jeder Kanal hat eine eigene Textlänge und einen eigenen Ton. Nicht
denselben Text überall einsetzen – die Beschreibungen stehen unten je Kanal.

---

## selfh.st – Self-Host Weekly (Newsletter)

Einreichung über <https://selfh.st/submit/> → **Self-Host Weekly (Newsletter)**,
dort als *Project Launch*.

Das Verzeichnis nimmt ausdrücklich keine neuen Projekte auf („Newly launched
projects will not be considered for the directory"), verweist dafür aber genau
auf diesen Weg.

Im Newsletter heisst der Abschnitt **Content Spotlight** und folgt einem festen
Muster: erst ein Absatz, was die Software tut, dann ein kurzer Absatz zur
Bereitstellung, dann die Links. Hier ist der Hinweis auf die
Proxmox-Installation also erwünscht – anders als in den kurzen
Verzeichnis-Einträgen.

Einzukalkulieren: Der Betreiber erhält nach eigener Aussage über 25
Einreichungen pro Tag, vorgestellt wird ein Projekt pro Ausgabe.

**Text:**

```
Meet WoL Passkey, a Wake-on-LAN interface built for phones. Devices appear as
tiles — tap one to send the magic packet, and an optional IP address shows at a
glance whether a machine is already awake. Sign-in uses a passkey (WebAuthn)
via fingerprint or Face ID, with a password as fallback.

WoL Passkey is flat-file PHP with no database and no build step, so it runs on
any web server with PHP 8. A one-line script is also available to deploy it as
a Proxmox LXC.
```

Falls das Formularfeld kurz ist, reicht der erste Satz plus der
Passkey-Hinweis.

Links zum Angeben: <https://github.com/brunoz78/wol-passkey>

---

## selfh.st – Verzeichnis

Später, wenn das Projekt nicht mehr als „newly launched" gilt. Es gibt dort
eine eigene Kategorie **Wake-on-LAN** mit UpSnap, wol und WoLi WebGUI –
letzteres mit 55 Sternen, eine Sterne-Hürde besteht also nicht.

Die Einträge sind sehr knapp gehalten, Substantivphrase ohne Artikel:

```
Mobile-friendly Wake-on-LAN interface with passkey login
```

Kategorien: `Wake-on-LAN`, `Networking`, `Remote Access`.
Kein Proxmox-Hinweis – dafür ist der Platz zu knapp, und Passkey ist das
Unterscheidungsmerkmal gegenüber den drei bestehenden Einträgen.

---

## awesome-selfhosted

**Frühestens ab 9. November 2026.** Einziges noch offenes Kriterium: *„Any
software project you are adding was first released more than 4 months ago."*
Release `v1.0.0` erschien am 09.07.2026. Ein früherer Pull Request wird mit
einer vorgefertigten Antwort geschlossen.

Alle übrigen Kriterien sind erfüllt: MIT-Lizenz, aktive Pflege, getaggte
Releases mit Changelog, englische Dokumentation (`README_en.md`), funktionierende
Installationsanleitung. Eine Sterne-Hürde gibt es nicht.

Eingereicht wird nicht im Repo `awesome-selfhosted` – die Liste wird generiert –
sondern in
[awesome-selfhosted-data](https://github.com/awesome-selfhosted/awesome-selfhosted-data),
eine YAML-Datei pro Software unter `software/`. Komplett über die
GitHub-Weboberfläche machbar:

1. In `software/` auf **Add file → Create new file**
2. Dateiname `wol-passkey.yml` (kebab-case ist Vorschrift)
3. Inhalt aus dem Block unten einfügen
4. **Create a new branch for this commit and start a pull request** wählen
5. Commit-Nachricht `add WoL Passkey`
6. Im PR-Formular alle Checkboxen abhaken

**Dateiinhalt:**

```yaml
name: WoL Passkey
website_url: https://github.com/brunoz78/wol-passkey
source_code_url: https://github.com/brunoz78/wol-passkey
description: Wake on LAN dashboard with passkey (WebAuthn) login. Tap a tile to send the magic packet and see which devices are already online.
licenses:
  - MIT
platforms:
  - PHP
tags:
  - Network Utilities
```

Mehr Felder braucht es nicht. `stargazers_count`, `current_release`,
`commit_history` und `archived` erzeugen die Maintainer automatisch aus der
GitHub-API. `Network Utilities` ist der Tag, unter dem auch UpSnap und
Wakupator laufen.

Stilregeln dort: kürzer als 250 Zeichen, Satzanfang gross, keine Wörter wie
*open-source*, *free* oder *self-hosted*, Kurzform bevorzugt (`Minimalist text
adventure game` statt `A minimalist text adventure game`).

Ausdrücklich in den Richtlinien: *„Machine/LLM-generated contributions, that do
not respect project guidelines are not allowed and will result in a ban."* Die
Beschreibung vor dem Absenden also selbst gegenlesen.

Quellen: [CONTRIBUTING.md](https://github.com/awesome-selfhosted/awesome-selfhosted-data/blob/master/CONTRIBUTING.md) ·
[Dateiaufbau](https://github.com/awesome-selfhosted/awesome-selfhosted-data/blob/master/.github/ISSUE_TEMPLATE/addition.md) ·
[PR-Checkliste](https://github.com/awesome-selfhosted/awesome-selfhosted-data/blob/master/.github/PULL_REQUEST_TEMPLATE.md)

---

## community-scripts.org

Blockiert. Neue Scripts gehen in das Repo
[ProxmoxVED](https://github.com/community-scripts/ProxmoxVED) (nicht ProxmoxVE),
und deren PR-Vorlage verlangt **600+ GitHub-Sterne** sowie ein mindestens
sechs Monate altes Projekt. Die Regel wird strikt durchgesetzt: Von 53
gelisteten Quellprojekten lagen 51 darüber.

Die Dateien in [`proxmox/`](../proxmox/) sind bereits spezifikationskonform –
ein Pull Request wäre reine Formsache, sobald die Hürden fallen. Ausgeliefert
wird bis dahin über den eigenen Fork, siehe
[`proxmox/README.md`](../proxmox/README.md).

---

## Weitere Kanäle

- <https://www.reddit.com/r/selfhosted/> – grösste Reichweite; vorher die Regeln
  zur Eigenwerbung lesen
- <https://www.reddit.com/r/homelab/>
- <https://www.computerbase.de/forum/forums/heimnetzwerke-und-internethardware.41/>
  – deutschsprachig

Das **Proxmox-Forum** ist ein reines Support-Forum ohne Bereich für eigene
Projekte; dort lohnt sich der Aufwand nicht.
