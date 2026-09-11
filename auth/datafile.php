<?php
/*
  Lesen und Schreiben der selbstschützenden Datendateien (gleiches Format wie
  auth/data.php: PHP-Header mit 403-exit, danach base64-kodiertes JSON) für
  Dateien, die von mehreren Anfragen gleichzeitig geändert werden - das
  Protokoll und der Gerätestatus. Die Gerätekacheln fragen parallel an, und
  die Hintergrundprüfung (cron.php) läuft unabhängig davon; ohne Sperre würde
  ein Schreibvorgang den anderen überschreiben.
*/

function wol_datafile_read($file) {
    if (!is_file($file)) {
        return [];
    }
    $raw = (string)file_get_contents($file);
    $pos = strpos($raw, '?>');
    $encoded = $pos === false ? '' : trim(substr($raw, $pos + 2));
    $json = $encoded !== '' ? base64_decode($encoded, true) : false;
    $data = $json !== false ? json_decode($json, true) : null;
    return is_array($data) ? $data : [];
}

function wol_datafile_write($file, array $data) {
    $encoded = base64_encode(json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE));
    $content = "<?php http_response_code(403); exit; ?>\n" . $encoded . "\n";
    $tmp = $file . '.tmp';
    if (@file_put_contents($tmp, $content) !== strlen($content)) {
        @unlink($tmp);
        return false;
    }
    if (!@rename($tmp, $file)) {
        @unlink($tmp);
        return false;
    }
    return true;
}

/*
  Liest die Datei unter exklusiver Sperre, übergibt den Inhalt an $fn und
  schreibt das Ergebnis zurück. Gibt $fn null zurück, wird nichts
  geschrieben. Die Sperre liegt auf einer eigenen .lock-Datei, weil rename()
  die Datendatei durch eine neue ersetzt und eine Sperre darauf nichts mehr
  schützen würde.
*/
function wol_datafile_update($file, callable $fn) {
    $lock = @fopen($file . '.lock', 'c');
    if ($lock === false || !flock($lock, LOCK_EX)) {
        return false;
    }
    try {
        $new = $fn(wol_datafile_read($file));
        return $new === null ? true : wol_datafile_write($file, $new);
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}
