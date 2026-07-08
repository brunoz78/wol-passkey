<?php
/*
  Konfigurations-VORLAGE.

  Installation:
    1. Diese Datei nach config.php kopieren
    2. Alle Werte unten anpassen (insbesondere $setup_key!)
    3. config.php niemals veröffentlichen - sie enthält deinen Setup-Schlüssel
*/

// Titel der Webseite (wird auch im Passkey-Dialog des Geräts angezeigt)
$sitename = "Wake on LAN";

// Geheimer Setup-Schlüssel: Wer ihn kennt, kann über setup.php das
// Login-Passwort (zurück)setzen. Einen langen Zufallswert eintragen und
// z.B. im Passwort-Manager ablegen. Solange der Platzhalter unten nicht
// ersetzt wurde, verweigert setup.php den Dienst.
$setup_key = "BITTE-EIGENEN-SCHLUESSEL-EINTRAGEN";

// UDP-Port für das Magic Packet. Üblich ist Port 9.
$port = 9;

// Broadcast-Adresse deines Heimnetzes.
// Beispiel: Netz 192.168.1.x  ->  192.168.1.255
$networkbroadcast = "192.168.1.255";

/*
  Startliste der Zielgeräte ("Name" => "MAC-Adresse").
  Erlaubte MAC-Formate:
   Windows: 00-1e-8C-5B-C8-28
   Unix:    00:1E:8C:5B:C8:29
   Cisco:   001E.8C5B.C827
   Einfach: 001E8C5bc826

  HINWEIS: Diese Liste dient nur als ERSTBEFÜLLUNG. Sobald die Datei
  auth/devices-data.php existiert (wird beim ersten Seitenaufruf angelegt),
  werden die Geräte über die Webseite "Geräte verwalten" gepflegt und
  Änderungen hier haben keine Wirkung mehr.
*/
$maclist = [
//	"Wohnzimmer-PC"	=> "00:11:22:33:44:55",
//	"NAS"			=> "66:77:88:99:AA:BB",
	];
?>
