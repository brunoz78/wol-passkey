<?php
/*
  Prüft die Sprachdateien in lang/ gegeneinander:

    - doppelte Schlüssel innerhalb einer Datei
    - Schlüssel, die gegenüber der Ausgangssprache fehlen oder zu viel sind
    - abweichende Anzahl von sprintf-Platzhaltern (%s, %d)

  Doppelte Schlüssel fallen beim blossen Laden der Datei NICHT auf: PHP
  behält bei doppelten Array-Schlüsseln stillschweigend den letzten. Genau
  so lässt sich ein bestehender Text versehentlich überschreiben. Deshalb
  wird hier der Quelltext zerlegt statt das geladene Array betrachtet.

  Aufruf:  php tools/check-lang.php
  Rückgabewert 0 = alles in Ordnung, 1 = Beanstandungen (für Skripte).
*/

$langDir = dirname(__DIR__) . '/lang';
$source  = 'de';   // Ausgangssprache, siehe auth/i18n.php (I18N_SOURCE)

/*
  Liefert ['schlüssel' => [zeile, ...]] aus dem Quelltext einer Sprachdatei.
  Zerlegt wird mit dem PHP-Tokenizer; ein Schlüssel ist jede Zeichenkette,
  auf die auf der obersten Array-Ebene ein "=>" folgt.
*/
function lang_keys_from_source(string $file): array {
    $tokens = token_get_all((string)file_get_contents($file));
    $keys = [];
    $depth = 0;
    $last = null;
    $lastLine = 0;

    foreach ($tokens as $token) {
        if (is_string($token)) {
            if ($token === '[') {
                $depth++;
                $last = null;
            } elseif ($token === ']') {
                $depth--;
                $last = null;
            }
            continue;
        }

        [$id, $text, $line] = $token;

        if ($id === T_WHITESPACE || $id === T_COMMENT || $id === T_DOC_COMMENT) {
            continue;
        }
        if ($id === T_CONSTANT_ENCAPSED_STRING) {
            $last = $text;
            $lastLine = $line;
            continue;
        }
        if ($id === T_DOUBLE_ARROW && $depth === 1 && $last !== null) {
            $keys[substr($last, 1, -1)][] = $lastLine;
        }
        $last = null;
    }
    return $keys;
}

/* Anzahl der sprintf-Platzhalter; "%%" ist ein ausgegebenes Prozentzeichen. */
function placeholder_count(string $text): int {
    return preg_match_all('/%(?!%)[-+ 0#\']*[0-9]*(?:\.[0-9]+)?[bcdeEfFgGosuxX]/', $text);
}

$files = glob($langDir . '/*.php');
sort($files);
if ($files === [] || $files === false) {
    fwrite(STDERR, "Keine Sprachdateien in $langDir gefunden.\n");
    exit(1);
}

$problems = 0;
$sourceFile = $langDir . '/' . $source . '.php';
if (!is_file($sourceFile)) {
    fwrite(STDERR, "Ausgangssprache $source.php fehlt.\n");
    exit(1);
}
$sourceTexts = include $sourceFile;

foreach ($files as $file) {
    $code = basename($file, '.php');
    $keys = lang_keys_from_source($file);
    $texts = include $file;
    $notes = [];

    // 1. Doppelte Schlüssel
    foreach ($keys as $key => $lines) {
        if (count($lines) > 1) {
            $notes[] = sprintf('doppelter Schlüssel "%s" in Zeile %s', $key, implode(' und ', $lines));
        }
    }

    // 2./3. Abgleich mit der Ausgangssprache
    if ($code !== $source) {
        foreach (array_diff(array_keys($sourceTexts), array_keys($texts)) as $missing) {
            $notes[] = sprintf('fehlt: "%s"', $missing);
        }
        foreach (array_diff(array_keys($texts), array_keys($sourceTexts)) as $extra) {
            $notes[] = sprintf('unbekannt (nicht in %s.php): "%s"', $source, $extra);
        }
        foreach ($texts as $key => $text) {
            if (!isset($sourceTexts[$key]) || !is_string($text) || !is_string($sourceTexts[$key])) {
                continue;
            }
            $want = placeholder_count($sourceTexts[$key]);
            $have = placeholder_count($text);
            if ($want !== $have) {
                $notes[] = sprintf('"%s": %d Platzhalter statt %d', $key, $have, $want);
            }
        }
    }

    if ($notes === []) {
        printf("%s.php: %d Schlüssel, in Ordnung\n", $code, count($texts));
    } else {
        $problems += count($notes);
        printf("%s.php: %d Schlüssel, %d Beanstandung(en)\n", $code, count($texts), count($notes));
        // Fehlt eine Datei komplett, wären es hunderte Zeilen - die ersten
        // reichen, um das Problem zu erkennen.
        foreach (array_slice($notes, 0, 12) as $note) {
            echo '  - ' . $note . "\n";
        }
        if (count($notes) > 12) {
            printf("  ... und %d weitere\n", count($notes) - 12);
        }
    }
}

if ($problems > 0) {
    printf("\n%d Beanstandung(en) insgesamt.\n", $problems);
    exit(1);
}
echo "\nAlle Sprachdateien sind synchron.\n";
exit(0);
