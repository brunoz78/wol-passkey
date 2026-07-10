<?php
/*
  Mehrsprachigkeit.

  Die Sprache wird in dieser Reihenfolge bestimmt:
    1. ?lang=xx in der Adresse (wird in einem Cookie gemerkt)
    2. Cookie "wol_lang" aus einem früheren Besuch
    3. Sprache des Browsers (Accept-Language)
    4. Rückfallwert I18N_FALLBACK

  Fehlt ein Text in der gewählten Sprache, wird der deutsche Text benutzt
  (Deutsch ist die Ausgangssprache).

  Neue Sprache: lang/de.php kopieren, übersetzen und den Code unten in
  i18n_languages() eintragen - mehr ist nicht nötig.
*/

define('I18N_DIR',      dirname(__DIR__) . '/lang');
define('I18N_COOKIE',   'wol_lang');
define('I18N_SOURCE',   'de');   // Ausgangssprache = Rückfall für fehlende Texte
define('I18N_FALLBACK', 'en');   // wenn der Browser nichts Passendes mitschickt

/* Verfügbare Sprachen: Code => Bezeichnung in der jeweiligen Sprache. */
function i18n_languages() {
    return [
        'de' => 'Deutsch',
        'en' => 'English',
    ];
}

function i18n_is_available($code) {
    return is_string($code) && isset(i18n_languages()[$code]);
}

/* Aktuell aktive Sprache (Code, z.B. "de"). */
function i18n_current() {
    return $GLOBALS['I18N_LANG'] ?? I18N_FALLBACK;
}

/* Wählt die Sprache anhand von Cookie bzw. Browser-Einstellung. */
function i18n_detect() {
    if (isset($_COOKIE[I18N_COOKIE]) && i18n_is_available($_COOKIE[I18N_COOKIE])) {
        return $_COOKIE[I18N_COOKIE];
    }
    foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '') as $part) {
        $code = strtolower(substr(trim(explode(';', $part)[0]), 0, 2));
        if (i18n_is_available($code)) {
            return $code;
        }
    }
    return I18N_FALLBACK;
}

function i18n_load($lang) {
    $file = I18N_DIR . '/' . $lang . '.php';
    return is_file($file) ? (array)require $file : [];
}

/*
  Muss vor jeder Ausgabe laufen (setzt ggf. Cookie und leitet um).
  Wird von auth/session.php aufgerufen, gilt also auf allen Seiten.
*/
function i18n_init() {
    // Sprachwechsel über ?lang=xx: merken und die Adresse ohne den Parameter
    // neu laden, damit er nicht in Lesezeichen und Links hängen bleibt.
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET'
        && isset($_GET['lang']) && i18n_is_available($_GET['lang'])) {

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
               || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        setcookie(I18N_COOKIE, $_GET['lang'], [
            'expires'  => time() + 365 * 24 * 3600,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => false, // rein kosmetisch, kein Sicherheitsmerkmal
            'samesite' => 'Lax',
        ]);

        $target = strtok($_SERVER['REQUEST_URI'] ?? 'index.php', '?');
        header('Location: ' . ($target !== false && $target !== '' ? $target : 'index.php'));
        exit;
    }

    $lang = i18n_detect();
    $GLOBALS['I18N_LANG'] = $lang;

    // Ausgangssprache als Basis, gewählte Sprache überschreibt sie.
    $GLOBALS['I18N_STRINGS'] = $lang === I18N_SOURCE
        ? i18n_load(I18N_SOURCE)
        : array_merge(i18n_load(I18N_SOURCE), i18n_load($lang));
}

/*
  Übersetzt einen Schlüssel. Weitere Argumente werden wie bei sprintf
  eingesetzt:  t('index.wake_sent', $name)
*/
function t($key, ...$args) {
    $text = $GLOBALS['I18N_STRINGS'][$key] ?? $key;
    return $args ? vsprintf($text, $args) : $text;
}

/* Wie t(), gibt den Text aber direkt HTML-sicher aus. */
function te($key, ...$args) {
    echo htmlspecialchars(t($key, ...$args));
}

/*
  Die Texte für das JavaScript (Schlüssel "js.*", ohne das Präfix).
  Werden in partials/head.php als window.WOL_I18N eingebettet.
*/
function i18n_js_strings() {
    $out = [];
    foreach (($GLOBALS['I18N_STRINGS'] ?? []) as $key => $text) {
        if (strncmp($key, 'js.', 3) === 0) {
            $out[substr($key, 3)] = $text;
        }
    }
    return $out;
}
