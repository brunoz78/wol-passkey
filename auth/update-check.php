<?php
/*
  Update-Hinweis: Prüft höchstens 1x täglich (WOL_UPDATE_CHECK_INTERVAL) über
  die GitHub-Releases-API, ob eine neuere Version als WOL_VERSION existiert,
  und merkt sich das Ergebnis in einer selbstschützenden Cache-Datei (gleiches
  Format wie auth/data.php: PHP-Header mit 403-exit, danach base64-kodiertes
  JSON). Kein Auto-Update - es wird nur ein Hinweis mit Link zur Release-Seite
  angezeigt (siehe partials/head.php). Netzwerkfehler werden verschluckt: die
  Seite darf dadurch nie langsamer werden oder abbrechen.
*/

function wol_update_cache_load() {
    if (!is_file(WOL_UPDATE_CACHE_FILE)) {
        return null;
    }
    $raw = file_get_contents(WOL_UPDATE_CACHE_FILE);
    $marker = '?>';
    $pos = strpos($raw, $marker);
    $encoded = $pos === false ? '' : trim(substr($raw, $pos + strlen($marker)));
    if ($encoded === '') {
        return null;
    }
    $json = base64_decode($encoded, true);
    $data = $json !== false ? json_decode($json, true) : null;
    return is_array($data) ? $data : null;
}

function wol_update_cache_save(array $data) {
    $encoded = base64_encode(json_encode($data));
    $content = "<?php http_response_code(403); exit; ?>\n" . $encoded . "\n";
    $tmp = WOL_UPDATE_CACHE_FILE . '.tmp';
    if (@file_put_contents($tmp, $content, LOCK_EX) === strlen($content)) {
        @rename($tmp, WOL_UPDATE_CACHE_FILE);
    }
}

/* Fragt die neueste Release von GitHub ab. Kurzer Timeout, liefert null bei jedem Fehler. */
function wol_update_fetch_latest() {
    $ctx = stream_context_create(['http' => [
        'method'  => 'GET',
        'header'  => "User-Agent: wol-passkey-update-check\r\nAccept: application/vnd.github+json\r\n",
        'timeout' => 3,
    ]]);
    $json = @file_get_contents(
        'https://api.github.com/repos/' . WOL_UPDATE_REPO . '/releases/latest', false, $ctx
    );
    if ($json === false) {
        return null;
    }
    $data = json_decode($json, true);
    $tag = $data['tag_name'] ?? null;
    if (!is_string($tag) || $tag === '') {
        return null;
    }
    return [
        'version' => ltrim($tag, 'v'),
        'url'     => is_string($data['html_url'] ?? null)
            ? $data['html_url']
            : 'https://github.com/' . WOL_UPDATE_REPO . '/releases/latest',
    ];
}

/**
 * Liefert ['available' => bool, 'latest' => string, 'url' => string]
 * oder null, wenn (noch) keine Information vorliegt (z.B. erster Aufruf ohne
 * Internetzugang). Greift nur auf die GitHub-API zu, wenn der Cache fehlt
 * oder älter als WOL_UPDATE_CHECK_INTERVAL ist.
 */
function wol_update_check() {
    $cache = wol_update_cache_load();
    $now = time();
    $stale = !$cache || ($now - ($cache['checked_at'] ?? 0)) > WOL_UPDATE_CHECK_INTERVAL;

    if ($stale) {
        $latest = wol_update_fetch_latest();
        if ($latest) {
            $cache = ['checked_at' => $now, 'version' => $latest['version'], 'url' => $latest['url']];
            wol_update_cache_save($cache);
        } elseif (!$cache) {
            // Kein alter Cache und Abruf fehlgeschlagen (z.B. kein Internet): nichts anzeigen.
            return null;
        }
        // Abruf fehlgeschlagen, aber alter Cache vorhanden: den weiter benutzen
        // und den Zeitpunkt NICHT aktualisieren, damit es beim nächsten
        // Seitenaufruf erneut versucht wird.
    }

    if (empty($cache['version'])) {
        return null;
    }

    return [
        'available' => version_compare($cache['version'], WOL_VERSION, '>'),
        'latest'    => $cache['version'],
        'url'       => $cache['url'] ?? ('https://github.com/' . WOL_UPDATE_REPO . '/releases/latest'),
    ];
}
