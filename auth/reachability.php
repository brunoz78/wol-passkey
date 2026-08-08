<?php
/*
  Prüft, ob ein Gerät im Netzwerk erreichbar ist - ganz ohne shell_exec() oder
  Raw-Sockets (dafür bräuchte der Webserver-Prozess Root-Rechte, die er auf
  einem NAS oder in einem Container normalerweise nicht hat). Stattdessen wird
  auf ein paar gängigen Ports ein TCP-Verbindungsversuch gemacht.

  Als "läuft" gilt nur, was das Zielgerät SELBST beantwortet hat:
    - es nimmt die Verbindung an (Port offen), oder
    - sein Betriebssystem lehnt sie mit "connection refused" ab
      (Port zu, Rechner aber wach).
  Alles andere - gar keine Antwort, "no route to host", "network unreachable" -
  gilt als nicht erreichbar. Diese Fehler kommen nämlich vom Router oder vom
  Kernel des eigenen Servers, nicht vom Zielgerät.

  Wichtig: Wie SCHNELL der Versuch scheitert, taugt nicht als Unterscheidung
  (bis 1.4.2 wurde es dafür benutzt und lag falsch). Bekommt der Kernel auf
  seine ARP-Anfrage keine Antwort, merkt er sich den Nachbarn eine Weile als
  "failed" und beantwortet weitere Verbindungsversuche dorthin sofort selbst
  mit "no route to host" - genauso schnell wie ein echtes "connection refused".
  Ein ausgeschaltetes Gerät im selben Subnetz wurde dadurch dauerhaft als
  online angezeigt.

  Die Ports werden gleichzeitig geprüft, nicht nacheinander: Sonst kostet ein
  ausgeschaltetes Gerät das Zeitlimit mal Anzahl Ports.
*/

define('DEVICE_CHECK_PORTS', [22, 80, 443, 445, 3389]);
define('DEVICE_CHECK_TIMEOUT', 0.5); // Sekunden, gilt für alle Ports zusammen

/*
  errno-Werte für ECONNREFUSED. Die Zahl ist plattformabhängig, deshalb sind
  die gängigen Werte fest hinterlegt; ist die (ohnehin vorausgesetzte)
  sockets-Extension geladen, kommt der exakte Wert dieser Plattform dazu.
*/
function device_refused_errnos() {
    $codes = [111, 61, 10061]; // Linux, BSD/macOS, Windows
    if (defined('SOCKET_ECONNREFUSED')) {
        $codes[] = SOCKET_ECONNREFUSED;
    }
    return array_values(array_unique($codes));
}

function device_is_reachable($ip) {
    if (!is_string($ip) || $ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
        return false;
    }

    if (function_exists('socket_create')) {
        return device_probe_parallel($ip);
    }
    return device_probe_sequential($ip);
}

/*
  Alle Ports gleichzeitig anstossen und einmal gemeinsam warten. Über
  SO_ERROR steht danach pro Socket der exakte Fehler fest.
*/
function device_probe_parallel($ip) {
    $refused = device_refused_errnos();
    $pending = [];

    foreach (DEVICE_CHECK_PORTS as $port) {
        $sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($sock === false) {
            continue;
        }
        socket_set_nonblock($sock);
        socket_clear_error($sock);

        if (@socket_connect($sock, $ip, $port)) {
            socket_close($sock);
            device_close_all($pending);
            return true; // sofort verbunden -> Gerät läuft
        }

        $err = socket_last_error($sock);
        if (in_array($err, $refused, true)) {
            socket_close($sock);
            device_close_all($pending);
            return true; // sofort abgelehnt -> Gerät läuft, Port ist nur zu
        }
        if ($err === SOCKET_EINPROGRESS || $err === SOCKET_EWOULDBLOCK || $err === 0) {
            $pending[] = $sock; // Verbindungsaufbau läuft noch
        } else {
            socket_close($sock); // sofortiger anderer Fehler, z.B. "no route to host"
        }
    }

    $deadline = microtime(true) + DEVICE_CHECK_TIMEOUT;

    while ($pending) {
        $rest = $deadline - microtime(true);
        if ($rest <= 0) {
            break;
        }
        $read = null;
        $write = $pending;
        $except = $pending;
        $sec = (int) $rest;
        $usec = (int) (($rest - $sec) * 1000000);

        $ready = @socket_select($read, $write, $except, $sec, $usec);
        if ($ready === false || $ready === 0) {
            break; // Fehler oder Zeitlimit -> keine Antwort
        }

        // Ein Socket kann in beiden Listen stehen - sonst würde er doppelt
        // geschlossen.
        $fertig = [];
        foreach (array_merge((array) $write, (array) $except) as $sock) {
            if (!in_array($sock, $fertig, true)) {
                $fertig[] = $sock;
            }
        }

        foreach ($fertig as $sock) {
            $err = @socket_get_option($sock, SOL_SOCKET, SO_ERROR);
            if ($err === 0 || in_array($err, $refused, true)) {
                device_close_all($pending);
                return true;
            }
            foreach ($pending as $k => $open) {
                if ($open === $sock) {
                    unset($pending[$k]);
                    break;
                }
            }
            socket_close($sock);
        }
        $pending = array_values($pending);
    }

    device_close_all($pending);
    return false;
}

function device_close_all($sockets) {
    foreach ($sockets as $sock) {
        @socket_close($sock);
    }
}

/*
  Rückfallweg ohne sockets-Extension: nacheinander, entsprechend langsamer.
*/
function device_probe_sequential($ip) {
    $refused = device_refused_errnos();

    foreach (DEVICE_CHECK_PORTS as $port) {
        $errno = 0;
        $errstr = '';
        $conn = @fsockopen($ip, $port, $errno, $errstr, DEVICE_CHECK_TIMEOUT);
        if ($conn !== false) {
            fclose($conn);
            return true;
        }
        if (in_array($errno, $refused, true)) {
            return true;
        }
    }

    return false;
}
