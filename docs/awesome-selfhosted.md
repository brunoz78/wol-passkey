# Eintrag für awesome-selfhosted

Vorbereitete Einreichung für [awesome-selfhosted.net](https://awesome-selfhosted.net).
Anders als bei community-scripts gibt es dort **keine Sterne-Hürde** – Wakupator
steht mit 178 Sternen auf der Liste.

## Frühestens ab 9. November 2026

Das einzige Kriterium, das noch fehlt: *„Any software project you are adding was
first released more than 4 months ago."* Erstes Release war `v1.0.0` am
**09.07.2026**, die Frist endet also um den **09.11.2026**.

Ein früher eingereichter Pull Request wird mit einer vorgefertigten Antwort
geschlossen. Alle übrigen Kriterien sind erfüllt: MIT-Lizenz, aktive Pflege,
getaggte Releases mit Changelog, englische Dokumentation (`README_en.md`) und
eine funktionierende Installationsanleitung.

## Wie eingereicht wird

Nicht im Repo `awesome-selfhosted` – die Liste wird generiert. Gepflegt wird
[awesome-selfhosted-data](https://github.com/awesome-selfhosted/awesome-selfhosted-data),
eine YAML-Datei pro Software unter `software/`.

Komplett über die GitHub-Weboberfläche:

1. In `software/` auf **Add file → Create new file**
2. Dateiname `wol-passkey.yml` (kebab-case ist Vorschrift)
3. Inhalt aus dem Block unten einfügen
4. **Create a new branch for this commit and start a pull request** wählen
5. Commit-Nachricht `add WoL Passkey`
6. Im PR-Formular alle Checkboxen abhaken

## Der Dateiinhalt

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
GitHub-API – siehe `software/upsnap.yml` im Daten-Repo.

`Network Utilities` ist der Tag, unter dem auch UpSnap und Wakupator laufen.

## Stilregeln für die Beschreibung

Die Maintainer achten darauf:

- kürzer als 250 Zeichen, Satzanfang gross
- keine Wörter wie *open-source*, *free* oder *self-hosted* – auf dieser Liste
  ist das selbstverständlich
- Kurzform bevorzugt: `Minimalist text adventure game` statt
  `A minimalist text adventure game` oder `$PROJECT is a ...`

Ausdrücklich in den Richtlinien: *„Machine/LLM-generated contributions, that do
not respect project guidelines are not allowed and will result in a ban."* Die
Beschreibung oben vor dem Absenden also selbst gegenlesen und ruhig umformulieren.

## Quellen

- [CONTRIBUTING.md](https://github.com/awesome-selfhosted/awesome-selfhosted-data/blob/master/CONTRIBUTING.md)
- [Vorlage für den Dateiaufbau](https://github.com/awesome-selfhosted/awesome-selfhosted-data/blob/master/.github/ISSUE_TEMPLATE/addition.md)
- [Checkliste im PR-Formular](https://github.com/awesome-selfhosted/awesome-selfhosted-data/blob/master/.github/PULL_REQUEST_TEMPLATE.md)
