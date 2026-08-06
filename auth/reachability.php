<?php
/*
  Prüft, ob ein Gerät im Netzwerk erreichbar ist - ganz ohne shell_exec() oder
  Raw-Sockets (dafür bräuchte der Webserver-Prozess Root-Rechte, die er auf
  einem NAS/Shared-Hosting normalerweise nicht hat). Stattdessen wird auf ein
  paar gängigen Ports ein TCP-Verbindungsversuch gemacht.

  Kein Port muss dafür wirklich offen sein: Antwortet das Betriebssystem
  schnell mit "connection refused", läuft der Rechner trotzdem - das lässt
  sich am Zeitpunkt des Fehlschlags erkennen (deutlich vor Ablauf des
  Timeouts), ohne auf plattformabhängige errno-Codes angewiesen zu sein.
  Kommt dagegen gar keine Antwort (voller Timeout), gilt der Port als
  "keine Aussage" und der nächste wird versucht. Antwortet KEINER der Ports,
  gilt das Gerät als nicht erreichbar (typischerweise: ausgeschaltet).
*/

define('DEVICE_CHECK_PORTS', [22, 80, 443, 445, 3389]);
define('DEVICE_CHECK_TIMEOUT', 0.3); // Sekunden pro Port

function device_is_reachable($ip) {
    if (!is_string($ip) || $ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
        return false;
    }

    foreach (DEVICE_CHECK_PORTS as $port) {
        $start = microtime(true);
        $conn = @fsockopen($ip, $port, $errno, $errstr, DEVICE_CHECK_TIMEOUT);
        if ($conn !== false) {
            fclose($conn);
            return true; // Port offen -> Gerät läuft
        }
        if ((microtime(true) - $start) < DEVICE_CHECK_TIMEOUT * 0.7) {
            return true; // schnelle Ablehnung -> Gerät läuft, dieser Port ist nur zu
        }
    }

    return false;
}
